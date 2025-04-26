<?php
/* Smarty version 4.5.5, created on 2025-04-25 21:36:43
  from '/home/sellx-template/htdocs/template.sellx.studio/admin/templates/bootstrap/tpl_inc/header.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_680be44b9e1525_67530603',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f88171d0a26fb1f0034f73c842bc156fe3c3295e' => 
    array (
      0 => '/home/sellx-template/htdocs/template.sellx.studio/admin/templates/bootstrap/tpl_inc/header.tpl',
      1 => 1743742868,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:snippets/selectpicker.tpl' => 1,
    'file:tpl_inc/backend_sidebar.tpl' => 1,
    'file:tpl_inc/backend_search.tpl' => 1,
    'file:tpl_inc/favs_drop.tpl' => 1,
    'file:tpl_inc/notify_drop.tpl' => 1,
    'file:tpl_inc/updates_drop.tpl' => 1,
    'file:snippets/alert_list.tpl' => 1,
  ),
),false)) {
function content_680be44b9e1525_67530603 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('bForceFluid', (($tmp = $_smarty_tpl->tpl_vars['bForceFluid']->value ?? null)===null||$tmp==='' ? false : $tmp));
$_smarty_tpl->_assignInScope('themeMode', (($tmp = $_smarty_tpl->tpl_vars['themeMode']->value ?? null)===null||$tmp==='' ? 'auto' : $tmp));?>
<!DOCTYPE html>
<html lang="de" class="theme-<?php echo $_smarty_tpl->tpl_vars['themeMode']->value;?>
">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?php echo __('shopTitle');?>
</title>
    <?php $_smarty_tpl->_assignInScope('urlPostfix', ('?v=').($_smarty_tpl->tpl_vars['adminTplVersion']->value));?>
    <link type="image/x-icon" href="<?php echo $_smarty_tpl->tpl_vars['faviconAdminURL']->value;?>
" rel="icon">
    <?php echo $_smarty_tpl->tpl_vars['admin_css']->value;?>

    <?php $_smarty_tpl->_assignInScope('cm', (($_smarty_tpl->tpl_vars['shopURL']->value).('/')).((defined('PFAD_CODEMIRROR') ? constant('PFAD_CODEMIRROR') : null)));?>
    <link type="text/css" rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
lib/codemirror.css<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
">
    <link type="text/css" rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
theme/ayu-dark.css<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
">
    <link type="text/css" rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
addon/hint/show-hint.css<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
">
    <link type="text/css" rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
addon/display/fullscreen.css<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
">
    <link type="text/css" rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
addon/scroll/simplescrollbars.css<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
">
    <?php echo $_smarty_tpl->tpl_vars['admin_js']->value;?>

    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['shopURL']->value;?>
/includes/libs/tinymce/js/tinymce/tinymce.min.js"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
lib/codemirror.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
addon/hint/show-hint.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
addon/hint/sql-hint.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
addon/scroll/simplescrollbars.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
addon/display/fullscreen.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
mode/css/css.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
mode/javascript/javascript.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
mode/xml/xml.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
mode/php/php.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
mode/htmlmixed/htmlmixed.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
mode/sass/sass.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
mode/smarty/smarty.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
mode/smartymixed/smartymixed.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['cm']->value;?>
mode/sql/sql.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['templateBaseURL']->value;?>
js/codemirror_init.js<?php echo $_smarty_tpl->tpl_vars['urlPostfix']->value;?>
"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
>
        var bootstrapButton = $.fn.button.noConflict();
        $.fn.bootstrapBtn = bootstrapButton;
        setJtlToken('<?php echo $_SESSION['jtl_token'];?>
');
        setBackendURL('<?php echo $_smarty_tpl->tpl_vars['adminURL']->value;?>
/');

        function switchAdminLang(tag)
        {
            event.target.href = `<?php echo $_smarty_tpl->tpl_vars['adminURL']->value;?>
/users?token=<?php echo $_SESSION['jtl_token'];?>
&action=quick_change_language&language=` + tag + `&referer=` +  encodeURIComponent(window.location.href);
        }
    <?php echo '</script'; ?>
>

    <?php echo '<script'; ?>
 type="text/javascript"
            src="<?php echo $_smarty_tpl->tpl_vars['templateBaseURL']->value;?>
js/fileinput/locales/<?php echo mb_substr($_smarty_tpl->tpl_vars['language']->value,0,2);?>
.js"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 type="module" src="<?php echo $_smarty_tpl->tpl_vars['templateBaseURL']->value;?>
js/app/app.js"><?php echo '</script'; ?>
>
    <?php $_smarty_tpl->_subTemplateRender('file:snippets/selectpicker.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
</head>
<body>
<?php if ($_smarty_tpl->tpl_vars['account']->value !== false && (isset($_SESSION['loginIsValid'])) && $_SESSION['loginIsValid'] === true) {?>
    <?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['getCurrentPage'][0], array( array('assign'=>'currentPage'),$_smarty_tpl ) );?>

    <div class="spinner"></div>
    <div id="page-wrapper" class="backend-wrapper hidden disable-transitions<?php if ($_smarty_tpl->tpl_vars['currentPage']->value === 'index' || $_smarty_tpl->tpl_vars['currentPage']->value === 'status') {?> dashboard<?php }?>">
        <?php if (!$_smarty_tpl->tpl_vars['hasPendingUpdates']->value && $_smarty_tpl->tpl_vars['wizardDone']->value) {?>
            <?php $_smarty_tpl->_subTemplateRender('file:tpl_inc/backend_sidebar.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
        <?php }?>
        <div class="backend-main <?php if (!$_smarty_tpl->tpl_vars['hasPendingUpdates']->value && $_smarty_tpl->tpl_vars['wizardDone']->value) {?>sidebar-offset<?php }?>">
            <?php if ((defined('SAFE_MODE') ? constant('SAFE_MODE') : null)) {?>
            <div class="alert alert-warning fade show" role="alert">
                <i class="fal fa-exclamation-triangle mr-2"></i>
                <?php echo __('Safe mode enabled.');?>

                <a href="./?safemode=off" class="btn btn-light"><span class="fas fa-exclamation-circle mr-0 mr-lg-2"></span><span><?php echo __('deactivate');?>
</span></a>
            </div>
            <?php }?>
            <div id="topbar" class="backend-navbar row mx-0 align-items-center topbar flex-nowrap">
                <?php if (!$_smarty_tpl->tpl_vars['hasPendingUpdates']->value && $_smarty_tpl->tpl_vars['wizardDone']->value) {?>
                <div class="col search px-0 px-md-3">
                    <?php $_smarty_tpl->_subTemplateRender('file:tpl_inc/backend_search.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
                </div>
                <?php }?>
                <div class="col-auto ml-auto px-2">
                    <ul class="nav align-items-center">
                        <?php if (!$_smarty_tpl->tpl_vars['hasPendingUpdates']->value && $_smarty_tpl->tpl_vars['wizardDone']->value) {?>
                            <li class="nav-item dropdown mr-md-3" id="favs-drop">
                                <?php $_smarty_tpl->_subTemplateRender("file:tpl_inc/favs_drop.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
                            </li>
                            <li class="nav-item dropdown fa-lg">
                                <a href="#" class="nav-link text-dark-gray px-2" data-toggle="dropdown">
                                    <span class="fal fa-map-marker-question fa-fw"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <span class="dropdown-header"><?php echo __('helpCenterHeader');?>
</span>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="https://jtl-url.de/shopschritte" target="_blank" rel="noopener">
                                        <?php echo __('firstSteps');?>

                                    </a>
                                    <a class="dropdown-item" href="https://jtl-url.de/0762z" target="_blank" rel="noopener">
                                        <?php echo __('jtlGuide');?>

                                    </a>
                                    <a class="dropdown-item" href="https://forum.jtl-software.de" target="_blank" rel="noopener">
                                        <?php echo __('jtlForum');?>

                                    </a>
                                    <a class="dropdown-item" href="https://training.jtl-software.com" target="_blank" rel="noopener">
                                        <?php echo __('training');?>

                                    </a>
                                    <a class="dropdown-item" href="https://www.jtl-software.de/Servicepartner" target="_blank" rel="noopener">
                                        <?php echo __('servicePartners');?>

                                    </a>
                                </div>
                            </li>
                            <li class="nav-item dropdown fa-lg" id="notify-drop"><?php $_smarty_tpl->_subTemplateRender("file:tpl_inc/notify_drop.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></li>
                            <li class="nav-item dropdown fa-lg" id="updates-drop"><?php $_smarty_tpl->_subTemplateRender("file:tpl_inc/updates_drop.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></li>
                        <?php }?>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle parent btn-toggle" data-toggle="dropdown">
                                <i class="fal fa-language d-sm-none"></i> <span class="d-sm-block d-none"><?php echo $_smarty_tpl->tpl_vars['languageName']->value;?>
</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['languages']->value, 'langName', false, 'tag');
$_smarty_tpl->tpl_vars['langName']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['tag']->value => $_smarty_tpl->tpl_vars['langName']->value) {
$_smarty_tpl->tpl_vars['langName']->do_else = false;
?>
                                    <?php if ($_smarty_tpl->tpl_vars['language']->value !== $_smarty_tpl->tpl_vars['tag']->value) {?>
                                        <a class="dropdown-item" onclick="switchAdminLang('<?php echo $_smarty_tpl->tpl_vars['tag']->value;?>
')" href="#">
                                            <?php echo $_smarty_tpl->tpl_vars['langName']->value;?>

                                        </a>
                                    <?php }?>
                                <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                            </div>
                        </li>
                        <li class="nav-item dropdown fa-lg" id="theme-toggle">
                            <a href="#" class="nav-link px-2" data-toggle="dropdown">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-circle-half theme-toggle-auto<?php if ($_smarty_tpl->tpl_vars['themeMode']->value !== 'auto') {?> d-none<?php }?>" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
                                </svg>

                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-brightness-high theme-toggle-light<?php if ($_smarty_tpl->tpl_vars['themeMode']->value !== 'light') {?> d-none<?php }?>" viewBox="0 0 16 16">
                                    <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
                                </svg>

                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-moon-stars theme-toggle-dark<?php if ($_smarty_tpl->tpl_vars['themeMode']->value !== 'dark') {?> d-none<?php }?>" viewBox="0 0 16 16">
                                    <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z"/>
                                    <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
                                </svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right py-0">
                                <a class="dropdown-item py-3<?php if ($_smarty_tpl->tpl_vars['themeMode']->value === 'light') {?> active<?php }?>"
                                   href="#" rel="noopener" data-theme="light" data-icon="fa-lightbulb-o">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-brightness-high align-bottom" viewBox="0 0 16 16">
                                        <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
                                    </svg> Light
                                </a>
                                <a class="dropdown-item py-3<?php if ($_smarty_tpl->tpl_vars['themeMode']->value === 'dark') {?> active<?php }?>"
                                   href="#" rel="noopener" data-theme="dark" data-icon="fa-moon-o">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars align-bottom" viewBox="0 0 16 16">
                                        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z"/>
                                        <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
                                    </svg> Dark
                                </a>
                                <a class="dropdown-item py-3<?php if ($_smarty_tpl->tpl_vars['themeMode']->value === 'auto') {?> active<?php }?>"
                                   href="#" rel="noopener" data-theme="auto" data-icon="fa-adjust">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle-half align-bottom" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
                                    </svg> Auto
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="col-auto border-left border-dark-gray px-0 px-md-3">
                    <div class="dropdown avatar">
                        <button class="btn btn-link text-decoration-none dropdown-toggle p-0" data-toggle="dropdown">
                            <img src="<?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['getAvatar'][0], array( array('account'=>$_smarty_tpl->tpl_vars['account']->value),$_smarty_tpl ) );?>
" class="img-circle">
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item link-shop" href="<?php echo $_smarty_tpl->tpl_vars['shopURL']->value;?>
?fromAdmin=yes" title="<?php echo __('goShop');?>
" target="_blank">
                                <i class="fa fa-shopping-cart"></i> <?php echo __('goShop');?>

                            </a>
                            <a class="dropdown-item link-logout" href="<?php echo $_smarty_tpl->tpl_vars['adminURL']->value;?>
/logout?token=<?php echo $_SESSION['jtl_token'];?>
"
                               title="<?php echo __('logout');?>
">
                                <i class="fa fa-sign-out"></i> <?php echo __('logout');?>

                            </a>
                        </div>
                    </div>
                </div>
                <div class="opaque-background"></div>
            </div>
            <?php if (!$_smarty_tpl->tpl_vars['hasPendingUpdates']->value && (isset($_smarty_tpl->tpl_vars['expiredLicenses']->value)) && $_smarty_tpl->tpl_vars['expiredLicenses']->value->count() > 0) {?>
                <div class="modal fade in" id="expiredLicensesNotice" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel"><?php echo __('Licensing problem detected');?>
</h5>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-2"><i class="fa fa-exclamation-triangle" style="font-size: 8em; padding-bottom:10px; color: red;"></i></div>
                                    <div class="col-md-10 ml-auto">
                                        <strong><?php echo __('No valid licence found for the following installed and active extensions:');?>
</strong>
                                        <?php $_block_plugin2 = isset($_smarty_tpl->smarty->registered_plugins['block']['form'][0][0]) ? $_smarty_tpl->smarty->registered_plugins['block']['form'][0][0] : null;
if (!is_callable(array($_block_plugin2, 'render'))) {
throw new SmartyException('block tag \'form\' not callable or registered');
}
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('form', array('id'=>"plugins-disable-form"));
$_block_repeat=true;
echo $_block_plugin2->render(array('id'=>"plugins-disable-form"), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>
                                            <input type="hidden" name="action" value="disable-expired-plugins">
                                            <ul>
                                                <?php $_smarty_tpl->_assignInScope('hasPlugin', false);?>
                                                <?php $_smarty_tpl->_assignInScope('hasTemplate', false);?>
                                                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['expiredLicenses']->value, 'license');
$_smarty_tpl->tpl_vars['license']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['license']->value) {
$_smarty_tpl->tpl_vars['license']->do_else = false;
?>
                                                    <?php if ($_smarty_tpl->tpl_vars['license']->value->getType() === 'plugin') {?>
                                                        <?php $_smarty_tpl->_assignInScope('hasPlugin', true);?>
                                                    <?php } elseif ($_smarty_tpl->tpl_vars['license']->value->getType() === 'template') {?>
                                                        <?php $_smarty_tpl->_assignInScope('hasTemplate', true);?>
                                                    <?php }?>
                                                    <li><?php echo $_smarty_tpl->tpl_vars['license']->value->getName();?>
</li>
                                                    <input type="hidden" name="pluginID[]" value="<?php echo $_smarty_tpl->tpl_vars['license']->value->getReferencedItem()->getInternalID();?>
">
                                                <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                                            </ul>
                                        <?php $_block_repeat=false;
echo $_block_plugin2->render(array('id'=>"plugins-disable-form"), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                                    </div>
                                </div>
                                <div class="alert alert-secondary" role="alert">
                                    <p><strong><?php echo __('Possible reasons:');?>
</strong></p>
                                    <ul class="small">
                                        <li><?php echo __('The extension was obtained from a different source than the JTL-Extension Store');?>
</li>
                                        <li><?php echo __('The licence is not bound to this shop yet (check licence in "My purchases")');?>
</li>
                                        <li><?php echo __('The licence is bound to a different customer account that is not connected to this shop (check connected account in "My purchases")');?>
</li>
                                        <li><?php echo __('The manufacturer disabled the licence');?>
</li>
                                    </ul>
                                </div>
                                <p><strong><?php echo __('Further use of the extension may constitute a licence violation!');?>
</strong><br>
                                    <?php echo __('Please purchase a licence in the JTL-Extension Store or contact the manufacturer of the extension for information on rights of use.');?>

                                </p>
                            </div>
                            <div class="modal-footer">
                                <input type="checkbox" id="understood-license-notice">
                                <label for="understood-license-notice"><?php echo __('I understood this notice.');?>
</label>
                                <button type="button" class="btn btn-default" disabled data-dismiss="modal" id="licenseUnderstood"><?php echo __('Understood');?>
</button>
                                <?php if ($_smarty_tpl->tpl_vars['hasPlugin']->value === true) {?>
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="licenseDisablePlugins"><?php echo __('Disable plugins');?>
</button>
                                <?php }?>
                                <?php if ($_smarty_tpl->tpl_vars['hasTemplate']->value === true) {?>
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="licenseGotoTemplates"><?php echo __('Disable template');?>
</button>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo '<script'; ?>
>
                    $(document).ready(function() {
                        $('#expiredLicensesNotice').modal('show');
                        $('#understood-license-notice').on('click', function (e) {
                            $('#licenseUnderstood').attr('disabled', false);
                        });
                        $('#licenseUnderstood').on('click', function (e) {
                            var newURL = new URL(window.location.href);
                            newURL.searchParams.append('licensenoticeaccepted', 'true');
                            window.location.href = newURL.toString();
                            return true;
                        });
                        $('#licenseDisablePlugins').on('click', function (e) {
                            $('#plugins-disable-form').submit();
                            return true;
                        });
                        $('#licenseGotoTemplates').on('click', function (e) {
                            window.location.href = '<?php echo $_smarty_tpl->tpl_vars['adminURL']->value;?>
/template?licensenoticeaccepted=true';
                            return true;
                        });
                    });
                <?php echo '</script'; ?>
>
            <?php }?>
            <div class="backend-content" id="content_wrapper">

            <?php $_smarty_tpl->_subTemplateRender('file:snippets/alert_list.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
}
