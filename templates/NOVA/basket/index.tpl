{block name='basket-index'}
    {block name='basket-index-include-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='basket-index-content'}
        {get_static_route id='warenkorb.php' assign='cartURL'}
        {container fluid=$Link->getIsFluid() class="basket {if $Einstellungen.template.theme.left_sidebar === 'Y' && $boxesLeftActive}container-plus-sidebar{/if}"}
            {row}
                {block name='basket-index-main'}
                {$lgCols = 12}
                {if ($Warenkorb->PositionenArr|count > 0)}{$lgCols = 7}{/if}
                {col cols=12 lg=$lgCols}
                    {opcMountPoint id='opc_before_heading'}
                    {block name='basket-index-heading'}
                        <h1 class="h2 basket-heading">
                            {lang key='basket'} {if $WarenkorbArtikelAnzahl > 0}
                                ({$WarenkorbArtikelAnzahl} {lang key='products'})
                            {/if}
                        </h1>
                    {/block}
                    {block name='basket-index-include-extension'}
                        {include file='snippets/extension.tpl'}
                    {/block}

                    {if ($Warenkorb->PositionenArr|count > 0)}
                        {block name='basket-index-basket'}
                            {opcMountPoint id='opc_before_basket'}
                            <div class="basket_wrapper">
                                {block name='basket-index-basket-items'}
                                    {block name='basket-index-form-cart'}
                                        {form id="cart-form" method="post" action=$cartURL class="jtl-validate" slide=true}
                                            {button name="fake"
                                                aria=["label"=>"{lang key='addToCart'}"]
                                                variant="hidden"
                                                type="submit"
                                                class="btn-hidden-default"
                                                tabindex="-1"
                                            }{/button}
                                            {input type="hidden" name="wka" value="1"}
                                            <div class="basket-items">
                                                {block name='basket-index-include-order-items'}
                                                    {include file='basket/cart_items.tpl'}
                                                {/block}
                                            </div>
                                            {block name='basket-index-include-uploads'}
                                                {include file='snippets/uploads.tpl' tplscope='basket'}
                                            {/block}
                                        {/form}
                                    {/block}

                                    {if $Einstellungen.kaufabwicklung.warenkorb_versandermittlung_anzeigen === 'Y'}
                                        {block name='basket-index-form-shipping-calc'}
                                            {opcMountPoint id='opc_before_shipping_calculator'}
                                            {form id="basket-shipping-estimate-form" class="shipping-calculator-form" method="post" action="{$cartURL}#basket-shipping-estimate-form" slide=true}
                                                {block name='basket-index-include-shipping-calculator'}
                                                    {include file='snippets/shipping_calculator.tpl' checkout=true hrAtEnd=false}
                                                {/block}
                                            {/form}
                                        {/block}
                                    {/if}
                                    {if $freeGifts->count() > 0}
                                        {block name='basket-index-freegifts-content'}
                                            {$selectedFreegift=0}
                                            {foreach JTL\Session\Frontend::getCart()->PositionenArr as $oPosition}
                                                {if $oPosition->nPosTyp === $smarty.const.C_WARENKORBPOS_TYP_GRATISGESCHENK}
                                                    {$selectedFreegift=$oPosition->Artikel->kArtikel}
                                                {/if}
                                            {/foreach}
                                            {row class="basket-freegift"}
                                                {col cols=12}
                                                    {block name='basket-index-freegifts-heading'}
                                                        <div id="freeGiftsHeading" class="h2 basket-heading hr-sect">
                                                            {if !empty($oSpezialseiten_arr) && isset($oSpezialseiten_arr[$smarty.const.LINKTYP_GRATISGESCHENK])}
                                                                <a href="{$oSpezialseiten_arr[$smarty.const.LINKTYP_GRATISGESCHENK]->getURL()}"
                                                                   title="{lang key='freeGiftFromOrderValueBasket'}">
                                                                    {lang key='freeGiftFromOrderValueBasket'}
                                                                </a>
                                                            {else}
                                                                {lang key='freeGiftFromOrderValueBasket'}
                                                            {/if}
                                                        </div>
                                                    {/block}
                                                {/col}
                                                {col cols=12}
                                                    {block name='basket-index-form-freegift'}
                                                        {form method="post" name="freegift" action=$cartURL class="text-center-util" slide=true}
                                                            {block name='basket-index-freegifts'}
                                                                {$additional = ''}
                                                                {if $freeGifts->count() < 3}{$additional = ' slider-no-preview'}{/if}
                                                                {row id="freegift"
                                                                     class="slick-smooth-loading carousel carousel-arrows-inside slick-lazy slick-type-half{$additional}"
                                                                     data=["slick-type"=>"slider-half"]}
                                                                    {include file='snippets/slider_items.tpl'
                                                                    items=$freeGifts
                                                                    type='freegift'}
                                                                {/row}
                                                            {/block}
                                                            {block name='basket-index-freegifts-form-submit'}
                                                                {input type="hidden" name="gratis_geschenk" value="1"}
                                                                {input name="gratishinzufuegen" type="hidden" value="{lang key='addToCart'}"}
                                                            {/block}
                                                        {/form}
                                                    {/block}
                                                {/col}
                                            {/row}
                                        {/block}
                                    {/if}
                                {/block}

                                {opcMountPoint id='opc_before_xselling'}
                                {if !empty($xselling->Kauf) && count($xselling->Kauf->Artikel) > 0}
                                    {block name='basket-index-basket-xsell'}
                                        {lang key='basketCustomerWhoBoughtXBoughtAlsoY' assign='panelTitle'}
                                        {block name='basket-index-include-product-slider'}
                                            {include file='snippets/product_slider.tpl' productlist=$xselling->Kauf->Artikel title=$panelTitle tplscope='half'}
                                        {/block}
                                    {/block}
                                {/if}
                                {opcMountPoint id='opc_after_xselling'}
                            </div>
                        {/block}
                    {else}
                        {block name='basket-index-cart-empty'}
                            {row class="basket-empty"}
                                {col}
                                    {block name='basket-index-alert-empty'}
                                        {alert variant="info"}
                                            {badge variant="light" class="bubble"}
                                                <i class="fas fa-shopping-cart"></i>
                                            {/badge}<br/>
                                            {lang key='emptybasket' section='checkout'}
                                        {/alert}
                                    {/block}
                                    {block name='basket-index-empty-continue-shopping'}
                                        {link href=$ShopURL class="btn btn-primary"}{lang key='continueShopping' section='checkout'}{/link}
                                    {/block}
                                {/col}
                            {/row}
                        {/block}
                    {/if}
                {/col}
                {/block}
                {if ($Warenkorb->PositionenArr|count > 0)}
                    {block name='basket-index-side'}
                    {col class='ml-auto-util' cols=12 lg=4}
                        <div class="sticky-top cart-summary">
                            {block name='basket-index-side-heading'}
                                <div class="h2 basket-heading">{lang key="orderOverview" section="account data"}</div>
                            {/block}
                            {if $Einstellungen.kaufabwicklung.warenkorb_kupon_anzeigen === 'Y'}
                                {block name='basket-index-coupon'}
                                    {card class='card-gray' no-body=true}
                                        {if $KuponMoeglich == 1}
                                            {block name='basket-index-coupon-available'}
                                                {cardheader}
                                                {block name='basket-index-coupon-heading'}
                                                    {button variant="link" block=true class="font-size-lg px-0" data=['toggle' => 'collapse', 'target' => '#coupon-form']}{lang key='useCoupon' section='checkout'}{/button}
                                                {/block}
                                                {/cardheader}
                                                {collapse id="coupon-form"}
                                                    {cardbody}
                                                    {block name='basket-index-coupon-form'}
                                                        {form class="jtl-validate" id="basket-coupon-form" method="post" action=$cartURL slide=true}
                                                            {formgroup label-for="couponCode" label={lang key='couponCodePlaceholder' section='checkout'} class="mw-100{if !empty($invalidCouponCode)} has-error{/if}"}
                                                                {input aria=["label"=>"{lang key='couponCode' section='account data'}"] type="text" name="Kuponcode" id="couponCode" maxlength="32" placeholder=" " required=true}
                                                            {/formgroup}
                                                            {button type="submit" value=1 variant="outline-primary" block=true}{lang key='couponSubmit' section='checkout'}{/button}
                                                        {/form}
                                                    {/block}
                                                    {/cardbody}
                                                {/collapse}
                                            {/block}
                                        {else}
                                            {block name='basket-index-coupon-unavailable'}
                                                {cardheader}
                                                    {lang key='couponUnavailable' section='checkout'}
                                                {/cardheader}
                                            {/block}
                                        {/if}
                                    {/card}
                                {/block}
                            {/if}
                            {card class="card-gray basket-summary"}
                                {block name='basket-index-price-tax'}
                                    {if $NettoPreise}
                                        {block name='basket-index-price-net'}
                                            {row class="total-net"}
                                                {col class="text-left-util" cols=7}
                                                    <span class="price_label"><strong>{lang key='subtotal' section='account data'} ({lang key='net'}):</strong></span>
                                                {/col}
                                                {col class="text-right-util price-col" cols=5}
                                                    <strong class="price total-sum">{$WarensummeLocalized[$NettoPreise]}</strong>
                                                {/col}
                                            {/row}
                                        {/block}
                                    {/if}

                                    {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N' && $Steuerpositionen|count > 0}
                                        {block name='basket-index-tax'}
                                            {foreach $Steuerpositionen as $Steuerposition}
                                                {row class="tax"}
                                                    {col class="text-left-util" cols=7}
                                                        <span class="tax_label">{$Steuerposition->cName}:</span>
                                                    {/col}
                                                    {col class="text-right-util price-col" cols=5}
                                                        <span class="tax_label">{$Steuerposition->cPreisLocalized}</span>
                                                    {/col}
                                                {/row}
                                            {/foreach}
                                        {/block}
                                    {/if}

                                    {if isset($smarty.session.Bestellung->GuthabenNutzen) && $smarty.session.Bestellung->GuthabenNutzen == 1}
                                        {block name='basket-index-credit'}
                                            {row class="customer-credit"}
                                                {col class="text-left-util" cols=7}
                                                    {lang key='useCredit' section='account data'}
                                                {/col}
                                                {col class="text-right-util" cols=5}
                                                    {$smarty.session.Bestellung->GutscheinLocalized}
                                                {/col}
                                            {/row}
                                        {/block}
                                    {/if}
                                    {block name='basket-index-price-sticky'}
                                        {row class="basket-summary-total"}
                                            {col class="text-left-util" cols=7}
                                                <span class="price_label">{lang key='subtotal' section='account data'}:</span>
                                            {/col}
                                            {col class="text-right-util price-col" cols=5}
                                                <strong class="total-sum">{$WarensummeLocalized[0]}</strong>
                                            {/col}
                                        {/row}
                                    {/block}
                                {/block}
                                {block name='basket-index-shipping'}
                                    {if $favourableShippingString !== ''}
                                        {row class="shipping-costs"}
                                            {col cols=12}
                                                <small>{$favourableShippingString}</small>
                                            {/col}
                                        {/row}
                                    {/if}
                                {/block}
                                {block name='basket-index-proceed-button'}
                                    {link id="cart-checkout-btn" href="{get_static_route id='bestellvorgang.php'}?wk=1" class="btn btn-primary"}
                                        {lang key='nextStepCheckout' section='checkout'}
                                    {/link}
                                {/block}
                            {/card}
                            {if !empty($WarenkorbVersandkostenfreiHinweis) && $Warenkorb->PositionenArr|count > 0}
                                {block name='basket-index-alert'}
                                    <div class="font-weight-bold mt-3">
                                        {if !empty($oSpezialseiten_arr) && isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}
                                            <a href="{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}"
                                               title="{lang key='shippingInfo' section='login'}"><i class="fas fa-truck text-dark mr-1"></i></a>
                                        {else}
                                            <i class="fas fa-truck text-dark mr-1"></i>
                                        {/if}
                                        <span>{lang key='shippingInfo' section='login'}</span>
                                    </div>
                                    {$WarenkorbVersandkostenfreiHinweis}
                                {/block}
                            {/if}
                            {block name='basket-index-shipping-include-free-hint'}
                                {include file='basket/freegift_hint.tpl'}
                            {/block}
                            {if $Einstellungen.kaufabwicklung.warenkorb_gesamtgewicht_anzeigen === 'Y'}
                                {block name='basket-index-notice-weight'}
                                    {row class="basket-summary-notice-weight-wrapper {if empty($WarenkorbVersandkostenfreiHinweis)}basket-summary-top{/if}"}
                                        {col cols=1}<i class="fas fa-weight-hanging"></i>{/col}
                                        {col cols=10 class="basket-summary-notice-weight"}
                                            {lang key='cartTotalWeight' section='basket' printf=$WarenkorbGesamtgewicht}
                                        {/col}
                                    {/row}
                                {/block}
                            {/if}
                        </div>
                    {/col}
                    {/block}
                {/if}
            {/row}
        {/container}
    {/block}

    {block name='basket-index-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
