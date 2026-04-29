let workStateTimer;
let currentStateTimer;

let currentFontSize = 'normal';
let currentDTFontSize = 'normal';

window.LaravelDataTables = window.LaravelDataTables || {};

$(document).ready(function ($) {
    'use strict';
console.log();
    window.csrfToken = $('meta[name="csrf-token"]').attr('content');
    let fromAllSwitch = $('#getNotificationFromAll');
    let departments = $('#departmentsToGetNotificationsFrom');
    let employees = $('#employeesToGetNotificationsFrom');

    departments.on("chosen:ready", function(){
        if (fromAllSwitch.prop('checked') === true) {
            departments.closest('.form-group').addClass('d-none');
        }
    });

    employees.on("chosen:ready", function(){
        if (fromAllSwitch.prop('checked') === true) {
            employees.closest('.form-group').addClass('d-none');
        }
    });


    $('.menu-list a.nav-link').each(function(){
        let thisHref = $(this).attr('href');
        if(thisHref !== '#' && thisHref === window.location.href)
            $(this)
                .addClass('active')
                .closest('div.submenu')
                .removeClass('collapse')
                .prev().attr('aria-expanded','true');
    });

    // ==============================================================
    // Select all script
    // ==============================================================
    $('input.select_all').on('click', function () {
        $(this).closest('table').find('input.chk_item').prop('checked', $(this).prop('checked'));
    });

    setWindow();

    $( window ).resize(function() {
        setWindow();
    });

    $('.navbar-toggler').on('click',function (e) {
        if( $(window).width() > 991-17) {
            e.stopPropagation();
            if($(window).width() > 1200-16) {
                $('body').toggleClass('closed-navbar');
                $.ajax({
                    beforeSend: function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                    },
                    method: "POST",
                    url: "/employee/settings/leftmenucollapse",
                    data: {
                        leftMenuCollapse: {
                            value: $('body').hasClass('closed-navbar') ? 1 : 0
                        }
                    }
                })
                    .done(function (response) {
                        handleAjaxResponse(response);
                    })

                    .fail(function (response) {
                        handleAjaxResponse(response);
                    });
            }
        }
    });

    let $dateOfBirth = $('#date_of_birth');

    $dateOfBirth.flatpickr({
        maxDate: $.now()
    });

    $dateOfBirth.attr('readonly', false);

    fromAllSwitch.on('change', function() {
        departments.closest('.form-group').toggleClass('d-none');
        employees.closest('.form-group').toggleClass('d-none');
    });

    $(document).on('click','.removeAllNotifications', function(e){
        e.preventDefault();

        $.ajax({
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "POST",
            url: '/read_notifications',
        })
            .done(function (response) {
                $('.dropdown-menu.dropdown-menu-right.nav-user-dropdown')
                    .html('')
                    .removeClass('show');
                $('.nav-link.btn-primary.notifications')
                    .data('count',0)
                    .removeAttr('data-count');
            })
            .fail(function (response) {
                handleAjaxResponse(response);
            });
    });

    $('div#navbarSupportedContent').find('a.global-year').on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();

        $(this).siblings('div.dropdown-menu').toggleClass('show');

        $(this).parents('li').first().toggleClass('show');

        if ($(this).attr('aria-expanded') === 'true') {
            $(this).attr('aria-expanded', 'false');
        } else {
            $(this).attr('aria-expanded', 'true');
            initChosen();
        }
    });

    $('select#global_year').on('change', function (e) {
        $.ajax( {
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "POST",
            url: '/save_selected_year',
            data: {
                global_year : $('select#global_year').val()
            }
        })
            .done(function (response) {
                window.location.reload();
                handleAjaxResponse(response);
            })
            .fail(function (response) {
                handleAjaxResponse(response);
            });
    });

    $(document).on('shown.bs.tab', function(e) {
        initChosen();
    });

    //initChosen();

    //reservation index request
    $('#reserv_get_button').on('click', function (e) {
        localStorage.setItem("selectResourceType", $('input[name="select_resource_type"]').val());
        $.ajax( {
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "POST",
            url: '/reservation/view',
            data: {
                //resource_type: $('select#select_resource_type').val(),
                //resource_type: $('#reserv_get_button').val(),
                resource_type: $('input[name="select_resource_type"]').val(),
                resource: $('select#select_resource').val()
            }
        })
            .done(function (response) {

                if(typeof response.reservation !== 'undefined' && response.reservation.length !== 0) {
                    let appendUrl = '/reservation/reservcalendar?response='+btoa(unescape(encodeURIComponent(JSON.stringify(response.reservation))));
                    localStorage.setItem("appendUrl", encodeURIComponent(appendUrl));
                    window.location.replace(appendUrl);
                } else {
                    //let appendUrl = '/reservation/reservcalendar?response=null'; //korabbi_megvalositashoz
                    let appendUrl = '/reservation/reservcalendar?response='+btoa(unescape(encodeURIComponent(JSON.stringify(response.reservation))));
                    localStorage.setItem("appendUrl", encodeURIComponent(appendUrl));
                    window.location.replace(appendUrl);
                }

            })
            .fail(function (response) {
                handleAjaxResponse(response);
            });
    });

    registerHaveAnIdeaModal();

    registerWorkStateModal();

    setFontSize();

    //w_32141 - crop profile image
    if (typeof $('#upload-profile-crop') !== 'undefined' && $('#upload-profile-crop').length == 1) {

        $('.image-upload-result').prepend('<i class="fas fa-upload"></i>');
        $('.image-upload-modify').prepend('<i class="fas fa-pen"></i>');
        $('.profile-image-delete').prepend('<i class="fas fa-trash"></i>');
        $('i.fa-upload').css('margin-right', '10px');
        $('i.fa-pen').css('margin-right', '10px');
        $('i.fa-trash').css('margin-right', '10px');

        $('.image-upload-result').on('click', function (e) {
            $('#profile-dialog').modal("show");
            $('.modal-title').html("Profilkép hozzáadása");
            $('.upload-modify-result').attr('class', 'btn btn-primary upload-result');
            $('#upload-modify').attr('id', 'upload');
            $('#upload').show();
            $('.dropbox-form').show();
            $('#dropbox-modal-div').show();
            $('.upload-result').html("Mentés");

            // oroginal Cropper integration
            $('#upload').on('change', function () {
                var reader = new FileReader();
                reader.onload = function (e) {
                    uploadCrop.croppie('bind', {
                        url: e.target.result
                    }).then(function () {
                        //console.log('jQuery bind complete');
                    });
                }
                reader.readAsDataURL(this.files[0]);
            });
        });

        $('.image-upload-modify').on('click', function (e) {
            $('#profile-dialog').modal("show");
            $('.modal-title').html("Profilkép módosítása");
            $('.upload-result').attr('class', 'btn btn-primary upload-modify-result');
            $('#upload').attr('id', 'upload-modify');
            $('.upload-modify-result').html("Módosítás");
            $('#upload-modify').hide();
            $('.dropbox-form').hide();
            $('#dropbox-modal-div').hide();

            var reader = new FileReader();
            let image = $('#profile-image-content').children()[0].currentSrc;
            uploadCrop.croppie('bind', {
                url: image
            }).then(function () {
                console.log('jQuery bind complete');
            });
        });


        let uploadCrop = $('#upload-profile-crop').croppie({
            enableExif: true,
            viewport: {
                width: 200,
                height: 200,
                type: 'circle'
            },
            boundary: {
                width: 200,
                height: 200
            }
        });

        $('.upload-result').on('click', function (e) {

            let formUrlId = e.target.formAction;
            let pathname = new URL(formUrlId).pathname;
            let id = pathname.split("/")[3];

            uploadCrop.croppie('result', {
                type: 'canvas',
                size: 'viewport',
                circle: true

            }).then(function (resp) {

                $.ajax({
                    beforeSend: function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                    },
                    url: "/employee/image-crop-insert",
                    type: "POST",
                    data: {
                        "image": resp,
                        "id": id
                    },
                    success: function (data) {
                        let html = '<img src="' + resp + '" />';
                        $("#profile-image-content").html(html);
                        $('#profile-dialog').modal("hide");
                        location.reload();
                    }
                });

            });
        });

        $('.upload-modify-result').on('click', function (e) {

            let formUrlId = e.target.formAction;
            let pathname = new URL(formUrlId).pathname;
            let id = pathname.split("/")[3];

            console.log("/employee/image-crop-modify");

            uploadCrop.croppie('result', {
                type: 'canvas',
                size: 'viewport',
                circle: true

            }).then(function (resp) {

                $.ajax({
                    beforeSend: function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                    },
                    url: "/employee/image-crop-modify",
                    type: "POST",
                    data: {
                        "image": resp,
                        "id": id
                    },
                    success: function (data) {
                        let html = '<img src="' + resp + '" />';
                        $("#profile-image-content").html(html);
                        $('#profile-dialog').modal("hide");
                    }
                });

            });
        });

        $('.profile-image-delete').on('click', function (e) {

            let formUrlId = e.target.formAction;
            let pathname = new URL(formUrlId).pathname;
            let employeeId = pathname.split("/")[3];

            let imageFileText = $('div#profile-image-content').children()[0].attributes[0].nodeValue;

            if (imageFileText.length > 0) {
                $.ajax({
                    beforeSend: function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                    },
                    url: "/employee/image-crop-delete",
                    type: "POST",
                    data: {
                        "employeeId": employeeId,
                        "imageFile": imageFileText
                    },
                    success: function (data) {
                        let html = '<img src="" />';
                        $("#profile-image-content").html(html);
                    }
                });
            }
        });
        //w_32141 - crop profile image
    }
}); // END OF Document Ready

