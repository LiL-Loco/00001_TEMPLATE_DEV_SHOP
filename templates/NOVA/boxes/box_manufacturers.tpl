{block name='boxes-box-manufacturers'}
    <div class="box box-manufacturers box-normal dropdown-full-width" id="sidebox{$oBox->getID()}">
        {block name='boxes-box-manufacturers-content'}
            {block name='boxes-box-manufacturers-toggle-title'}
                {link id="crd-hdr-{$oBox->getID()}"
                    href="#crd-cllps-{$oBox->getID()}"
                    data=["toggle"=>"collapse"]
                    role="button"
                    aria=["expanded"=>"false","controls"=>"crd-cllps-{$oBox->getID()}"]
                    class="box-normal-link dropdown-toggle"}
                    {lang key='manufacturers'}
                {/link}
            {/block}
            {block name='boxes-box-manufacturers-title'}
                <div class="productlist-filter-headline hidden md:flex">
                    {lang key='manufacturers'}
                </div>
            {/block}
            {block name='boxes-box-manufacturers-collapse'}
                {collapse
                class="md:block"
                visible=false
                id="crd-cllps-{$oBox->getID()}"
                aria=["labelledby"=>"crd-hdr-{$oBox->getID()}"]}
                    {if $oBox->getManufacturers()|count > 8}
                        {block name='boxes-box-manufacturers-dropdown'}
                            {dropdown class="w-100-util" variant="secondary btn-block" text="{lang key='selectManufacturer'}<span class='caret'></span>"}
                                {foreach $oBox->getManufacturers() as $manufacturer}
                                    {dropdownitem href=$manufacturer->getURL()}
                                        {$manufacturer->getName()|escape:'html'}
                                    {/dropdownitem}
                                {/foreach}
                            {/dropdown}
                        {/block}
                    {else}
                        {block name='boxes-box-manufacturers-link'}
                            {nav vertical=true}
                                {foreach $oBox->getManufacturers() as $manufacturer}
                                    {navitem href=$manufacturer->getURL() title=$manufacturer->getName()|escape:'html'}
                                        {$manufacturer->getName()|escape:'html'}
                                    {/navitem}
                                {/foreach}
                            {/nav}
                        {/block}
                    {/if}
                {/collapse}
            {/block}
            {block name='boxes-box-manufacturers-hr-end'}
                <hr class="box-normal-hr">
            {/block}
        {/block}
    </div>
{/block}
