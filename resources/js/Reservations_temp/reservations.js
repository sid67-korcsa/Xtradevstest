$(document).ready(function ($) {
    'use strict';

    window.csrfToken = $('meta[name="csrf-token"]').attr('content');

    $('#reservation_subject').on('change',function (e) {
        //check end date
        let _date_postfix = ' 23:59';
        let _date_start = $('#reservation_date_start_input').val();
        let _date_end = $('#reservation_date_end_input').val();
        let _date_end_split = _date_start.split(" ");
        let _date_end_check = _date_end_split[0]+_date_postfix;
        if(_date_end > _date_end_check) {
            $('.switch-button-xs').hide();
            $('#reserv_extend_block_01').hide();
            $('#reserv_extend_block_02').hide();
            $('#reserv_extend_block_03').hide();
        }
    });

    //default switch button value
    //$("#switch_button").val(0);

    $('#reserv_get_button').on('click',function (e) {
        $('.fc-timeGridDay-button').html('Napi nézet');

        var selectResource = $('select#select_resource').val();
        localStorage.setItem("selectResource", selectResource);
    });

    if($('h4.modal-title').html() !== 'Teszt foglalás módosítása') {
        $('#reserv_extend_block_01').hide();
        $('#reserv_extend_block_02').hide();
        $('#reserv_extend_block_03').hide();
        //$("#switch_button" ).prop( "checked", false);
        //$("#switch_button").val(0);
    } else {
        $('#reserv_extend_block_01').show();
        $('#reserv_extend_block_02').show();
        $('#reserv_extend_block_03').show();
        //$("#switch_button" ).prop( "checked", true);
        //$("#switch_button").val(1);
    }

    $('#switch_button').on('click',function (e) {
        if($('#switch_button:checked').length != 1) {
            $('#reserv_extend_block_01').hide();
            $('#reserv_extend_block_02').hide();
            $('#reserv_extend_block_03').hide();
        } else {
            $('#reserv_extend_block_01').show();
            $('#reserv_extend_block_02').show();
            $('#reserv_extend_block_03').show();
        }
    });
    //$( "#switch_button" ).trigger( "click" );

    $('#reservation_all_day').on('click',function (e) {
        if($('#reservation_all_day:checked').length != 0) {
            let timeStart = ' 00:01';
            let timeEnd = ' 23:59';
            let dateStart = $('input#reservation_date_start_input').val().split(" ");
            let dateEnd = $('input#reservation_date_start_input').val().split(" ");
            $('#reservation_date_start_input').val(dateStart[0] + timeStart);
            $('#reservation_date_end_input').val(dateEnd[0] + timeEnd);
        }
    });

    $('#repeat_role_weekday').on('click',function (e) {
        $("#repeat_role").prop('disabled', true);
        $("#repeat_role").val('');
    });

    $('#repeat_role_custom').on('click',function (e) {
        $("#repeat_role").prop('disabled', false);
    });

    $('#switch_button').on('click',function (e) {
        $("#repeat_role").prop('disabled', false);
    });

    $('#repeat_role_end_count_input').on('click',function (e) {
        $("#repeat_start").prop('disabled', true);
        $("#repeat_start").val('');
        $("#repeat_end").prop('disabled', true);
        $("#repeat_end").val('');
    });

    $('#repeat_role_end_date_check').on('click',function (e) {
        $("#repeat_role_end_count_input").val('');
        $("#repeat_role_end_count_input").prop('disabled', true);
        $("#repeat_start").prop('disabled', false);
        $("#repeat_end").prop('disabled', false);
    });

    $('#repeat_role_end_count').on('click',function (e) {
        $("#repeat_role_end_count_input").prop('disabled', false);
        $("#repeat_start").val('');
        $("#repeat_end").val('');
        $("#repeat_start").prop('disabled', true);
        $("#repeat_end").prop('disabled', true);
    });

    $('#repeat_day').on('click',function (e) {
        $("#role_input_text").html('<b>naponta</b>');
        $("#repeat_role_weekday").prop('disabled', false);
        $('#role_weekdays_text').show();
        $('#repeat_role_weekday').css('color','currentColor');
        $('#repeat_role_weekday').css('background-color','var(--form-background)');
        $('#repeat_role_weekday').css('visibility','visible');
        $('#repeat_role_custom_week').attr('id', 'repeat_role_custom');
        $('#repeat_role_custom').css('visibility','visible');
    });
    $('#repeat_week').on('click',function (e) {
        $("#role_input_text").html('<b>hetente</b>');
        $("#repeat_role_weekday").prop('disabled', true);
        $("#repeat_role_weekday").hide();
        $('#repeat_role_weekday').css('display','none');
        $('#repeat_role_weekday').css('visibility','hidden');
        $('#repeat_role_weekday').css('color','white');
        $('#repeat_role_weekday').css('background','white');
        $('#repeat_role_weekday:checked').css('display','none');
        $('#role_weekdays_text').hide();
        $('#repeat_role_custom').attr('id', 'repeat_role_custom_week');
        $('#repeat_role_custom_week').css('visibility','hidden');
    });

    $('#repeat_month').on('click',function (e) {
        $("#role_input_text").html('<b>havonta</b>');
        $("#repeat_role_weekday").prop('disabled', true);
        $("#repeat_role_weekday").hide();
        $('#repeat_role_weekday').css('display','none');
        $('#repeat_role_weekday').css('visibility','hidden');
        $('#repeat_role_weekday').css('color','white');
        $('#repeat_role_weekday').css('background','white');
        $('#repeat_role_weekday:checked').css('display','none');
        $('#role_weekdays_text').hide();
        $('#repeat_role_custom').attr('id', 'repeat_role_custom_month');
        $('#repeat_role_custom_month').css('visibility','hidden');
    });

    $("button[name='select_resource_type']").on('click', function (e) {
        let submitData = $(this).val();
        $('.select_resource_type').css('background-color', 'initial');
        $(this).css('background-color', 'transparent');
        $(this).css('border', '3px');
        $(this).css('background-color', '#C0E3FA');
        if( $('input[name="select_resource_type"]').val() === 'undefined' ) {
            $('<input>').attr({type: 'hidden', name: 'select_resource_type', value: submitData}).appendTo('form');
        } else {
            $('input[name="select_resource_type"]').remove();
            $('<input>').attr({type: 'hidden', name: 'select_resource_type', value: submitData}).appendTo('form');
        }

        localStorage.setItem("selectResourceType", submitData);
        $('#reserv_get_button').attr('value', submitData);
    });

    $('input#reservation_subject').on('change', function (e) {
        localStorage.removeItem('prevSubmitResource');
    });

    $('button.cancelButton').on('click', function (e) {
        localStorage.removeItem('prevSubmitResource');
    });

    $('button.close').on('click', function (e) {
        localStorage.removeItem('prevSubmitResource');
    });

    //modal submit event
    $('.addReservSaveButton').on('click', function (e) {
        e.preventDefault();

        localStorage.removeItem('prevSubmitResource');

        const data = [];

        data["resource_type"] = $('#resource_type').val();
        data["resource"] = $('#resource').val();
        data["reservation_date_start"] = $('#reservation_date_start_input').val();
        data["reservation_date_end"] = $('#reservation_date_end_input').val();
        data["reservation_check"] = true;

        if( ( $('#reservation_date_start_input').val().length == 0 ||
                $('#reservation_date_end_input').val().length == 0 ||
                $('#reservation_subject').val().length == 0)||
            ($('#reservation_date_start_input').val().length == 0 &&
                $('#reservation_date_end_input').val().length == 0 &&
                $('#reservation_subject').val().length == 0)
        ) {
            alert("Kérjük, hogy töltse ki a kötelező mezőket!");
        } else if($('#switch_button:checked').length) {
            //reservation Save repeat - Submit button actions
            if ($('.repeat_type').val() &&
                !$('#repeat_role').val() &&
                !$('#repeat_start').val() &&
                !$('#repeat_end').val() &&
                !$('#repeat_role_end_count_input').val()
            ){
                alert("Összefüggő részek kitöltése kötelező!");
            } else {
                //check end date
                let _date_postfix = ' 23:59:59';
                let _date_start = $('#reservation_date_start_input').val();
                let _date_end = $('#reservation_date_end_input').val();
                let _date_end_split = _date_start.split(" ");
                let _date_end_check = _date_end_split[0]+_date_postfix;
                if($('#reservation_date_end_input').val() < $('#reservation_date_start_input').val()) {
                    alert("A foglalás befejező dátuma nem lehet kissebb mint a kezdő dátum!");
                } else if(_date_end > _date_end_check) {
                    $('#switch_button').hide();
                } else {
                    if($('#resource').val() == 'Kérem válasszon') {
                        alert("Kérem, hogy válasszon eszközt!");
                    } else {
                        //let checkResult = checkReservationData(e, data);
                        if(typeof $("input[name=reservation_update_id]").val() == 'undefined') {
                            console.log("INS");
                            $.ajax({
                                beforeSend: function (request) {
                                    request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                                },
                                method: "POST",
                                url: '/reservation/checkcalendar',
                                data: {
                                    "resource_type": data["resource_type"],
                                    "resource": data["resource"],
                                    "reservation_date_start": data["reservation_date_start"],
                                    "reservation_date_end": data["reservation_date_end"],
                                    "reservation_check": data["reservation_check"]
                                }
                            })
                                .done(function (response) {
                                    //if response is valid and not request error, then logging full response data
                                    console.log(response);
                                    if (typeof response.data.success !== "undefined" && response.data.success) {
                                        //if response.data.success is TRUE, then SUBMIT POST data!
                                        $('#frmresources').submit();
                                    } else {
                                        //else display error text!
                                        window.alert(response.data.reservation_check_text);
                                    }
                                })
                                .fail(function (response) {
                                    handleAjaxResponse(response);
                                });
                        } else {
                            $('#frmresources').submit();
                        }

                    }
                }
            }
        } else {
            //check end date
            if($('#reservation_date_end_input').val() < $('#reservation_date_start_input').val()) {
                alert("A foglalás befejező dátuma nem lehet kissebb mint a kezdő dátum!");
            } else {
                if($('#resource').val() == 'Kérem válasszon') {
                    alert("Kérem, hogy válasszon eszközt!");
                } else {
                    //let checkResult = checkReservationData(e, data);
                    if(typeof $("input[name=reservation_update_id]").val() == 'undefined') {
                        $.ajax({
                            beforeSend: function (request) {
                                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                            },
                            method: "POST",
                            url: '/reservation/checkcalendar',
                            data: {
                                "resource_type": data["resource_type"],
                                "resource": data["resource"],
                                "reservation_date_start": data["reservation_date_start"],
                                "reservation_date_end": data["reservation_date_end"],
                                "reservation_check": data["reservation_check"]
                            }
                        })
                            .done(function (response) {
                                console.log(response);
                                if (typeof response.data.success !== "undefined" && response.data.success) {
                                    $('#frmresources').submit();
                                } else {
                                    window.alert(response.data.reservation_check_text);
                                }
                            })
                            .fail(function (response) {
                                handleAjaxResponse(response);
                            });
                    } else {
                        $('#frmresources').submit();
                    }
                }
            }
        }
    });

    //Ajax request - TODO?
    $('.addReservAlterSaveButton').on('click', function (e) {
        e.preventDefault();

        $.ajax( {
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "POST",
            url: '/reservation/new',
            data: { }
        })
            .done(function (response) {

            })
            .fail(function (response) {
                handleAjaxResponse(response);
            });
    });

    $('.addReservDeleteButton').on('click', function (e) {
        let reservationRepeatId = $('input[name="reservation_update_id"]').val();
        let reservationPresentRepeat = $('input[name="present_repeat"]').val();

        $.ajax( {
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "GET",
            data: {
                "repeat_data": reservationPresentRepeat
            },
            url: '/reservation/deletes/'+reservationRepeatId,
        })
            .done(function (response) {
                let appendUrl = '/reservation/reservcalendar';
                window.location.replace(appendUrl);
            })
            .fail(function (response) {
                handleAjaxResponse(response);
            });
    });

    $('#reservationModal').on('hidden.bs.modal', function () {

        let modal = $('#reservationModal');

        let reservationModalFields = [
            ['input', 'reservation_subject'],
            ['input', 'repeat_role_input'],
            ['input', 'repeat_start_at'],
            ['input', 'repeat_role_end_count_input'],
            ['input', 'repeat_end_at'],
            ['input_textarea', 'reservation_description'],
            ['input_hidden', 'reservation_repeat_id'],
            ['checkbox', 'reservation_all_day'],
            ['checkbox', 'reservation_repeat'],
        ];

        $.each(reservationModalFields, function(index, item) {
            if(item[0] == 'input') {
                let input = modal.find('input[name="'+item[1]+'"]');
                input.val('');
            }
            if(item[0] == 'input_textarea') {
                let input_textarea = modal.find('textarea[name="'+item[1]+'"]');
                input_textarea.val('');
            }
            if(item[0] == 'input_hidden') {
                let input_hidden = modal.find('input[name="'+item[1]+'"]');
                input_hidden.val('');
            }
            if(item[0] == 'checkbox') {
                let checkbox = modal.find('input[name="'+item[1]+'"]');
                checkbox.prop('checked', false);
            }
            //webpage reload method!
            //location.reload(true);
       });
        localStorage.removeItem('prevSubmitResource');
        /*modal.find('input[name=reservation_update_id]').remove();
        modal.find('input[name=reservation_repeat_id]').remove();*/

        //webpage reload method!
        //location.reload(location.href);
        setTimeout(function(){
            console.log("reload");
            location.reload(true);
        }, 500); // 500 milliseconds = 0.5 seconds
    });

    //start date datepicker event listener
    $('#reservation_date_start_input').on('change',function (e) {
        e.preventDefault();

        let reservationDbResource;
        let reservationDbStartDate;

        if($('#resource_type').val().length !== 0) {
            var resourceType = $('#resource_type').val();
        }
        if($('#resource').val().length !== 0) {
            var resource = $('#resource').val();
        }
        if($('#reservation_date_start_input').val().length !== 0) {
            var dateStart = $('#reservation_date_start_input').val();
        }

        if(typeof $("input[name=reservation_update_id]").val() == 'undefined') {
            $.ajax({
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                },
                method: "POST",
                url: '/reservation/reservcalendar/postReservationByDate',
                data: {
                    "resource_type": resourceType,
                    "resource": resource,
                    "date_start": dateStart,
                    "event": "S"
                }
            })
                .done(function (response) {
                    if (typeof response.data.reservation_data !== 'undefined' && response.data.reservation_data.length !== 0) {
                        console.log("reservation_date_start_input");
                        console.log(response.data.reservation_data[0]);
                        $.each(response.data.reservation_data[0], function (index, item) {
                            if (index == "resource") {
                                reservationDbResource = item;
                            }
                            if (index == "reservation_date_start") {
                                reservationDbStartDate = item;
                            }

                        });

                        window.alert("Létező foglalási időpont a " + reservationDbResource + " eszköznél!");
                    }
                })
                .fail(function (response) {
                    handleAjaxResponse(response);
                });
        }
    });

    //end date datepicker event listener
    $('#reservation_date_end_input').on('change',function (e) {
        e.preventDefault();

        let reservationDbResource;
        let reservationDbStartDate;
        let reservationDbEndDate;

        if($('#resource_type').val().length !== 0) {
            var resourceType = $('#resource_type').val();
        }
        if($('#resource').val().length !== 0) {
            var resource = $('#resource').val();
        }
        if($('#reservation_date_start_input').val().length !== 0) {
            var dateStart = $('#reservation_date_start_input').val();
        }
        if($('#reservation_date_end_input').val().length !== 0) {
            var dateEnd = $('#reservation_date_end_input').val();
        }

        if(typeof $("input[name=reservation_update_id]").val() == 'undefined') {
            $.ajax({
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                },
                method: "POST",
                url: '/reservation/reservcalendar/postReservationByDate',
                data: {
                    "resource_type": resourceType,
                    "resource": resource,
                    "date_start": dateStart,
                    "date_end": dateEnd,
                    "event": "E"
                }
            })
                .done(function (response) {
                    if (typeof response.data.reservation_data !== 'undefined' && response.data.reservation_data.length !== 0) {
                        console.log("reservation_date_end_input");
                        console.log(response.data.reservation_data[0]);
                        $.each(response.data.reservation_data[0], function (index, item) {
                            if (index == "resource") {
                                reservationDbResource = item;
                            }
                            if (index == "reservation_date_start") {
                                reservationDbStartDate = item;
                            }
                            if (index == "reservation_date_end") {
                                reservationDbEndDate = item;
                            }
                        });

                        window.alert("Létező befejező foglalási időpont a " + reservationDbResource + " eszköznél!");
                    }
                })
                .fail(function (response) {
                    handleAjaxResponse(response);
                });
        }
    });

    $('button.fc-timeGridDay-button').on('click', function (e) {
        $(this).css('background-color', '#C0E3FA');
        $('button.fc-timeGridDay-button').css('background-color', '#C0E3FA');
        $('button.fc-myCustomButton-button').css('background-color', 'transparent');
        $('.fc-timeGridDay-button').html('Napi nézet');
    });

    $('button.fc-myCustomButton-button').on('click', function (e) {
        $(this).css('background-color', '#C0E3FA');
        $('button.fc-myCustomButton-button').css('background-color', '#C0E3FA');
        $('button.fc-timeGridDay-button').css('background-color', 'transparent');
    });

    //Detect browser back button event(s) - w_28858
    window.onhashchange = function() {
        //let appendUrl = '/reservation/reservcalendar';
        //window.location.replace(appendUrl);
        window.location.replace(location.href);
    }

    if (window.history && window.history.pushState) {

        //window.history.pushState('forward', null, './reservation');
        window.history.pushState('forward', null, location.href);

        $(window).on('popstate', function() {
            console.log("window.popstate");
            console.log(location.href);
            window.location.replace(location.href);
            /*let appendUrl = '/reservation';
            window.location.replace(appendUrl);*/
        });

    }

    $('.fc-timeGridDay-button').html('Napi nézet');

    //prepend modal button icon
    $('.addReservSaveButton').prepend('<i class="fas fa-check"></i>');
    $('#addReservModifyButton').prepend('<i class="fas fa-check"></i>');
    $('.addReservDeleteButton').prepend('<i class="fas fa-trash-alt"></i>');

    //flatpickr - custom datetime picker init
    $('input[type="date"]:not([readonly]), ' +
        'input[type="time"]:not([readonly]), ' +
        'input[type="datetime"]:not([readonly]), ' +
        'input[type="datetimelocal"]:not([readonly])')
        .each( setFlatpickr );
    $('.flatpickr-calendar')
        .find('input.cur-year')
        .addClass('text-left');

}); // /Document Ready

