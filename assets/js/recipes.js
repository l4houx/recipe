// snazzy-info-window must be loader after Google Maps is completely loader
if ($('#recipes-map').length && $('#recipes-map').data('recipes').length) {
    function initMap() {
        $.getScript('https://cdn.jsdelivr.net/npm/snazzy-info-window@1.1.1/dist/snazzy-info-window.min.js', function () {
            drawMap($('#recipes-map').data('recipes'));
        });
    }
    global.initMap = initMap;
}

// Initializes Google Maps
function drawMap(recipes) {
    var map = new google.maps.Map(document.getElementById('recipes-map'), {
        zoom: 7,
        center: {
            lat: parseFloat(recipes[0].lat),
            lng: parseFloat(recipes[0].lng)
        }
    });
    var markers = recipes.map(function (recipe, i) {
        var marker = new google.maps.Marker({
            position: {
                lat: parseFloat(recipe.lat),
                lng: parseFloat(recipe.lng)
            },
            icon: $('#recipes-map').data('pin-path')
        });
        var template = Handlebars.compile($('#recipe-info-box').html());
        var info = null;
        var closeDelayed = false;
        var closeDelayHandler = function () {
            $(info.getWrapper()).removeClass('active');
            setTimeout(function () {
                closeDelayed = true;
                info.close();
            }, 300);
        };
        // Add a Snazzy Info Window to the marker
        info = new SnazzyInfoWindow({
            marker: marker,
            wrapperClass: 'custom-window',
            offset: {
                top: '-72px'
            },
            edgeOffset: {
                top: 50,
                right: 60,
                bottom: 50
            },
            border: false,
            closeButtonMarkup: '<button type="button" class="custom-close">&#215;</button>',
            content: template({
                title: recipe.name,
                link: recipe.link,
                bgImg: recipe.image,
                body:
                    '<p class="text-muted"><small>' + recipe.address + '</small></p>' +
                    '<p class="text-muted"><small>' + recipe.date + '</small></p>' +
                    '<p class="text-muted"><small>' + ($('body').data('currency-position') == 'left' ? $('body').data('currency-symbol') : '') + recipe.price + ($('body').data('currency-position') == 'right' ? $('body').data('currency-symbol') : '') + '</small></p>'
            }),
            callbacks: {
                open: function () {
                    $(this.getWrapper()).addClass('open');
                },
                afterOpen: function () {
                    var wrapper = $(this.getWrapper());
                    wrapper.addClass('active');
                    wrapper.find('.custom-close').on('click', closeDelayHandler);
                },
                beforeClose: function () {
                    if (!closeDelayed) {
                        closeDelayHandler();
                        return false;
                    }
                    return true;
                },
                afterClose: function () {
                    var wrapper = $(this.getWrapper());
                    wrapper.find('.custom-close').off();
                    wrapper.removeClass('open');
                    closeDelayed = false;
                }
            }
        });
        return marker;
    });
    new MarkerClusterer(map, markers,
        { imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m' });
}

$(document).ready(function () {
    // Search filters
    $('#filter-local-only').on('change', function () {
        if ($(this).is(':checked')) {
            $('#user-country').show();
            $('#filter-country-container').hide();
            $('#country').select2('val', 'all');
            $('.filter-online-container').hide();
            $('#filter-online-only').prop("checked", false);
            $('#filter-location-container').hide();
            $('#location').val('');
        } else {
            $('#user-country').hide();
            $('#filter-country-container').show();
            $('.filter-online-container').show();
        }
    });
    if (getURLParameter('localonly') == "1") {
        $('#filter-local-only').attr('checked', true);
        $('#filter-local-only').trigger('change');
    }
    if (getURLParameter('category')) {
        $('select#category option').each(function () {
            if ($(this).val() == getURLParameter('category')) {
                $(this).prop('selected', true).trigger('change');
            }
        });
    }
    $('#filter-location-container').hide();
    if (getURLParameter('country')) {
        $('#country option').each(function () {
            if ($(this).val() == getURLParameter('country')) {
                $(this).prop('selected', true).trigger('change');
                $('#filter-location-container').show();
            }
        });
        $('#country').trigger('change');
    }
    $('#country').change(function () {
        if ($(this).val() != "all") {
            $('#filter-location-container').show();
        } else {
            $('#filter-location-container').hide();
            $('#location').val('');
        }
    });
    if (getURLParameter('audience')) {
        $('#' + getURLParameter('audience')).attr('checked', true);
        $('#' + getURLParameter('audience')).closest('label').addClass('active');
    }
    if (getURLParameter('startdate')) {
        if (getURLParameter('startdate') != "today" && getURLParameter('startdate') != "tomorrow" && getURLParameter('startdate') != "thisweekend" && getURLParameter('startdate') != "thisweek" && getURLParameter('startdate') != "nextweek" && getURLParameter('startdate') != "thismonth" && getURLParameter('startdate') != "nextmonth") {
            $('#date-pickadate').val(getURLParameter('startdate'));
            $('#date-pickadate').attr('checked', true);
        } else {
            $('input[name="startdate"][value="' + getURLParameter('startdate') + '"]').attr('checked', true);
        }
    }
    $('#free-recipes-only').on('change', function () {
        if ($(this).is(':checked')) {
            $('.recipes-price-range-slider-wrapper').hide();
            $('#pricemin').val('0');
            $('#pricemin').trigger('change');
            $('#pricemax').val('10000');
            $('#pricemax').trigger('change');
        } else {
            $('.recipes-price-range-slider-wrapper').show();
        }
    });
    if (getURLParameter('freeonly') == "1") {
        $('#free-recipes-only').attr('checked', true);
        $('#free-recipes-only').trigger('change');
    }
    $('#filter-online-only').on('change', function () {
        if ($(this).is(':checked')) {
            $('.location-based-filters').hide();
            $('#filter-local-only').prop("checked", false);
            $('#country').select2('val', 'all');
            $('#filter-location-container').hide();
            $('#location').val('');
        } else {
            $('.location-based-filters').show();
        }
    });
    if (getURLParameter('onlineonly') == "1") {
        $('#filter-online-only').attr('checked', true);
        $('#filter-online-only').trigger('change');
    }
    // Initializes recipes calendar
    if ($('#recipes-calendar').length) {
        var calendarEl = document.getElementById('recipes-calendar');
        var calendar = new Calendar(calendarEl, {
            plugins: ['interaction', dayGridPlugin, timeGridPlugin],
            defaultView: 'dayGridMonth',
            defaultDate: $('#recipes-calendar').data('default-date'),
            height: "auto",
            locale: $('html').attr('lang'),
            header: {
                left: 'prev,next today allrecipescalendarlink',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: Translator.trans("Today", {}, 'javascript'),
                day: Translator.trans("Day", {}, 'javascript'),
                week: Translator.trans("Week", {}, 'javascript'),
                month: Translator.trans("Month", {}, 'javascript')
            },
            recipes: $('#recipes-calendar').data('recipes')
        });
        calendar.render();
    }
});
