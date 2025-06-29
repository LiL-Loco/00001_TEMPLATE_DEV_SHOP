{block name='snippets-categories-mega'}
    {strip}
    {block name='snippets-categories-mega-assigns'}
        {if !isset($i)}
            {assign var=i value=0}
        {/if}
        {if !isset($activeId)}
            {if $NaviFilter->hasCategory()}
                {$activeId = $NaviFilter->getCategory()->getValue()}
            {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && isset($Artikel)}
                {$activeId = $Artikel->gibKategorie()}
            {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && isset($smarty.session.LetzteKategorie)}
                {$activeId = $smarty.session.LetzteKategorie}
            {else}
                {$activeId = 0}
            {/if}
        {/if}
    {/block}
    {block name='snippets-categories-mega-categories'}
    {if $Einstellungen.template.megamenu.show_categories !== 'N'
        && ($Einstellungen.global.global_sichtbarkeit != 3 || \JTL\Session\Frontend::getCustomer()->getID() > 0)}
        {get_category_array categoryId=0 assign='categories'}
        {if !empty($categories)}
            {if !isset($activeParents)
            && ($nSeitenTyp === $smarty.const.PAGE_ARTIKEL || $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE)}
                {get_category_parents categoryId=$activeId assign='activeParents'}
            {/if}
            {block name='snippets-categories-mega-categories-inner'}
            {foreach $categories as $category}
                {if isset($activeParents) && is_array($activeParents) && isset($activeParents[$i])}
                    {assign var=activeParent value=$activeParents[$i]}
                {/if}
                {if $category->isOrphaned() === false}
                    {if $category->hasChildren()}
                        {block name='snippets-categories-mega-category-child'}
                            <li class="nav-item nav-scrollbar-item dropdown dropdown-full
                                {if $Einstellungen.template.megamenu.show_categories === 'mobile'} lg:hidden
                                {elseif $Einstellungen.template.megamenu.show_categories === 'desktop'} hidden d-lg-inline-block {/if}
                                {if $category->getID() === $activeId
                            || (isset($activeParent)
                                && $activeParent->getID() === $category->getID())} active{/if}">
                                {link href=$category->getURL()
                                    title=$category->getName()|escape:'html'
                                    class="nav-link dropdown-toggle"
                                    target="_self"
                                    data=["category-id"=>$category->getID()]}
                                    <span class="nav-mobile-heading">{$category->getShortName()}</span>
                                {/link}
                                <div class="dropdown-menu">
                                    <div class="dropdown-body">
                                        {container class="subcategory-wrapper"}
                                            {row class="lg-row-lg nav"}
                                                {col lg=4 xl=3 class="nav-item-lg-m nav-item dropdown lg:hidden"}
                                                    {link href=$category->getURL()}
                                                        <strong class="nav-mobile-heading">{lang key='menuShow' printf=$category->getShortName()}</strong>
                                                    {/link}
                                                {/col}
                                                {block name='snippets-categories-mega-sub-categories'}
                                                    {if $category->hasChildren()}
                                                        {if !empty($category->getChildren())}
                                                            {assign var=sub_categories value=$category->getChildren()}
                                                        {else}
                                                            {get_category_array categoryId=$category->getID() assign='sub_categories'}
                                                        {/if}
                                                        {foreach $sub_categories as $sub}
                                                            {col lg=4 xl=3 class="nav-item-lg-m nav-item {if $sub->hasChildren()}dropdown{/if}"}
                                                                {block name='snippets-categories-mega-category-child-body-include-categories-mega-recursive'}
                                                                    {include file='snippets/categories_mega_recursive.tpl' mainCategory=$sub firstChild=true subCategory=$i + 1}
                                                                {/block}
                                                            {/col}
                                                        {/foreach}
                                                    {/if}
                                                {/block}
                                            {/row}
                                        {/container}
                                    </div>
                                </div>
                            </li>
                        {/block}
                    {else}
                        {block name='snippets-categories-mega-category-no-child'}
                            {navitem href=$category->getURL() title=$category->getName()|escape:'html'
                                class="nav-scrollbar-item {if $Einstellungen.template.megamenu.show_categories === 'mobile'} lg:hidden
                                    {elseif $Einstellungen.template.megamenu.show_categories === 'desktop'} hidden d-lg-inline-block {/if}
                                    {if $category->getID() === $activeId}active{/if}"
                                data=["category-id"=>$category->getID()]}
                                <span class="text-truncate d-block">{$category->getShortName()}</span>
                            {/navitem}
                        {/block}
                    {/if}
                {/if}
            {/foreach}
            {/block}
        {/if}
    {/if}
    {/block}{* /megamenu-categories*}

    {block name='snippets-categories-mega-manufacturers'}
    {if $Einstellungen.template.megamenu.show_manufacturers !== 'N'
        && ($Einstellungen.global.global_sichtbarkeit != 3 || JTL\Session\Frontend::getCustomer()->getID() > 0)}
        {get_manufacturers assign='manufacturers'}
        {if !empty($manufacturers)}
            {assign var=manufacturerOverview value=null}
            {if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_HERSTELLER])}
                {$manufacturerOverview=$oSpezialseiten_arr[$smarty.const.LINKTYP_HERSTELLER]}
            {/if}
            {block name='snippets-categories-mega-manufacturers-inner'}
                <li class="nav-item nav-scrollbar-item dropdown dropdown-full {if $nSeitenTyp === $smarty.const.PAGE_HERSTELLER}active{/if}">
                    {link href="{if $manufacturerOverview !== null}{$manufacturerOverview->getURL()}{else}#{/if}" title={lang key='manufacturers'} class="nav-link dropdown-toggle" target="_self"}
                        <span class="text-truncate nav-mobile-heading">
                            {if $manufacturerOverview !== null && !empty($manufacturerOverview->getName())}
                                {$manufacturerOverview->getName()}
                            {else}
                                {lang key='manufacturers'}
                            {/if}
                        </span>
                    {/link}
                    <div class="dropdown-menu">
                        <div class="dropdown-body">
                            {container}
                                {row class="lg-row-lg nav"}
                                    {if $manufacturerOverview !== null}
                                        {col lg=4 xl=3 class="nav-item-lg-m nav-item lg:hidden"}
                                            {block name='snippets-categories-mega-manufacturers-header'}
                                                {link href="{$manufacturerOverview->getURL()}"}
                                                    <strong class="nav-mobile-heading">
                                                        {if !empty($manufacturerOverview->getName())}
                                                            {$manufacturerTitle = $manufacturerOverview->getName()}
                                                        {else}
                                                            {$manufacturerTitle = {lang key='manufacturers'}}
                                                        {/if}
                                                        {lang key='menuShow' printf=$manufacturerTitle}
                                                    </strong>
                                                {/link}
                                            {/block}
                                        {/col}
                                    {/if}
                                    {foreach $manufacturers as $mft}
                                        {col lg=4 xl=3 class='nav-item-lg-m nav-item'}
                                            {block name='snippets-categories-mega-manufacturers-link'}
                                                {link href=$mft->getURL() title=$mft->getName()|escape:'html' class='submenu-headline submenu-headline-toplevel nav-link '}
                                                    {if $Einstellungen.template.megamenu.show_manufacturer_images !== 'N'
                                                        && (!$isMobile || $isTablet)}
                                                        {include file='snippets/image.tpl'
                                                            class='submenu-headline-image'
                                                            item=$mft
                                                            square=false
                                                            srcSize='sm'
                                                            alt="{lang section='productOverview' key='manufacturerSingle'}: {$mft->getName()|escape:'html'}"}
                                                    {/if}
                                                    {$mft->getName()}
                                                {/link}
                                            {/block}
                                        {/col}
                                    {/foreach}
                                {/row}
                            {/container}
                        </div>
                    </div>
                </li>
            {/block}
        {/if}
    {/if}
    {/block} {* /megamenu-manufacturers*}
    {if $Einstellungen.template.megamenu.show_pages !== 'N'}
        {block name='snippets-categories-mega-include-linkgroup-list'}
            {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier='megamenu' dropdownSupport=true tplscope='megamenu'}
        {/block}
    {/if} {* /megamenu-pages*}

    {if $isMobile}
        {block name='snippets-categories-mega-top-links-hr'}
            <li class="lg:hidden"><hr></li>
        {/block}
        {block name='snippets-categories-mega-wishlist'}
        {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
            {navitem href="{get_static_route id='wunschliste.php'}" class="wl-nav-scrollbar-item nav-scrollbar-item"}
                {lang key='wishlist'}
                {badge id="badge-wl-count" variant="primary" class="product-count"}
                    {if \JTL\Session\Frontend::getWishlist()->getID() > 0}
                        {\JTL\Session\Frontend::getWishlist()->getItems()|count}
                    {else}
                        0
                    {/if}
                {/badge}
            {/navitem}
        {/if}
        {/block}
        {block name='snippets-categories-mega-comparelist'}
        {if $Einstellungen.vergleichsliste.vergleichsliste_anzeigen === 'Y'}
            {navitem href="{get_static_route id='vergleichsliste.php'}" class="comparelist-nav-scrollbar-item nav-scrollbar-item"}
                {lang key='compare'}
                {badge id="comparelist-badge" variant="primary" class="product-count"}
                    {count(JTL\Session\Frontend::getCompareList()->oArtikel_arr)}
                {/badge}
            {/navitem}
        {/if}
        {/block}
        {if $linkgroups->getLinkGroupByTemplate('Kopf') !== null}
        {block name='snippets-categories-mega-top-links'}
            {foreach $linkgroups->getLinkGroupByTemplate('Kopf')->getLinks() as $Link}
                {navitem class="nav-scrollbar-item lg:hidden" active=$Link->getIsActive() href=$Link->getURL() title=$Link->getTitle() target=$Link->getTarget()}
                    {$Link->getName()}
                {/navitem}
            {/foreach}
        {/block}
        {/if}
        {block name='layout-header-top-bar-user-settings'}
            {block name='layout-header-top-bar-user-settings-currency'}
                {if JTL\Session\Frontend::getCurrencies()|count > 1}
                    <li class="currency-nav-scrollbar-item nav-item nav-scrollbar-item dropdown dropdown-full lg:hidden">
                        {block name='layout-header-top-bar-user-settings-currency-link'}
                            {link id='currency-dropdown' href='#' title={lang key='currency'} class="nav-link dropdown-toggle" target="_self"}
                                {lang key='currency'}
                            {/link}
                        {/block}
                        {block name='layout-header-top-bar-user-settings-currency-body'}
                            <div class="dropdown-menu">
                                <div class="dropdown-body">
                                    {container}
                                        {row class="lg-row-lg nav"}
                                            {col lg=4 xl=3 class="nav-item-lg-m nav-item dropdown lg:hidden"}
                                                {block name='layout-header-top-bar-user-settings-currency-header'}
                                                    <strong class="nav-mobile-heading">{lang key='currency'}</strong>
                                                {/block}
                                            {/col}
                                            {foreach JTL\Session\Frontend::getCurrencies() as $currency}
                                                {col lg=4 xl=3 class='nav-item-lg-m nav-item'}
                                                    {block name='layout-header-top-bar-user-settings-currency-header-items'}
                                                        {dropdownitem href=$currency->getURLFull() rel="nofollow" active=(JTL\Session\Frontend::getCurrency()->getName() === $currency->getName())}
                                                            {$currency->getName()}
                                                        {/dropdownitem}
                                                    {/block}
                                                {/col}
                                            {/foreach}
                                        {/row}
                                    {/container}
                                </div>
                            </div>
                        {/block}
                    </li>
                {/if}
            {/block}
        {/block}
    {/if}

    {/strip}
{/block}
