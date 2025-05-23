<?php

declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\Cart\PersistentCart;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Kupon;
use JTL\Customer\CustomerGroup;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Form;
use JTL\Helpers\Order;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Session\Frontend;
use JTL\Shipping\DomainObjects\ShippingCartPositionDTO;
use JTL\Shipping\DomainObjects\ShippingDTO;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class CartController
 * @package JTL\Router\Controller
 */
class CartController extends PageController
{
    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';

        Shop::setPageType(\PAGE_WARENKORB);
        $linkHelper      = Shop::Container()->getLinkService();
        $couponCodeValid = true;
        $cart            = Frontend::getCart();
        $valid           = Form::validateToken();
        if ($valid) {
            CartHelper::applyCartChanges();
        }
        CartHelper::validateCartConfig();
        Order::setUsedBalance();
        $warning = $valid
            ? $this->checkForWarning($cart)
            : '';

        // Kupon nicht mehr verfügbar. Redirect im Bestellabschluss. Fehlerausgabe
        if (isset($_SESSION['checkCouponResult'])) {
            $couponCodeValid = false;
            $couponError     = $_SESSION['checkCouponResult'];
            unset($_SESSION['checkCouponResult']);
            $this->smarty->assign('cKuponfehler', $couponError['ungueltig']);
        }
        if (($msg = $this->checkErrors($cart)) !== null) {
            $warning = $msg;
        }
        $this->currentLink  = $linkHelper->getSpecialPage(\LINKTYP_WARENKORB);
        $this->canonicalURL = $this->currentLink->getURL();
        $uploads            = Upload::gibWarenkorbUploads($cart);
        $maxSize            = Upload::uploadMax();
        // alerts
        if (($quickBuyNote = CartHelper::checkQuickBuy()) !== '') {
            $this->alertService->addInfo($quickBuyNote, 'quickBuyNote');
        }
        if (!empty($_SESSION['Warenkorbhinweise'])) {
            foreach ($_SESSION['Warenkorbhinweise'] as $key => $cartNotice) {
                $this->alertService->addWarning($cartNotice, 'cartNotice' . $key);
            }
            unset($_SESSION['Warenkorbhinweise']);
        }
        if ($warning !== '') {
            $this->alertService->addDanger($warning, 'cartWarning', ['id' => 'msgWarning']);
        }
        if (($orderAmountStock = CartHelper::checkOrderAmountAndStock($this->config)) !== '') {
            $this->alertService->addWarning($orderAmountStock, 'orderAmountStock');
        }

