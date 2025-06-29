{block name='layout-header-logo'}
    <div class="toggler-logo-wrapper">
        {block name='layout-header-logo-navbar-toggle'}
            <button id="burger-menu" class="burger-menu-wrapper navbar-toggler collapsed {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}hidden{/if}" type="button" data-toggle="collapse" data-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="{lang section='aria' key='toggleNavigation'}">
                <span class="navbar-toggler-icon"></span>
            </button>
        {/block}

        {block name='layout-header-logo-logo'}
            <div id="logo" class="logo-wrapper" itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
                <span itemprop="name" class="hidden">{$meta_publisher}</span>
                <meta itemprop="url" content="{$ShopHomeURL}">
                <meta itemprop="logo" content="{$ShopLogoURL}">
                {link class="navbar-brand" href=$ShopHomeURL}
                {if isset($ShopLogoURL)}
                    {image width=180 height=50 src=$ShopLogoURL
                        alt="{$Einstellungen.global.global_shopname} {lang key='startpage' section='breadcrumb'} "
                        id="shop-logo"
                    }
                {else}
                    <span class="h1">{$Einstellungen.global.global_shopname}</span>
                {/if}
                {/link}
            </div>
        {/block}
    </div>
{/block}
