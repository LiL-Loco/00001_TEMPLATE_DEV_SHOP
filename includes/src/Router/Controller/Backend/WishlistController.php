<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Customer\Customer;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class WishlistController
 * @package JTL\Router\Controller\Backend
 */
class WishlistController extends AbstractBackendController
{
    /**
     * @var string[]
     */
    private static array $settingsIDs = [
        'boxen_wunschzettel_anzahl',
        'boxen_wunschzettel_bilder',
        'global_wunschliste_weiterleitung',
        'global_wunschliste_anzeigen',
        'global_wunschliste_freunde_aktiv',
        'global_wunschliste_max_email',
        'global_wunschliste_artikel_loeschen_nach_kauf'
    ];

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::MODULE_WISHLIST_VIEW);
        $this->getText->loadAdminLocale('pages/wunschliste');

        if (Request::verifyGPCDataInt('einstellungen') === 1) {
            $this->alertService->addSuccess(
                $this->saveAdminSettings(self::$settingsIDs, $_POST, [\CACHING_GROUP_OPTION], true),
                'saveSettings'
            );
        }
        if (Request::gInt('delete') > 0 && Form::validateToken()) {
            Wishlist::delete(Request::gInt('delete'), true);
        }
        $this->handleItems();
        $this->handleProducts();
        $this->handleFriends();
        $this->getAdminSectionSettings(self::$settingsIDs, true);

        return $smarty->assign('route', $this->route)
            ->getResponse('wunschliste.tpl');
    }

    private function handleItems(): void
    {
        $itemCount     = $this->db->getSingleInt(
            'SELECT COUNT(DISTINCT twunschliste.kWunschliste) AS cnt
                 FROM twunschliste
                 JOIN twunschlistepos
                     ON twunschliste.kWunschliste = twunschlistepos.kWunschliste',
            'cnt'
        );
        $posPagination = (new Pagination('pos'))
            ->setItemCount($itemCount)
            ->assemble();

        $wishLists = $this->db->getObjects(
            "SELECT tkunde.kKunde, tkunde.cNachname, tkunde.cVorname, twunschliste.kWunschliste, twunschliste.cName,
                twunschliste.cURLID, DATE_FORMAT(twunschliste.dErstellt, '%d.%m.%Y %H:%i') AS Datum, 
                twunschliste.nOeffentlich, COUNT(twunschlistepos.kWunschliste) AS Anzahl,
                tbesucher.kBesucher AS isOnline
                FROM twunschliste
                JOIN twunschlistepos 
                    ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
                LEFT JOIN tkunde 
                    ON twunschliste.kKunde = tkunde.kKunde
                LEFT JOIN tbesucher
                    ON tbesucher.kKunde=tkunde.kKunde
                GROUP BY twunschliste.kWunschliste
                ORDER BY twunschliste.dErstellt DESC
                LIMIT " . $posPagination->getLimitSQL()
        );
        $service   = Shop::Container()->getPasswordService();
        foreach ($wishLists as $wishList) {
            if ($wishList->kKunde === null) {
                continue;
            }
            $customer            = new Customer((int)$wishList->kKunde, $service, $this->db);
            $wishList->cNachname = $customer->cNachname;
        }

        $this->getSmarty()->assign('oPagiPos', $posPagination)
            ->assign('CWunschliste_arr', $wishLists);
    }

    private function handleProducts(): void
    {
        $productCount      = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM twunschlistepos',
            'cnt'
        );
        $productPagination = (new Pagination('artikel'))
            ->setItemCount($productCount)
            ->assemble();
        $wishListPositions = $this->db->getObjects(
            "SELECT kArtikel, cArtikelName, COUNT(kArtikel) AS Anzahl,
                DATE_FORMAT(dHinzugefuegt, '%d.%m.%Y %H:%i') AS Datum
                FROM twunschlistepos
                GROUP BY kArtikel
                ORDER BY Anzahl DESC
                LIMIT " . $productPagination->getLimitSQL()
        );

        $this->getSmarty()->assign('oPagiArtikel', $productPagination)
            ->assign('CWunschlistePos_arr', $wishListPositions);
    }

    private function handleFriends(): void
    {
        $friends           = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM twunschliste
                JOIN twunschlisteversand 
                    ON twunschliste.kWunschliste = twunschlisteversand.kWunschliste',
            'cnt'
        );
        $friendsPagination = (new Pagination('freunde'))
            ->setItemCount($friends)
            ->assemble();
        $sentWishLists     = $this->db->getObjects(
            "SELECT tkunde.kKunde, tkunde.cNachname, tkunde.cVorname, twunschlisteversand.nAnzahlArtikel, 
                twunschliste.kWunschliste, twunschliste.cName, twunschliste.cURLID, 
                twunschlisteversand.nAnzahlEmpfaenger,
                DATE_FORMAT(twunschlisteversand.dZeit, '%d.%m.%Y  %H:%i') AS Datum
                FROM twunschliste
                JOIN twunschlisteversand 
                    ON twunschliste.kWunschliste = twunschlisteversand.kWunschliste
                LEFT JOIN tkunde 
                    ON twunschliste.kKunde = tkunde.kKunde
                ORDER BY twunschlisteversand.dZeit DESC
                LIMIT " . $friendsPagination->getLimitSQL()
        );
        $service           = Shop::Container()->getPasswordService();
        foreach ($sentWishLists as $wishList) {
            if ($wishList->kKunde === null) {
                continue;
            }
            $customer            = new Customer((int)$wishList->kKunde, $service, $this->db);
            $wishList->cNachname = $customer->cNachname;
        }
        $this->getSmarty()->assign('oPagiFreunde', $friendsPagination)
            ->assign('CWunschlisteVersand_arr', $sentWishLists);
    }
}