function handleDescriptionModalOkButtonClicked(resolve, modal, textArea) {
    return function() {
        if (textArea.val().length < 1) {
            return;
        }

        resolve(textArea.val());

        textArea.val('');
        modal.removeClass('show');
        modal.css({
            'padding-right': '',
            'display': 'none'
        });
    }
}

function runSpeechRecog(elem, event, outputSelector) {
    event.preventDefault();
    console.log($(elem));
    let outputElement = $(outputSelector);
    let recognization = new webkitSpeechRecognition();
    recognization.onstart = () => {
        console.log($(this));
        $(elem).html('<i class="fas fa-spinner fa-spin"></i>');
    }
    recognization.onresult = (e) => {
        let transcript = e.results[0][0].transcript;
        console.log(transcript);
        outputElement.html(outputElement.html() + transcript);
        $(elem).html('<i class="fas fa-microphone"></i>');
    }
    recognization.start();
}

// function closeButtonPreventDefault(event) {
//     event.preventDefault();
// }

async function showWorkStateChangesStateDescriptionModal() {
    return new Promise((resolve, reject) => {
        const modal = $('#stateChangeTextBoxModal');

        modal.addClass('show');
        modal.css({
            'padding-right': '17px',
            'display': 'block'
        });

        const okButton = modal.find('.okButton');

        const closeButton = modal.find('button.close');

        const textArea = modal.find('#workstate-changes-state-description-textarea');

        okButton.off('click.description');

        okButton.on('click.description', handleDescriptionModalOkButtonClicked(resolve, modal, textArea));

        // closeButton.off('click.descriptionclose');
        //
        // closeButton.on('click.descriptionclose', closeButtonPreventDefault(event));
    });
}

