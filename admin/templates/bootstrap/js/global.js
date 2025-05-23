var JTL_TOKEN = null,
    BACKEND_URL = '';

/**
 * Functions that communicate with the server like 'ioCall()' need the XSRF token to be set first.
 * Call this function somewhere on your admin page before doing any ioCall's:
 *
 *  setJtlToken('{$smarty.session.jtl_token}');
 *
 * @param jtlToken
 */
function setJtlToken(jtlToken) {
    JTL_TOKEN = jtlToken;
}

function setBackendURL(url) {
    BACKEND_URL = url;
}

/**
 * @returns {jQuery.fn}
 */
jQuery.fn.center = function () {
    this.css('position', 'absolute');
    this.css('top', ($(window).height() - this.height()) / 2 + $(window).scrollTop() + 'px');
    this.css('left', ($(window).width() - this.width()) / 2 + $(window).scrollLeft() + 'px');
    return this;
};

/**
 * @param form
 * @constructor
 */
function AllMessages(form) {
    var x,
        y;
    for (x = 0; x < form.elements.length; x++) {
        y = form.elements[x];
        if (y.name !== 'ALLMSGS') {
            y.checked = form.ALLMSGS.checked;
        }
    }
}

function updateAllMessages(form) {
    let x,
        y,
        allChecked = true;

    for (const y of $(form).find('[type=checkbox]')) {
        if (y.checked === false && y.name !== 'ALLMSGS') {
            allChecked = false;
        }
    }

    $('#ALLMSGS').prop('checked', allChecked);
}

/**
 * @param selector
 */
function checkToggle(selector) {
    var elem = $(selector + ' input[type="checkbox"]');
    elem.prop('checked', !elem.prop('checked'));
}

/**
 * check/un-check all checkboxes of a given form-object,
 * EXCEPT those, which are contained in the given array
 * or single string.
 *
 * @param Object  object of type HTML.form
 * @param Array|String  array of strings or single string - name(s), which we did NOT want to "check/un-check"
 * @return void
 */
function AllMessagesExcept(form, IDs) {
    var x,
        y;
    // check, if we got an array here
    if (IDs instanceof Object || IDs instanceof Array) {
        for (x = 0; x < form.elements.length; x++) {
            // iterate over all checkboxes, except the one with the name "ALLMSGS"
            if ('checkbox' === form.elements[x].type && 'ALLMSGS' !== form.elements[x].name) {
                // check, if that element is NOT in our "except-array" ('undefined')..
                if (typeof IDs[form.elements[x].value] === 'undefined') {
                    // ..and set the same state, as ALLMSGS has
                    form.elements[x].checked = form.ALLMSGS.checked;
                }
            }
        }
    } else {
        // legacy functionality - "single string except"
        for (x = 0; x < form.elements.length; x++) {
            y = form.elements[x];
            if (y.name !== 'ALLMSGS') {
                if (IDs.length > 0) {
                    if (y.id.indexOf(IDs)) {
                        y.checked = form.ALLMSGS.checked;
                    }
                }
            }
        }
    }
}

/**
 * @param elemID
 * @param picExpandID
 * @param picRetractID
 */
function expand(elemID, picExpandID, picRetractID) {
    var elem;
    if (elemID.length > 0) {
        elem = document.getElementById(elemID);
        if (typeof (elem) !== 'undefined') {
            elem.style.display = 'table-row';
            if (picExpandID.length > 0 && picRetractID.length > 0) {
                document.getElementById(picExpandID).style.display = 'none';
                document.getElementById(picRetractID).style.display = 'table-row';
            }
        }
    }
}

/**
 * @param elemID
 * @param picExpandID
 * @param picRetractID
 */
function retract(elemID, picExpandID, picRetractID) {
    var elem;
    if (elemID.length > 0) {
        elem = document.getElementById(elemID);
        if (typeof (elem) !== 'undefined') {
            elem.style.display = 'none';
            if (picExpandID.length > 0 && picRetractID.length > 0) {
                document.getElementById(picExpandID).style.display = 'table-row';
                document.getElementById(picRetractID).style.display = 'none';
            }
        }
    }
}

