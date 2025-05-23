'use strict';

$('body').on('click', '.option li', function (e) {
    var i = $(this).parents('.select').attr('id'),
        v = $(this).children().text(),
        o = $(this).attr('id');
    $('#' + i + ' .selected').attr('id', o).text(v);
});

// prevent multiple form submit on client side
$('.submit_once').closest('form').on('submit', function() {
    $(this).find('.submit_once').prop('disabled', 'true');
    return true;
});

/**
 *  Format file size
 */
function formatSize(size) {
    var fileSize = Math.round(size / 1024),
        suffix = 'KB',
        fileSizeParts;

    if (fileSize > 1000) {
        fileSize = Math.round(fileSize / 1000);
        suffix = 'MB';
    }

    fileSizeParts = fileSize.toString().split('.');
    fileSize = fileSizeParts[0];

    if (fileSizeParts.length > 1) {
        fileSize += '.' + fileSizeParts[1].substr(0, 2);
    }
    fileSize += suffix;

    return fileSize;
}

function getCategoryMenu(categoryId, success) {
    var xx = {};
    var io = $.evo.io();

    io.call('getCategoryMenu', [categoryId], xx, function (error, data) {
        if (error) {
            console.error(data);
        } else if (typeof success === 'function') {
            success(xx.response);
        }
    });

    return true;
}

function categoryMenu(rootcategory) {
    if (typeof rootcategory === 'undefined') {
        rootcategory = $('.sidebar-offcanvas .navbar-categories').html();
    }

    $('.sidebar-offcanvas li a.nav-sub').on('click', function(e) {
        var navbar = $('.sidebar-offcanvas .navbar-categories'),
            ref = $(this).data('ref');

        if (ref === 0) {
            $(navbar).html(rootcategory);
            categoryMenu(rootcategory);
        }
        else {
            getCategoryMenu(ref, function(data) {
                $(navbar).html(data);
                categoryMenu(rootcategory);
            });
        }

        return false;
    });
}

function compatibility() {
    var __enforceFocus = $.fn.modal.Constructor.prototype.enforceFocus;
    $.fn.modal.Constructor.prototype.enforceFocus = function () {
        if ($('.modal-body .g-recaptcha').length === 0) {
            __enforceFocus.apply(this, arguments);
        }
    };
}
function regionsToState(){
    $('.js-country-select').on('change', function() {

        var result = {};
        var io = $.evo.io();
        var country = $(this).find(':selected').val();
        country = (country !== null && country !== undefined) ? country : '';
        var connection_id = $(this).attr('id').toString().replace("-country","");

        io.call('getRegionsByCountry', [country], result, function (error, data) {
            if (error) {
                console.error(data);
            } else {
                var state_id = connection_id+'-state';
                var state = $('#'+state_id);
                var state_data = state.data();

                if (typeof(result.response) === 'undefined' || state.length === 0) {
                    return;
                }
                var title = state_data.defaultoption;
                var stateIsRequired = result.response.required;
                var data = result.response.states;
                var def = $('#'+state_id).val();
                if(typeof(data)!=='undefined'){
                    if (data !== null && data.length > 0) {
                        if (stateIsRequired){
                            var state = $('<select />').attr({ id: state_id, name: state.attr('name'), class: 'custom-select required form-control js-state-select', required: 'required'});
                        } else {
                            var state = $('<select />').attr({ id: state_id, name: state.attr('name'), class: 'custom-select form-control js-state-select'});
                        }

                        Object.keys(state_data).forEach(function(key,index) {
                            state.data(key,state_data[key]);
                        });

                        state.append('<option value="">' + title + '</option>');
                        $(data).each(function(idx, item) {
                            state.append(
                                $('<option></option>').val(item.iso).html(item.name)
                                    .attr('selected', item.iso == def || item.name == def ? 'selected' : false)
                            );
                        });
                        $('#'+state_id).replaceWith(state);
                    } else {
                        if (stateIsRequired) {
                            var state = $('<input />').attr({ type: 'text', id: state_id, name: state.attr('name'),  class: 'required form-control js-state-select', placeholder: title, required: 'required' });
                        } else {
                            var state = $('<input />').attr({ type: 'text', id: state_id, name: state.attr('name'),  class: 'form-control js-state-select', placeholder: title });
                        }
                        Object.keys(state_data).forEach(function(key,index) {
                            state.data(key,state_data[key]);
                        });
                        $('#'+state_id).replaceWith(state);
                    }
                    if (stateIsRequired){
                        state.parent().find('.state-optional').addClass('d-none');
                    } else {
                        state.parent().find('.state-optional').removeClass('d-none');
                    }
                }
            }
        });
        return false;
    }).trigger('change');
}

