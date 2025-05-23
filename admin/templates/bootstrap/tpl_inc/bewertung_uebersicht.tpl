{include file='tpl_inc/seite_header.tpl' cTitel=__('votesystem') cBeschreibung=__('votesystemDesc') cDokuURL=__('votesystemURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/language_switcher.tpl' action=$adminURL|cat:$route}
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6 col-xl-auto">
                    {include file='tpl_inc/csv_export_btn.tpl' exporterId='exportRatings'}
                </div>
                <div class="col-sm-6 col-xl-auto">
                    {include file='tpl_inc/csv_import_btn.tpl' importerId='importRatings'}
                </div>
            </div>
        </div>
    </div>

    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'freischalten'} active{/if}" data-toggle="tab" role="tab" href="#freischalten">
                        {__('ratingsInaktive')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'letzten50'} active{/if}" data-toggle="tab" role="tab" href="#letzten50">
                        {__('ratingLast50')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'artikelbewertung'} active{/if}" data-toggle="tab" role="tab" href="#artikelbewertung">
                        {__('ratingForProduct')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#einstellungen">
                        {__('settings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="freischalten" class="tab-pane fade {if $cTab === '' || $cTab === 'freischalten'} active show{/if}">
                {if count($inactiveReviews) > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiInaktiv cAnchor='freischalten'}
                    <form method="post" action="{$adminURL}{$route}">
                        {$jtl_token}
                        <input type="hidden" name="bewertung_nicht_aktiv" value="1" />
                        <input type="hidden" name="tab" value="freischalten" />
                        <div>
                            <div class="subheading1">{__('ratingsInaktive')}</div>
                            <hr class="mb-3">
                            <div class="table-responsive">
                                <table  class="table table-striped table-align-top">
                                    <thead>
                                    <tr>
                                        <th class="check">&nbsp;</th>
                                        <th class="text-left">{__('productName')}</th>
                                        <th class="text-left">{__('customerName')}</th>
                                        <th class="text-left">{__('ratingText')}</th>
                                        <th class="th-5 text-center">{__('ratingStars')}</th>
                                        <th class="th-6 text-center">{__('date')}</th>
                                        <th class="th-7 text-center">&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $inactiveReviews as $review}
                                            <tr>
                                                <td class="check">
                                                    <input type="hidden" name="kArtikel[{$review@index}]" value="{$review->kArtikel}"/>
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" name="kBewertung[{$review@index}]" type="checkbox" value="{$review->kBewertung}" id="inactive-{$review->kBewertung}" />
                                                        <label class="custom-control-label" for="inactive-{$review->kBewertung}"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label for="inactive-{$review->kBewertung}">{$review->ArtikelName}</label>
                                                    &nbsp;<a href="{$shopURL}/?a={$review->kArtikel}" target="_blank"><i class="fas fa fa-external-link"></i></a>
                                                </td>
                                                <td>{$review->cName}.</td>
                                                <td><b>{$review->cTitel}</b><br />{$review->cText}</td>
                                                <td class="text-center">{$review->nSterne}</td>
                                                <td class="text-center">{$review->Datum}</td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="{$adminURL}{$route}?a=editieren&kBewertung={$review->kBewertung}&tab=freischalten&token={$smarty.session.jtl_token}"
                                                           class="btn btn-link px-2"
                                                           title="{__('modify')}"
                                                           data-toggle="tooltip">
                                                            <span class="icon-hover">
                                                                <span class="fal fa-edit"></span>
                                                                <span class="fas fa-edit"></span>
                                                            </span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer save-wrapper">
                                <div class="row">
                                    <div class="col-sm-4 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                            <label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-4 col-xl-auto">
                                        <button name="action" type="submit" value="delete" class="btn btn-danger btn-block">
                                            <i class="fas fa-trash-alt"></i> {__('delete')}
                                        </button>
                                    </div>
                                    <div class="col-sm-4 col-xl-auto">
                                        <button name="action" type="submit" value="activate" class="btn btn-primary btn-block">
                                            <i class="fa fa-thumbs-up"></i> {__('activate')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiInaktiv cAnchor='freischalten' isBottom=true}
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="letzten50" class="tab-pane fade {if $cTab === 'letzten50'} active show{/if}">
                {if count($activeReviews) > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiAktiv cAnchor='letzten50'}
                    <form name="letzten50" method="post" action="{$adminURL}{$route}">
                        {$jtl_token}
                        <input type="hidden" name="bewertung_aktiv" value="1" />
                        <input type="hidden" name="tab" value="letzten50" />
                        <div>
                            <div class="subheading1">{__('ratingLast50')}</div>
                            <hr class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-striped table-align-top">
                                    <thead>
                                    <tr>
                                        <th class="check">&nbsp;</th>
                                        <th class="text-left">{__('productName')}</th>
                                        <th class="text-left">{__('customerName')}</th>
                                        <th class="text-left">{__('ratingText')}</th>
                                        <th class="th-5 text-center">{__('ratingStars')}</th>
                                        <th class="th-6 text-center">{__('date')}</th>
                                        <th class="th-7 text-center min-w-sm">{__('actions')}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $activeReviews as $review}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kBewertung[]" type="checkbox" value="{$review->kBewertung}" id="l50-{$review->kBewertung}">
                                                    <label class="custom-control-label" for="l50-{$review->kBewertung}"></label>
                                                </div>
                                                <input type="hidden" name="kArtikel[]" value="{$review->kArtikel}">
                                            </td>
                                            <td>
                                                <label for="l50-{$review->kBewertung}">{$review->ArtikelName}</label>
                                            </td>
                                            <td>{$review->cName}</td>
                                            <td>
                                                <strong>{$review->cTitel}</strong><br>
                                                {$review->cText}
                                                {if !empty($review->cAntwort)}
                                                    <blockquote class="review-reply">
                                                        <strong>{__('ratingReply')}</strong><br>
                                                        {$review->cAntwort}
                                                    </blockquote>
                                                {/if}
                                            </td>
                                            <td class="text-center">{$review->nSterne}</td>
                                            <td class="text-center">{$review->Datum}</td>
                                            <td class="text-center">
                                                {if !empty($review->cAntwort)}
                                                    <a href="{$adminURL}{$route}?a=delreply&kBewertung={$review->kBewertung}&tab=letzten50&token={$smarty.session.jtl_token}"
                                                       class="btn btn-link px-2 delete-confirm"
                                                       title="{__('removeReply')}"
                                                       data-toggle="tooltip"
                                                       data-modal-body="{__('removeReply')} | {$review->ArtikelName}">
                                                        <span class="icon-hover">
                                                            <span class="fal fa-trash-alt"></span>
                                                            <span class="fas fa-trash-alt"></span>
                                                        </span>
                                                    </a>
                                                {/if}
                                                <a class="btn btn-link px-2"
                                                   href="{$shopURL}/?a={$review->kArtikel}"
                                                   target="_blank"
                                                   title="{__('linkItemShop')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-external-link"></span>
                                                        <span class="fas fa-external-link"></span>
                                                    </span>
                                                </a>
                                                <a href="{$adminURL}{$route}?a=editieren&kBewertung={$review->kBewertung}&tab=letzten50&token={$smarty.session.jtl_token}"
                                                   class="btn btn-link px-2"
                                                   title="{__('modify')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-edit"></span>
                                                        <span class="fas fa-edit"></span>
                                                    </span>
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);">
                                            <label class="custom-control-label" for="ALLMSGS3">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="action" type="submit" value="delete" class="btn btn-danger btn-block"><i class="fas fa-trash-alt"></i> {__('deleteSelected')}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiAktiv cAnchor='letzten50' isBottom=true}
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="artikelbewertung" class="tab-pane fade {if $cTab === 'artikelbewertung'} active show{/if}">
                <form name="artikelbewertung" method="post" action="{$adminURL}{$route}">
                    <div class="mb-3">
                        {$jtl_token}
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="content">{__('ratingcArtNr')}:</label>
                            <input type="hidden" name="bewertung_aktiv" value="1" />
                            <input type="hidden" name="tab" value="artikelbewertung" />
                            <div class="col-sm-auto mb-3">
                                <input class="form-control" name="cArtNr" type="text" value="{$cArtNr|default:''}" />
                            </div>
                            <span class="col-sm-auto">
                                <button name="action" type="submit" value="search" class="btn btn-primary btn-block mb-3">
                                    <i class="fal fa-search"></i>
                                </button>
                            </span>
                        </div>
                        {if isset($cArtNr) && $cArtNr|strlen > 0}
                            <div class="alert alert-info">{__('ratingSearchedFor')}: {$cArtNr}</div>
                        {/if}
                        {if !(isset($filteredReviews) && count($filteredReviews) > 0)}
                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                        {/if}
                    </div>
                    {if isset($filteredReviews) && count($filteredReviews) > 0}
                        <div>
                            <div class="subheading1">{$cArtNr}</div>
                            <hr class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-striped table-align-top">
                                    <thead>
                                    <tr>
                                        <th class="th-1">&nbsp;</th>
                                        <th class="text-left">{__('productName')}</th>
                                        <th class="text-left">{__('customerName')}</th>
                                        <th class="text-left">{__('ratingText')}</th>
                                        <th class="th-5 text-center">{__('ratingStars')}</th>
                                        <th class="th-6 text-center">{__('date')}</th>
                                        <th class="th-7 text-center">&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $filteredReviews as $review}
                                        <tr>
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kBewertung[]" type="checkbox" value="{$review->kBewertung}" id="filtered-{$review->kBewertung}">
                                                    <label class="custom-control-label" for="filtered-{$review->kBewertung}"></label>
                                                </div>
                                                <input type="hidden" name="kArtikel[]" value="{$review->kArtikel}">
                                            </td>
                                            <td>
                                                <label for="filtered-{$review->kBewertung}">{$review->ArtikelName}</label>
                                                &nbsp;<a href="{$shopURL}/?a={$review->kArtikel}" target="_blank"><i class="fas fa fa-external-link"></i></a>
                                            </td>
                                            <td>{$review->cName}.</td>
                                            <td><b>{$review->cTitel}</b><br />{$review->cText}</td>
                                            <td class="text-center">{$review->nSterne}</td>
                                            <td class="text-center">{$review->Datum}</td>
                                            <td class="text-center">
                                                <a href="{$adminURL}{$route}?a=editieren&kBewertung={$review->kBewertung}&tab=artikelbewertung"
                                                   class="btn btn-link px-2"
                                                   title="{__('modify')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-edit"></span>
                                                        <span class="fas fa-edit"></span>
                                                    </span>
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);">
                                            <label class="custom-control-label" for="ALLMSGS2">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="action" type="submit" value="delete" class="btn btn-danger btn-block"><i class="fas fa-trash-alt"></i> {__('deleteSelected')}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                </form>
            </div>
            <div id="einstellungen" class="tab-pane fade {if $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl'
                    name='einstellen'
                    action=$adminURL|cat:$route
                    buttonCaption=__('saveWithIcon')
                    title=__('settings')
                    tab='einstellungen'
                }
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    {foreach $sections as $section}
        {foreach $section->getSubsections() as $subsection}
            {foreach $subsection->getItems() as $config}
                {if strpos($config->getValueName(), '_guthaben')}
                    ioCall(
                        'getCurrencyConversion',
                        [0.0, $('#{$config->getValueName()}').val(), 'EinstellungAjax_{$config->getValueName()}'],
                        undefined,
                        undefined,
                        undefined,
                        true
                    );
                {/if}
            {/foreach}
        {/foreach}
    {/foreach}
</script>
