{$class = "grid grid-cols-12 gap-4 {$params.class->getValue()}"}
{if $params['align-h']->hasValue()}
    {$class = "{$class} justify-{$params['align-h']->getValue()}"}
{/if}
{if $params['align-v']->hasValue()}
    {$class = "{$class} items-{$params['align-v']->getValue()}"}
{/if}


<{$params.tag->getValue()}
    class="{$class}"
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
    {if $params.attribs->hasValue()}
        {foreach $params.attribs->getValue() as $key => $val} {$key}="{$val}" {/foreach}
    {/if}
>
    {$blockContent}
</{$params.tag->getValue()}>