function initChosen() {
    $('.chosenFixed').chosen('destroy').chosen({
        allow_single_deselect: true,
        no_results_text:            typeof(presenterVars.chosen.no_results_text)             !== 'undefined' ? presenterVars.chosen.no_results_text           : 'No results match: ',
        placeholder_text_single:    typeof(presenterVars.chosen.placeholder_text_single)     !== 'undefined' ? presenterVars.chosen.placeholder_text_single   : 'Choose an element!',
        placeholder_text_multiple:  typeof(presenterVars.chosen.placeholder_text_multiple)   !== 'undefined' ? presenterVars.chosen.placeholder_text_multiple : 'Choose same options!'
    });
}

/**
 *
 * @param value
 * @param exp
 * @returns {number}
 */
function round(value, exp) {
    if (typeof exp === 'undefined' || +exp === 0)
        return Math.round(value);

    value = +value;
    exp = +exp;

    if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0))
        return NaN;

    // Shift
    value = value.toString().split('e');
    value = Math.round(+(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp)));

    // Shift back
    value = value.toString().split('e');
    return +(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp));
}

/**
 * Get actual Laravel csrf token
 * @returns {string}
 */
function csrf_token() {
    if (window.csrfToken === undefined) {
        console.error('CSRF token not found. Document ready has gone? You have csrf-token meta element?')
    }
    return window.csrfToken;
}

/**
 *
 * @param messages
 */
function displayErrorMessages(messages) {

    if(typeof messages !== 'undefined') {

        if(Array.isArray(messages)) {
            messages.forEach(function (item) {
                $('#alert-block').append(item);
            });
        }
        else {
            $('#alert-block').append(messages.toString());
        }

        scrollToTop();
    }

}

