<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Alert\Alert;
use JTL\Backend\AdminFavorite;
use JTL\Backend\AdminIO;
use JTL\Backend\JSONAPI;
use JTL\Backend\Notification;
use JTL\Backend\Permissions;
use JTL\Backend\Settings\Manager as SettingsManager;
use JTL\Backend\ShippingClassWizard\Wizard as ShippingClassWizard;
use JTL\Backend\Wizard\WizardIO;
use JTL\Catalog\Currency;
use JTL\Checkout\ShippingSurcharge;
use JTL\Checkout\ShippingSurchargeArea;
use JTL\Checkout\Versandart;
use JTL\Checkout\ZipValidator;
use JTL\Customer\Import;
use JTL\DB\Migration\Check;
use JTL\DB\Migration\Info;
use JTL\DB\Migration\InnoDB;
use JTL\DB\Migration\Structure;
use JTL\Export\Validator;
use JTL\Filter\States\BaseSearchQuery;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\IO\IOError;
use JTL\IO\IOResponse;
use JTL\Jtllog;
use JTL\Language\LanguageHelper;
use JTL\Link\Admin\LinkAdmin;
use JTL\Mail\Validator\SyntaxChecker;
use JTL\Media\Manager;
use JTL\Plugin\Helper;
use JTL\Redirect\Helpers\Normalizer;
use JTL\Redirect\Repositories\RedirectRefererRepository;
use JTL\Redirect\Repositories\RedirectRepository;
use JTL\Redirect\Services\RedirectService;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use JTL\TwoFA\BackendTwoFA;
use JTL\Update\UpdateIO;
use JTL\Widgets\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SmartyException;
use stdClass;

/**
 * Class IOController
 * @package JTL\Router\Controller\Backend
 */