function loadContent(url, setCustomAnchorScrolling)
{
    $.evo.extended().loadContent(url, function() {
        $.evo.extended().register();

        if (typeof $.evo.article === 'function') {
            $.evo.article().onLoad();
            $.evo.article().register();
            addValidationListener();
        }

        let topbarHeight      = $('#header-top-bar').outerHeight() || 0,
            wrapperHeight     = $('#jtl-nav-wrapper').outerHeight() || 0,
            productListHeight = $('#product-list').offset().top || 0,
            pageNavHeight     = $('.productlist-page-nav').outerHeight() || 0;

        if (setCustomAnchorScrolling instanceof Function) {
            setCustomAnchorScrolling();
        }

        $('html,body').animate({
            scrollTop: productListHeight - wrapperHeight - topbarHeight - pageNavHeight - 20
        }, 100);
    });
}

function sanitizeOutput(val) {
    return val.replace(/\&/g, '&amp;')
        .replace(/\</g, '&lt;')
        .replace(/\>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/\'/g, '&#x27;')
        .replace(/\//g, '&#x2F;');
}

function addValidationListener() {
    var forms      = $('form.jtl-validate'),
        inputs     = $('form.jtl-validate input, form.jtl-validate textarea').not('[type="radio"],[type="checkbox"]'),
        selects    = $('form.jtl-validate select'),
        checkables = $('form.jtl-validate input[type="radio"], form.jtl-validate input[type="checkbox"]'),
        $body      = $('body');

    for (var i = 0; i < forms.length; i++) {
        forms[i].addEventListener('invalid', function (event) {
            event.preventDefault();
            $(event.target).closest('.form-group').find('div.form-error-msg').remove();
            $(event.target).closest('.form-group')
                .addClass('has-error')
                .append('<div class="form-error-msg w-100">' + sanitizeOutput(event.target.validationMessage) + '</div>');

            if (!$body.data('doScrolling')) {
                var $firstError = $(event.target).closest('.form-group.has-error');
                if ($firstError.length > 0) {
                    $body.data('doScrolling', true);
                    var $nav        = $('#jtl-nav-wrapper.sticky-top'),
                        fixedOffset = $nav.length > 0 ? $nav.outerHeight() : 0,
                        vpHeight    = $(window).height(),
                        scrollTop   = $(window).scrollTop();
                    if ($firstError.offset().top > (scrollTop + vpHeight) || $firstError.offset().top < scrollTop) {
                        $('html, body').animate(
                            {
                                scrollTop: $firstError.offset().top - fixedOffset - parseInt($firstError.css('margin-top'))
                            },
                            {
                                done: function () {
                                    $body.data('doScrolling', false);
                                }
                            }, 300
                        );
                    }
                }
            }
        }, true);
    }

    for (var i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener('blur', function (event) {
            checkInputError(event);
        }, true);
    }
    for (var i = 0; i < checkables.length; i++) {
        checkables[i].addEventListener('click', function (event) {
            checkInputError(event);
        }, true);
    }
    for (var i = 0; i < selects.length; i++) {
        selects[i].addEventListener('change', function (event) {
            checkInputError(event);
        }, true);
    }
}

const customAnchorScrollingListener = function customAnchorScrollingListener(event) {
    event.preventDefault();
    const href = event.target.getAttribute('href');
    if (href === null) {
        return false;
    }

    let targetID = href.replace(/^#/, '');
    if (targetID === '') {
        return false;
    }

    const scrollTo = document.getElementById(targetID);
    if (scrollTo) {
        if (scrollTo.scrollIntoView) {
            scrollTo.scrollIntoView({ behavior: 'smooth' });
        } else {
            let rect = scrollTo.getBoundingClientRect();
            window.scrollTo(rect.x, rect.y);
        }
    }
};

const setCustomAnchorScrolling = function setCustomAnchorScrolling() {
    const links = Array.prototype.slice.call(
        document.querySelectorAll('a'),
        0
    );
    links.filter(el => (el.getAttribute('href') || '').match(/^#/)).forEach(el => {
        let targetID = el.getAttribute('href').replace(/^#/, '');
        if (targetID === '') {
            return false;
        }

        const scrollTo = document.getElementById(targetID);
        if (scrollTo) {
            el.removeEventListener('click', customAnchorScrollingListener);
            el.addEventListener('click', customAnchorScrollingListener);
        }
    });
};

function checkInputError(event)
{
    var $target = $(event.target);
    if ($target.parents('.cfg-group') != undefined) {
        $target.parents('.cfg-group').find('div.form-error-msg').remove();
    }
    $target.parents('.form-group').find('div.form-error-msg').remove();

    if ($target.data('must-equal-to') !== undefined) {
        var $equalsTo = $($target.data('must-equal-to'));
        if ($equalsTo.length === 1) {
            var theOther = $equalsTo[0];
            if (theOther.value !== '' && theOther.value !== event.target.value && event.target.value !== '') {
                event.target.setCustomValidity($target.data('custom-message') !== undefined ? $target.data('custom-message') : sanitizeOutput(event.target.validationMessage));
            } else {
                event.target.setCustomValidity('');
            }
        }
    }

    if (event.target.validity.valid) {
        $target.closest('.form-group').removeClass('has-error');
    } else {
        $target.closest('.form-group').addClass('has-error').append('<div class="form-error-msg">' + sanitizeOutput(event.target.validationMessage) + '</div>');
    }
}

function captcha_filled() {
    $('.g-recaptcha').closest('.form-group').find('div.form-error-msg').remove();
}

function isTouchCapable() {
    return 'ontouchstart' in window || (window.DocumentTouch && document instanceof window.DocumentTouch);
}

function initWow()
{
    new WOW().init();
}
/*
$(window).load(function(){
    navigation();
});*/

$(document).ready(function () {
    $('.collapse-non-validate')
        .on('hidden.bs.collapse', function(e) {
            $(e.target)
                .addClass('hidden')
                .find('fieldset, .form-control')
                .attr('disabled', true);
            e.stopPropagation();
        })
        .on('show.bs.collapse', function(e) {
            $(e.target)
                .removeClass('hidden')
                .attr('disabled', false);
            e.stopPropagation();
        }).on('shown.bs.collapse', function(e) {
            $(e.target)
                .find('fieldset, .form-control')
                .filter(function (i, e) {
                    return $(e).closest('.collapse-non-validate.collapse').hasClass('show');
                })
                .attr('disabled', false);
            e.stopPropagation();
        });
    $('.collapse-non-validate.collapse.show')
        .removeClass('hidden')
        .find('fieldset, .form-control')
        .attr('disabled', false);
    $('.collapse-non-validate.collapse:not(.show)')
        .addClass('hidden')
        .find('fieldset, .form-control')
        .attr('disabled', true);

    $('.collapse.collapse-with-button')
        .on('show.bs.collapse', function (e) {
            let btn = $('a[data-toggle="collapse"][href="#' + $(e.target).attr("id") + '"][data-label-show][data-label-hide]');
            btn.html(btn.data('labelHide'));
        })
        .on('hidden.bs.collapse', function (e) {
            let btn = $('a[data-toggle="collapse"][href="#' + $(e.target).attr("id") + '"][data-label-show][data-label-hide]');
            btn.html(btn.data('labelShow'));
        });
    $('.collapse.collapse-with-clip')
        .on('shown.bs.collapse', function (e) {
            $('#clip-text-' + $(e.target).attr("id")).hide();
        })
        .on('hide.bs.collapse', function (e) {
            $('#clip-text-' + $(e.target).attr("id")).show();
        });

    $('#complete-order-button').on('click', function () {
        var commentField = $('#comment'),
            commentFieldHidden = $('#comment-hidden');
        if (commentField && commentFieldHidden) {
            commentFieldHidden.val(commentField.val());
        }
    });

    $(document).on('click', '.footnote-vat a, .versand, .popup', function(e) {
        let url     = e.currentTarget.href,
            classes = $(this).data('modal-classes') || '';
        url += (url.indexOf('?') === -1) ? '?isAjax=true' : '&isAjax=true';
        eModal.ajax({
            size: 'xl ' + classes,
            url: url,
            title: typeof e.currentTarget.title !== 'undefined' ? e.currentTarget.title : '',
            keyboard: true,
            tabindex: -1,
            buttons: false
        });
        e.stopPropagation();
        return false;
    });

    $(document).on('click', '.pagination-ajax a:not(.active), .js-pagination-ajax:not(.active)', function(e) {
        let url = $(this).attr('href');
        history.pushState(null, null, url);
        loadContent(url, setCustomAnchorScrolling);
        return e.preventDefault();
    });

    if ($('.js-pagination-ajax').length > 0) {
        window.addEventListener('popstate', () => {
            loadContent(document.location.href, setCustomAnchorScrolling);
        }, false);
    }

    setCustomAnchorScrolling();

    $('.dropdown .dropdown-menu.keepopen').on('click touchstart', function(e) {
        e.stopPropagation();
    },{passive: true});

    if (typeof $.fn.jtl_search === 'undefined') {
        var productSearch = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('keyword'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote:         {
                url:      $.evo.io().options.ioUrl + '?io={"name":"suggestions", "params":["%QUERY"]}',
                wildcard: '%QUERY'
            }
        });

        let $searchInput = $('input[name="qs"]');
        $searchInput.typeahead(
            {
                highlight: true
            },
            {
                name:      'product-search',
                display:   'keyword',
                source:    productSearch,
                templates: {
                    suggestion: function (e) {
                        return e.suggestion;
                    }
                }
            }
        );
        $searchInput.on('keydown keyup blur', function () {
            if ($(this).val().length === 0) {
                $(this).closest('form').find('.form-clear').addClass('d-none');
            } else {
                $(this).closest('form').find('.form-clear').removeClass('d-none');
            }
        });
        $('.search-wrapper .form-clear').on('click', function() {
            $searchInput.typeahead('val', '');
            $(this).addClass('d-none');
        });
    }

    let citySuggestion = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('keyword'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote:         {
            url:      $.evo.io().options.ioUrl + '?io={"name":"getCitiesByZip", "params":["%QUERY", "'
                + $(this).closest('fieldset').find('.country-input').val() + '", "'
                + $(this).closest('fieldset').find('.postcode_input').val() + '"]}',
            wildcard: '%QUERY'
        },
        dataType: "json"
    });

    let postCodeSuggestion = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('keyword'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote:         {
            url:      $.evo.io().options.ioUrl + '?io={"name":"getZips", "params":["%QUERY", "'
                + $(this).closest('fieldset').find('.country-input').val() + '", "'
                + $(this).closest('fieldset').find('.city_input.tt-input').val() + '"]}',
            wildcard: '%QUERY'
        },
        dataType: "json"
    });

    $('.city_input').on('focusin change', function () {
        citySuggestion.remote.url = $.evo.io().options.ioUrl + '?io={"name":"getCitiesByZip", "params":["%QUERY", "'
            + $(this).closest('fieldset').find('.country-input').val() + '", "'
            + $(this).closest('fieldset').find('.postcode_input.tt-input').val() + '"]}';

        postCodeSuggestion.remote.url = $.evo.io().options.ioUrl + '?io={"name":"getZips", "params":["%QUERY", "'
            + $(this).closest('fieldset').find('.country-input').val() + '", "'
            + $(this).val() + '"]}';
        
        const oldVal = $(this).closest('fieldset').find('.city_input.tt-input').val();
        $(this).closest('fieldset').find('.city_input').parent().siblings('label').addClass('focused');
        $(this).closest('fieldset').find('.city_input')
            .typeahead('val', '.')
            .typeahead('val', oldVal);
    });

    $('.city_input').on('focusout', function () {
        if ($(this).closest('fieldset').find('.city_input.tt-input').val() == ''){
            $(this).closest('fieldset').find('.city_input').parent().siblings('label').removeClass('focused');
        }
    });

    $('.postcode_input').on('focusin change', function () {
        postCodeSuggestion.remote.url = $.evo.io().options.ioUrl + '?io={"name":"getZips", "params":["%QUERY", "'
            + $(this).closest('fieldset').find('.country-input').val() + '", "'
            + $(this).closest('fieldset').find('.city_input.tt-input').val() + '"]}';

        citySuggestion.remote.url = $.evo.io().options.ioUrl + '?io={"name":"getCitiesByZip", "params":["%QUERY", "'
            + $(this).closest('fieldset').find('.country-input').val() + '", "'
            + $(this).val() + '"]}';

        const oldVal = $(this).closest('fieldset').find('.postcode_input.tt-input').val();
        $(this).closest('fieldset').find('.postcode_input').parent().siblings('label').addClass('focused');
        $(this).closest('fieldset').find('.postcode_input')
            .typeahead('val', '.')
            .typeahead('val', oldVal);
    });

    $('.postcode_input').on('focusout', function () {
        if ($(this).closest('fieldset').find('.postcode_input.tt-input').val() == ''){
            $(this).closest('fieldset').find('.postcode_input').parent().siblings('label').removeClass('focused');
        }
    });

    $('.country-input').on('change', function () {
        citySuggestion.remote.url = $.evo.io().options.ioUrl + '?io={"name":"getCitiesByZip", "params":["%QUERY", "'
            + $(this).val() + '", "'
            + $(this).closest('fieldset').find('.postcode_input.tt-input').val() + '"]}';

        postCodeSuggestion.remote.url = $.evo.io().options.ioUrl + '?io={"name":"getZips", "params":["%QUERY", "'
            + $(this).val() + '", "'
            + $(this).closest('fieldset').find('.city_input.tt-input').val() + '"]}';
    });

    $('.postcode_input').typeahead(
        {
            hint: true,
            highlight: true,
            minLength: 1
        },
        {
            limit:  20,
            name:   'zipcodes',
            source: postCodeSuggestion
        }
    ).bind('typeahead:select', function() {
        let cityInput = $(this).closest('fieldset').find('.city_input');
        if (cityInput.val() === '') {
            cityInput.trigger('focusin');

            setTimeout(function() {
                if (cityInput.val() === '') {
                    let hints = cityInput.typeahead('getHints');
                    if (hints.length === 1 && hints[0].innerHTML.length > 1) {
                        cityInput.typeahead('val', hints[0].innerHTML);
                        cityInput.typeahead('close');
                        cityInput.trigger('change');
                    }
                }
            }, 1000);
        }
    });
    $('.postcode_input').each(function() {
        if ($(this).val() !== '') {
            $(this).trigger('focusin');
        }
    });

    $('.city_input').typeahead(
        {
            hint: true,
            highlight: true,
            minLength: 1
        },
        {
            limit:  20,
            name:   'cities',
            source: citySuggestion
        }
    );
    $('.city_input').each(function() {
        if ($(this).val() !== '') {
            $(this).trigger('focusin');
        }
    });

    $('.btn-offcanvas').on('click', function() {
        $('body').trigger('click');
    });

    if ("ontouchstart" in document.documentElement) {
        $('.variations .swatches .variation').on('mouseover', function() {
            $(this).trigger('click');
        });
    }

    /*
     * show subcategory on caret click
     */
    $('section.box-categories .nav-panel li a').on('click', function(e) {
        if ($(e.target).hasClass("nav-toggle")) {
            $(e.delegateTarget)
                .parent('li')
                .find('> ul.nav').toggle();
            return false;
        }
    });

    /*
     * show linkgroup on caret click
     */
    $('section.box-linkgroup .nav-panel li a').on('click', function(e) {
        if ($(e.target).hasClass("nav-toggle")) {
            $(e.delegateTarget)
                .parent('li')
                .find('> ul.nav').toggle();
            return false;
        }
    });

    /*
     * Banner
     */
    var bannerLink = $('.banner > a:not(.empty-popover)');
    bannerLink.popover({
        html:      true,
        placement: 'bottom',
        trigger:   'hover',
        container: 'body',
        sanitize: false,
        template:  	'<div class="popover popover-min-width" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
        content:   function () {
            return $(this).children('.area-desc').html()
        }
    });

    bannerLink.on('mouseenter', function () {
        $(this).animate({
            borderWidth: 2,
            opacity:     0.5
        }, 300);
    });

    bannerLink.on('mouseleave', function () {
        $(this).animate({
            borderWidth: 0,
            opacity:     1
        }, 300);
    });

    $('.banner').on('mouseenter', function () {
        $(this).children('a').animate({
            borderWidth: 8,
            opacity:     0
        }, 900, function () {
            $(this).css({opacity: 1, borderWidth: 0});
        });
    });

    $('.banner > a[href=""]').on('click', function () {
        return false;
    });

    /*
     * alert actions
     */
    $('.alert .close').on('click', function (){
        $(this).parent().fadeOut(1000);
    });

    $('.alert').each(function(){
        if ($(this).data('fade-out') > 0) {
            $(this).fadeOut($(this).data('fade-out'));
        }
    });

    /*
     * margin to last filter-box
     */
    $('aside .box[class*=box-filter-]').last().addClass('mb-5');

    /*
     * set bootstrap viewport
     */
    (function($, document, window, viewport){
        var $body = $('body');

        $(window).on('resize',
            viewport.changed(function() {
                $body.attr('data-viewport', viewport.current());
            })
        );
        $body.attr('data-viewport', viewport.current());
        $body.attr('data-touchcapable', isTouchCapable() ? 'true' : 'false');
    })(jQuery, document, window, ResponsiveBootstrapToolkit);


    $('.onchangeSubmit').on('change', function(){
        this.form.submit();
    });

    $('#mobile-search-dropdown').on('click', function() {
        setTimeout(function(){
            $('#search-header-desktop').focus();
        },100);
    });

    categoryMenu();
    regionsToState();
    compatibility();
    addValidationListener();
    initWow();
    setClickableRow();

    document.addEventListener('lazybeforesizes', function(e){
        //use width of parent node instead of the image width itself
        var parent = e.target.parentNode;

        if(parent.nodeName == 'PICTURE'){
            parent = parent.parentNode;
        }
        e.detail.width = parent.offsetWidth || e.detail.width;
    });

    // init auto expand for textareas
    $('textarea.auto-expand').on('input click', function (event) {
        autoExpand(event.target);
    });

    $('input[maxlength][data-chars-left-msg]').on('input', function (event) {
        let maxLength = $(this).attr('maxlength'),
            limit     = .1 * maxLength,
            remaining  = maxLength - $(this).val().length;

        $(this).closest('.form-group').find('.char-left').remove();

        if (remaining <= limit) {
            $(this).closest('.form-group').append(
                '<div class="text-warning char-left"><b>' + remaining + '</b> ' + $(this).data('chars-left-msg') + '</div>'
            );
        }
    });

    $('.video-transcript').each(function() {
        let container = $(this);
        container.find('[data-toggle="collapse"]').on('click', function() {
            $(this).attr('aria-expanded', function(i, attr) {
                return attr === 'true' ? 'false' : 'true'
            });
            container.find('.collapse').collapse('toggle');
        }).on('focus', function() {
            $(this).parent('.video-transcript').toggleClass('focus');
        })
    });

    $('[data-video-transcript]').each(function() {
        let transcriptElement = $(this);

        transcriptElement.on('click', function (e) {
            e.preventDefault();
            let content = transcriptElement.data('video-transcript');
            if (content !== '') {
                let popup = window.open(
                    '',
                    'TranscriptWindow',
                    'width=600, height=400, scrollbars=yes'
                );
                $(popup.document.body).html(content);
            }
        });
    });

    $('.cart-icon-dropdown').on('show.bs.dropdown', function() {
        const $topBar = $('#header-top-bar');
        let maxMiniCartHeight = $(window).height()
            - ($('header').outerHeight() || 0)
            - 12;
        if ($topBar.length && $topBar.isInViewport()) {
            maxMiniCartHeight -= $topBar.outerHeight();
        }
        $(this).find('.dropdown-menu').css({maxHeight: maxMiniCartHeight});
    });
});

function setClickableRow()
{
    $('.clickable-row').off().on('click', function() {
        window.location = $(this).data('href');
    });
}

function isMobileByBodyClass() {
    return $('body').hasClass('is-mobile');
}

function autoExpand(field) {
    field.style.height = 'inherit';

    let computed = window.getComputedStyle(field),
        height = parseInt(computed.getPropertyValue('border-top-width'), 10)
            + parseInt(computed.getPropertyValue('padding-top'), 10)
            + field.scrollHeight
            + parseInt(computed.getPropertyValue('padding-bottom'), 10)
            + parseInt(computed.getPropertyValue('border-bottom-width'), 10);

    field.style.height = height + 'px';
}

$.fn.isInViewport = function() {
    let elementTop = $(this).offset().top;
    let elementBottom = elementTop + $(this).outerHeight();

    let viewportTop = $(window).scrollTop();
    let viewportBottom = viewportTop + $(window).height();

    return elementBottom > viewportTop && elementTop < viewportBottom;
};