function limitInputLength(inputElementSelector, maxLength, counterElementSelector = null) {
    $(inputElementSelector).on('input', function (e) {
        let charCount = $(this).val().length;

        if (charCount > maxLength) {
            $(this).val(function(_, value) {
                return value.slice(0, maxLength)
            });

            if (counterElementSelector !== null) {
                charCount = $(this).val().length;
            }
        }
        if (counterElementSelector !== null) {
            $(counterElementSelector).text(`${charCount} / ${maxLength}`);
        }
    });

    $(inputElementSelector).on('keydown', function(e) {
        let charCount = $(this).val().length;

        if (charCount > maxLength) {
            e.preventDefault();
        }
    });
}

function initCounter(inputElementSelector, maxLength, counterElementSelector) {
    let charCount = $(inputElementSelector).val().length;
    $(counterElementSelector).text(`${charCount} / ${maxLength}`);
}

function scrollToTop(duration) {
    $('html, body').animate({
        scrollTop: 0
    }, typeof duration === 'undefined' ? 70 : parseInt(duration));
}

function clearIdSelector( id ) {
    return "#" + id.replace( /([:,.=[\]])/g, "\\$1" );
}

/**
 *
 * @param response Jquery Response
 * @returns {*}
 */
function handleAjaxResponse(response) {

    if ('has_errors' in response && response.has_errors !== false) {
        console.error("Something went wrong... Ajax response:", response);
    }

    if ('messages' in response) {
        displayErrorMessages(response.messages)
    }

    if ('redirect' in response) {
        window.location.href = response.redirect;
    }

    return response
}

/**
 *
 * @param message           string
 * @param type              string  success|info|warning|error
 */
function showMessage(message, type) {
    let output = '<div class="alert alert-dismissible fade show alert-' + type + '" role="alert">' +
                    message +
                 '<button type="button" class="close" data-dismiss="alert" aria-label="Close" ><span aria-hidden="true">×</span></button></div>';

    $('#alert-block').append(output.toString());

    scrollToTop();
}

function setWindow() {
    let width = $(window).width();

    let collapsed = typeof presenterVars.leftMenuCollapse === 'undefined' ? 0 : parseInt(presenterVars.leftMenuCollapse);

    if (width < 1200-17 && width > 991 || collapsed === 1) {
        $('body').addClass('closed-navbar');
    }
    else {
        $('body').removeClass('closed-navbar');
    }
    let profileText = $('.profile-text');
    let $navbarSupportedContent = $('#navbarSupportedContent');
    if(width <= 991) {
        let leftToggler = $('.nav-left-sidebar .menu-list nav button.left-toggler');
        let userNotifications = $navbarSupportedContent.find('ul li.userNotifications');
        let yearSelect = $navbarSupportedContent.find('ul li.global-year');
        profileText.hide();

        if(leftToggler.length > 0)
            $navbarSupportedContent.parent()
                .append(leftToggler);
        if(yearSelect.length > 0)
            $navbarSupportedContent.parent()
                .append('<ul class="mdNav row"></ul>').find('ul.mdNav')
                    .append(yearSelect);
        let mdNav = $('ul.mdNav');
        if(userNotifications.length > 0)
            mdNav.append(userNotifications);
        mdNav.append($('li.nav-user'));

    }
    else {
        if($navbarSupportedContent.next('div.top-button').length > 0)
            $navbarSupportedContent.find('ul li:first-child:not(.dropdown) + li:not(.dropdown)')
                .append( $navbarSupportedContent.next('div.top-button') );
        if($navbarSupportedContent.parent().find('.left-toggler').length > 0)
            $('.nav-left-sidebar .menu-list nav')
                .append($('.left-toggler'));
        if($navbarSupportedContent.parent().find('ul.mdNav').length > 0) {
            $navbarSupportedContent.parent().find('ul.mdNav li.nav-item')
                .insertAfter($navbarSupportedContent.find('ul li:first-child:not(.dropdown) + li:not(.dropdown)'));
            $navbarSupportedContent.parent().find('ul.mdNav')
                .remove();
        }
        profileText.show();
    }
}


/**
 * Statistics callbacks and options generating.
 */

function countFormatCallback(value){
    return formatNumber(value,0,' ',',') + ' db';
}

function dateFormatCallback(value){
    return value.replaceAll('-','.');
}

function priceFormatCallback(value, currency){
    let decimals = 0;
    if(presenterVars['currencyDecimals'] !== 'undefined' && presenterVars['currencyDecimals'] !== undefined){
        decimals = parseInt(presenterVars['currencyDecimals']);
    }
    return formatNumber(value,decimals,' ',',') + ' ' + currency;
}

