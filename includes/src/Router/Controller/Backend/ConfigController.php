<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\AdminAccount;
use JTL\Backend\Permissions;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\Search;
use JTL\Backend\Settings\SectionFactory;
use JTL\Backend\Settings\Sections\SectionInterface;
use JTL\Backend\Settings\Sections\Subsection;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\L10n\GetText;
use JTL\Mail\GmailTest;
use JTL\Mail\OutlookTest;
use JTL\Mail\SmtpTest;
use JTL\Router\Route;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Settings\Settings;
use JTL\Shipping\Services\ShippingService;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ConfigController
 * @package JTL\Router\Controller\Backend
 */
class ConfigController extends AbstractBackendController
{
    private Manager $settingManager;

    private ShippingService $shippingService;

    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        AlertServiceInterface $alertService,
        AdminAccount $account,
        GetText $getText,
        ?ShippingService $shippingService = null,
    ) {
        parent::__construct($db, $cache, $alertService, $account, $getText);
        $this->shippingService = $shippingService ?? Shop::Container()->getShippingService();
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/einstellungen');
        $sectionID            = (int)($args['id'] ?? $_REQUEST['kSektion'] ?? 0);
        $isSearch             = (int)($_REQUEST['einstellungen_suchen'] ?? 0) === 1;
        $sectionFactory       = new SectionFactory();
        $search               = Text::filterXSS(Request::verifyGPDataString('cSuche'));
        $this->settingManager = new Manager($this->db, $smarty, $this->account, $this->getText, $this->alertService);
        $this->getText->loadConfigLocales(true, true);
        $this->route = \str_replace('[/{id}]', '', $this->route);
        if (($response = $this->validatePermissionForSection($sectionID, $request, $args)) !== null) {
            return $response;
        }
        $step = 'uebersicht';
        if ($sectionID > 0) {
            $step    = 'einstellungen bearbeiten';
            $section = $sectionFactory->getSection($sectionID, $this->settingManager);
        } else {
            $section = $sectionFactory->getSection(\CONF_GLOBAL, $this->settingManager);
        }
        $smarty->assign('kEinstellungenSektion', $section->getID())
            ->assign('testResult');
        if ($isSearch) {
            $step = 'einstellungen bearbeiten';
        }
        if (Request::postVar('resetSetting') !== null) {
            $this->settingManager->resetSetting(Request::pString('resetSetting'));
        } elseif ($sectionID > 0 && Request::pInt('einstellungen_bearbeiten') === 1 && Form::validateToken()) {
            $step    = 'einstellungen bearbeiten';
            $section = $this->editConfig($isSearch, $search, $section, $sectionFactory, $sectionID);
        }
        if ($step === 'einstellungen bearbeiten') {
            $this->getConfig($isSearch, $search, $sectionFactory, $sectionID, $section);
        }
        $this->assignScrollPosition();

        return $smarty->assign('cPrefURL', \__('prefURL' . $sectionID))
            ->assign('step', $step)
            ->assign('sectionOverview', $this->settingManager->getAllSections())
            ->assign('route', $this->route)
            ->assign(
                'delivarableCountries',
                $this->shippingService->getPossibleShippingCountries()
            )
            ->assign('countries', Shop::Container()->getCountryService()->getCountrylist())
            ->assign('waehrung', $this->db->select('twaehrung', 'cStandard', 'Y')->cName ?? '')
            ->getResponse('einstellungen.tpl');
    }

    /**
     * @param array<string, int|string> $args
     */
    private function validatePermissionForSection(
        int $sectionID,
        ServerRequestInterface $request,
        array $args
    ): ?ResponseInterface {
        if ((int)($_REQUEST['einstellungen_suchen'] ?? 0) === 1) {
            $sectionID = -1;
        }
        $permission = $this->getPermissionForSection($sectionID);
        if ($permission === null) {
            if ($sectionID === \CONF_BILDER) {
                return new RedirectResponse($this->baseURL . '/' . Route::IMAGES);
            }

            return $sectionID === 0
                ? null
                : $this->notFoundResponse($request, $args, $this->getSmarty());
        }
        $this->checkPermissions($permission);
        if ($sectionID === \CONF_ARTIKELUEBERSICHT) {
            // Sucheinstellungen haben eigene Logik
            return new RedirectResponse($this->baseURL . '/' . Route::SEARCHCONFIG);
        }

        return null;
    }

    public function getConfig(
        bool $isSearch,
        string $search,
        SectionFactory $sectionFactory,
        int $sectionID,
        SectionInterface $section
    ): void {
        $group = Text::filterXSS(Request::verifyGPDataString('group'));
        if ($isSearch) {
            $searchInstance = new Search($this->db, $this->getText, $this->settingManager);
            $sections       = $searchInstance->getResultSections($search);
            $this->getSmarty()->assign('cSearch', $searchInstance->getTitle())
                ->assign('cSuche', $search);
        } else {
            $sectionInstance = $sectionFactory->getSection($sectionID, $this->settingManager);
            $sectionInstance->load();
            $filtered = $sectionInstance->filter($group);
            if ($group !== '' && \count($filtered) > 0) {
                $subsection = new Subsection();
                $subsection->setName(\__($group));
                $subsection->setItems($filtered);
                $sectionInstance->setItems([]);
                $sectionInstance->setSubsections([$subsection]);
            }
            $sections = [$sectionInstance];
        }
        $this->getSmarty()->assign('section', $section)
            ->assign('title', \__('settings') . ': ' . ($group !== '' ? \__($group) : \__($section->getName())))
            ->assign('sections', $sections);
    }

    private function mailServerTest(): void
    {
        \ob_start();
        $meth = Request::pString('email_methode');

        if ($meth === 'smtp') {
            $test = new SmtpTest();
        } elseif ($meth === 'outlook') {
            $test = new OutlookTest();
        } elseif ($meth === 'gmail') {
            $test = new GmailTest();
        } else {
            return;
        }

        $test->run(Settings::fromSectionID(\CONF_EMAILS));
        $result = \ob_get_clean();
        $this->getSmarty()->assign('testResult', $result);
    }

    public function editConfig(
        bool $isSearch,
        string $search,
        SectionInterface $section,
        SectionFactory $sectionFactory,
        int $sectionID
    ): SectionInterface {
        if ($isSearch) {
            $searchInstance = new Search($this->db, $this->getText, $this->settingManager);
            $sections       = $searchInstance->getResultSections($search);
            $this->getSmarty()->assign('cSearch', $searchInstance->getTitle());
            foreach ($sections as $sectionItem) {
                $sectionItem->update($_POST);
            }
        } else {
            $sectionFactory->getSection($sectionID, $this->settingManager)->update($_POST);
        }
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
        $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
        $tagsToFlush = [\CACHING_GROUP_OPTION];
        if (\in_array($sectionID, [\CONF_GLOBAL, \CONF_ARTIKELUEBERSICHT, \CONF_ARTIKELDETAILS], true)) {
            $tagsToFlush[] = \CACHING_GROUP_CORE;
            $tagsToFlush[] = \CACHING_GROUP_ARTICLE;
            $tagsToFlush[] = \CACHING_GROUP_CATEGORY;
        } elseif ($sectionID === \CONF_BOXEN) {
            $tagsToFlush[] = \CACHING_GROUP_BOX;
        }
        $this->cache->flushTags($tagsToFlush);
        Shopsetting::getInstance($this->db, $this->cache)->reset();

        if (Request::pInt('test_emails') === 1) {
            $this->mailServerTest();
        } elseif (Request::pInt('authorize_oauth') === 1) {
            $this->authorizeOauth();
        }

        return $section;
    }

    private function getPermissionForSection(int $setionID): ?string
    {
        return match ($setionID) {
            -1                      => Permissions::SETTINGS_SEARCH_VIEW,
            \CONF_GLOBAL            => Permissions::SETTINGS_GLOBAL_VIEW,
            \CONF_STARTSEITE        => Permissions::SETTINGS_STARTPAGE_VIEW,
            \CONF_EMAILS            => Permissions::SETTINGS_EMAILS_VIEW,
            \CONF_ARTIKELUEBERSICHT => Permissions::SETTINGS_ARTICLEOVERVIEW_VIEW,
            \CONF_ARTIKELDETAILS    => Permissions::SETTINGS_ARTICLEDETAILS_VIEW,
            \CONF_KUNDEN            => Permissions::SETTINGS_CUSTOMERFORM_VIEW,
            \CONF_KAUFABWICKLUNG    => Permissions::SETTINGS_BASKET_VIEW,
            \CONF_BOXEN             => Permissions::SETTINGS_BOXES_VIEW,
            default                 => null,
        };
    }
}
