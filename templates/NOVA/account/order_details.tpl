{block name='account-order-details'}
    {block name='account-order-details-script-location'}
        <script>
            if (top.location !== self.location) {
                top.location = self.location.href;
            }
        </script>
    {/block}
    {block name='account-order-details-heading'}
        <h1>{lang key='orderCompletedPre' section='checkout'}</h1>
    {/block}
    {block name='account-order-details-order-details-data'}
        {card no-body=true class='order-details'}
            {cardheader}
            {block name='account-order-details-order-heading'}
                {row class='align-items-center-util'}
                    {block name='account-order-details-order-heading-date'}
                        {col cols=12 lg=3 class='border-lg-right'}
                            <div class="order-details-date">
                                <span class="far fa-calendar"></span>{$Bestellung->dErstelldatum_de}
                            </div>
                        {/col}
                    {/block}
                    {col cols=6 lg='auto'}
                        {lang key='yourOrderId' section='checkout'}: {$Bestellung->cBestellNr}
                    {/col}
                    {col cols=6 lg='auto' class='order-details-status'}
                        {lang key='orderStatus' section='login'}: {$Bestellung->Status}
                    {/col}
                {/row}
            {/block}
            {/cardheader}
            {if isset($Kunde) && $Kunde->kKunde > 0}
                {cardbody}
                {block name='account-order-details-order-body'}
                    {row}
                        {col cols=12 lg=3 class='border-lg-right'}
                            <ul class="list-unstyled order-details-data">
                                <li>
                                    {block name='account-order-details-payment'}
                                        {lang key='paymentOptions' section='global'}:
                                        <span class="order-details-data-item">
                                            <dl class="list-unstyled">
                                                {if $Bestellung->dBezahldatum_de === '00.00.0000' || empty($Bestellung->dBezahldatum_de)}
                                                    <dt>{$Bestellung->cZahlungsartName}</dt>
                                                    <dd>
                                                        {if ($Bestellung->cStatus == $smarty.const.BESTELLUNG_STATUS_OFFEN || $Bestellung->cStatus == $smarty.const.BESTELLUNG_STATUS_IN_BEARBEITUNG)
                                                            && (($Bestellung->Zahlungsart->cModulId !== 'za_ueberweisung_jtl'
                                                            && $Bestellung->Zahlungsart->cModulId !== 'za_nachnahme_jtl'
                                                            && $Bestellung->Zahlungsart->cModulId !== 'za_rechnung_jtl'
                                                            && $Bestellung->Zahlungsart->cModulId !== 'za_barzahlung_jtl')
                                                            && (isset($Bestellung->Zahlungsart->bPayAgain) && $Bestellung->Zahlungsart->bPayAgain))}
                                                                {link href="{get_static_route id='bestellabschluss.php'}?payAgain=1&kBestellung={$Bestellung->kBestellung}"}{lang key='payNow' section='global'}{/link}
                                                        {else}
                                                            {lang key='notPayedYet' section='login'}
                                                        {/if}
                                                    </dd>
                                                {elseif $incommingPayments->isEmpty()}
                                                    <dt>{$Bestellung->cZahlungsartName}</dt>
                                                {/if}
                                                {foreach $incommingPayments as $paymentProvider => $incommingPayment}
                                                    <dt>{htmlentities($paymentProvider)}</dt>
                                                    {foreach $incommingPayment as $payment}
                                                        <dd>{$payment->paymentLocalization}</dd>
                                                    {/foreach}
                                                {/foreach}
                                            </dl>
                                        </span>
                                    {/block}
                                </li>
                                <li>
                                    {block name='account-order-details-shipping'}
                                        {lang key='shippingOptions' section='global'}:
                                        <span class="order-details-data-item">
                                            <ul class="list-unstyled">
                                                <li>{$Bestellung->cVersandartName}</li>
                                                {if $Bestellung->cStatus == $smarty.const.BESTELLUNG_STATUS_VERSANDT}
                                                    <li>{lang key='shippedOn' section='login'} {$Bestellung->dVersanddatum_de}</li>
                                                {elseif $Bestellung->cStatus == $smarty.const.BESTELLUNG_STATUS_TEILVERSANDT}
                                                    <li>{$Bestellung->Status}</li>
                                                {else}
                                                    <li><span>{lang key='notShippedYet' section='login'}</span></li>
                                                    {if $Bestellung->cStatus !== $smarty.const.BESTELLUNG_STATUS_STORNO}
                                                        <li>
                                                            <span>{lang key='shippingTime' section='global'}: {if isset($cEstimatedDeliveryEx)}{$cEstimatedDeliveryEx}{else}{$Bestellung->cEstimatedDelivery}{/if}</span>
                                                        </li>
                                                    {/if}
                                                {/if}
                                            </ul>
                                        </span>
                                    {/block}
                                </li>
                                <li>
                                    {block name='account-order-details-billing-address'}
                                        {lang key='billingAdress' section='checkout'}:
                                        <span class="order-details-data-item">
                                            {block name='account-order-details-include-inc-billing-address'}
                                                {include file='checkout/inc_billing_address.tpl' orderDetail=true}
                                            {/block}
                                        </span>
                                    {/block}
                                </li>
                                <li>
                                    {block name='account-order-details-shipping-address'}
                                        {lang key='shippingAdress' section='checkout'}:
                                        <span class="order-details-data-item">
                                            {if !empty($Lieferadresse->kLieferadresse)}
                                                {block name='account-order-details-include-inc-delivery-address'}
                                                    {include file='checkout/inc_delivery_address.tpl' orderDetail=true}
                                                {/block}
                                            {else}
                                                {lang key='shippingAdressEqualBillingAdress' section='account data'}
                                            {/if}
                                        </span>
                                    {/block}
                                </li>
                                {if $Einstellungen.global.global_rma_enabled === 'Y' && $rmaLink|default:'' !== ''}
                                    <li>
                                        {block name='account-order-details-rma-button'}
                                        <a href="{$rmaLink}" class="btn btn-outline-primary">
                                            <i class="fa fa-retweet"
                                               aria-label="{lang key='rma' section='rma'}: {lang key='rma_artikelwahl' section='rma'}"
                                               title="{lang key='rma' section='rma'}: {lang key='rma_artikelwahl' section='rma'}"></i>
                                            {lang key='createRetoure' section='rma'}
                                        </a>
                                        {/block}
                                    </li>
                                {/if}
                            </ul>
                        {/col}
                        {col cols=12 lg=9}
                            {block name='account-order-details-order-subheading-basket'}
                                <span class="subheadline">{lang key='basket'}</span>
                            {/block}
                            {block name='account-order-details-include-order-item'}
                                {include file='account/order_item.tpl' tplscope='confirmation'}
                            {/block}
                        {/col}
                    {/row}
                    {/block}
                {/cardbody}
            {else}
                {cardbody class="order-details-request-plz"}
                    {block name='account-order-details-request-plz'}
                        {row}
                            {col cols=12 md=6}
                                {form method="post" id='request-plz' action="{get_static_route}" class="jtl-validate" slide=true}
                                    {input type="hidden" name="uid" value="{$uid}"}
                                    <p>{lang key='enter_plz_for_details' section='account data'}</p>
                                    {formgroup
                                        label-for="postcode"
                                        label={lang key='plz' section='account data'}
                                    }
                                        {input
                                            type="text"
                                            name="plz"
                                            value=""
                                            id="postcode"
                                            class="postcode_input"
                                            placeholder=" "
                                            required=true
                                            autocomplete="billing postal-code"
                                        }
                                    {/formgroup}
                                    {row}
                                        {col class='ml-auto-util col-md-auto'}
                                            {button type='submit' value='1' block=true variant='primary' class='mb-3'}
                                                {lang key='view' section='global'}
                                            {/button}
                                        {/col}
                                    {/row}
                                {/form}
                            {/col}
                            {col cols=12 md=9}
                                {block name='account-order-details-order-subheading-basket'}
                                    <span class="subheadline">{lang key='basket'}</span>
                                {/block}
                                {block name='account-order-details-include-order-item'}
                                    {include file='account/order_item.tpl' tplscope='confirmation'}
                                {/block}
                            {/col}
                        {/row}
                    {/block}
                {/cardbody}
            {/if}
        {/card}
    {/block}

    {if isset($Kunde) && $Kunde->kKunde > 0}
        {block name='account-order-details-include-downloads'}
            {include file='account/downloads.tpl'}
        {/block}
        {block name='account-order-details-include-uploads'}
            {include file='account/uploads.tpl'}
        {/block}

        {if $Bestellung->oLieferschein_arr|count > 0}
            {block name='account-order-details-delivery-note-content'}
                <div class="h2">{if $Bestellung->cStatus == $smarty.const.BESTELLUNG_STATUS_TEILVERSANDT}{lang key='partialShipped' section='order'}{else}{lang key='shipped' section='order'}{/if}</div>
                <div class="table-responsive mb-3">
                    <table class="table table-striped table-bordered">
                        <thead>
                            {block name='account-order-details-delivery-note-header'}
                                <tr>
                                    <th>{lang key='shippingOrder' section='order'}</th>
                                    <th>{lang key='shippedOn' section='login'}</th>
                                    <th class="text-right-util">{lang key='packageTracking' section='order'}</th>
                                </tr>
                            {/block}
                        </thead>
                        <tbody>
                            {block name='account-order-details-delivery-notes'}
                                {foreach $Bestellung->oLieferschein_arr as $oLieferschein}
                                    <tr>
                                        <td>{link data=["toggle"=>"modal", "target"=>"#shipping-order-{$oLieferschein->getLieferschein()}"] id=$oLieferschein->getLieferschein() href="#" title=$oLieferschein->getLieferscheinNr()}{$oLieferschein->getLieferscheinNr()}{/link}</td>
                                        <td>{$oLieferschein->getErstellt()|date_format:'d.m.Y H:i'}</td>
                                        <td class="text-right-util">
                                            {foreach $oLieferschein->oVersand_arr as $oVersand}
                                                {if $oVersand->getIdentCode()}
                                                    <p>{link href=$oVersand->getLogistikVarUrl() target="_blank" class="shipment" title=$oVersand->getIdentCode()}{lang key='packageTracking' section='order'}{/link}</p>
                                                {/if}
                                            {/foreach}
                                        </td>
                                    </tr>
                                {/foreach}
                            {/block}
                        </tbody>
                    </table>
                </div>

                {* Lieferschein Popups *}
                {foreach $Bestellung->oLieferschein_arr as $oLieferschein}
                    {block name='account-order-details-delivery-note-popup'}
                        {modal id="shipping-order-{$oLieferschein->getLieferschein()}"
                            title=(($Bestellung->cStatus == $smarty.const.BESTELLUNG_STATUS_TEILVERSANDT) ? {lang key='partialShipped' section='order'} : {lang key='shipped' section='order'})
                            class="fade shipping-order-modal"
                            size="lg"}
                            {block name='account-order-details-delivery-note-popup-heading'}
                                <div class="shipping-order-modal-mb">
                                    <strong>{lang key='shippingOrder' section='order'}</strong>: {$oLieferschein->getLieferscheinNr()}<br />
                                    <strong>{lang key='shippedOn' section='login'}</strong>: {$oLieferschein->getErstellt()|date_format:'d.m.Y H:i'}<br />
                                </div>
                            {/block}
                            {if $oLieferschein->getHinweis()|strlen > 0}
                                {block name='account-order-details-delivery-note-popup-alert'}
                                    {alert variant="info" class="shipping-order-modal-mb"}{$oLieferschein->getHinweis()}{/alert}
                                {/block}
                            {/if}
                            {block name='account-order-details-delivery-note-popup-tracking'}
                                <div class="shipping-order-modal-mb">
                                    {foreach $oLieferschein->oVersand_arr as $oVersand}
                                        {if $oVersand->getIdentCode()}
                                            <p>
                                                {link href=$oVersand->getLogistikVarUrl() target="_blank" class="shipment" title=$oVersand->getIdentCode()}
                                                    {lang key='packageTracking' section='order'}
                                                {/link}
                                            </p>
                                        {/if}
                                    {/foreach}
                                </div>
                            {/block}
                            {block name='account-order-details-delivery-note-popup-table'}
                                {$hasPositions=false}
                                {foreach $oLieferschein->oLieferscheinPos_arr as $oLieferscheinpos}
                                    {if $oLieferscheinpos->oPosition->cName|default:'' !== ''}
                                        {$hasPositions=true}
                                    {/if}
                                {/foreach}
                                {if $hasPositions}
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>{lang key="partialShippedPosition" section="order"}</th>
                                        <th>{lang key="partialShippedCount" section="order"}</th>
                                        <th>{lang key='productNo' section='global'}</th>
                                        <th>{lang key='product' section='global'}</th>
                                        <th>{lang key="order" section="global"}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $oLieferschein->oLieferscheinPos_arr as $oLieferscheinpos}
                                        {if $oLieferscheinpos->oPosition->cName|default:'' === ''}
                                            {continue}
                                        {/if}
                                        <tr>
                                            <td>{$oLieferscheinpos@iteration}</td>
                                            <td>{$oLieferscheinpos->getAnzahl()}</td>
                                            <td>{$oLieferscheinpos->oPosition->cArtNr|default:''}</td>
                                            <td>
                                                {$oLieferscheinpos->oPosition->cName}
                                                <ul class="list-unstyled text-muted-util small">
                                                    {if !empty($oLieferscheinpos->oPosition->cHinweis)}
                                                        <li class="text-info notice">{$oLieferscheinpos->oPosition->cHinweis}</li>
                                                    {/if}
                                                    {* Artikel property is not set on free gifts and maybe some other position types *}
                                                    {if isset($oLieferscheinpos->oPosition->Artikel)}
                                                        {* eindeutige Merkmale *}
                                                        {if $oLieferscheinpos->oPosition->Artikel->cHersteller && $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== "N"}
                                                            <li class="manufacturer">
                                                                <strong>{lang key='manufacturer' section='productDetails'}</strong>:
                                                                <span class="values">
                                                                   {$oLieferscheinpos->oPosition->Artikel->cHersteller}
                                                                </span>
                                                            </li>
                                                        {/if}

                                                        {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelmerkmale == 'Y' && !empty($oLieferscheinpos->oPosition->Artikel->oMerkmale_arr)}
                                                            {foreach $oLieferscheinpos->oPosition->Artikel->oMerkmale_arr as $characteristic}
                                                                <li class="characteristic">
                                                                    <strong>{$characteristic->getName()|escape:'html'}</strong>:
                                                                    <span class="values">
                                                                        {foreach $characteristic->getCharacteristicValues() as $characteristicValue}
                                                                            {if !$characteristicValue@first}, {/if}
                                                                            {$characteristicValue->getValue()}
                                                                        {/foreach}
                                                                    </span>
                                                                </li>
                                                            {/foreach}
                                                        {/if}
                                                    {/if}
                                                </ul>
                                            </td>
                                            <td>{$Bestellung->cBestellNr}</td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                                {/if}
                            {/block}
                        {/modal}
                    {/block}
                {/foreach}
            {/block}
        {/if}

        {block name='account-order-details-order-comment'}
            {if $Bestellung->cKommentar !== null && !empty(trim($Bestellung->cKommentar))}
                <div class="h3">{lang key='yourOrderComment' section='login'}</div>
                <p>{$Bestellung->cKommentar}</p>
            {/if}
        {/block}
        {block name='account-order-details-actions'}
            {row class="btn-row"}
                {col md=3 cols=12}
                    {link class="btn btn-outline-primary btn-block" href="{get_static_route id='jtl.php'}?bestellungen=1"}
                        {lang key='back'}
                    {/link}
                {/col}
            {/row}
        {/block}
    {/if}
{/block}