function setChartOptions(response){
    chartOptions = JSON.parse(response.data.chartOptions);
    if(chartOptions.scales
        && chartOptions.scales.xAxes[0]
        && chartOptions.scales.xAxes[0].ticks){
        chartOptions.scales.xAxes[0].ticks.callback = function(value, index, values) {
            switch(response.data.xAxisDataType){
                case 'date':
                    return dateFormatCallback(value);
                case 'dateLite':
                    return dateFormatCallback(value);
                case 'price':
                    return priceFormatCallback(value, response.data.currency);
                case 'count':
                    return countFormatCallback(value);
                default:
                    return value;
            }
        };
    }
    if(chartOptions.scales
        && chartOptions.scales.yAxes[0]
        && chartOptions.scales.yAxes[0].ticks) {
        chartOptions.scales.yAxes[0].ticks.callback = function (value, index, values) {
            switch (response.data.yAxisDataType) {
                case 'date':
                    return dateFormatCallback(value);
                case 'dateLite':
                    return dateFormatCallback(value);
                case 'price':
                    return priceFormatCallback(value, response.data.currency);
                case 'count':
                    return countFormatCallback(value);
                default:
                    return value;
            }
        };
    }
    chartOptions.tooltips.callbacks.label = function(tooltipItem, data) {
        let label = data.datasets[tooltipItem.datasetIndex].label || '';

        let tooltipValue = tooltipItem.yLabel || '';

        if (label) {
            label += ': ';
        }

        if(tooltipItem.xLabel === undefined || tooltipItem.xLabel === 'undefined'){
            tooltipItem.xLabel = label;
        }

        if(!tooltipValue){
            tooltipValue = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
        }

        switch (response.data.yAxisDataType) {
            case 'date':
                label += dateFormatCallback(tooltipValue);
                break;
            case 'dateLite':
                label += dateFormatCallback(tooltipValue);
                break;
            case 'price':
                label += priceFormatCallback(tooltipValue, response.data.currency);
                break;
            case 'count':
                label += countFormatCallback(tooltipValue);
                break;
            case 'onlydataset':
                label = data.labels[tooltipItem.index] + ': ' + tooltipValue;
                break;
            default:
                label += tooltipValue;
                break;
        }

        return label;
    };

    chartOptions.tooltips.callbacks.title = function(tooltipItem, data) {
        switch (response.data.yAxisDataType) {
            case 'onlydataset':
                return response.data.tooltipTitle;
                break;
            default:
                return data.datasets[tooltipItem[0].datasetIndex].label || '';
                break;
        }
    };

    if (chartOptions.hasOwnProperty('plugins')) {
        if (chartOptions.plugins.hasOwnProperty('datalabels')) {
            chartOptions.plugins.datalabels.formatter = function(value, context) {
                return value + '%';
            };
        }
    }

    return chartOptions;
}

//return a formatted number
function formatNumber(value, numberOfDecimal, thousandSeparator, decimalPointChar)
{
    let isNegative = value.toString().charAt(0) === '-';
    value = isNegative ? parseFloat(value.toString().substr(1)).toFixed(numberOfDecimal) : parseFloat(value).toFixed(numberOfDecimal);
    var val_string = value+'';
    var tmp = val_string.split('.');
    var abs_val_string = (tmp.length === 2) ? tmp[0] : val_string;
    var deci_string = ('0.' + (tmp.length === 2 ? tmp[1] : 0)).substr(2);
    var nb = abs_val_string.length;

    for (var i = 1 ; i < 4; i++)
        if (value >= Math.pow(10, (3 * i)))
            abs_val_string = abs_val_string.substring(0, nb - (3 * i)) + thousandSeparator + abs_val_string.substring(nb - (3 * i));

    if (parseInt(numberOfDecimal) === 0)
        return (isNegative ? '-' : '') + abs_val_string;
    return (isNegative ? '-' : '') + abs_val_string + decimalPointChar + (deci_string > 0 ? deci_string : '00');
}

