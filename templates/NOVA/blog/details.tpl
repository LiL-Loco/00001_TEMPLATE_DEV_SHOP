{block name='blog-details'}
    {block name='blog-details-include-extension'}
        {include file='snippets/extension.tpl'}
    {/block}

    {container fluid=$Link->getIsFluid() class="blog-details {if $Einstellungen.template.theme.left_sidebar === 'Y' && $boxesLeftActive}container-plus-sidebar{/if}"}
    {if !empty($cNewsErr)}
        {block name='blog-details-alert'}
            {alert variant="danger"}{lang key='newsRestricted' section='news'}{/alert}
        {/block}
    {else}
        {block name='blog-details-article'}
            <article itemprop="mainEntity" itemscope itemtype="https://schema.org/BlogPosting">
                <meta itemprop="mainEntityOfPage" content="{$newsItem->getURL()}">
                {block name='blog-details-heading'}
                    {opcMountPoint id='opc_before_heading'}
                    <h1 itemprop="headline">
                        {$newsItem->getTitle()}
                    </h1>
                {/block}

                {block name='blog-details-author'}
                    <div class="author-meta mb-3">
                        {if empty($newsItem->getDateValidFrom())}
                            {assign var=dDate value=$newsItem->getDateCreated()->format('Y-m-d H:i:s')}
                        {else}
                            {assign var=dDate value=$newsItem->getDateValidFrom()->format('Y-m-d H:i:s')}
                        {/if}
                        {if $newsItem->getAuthor() !== null}
                            {block name='blog-details-include-author'}
                                {include file='snippets/author.tpl' oAuthor=$newsItem->getAuthor() dDate=$dDate cDate=$newsItem->getDateValidFrom()->format('Y-m-d H:i:s')}
                            {/block}
                        {else}
                            {block name='blog-details-noauthor'}
                                <div itemprop="author publisher" itemscope itemtype="https://schema.org/Organization" class="hidden">
                                    <span itemprop="name">{$meta_publisher}</span>
                                    <meta itemprop="logo" content="{$ShopLogoURL}" />
                                </div>
                                <time itemprop="datePublished" datetime="{$dDate}" class="hidden">{$dDate}</time><span class="creation-date">{$newsItem->getDateValidFrom()->format('Y-m-d H:i:s')}</span>
                            {/block}
                        {/if}
                        <time itemprop="datePublished" datetime="{$dDate}" class="hidden">{$dDate}</time>
                        {if isset($newsItem->getDateCreated()->format('Y-m-d H:i:s'))}<time itemprop="dateModified" class="hidden">{$newsItem->getDateCreated()->format('Y-m-d H:i:s')}</time>{/if}

                        {if isset($Einstellungen.news.news_kategorie_unternewsanzeigen) && $Einstellungen.news.news_kategorie_unternewsanzeigen === 'Y' && !empty($oNewsKategorie_arr)}
                            {block name='blog-details-sub-news'}
                                <span class="news-categorylist">
                                    {if $newsItem->getAuthor() === null}/{/if}
                                    {foreach $oNewsKategorie_arr as $newsCategory}
                                        {link itemprop="articleSection"
                                            href="{$newsCategory->getURL()}"
                                            title="{$newsCategory->getDescription()|strip_tags|escape:'html'|truncate:60}"
                                            class="{if !$newsCategory@last}mr-1{/if} d-inline-block"
                                        }
                                            {$newsCategory->getName()}
                                        {/link}
                                    {/foreach}
                                </span>
                            {/block}
                        {/if}

                        {block name='blog-details-comments-link'}
                            {if $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                            {link class="text-decoration-none-util text-nowrap-util" href="#comments" title="{lang key='readComments' section='news'}"}
                                /
                                <span class="fas fa-comments"></span>
                                <span class="sr-only">
                                    {if $newsItem->getCommentCount() === 1}
                                        {lang key='newsComment' section='news'}
                                    {else}
                                        {lang key='newsComments' section='news'}
                                    {/if}
                                </span>
                                <span itemprop="commentCount">
                                    {$newsItem->getCommentCount()}
                                    {if $newsItem->getChildCommentsCount()  && $Einstellungen.news.news_kommentare_anzahl_antwort_kommentare_anzeigen === 'Y'}
                                        ({$newsItem->getChildCommentsCount()})
                                    {/if}
                                </span>
                            {/link}
                            {/if}
                        {/block}
                    </div>
                {/block}

                {block name='blog-details-article-content'}
                    {opcMountPoint id='opc_before_content'}
                    {row itemprop="articleBody" class="blog-details-content"}
                        {col cols=12}
                            {$newsItem->getContent()}
                        {/col}
                    {/row}
                    {opcMountPoint id='opc_after_content'}
                {/block}
                {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                    {block name='blog-details-article-comments'}
                        {if $userCanComment === true}
                            {block name='blog-details-form-comment'}
                                {block name='blog-details-form-comment-hr-top'}
                                    <hr class="blog-details-hr">
                                {/block}
                                {row}
                                    {col cols=12}
                                        {block name='blog-details-form-comment-heading'}
                                            <div class="h2">{lang key='newsCommentAdd' section='news'}</div>
                                        {/block}
                                        {block name='blog-details-form-comment-form'}
                                            {form method="post"
                                                action="{if !empty($newsItem->getSEO())}{$newsItem->getURL()}{else}{get_static_route id='news.php'}{/if}"
                                                class="form jtl-validate"
                                                id="news-addcomment"
                                                addhoneypot=true
                                                slide=true}
                                                {input type="hidden" name="kNews" value=$newsItem->getID()}
                                                {input type="hidden" name="kommentar_einfuegen" value="1"}
                                                {input type="hidden" name="n" value=$newsItem->getID()}
                                                <div class="required-info">{lang key='requiredInfo'}</div>
                                                {block name='blog-details-form-comment-logged-in'}
                                                    {formgroup
                                                        id="commentText"
                                                        class="{if $nPlausiValue_arr.cKommentar > 0} has-error{/if}"
                                                        label="<strong>{lang key='newsComment' section='news'}</strong>"
                                                        label-for="comment-text"
                                                        label-class="commentForm"
                                                    }
                                                        {if $nPlausiValue_arr.cKommentar > 0}
                                                            <div class="form-error-msg"><i class="fas fa-exclamation-triangle"></i>
                                                                {lang key='fillOut' section='global'}
                                                            </div>
                                                        {/if}
                                                        {if $Einstellungen.news.news_kommentare_freischalten === 'Y'}
                                                            <small class="form-text text-muted-util">{lang key='commentWillBeValidated' section='news'}</small>
                                                        {/if}
                                                        {textarea id="comment-text" name="cKommentar" required=true}{/textarea}
                                                    {/formgroup}
                                                    {row}
                                                        {col md=4 xl=3 class='blog-details-save'}
                                                            {button block=true variant="primary" name="speichern" type="submit"}
                                                                {lang key='newsCommentSave' section='news'}
                                                            {/button}
                                                        {/col}
                                                    {/row}
                                                {/block}
                                            {/form}
                                        {/block}
                                    {/col}
                                {/row}
                            {/block}
                        {else}
                            {block name='blog-details-alert-login'}
                                {alert variant="warning"}{lang key='newsLogin' section='news'}{/alert}
                            {/block}
                        {/if}
                        {if $comments|count > 0}
                            {block name='blog-details-comments-content'}
                                {if $newsItem->getURL() !== ''}
                                    {assign var=articleURL value=$newsItem->getURL()}
                                    {assign var=cParam_arr value=[]}
                                {else}
                                    {assign var=articleURL value='news.php'}
                                    {assign var=cParam_arr value=['kNews'=>$newsItem->getID(),'n'=>$newsItem->getID()]}
                                {/if}
                                {block name='blog-details-form-comment-hr-middle'}
                                    <hr class="blog-details-hr">
                                {/block}
                                <div id="comments">
                                    {row class="blog-comments-header"}
                                        {col cols="auto"}
                                            {block name='blog-details-comments-content-heading'}
                                                <div class="h2 section-heading">{lang key='newsComments' section='news'}
                                                    <span itemprop="commentCount">
                                                        {$newsItem->getCommentCount()}
                                                        {if $newsItem->getChildCommentsCount() && $Einstellungen.news.news_kommentare_anzahl_antwort_kommentare_anzeigen === 'Y'}
                                                            ({$newsItem->getChildCommentsCount()})
                                                        {/if}
                                                    </span>
                                                </div>
                                            {/block}
                                        {/col}
                                        {col cols="12" md=6 class="ml-auto-util"}
                                            {block name='blog-details-include-pagination'}
                                                {include file='snippets/pagination.tpl' oPagination=$oPagiComments cThisUrl=$articleURL cParam_arr=$cParam_arr noWrapper=true}
                                            {/block}
                                        {/col}
                                    {/row}
                                    {block name='blog-details-comments'}
                                        {listgroup class="blog-details-comments-list list-group-flush"}
                                            {foreach $comments as $comment}
                                                {listgroupitem class="blog-details-comments-list-item" itemprop="comment" itemscope=true itemtype="https://schema.org/Comment"}
                                                    <div itemprop="text">
                                                        <p>{$comment->getName()}, {$comment->getDateCreated()->format('d.m.y H:i')}</p>
                                                        {$comment->getText()}
                                                    </div>
                                                     {foreach $comment->getChildComments() as $childComment}
                                                        <div class="review-reply">
                                                            <span class="subheadline">{lang key='commentReply' section='news'}:</span>
                                                            <blockquote>
                                                                {$childComment->getText()}
                                                                <div class="blockquote-footer">{$childComment->getName()}, {$childComment->getDateCreated()->format('d.m.y H:i')}</div>
                                                            </blockquote>
                                                        </div>
                                                     {/foreach}
                                                {/listgroupitem}
                                            {/foreach}
                                        {/listgroup}
                                    {/block}
                                </div>
                            {/block}
                        {/if}
                    {/block}
                {/if}
            </article>
            {if $oNews_arr|count > 0}
            {block name='blog-details-form-comment-hr-bottom'}
                <hr class="blog-details-hr">
            {/block}
            {block name='blog-details-latest-news'}
                <div class="h2">{lang key='news' section='news'}</div>
                <div itemprop="about"
                    itemscope
                    itemtype="https://schema.org/Blog"
                    class="carousel carousel-arrows-inside mx-0 slick-lazy slick-type-three {if $oNews_arr|count < 3}slider-no-preview{/if}"
                    data-slick-type="slider-three">
                    {include file='snippets/slider_items.tpl' items=$oNews_arr type='news'}
                </div>
            {/block}
            {/if}
        {/block}
    {/if}
    {/container}
{/block}
