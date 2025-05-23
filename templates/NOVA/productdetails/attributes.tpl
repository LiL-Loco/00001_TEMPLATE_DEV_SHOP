{block name='productdetails-attributes'}
{$inQuickView = !empty($smarty.get.quickView)}
{if $showAttributesTable}
    <div class="product-attributes">
    {block name='productdetails-attributes-table'}
        <table class="table table-sm table-striped table-bordered-outline">
            <thead>
                <tr>
                    <th scope="col" class="sr-only">{lang section="productDetails" key='itemInformation'}</th>
                    <th scope="col" class="sr-only">{lang section="productDetails" key='itemValue'}</th>
                </tr>
            </thead>
            <tbody>
            {if $Einstellungen.artikeldetails.merkmale_anzeigen === 'Y'}
                {block name='productdetails-attributes-characteristics'}
                    {foreach $Artikel->oMerkmale_arr as $characteristic}
                        <tr>
                            <td class="h6">{$characteristic->getName()|escape:'html'}:</td>
                            <td class="attr-characteristic">
                                {strip}
                                    {foreach $characteristic->getCharacteristicValues() as $characteristicValue}
                                        {if $characteristic->getType() === 'TEXT' || $characteristic->getType() === 'SELECTBOX' || $characteristic->getType() === ''}
                                            {block name='productdetails-attributes-badge'}
                                                <a {if !$inQuickView}href="{$characteristicValue->getURL()}"{/if}
                                                   class="badge badge-primary">
                                                    {$characteristicValue->getValue()|escape:'html'}
                                                </a>
                                            {/block}
                                        {else}
                                            {block name='productdetails-attributes-image'}
                                                <a {if !$inQuickView}href="{$characteristicValue->getURL()}"{/if}
                                                    class="text-decoration-none-util"
                                                    data-toggle="tooltip" data-placement="top" data-boundary="window"
                                                    title="{$characteristicValue->getValue()|escape:'html'}"
                                                    aria-label="{$characteristicValue->getValue()|escape:'html'}"
                                                >
                                                    {$img = $characteristicValue->getImage(\JTL\Media\Image::SIZE_XS)}
                                                    {if $img !== null && strpos($img, $smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN) === false
                                                    && strpos($img, $smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN) === false}
                                                        {include file='snippets/image.tpl'
                                                            item=$characteristicValue
                                                            square=false
                                                            srcSize='xs'
                                                            sizes='40px'
                                                            width='40'
                                                            height='40'
                                                            class='img-aspect-ratio'
                                                            alt=$characteristicValue->getValue()}
                                                    {else}
                                                        {badge variant="primary"}{$characteristicValue->getValue()|escape:'html'}{/badge}
                                                    {/if}
                                                </a>
                                            {/block}
                                        {/if}
                                    {/foreach}
                                {/strip}
                            </td>
                        </tr>
                    {/foreach}
                {/block}
            {/if}

            {if $showShippingWeight}
                {block name='productdetails-attributes-shipping-weight'}
                    <tr>
                        <td class="h6">{lang key='shippingWeight'}:</td>
                        <td class="weight-unit">
                            {$Artikel->cGewicht} {lang key='weightUnit'}
                        </td>
                    </tr>
                {/block}
            {/if}

            {if $showProductWeight}
                {block name='productdetails-attributes-product-weight'}
                    <tr class="attr-weight">
                        <td class="h6">{lang key='productWeight'}:</td>
                        <td class="weight-unit" itemprop="weight" itemscope itemtype="https://schema.org/QuantitativeValue">
                            <span itemprop="value">{$Artikel->cArtikelgewicht}</span> <span itemprop="unitText">{lang key='weightUnit'}
                        </td>
                    </tr>
                {/block}
            {/if}

            {if $Einstellungen.artikeldetails.artikeldetails_inhalt_anzeigen === 'Y'
                && isset($Artikel->cMasseinheitName)
                && isset($Artikel->fMassMenge)
                && $Artikel->fMassMenge > 0
                && $Artikel->cTeilbar !== 'Y'
                && ($Artikel->fAbnahmeintervall == 0 || $Artikel->fAbnahmeintervall == 1)
                && isset($Artikel->cMassMenge)}
                {block name='productdetails-attributes-unit'}
                    <tr class="attr-contents">
                        <td class="h6">{lang key='contents' section='productDetails'}: </td>
                        <td class="attr-value">
                            {$Artikel->cMassMenge} {$Artikel->cMasseinheitName}
                        </td>
                    </tr>
                {/block}
            {/if}

            {if $dimension && $Einstellungen.artikeldetails.artikeldetails_abmessungen_anzeigen === 'Y'}
                {block name='productdetails-attributes-dimensions'}
                    {assign var=dimensionArr value=$Artikel->getDimensionLocalized()}
                    {if $dimensionArr|count > 0}
                        <tr class="attr-dimensions">
                            <td class="h6">{lang key='dimensions' section='productDetails'}
                                ({foreach $dimensionArr as $dimkey => $dim}
                                    {$dimkey}{if !$dim@last} &times; {/if}
                                {/foreach}):
                            </td>
                            <td class="attr-value">
                                {foreach $dimensionArr as $dim}
                                    {$dim}{if $dim@last} cm {else} &times; {/if}
                                {/foreach}
                            </td>
                        </tr>
                    {/if}
                {/block}
            {/if}

            {if $Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y'
            || $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_ATTRIBUTEANHAENGEN]|default:0 == 1}
                {block name='productdetails-attributes-shop-attributes'}
                    {foreach $Artikel->Attribute as $Attribut}
                        <tr class="attr-custom">
                            <td class="h6">{$Attribut->cName}: </td>
                            <td class="attr-value">{$Attribut->cWert}</td>
                        </tr>
                    {/foreach}
                {/block}
            {/if}
            </tbody>
        </table>
    {/block}
    </div>
{/if}
{/block}