function registerHaveAnIdeaModal() {
    $('.send-idea').on('click', function (ev) {
        $.ajax({
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "POST",
            url: "/ideas/new",
            data: {
                idea: $('textarea#have_an_idea_textarea').val()
            }
        })
            .done(function (response) {
                $('textarea#have_an_idea_textarea').val('');
                $('#have_an_idea_counter').text('0 / 500');
            })
            .fail(function (response) {
                $('textarea#have_an_idea_textarea').val('');
                $('#have_an_idea_counter').text('0 / 500');
                handleAjaxResponse(response);
            });
    });

    $('#have_an_idea_button').on('click', function (ev) {
       const modal = $('#haveAnIdeaModal');

       modal.modal();
    });
}

function registerWorkStateModal()
{
    $('#state_change_button').on('click', function (e) {
        $.ajax({
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "GET",
            url: "/workstate/availables",
            data: {

            }
        })
            .done(function (response) {
                let modal = $('#stateChangeModal');
                modal.find('#stateChangeDropdownContainer').html(response.data.options);

                let monthlyWorkTimer = modal.find('.monthly-work-timer');
                monthlyWorkTimer.text(response.data.monthly_work_time);

                //total
                let timer = modal.find('.daily-work-timer');
                let timeInSecs = parseInt(response.data.daily_total);
                timer.data('time-in-secs', timeInSecs);

                let hours = parseInt(timeInSecs / 3600);
                let hourMinutes = timeInSecs % 3600;
                let minutes = parseInt(hourMinutes / 60);
                let seconds = hourMinutes % 60;

                let timeString = addZeroDigit(hours) + ':' + addZeroDigit(minutes) + ':' + addZeroDigit(seconds);
                timer.text(timeString);

                //current
                let cTimer = modal.find('.status-timer');
                let cTimeInSecs = parseInt(response.data.current_work_state_time);
                cTimer.data('time-in-secs', cTimeInSecs);

                let cHours = parseInt(cTimeInSecs / 3600);
                let cHourMinutes = cTimeInSecs % 3600;
                let cMinutes = parseInt(cHourMinutes / 60);
                let cSeconds = cHourMinutes % 60;

                let cTimeString = addZeroDigit(cHours) + ':' + addZeroDigit(cMinutes) + ':' + addZeroDigit(cSeconds);
                cTimer.text(cTimeString);

                clearInterval(workStateTimer);
                clearInterval(currentStateTimer);
                if (response.data.has_entered) {
                    if (response.data.current_is_work) {
                        workStateTimer = setInterval(function () {
                            //total
                            timeInSecs = timer.data('time-in-secs');
                            timeInSecs++;
                            timer.data('time-in-secs', timeInSecs);

                            hours = parseInt(timeInSecs / 3600);
                            hourMinutes = timeInSecs % 3600;
                            minutes = parseInt(hourMinutes / 60);
                            seconds = hourMinutes % 60;

                            timeString = addZeroDigit(hours) + ':' + addZeroDigit(minutes) + ':' + addZeroDigit(seconds);

                            timer.text(timeString);
                        }, 1000);
                    }

                    if (!response.data.current_is_exit) {
                        currentStateTimer = setInterval(function () {
                            //current
                            cTimeInSecs = cTimer.data('time-in-secs');
                            cTimeInSecs++;
                            cTimer.data('time-in-secs', cTimeInSecs);

                            cHours = parseInt(cTimeInSecs / 3600);
                            cHourMinutes = cTimeInSecs % 3600;
                            cMinutes = parseInt(cHourMinutes / 60);
                            cSeconds = cHourMinutes % 60;

                            cTimeString = addZeroDigit(cHours) + ':' + addZeroDigit(cMinutes) + ':' + addZeroDigit(cSeconds);

                            cTimer.text(cTimeString);
                        }, 1000);
                    }
                }

                if (response.data.description !== null) {
                    $('div#completed-work-information-div').css('display', 'block');
                    $('textarea#completed-work-information-textarea').val(response.data.description);
                } else {
                    $('div#completed-work-information-div').css('display', 'none');
                }

                modal.find('.status-text').html(response.data.current_state_name);
                const statusTextContainer = modal.find('.status-text-container');
                statusTextContainer.attr('data-is-textbox', response.data.current_is_textbox);
                registerWorkStateLinkListeners();
                modal.modal();
            })

            .fail(function (response) {
                handleAjaxResponse(response);
            });

    });
}

function openStateChangeModal() {
    const modal = $('#stateChangeModal');

    modal.addClass('show');
    modal.css({
        'padding-right': '17px',
        'display': 'block'
    });
}

function closeWorkstateChangesStateDescriptionModal() {
    const modal = $('#stateChangeTextBoxModal');

    $('#workstate-changes-state-description-textarea').val('');
    modal.removeClass('show');
    modal.css({
        'padding-right': '',
        'display': 'none'
    });
}