class IOController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        \ob_start();
        $io = AdminIO::getInstance();
        if (!$this->account->getIsAuthenticated()) {
            return $io->getResponse(new IOError('Not authenticated as admin.', 401));
        }
        if (!Form::validateToken()) {
            return $io->getResponse(new IOError('CSRF validation failed.', 403));
        }
        $io->setAccount($this->account);
        try {
            Shop::Container()->getOPC()->registerAdminIOFunctions($io);
            Shop::Container()->getOPCPageService()->registerAdminIOFunctions($io);
            $this->registerJsonAPI($io);
            $this->registerSearch($io);
            $this->registerRedirects($io);
            $this->registerMigrations($io);
            $this->registerImport($io);
            $this->registerSyntaxCheck($io);
            $this->registerWidgets($io);
            $this->registerImages($io);
            $this->registerUpdateIO($io);
            $this->registerWizardIO($io);
            $this->registerSettings($io);
            $this->registerConversions($io);
            $this->registerFavs($io);
            $this->registerSurcharges($io);
            $this->registerTwoFA($io);
            $this->registerMisc($io);
        } catch (Exception $e) {
            return $io->getResponse(new IOError($e->getMessage(), $e->getCode()));
        }
        $req = $_REQUEST['io'];

        \executeHook(\HOOK_IO_HANDLE_REQUEST_ADMIN, [
            'io'      => &$io,
            'request' => &$req
        ]);

        if (\ob_get_length() > 0) {
            \ob_end_clean();
        }

        return $io->getResponse($io->handleRequest($req));
    }

    private function registerRedirects(AdminIO $io): void
    {
        $redirectService = new RedirectService(
            new RedirectRepository($this->db),
            new RedirectRefererRepository($this->db),
            new Normalizer()
        );
        $io->register('redirectCheckAvailability', $redirectService->checkAvailability(...))
            ->register('updateRedirectState', $this->updateRedirectState(...), null, Permissions::REDIRECT_VIEW);
    }

    private function registerSearch(AdminIO $io): void
    {
        $searchController = new SearchController(
            $this->db,
            $this->cache,
            $this->alertService,
            $this->account,
            $this->getText
        );
        $searchController->setSmarty($this->getSmarty());
        $io->register('adminSearch', $searchController->adminSearch(...), null, Permissions::SETTINGS_SEARCH_VIEW)
            ->register(
                'createSearchIndex',
                $this->createSearchIndex(...),
                null,
                Permissions::SETTINGS_ARTICLEOVERVIEW_VIEW
            )
            ->register(
                'clearSearchCache',
                $this->clearSearchCache(...),
                null,
                Permissions::SETTINGS_ARTICLEOVERVIEW_VIEW
            );
    }

    private function registerMigrations(AdminIO $io): void
    {
        $info   = new Info($this->db);
        $check  = new Check($this->db);
        $struct = new Structure($this->db, $this->cache, $info);
        $innodb = new InnoDB($this->db, $info, $check, $struct, $this->getText);
        $io->register(
            'migrateToInnoDB_utf8',
            $innodb->doMigrateToInnoDBUTF8(...),
            null,
            Permissions::DBCHECK_VIEW
        );
    }

    private function registerImport(AdminIO $io): void
    {
        $customerImport = new Import($this->db);
        $io->register(
            'notifyImportedCustomers',
            [$customerImport, 'notifyCustomers'],
            null,
            Permissions::IMPORT_CUSTOMER_VIEW
        );
    }

    private function registerJsonAPI(AdminIO $io): void
    {
        $jsonApi = JSONAPI::getInstance($this->db, $this->cache);
        $io->register('getPagesByLinkGroup', $jsonApi->getPagesByLinkGroup(...))
            ->register('getPages', $jsonApi->getPages(...))
            ->register('getCategories', $jsonApi->getCategories(...))
            ->register('getProducts', $jsonApi->getProducts(...))
            ->register('getManufacturers', $jsonApi->getManufacturers(...))
            ->register('getCustomers', $jsonApi->getCustomers(...))
            ->register('getSeos', $jsonApi->getSeos(...))
            ->register('getAttributes', $jsonApi->getAttributes(...));
    }

    private function registerSyntaxCheck(AdminIO $io): void
    {
        $syntaxChecker = new Validator(
            $this->db,
            $this->cache,
            $this->getText,
            $this->getSmarty(),
            Shop::Container()->getLogService()
        );
        $io->register(
            'exportformatSyntaxCheck',
            $syntaxChecker->ioCheckSyntax(...),
            null,
            Permissions::EXPORT_FORMATS_VIEW
        )
            ->register('testExport', $syntaxChecker->preview(...), null, Permissions::EXPORT_FORMATS_VIEW);
    }

    private function registerWidgets(AdminIO $io): void
    {
        $widgets = new Controller($this->db, $this->cache, $this->getText, $this->getSmarty(), $this->account);
        $io->register('setWidgetPosition', $widgets->setWidgetPosition(...), null, Permissions::DASHBOARD_VIEW)
            ->register('closeWidget', $widgets->closeWidget(...), null, Permissions::DASHBOARD_VIEW)
            ->register('addWidget', $widgets->addWidget(...), null, Permissions::DASHBOARD_VIEW)
            ->register('expandWidget', $widgets->expandWidget(...), null, Permissions::DASHBOARD_VIEW)
            ->register(
                'getAvailableWidgets',
                $widgets->getAvailableWidgetsIO(...),
                null,
                Permissions::DASHBOARD_VIEW
            )
            ->register('getRemoteData', $widgets->getRemoteDataIO(...), null, Permissions::DASHBOARD_VIEW)
            ->register('getShopInfo', $widgets->getShopInfoIO(...), null, Permissions::DASHBOARD_VIEW);
    }

    private function registerImages(AdminIO $io): void
    {
        $images = new Manager($this->db, $this->getText);
        $io->register('loadStats', $images->loadStats(...), null, Permissions::DISPLAY_IMAGES_VIEW)
            ->register('cleanupStorage', $images->cleanupStorage(...), null, Permissions::DISPLAY_IMAGES_VIEW)
            ->register('clearImageCache', $images->clearImageCache(...), null, Permissions::DISPLAY_IMAGES_VIEW)
            ->register(
                'generateImageCache',
                $images->generateImageCache(...),
                null,
                Permissions::DISPLAY_IMAGES_VIEW
            );
    }

    private function registerUpdateIO(AdminIO $io): void
    {
        $updateIO = new UpdateIO($this->db, $this->getText);
        $io->register('dbUpdateIO', $updateIO->update(...), null, Permissions::SHOP_UPDATE_VIEW)
            ->register('dbupdaterBackup', $updateIO->backup(...), null, Permissions::SHOP_UPDATE_VIEW)
            ->register('dbupdaterDownload', $updateIO->download(...), null, Permissions::SHOP_UPDATE_VIEW)
            ->register('dbupdaterStatusTpl', $updateIO->getStatus(...), null, Permissions::SHOP_UPDATE_VIEW)
            ->register('dbupdaterMigration', $updateIO->executeMigration(...), null, Permissions::SHOP_UPDATE_VIEW);
    }

    private function registerWizardIO(AdminIO $io): void
    {
        $wizardIO = new WizardIO($this->db, $this->cache, $this->alertService, $this->getText);
        $io->register('finishWizard', $wizardIO->answerQuestions(...), null, Permissions::WIZARD_VIEW)
            ->register('validateStepWizard', $wizardIO->validateStep(...), null, Permissions::WIZARD_VIEW)
            ->register('wizardShippingMethod', $this->wizardShippingMethodRender(...))
            ->register('wizardShippingMethodCreate', $this->wizardShippingMethodCreate(...));
    }

    private function registerSettings(AdminIO $io): void
    {
        $settings = new SettingsManager(
            $this->db,
            $this->getSmarty(),
            $this->account,
            $this->getText,
            $this->alertService
        );
        $io->register('getSettingLog', $settings->getSettingLog(...));
    }

    private function registerConversions(AdminIO $io): void
    {
        $io->register('getCurrencyConversion', $this->getCurrencyConversionIO(...))
            ->register('setCurrencyConversionTooltip', $this->setCurrencyConversionTooltipIO(...));
    }

    private function registerFavs(AdminIO $io): void
    {
        $io->register('addFav', $this->addFav(...))
            ->register('reloadFavs', $this->reloadFavs(...));
    }

    private function registerSurcharges(AdminIO $io): void
    {
        $io->register(
            'saveShippingSurcharge',
            $this->saveShippingSurcharge(...),
            null,
            Permissions::ORDER_SHIPMENT_VIEW
        )
            ->register(
                'deleteShippingSurcharge',
                $this->deleteShippingSurcharge(...),
                null,
                Permissions::ORDER_SHIPMENT_VIEW
            )
            ->register(
                'deleteShippingSurchargeZIP',
                $this->deleteShippingSurchargeZIP(...),
                null,
                Permissions::ORDER_SHIPMENT_VIEW
            )
            ->register(
                'createShippingSurchargeZIP',
                $this->createShippingSurchargeZIP(...),
                null,
                Permissions::ORDER_SHIPMENT_VIEW
            )
            ->register(
                'getShippingSurcharge',
                $this->getShippingSurcharge(...),
                null,
                Permissions::ORDER_SHIPMENT_VIEW
            );
    }

    private function registerTwoFA(AdminIO $io): void
    {
        $io->register('getNewTwoFA', BackendTwoFA::getNewTwoFA(...))
            ->register('genTwoFAEmergencyCodes', BackendTwoFA::genTwoFAEmergencyCodes(...));
    }

    private function registerMisc(AdminIO $io): void
    {
        $io->register('isDuplicateSpecialLink', LinkAdmin::isDuplicateSpecialLink(...))
            ->register('getNotifyDropIO', Notification::getNotifyDropIO(...))
            ->register('truncateJtllog', Jtllog::truncateLog(...), null, Permissions::DASHBOARD_VIEW)
            ->register('getRandomPassword', $this->getRandomPassword(...), null, Permissions::ACCOUNT_VIEW)
            ->register(
                'saveBannerAreas',
                BannerController::saveAreasIO(...),
                null,
                Permissions::DISPLAY_BANNER_VIEW
            )
            ->register(
                'mailvorlageSyntaxCheck',
                SyntaxChecker::ioCheckSyntax(...),
                null,
                Permissions::CONTENT_EMAIL_TEMPLATE_VIEW
            )
            ->register('notificationAction', Notification::ioNotification(...))
            ->register('pluginTestLoading', Helper::ioTestLoading(...))
            ->register('setTheme', $this->setTheme(...));
    }

    public function getCurrencyConversionIO(
        float|string $netPrice,
        float|string $grossPrice,
        string $targetID
    ): IOResponse {
        $response = new IOResponse();
        $response->assignDom($targetID, 'innerHTML', Currency::getCurrencyConversion($netPrice, $grossPrice));

        return $response;
    }

    public function setCurrencyConversionTooltipIO(
        float|int $netPrice,
        float|int $grossPrice
    ): IOResponse {
        $response = new IOResponse();
        $response->assignVar('originalTitle', Currency::getCurrencyConversion($netPrice, $grossPrice));

        return $response;
    }

    /**
     * @return array{title: string, url: string}|IOError
     */
    public function addFav(string $title, string $url): array|IOError
    {
        $success = false;
        $adminID = $this->account->getID();
        if (!empty($title) && !empty($url)) {
            $success = (new AdminFavorite($this->db))->add($adminID, $title, $url);
        }

        if ($success) {
            $result = [
                'title' => $title,
                'url'   => $url
            ];
        } else {
            $result = new IOError('Unauthorized', 401);
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    public function reloadFavs(): array
    {
        $tpl = $this->getSmarty()->assign('favorites', $this->account->favorites())
            ->fetch('tpl_inc/favs_drop.tpl');

        return ['tpl' => $tpl];
    }

    /**
     * @return IOResponse
     * @throws Exception
     */
    public function getRandomPassword(): IOResponse
    {
        $response = new IOResponse();
        $password = Shop::Container()->getPasswordService()->generate(\PASSWORD_DEFAULT_LENGTH);
        $response->assignDom('cPass', 'value', $password);

        return $response;
    }

    /**
     * @param string $idx
     * @param string $create
     * @return array|IOError
     */
    public function createSearchIndex(string $idx, string $create): array|IOError
    {
        $this->getText->loadAdminLocale('pages/sucheinstellungen');
        $idx      = \mb_convert_case(Text::xssClean($idx), \MB_CASE_LOWER);
        $notice   = '';
        $errorMsg = '';
        if (!\in_array($idx, ['tartikel', 'tartikelsprache'], true)) {
            return new IOError(\__('errorIndexInvalid'), 403);
        }
        $keyName = 'idx_' . $idx . '_fulltext';
        try {
            if (
                $this->db->getSingleObject(
                    'SHOW INDEX FROM ' . $idx . ' WHERE KEY_NAME = :keyName',
                    ['keyName' => $keyName]
                )
            ) {
                $this->db->query('ALTER TABLE ' . $idx . ' DROP KEY ' . $keyName);
            }
        } catch (Exception) {
            // Fehler beim Index löschen ignorieren
        }

        if ($create !== 'Y') {
            return ['hinweis' => \sprintf(\__('successIndexDelete'), $idx)];
        }
        $searchRows = \array_map(static function ($item) {
            $items = \explode('.', $item, 2);

            return $items[1];
        }, BaseSearchQuery::getSearchRows());

        switch ($idx) {
            case 'tartikel':
                $rows = \array_intersect(
                    $searchRows,
                    [
                        'cName',
                        'cSeo',
                        'cSuchbegriffe',
                        'cArtNr',
                        'cKurzBeschreibung',
                        'cBeschreibung',
                        'cBarcode',
                        'cISBN',
                        'cHAN',
                        'cAnmerkung'
                    ]
                );
                break;
            case 'tartikelsprache':
                $rows = \array_intersect($searchRows, ['cName', 'cSeo', 'cKurzBeschreibung', 'cBeschreibung']);
                break;
            default:
                return new IOError(\__('errorIndexInvalid'), 403);
        }

        /** @noinspection SqlWithoutWhere */
        $this->db->query('UPDATE tsuchcache SET dGueltigBis = DATE_ADD(NOW(), INTERVAL 10 MINUTE)');
        $res = $this->db->getPDOStatement(
            'ALTER TABLE ' . $idx . ' ADD FULLTEXT KEY idx_' . $idx . '_fulltext (' . \implode(', ', $rows) . ')'
        );

        if (!isset($res->queryString)) {
            $errorMsg     = \__('errorIndexNotCreatable');
            $shopSettings = Shopsetting::getInstance($this->db, $this->cache);
            $settings     = $shopSettings[Shopsetting::mapSettingName(\CONF_ARTIKELUEBERSICHT)];

            if ($settings['suche_fulltext'] !== 'N') {
                $settings['suche_fulltext'] = 'N';
                $this->db->update(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [\CONF_ARTIKELUEBERSICHT, 'suche_fulltext'],
                    (object)['cWert' => 'N']
                );
                $this->cache->flushTags([
                    \CACHING_GROUP_OPTION,
                    \CACHING_GROUP_CORE,
                    \CACHING_GROUP_ARTICLE,
                    \CACHING_GROUP_CATEGORY
                ]);
                $shopSettings->reset();
            }
        } else {
            $notice = \sprintf(\__('successIndexCreate'), $idx);
        }

        return $errorMsg !== '' ? new IOError($errorMsg) : ['hinweis' => $notice];
    }

    /**
     * @return array<string, string>
     * @noinspection SqlWithoutWhere
     */
    public function clearSearchCache(): array
    {
        $this->db->query('DELETE FROM tsuchcachetreffer');
        $this->db->query('DELETE FROM tsuchcache');
        $this->getText->loadAdminLocale('pages/sucheinstellungen');

        return ['hinweis' => \__('successSearchCacheDelete')];
    }

    public function updateRedirectState(int $redirectID): bool
    {
        $redirectService = new RedirectService(
            new RedirectRepository($this->db),
            new RedirectRefererRepository($this->db),
            new Normalizer()
        );
        $url             = $this->db->select('tredirect', 'kRedirect', $redirectID)->cToUrl ?? '';
        $available       = $url !== '' && $redirectService->checkAvailability($url) ? 'y' : 'n';
        $this->db->update('tredirect', 'kRedirect', $redirectID, (object)['cAvailable' => $available]);

        return $available === 'y';
    }

    /**
     * @throws SmartyException
     */
    public function getShippingSurcharge(int $id): stdClass
    {
        $this->getText->loadAdminLocale('pages/versandarten');
        $result       = new stdClass();
        $result->body = $this->getSmarty()->assign('sprachen', LanguageHelper::getAllLanguages(0, true))
            ->assign('surchargeNew', new ShippingSurcharge($id))
            ->assign('surchargeID', $id)
            ->fetch('snippets/zuschlagliste_form.tpl');

        return $result;
    }

    /**
     * @param array $data
     * @return stdClass
     * @throws SmartyException
     */
    public function saveShippingSurcharge(array $data): stdClass
    {
        $this->getText->loadAdminLocale('pages/versandarten');
        $post         = [];
        $surchargeTMP = null;
        foreach ($data as $item) {
            $post[$item['name']] = $item['value'];
        }
        $surcharge = (float)\str_replace(',', '.', $post['fZuschlag']);

        if (!$post['cName']) {
            $this->alertService->addError(\__('errorListNameMissing'), 'errorListNameMissing');
        }
        if (empty($surcharge)) {
            $this->alertService->addError(\__('errorListPriceMissing'), 'errorListPriceMissing');
        }
        if (!$this->alertService->alertTypeExists(Alert::TYPE_ERROR)) {
            if (empty($post['kVersandzuschlag'])) {
                $surchargeTMP = (new ShippingSurcharge())
                    ->setISO($post['cISO'])
                    ->setSurcharge($surcharge)
                    ->setShippingMethod((int)$post['kVersandart'])
                    ->setTitle($post['cName']);
            } else {
                $surchargeTMP = (new ShippingSurcharge((int)$post['kVersandzuschlag']))
                    ->setTitle($post['cName'])
                    ->setSurcharge($surcharge);
            }
            foreach (LanguageHelper::getAllLanguages(0, true) as $lang) {
                $idx = 'cName_' . $lang->getCode();
                if (isset($post[$idx])) {
                    $surchargeTMP->setName($post[$idx] ?: $post['cName'], $lang->getId());
                }
            }
            $surchargeTMP->save();
            $surchargeTMP = new ShippingSurcharge($surchargeTMP->getID());
        }
        $message = $this->getSmarty()->assign('alertList', $this->alertService)
            ->fetch('snippets/alert_list.tpl');

        $this->cache->flushTags([
            \CACHING_GROUP_OBJECT,
            \CACHING_GROUP_OPTION,
            \CACHING_GROUP_ARTICLE
        ]);

        return (object)[
            'title'          => $surchargeTMP?->getTitle() ?? '',
            'priceLocalized' => $surchargeTMP?->getPriceLocalized() ?? '',
            'id'             => $surchargeTMP?->getID() ?? '',
            'reload'         => empty($post['kVersandzuschlag']),
            'message'        => $message,
            'error'          => $this->alertService->alertTypeExists(Alert::TYPE_ERROR)
        ];
    }

    public function deleteShippingSurcharge(int $surchargeID): stdClass
    {
        $this->db->queryPrepared(
            'DELETE tversandzuschlag, tversandzuschlagsprache, tversandzuschlagplz
                FROM tversandzuschlag
                LEFT JOIN tversandzuschlagsprache USING(kVersandzuschlag)
                LEFT JOIN tversandzuschlagplz USING(kVersandzuschlag)
                WHERE tversandzuschlag.kVersandzuschlag = :surchargeID',
            ['surchargeID' => $surchargeID]
        );
        $this->cache->flushTags([
            \CACHING_GROUP_OBJECT,
            \CACHING_GROUP_OPTION,
            \CACHING_GROUP_ARTICLE
        ]);

        return (object)['surchargeID' => $surchargeID];
    }

    public function deleteShippingSurchargeZIP(int $surchargeID, string $ZIP): stdClass
    {
        $partsZIP = \explode('-', $ZIP);
        if (\count($partsZIP) === 1) {
            $this->db->queryPrepared(
                'DELETE 
                    FROM tversandzuschlagplz
                    WHERE kVersandzuschlag = :surchargeID
                      AND cPLZ = :ZIP',
                [
                    'surchargeID' => $surchargeID,
                    'ZIP'         => $partsZIP[0]
                ]
            );
        } elseif (\count($partsZIP) === 2) {
            $this->db->queryPrepared(
                'DELETE 
                    FROM tversandzuschlagplz
                    WHERE kVersandzuschlag = :surchargeID
                      AND cPLZab = :ZIPFrom
                      AND cPLZbis = :ZIPTo',
                [
                    'surchargeID' => $surchargeID,
                    'ZIPFrom'     => $partsZIP[0],
                    'ZIPTo'       => $partsZIP[1]
                ]
            );
        }
        $this->cache->flushTags([
            \CACHING_GROUP_OBJECT,
            \CACHING_GROUP_OPTION,
            \CACHING_GROUP_ARTICLE
        ]);

        return (object)['surchargeID' => $surchargeID, 'ZIP' => $ZIP];
    }

    /**
     * @param array $data
     * @return stdClass
     * @throws SmartyException
     */
    public function createShippingSurchargeZIP(array $data): stdClass
    {
        $this->getText->loadAdminLocale('pages/versandarten');

        $post = [];
        foreach ($data as $item) {
            $post[$item['name']] = $item['value'];
        }
        $surcharge      = new ShippingSurcharge((int)$post['kVersandzuschlag']);
        $shippingMethod = new Versandart($surcharge->getShippingMethod());
        $zipValidator   = new ZipValidator($surcharge->getISO());
        $surchargeZip   = new stdClass();

        $surchargeZip->kVersandzuschlag = $surcharge->getID();
        $surchargeZip->cPLZ             = '';
        $surchargeZip->cPLZAb           = '';
        $surchargeZip->cPLZBis          = '';
        $area                           = null;

        if (!empty($post['cPLZ'])) {
            $surchargeZip->cPLZ = $zipValidator->validateZip($post['cPLZ']);
        } elseif (!empty($post['cPLZAb']) && !empty($post['cPLZBis'])) {
            $area = new ShippingSurchargeArea($post['cPLZAb'], $post['cPLZBis']);
            if ($area->getZIPFrom() === $area->getZIPTo()) {
                $surchargeZip->cPLZ = $zipValidator->validateZip($area->getZIPFrom());
            } else {
                $surchargeZip->cPLZAb  = $zipValidator->validateZip($area->getZIPFrom());
                $surchargeZip->cPLZBis = $zipValidator->validateZip($area->getZIPTo());
            }
        }
        /** @var ?ShippingSurcharge $zipMatchSurcharge */
        $zipMatchSurcharge = $shippingMethod->getShippingSurchargesForCountry($surcharge->getISO())
            ->first(static function (ShippingSurcharge $surchargeTMP) use ($surchargeZip): bool {
                return ($surchargeTMP->hasZIPCode($surchargeZip->cPLZ)
                    || $surchargeTMP->hasZIPCode($surchargeZip->cPLZAb)
                    || $surchargeTMP->hasZIPCode($surchargeZip->cPLZBis)
                    || $surchargeTMP->areaOverlapsWithZIPCode($surchargeZip->cPLZAb, $surchargeZip->cPLZBis)
                );
            });
        if ($area !== null && !$area->lettersMatch()) {
            $this->alertService->addError(\__('errorZIPsDoNotMatch'), 'errorZIPsDoNotMatch');
        } elseif (empty($surchargeZip->cPLZ) && empty($surchargeZip->cPLZAb)) {
            $error = $zipValidator->getError();
            if ($error !== '') {
                $this->alertService->addError($error, 'errorZIPValidator');
            } else {
                $this->alertService->addError(\__('errorZIPMissing'), 'errorZIPMissing');
            }
        } elseif ($zipMatchSurcharge !== null) {
            $this->alertService->addError(
                \sprintf(
                    isset($surchargeZip->cPLZ) ? \__('errorZIPOverlap') : \__('errorZIPAreaOverlap'),
                    $surchargeZip->cPLZ ?? $surchargeZip->cPLZAb . ' - ' . $surchargeZip->cPLZBis,
                    $zipMatchSurcharge->getTitle()
                ),
                'errorZIPOverlap'
            );
        } elseif ($this->db->insert('tversandzuschlagplz', $surchargeZip)) {
            $this->alertService->addSuccess(\__('successZIPAdd'), 'successZIPAdd');
        }
        $this->cache->flushTags([
            \CACHING_GROUP_OBJECT,
            \CACHING_GROUP_OPTION,
            \CACHING_GROUP_ARTICLE
        ]);

        $message = $this->getSmarty()->assign('alertList', $this->alertService)
            ->fetch('snippets/alert_list.tpl');
        $badges  = $this->getSmarty()->assign('surcharge', new ShippingSurcharge($surcharge->getID()))
            ->fetch('snippets/zuschlagliste_plz_badges.tpl');

        return (object)['message' => $message, 'badges' => $badges, 'surchargeID' => $surcharge->getID()];
    }

    /**
     * @since 5.3.0
     */
    public function setTheme(string $theme): stdClass
    {
        if (!\in_array($theme, ['light', 'dark', 'auto'], true)) {
            return (object)['theme' => ''];
        }
        $_SESSION['adminTheme'] = $theme;
        if (
            $this->db->update(
                'tadminlogin',
                'kAdminlogin',
                $this->account->getID(),
                (object)['theme' => $theme]
            ) >= 0
        ) {
            return (object)['theme' => $theme];
        }
        return (object)['theme' => ''];
    }

    private function getShippingMethodsController(): ShippingMethodsController
    {
        $controller = new ShippingMethodsController(
            $this->getDB(),
            $this->getCache(),
            $this->getAlertService(),
            Shop::Container()->getAdminAccount(),
            $this->getGetText()
        );
        $controller->init();

        return $controller;
    }

    public function wizardShippingMethodRender(
        int $id,
        string $shippingClassIds,
        string $definition,
        bool $suppressWarning = false
    ): string|IOError {
        $this->getGetText()->loadAdminLocale('pages/versandarten');
        $this->getGetText()->loadAdminLocale('pages/shippingclass_wizard');

        return ShippingClassWizard::instance(
            $this->getSmarty(),
            $this->getShippingMethodsController()
        )->ioRender($id, $shippingClassIds, $definition, $suppressWarning);
    }

    public function wizardShippingMethodCreate(string $formData): stdClass|IOError
    {
        $this->getGetText()->loadAdminLocale('pages/versandarten');
        $this->getGetText()->loadAdminLocale('pages/shippingclass_wizard');

        \parse_str($formData, $data);
        $wizardData = Text::filterXSS($data['wizard']);
        if (!Form::validateToken() || Request::postVar('jtl_token') !== $data['jtl_token']) {
            return new IOError(\__('invalidToken'));
        }

        return ShippingClassWizard::instance(
            $this->getSmarty(),
            $this->getShippingMethodsController()
        )->ioCalculateMethods($wizardData);
    }
}
