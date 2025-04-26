<?php
/* Smarty version 4.5.5, created on 2025-04-25 21:36:43
  from '/home/sellx-template/htdocs/template.sellx.studio/admin/templates/bootstrap/tpl_inc/footer.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_680be44b9f71b2_17478296',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0f4b86d5656f682d230cbf7f46404937fa18d9fd' => 
    array (
      0 => '/home/sellx-template/htdocs/template.sellx.studio/admin/templates/bootstrap/tpl_inc/footer.tpl',
      1 => 1743742868,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_680be44b9f71b2_17478296 (Smarty_Internal_Template $_smarty_tpl) {
if ($_smarty_tpl->tpl_vars['account']->value) {?>
        <button class="navbar-toggler sidebar-toggler collapsed" type="button" data-toggle="collapse" data-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="modal" tabindex="-1" role="dialog" id="modal-footer">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title"></h2>
                        <button type="button" class="close" data-dismiss="modal">
                            <i class="fal fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>

        <div class="modal" tabindex="-1" role="dialog" id="modal-footer-delete-confirm">
            <div id="modal-footer-delete-confirm-default-title" class="d-none"><?php echo __('defaultDeleteConfirmTitle');?>
</div>
            <div id="modal-footer-delete-confirm-default-submit" class="d-none"><?php echo __('delete');?>
</div>
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title"></h2>
                        <button type="button" class="close" data-dismiss="modal">
                            <i class="fal fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="ml-auto col-sm-6 col-xl-auto mb-2">
                                <button type="button" id="modal-footer-delete-confirm-yes" class="btn btn-danger btn-block">
                                    <i class="fas fa-trash-alt"></i> <?php echo __('delete');?>

                                </button>
                            </div>
                            <div class="col-sm-6 col-xl-auto">
                                <button type="button" class="btn btn-outline-primary btn-block" data-dismiss="modal">
                                    <?php echo __('cancelWithIcon');?>

                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </div>
</div><?php $_smarty_tpl->_assignInScope('finderURL', (($_smarty_tpl->tpl_vars['adminURL']->value).('/')).(JTL\Router\Route::ELFINDER));
echo '<script'; ?>
>
    function initTinyMCE() {
        let language = '<?php echo JTL\Shop::Container()->getGetText()->getLanguage();?>
'.split('-')[0];
        tinymce.remove('textarea.tinymce');
        tinymce.init({
            selector: 'textarea.tinymce',
            promotion: false,
            branding: false,
            menubar: false,
            relative_urls: false,
            remove_script_host: false,
            document_base_url: '<?php echo $_smarty_tpl->tpl_vars['shopURL']->value;?>
/',
            valid_elements: '*[*]',
            skin: isDarkMode() ? 'oxide-dark' : 'tinymce-5',
            content_css: isDarkMode() ? 'dark' : 'default',
            plugins: 'lists image code emoticons table anchor link',
            language: language,
            language_url: '<?php echo $_smarty_tpl->tpl_vars['shopURL']->value;?>
/includes/libs/tinymce/js/tinymce/langs/' + language + '.js',
            file_picker_callback: (callback, value, meta) => {
                openElFinder((file, mediafilesBaseUrlPath) => {
                    console.log(file.url);
                    callback(file.url);
                }, 'image');
            },
            toolbar: [
                `
                    bold italic underline strikethrough subscript superscript |
                    bullist numlist |
                    outdent indent |
                    blockquote |
                    alignleft aligncenter alignright alignjustify
                `,
                `
                    anchor image emoticons link table hr | code
                `,
                `
                    blocks fontfamily fontsize forecolor backcolor
                `
            ],
        });
    }
    document.addEventListener('colorThemeChanged', initTinyMCE);
    initTinyMCE();

    $('.select2').select2();
    $(function() {
        ioCall('notificationAction', ['update'], undefined, undefined, undefined, true);
    });

    $( document ).scroll(function () {
        $('[name="scrollPosition"]').val(window.scrollY);
    });

    <?php if (!empty($_smarty_tpl->tpl_vars['scrollPosition']->value)) {?>
        var scrollPosition = '<?php echo $_smarty_tpl->tpl_vars['scrollPosition']->value;?>
';
        $('html, body').animate({
            scrollTop: $("html").offset().top + scrollPosition
        }, 1000);
    <?php }
echo '</script'; ?>
>

<?php }?>
</body></html>
<?php }
}
