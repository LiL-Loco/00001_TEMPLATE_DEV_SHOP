<?php
/* Smarty version 4.5.5, created on 2025-04-25 21:36:43
  from '/home/sellx-template/htdocs/template.sellx.studio/admin/templates/bootstrap/snippets/selectpicker.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_680be44b9e6275_88866511',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c248e3bb9bead67ff25dc13ddf4d9f5e29468fd1' => 
    array (
      0 => '/home/sellx-template/htdocs/template.sellx.studio/admin/templates/bootstrap/snippets/selectpicker.tpl',
      1 => 1743742868,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_680be44b9e6275_88866511 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
 type="module">
    
        $('.selectpicker').selectpicker({
            noneSelectedText: '<?php echo __('selectPickerNoneSelectedText');?>
',
            noneResultsText: '<?php echo __('selectPickerNoneResultsText');?>
',
            countSelectedText: '<?php echo __('selectPickerCountSelectedText');?>
',
            maxOptionsText: () => [
                '<?php echo __('selectPickerLimitReached');?>
',
                '<?php echo __('selectPickerGroupLimitReached');?>
',
            ],
            selectAllText: '<?php echo __('selectPickerSelectAllText');?>
',
            deselectAllText: '<?php echo __('selectPickerDeselectAllText');?>
',
            doneButtonText: '<?php echo __('close');?>
',
            style: ''
        });
    
<?php echo '</script'; ?>
>
<?php }
}
