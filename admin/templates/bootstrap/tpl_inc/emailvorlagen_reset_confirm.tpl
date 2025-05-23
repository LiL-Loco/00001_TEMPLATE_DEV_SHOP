{include file='tpl_inc/seite_header.tpl' cTitel=__('emailTemplates')}
<div id="content">
    <div class="card">
        <div class="card-body">
            <form method="post" action="{$adminURL}{$route}">
                {$jtl_token}
                <input type="hidden" name="resetEmailvorlage" value="1">
                {if isset($mailTemplate) && $mailTemplate->getPluginID() > 0}
                    <input type="hidden" name="kPlugin" value="{$mailTemplate->getPluginID()}">
                {/if}
                {if isset($emailTemplateIDsToReset)}
                    {foreach $emailTemplateIDsToReset as $kEmailvorlageID}
                        <input type="hidden" name="kEmailvorlage[]" value="{$kEmailvorlageID}">
                    {/foreach}
                {else}
                    <input type="hidden" name="kEmailvorlage" value="{$mailTemplate->getID()}">
                {/if}
                <div class="alert alert-danger">
                    {if isset($mailTemplate)}
                        <p><strong>{__('danger')}</strong>: {__('resetEmailTemplate')}</p>
                        <p>{sprintf(__('sureResetEmailTemplate'), __('name_'|cat:$mailTemplate->getModuleID()))}</p>
                    {else}
                        <p><strong>{__('danger')}</strong>:
                            {if $resetAllTemplates === true}
                                {__('resetAllEmailTemplates')}
                            {else}
                                {__('resetEmailTemplates')}
                            {/if}
                        </p>
                        {if isset('emailTemplateNamesToReset')}
                            {if $resetAllTemplates === false}
                                <ul>
                                    {foreach $emailTemplateNamesToReset as $templateName}
                                        <li>{$templateName}</li>
                                    {/foreach}
                                </ul>
                            {/if}
                        {/if}
                        <p>{(__('sureResetEmailTemplates'))}</p>
                        <input type="hidden" name="resetSelectedTemplates" value="2">
                    {/if}
                </div>
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto mb-2">
                        <button name="resetConfirmJaSubmit" type="submit" value="1"
                                class="btn btn-danger btn-block min-w-sm">
                            <i class="fal fa-check"></i> {__('yes')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button name="resetConfirmNeinSubmit" type="submit" value="1"
                                class="btn btn-outline-primary btn-block min-w-sm">
                            <i class="fa fa-close"></i> {__('no')}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
