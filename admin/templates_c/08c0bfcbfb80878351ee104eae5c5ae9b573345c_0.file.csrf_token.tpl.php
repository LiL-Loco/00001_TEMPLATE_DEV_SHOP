<?php
/* Smarty version 4.5.5, created on 2025-04-25 21:36:43
  from '/home/sellx-template/htdocs/template.sellx.studio/includes/vendor/jtlshop/scc/src/scc/templates/csrf_token.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_680be44b9f3e47_90654098',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '08c0bfcbfb80878351ee104eae5c5ae9b573345c' => 
    array (
      0 => '/home/sellx-template/htdocs/template.sellx.studio/includes/vendor/jtlshop/scc/src/scc/templates/csrf_token.tpl',
      1 => 1712824458,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_680be44b9f3e47_90654098 (Smarty_Internal_Template $_smarty_tpl) {
?><input type="hidden" class="jtl_token" name="jtl_token" value="<?php echo $_SESSION['jtl_token'];?>
"/>
<?php }
}
