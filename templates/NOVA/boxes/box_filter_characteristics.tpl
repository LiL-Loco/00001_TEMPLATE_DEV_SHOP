{block name='boxes-box-filter-characteristics'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
        && !($isMobile || $Einstellungen.template.productlist.filter_placement === 'modal')}
        {foreach $oBox->getItems() as $characteristic}
            <div id="sidebox{$oBox->getID()}-{$characteristic->getID()}" class="box box-filter-characteristics hidden lg:block">
                {assign var=visible value=$characteristic->isActive() || $Einstellungen.template.productlist.filter_items_always_visible === 'Y'}
                {button
                    variant="link"
                    class="btn-filter-box dropdown-toggle"
                    block=true
                    aria=["expanded"=>($visible===true) ? "true" : "false"]
                    data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}-{$characteristic->getID()}"]
                }
                    <span class="characteristic-collapse-btn-inner text-truncate">
                        {$img = $characteristic->getImage(\JTL\Media\Image::SIZE_XS)}
                        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T'
                        && $img !== null
                        && strpos($img, $smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN) === false
                        && strpos($img, $smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN) === false}
                            {include file='snippets/image.tpl'
                                item=$characteristic
                                square=false
                                class='img-xs'
                                srcSize='xs'
                                sizes='24px'}
                        {/if}
                        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                            {$characteristic->getName()|escape:'html'}
                        {/if}
                    </span>
                {/button}
                {collapse
                    id="cllps-box{$oBox->getID()}-{$characteristic->getID()}"
                    visible=$visible}
                    {block name='boxes-box-filter-characteristics-characteristics'}
                        {if ($characteristic->getData('cTyp') === 'SELECTBOX') && $characteristic->getOptions()|count > 0}
                            {block name='boxes-box-filter-characteristics-select'}
                                {dropdown variant="outline-secondary" text="{lang key='selectFilter' section='global'} " toggle-class="btn-block text-left-util"}
                                {block name='boxes-box-filter-characteristics-include-characteristics-dropdown'}
                                    {include file='snippets/filter/characteristic.tpl' Merkmal=$characteristic}
                                {/block}
                                {/dropdown}
                            {/block}
                        {else}
                            {block name='boxes-box-filter-characteristics-link'}
                                {block name='boxes-box-filter-characteristics-include-characteristics-link'}
                                    {include file='snippets/filter/characteristic.tpl' Merkmal=$characteristic}
                                {/block}
                            {/block}
                        {/if}
                    {/block}
                {/collapse}
                {block name='boxes-box-filter-characteristics-hr'}
                    <hr class="box-filter-hr">
                {/block}
            </div>
        {/foreach}
    {/if}
{/block}