function getPosition() {
    return new Promise((resolve, reject) => navigator.geolocation.getCurrentPosition(position => resolve(position), error => reject(error)));
}

function showPermissionPrompt(workstateId) {
    var retry = confirm("A státuszváltáshoz szükség van a lokációs adataidra. Kérlek engedélyezd a helymeghatározás elérését és kattints az OK gombra.");
    if (retry) {
        $(`.work-state-link[data-workstate-id=${workstateId}]`).click();
    } else {
        console.log("User declined to enable location services.");
    }
}

function registerWorkStateLinkListeners()
{
    $('.work-state-link').on('click', async function (e) {
        let positions;
        if ($(this).data('is-gps') == '1') {
            try {
                positions = await getPosition();
            } catch (error) {
                if (error.code === error.PERMISSION_DENIED) {
                    console.log("User denied permission to access location.");

                    showPermissionPrompt($(this).data('workstate-id'));
                } else {
                    console.error('Error getting user location: ', error.message);
                }
            }
        }

        let description;

        if ($('#stateChangeModal').find('.status-text-container').attr('data-is-textbox') == '1') {
            description = await showWorkStateChangesStateDescriptionModal();
        }

        console.log(description);

        $.ajax({
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "POST",
            url: "/workstate/change",
            data: {
                work_state_id : $(this).data('workstate-id'),
                latitude: positions ? positions.coords.latitude : null,
                longitude: positions ? positions.coords.longitude : null,
                description: description ? description : null
            }
        })
            .done(function (response) {
                console.log(response.data.current_is_textbox)
                let modal = $('#stateChangeModal');
                modal.find('#stateChangeDropdownContainer').html(response.data.options);
                registerWorkStateLinkListeners();

                modal.find('.status-text').html(response.data.current_state_name);
                const statusTextContainer = modal.find('.status-text-container');
                statusTextContainer.attr('data-is-textbox', response.data.current_is_textbox);

                let monthlyWorkTimer = modal.find('.monthly-work-timer');
                monthlyWorkTimer.text(response.data.monthly_work_time);

                //total
                let timer = modal.find('.daily-work-timer');
                let timeInSecs = parseInt(response.data.daily_total);
                timer.data('time-in-secs', timeInSecs);

                let hours = parseInt(timeInSecs / 3600);
                let hourMinutes = timeInSecs % 3600;
                let minutes = parseInt(hourMinutes / 60);
                let seconds = hourMinutes % 60;

                let timeString = addZeroDigit(hours) + ':' + addZeroDigit(minutes) + ':' + addZeroDigit(seconds);
                timer.text(timeString);

                //current
                let cTimer = modal.find('.status-timer');
                let cTimeInSecs = parseInt(response.data.current_work_state_time);
                cTimer.data('time-in-secs', cTimeInSecs);

                let cHours = parseInt(cTimeInSecs / 3600);
                let cHourMinutes = cTimeInSecs % 3600;
                let cMinutes = parseInt(cHourMinutes / 60);
                let cSeconds = cHourMinutes % 60;

                let cTimeString = addZeroDigit(cHours) + ':' + addZeroDigit(cMinutes) + ':' + addZeroDigit(cSeconds);
                cTimer.text(cTimeString);

                clearInterval(workStateTimer);
                clearInterval(currentStateTimer);
                if (response.data.current_is_work) {
                    workStateTimer = setInterval(function () {
                        //total
                        timeInSecs = timer.data('time-in-secs');
                        timeInSecs++;
                        timer.data('time-in-secs', timeInSecs);

                        hours = parseInt(timeInSecs / 3600);
                        hourMinutes = timeInSecs % 3600;
                        minutes = parseInt(hourMinutes / 60);
                        seconds = hourMinutes % 60;

                        timeString = addZeroDigit(hours) + ':' + addZeroDigit(minutes) + ':' + addZeroDigit(seconds);

                        timer.text(timeString);
                    }, 1000);
                }

                if (!response.data.current_is_exit) {
                    currentStateTimer = setInterval(function () {
                        //current
                        cTimeInSecs = cTimer.data('time-in-secs');
                        cTimeInSecs++;
                        cTimer.data('time-in-secs', cTimeInSecs);

                        cHours = parseInt(cTimeInSecs / 3600);
                        cHourMinutes = cTimeInSecs % 3600;
                        cMinutes = parseInt(cHourMinutes / 60);
                        cSeconds = cHourMinutes % 60;

                        cTimeString = addZeroDigit(cHours) + ':' + addZeroDigit(cMinutes) + ':' + addZeroDigit(cSeconds);

                        cTimer.text(cTimeString);
                    }, 1000);
                }

                if (response.data.description !== null) {
                    $('div#completed-work-information-div').css('display', 'block');
                    $('textarea#completed-work-information-textarea').val(response.data.description);
                } else {
                    $('div#completed-work-information-div').css('display', 'none');
                }
            })

            .fail(function (response) {
                handleAjaxResponse(response);
            });
    });
}