        $freeGiftProductsArray = Shop::Container()->getFreeGiftService()
            ->getFreeGifts($this->config, $this->customerGroupID)
            ->setStillMissingAmounts(
                Frontend::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true),
            );

        $this->smarty->assign('MsgWarning', $warning)
            ->assign('nMaxUploadSize', $maxSize)
            ->assign('cMaxUploadSize', Upload::formatGroesse($maxSize))
            ->assign('oUploadSchema_arr', $uploads)
            ->assign('Link', $this->currentLink)
            ->assignDeprecated(
                'laender',
                Shop::Container()->getCountryService()->getCountrylist(),
                '5.5.0',
            )
            ->assign('KuponMoeglich', Kupon::couponsAvailable($this->db))
            ->assign('currentCoupon', Shop::Lang()->get('currentCoupon', 'checkout'))
            ->assign(
                'currentCouponName',
                (!empty($_SESSION['Kupon']->translationList)
                    ? $_SESSION['Kupon']->translationList
                    : null)
            )
            ->assign(
                'currentShippingCouponName',
                (!empty($_SESSION['oVersandfreiKupon']->translationList)
                    ? $_SESSION['oVersandfreiKupon']->translationList
                    : null)
            )
            ->assign('xselling', CartHelper::getXSelling())
            ->assignDeprecated('oArtikelGeschenk_arr', $freeGiftProductsArray->getProductArray(), '5.4.0')
            ->assign('freeGifts', $freeGiftProductsArray)
            ->assign('KuponcodeUngueltig', !$couponCodeValid)
            ->assign('Warenkorb', $cart);

        $this->preRender();

        \executeHook(\HOOK_WARENKORB_PAGE);

        return $this->smarty->getResponse('basket/index.tpl');
    }

    protected function checkErrors(Cart $cart): ?string
    {
        if (($res = Request::getInt('fillOut', -1)) < 0) {
            return null;
        }
        $warning = null;
        $mbw     = Frontend::getCustomerGroup()->getAttribute(\KNDGRP_ATTRIBUT_MINDESTBESTELLWERT);
        if ($res === 9 && $mbw > 0 && $cart->gibGesamtsummeWarenOhne([\C_WARENKORBPOS_TYP_GUTSCHEIN], true) < $mbw) {
            $warning = Shop::Lang()->get('minordernotreached', 'checkout')
                . ' ' . Preise::getLocalizedPriceString($mbw);
        } elseif ($res === 8) {
            $warning = Shop::Lang()->get('orderNotPossibleNow', 'checkout');
        } elseif ($res === 3) {
            $warning = Shop::Lang()->get('yourbasketisempty', 'checkout');
        } elseif ($res === 10) {
            $warning = Shop::Lang()->get('missingProducts', 'checkout');
        } elseif ($res === \UPLOAD_ERROR_NEED_UPLOAD) {
            $warning = Shop::Lang()->get('missingFilesUpload', 'checkout');
        } elseif ($res === \UPLOAD_CHECK_NEED_UPLOAD) {
            Upload::clearUploadCheckNeeded();
        }

        return $warning;
    }

    protected function checkGifts(Cart $cart): string
    {
        // @todo $giftID could also be $_POST['a'] when $_POST['inWarenkorb'] is present
        // But what when quantity is more than 1? If its a GG, never add more than 1 unit to basket. But how to handle
        // the remaining quantity of the product? Just add it as a normal product?
        if (
            !isset($_POST['gratis_geschenk'], $_POST['gratisgeschenk'])
            || (int)$_POST['gratis_geschenk'] !== 1
        ) {
            return '';
        }

        $giftID = (int)$_POST['gratisgeschenk'];
        $gift   = Shop::Container()->getFreeGiftService()->getFreeGiftProduct(
            productID: $giftID,
            basketSum: $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true),
            customerGroupID: $this->customerGroupID,
        );
        if ($gift === null) {
            return Shop::Lang()->get('freegiftsNotAvailable', 'errorMessages');
        }

        \executeHook(\HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
        $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_GRATISGESCHENK)
            ->fuegeEin($giftID, 1, [], \C_WARENKORBPOS_TYP_GRATISGESCHENK);

        PersistentCart::getInstance(Frontend::getCustomer()->getID())
            ->check($giftID, 1, [], '', 0, \C_WARENKORBPOS_TYP_GRATISGESCHENK);

        return '';
    }

    protected function checkCoupons(Cart $cart): void
    {
        if (
            Request::postVar('Kuponcode', '') === ''
            || $cart->gibAnzahlArtikelExt([\C_WARENKORBPOS_TYP_ARTIKEL]) < 1
        ) {
            // Kupon darf nicht im leeren Warenkorb eingelöst werden
            return;
        }
        $coupon            = new Kupon(0, $this->db);
        $coupon            = $coupon->getByCode(Request::pString('Kuponcode'));
        $invalidCouponCode = 11;
        if ($coupon !== false && $coupon->kKupon > 0) {
            $couponError       = $coupon->check();
            $check             = Form::hasNoMissingData($couponError);
            $invalidCouponCode = 0;
            \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI, [
                'error'        => &$couponError,
                'nReturnValue' => &$check
            ]);
            if ($check) {
                if ($coupon->cKuponTyp === Kupon::TYPE_STANDARD) {
                    $coupon->accept();
                    \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
                } elseif (
                    !empty($coupon->kKupon)
                    && $coupon->cKuponTyp === Kupon::TYPE_SHIPPING
                ) {
                    $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON);
                    $_SESSION['oVersandfreiKupon'] = $coupon;
                    $this->alertService->addSuccess(
                        message: Shop::Lang()->get('couponSucc1')
                        . ' '
                        . \trim(
                            string: \str_replace(
                                search: ';',
                                replace: ', ',
                                subject: (string)$coupon->cLieferlaender
                            ),
                            characters: ', '
                        ),
                        key: 'shippingFreeSuccess',
                    );
                }
            }
        }

        $this->smarty->assign(
            'invalidCouponCode',
            Kupon::mapCouponErrorMessage($couponError['ungueltig'] ?? $invalidCouponCode)
        );
    }

    private function checkForWarning(Cart $cart): string
    {
        $warning = '';
        if (isset($_POST['versandrechnerBTN']) && (empty($_POST['land']) || empty($_POST['plz']))) {
            $warning = Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages');
        } elseif (isset($_POST['land'], $_POST['plz'])) {
            $warning         = Shop::Lang()->get('noDispatchAvailable');
            $shippingMethods = $this->getShippingService()->getPossibleShippingMethods(
                Frontend::getCustomer(),
                new CustomerGroup($this->customerGroupID),
                $_POST['land'],
                Frontend::getCurrency(),
                $_POST['plz'],
                Frontend::getCart()->PositionenArr,
            );

            if (empty($shippingMethods) === false) {
                $warning = '';
                $this->smarty
                    ->assign(
                        'ArtikelabhaengigeVersandarten',
                        \array_map(
                            static function (ShippingCartPositionDTO $dto): stdClass {
                                return $dto->toLegacyObject();
                            },
                            $shippingMethods[0]->customShippingCosts
                        )
                    )
                    ->assign(
                        'Versandarten',
                        \array_map(
                            static function (ShippingDTO $dto): stdClass {
                                return $dto->toLegacyObject();
                            },
                            $shippingMethods
                        )
                    )
                    ->assign('Versandland', LanguageHelper::getCountryCodeByCountryName($_POST['land']))
                    ->assign('VersandPLZ', Text::filterXSS($_POST['plz']));
            }
            \executeHook(\HOOK_WARENKORB_PAGE_ERMITTLEVERSANDKOSTEN);
        }
        $this->checkCoupons($cart);

        return $warning !== ''
            ? $warning
            : $this->checkGifts($cart);
    }
}
