{block name='snippets-filter-manufacturer'}
    {$limit = $Einstellungen.template.productlist.filter_max_options}
    {$collapseInit = false}
    <div class="filter-search-wrapper">
    {block name='snippets-filter-manufacturer-include-search-in-items'}
        {include file='snippets/filter/search_in_items.tpl' itemCount=count($filter->getOptions()) name=$filter->getFrontendName()}
    {/block}
    {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'B'}
        <nav class="nav nav-filter-has-image">
    {/if}
    {foreach $filter->getOptions() as $filterOption}
        {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
        {if $limit != -1 && $filterOption@iteration > $limit && !$collapseInit}
            {block name='snippets-filter-manufacturer-more-top'}
                <div class="collapse {if $filter->isActive()} show{/if}" id="box-collps-filter{$filter->getNiceName()}" aria-expanded="false" role="button">
                    <nav class="nav {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als !== 'B'}flex-column{/if}">
                {$collapseInit = true}
            {/block}
        {/if}
        {block name='snippets-filter-manufacturer-item'}
            {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als == 'B'}
                {$tooltip = ["toggle"=>"tooltip", "placement"=>"top", "boundary"=>"window"]}
            {else}
                {$tooltip = []}
            {/if}
            {link href="{if !empty($filterOption->getURL())}{$filterOption->getURL()}{else}#{/if}"
                title="{$filterOption->getName()|escape:'html'}: {$filterOption->getCount()} {lang key='productsFound'}"
                data=$tooltip
                class="filter-item {if $filterOption->isActive()}active{/if}"
                rel="nofollow"
            }
                <div class="box-link-wrapper">
                    {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als == 'B'}
                        {block name='snippets-filter-manufacturer-item-image'}
                            {image lazy=true webp=true
                                alt=$filterOption->getName()|escape:'html'
                                src=$filterOption->getData('cBildpfadKlein')
                                class="vmiddle filter-img"
                            }
                            <span class="hidden filter-item-value">{$filterOption->getName()}</span>
                        {/block}
                    {elseif $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'BT'}
                        {block name='snippets-filter-manufacturer-item-image-text'}
                            {image lazy=true webp=true
                                alt=$filterOption->getName()|escape:'html'
                                src=$filterOption->getData('cBildpfadKlein')
                                class="vmiddle filter-img"
                            }
                            <span class="word-break filter-item-value">{$filterOption->getName()}</span>
                            {badge variant="outline-secondary"}{$filterOption->getCount()}<span class="sr-only"> {lang key='productsFound'}</span>{/badge}
                        {/block}
                    {elseif $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'T'}
                        {block name='snippets-filter-manufacturer-item-text'}
                            <i class="far fa-{if $filterIsActive === true}check-{/if}square snippets-filter-item-icon-right"></i>
                            <span class="word-break filter-item-value">{$filterOption->getName()}</span>
                            {badge variant="outline-secondary"}{$filterOption->getCount()}<span class="sr-only"> {lang key='productsFound'}</span>{/badge}
                        {/block}
                    {/if}
                </div>
            {/link}
        {/block}
    {/foreach}
    {if $limit != -1 && $filter->getOptions()|count > $limit}
        {block name='snippets-filter-manufacturer-more-bottom'}
                </nav>
            </div>
            <div class="snippets-filter-show-all">
                {button
                    variant="link"
                    data=["toggle"=> "collapse", "target"=>"#box-collps-filter{$filter->getNiceName()}"]
                    aria=["expanded"=> "false"]}
                    {lang key='showAll'}
                {/button}
            </div>
        {/block}
    {/if}
    {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'B'}
        </nav>
    {/if}
    </div>
{/block}
