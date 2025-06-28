<div role="alert"
    {assign var=alertClass value='border-l-4 p-4 rounded '}
    {if $params.variant->hasValue()}
        {if $params.variant == 'primary'}
            {assign var=alertClass value="$alertClass bg-blue-100 border-blue-500 text-blue-700"}
        {elseif $params.variant == 'secondary'}
            {assign var=alertClass value="$alertClass bg-gray-100 border-gray-500 text-gray-700"}
        {elseif $params.variant == 'danger'}
            {assign var=alertClass value="$alertClass bg-red-100 border-red-500 text-red-700"}
        {else}
            {assign var=alertClass value="$alertClass bg-gray-100 border-gray-500 text-gray-700"}
        {/if}
    {else}
        {assign var=alertClass value="$alertClass bg-gray-100 border-gray-500 text-gray-700"}
    {/if}
    class="{$alertClass}{if $params.class->hasValue()} {$params.class->getValue()}{/if}{if $params.dismissible->getValue() === true} pr-8{/if}"
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
    {if $params.attribs->hasValue()}
        {foreach $params.attribs->getValue() as $key => $val} {$key}="{$val}" {/foreach}
    {/if}>
{if $params.dismissible->getValue() === true}
    <button type="button" aria-label="{$params['dismiss-label']->getValue()}" class="absolute top-0 right-0 mt-2 mr-2" onclick="this.parentElement.remove()">
        <span aria-hidden="true">&times;</span>
    </button>
{/if}
    {if $params.variant->hasValue()}<div class="sr-only">x</div>{/if}
    {$blockContent}
</div>