/**
 * Format file size
 */
function formatSize(bytes, si) {
    var thresh = 1024;
    if (Math.abs(bytes) < thresh) {
        return bytes + ' b';
    }
    var units = ['Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb']
    var u = -1;
    do {
        bytes /= thresh;
        ++u;
    } while (Math.abs(bytes) >= thresh && u < units.length - 1);
    return bytes.toFixed(2) + ' ' + units[u];
}

function getRange(a, b, c) {
    var li = [],
        i,
        start, end, step,
        up = true;

    if (arguments.length === 1) {
        start = 0;
        end = a;
        step = 1;
    }

    if (arguments.length === 2) {
        start = a;
        end = b;
        step = 1;
    }

    if (arguments.length === 3) {
        start = a;
        end = b;
        step = c;
        if (c < 0) {
            up = false;
        }
    }

    if (up) {
        for (i = start; i < end; i += step) {
            li.push(i);
        }
    } else {
        for (i = start; i > end; i += step) {
            li.push(i);
        }
    }

    return li;
}

/**
 * @param type
 * @param title
 * @param message
 */
function showNotify(type, title, message) {
    return createNotify({
        title: title,
        message: message
    }, {
        type: type
    });
}

/**
 * @param options
 * @param settings
 * @returns {*|undefined}
 */
function createNotify(options, settings) {
    options = $.extend({}, {
        message: '...',
        title: 'Notification',
        icon: 'fal fa-info-circle'
    }, options);

    settings = $.extend({}, {
        type: 'info',
        delay: 5000,
        allow_dismiss: false,
        placement: {from: 'bottom', align: 'center'},
        animate: {enter: 'animated fadeInDown', exit: 'animated fadeOutUp'},
        template: '<div data-notify="container" class="col-xs-11 col-sm-4 alert alert-{0} alert-custom alert-dismissible" role="alert">' +
            '  <button type="button" aria-hidden="true" class="close" data-notify="dismiss"><i class="fal fa-times alert-{0}"></i></button>' +
            '  <div>' +
            '    <div style="float:left;margin-right:10px">' +
            '      <i data-notify="icon"></i>' +
            '    </div>' +
            '    <div style="overflow:hidden">' +
            '      <p data-notify="title" style="font-weight:bold">{1}</p>' +
            '      <div data-notify="message" class="clearfix">{2}</div>' +
            '      <div class="progress" data-notify="progressbar">' +
            '        <div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '</div>'
    }, settings);

    return $.notify(options, settings);
}

function updateNotifyDrop() {
    ioCall(
        'getNotifyDropIO', [],
        function (result) {
            if (result.tpl) {
                $('#notify-drop').html(result.tpl);
            } else {
                $('#notify-drop').html('');
            }
        }
    );
}

function massCreationCoupons() {
    var checkboxCreationCoupons = $("#couponCreation").prop("checked");
    $("#massCreationCouponsBody").toggleClass("hidden", !checkboxCreationCoupons);
    $("#singleCouponCode").toggleClass("hidden", checkboxCreationCoupons);
    $("#limitedByCustomers").toggleClass("hidden", checkboxCreationCoupons);
    $("#informCustomers").toggleClass("hidden", checkboxCreationCoupons);
}

function switchCouponTooltipVisibility() {
    $('#cWertTyp').on('change', function () {
        if ($(this).val() === 'prozent') {
            $('#fWertTooltip').parent().hide();
        } else {
            $('#fWertTooltip').parent().show();
        }
    });
}

