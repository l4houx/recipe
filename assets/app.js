/* darkmode js */
import './vendor/darkMode.js';

/* Libs CSS */
/*
import './vendor/@fortawesome/fontawesome-free/css/all.min.css';
import './vendor/bootstrap-icons/font/bootstrap-icons.min.css';
import './vendor/feather-icons/dist/feather-icons.css';
import './vendor/simplebar/dist/simplebar.min.css';
import './vendor/nouislider/dist/nouislider.min.css';
import './vendor/tiny-slider/tiny-slider.css';
*/

/* Theme CSS */
import './styles/css/app.min.css';

// loads the Bootstrap plugins
//import './vendor/jquery/jquery.index.js';


/* Scripts */
//import './vendor/nouislider/nouislider.index.js';

/* Libs JS */
import './vendor/@popperjs/core/core.index.js';
import './vendor/bootstrap/bootstrap.index.js';
//import './vendor/simplebar/dist/simplebar.min.js';
//import './vendor/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js';
//import './vendor/tiny-slider/tiny-slider.index.js';


/*
import canvasConfetti from 'canvas-confetti';

document.body.addRecipeListener('click', () => {
    canvasConfetti()
})
*/

// start the Stimulus application
import './bootstrap.js';

/* Theme JS */
import './js/theme.min.js';

$(document).ready(function () {
    // Initializes Font Awesome picker
    if ($('.icon-picker').length) {
        $('.icon-picker').iconpicker({
            animation: false,
            inputSearch: true
        });
    }

    // Initializes wysiwyg editor
    if ($('.wysiwyg').length) {
        $('.wysiwyg').summernote({
            height: 500,
        });
    }

    // Initializes form collection plugin


    // Tags input
    if ($(".tags-input").length) {
        $(".tags-input").each(function () {
            $(this).tagsinput({
                tagClass: 'badge bg-primary'
            });
        });
        $('.bootstrap-tagsinput').each(function () {
            $(this).addClass('form-control');
        });
    }

    // Datetimepickers
    if ($('.datetimepicker').length) {
        $('.datetimepicker').each(function () {
            $(this).datetimepicker({
                format: 'Y-m-d H:i'
            });
        });
    }

    if ($('.datepicker').length) {
        $('.datepicker').each(function () {
            $(this).datetimepicker({
                format: 'Y-m-d',
                timepicker: false
            });
        });
    }

    // Recipe favorites ajax add and remove
    /*
    $(document).on("click", ".recipe-favorites-new, .recipe-favorites-remove", function () {
        var $thisButton = $(this);
        if ($thisButton.attr("data-action-done") == "1") {
            $thisButton.unbind("click");
            return false;
        }
        $.ajax({
            type: "GET",
            url: $thisButton.data('target'),
            beforeSend: function () {
                $thisButton.attr("data-action-done", "1");
                $thisButton.html("<i class='fas fa-spinner fa-spin'></i>");
            },
            success: function (response) {
                if (response.hasOwnProperty('success')) {
                    if ($thisButton.hasClass('recipe-favorites-new')) {
                        $thisButton.html('<i class="fas fa-heart"></i>');
                    } else {
                        $thisButton.html('<i class="far fa-heart"></i>');
                    }
                    $thisButton.attr("title", response.success).tooltip("_fixTitle");
                    showStackBarTop('success', '', response.success);
                } else if (response.hasOwnProperty('danger')) {
                    $thisButton.html('<i class="far fa-heart"></i>');
                    $thisButton.attr("title", response.danger).tooltip("_fixTitle");
                    showStackBarTop('danger', '', response.danger);
                } else {
                    $thisButton.html('<i class="far fa-heart"></i>');
                    $thisButton.attr("title", Translator.trans('An danger has occured', {}, 'javascript')).tooltip("_fixTitle");
                    showStackBarTop('danger', '', Translator.trans('An danger has occured', {}, 'javascript'));
                }
            }
        });
    });
    */

    // Follow / unfollow restaurant
    /*
    $(document).on("click", ".restaurant-follow, .restaurant-unfollow", function () {
        var $thisButton = $(this);
        if ($thisButton.attr("data-action-done") == "1") {
            $thisButton.unbind("click");
            return false;
        }
        $.ajax({
            type: "GET",
            url: $thisButton.data('target'),
            beforeSend: function () {
                $thisButton.attr("data-action-done", "1");
                $thisButton.html("<i class='fas fa-spinner fa-spin'></i>");
            },
            success: function (response) {
                if (response.hasOwnProperty('success')) {
                    if ($thisButton.hasClass('restaurant-follow')) {
                        $thisButton.html('<i class="fas fa-folder-plus"></i>');
                    } else {
                        $thisButton.html('<i class="fas fa-folder-minus"></i>');
                    }
                    $thisButton.attr("title", response.success).tooltip("_fixTitle");
                    showStackBarTop('success', '', response.success);
                } else if (response.hasOwnProperty('danger')) {
                    $thisButton.html('<i class="fas fa-folder"></i>');
                    $thisButton.attr("title", response.danger).tooltip("_fixTitle");
                    showStackBarTop('danger', '', response.danger);
                } else {
                    $thisButton.html('<i class="fas fa-folder"></i>');
                    $thisButton.attr("title", Translator.trans('An danger has occured', {}, 'javascript')).tooltip("_fixTitle");
                    showStackBarTop('danger', '', Translator.trans('An danger has occured', {}, 'javascript'));
                }
            }
        });
    });
    */

    // Initializes Bloodhound Search Engine
    /*
    var recipesForTopSearch = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: location.protocol + '//' + location.host + Routing.generate('get_recipes', {'_locale': $('html').attr('lang')}, false) + "?q=%QUERY",
            wildcard: '%QUERY'
        },
    });

    $('.top-search').typeahead({
        hint: false,
        highlight: true,
        minLength: 0,
        limit: 3
    }, {
        name: 'top-search',
        display: 'text',
        source: recipesForTopSearch,
        templates: {
            empty: [
                '<div class="dropdown-menu show">',
                Translator.trans('No results found', {}, 'javascript'),
                '</div>'
            ].join('\n'),
            suggestion: Handlebars.compile($("#top-search-result-template").html())
        }
    });
    */

    // Color picker
    $(".color-picker").colorpicker();
});

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
