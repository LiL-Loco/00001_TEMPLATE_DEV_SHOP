{block name='blog-preview'}
    {$title = $newsItem->getTitle()|escape:'quotes'}
    <div itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting" class="newsbox blog-preview">
        <meta itemprop="mainEntityOfPage" content="{$newsItem->getURL()}">
        {block name='blog-preview-news-header'}
            <div class="newsbox-header">
                {if !empty($newsItem->getPreviewImage())}
                    {block name='blog-preview-news-image'}
                        {link href=$newsItem->getURL() title=$title tabindex="-1" class="preview-image"}
                            <div class="newsbox-image">
                                {include file='snippets/image.tpl'
                                    item =$newsItem
                                    square = false
                                    sizes = '(min-width: 1300px) 25vw, (min-width: 992px) 38vw, (min-width: 768px) 55vw, 100vw'
                                    alt="{$title} - {$newsItem->getMetaTitle()|escape:'quotes'}"}
                            </div>
                            <meta itemprop="image" content="{$imageBaseURL}{$newsItem->getPreviewImage()}">
                        {/link}
                    {/block}
                {/if}
            </div>
        {/block}
        {block name='blog-preview-news-body'}
            <div class="newsbox-body">
                {row}
                    {col cols=8 class="blog-preview-author"}
                        {assign var=dDate value=$newsItem->getDateValidFrom()->format('Y-m-d')}
                        {block name='blog-preview-author'}
                            {if $newsItem->getAuthor() !== null}
                                <div class="newsbox-author">
                                    {block name='blog-preview-include-author'}
                                        {include file='snippets/author.tpl' oAuthor=$newsItem->getAuthor() showModal=false}
                                    {/block}
                                </div>
                            {else}
                                <div itemprop="author publisher" itemscope itemtype="https://schema.org/Organization" class="hidden">
                                    <span itemprop="name">{$meta_publisher}</span>
                                    <meta itemprop="url" content="{$ShopURL}">
                                    <meta itemprop="logo" content="{$ShopLogoURL}">
                                </div>
                            {/if}
                            <time itemprop="dateModified" class="hidden">{$newsItem->getDateCreated()->format('Y-m-d')}</time>
                            <time itemprop="datePublished" datetime="{$dDate}" class="hidden">{$dDate}</time>
                            <span class="align-middle">{$newsItem->getDateValidFrom()->format('d.m.Y')}</span>
                        {/block}
                    {/col}
                    {col cols="auto" class="blog-preview-comment"}
                        {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                            {block name='blog-preview-comments'}
                                {link href=$newsItem->getURL()|cat:'#comments'}
                                    <span class="fas fa-comments"></span>
                                    <span itemprop="commentCount">
                                        {$newsItem->getCommentCount()}
                                        {if $newsItem->getChildCommentsCount() && $Einstellungen.news.news_kommentare_anzahl_antwort_kommentare_anzeigen === 'Y'}
                                            ({$newsItem->getChildCommentsCount()})
                                        {/if}
                                    </span>
                                    <span class="sr-only">
                                        {if $newsItem->getCommentCount() === 1}
                                            {lang key='newsCommentForPosting' section='news' printf=$title}
                                        {else}
                                            {lang key='newsCommentsForPosting' section='news' printf=$title}
                                        {/if}
                                    </span>
                                {/link}
                            {/block}
                        {/if}
                    {/col}
                {/row}
                {block name='blog-preview-heading'}
                    {link itemprop="url" href=$newsItem->getURL() title=$title class="newsbox-headline"}
                        <span itemprop="headline">{$title}</span>
                    {/link}
                {/block}
                {block name='blog-preview-description'}
                    <p itemprop="description">
                        {if $newsItem->getPreview()|strip_tags|strlen > 0}
                            {$newsItem->getPreview()|strip_tags}
                        {else}
                            {$newsItem->getContent()|strip_tags|truncate:200:''}
                        {/if}
                    </p>
                {/block}
            </div>
        {/block}
        {block name='blog-preview-news-footer'}
            <div class="newsbox-footer">
                {link href=$newsItem->getURL() title=$title}
                    {lang key='moreLink' section='news'}
                    <i class="fas fa-long-arrow-alt-right icon-mr-2"></i>
                {/link}
            </div>
        {/block}
    </div>
{/block}