function tristate(cb) {
    let boxId = cb.dataset.boxId;
    let boxIgnore = $('#boxIgnore' + boxId);

    if (cb.readOnly) {
        // checkbox was indeterminate before
        // so uncheck it
        cb.checked = cb.readOnly = false;
        boxIgnore.val('-1');
    } else if (!cb.checked) {
        // checkbox was checked before
        // so set it to indeterminate
        cb.readOnly = cb.indeterminate = true;
        boxIgnore.val(boxId);
    } else {
        // checkbox was unchecked before
        boxIgnore.val('-1');
    }
}

function checkSingleSettingCard() {
    if ($('#settings .card').length === 1) {
        $('#settings .card').addClass('single');
    }
}

/**
 * document ready
 */
$(document).ready(function () {
    switchCouponTooltipVisibility();
    $('.collapse').removeClass('in');

    $('.accordion-toggle').on('click', function () {
        $(this).find('i').toggleClass('fa-minus fa-plus');
        let parent = $(this).data("parent");
        if (parent.length > 0) {
            let clicked = $(this);
            $(".accordion-toggle[data-parent='" + parent + "']").each(function () {
                // Remove minus and add a plus sign for all accordion-toggles with same destination except the clicked one
                if ($(this).attr("href") !== clicked.attr("href")) {
                    $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
                }
            });
        }
    });

    $('.help').each(function () {
        var id = $(this).attr('ref'),
            tooltip = $('<div></div>').text($(this).attr('title')).addClass('tooltip').attr('id', 'help' + id),
            offset;
        $('body').append(tooltip);
        $(this).attr('title', '');
        $(this).bind('mouseenter', function () {
            var help = $('#help' + id);
            offset = $(this).offset();
            help.css({
                left: offset.left - help.outerWidth() + $(this).outerWidth() + 5,
                top: offset.top - ((help.outerHeight() - $(this).outerHeight()) / 2)
            }).fadeIn(200);
        }).bind('mouseleave', function () {
            $('#help' + id).hide();
        });
    });

    $('body').tooltip({selector: '[data-toggle=tooltip]'});
    $('#user_login').focus();
    $('#check-menus').on('change', function () {
        $(this).parent().submit();
    });

    $("#subnav ul li a[href^='#']").on('click', function (e) {
        var hash = this.hash;
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $(this.hash).offset().top
        }, 300, function () {
            window.location.hash = hash;
        });

    });

    $('#fav-add').on('click', function () {
        var title = $('.content-header h1').text();
        var url = window.location.href;
        ioCall('addFav', [title, url], function () {
            ioCall('reloadFavs', [], function (data) {
                $('#favs-drop').html(data.tpl);
            });
            showNotify('success', 'Favoriten', 'Wurde erfolgreich hinzugef&uuml;gt');
        });

        return false;
    });

    $('button.blue, input[type=submit].blue').addClass('btn btn-primary');
    $('button.orange, input[type=submit].orange').addClass('btn btn-default');

    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 100) {
            $('#scroll-top').fadeIn();
        } else {
            $('#scroll-top').fadeOut();
        }
    });
    //Click event to scroll to top
    $('#scroll-top').on('click', function () {
        $('html, body').animate({scrollTop: 0}, 800);
        return false;
    });
    $('.btn-tooltip').tooltip({
        container: 'body'
    });
    //open tabs if url contains corresponding hash
    if (location.hash.length > 0 && typeof jQuery.fn.tab === 'function') {
        $('body a[href="' + location.hash + '"]').tab('show');
    }
    //Checkboxen de-/aktivieren die über der Einstellung liegen und in der gleichen Klasse sind
    $(".Boxen").on('click', function () {
        var checkbox = $(this).parent().parent().find("input:not(.Boxen)");
        var activitem = $(this).prop("checked");
        $(checkbox).each(function (id, item) {
            $(item).prop("checked", activitem);
        });
    });

    $('.switcher .switcher-wrapper').on('click', function (e) {
        e.stopPropagation();
    });
    $('.switcher').on('show.bs.dropdown', function () {
        showBackdrop();
        ioCall('getAvailableWidgets');
    }).on('hide.bs.dropdown', function () {
        hideBackdrop();
    });

    $('#nbc-1 .dropdown').on('show.bs.dropdown', function () {
        showBackdrop();
    }).on('hide.bs.dropdown', function () {
        hideBackdrop();
    });

    // Massenerstellung von Kupons de-/aktivieren
    $("#couponCreation").on('change', function () {
        massCreationCoupons();
    });

    /*
     * alert actions
     */
    $('.alert .close').on('click', function () {
        $(this).closest('.alert').fadeOut(1000);
    });

    $('.alert').each(function () {
        if ($(this).data('fade-out') > 0) {
            $(this).fadeOut($(this).data('fade-out'));
        }
    });

    let tristateCheckboxes = $("input[type=checkbox].tristate");

    tristateCheckboxes
        .prop("indeterminate", true).prop("readonly", true)
        .on('change', e => {
            tristate(e.target);
        });

    $('.fieldfillout').on('change', function () {
        $(this).removeClass('fieldfillout');
    });

    $('.form-error input, .form-error select').on('change', function () {
        $(this).closest('.form-error').removeClass('form-error');
    });

    checkSingleSettingCard();
    onChangeFormSubmit();
    getSettingListeners();
    deleteConfirmation();

    // Add top scrollbar to tables when they are scrollable.
    $('.table-responsive').topScrollbar();
    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $(this).closest('.tabs').find('.tab-content .table-responsive').topScrollbar();
    });
    $('.collapse').on('shown.bs.collapse', function () {
        $(this).find('.table-responsive').topScrollbar();
    });
    $('img[loading="lazy"]').on('load', function () {
        $(this).closest('.table-responsive').topScrollbar();
    });
    let windowResizeTimeout = null;

    function windowResized() {
        $('.table-responsive').topScrollbar();
    }

    $(window).on('resize', function () {
        if (windowResizeTimeout) {
            $('.jquery-top-scrollbar').remove();
            clearTimeout(windowResizeTimeout);
        }
        windowResizeTimeout = setTimeout(windowResized, 500);
    });

    function toggleCardWidget(elem, $widgetContent, widget) {
        if ($widgetContent.is(':hidden')) {
            $widgetContent.slideDown('fast');
            $('i', elem).attr('class', 'fa fa-chevron-up');
            $(widget).find('.card-header hr').removeClass('d-none');
        } else {
            $widgetContent.slideUp('fast');
            $('i', elem).attr('class', 'fa fa-chevron-down');
            $(widget).find('.card-header hr').addClass('d-none');
        }
    }

    // Add widget functionality to elements with class card-widget
    $('.card-widget').each(function (i, widget) {
        let $widgetContent = $('.card-body', widget),
            $title = $(widget).find('.card-header > *:first-child');

        // add click handler for widgets collapse button
        if ($(widget).find('.card-header').length && $(widget).find('.card-body').length && $title.length) {
            let chevron = $title.html('<a href="#" class="text-decoration-none">'
                + $title.html()
                + '<span class="btn-sm chevronToggle"><i class="fa fa-chevron-' + 'down' + '"></i></span></a>')
                .on('click', function (e) {
                    toggleCardWidget(this, $widgetContent, widget);
                    e.preventDefault();
                })
                .appendTo($(widget).find('.card-header > *:first-child'));
            if ($('.body-hidden', widget).length > 0) {
                chevron.trigger('click');
            }
        }
    });

    $('#theme-toggle a.dropdown-item').on('click', function (e) {
        e.preventDefault();
        let btn = $(this),
            theme = $(this).data('theme');
        ioCall('setTheme', [theme], function (data) {
            if (data['theme'] === theme) {
                $('#theme-toggle a.dropdown-item').removeClass('active');
                btn.addClass('active');

                $('html')
                    .removeClass('theme-auto')
                    .removeClass('theme-light')
                    .removeClass('theme-dark')
                    .addClass('theme-' + data['theme']);

                $('#theme-toggle .theme-toggle-auto').addClass('d-none');
                $('#theme-toggle .theme-toggle-light').addClass('d-none');
                $('#theme-toggle .theme-toggle-dark').addClass('d-none');
                $('#theme-toggle .theme-toggle-' + theme).removeClass('d-none');

                document.dispatchEvent(colorThemeEvent);
            } else {
                showNotify('danger', 'Theme', 'Theme konnte nicht gesetzt werden.');
            }
        });
    });
});

