<style type="text/css">
    .krajee-default .file-size-info, .krajee-default .file-caption-info { width: 100%; }
</style>
{include file='tpl_inc/seite_header.tpl' cTitel=__('news') cBeschreibung=__('newsDesc')}
<div id="content">
    <form name="news" method="post" action="{$adminURL}{$route}" enctype="multipart/form-data" class="hide-fileinput-remove">
        {$jtl_token}
        <input type="hidden" name="news" value="1" />
        <input type="hidden" name="news_speichern" value="1" />
        <input type="hidden" name="tab" value="aktiv" />
        {if $oNews->getID() > 0}
            <input type="hidden" name="news_edit_speichern" value="1" />
            <input type="hidden" name="kNews" value="{$oNews->getID()}" />
            {if isset($cSeite)}
                <input type="hidden" name="s2" value="{$cSeite}" />
            {/if}
        {/if}
        <div class="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{if $oNews->getID() > 0}{__('edit')} (ID {$oNews->getID()}){else}{__('newAdd')}{/if}</div>
                    <hr class="mb-n3">
                </div>
                <div class="table-responsive">
                    <div id="formtable" class="card-body">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="kkundengruppe">{__('customerGroup')} *:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="kkundengruppe"
                                        name="kKundengruppe[]"
                                        multiple="multiple"
                                        class="selectpicker custom-select{if !empty($validation.kKundengruppe_arr)} error{/if}"
                                        data-selected-text-format="count > 2"
                                        data-size="7">
                                    <option value="-1"
                                        {if isset($cPostVar_arr.kKundengruppe)}
                                            {foreach $cPostVar_arr.kKundengruppe as $kKundengruppe}
                                                {if $kKundengruppe == '-1'}selected{/if}
                                            {/foreach}
                                        {else}
                                            {foreach $oNews->getCustomerGroups() as $kKundengruppe}
                                                {if $kKundengruppe === -1}selected{/if}
                                            {/foreach}
                                        {/if}>
                                        {__('all')}
                                    </option>
                                    <option data-divider="true"></option>
                                    {foreach $customerGroups as $customerGroup}
                                        <option value="{$customerGroup->getID()}"
                                            {if isset($cPostVar_arr.kKundengruppe)}
                                                {foreach $cPostVar_arr.kKundengruppe as $kKundengruppe}
                                                    {if $customerGroup->getID() == $kKundengruppe}selected{/if}
                                                {/foreach}
                                            {else}
                                                {foreach $oNews->getCustomerGroups() as $kKundengruppe}
                                                    {if $customerGroup->getID() === $kKundengruppe}selected{/if}
                                                {/foreach}
                                            {/if}>{$customerGroup->getName()}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="kNewsKategorie">{__('category')} *:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="kNewsKategorie"
                                        class="selectpicker custom-select{if !empty($validation.kNewsKategorie_arr)} error{/if}"
                                        name="kNewsKategorie[]"
                                        multiple="multiple"
                                        data-selected-text-format="count > 2"
                                        data-size="7"
                                        data-live-search="true"
                                        data-actions-box="true">
                                    {foreach $newsCategories as $category}
                                        <option value="{$category->getID()}"
                                            {if isset($cPostVar_arr.kNewsKategorie)}
                                                {foreach $cPostVar_arr.kNewsKategorie as $kNewsKategorieNews}
                                                    {if $category->getID() == $kNewsKategorieNews}selected{/if}
                                                {/foreach}
                                            {else}
                                                {foreach $oNews->getCategoryIDs() as $categoryID}
                                                    {if $category->getID() === $categoryID}selected{/if}
                                                {/foreach}
                                            {/if}>{$category->getName()}</option>
                                        {foreach $category->getChildren() as $category}
                                            <option value="{$category->getID()}"
                                                {if isset($cPostVar_arr.kNewsKategorie)}
                                                    {foreach $cPostVar_arr.kNewsKategorie as $kNewsKategorieNews}
                                                        {if $category->getID() == $kNewsKategorieNews}selected{/if}
                                                    {/foreach}
                                                {else}
                                                    {foreach $oNews->getCategoryIDs() as $categoryID}
                                                        {if $category->getID() === $categoryID}selected{/if}
                                                    {/foreach}
                                                {/if}>&nbsp;&nbsp;&nbsp;{$category->getName()}</option>
                                        {/foreach}
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="dGueltigVon">{__('newsValidation')} *:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" id="dGueltigVon" name="dGueltigVon" type="text" value="{if isset($cPostVar_arr.dGueltigVon) && $cPostVar_arr.dGueltigVon}{$cPostVar_arr.dGueltigVon}{else}{$oNews->getDateValidFrom()->format('d.m.Y H:i')}{/if}" />
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nAktiv">{__('active')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="custom-select" id="nAktiv" name="nAktiv">
                                    <option value="1"{if isset($cPostVar_arr.nAktiv)}{if $cPostVar_arr.nAktiv == 1} selected{/if}{elseif $oNews->getIsActive() === true} selected{/if}>{__('yes')}</option>
                                    <option value="0"{if isset($cPostVar_arr.nAktiv)}{if $cPostVar_arr.nAktiv == 0} selected{/if}{elseif $oNews->getIsActive() === false} selected{/if}>{__('no')}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right"for="kAuthor">{__('newsAuthor')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="custom-select" id="kAuthor" name="kAuthor">
                                    <option value="0">{if count($oPossibleAuthors_arr) > 0}{__('selectAuthor')}{else}{__('authorNotAvailable')}{/if}</option>
                                    {foreach $oPossibleAuthors_arr as $oPossibleAuthor}
                                        <option value="{$oPossibleAuthor->kAdminlogin}"{if isset($cPostVar_arr.nAuthor)}{if isset($cPostVar_arr.nAuthor) && $cPostVar_arr.nAuthor == $oPossibleAuthor->kAdminlogin} selected="selected"{/if}{elseif isset($oAuthor->kAdminlogin) && $oAuthor->kAdminlogin == $oPossibleAuthor->kAdminlogin} selected="selected"{/if}>{$oPossibleAuthor->cName}</option>
                                    {/foreach}
                                </select>
                            </div>
                            {if count($oPossibleAuthors_arr) === 0}
                                <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                    <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('noNewsAuthor')}">
                                        <span class="fas fa-info-circle fa-fw"></span>
                                    </span>
                                </div>
                            {/if}
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="previewImage">{__('preview')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                {include file='tpl_inc/fileupload.tpl'
                                    fileID='previewImage'
                                    fileMaxSize={$nMaxFileSize}
                                    fileInitialPreview="[
                                            {if !empty($oNews->getPreviewImage())}
                                            '<img src=\"{$shopURL}/{$oNews->getPreviewImage()}\" class=\"preview-image\"/><a class=\"d-block\" href=\"{$adminURL}{$route}?news=1&news_editieren=1&kNews={$oNews->getID()}&delpic={$oNews->getPreviewImageBaseName()}&token={$smarty.session.jtl_token}\" title=\"{__('delete')}\"><i class=\"fas fa-trash-alt\"></i></a>',
                                            {/if}
                                        ]"
                                    fileInitialPreviewConfig="[
                                            {if !empty($oNews->getPreviewImage())}
                                            {
                                                caption: '$#preview#$',
                                                width:   '120px'
                                            }
                                            {/if}
                                        ]"
                                }
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right">{__('newsPics')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                {include file='tpl_inc/fileupload.tpl'
                                    fileID='images'
                                    fileName='Bilder[]'
                                    fileMaxSize={$nMaxFileSize}
                                    fileIsSingle=false
                                    fileInitialPreview="[
                                            {foreach $files as $file}
                                            '<img src=\"{$file->cURLFull}\" class=\"file-preview-image img-fluid\"/><a class=\"d-block\" href=\"{$adminURL}{$route}?news=1&news_editieren=1&kNews={$oNews->getID()}&delpic={$file->cName}&token={$smarty.session.jtl_token}\" title=\"{__('delete')}\"><i class=\"fas fa-trash-alt\"></i></a>',
                                            {/foreach}
                                        ]"
                                    fileInitialPreviewConfig="[
                                            {foreach $files as $file}
                                            {
                                                caption: '$#{$file->cName}#$',
                                                width:   '120px'
                                            },
                                            {/foreach}
                                        ]"
                                }
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <nav class="tabs-nav">
                <ul class="nav nav-tabs" role="tablist">
                    {foreach $availableLanguages as $i => $language}
                        <li class="nav-item">
                            <a class="nav-link {if $i === 0}active{/if}" data-toggle="tab" role="tab"
                               href="#lang_{$language->getIso()}" aria-expanded="false">
                                {$language->getLocalizedName()}
                                {if $language->getShopDefault() === 'Y'}({__('standard')}){/if}
                            </a>
                        </li>
                    {/foreach}
                </ul>
            </nav>
            <div class="tab-content">
                {foreach $availableLanguages as $i => $language}
                    <div id="lang_{$language->getIso()}"
                         class="tab-pane fade {if $i === 0}active show{/if}">
                        {$cISO   = $language->getIso()}
                        {$langID = $language->getId()}
                        <input type="hidden" name="lang_{$cISO}" value="{$langID}">
                        <div id="iso_{$cISO}" class="iso_wrapper">
                            <div class="card">
                                <div class="card-header">
                                    <div class="subheading1">{__('metaSeo')} ({$language->getLocalizedName()})</div>
                                    <hr class="mb-n3">
                                </div>
                                <div class="card-body">
                                    <div class="form-group form-row align-items-center">
                                        {$nameVariable = 'cName_'|cat:$cISO}
                                        <label class="col col-sm-4 col-form-label text-sm-right"
                                               for="{$nameVariable}">
                                            {__('headline')}:
                                        </label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <input class="form-control{if !empty($validation.cBetreff)} error{/if}"
                                                   id="{$nameVariable}" type="text" name="{$nameVariable}"
                                                   value="{if empty($cPostVar_arr.$nameVariable) === false}{$cPostVar_arr.$nameVariable}{else}{$oNews->getTitle($langID)}{/if}" />
                                        </div>
                                    </div>
                                    <div class="form-group form-row align-items-center">
                                        {$seoVariable = 'cSeo_'|cat:$cISO}
                                        <label class="col col-sm-4 col-form-label text-sm-right"
                                               for="{$seoVariable}">{__('newsSeo')}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <input id="{$seoVariable}" name="{$seoVariable}" class="form-control"
                                                   type="text"
                                                   value="{if empty($cPostVar_arr.$seoVariable) === false}{$cPostVar_arr.$seoVariable}{else}{$oNews->getSEO($langID)}{/if}" />
                                        </div>
                                    </div>
                                    <div class="form-group form-row align-items-center">
                                        {$titleVariable = 'cMetaTitle_'|cat:$cISO}
                                        <label class="col col-sm-4 col-form-label text-sm-right"
                                               for="{$titleVariable}">{__('newsMetaTitle')}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <input class="form-control" id="{$titleVariable}"
                                                   name="{$titleVariable}" type="text"
                                                   value="{if empty($cPostVar_arr.$titleVariable) === false}{$cPostVar_arr.$titleVariable}{else}{$oNews->getMetaTitle($langID)}{/if}" />
                                        </div>
                                    </div>
                                    <div class="form-group form-row align-items-center">
                                        {$descriptionVariable = 'cMetaDescription_'|cat:$cISO}
                                        <label class="col col-sm-4 col-form-label text-sm-right"
                                               for="{$descriptionVariable}">{__('newsMetaDescription')}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <input id="{$descriptionVariable}" class="form-control"
                                                   name="{$descriptionVariable}" type="text"
                                                   value="{if empty($cPostVar_arr.$descriptionVariable) === false}{$cPostVar_arr.$descriptionVariable}{else}{$oNews->getMetaDescription($langID)}{/if}" />
                                        </div>
                                    </div>
                                    <div class="form-group form-row align-items-center">
                                        {$keywordsVariable = 'cMetaKeywords_'|cat:$cISO}
                                        <label class="col col-sm-4 col-form-label text-sm-right"
                                               for="{$keywordsVariable}">{__('newsMetaKeywords')}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <input class="form-control" id="{$keywordsVariable}"
                                                   name="{$keywordsVariable}" type="text"
                                                   value="{if empty($cPostVar_arr.$keywordsVariable) === false}{$cPostVar_arr.$keywordsVariable}{else}{$oNews->getMetaKeyword($langID)}{/if}" />
                                        </div>
                                    </div>
                                    <div class="form-group form-row align-items-center">
                                        <label class="col col-sm-4 col-form-label text-sm-right"
                                               for="newstext_{$cISO}">{__('text')}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            {$textVariable = 'text_'|cat:$cISO}
                                            {if empty($cPostVar_arr.$textVariable) === false}
                                                {$textareaContent = $cPostVar_arr.$textVariable}
                                            {else}
                                                {$textareaContent = $oNews->getContent($langID)}
                                            {/if}
                                            <textarea id="newstext_{$cISO}" class="tinymce" name="{$textVariable}"
                                                      rows="15" cols="60">{htmlentities($textareaContent)}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group form-row align-items-center">
                                        <label class="col col-sm-4 col-form-label text-sm-right"
                                               for="previewtext_{$cISO}">{__('newsPreviewText')}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            {$previewTextVariable = 'cVorschauText_'|cat:$cISO}
                                            {if empty($cPostVar_arr.$previewTextVariable) === false}
                                                {$textareaConent = $cPostVar_arr.$previewTextVariable}
                                            {else}
                                                {$textareaConent = $oNews->getPreview($langID)}
                                            {/if}
                                            <textarea id="previewtext_{$cISO}" class="tinymce"
                                                      name="{$previewTextVariable}" rows="15"
                                                      cols="60">{htmlentities($textareaConent)}</textarea>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">{__('newsMandatoryFields')}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a class="btn btn-outline-primary btn-block" href="{$adminURL}{$route}{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}">
                            <i class="fa fa-exclamation"></i> {__('Cancel')}
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        {include file='snippets/buttons/saveAndContinueButton.tpl' value='news'}
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {if $oNews->getID() > 0}
        {getRevisions type='news' key=$oNews->getID() show=['content'] secondary=true data=$oNews->getData()}
    {/if}
</div>
