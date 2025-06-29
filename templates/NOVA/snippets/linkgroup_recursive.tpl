{block name='snippets-linkgroup-recursive'}
    {if isset($linkgroupIdentifier) && (!isset($i) || isset($limit) && $i < $limit)}
        {strip}
            {$layout = $layout|default:'dropdown'}
            {if !isset($i)}
                {assign var=i value=0}
            {/if}
            {if !isset($limit)}
                {assign var=limit value=3}
            {/if}
            {if !isset($activeId)}
                {assign var=activeId value=0}
                {if isset($Link) && $Link->getID() > 0}
                    {assign var=activeId value=$Link->getID()}
                {elseif JTL\Shop::$kLink > 0}
                    {assign var=activeId value=JTL\Shop::$kLink}
                    {assign var=Link value=JTL\Shop::Container()->getLinkService()->getLinkByID($activeId)}
                {/if}
            {/if}
            {if !isset($activeParents)}
                {assign var=activeParents value=JTL\Shop::Container()->getLinkService()->getParentIDs($activeId)}
            {/if}
            {if !isset($links)}
                {get_navigation linkgroupIdentifier=$linkgroupIdentifier assign='links'}
            {/if}
            {if !empty($links)}
                {if $layout === 'dropdown'}
                {block name='snippets-linkgroup-recursive-list'}
                    {foreach $links as $li}
                        {$activeParent = -1}
                        {$hasItems = $li->getChildLinks()->count() > 0 && (($i+1) < $limit)}
                        {if isset($activeParents) && is_array($activeParents) && isset($activeParents[$i])}
                            {$activeParent = $activeParents[$i]}
                        {/if}
                        {$active = $li->getIsActive()
                            || (isset($activeParent) && $activeParent == $li->getID())
                            || ($li->getID() == $activeId)}
                        {if $hasItems}
                            <li class="link-group-item nav-item {if $hasItems}dropdown{/if}{if $active} active{/if}">
                                {block name='snippets-linkgroup-recursive-link'}
                                    <span class="nav-link dropdown-toggle" data-toggle="collapse"
                                          data-target="#link_box_{$li->getID()}_{$i}"
                                          aria-expanded="{if $active}true{else}false{/if}">
                                        <a class="nav-link"  onclick="event.stopPropagation();" target="_self"
                                           href="{$li->getURL()}">
                                            {$li->getName()}
                                        </a>
                                    </span>
                                {/block}
                                {block name='snippets-linkgroup-recursive-has-items-nav'}
                                    {nav vertical=true class="collapse {if ($li->getReference() > 0 && $li->getReference() == $activeId)
                                        || ($li->getReference() > 0 && $li->getReference() == $activeParent)
                                        || $active || $activeParent === $li->getID()}show{/if}" id="link_box_{$li->getID()}_{$i}"
                                    }
                                    {block name='snippets-linkgroup-recursive-include-linkgroup-recursive'}
                                        {if $li->getChildLinks()->count() > 0}
                                            {include file='snippets/linkgroup_recursive.tpl' i=$i+1 links=$li->getChildLinks() limit=$limit activeId=$activeId activeParents=$activeParents}
                                        {else}
                                            {include file='snippets/linkgroup_recursive.tpl' i=$i+1 links=array($li) limit=$limit activeId=$activeId activeParents=$activeParents}
                                        {/if}
                                    {/block}
                                    {/nav}
                                {/block}
                            </li>
                        {else}
                            {block name='snippets-linkgroup-recursive-has-not-items'}
                                {navitem class="{if $active || $li->getID() === $activeId} active{/if}" href=$li->getURL() target=$li->getTarget()}
                                    {$li->getName()}
                                {/navitem}
                            {/block}
                        {/if}
                    {/foreach}
                {/block}
                {else}
                    {block name='snippets-linkgroup-recursive-mega'}
                        {block name='snippets-linkgroup-mega-recursive-main-link'}
                            {link href=$mainLink->getURL()
                                nofollow=$mainLink->getNoFollow()
                                class="lg:block {if $firstChild}submenu-headline submenu-headline-toplevel{/if} {$subCategory} {if $mainLink->getChildLinks()->count() > 0}nav-link dropdown-toggle{/if}"
                                aria=["expanded"=>"false"]
                                target=$mainLink->getTarget()}
                                <span class="text-truncate d-block">
                                    {$mainLink->getName()}
                                </span>
                            {/link}
                        {/block}
                        {if $mainLink->getChildLinks()->count() > 0 && $Einstellungen.template.megamenu.show_subcategories !== 'N'}
                            {block name='snippets-linkgroup-recursive-mega-child-content'}
                                <div class="categories-recursive-dropdown dropdown-menu">
                                    {nav}
                                    {block name='snippets-linkgroup-recursive-mega-child-header'}
                                        <li class="nav-item lg:hidden">
                                            {link href=$mainLink->getURL() target=$mainLink->getTarget()}
                                                <strong class="nav-mobile-heading">
                                                    {lang key='menuShow' printf=$mainLink->getName()}
                                                </strong>
                                            {/link}
                                        </li>
                                    {/block}
                                    {block name='snippets-linkgroup-recursive-mega-child-links'}
                                        {foreach $mainLink->getChildLinks() as $link}
                                            {if $link->getChildLinks()->count() > 0}
                                                {block name='snippets-linkgroup-recursive-mega-child-link-child'}
                                                    <li class="nav-item dropdown">
                                                        {include file='snippets/linkgroup_recursive.tpl'
                                                            linkgroupIdentifier='mega'
                                                            limit=100
                                                            tplscope='megamenu'
                                                            layout='list'
                                                            mainLink=$link
                                                            firstChild=false
                                                            subCategory=$subCategory + 1}
                                                    </li>
                                                {/block}
                                            {else}
                                                {block name='snippets-linkgroup-recursive-mega-child-link-no-child'}
                                                    {navitem href=$link->getURL()
                                                        nofollow=$link->getNoFollow()
                                                        target=$link->getTarget()}
                                                        <span class="text-truncate d-block">
                                                            {$link->getName()}
                                                        </span>
                                                    {/navitem}
                                                {/block}
                                            {/if}
                                        {/foreach}
                                    {/block}
                                    {/nav}
                                </div>
                            {/block}
                        {/if}
                    {/block}
                {/if}
            {/if}
        {/strip}
    {/if}
{/block}