$(window).on('load', () => {
    $('#page-wrapper').removeClass('hidden disable-transitions');
    $('html').addClass('ready');
    $('body > .spinner').remove();

    document.dispatchEvent(new CustomEvent('ready', {
        detail: {
            jquery: $
        }
    }))
});

function showBackdrop() {
    $backdrop = $('<div class="menu-backdrop fade" />')
        .appendTo($(document.body));
    $backdrop[0].offsetWidth;
    $backdrop.addClass('in');
}

function hideBackdrop() {
    $('.menu-backdrop').remove();
}

/**
 * Call a function asynchronously on the server. The server answers with a JSON-encoded IOResponse object, that ioCall()
 * will interpret afterwards.an or an IOError on failure or with some other generic data depending on the called
 * function on the server.
 *
 * @param name - name of the AJAX-function registered on the server
 * @param args - array of arguments passed to the function
 * @param success - (optional) function (data, context) success-callback
 * @param error - (optional) function (data) error-callback
 * @param context - object to be assigned 'this' in eval()-code (default: { } = a new empty anonymous object)
 * @param disableSpinner - bool, set true to disable spinner
 * @returns XMLHttpRequest jqxhr
 */
function ioCall(name, args = [], success = () => {
}, error = () => {
}, context = {}, disableSpinner = false) {
    if (JTL_TOKEN === null) {
        throw 'Error: IO call not possible. JTL_TOKEN was not set on this page.';
    }

    if (disableSpinner === false) {
        startSpinner();
    }

    return $.ajax({
        url: BACKEND_URL + 'io',
        method: 'post',
        dataType: 'json',
        data: {
            jtl_token: JTL_TOKEN,
            io: JSON.stringify({
                name: name,
                params: args
            })
        },
        success: function (data, textStatus, jqXHR) {
            if (data) {
                if (data.domAssigns) {
                    data.domAssigns.forEach(item => {
                        let $item = $('#' + item.target);

                        if ($item.length > 0) {
                            $item[0][item.attr] = item.data;
                        }
                    });
                }
                if (data.debugLogLines) {
                    data.debugLogLines.forEach(line => {
                        if (line[1]) {
                            console.groupCollapsed(...line[0]);
                        } else if (line[2]) {
                            console.groupEnd();
                        } else {
                            console.log(...line[0]);
                        }
                    });
                }
                if (data.varAssigns) {
                    data.varAssigns.forEach(assign => {
                        context[assign.name] = assign.value;
                    });
                }
                if (data.windowLocationHref) {
                    window.location.href = data.windowLocationHref;
                }
            }
            success(data, context);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.responseJSON) {
                error(jqXHR.responseJSON);
            }
        }
    }).always(function () {
        if (disableSpinner === false) {
            stopSpinner();
        }
    });
}

