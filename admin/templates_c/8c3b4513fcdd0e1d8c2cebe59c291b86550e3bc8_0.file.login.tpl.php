<?php
/* Smarty version 4.5.5, created on 2025-04-25 21:36:43
  from '/home/sellx-template/htdocs/template.sellx.studio/admin/templates/bootstrap/login.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_680be44b9c45b2_90549357',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '8c3b4513fcdd0e1d8c2cebe59c291b86550e3bc8' => 
    array (
      0 => '/home/sellx-template/htdocs/template.sellx.studio/admin/templates/bootstrap/login.tpl',
      1 => 1743742868,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:tpl_inc/header.tpl' => 1,
    'file:snippets/alert_list.tpl' => 1,
    'file:tpl_inc/footer.tpl' => 1,
  ),
),false)) {
function content_680be44b9c45b2_90549357 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender('file:tpl_inc/header.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php echo '<script'; ?>
 type="text/javascript">
    
    $(document).ready(function () {
        $("input.field:first").focus();
    });
    
<?php echo '</script'; ?>
>
<div class="vertical-center">
    <div class="container">
        <div id="login_wrapper">
            <div id="login_outer" class="card">
                <div class="card-body">
                    <p class="text-center mb-4">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['adminURL']->value;?>
/">
                            <img class="brand-logo" width="120" height="38" src="<?php echo $_smarty_tpl->tpl_vars['templateBaseURL']->value;?>
gfx/JTL-Shop-Logo-rgb.png" alt="JTL-Shop">
                            <img class="brand-logo brand-logo-white" width="120" height="38" src="<?php echo $_smarty_tpl->tpl_vars['templateBaseURL']->value;?>
gfx/JTL-Shop-Logo-rgb-white.png" alt="JTL-Shop">
                        </a>
                    </p>
                    <?php if ($_smarty_tpl->tpl_vars['alertError']->value) {?>
                        <?php $_smarty_tpl->_subTemplateRender('file:snippets/alert_list.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
                        <?php echo '<script'; ?>
 type="text/javascript">
                            
                            $(document).ready(function () {
                                $("#login_wrapper").effect("shake", {times: 2}, 50);
                            });
                            
                        <?php echo '</script'; ?>
>
                    <?php } elseif ((isset($_smarty_tpl->tpl_vars['pw_updated']->value)) && $_smarty_tpl->tpl_vars['pw_updated']->value === true) {?>
                        <div class="alert alert-success" role="alert"><i class="fal fa-info-circle"></i> <?php echo __('successPasswordChange');?>
</div>
                    <?php }?>

                    <?php $_block_plugin1 = isset($_smarty_tpl->smarty->registered_plugins['block']['form'][0][0]) ? $_smarty_tpl->smarty->registered_plugins['block']['form'][0][0] : null;
if (!is_callable(array($_block_plugin1, 'render'))) {
throw new SmartyException('block tag \'form\' not callable or registered');
}
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('form', array('method'=>"post",'action'=>((string)$_smarty_tpl->tpl_vars['adminURL']->value)."/",'class'=>"form-horizontal",'role'=>"form"));
$_block_repeat=true;
echo $_block_plugin1->render(array('method'=>"post",'action'=>((string)$_smarty_tpl->tpl_vars['adminURL']->value)."/",'class'=>"form-horizontal",'role'=>"form"), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>
                        <input id="benutzer" type="hidden" name="adminlogin" value="1" />
                        <?php if ((isset($_smarty_tpl->tpl_vars['uri']->value)) && strlen((string) $_smarty_tpl->tpl_vars['uri']->value) > 0) {?>
                            <input type="hidden" name="uri" value="<?php echo $_smarty_tpl->tpl_vars['uri']->value;?>
" />
                        <?php }?>
                        <?php if ((isset($_SESSION['AdminAccount']->TwoFA_active)) && true === $_SESSION['AdminAccount']->TwoFA_active) {?>                              <input type="hidden" name="benutzer" value="">
                            <input type="hidden" name="passwort" value="">
                            <p class="text-muted"><?php echo __('TwoFALogin');?>
</p>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text p-0 d-block overflow-hidden" id="codeIsStillValid">
                                        <div data-seconds="50%"></div>
                                    </span>
                                </div>
                                <input class="form-control" type="text" placeholder="2fa-code" name="TwoFA_code"
                                       id="inputTwoFA" value="" size="20" tabindex="10" />
                            </div>

                            
                                <?php echo '<script'; ?>
>
                                    $(document).ready(function () {
                                        let date = new Date(),
                                            seconds = date.getSeconds();

                                        if (seconds > 30) {
                                            seconds = seconds - 30;
                                        }

                                        document.body.style.setProperty(
                                            "--secondsCodeIsStillValid",
                                            "-" + seconds + "s"
                                        );
                                    });
                            
                                function switchUser() {
                                    window.location.href = '<?php echo $_smarty_tpl->tpl_vars['adminURL']->value;?>
/logout?token=' + $("[name$=jtl_token]").val();
                                }
                            <?php echo '</script'; ?>
>
                        <?php } else { ?>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-user"></i></span></div>
                                <input class="form-control" type="text" placeholder="<?php echo __('username');?>
" name="benutzer" id="user_login" value="" size="20" tabindex="10" autocomplete="username" />
                            </div>
                            <div class="input-group mt-2">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-lock"></i></span></div>
                                <input class="form-control" type="password" placeholder="<?php echo __('password');?>
" name="passwort" id="user_pass" value="" size="20" tabindex="20" />
                            </div>
                            <?php if ((isset($_smarty_tpl->tpl_vars['code_adminlogin']->value)) && $_smarty_tpl->tpl_vars['code_adminlogin']->value) {?>
                                <?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['captchaMarkup'][0], array( array('getBody'=>true),$_smarty_tpl ) );?>

                            <?php }?>
                        <?php }?>
                        <div id="collapseExtended" class="collapse input-group mt-2 form-group form-row align-items-center<?php if ($_smarty_tpl->tpl_vars['plgSafeMode']->value === true) {?> show<?php }?>" aria-labelledby="headingExtended">
                            <input id="safemode" class="col col-sm-auto ml-2" type="checkbox" name="safemode" value="on"<?php if ($_smarty_tpl->tpl_vars['plgSafeMode']->value === true) {?> checked="checked"<?php }?>>
                            <label for="safemode" class="col col-sm-auto col-form-label text-sm-right" title="<?php echo __('Safe mode enabled.');?>
" data-toggle="tooltip"><?php echo __('Safe mode');?>
</label>
                        </div>
                        <button type="submit" value="Anmelden" tabindex="100" class="btn btn-primary btn-block mt-3"><?php echo __('login');?>
</button>
                        <?php if ((isset($_SESSION['AdminAccount']->TwoFA_active)) && true === $_SESSION['AdminAccount']->TwoFA_active) {?>
                            <button type="button" tabindex="110" class="btn btn-default btn-block btn-md" onclick="switchUser();"><?php echo __('changerUser');?>
</button>
                        <?php }?>
                        <?php if ($_smarty_tpl->tpl_vars['plgSafeMode']->value !== true) {?>
                            <div id="headingExtended" class="mt-3 text-right small">
                                <a href="#" data-toggle="collapse" data-target="#collapseExtended" aria-expanded="false" aria-controls="collapseExtended"><?php echo __('extended');?>
</a>
                            </div>
                        <?php }?>
                    <?php $_block_repeat=false;
echo $_block_plugin1->render(array('method'=>"post",'action'=>((string)$_smarty_tpl->tpl_vars['adminURL']->value)."/",'class'=>"form-horizontal",'role'=>"form"), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                </div>
            </div>
            <p class="forgot-pw-wrap text-center">
                <small><a href="<?php echo $_smarty_tpl->tpl_vars['adminURL']->value;?>
/pass" title="<?php echo __('forgotPassword');?>
"><i class="fa fa-lock"></i> <?php echo __('forgotPassword');?>
</a></small>
            </p>
        </div>
    </div>
</div>
<?php $_smarty_tpl->_subTemplateRender('file:tpl_inc/footer.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
