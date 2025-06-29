{block name='snippets-search-form'}
    <div class="search-wrapper w-100-util">
        {form action="{$ShopURL}/" method='get' class='main-search flex-grow-1' slide=true}

            {inputgroup}
                {input id="{$id}" name="qs" type="text" class="ac_input" placeholder="{lang key='search'}" autocomplete="off" aria=["label"=>"{lang key='search'}"]}
                {inputgroupaddon append=true}
                    {button type="submit" name="search" variant="secondary" aria=["label"=>{lang key='search'}]}
                        <span class="fas fa-search"></span>
                    {/button}
                {/inputgroupaddon}
                <span class="form-clear hidden"><i class="fas fa-times"></i></span>
            {/inputgroup}
        {/form}
    </div>
{/block}