/**
 * Induce a file download provided by an AJAX function
 *
 * @param name
 * @param args
 */
function ioDownload(name, args) {
    if (JTL_TOKEN === null) {
        throw 'Error: IO download not possible. JTL_TOKEN was not set on this page.';
    }

    window.location.href = BACKEND_URL + 'io?token=' + JTL_TOKEN + '&io=' + encodeURIComponent(JSON.stringify({
        name: name,
        params: args
    }));
}

/**
 * @param adminPath
 * @param funcname
 * @param params
 * @param callback
 */
function ioManagedCall(adminPath, funcname, params, callback) {
    ioCall(
        funcname, params,
        function (result) {
            if (typeof callback === 'function') {
                callback(result, result.error);
            }
        },
        function (result) {
            if (typeof callback === 'function') {
                callback(result, result.error);
            } else if (result.error) {
                if (result.error.code === 401) {
                    createNotify(
                        {
                            title: 'Sitzung abgelaufen',
                            message: 'Sie werden zur Anmelde-Maske weitergeleitet...',
                            icon: 'fa fa-lock'
                        },
                        {
                            type: 'danger',
                            onClose: function () {
                                window.location.pathname = '/' + adminPath + 'index.php';
                            }
                        }
                    );
                } else if (result.error.message) {
                    createNotify(
                        {
                            title: 'Fehler',
                            message: result.error.message,
                            icon: 'fa fa-lock'
                        },
                        {
                            type: 'danger'
                        }
                    );
                }
            }
        }
    );
}