function addZeroDigit(number)
{
    if (number < 10) {
        number = '0' + number;
    }
    return number;
}

function smallerFontSize(button, selector, currentSize)
{
    let fontSizeSelector = $("body, th, td, span, p");

    if (typeof selector !== 'undefined') {
        fontSizeSelector = selector;
    }

    if (typeof currentSize === 'undefined') {
        currentSize = currentFontSize;
    }

    switch (currentSize) {
        case "normal":
            fontSizeSelector.css("fontSize", "-=3");
            break;
        case "bigger":
            fontSizeSelector.css("fontSize", "-=6");
            break;
        case "smaller":
        default:
            return;

    }

    $('a.button-select').removeClass('highlighted-button-select');

    saveFontSizeChange('smaller');

    $(button).addClass('highlighted-button-select');
    currentFontSize = 'smaller';
}

function normalFontSize(button, selector, currentSize)
{
    let fontSizeSelector = $("body, th, td, span, p");

    if (typeof selector !== 'undefined') {
        fontSizeSelector = selector;
    }

    if (typeof currentSize === 'undefined') {
        currentSize = currentFontSize;
    }

    switch (currentSize) {
        case "smaller":
            fontSizeSelector.css("fontSize", "+=3");
            break;
        case "bigger":
            fontSizeSelector.css("fontSize", "-=3");
            break;
        case "normal":
        default:
            return;

    }

    $('a.button-select').removeClass('highlighted-button-select');

    saveFontSizeChange('normal');

    $(button).addClass('highlighted-button-select');
    currentFontSize = 'normal';
}

function biggerFontSize(button, selector, currentSize)
{
    let fontSizeSelector = $("body, th, td, span, p, label");

    if (typeof selector !== 'undefined') {
        fontSizeSelector = selector;
    }

    if (typeof currentSize === 'undefined') {
        currentSize = currentFontSize;
    }

    switch (currentSize) {
        case "smaller":
            fontSizeSelector.css("fontSize", "+=6");
            break;
        case "normal":
            fontSizeSelector.css("fontSize", "+=3");
            break;
        case "bigger":
        default:
            return;

    }

    $('a.button-select').removeClass('highlighted-button-select');

    saveFontSizeChange('bigger');

    $(button).addClass('highlighted-button-select');
    currentFontSize = 'bigger';
}

function saveFontSizeChange(fontSizeText)
{
    $.ajax({
        beforeSend: function (request) {
            request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
        },
        method: "POST",
        url: '/save_font_size',
        data: {
            fontSizeText: fontSizeText
        }
    })
        .done(function (response) {
            console.log('Saved font size');
        })
        .fail(function (response) {
            handleAjaxResponse(response);
        });
}

function setFontSize(selector, currentSize)
{
    let selectedFontSize = $('a.button-select.highlighted-button-select');

    switch (selectedFontSize.data('size')) {
        case 'smaller':
            smallerFontSize(selectedFontSize, selector, currentSize);
            break;
        case 'bigger':
            biggerFontSize(selectedFontSize, selector, currentSize);
            break;
        case 'normal':
        default:
            break;

    }
}

//bootbox-cancel, bootbox-accept modification

$(document).ready(function() {
    $(document).on('shown.bs.modal', function (e) {
        $('.bootbox-cancel').text("Mégse");
        $('.bootbox-accept').text("Mentés");
    });
});

//status changing
window.addEventListener('resize', function() {
    var width = window.innerWidth;
    var statuscols = document.querySelectorAll('.statuscol');

    if (width < 1000) {
        statuscols.forEach(function(item) {
            if (!item.classList.contains('col-12')) {
                item.classList.add('col-12');
            }
        });
    } else {
        statuscols.forEach(function(item) {
            if (item.classList.contains('col-12')) {
                item.classList.remove('col-12');
            }
        });
    }
});