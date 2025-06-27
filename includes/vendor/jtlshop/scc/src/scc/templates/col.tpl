{$class = 'col-span-12 '|cat:$params.class->getValue()}
{if $params.cols->hasValue()}
    {$class = "{$class} col-span-{$params.cols->getValue()}"}
{/if}
{if $params.sm->getValue() !== false}
    {$val = $params.sm->getValue() === true ? 12 : $params.sm->getValue()}
    {$class = "{$class} sm:col-span-{$val}"}
{/if}
{if $params.md->getValue() !== false}
    {$val = $params.md->getValue() === true ? 12 : $params.md->getValue()}
    {$class = "{$class} md:col-span-{$val}"}
{/if}
{if $params.lg->getValue() !== false}
    {$val = $params.lg->getValue() === true ? 12 : $params.lg->getValue()}
    {$class = "{$class} lg:col-span-{$val}"}
{/if}
{if $params.xl->getValue() !== false}
    {$val = $params.xl->getValue() === true ? 12 : $params.xl->getValue()}
    {$class = "{$class} xl:col-span-{$val}"}
{/if}
{if $params.order->hasValue()}
    {$class = "{$class} order-{$params.order->getValue()}"}
{/if}

<{$params.tag->getValue()}
    class="{$class}"
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.aria->hasValue()}
        {foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}
    {/if}
    {if $params.data->hasValue()}
        {foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}
    {/if}
    {if $params.attribs->hasValue()}
        {foreach $params.attribs->getValue() as $key => $val} {$key}="{$val}" {/foreach}
    {/if}
>
{$blockContent}
</{$params.tag->getValue()}>