/**
 * Make an input element selected by 'selector' a typeahead input field. The data is queried on an ajax-function named
 * funcName. When an item from the suggestion list ist selected the callback onSelect is executed.
 *
 * @param selector the CSS selector to apply the typeahead onto
 * @param funcName the AJAX function name that provides the sugesstion data
 * @param display for a given suggestion, determines the string representation of it. This will be used when setting
 *      the value of the input control after a suggestion is selected. Can be either a key string or a function that
 *      transforms a suggestion object into a string. Defaults to stringifying the suggestion.
 * @param suggestion (default: null) a callback function to customize the sugesstion entry. Takes the item object and
 *      returns a HTML string
 * @param onSelect
 * @param spinnerElm
 * @param delay
 * @param args
 * @param noDataAvailable
 */
function enableTypeahead(selector, funcName, display, suggestion, onSelect, spinnerElm, delay = 0,
                         args = [], noDataAvailable = '') {
    var pendingRequest = null;
    var timeout;
    var templates = noDataAvailable === '' ? {suggestion: suggestion} : {
        suggestion: suggestion,
        notFound: '<div class="tt-suggestion">' + noDataAvailable + '</div>'
    }

    $(selector)
        .typeahead(
            {
                highlight: true,
                hint: true,
                minLength: 1
            },
            {
                limit: 40,
                source: function (query, syncResults, asyncResults) {
                    if (timeout) {
                        clearTimeout(timeout);
                    }
                    timeout = setTimeout(function () {
                        if (pendingRequest !== null) {
                            pendingRequest.abort();
                        }
                        pendingRequest = ioCall(funcName, [query, 50].concat(args), function (data) {
                            pendingRequest = null;
                            asyncResults(data);
                        });
                    }, delay);
                },
                display: display,
                templates: templates
            }
        )
        .on('typeahead:select', onSelect)
        .on('typeahead:asyncrequest', e => {
            $(spinnerElm).show();
        })
        .on('typeahead:asynccancel typeahead:asyncreceive', () => {
            $(spinnerElm).hide();
        })
    ;
}

function selectAllItems(elm, enable) {
    $(elm).closest('form').find('input[type=checkbox]').prop('checked', enable);
}

function openElFinder(callback, type) {
    window.elfinder = {getFileCallback: callback};
    window.open(
        BACKEND_URL + 'elfinder?token=' + JTL_TOKEN + '&mediafilesType=' + type,
        'elfinderWindow',
        'status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=1,scrollbars=0,width=800,height=600'
    );
}

function sprintf(format) {
    for (var i = 1; i < arguments.length; i++) {
        format = format.replace(/%s/, arguments[i]);
    }
    return format;
}

function onChangeFormSubmit() {
    $('.on-change-submit').on('change', function () {
        $(this).closest('form').submit();
    });
}

function closeTooltips() {
    $('.tooltip[role="tooltip"]').remove();
}

function simpleAjaxCall(url, data, success, error, context, disableSpinner) {
    'use strict';
    data = data || [];
    success = success || function () {
    };
    error = error || function () {
    };
    context = context || {};
    disableSpinner = disableSpinner || false;

    if (disableSpinner === false) {
        startSpinner();
    }
    $.ajax({
        type: 'POST',
        url: url,
        data: data,
        success: function (data) {
            success(data, context);
        },
        error: function (data) {
            error(data, context);
        }
    }).always(function () {
        if (disableSpinner === false) {
            stopSpinner();
        }
    });
}