/**
 * flatpickr - custom datetime picker function
 * @param index
 * @param item
 */
function setFlatpickr(index, item){
    let datePicker;
    let type, minDate;

    if(typeof item.getAttribute === 'function') { // for each item
        type = item.getAttribute('type');
        item.setAttribute('autocomplete','off');
        minDate = item.getAttribute('data-mindate');
    } else {
        type = item.attr('type');
        item.attr('autocomplete','off');
        minDate = item.attr('data-mindate');
    }

    switch(type){
        case "date":
            datePicker = flatpickr(item,
                {
                    allowInput: true,
                    minDate: minDate,
                    monthSelectorType: 'static',
                    defaultHour: 0
                });
            datePicker
                ._input
                .addEventListener(
                    'blur',
                    (event) => {
                        event.stopPropagation();
                        datePicker.setDate(datePicker._input.value);
                    }, true);
            break;
        case "time":
            datePicker = flatpickr(item,
                {
                    allowInput: true,
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    minDate: minDate,
                    defaultHour: 0
                });
            datePicker
                ._input
                .addEventListener(
                    'blur',
                    (event) => {
                        event.stopPropagation();
                        datePicker.setDate(datePicker._input.value);
                    }, true);
            break;
        case "datetime":
        case "datetimelocal":
        default:
            datePicker = flatpickr(item,
                {
                    allowInput: true,
                    enableTime: true,
                    time_24hr: true,
                    clickOpens: true,
                    defaultHour: 0,
                    minDate: minDate
                    //monthSelectorType: 'static'
                });
            datePicker
                ._input
                .addEventListener(
                    'blur',
                    (event) => {
                        event.stopPropagation();
                        datePicker.setDate(datePicker._input.value);
                    }, true);
            break;
    }
}

/**
 * checkReservationData
 *
 * @param e
 * @param data
 */
function checkReservationData(e, data) {

    if ( typeof data !== "undefined") {
        console.log("checkReservationData");
        console.log(data);
        console.log(data["reservation_date_end"]);

        $.ajax( {
            method: "POST",
            url: '/reservation/checkcalendar',
            data: {
                "resource_type": data["resource_type"],
                "resource": data["resource"],
                "reservation_date_start": data["reservation_date_start"],
                "reservation_date_end": data["reservation_date_end"],
                "reservation_check": data["reservation_check"]
            }
        })
        .done(function (response) {
            console.log(response);
        })
        .fail(function (response) {
            handleAjaxResponse(response);
        });
    }
}