function startSpinner() {
    if ($('.ajax-spinner').length === 0) {
        $('body').append('<div class="ajax-spinner"><i class="fa fa-spinner fa-pulse"></i></div>');
    }
}

function stopSpinner() {
    $('body').find('.ajax-spinner').remove();
}

function getSettingListeners() {
    $('.setting-changelog').on('click', function (e) {
        e.preventDefault();
        let $self = $(this);
        ioCall('getSettingLog', [$(this).data('setting-name')], function (data) {
            $('#modal-footer').modal('show');
            $('#modal-footer .modal-body').html(data);
            $('#modal-footer .modal-title').html(
                $self.data('name') + ' | ' + $self.data('setting-name') + ' | ' + $self.data('id'));
        });
    });
}

/**
 * open a delete modal to confirm deletion
 *
 * 3 types of delete buttons:
 * 1. By href: .delete-confirm - needs a href tag
 * 2. By form: .delete-confirm - needs type="submit"
 * 3. By io: .delete-confirm - needs .delete-confirm-io and confirm event is triggered by .trigger('delete.io');
 *
 * modal title can be changed by: data-modal-title
 * modal body can be changed by: data-modal-body
 */
function deleteConfirmation() {
    $('.delete-confirm').on('click', function (e) {
        e.preventDefault();
        let href = $(this).attr('href'),
            $self = $(this),
            $confirmButton = $('#modal-footer-delete-confirm-yes'),
            $modal = $('#modal-footer-delete-confirm'),
            title = $self.data('modal-title') || $('#modal-footer-delete-confirm-default-title').html(),
            body = $self.data('modal-body') || '',
            submit = $self.data('modal-submit') || $('#modal-footer-delete-confirm-default-submit').html();

        if (href !== undefined && href !== '') {
            $confirmButton.off().on('click', function () {
                window.location = href;
            });
        } else if ($(this).attr('type') === 'submit' || $(this).hasClass('btn-submit')) {
            $confirmButton.off().on('click', function () {
                let $form = $self.closest('form');
                $form.append(
                    '<input type="hidden" name="' + $self.attr('name') + '" value="' + $self.attr('value') + '" />'
                );
                $form.submit();
            });
        } else if ($self.hasClass('delete-confirm-io')) {
            $confirmButton.off().on('click', function () {
                $self.trigger('delete.io');
                $modal.modal('hide');
            });
        }
        $('#modal-footer-delete-confirm .modal-title').html(title);
        $('#modal-footer-delete-confirm .modal-body').html(body);
        $confirmButton.html(submit);
        $modal.modal('show');
    });
}

$.fn.isOnScreen = function () {

    var win = $(window);

    var viewport = {
        top: win.scrollTop(),
        left: win.scrollLeft()
    };
    viewport.right = viewport.left + win.width();
    viewport.bottom = viewport.top + win.height();

    var bounds = this.offset();
    bounds.right = bounds.left + this.outerWidth();
    bounds.bottom = bounds.top + this.outerHeight();

    return (!(viewport.right < bounds.left
        || viewport.left > bounds.right
        || viewport.bottom < bounds.top
        || viewport.top > bounds.bottom));

};

function isDarkMode() {
    let htmlTag = document.documentElement;

    if (htmlTag.classList.contains('theme-auto')) {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    return htmlTag.classList.contains('theme-dark');
}

const colorThemeEvent = new Event('colorThemeChanged');

function highlightTargetFormGroup() {
    let fragment = window.location.hash.slice(1);
    if (fragment) {
        let input = $('#' + fragment);
        if (input) {
            let formGroup = input.closest('.form-group');
            $('.form-group').removeClass('highlight');
            formGroup.addClass('highlight');
        }
    }
}
