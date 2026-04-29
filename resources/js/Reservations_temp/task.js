let dataTable;

$(document).ready(function ($) {
    'use strict';

    let width = $(window).width();

    if($(this)[0].baseURI.split("/").length == 6 && $(this)[0].baseURI.split("/")[5] == "detail") {
        dataTable = getdT('tableList-tasks');

        dataTable.on('preXhr.dt', function ( e, settings, data ) {
            data.date_filter = $('input[name="task_time_filter"]:checked').val();
        });
    }

    $('input#task_time_filter_all').prop('checked', true);

    $('input[name="task_name"]').on('input', function (e) {

        $('p#hidden_warning_text').html("");
        $('p#hidden_warning_text').css('display', 'none');

        if ($('input[name="task_name"]').val().length > 225) {
            $('p#hidden_warning_text').html("Túl hosszú beviteli érték..");
            $('p#hidden_warning_text').css('display', 'initial');
            $('p#hidden_warning_text').css('color', 'red');
            $('p#hidden_warning_text').css('margin-left', '-160px');
        } else {
            $('p#hidden_warning_text').html("");
            $('p#hidden_warning_text').css('display', 'none');
        }
    });

    $('#add_simple_task').on('click', function (e) {
        e.preventDefault();

        if (typeof $('input#task_name').val() !== 'undefined') {
            var taskNameData = $('input#task_name').val();
        }
        if (typeof $('input#task_name_custom').val() !== 'undefined') {
            var taskNameData = $('input#task_name_custom').val();
        }
        if (typeof $('input#task_name_mobile').val() !== 'undefined') {
            var taskNameData = $('input#task_name_mobile').val();
        }
        if(typeof $('input#task_responsible').val() !== 'undefined') {
            var taskResponsible = $('input#task_responsible').val();
        }
        if(typeof $('input#task_status').val() !== 'undefined') {
            var taskStatus = $('input#task_status').val();
        }
        if(typeof $('input#task_flag').val() !== 'undefined') {
            var taskFlag = $('input#task_flag').val();
        }
        if(typeof $('input#task_responsible_id').val() !== 'undefined') {
            var taskResponsibleId = $('input#task_responsible_id').val();
        }

        if( (typeof $('input#task_name').val() !== 'undefined' && $('input#task_name').val().length == 0) ||
            (typeof $('input#task_name_custom').val() !== 'undefined' && $('input#task_name_custom').val().length == 0) ||
            (typeof $('input#task_name_mobile').val() !== 'undefined' && $('input#task_name_mobile').val().length == 0)
        ) {
            alert("Hiányos beviteli érték!");
        } else {
            //console.log("INSERT_POST");
            $.ajax({
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                },
                method: "POST",
                data: {
                    "task_name": taskNameData,
                    "task_responsible": taskResponsible,
                    "task_status": taskStatus,
                    "task_flag": taskFlag,
                    "task_responsible_id" : taskResponsibleId
                },
                url: '/task/st/new',
            })
                .done(function (response) {
                    /*location.reload(true);*/
                    //let appendUrl = '/task/st/detail';
                    let appendUrl = '/task/st';
                    window.location.replace(appendUrl);
                })
                .fail(function (response) {
                    handleAjaxResponse(response);
                });
        }
    });

    if ($('button#to-report-button').length) {
        $('button#to-report-button').on('click', function (e) {
            $.ajax({
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                },
                method: "POST",
                data: {
                    "description": $('textarea#task_description').val(),
                },
                url: `/task/st/go-to-make-report`
            })
                .done(function (response) {
                    let appendUrl = response.data.url;
                    window.location.replace(appendUrl);
                })
                .fail(function (response) {
                    handleAjaxResponse(response);
                });
        });
    }

    if ($('textarea#ai_generated_from_text').length && $('textarea#task_description').length) {
        new ResizeObserver(() => {
            $('textarea#ai_generated_from_text').height($('textarea#task_description').height());
        }).observe(document.getElementById('task_description'));

        new ResizeObserver(() => {
            $('textarea#task_description').height($('textarea#ai_generated_from_text').height());
        }).observe(document.getElementById('ai_generated_from_text'));
    }

    //task/st/detail/edit/{id} update button event handler!
    $('#save_button').on('click', function (e) {
        e.preventDefault();

        if(typeof $('form.frmtasks').attr('action') !== 'undefined') {
            var updateId = $('form.frmtasks').attr('action').split("/")[7];
        }

        if (typeof $('input#task_name').val() !== 'undefined') {
            var taskNameData = $('input#task_name').val();
        }
        if (typeof $('input#task_name_custom').val() !== 'undefined') {
            var taskNameData = $('input#task_name_custom').val();
        }
        if (typeof $('input#task_name_mobile').val() !== 'undefined') {
            var taskNameData = $('input#task_name_mobile').val();
        }
        if(typeof $('input#task_responsible').val() !== 'undefined') {
            var taskResponsible = $('input#task_responsible').val();
        }
        if(typeof $('input#task_status').val() !== 'undefined') {
            var taskStatus = $('input#task_status').val();
        }
        if(typeof $('textarea#task_description').val() !== 'undefined') {
            var taskDescription = $('textarea#task_description').val();
        }
        if(typeof $('input#task_flag').val() !== 'undefined') {
            var taskFlag = $('input#task_flag').val();
        }
        if(typeof $('input[name=task_priority]:checked').val() !== 'undefined') {
            var taskPriority = $('input[name=task_priority]:checked').val();
        }
        if(typeof $('input[name=task_term]:checked').val() !== 'undefined') {
            var taskTerm = $('input[name=task_term]:checked').val();
        }
        if(typeof $('input[name=task_reminder]:checked').val() !== 'undefined') {
            var taskReminder = $('input[name=task_reminder]:checked').val();
        }

        if( (typeof $('input#task_name').val() !== 'undefined' && $('input#task_name').val().length == 0) ||
            (typeof $('input#task_name_custom').val() !== 'undefined' && $('input#task_name_custom').val().length == 0) ||
            (typeof $('input#task_name_mobile').val() !== 'undefined' && $('input#task_name_mobile').val().length == 0)
        ) {
            alert("Hiányos beviteli érték!");
        } else {
            $.ajax({
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                },
                method: "POST",
                data: {
                    "task_name": taskNameData,
                    "task_responsible": taskResponsible,
                    "task_status": taskStatus,
                    "task_description": taskDescription,
                    "task_flag": taskFlag,
                    "task_priority": taskPriority,
                    "task_term": taskTerm,
                    "task_reminder": taskReminder
                },
                url: '/task/st/edit/'+updateId
            })
                .done(function (response) {
                    /*location.reload(true);*/
                    let appendUrl = '/task/st/detail';
                    window.location.replace(appendUrl);
                })
                .fail(function (response) {
                    handleAjaxResponse(response);
                });
        }
    });

    $('#task_name').on('keyup', function (e) {
        e.preventDefault();

        if (e.key === 'Enter' || e.keyCode === 13 || e.keyCode === 160  || e.keyCode === 261) {

            console.log("form_frmtasks");
            console.log($('form.frmtasks').attr('action'));

            if( typeof $('form.frmtasks').attr('action') !== 'undefined' && $('form.frmtasks').attr('action').split("/")[5] == "edit" ||
                typeof $('form.frmtasks').attr('action') !== 'undefined' && $('form.frmtasks').attr('action').split("/")[6] == "edit") {
                //egyszeru feladat modositasa + ENTER

                let updateId = $('form.frmtasks').attr('action').split("/")[6];

                if(updateId == "edit") {
                    updateId = $('form.frmtasks').attr('action').split("/")[7];
                }

                if (typeof $('input#task_name').val() !== 'undefined') {
                    var taskNameData = $('input#task_name').val();
                }
                if (typeof $('input#task_name_custom').val() !== 'undefined') {
                    var taskNameData = $('input#task_name_custom').val();
                }
                if (typeof $('input#task_name_mobile').val() !== 'undefined') {
                    var taskNameData = $('input#task_name_mobile').val();
                }
                if (typeof $('input#task_responsible').val() !== 'undefined') {
                    var taskResponsible = $('input#task_responsible').val();
                }
                if (typeof $('input#task_status').val() !== 'undefined') {
                    var taskStatus = $('input#task_status').val();
                }
                if (typeof $('input#task_flag').val() !== 'undefined') {
                    var taskFlag = $('input#task_flag').val();
                }
                if(typeof $('input[name=task_priority]:checked').val() !== 'undefined') {
                    var taskPriority = $('input[name=task_priority]:checked').val();
                }
                if(typeof $('input[name=task_term]:checked').val() !== 'undefined') {
                    var taskTerm = $('input[name=task_term]:checked').val();
                }
                if(typeof $('input[name=task_reminder]:checked').val() !== 'undefined') {
                    var taskReminder = $('input[name=task_reminder]:checked').val();
                }

                if ((typeof $('input#task_name').val() !== 'undefined' && $('input#task_name').val().length == 0) ||
                    (typeof $('input#task_name_custom').val() !== 'undefined' && $('input#task_name_custom').val().length == 0) ||
                    (typeof $('input#task_name_mobile').val() !== 'undefined' && $('input#task_name_mobile').val().length == 0)
                ) {
                    alert("Hiányos beviteli érték!");
                } else {

                    $.ajax({
                        beforeSend: function (request) {
                            request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                        },
                        method: "POST",
                        data: {
                            "task_id": updateId,
                            "task_name": taskNameData,
                            "task_responsible": taskResponsible,
                            "task_status": taskStatus,
                            "task_flag": taskFlag,
                            "task_priority": taskPriority,
                            "task_term": taskTerm,
                            "task_reminder": taskReminder
                        },
                        url: '/task/st/edit/'+updateId,
                    })
                        .done(function (response) {
                            let appendUrl = '/task/st';
                            window.location.replace(appendUrl);
                        })
                        .fail(function (response) {
                            handleAjaxResponse(response);
                        });
                }
            } else {

                console.log("form.frmtasks_INSERT");

                if (typeof $('input#task_name').val() !== 'undefined') {
                    var taskNameData = $('input#task_name').val();
                }
                if (typeof $('input#task_name_custom').val() !== 'undefined') {
                    var taskNameData = $('input#task_name_custom').val();
                }
                if (typeof $('input#task_name_mobile').val() !== 'undefined') {
                    var taskNameData = $('input#task_name_mobile').val();
                }
                if (typeof $('input#task_responsible').val() !== 'undefined') {
                    var taskResponsible = $('input#task_responsible').val();
                }
                if (typeof $('input#task_status').val() !== 'undefined') {
                    var taskStatus = $('input#task_status').val();
                }
                if (typeof $('input#task_flag').val() !== 'undefined') {
                    var taskFlag = $('input#task_flag').val();
                }
                if(typeof $('input#task_responsible_id').val() !== 'undefined') {
                    var taskResponsibleId = $('input#task_responsible_id').val();
                }

                if ((typeof $('input#task_name').val() !== 'undefined' && $('input#task_name').val().length == 0) ||
                    (typeof $('input#task_name_custom').val() !== 'undefined' && $('input#task_name_custom').val().length == 0) ||
                    (typeof $('input#task_name_mobile').val() !== 'undefined' && $('input#task_name_mobile').val().length == 0)
                ) {
                    alert("Hiányos beviteli érték!");
                } else {

                    $.ajax({
                        beforeSend: function (request) {
                            request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                        },
                        method: "POST",
                        data: {
                            "task_name": taskNameData,
                            "task_responsible": taskResponsible,
                            "task_status": taskStatus,
                            "task_flag": taskFlag,
                            "task_responsible_id": taskResponsibleId
                        },
                        url: '/task/st/new',
                    })
                        .done(function (response) {
                            location.reload(true);
                        })
                        .fail(function (response) {
                            handleAjaxResponse(response);
                        });
                }
            }
        }
    });

    $('#task_name_mobile').on('keyup', function (e) {
        e.preventDefault();

        if (e.key === 'Enter' || e.keyCode === 13 || e.keyCode === 160  || e.keyCode === 261) {

            if( typeof $('form.frmtasks').attr('action') !== 'undefined' && $('form.frmtasks').attr('action').split("/")[5] == "edit") {

                let updateId = $('form.frmtasks').attr('action').split("/")[6];

                if (typeof $('input#task_name').val() !== 'undefined') {
                    var taskNameData = $('input#task_name').val();
                }
                if (typeof $('input#task_name_custom').val() !== 'undefined') {
                    var taskNameData = $('input#task_name_custom').val();
                }
                if (typeof $('input#task_name_mobile').val() !== 'undefined') {
                    var taskNameData = $('input#task_name_mobile').val();
                }
                if (typeof $('input#task_responsible').val() !== 'undefined') {
                    var taskResponsible = $('input#task_responsible').val();
                }
                if (typeof $('input#task_status').val() !== 'undefined') {
                    var taskStatus = $('input#task_status').val();
                }
                if (typeof $('input#task_flag').val() !== 'undefined') {
                    var taskFlag = $('input#task_flag').val();
                }
                if (typeof $('input#task_responsible_id').val() !== 'undefined') {
                    var taskResponsibleId = $('input#task_responsible_id').val();
                }

                if ((typeof $('input#task_name').val() !== 'undefined' && $('input#task_name').val().length == 0) ||
                    (typeof $('input#task_name_custom').val() !== 'undefined' && $('input#task_name_custom').val().length == 0) ||
                    (typeof $('input#task_name_mobile').val() !== 'undefined' && $('input#task_name_mobile').val().length == 0)
                ) {
                    alert("Hiányos beviteli érték!");
                } else {

                    $.ajax({
                        beforeSend: function (request) {
                            request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                        },
                        method: "POST",
                        data: {
                            "task_name": taskNameData,
                            "task_responsible": taskResponsible,
                            "task_status": taskStatus,
                            "task_flag": taskFlag,
                            "task_responsible_id": taskResponsibleId
                        },
                        url: '/task/st/edit/'+updateId,
                    })
                        .done(function (response) {
                            let appendUrl = '/task/st';
                            window.location.replace(appendUrl);
                        })
                        .fail(function (response) {
                            handleAjaxResponse(response);
                        });
                }
            } else {
                ////
                if (typeof $('input#task_name').val() !== 'undefined') {
                    var taskNameData = $('input#task_name').val();
                }
                if (typeof $('input#task_name_custom').val() !== 'undefined') {
                    var taskNameData = $('input#task_name_custom').val();
                }
                if (typeof $('input#task_name_mobile').val() !== 'undefined') {
                    var taskNameData = $('input#task_name_mobile').val();
                }
                if (typeof $('input#task_responsible').val() !== 'undefined') {
                    var taskResponsible = $('input#task_responsible').val();
                }
                if (typeof $('input#task_status').val() !== 'undefined') {
                    var taskStatus = $('input#task_status').val();
                }
                if (typeof $('input#task_flag').val() !== 'undefined') {
                    var taskFlag = $('input#task_flag').val();
                }
                if (typeof $('input#task_responsible_id').val() !== 'undefined') {
                    var taskResponsibleId = $('input#task_responsible_id').val();
                }

                if ((typeof $('input#task_name').val() !== 'undefined' && $('input#task_name').val().length == 0) ||
                    (typeof $('input#task_name_custom').val() !== 'undefined' && $('input#task_name_custom').val().length == 0) ||
                    (typeof $('input#task_name_mobile').val() !== 'undefined' && $('input#task_name_mobile').val().length == 0)
                ) {
                    alert("Hiányos beviteli érték!");
                } else {

                    $.ajax({
                        beforeSend: function (request) {
                            request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                        },
                        method: "POST",
                        data: {
                            "task_name": taskNameData,
                            "task_responsible": taskResponsible,
                            "task_status": taskStatus,
                            "task_flag": taskFlag,
                            "task_responsible_id": taskResponsibleId
                        },
                        url: '/task/st/new',
                    })
                        .done(function (response) {
                            location.reload(true);
                        })
                        .fail(function (response) {
                            handleAjaxResponse(response);
                        });
                }
            }
            ////
        }
    });

    $("#task_project").autocomplete({
        source: '/task/ct/autocomplete?task_project='+$('input#task_project').val(),
        change: function( event, ui ) {
        }
    });

    $('#task_project_temp').on('input', function (e) {
        e.preventDefault();

        if(typeof $('input#task_project').val() !== 'undefined' && $('input#task_project').val().length > 3) {
            var taskProjectData = $('input#task_project').val();
        }

        $.ajax({
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "POST",
            data: {
                "task_project": taskProjectData
            },
            url: '/task/ct/autocomplete',
        })
            .done(function (response) {
                console.log("response");
                console.log(response);
            })
            .fail(function (response) {
                handleAjaxResponse(response);
        });
    });

    if ($('.task_name_alter').length == 0) {
        $('#add_simple_task').hide();
    }

    $('button#delete_button').on('click', function (e) {
        let deleteId;

        /*let modal = $('#deleteWarningModal');
        modal.modal('show');*/

        e.preventDefault();

        if ( typeof $('form.frmtasks').attr('action') != "undefined" ) {
            deleteId = $('form.frmtasks').attr('action').split("/")[6];
        }

        $.ajax({
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "GET",
            url: '/task/st/delete/'+deleteId,
        })
        .done(function (response) {
            let appendUrl = '/task/st';
            window.location.replace(appendUrl);
        })
        .fail(function (response) {
            handleAjaxResponse(response);
        });

    });

    $('#task_name_edit').on('keyup', function (e) {
        e.preventDefault();

        if (e.key === 'Enter' || e.keyCode === 13 || e.keyCode === 160  || e.keyCode === 261) {

            if (typeof $('input#task_name_edit').val() !== 'undefined') {
                var taskNameData = $('input#task_name_edit').val();
            }
            if (typeof $('input#task_name_custom').val() !== 'undefined') {
                var taskNameData = $('input#task_name_custom').val();
            }
            if (typeof $('input#task_name_mobile').val() !== 'undefined') {
                var taskNameData = $('input#task_name_mobile').val();
            }
            if (typeof $('input#task_responsible').val() !== 'undefined') {
                var taskResponsible = $('input#task_responsible').val();
            }
            if (typeof $('input#task_status').val() !== 'undefined') {
                var taskStatus = $('input#task_status').val();
            }
            if (typeof $('input#task_flag').val() !== 'undefined') {
                var taskFlag = $('input#task_flag').val();
            }

            if ((typeof $('input#task_name_edit').val() !== 'undefined' && $('input#task_name_edit').val().length == 0) ||
                (typeof $('input#task_name_custom').val() !== 'undefined' && $('input#task_name_custom').val().length == 0) ||
                (typeof $('input#task_name_mobile').val() !== 'undefined' && $('input#task_name_mobile').val().length == 0)
            ) {
                alert("Hiányos beviteli érték!");
            } else {

                $.ajax({
                    beforeSend: function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                    },
                    method: "POST",
                    data: {
                        "task_name": taskNameData,
                        "task_responsible": taskResponsible,
                        "task_status": taskStatus,
                        "task_flag": taskFlag
                    },
                    url: '/task/st/new',
                })
                    .done(function (response) {
                        location.reload(true);
                    })
                    .fail(function (response) {
                        handleAjaxResponse(response);
                    });
            }
        }
    });

    $('label[for=tasks_list_select_all]').hide();

    // if ($('#complex-edit-button').length > 0) {
    //     let complexButton = $('#complex-edit-button');
    //     complexButton.css("margin-right", "10px");
    //     complexButton.css("padding", "9px 16px");
    //     complexButton[0].classList = "btn btn-sm btn-primary float-right";
    //     $('.card-footer').append(complexButton);
    // }

    let taskPriorityMain = $('input[name="task_priority"]');
    let taskTermMain = $('input[name="task_term"]');
    let taskReminderMain = $('input[name="task_reminder"]');

    if(typeof taskPriorityMain !== 'undefined' && typeof presenterVars.task_simples !== 'undefined') {
        taskPriorityMain.each( function(i,v) {
            if(v.value == presenterVars.task_simples.priority) {
                $('#task-priority-'+v.value+'').attr('checked', 'checked');
            }
        });
    }

    if(typeof taskTermMain !== 'undefined' && typeof presenterVars.task_simples !== 'undefined') {
        let termIdx = 0;
        taskTermMain.each( function(ti,tv) {
            if(String(tv.value) === String(presenterVars.task_simples.term)) {
                $('#task-term-'+termIdx+'').attr('checked', 'checked');
                termIdx++;
            }
            termIdx++;
        });
    }

    if(typeof taskReminderMain !== 'undefined' && typeof presenterVars.task_simples !== 'undefined') {
        let remIdx = 0;
        taskReminderMain.each( function(ri,rv) {
            if(String(rv.value) === String(presenterVars.task_simples.reminder)) {
                $('#task-reminder-'+remIdx+'').attr('checked', 'checked');
                remIdx++;
            }
            remIdx++;
        });
    }

    // clear/uncheck radio buttons
    $('.radio-task-priority-temp').on('click', function (e) {
        let radio = $(this);
        let parents = radio.parent().parent();

        if (radio.data('waschecked') == true) {
            radio.prop('checked', false);
            radio.data('waschecked', false);
            //
            $('.task-term-div').removeClass('active');
            $('.task-reminder-div').removeClass('active');
            //
            parents.removeClass('active');
        } else {
            radio.prop('checked', true);
            radio.data('waschecked', true);
            //initial status for div block
            $('.task-term-div').removeClass('active');
            $('.task-reminder-div').removeClass('active');
            parents.addClass('active');
        }
        radio.siblings('.radio-task-priority').data('waschecked', false);
    });

    $('.task-term-div').on('click', function (e) {
        let div = $(this);

        console.log("prev_active_div_console")
        console.log(localStorage.getItem('prev_active_div'));
        console.log("prev_active_div_console")

        if(typeof localStorage.getItem('prev_active_div') !== null) {
            console.log("prev_active_div");
            console.log(typeof localStorage.getItem('prev_active_div'));
            console.log(localStorage.getItem('prev_active_div'));
            console.log("prev_active_div");
            let prevDiv = localStorage.getItem('prev_active_div');
            prevDiv.removeClass('active');
            localStorage.removeItem('prev_active_div');
        } else {
            localStorage.setItem('prev_active_div', div);
            div.addClass('active');
        }
        /*if(typeof localStorage.getItem('prev_active_div') !== null) {
            localStorage.removeItem('prev_active_div');
        }*/
        let childrensDiv = div.children().children();
        console.log("childrensDiv");
        console.log(childrensDiv);
    });

    // conditional options
    $('#task-term-1').on('click', function () {
        $('.task-term-span').html(presenterVars.task_dates.today);
        localStorage.setItem('actual_term_date', presenterVars.task_dates.today);
        let returnVars = setTaskDetailVars();
        returnVars[1].css('visibility', 'hidden');
        returnVars[2].css('visibility', 'hidden');
        returnVars[3].css('visibility', 'hidden');
        returnVars[4].css('visibility', 'hidden');
    });

    $('#task-term-2').on('click', function () {
        let returnVars = setTaskDetailVars();
        $('.task-term-span').html(presenterVars.task_dates.tomorrow);
        localStorage.setItem('actual_term_date', presenterVars.task_dates.tomorrow);
        returnVars[1].css('visibility', 'initial');
        returnVars[2].css('visibility', 'hidden');
        returnVars[3].css('visibility', 'hidden');
        returnVars[4].css('visibility', 'hidden');
    });

    $('#task-term-3').on('click', function () {
        let returnVars = setTaskDetailVars();
        $('.task-term-span').html(presenterVars.task_dates.three_day);
        localStorage.setItem('actual_term_date', presenterVars.task_dates.three_day);
        returnVars[1].css('visibility', 'initial');
        returnVars[2].css('visibility', 'initial');
        returnVars[3].css('visibility', 'initial');
        returnVars[4].css('visibility', 'hidden');
    });

    $('#task-term-4').on('click', function () {
        let returnVars = setTaskDetailVars();
        $('.task-term-span').html(presenterVars.task_dates.one_week);
        localStorage.setItem('actual_term_date', presenterVars.task_dates.one_week);
        returnVars[1].css('visibility', 'initial');
        returnVars[2].css('visibility', 'initial');
        returnVars[3].css('visibility', 'initial');
        returnVars[4].css('visibility', 'initial');
    });

    $('#task-term-5').on('click', function () {
        let returnVars = setTaskDetailVars();
        $('.task-term-span').html(presenterVars.task_dates.two_week);
        localStorage.setItem('actual_term_date', presenterVars.task_dates.two_week);
        returnVars[1].css('visibility', 'initial');
        returnVars[2].css('visibility', 'initial');
        returnVars[3].css('visibility', 'initial');
        returnVars[4].css('visibility', 'initial');
    });

    $('button.btn-reminder-default-button').on('click', function () {
        let returnVars = setTaskDetailVars();
        returnVars[0].css('visibility', 'initial');
        returnVars[1].css('visibility', 'initial');
        returnVars[2].css('visibility', 'initial');
        returnVars[3].css('visibility', 'initial');
        returnVars[4].css('visibility', 'initial');
    });

    $('#task-reminder-1').on('click', function () {
        $('.task-reminder-span').html(presenterVars.task_dates.reminder_today);
    });
    $('#task-reminder-2').on('click', function () {
        $('.task-reminder-span').html(presenterVars.task_dates.reminder_tomorrow);
    });
    $('#task-reminder-3').on('click', function () {
        if(localStorage.key('actual_term_date')) {
            let reminderDate  = moment(localStorage.getItem('actual_term_date'), "YYYY-MM-DD").subtract(1, 'days');
            $('#task-reminder-3').val(reminderDate.format("YYYY-MM-DD")+" 09:00:00");
            $('.task-reminder-span').html(reminderDate.format("YYYY-MM-DD")+" 09:00:00");
        } else {
            $('.task-reminder-span').html("");
        }
    });
    $('#task-reminder-4').on('click', function () {
        if(localStorage.key('actual_term_date')) {
            let reminderDate  = moment(localStorage.getItem('actual_term_date'), "YYYY-MM-DD").subtract(2, 'days');
            $('#task-reminder-4').val(reminderDate.format("YYYY-MM-DD")+" 09:00:00");
            $('.task-reminder-span').html(reminderDate.format("YYYY-MM-DD")+" 09:00:00");
        } else {
            $('.task-reminder-span').html("");
        }
    });
    $('#task-reminder-5').on('click', function () {
        if(localStorage.key('actual_term_date')) {
            let reminderDate  = moment(localStorage.getItem('actual_term_date'), "YYYY-MM-DD").subtract(3, 'days');
            $('#task-reminder-5').val(reminderDate.format("YYYY-MM-DD")+" 09:00:00");
            $('.task-reminder-span').html(reminderDate.format("YYYY-MM-DD")+" 09:00:00");
        } else {
            $('.task-reminder-span').html("");
        }
    });

    if(width <= 991) {
        // let complexEditButton = $('#complex-edit-button');
        //
        // complexEditButton.css('margin-right', '80px');
        // complexEditButton.css('margin-top', '10px');
    }

    $('div.custom-checkbox').find('input[type="checkbox"]').not('#tasks_list_select_all').on('click', function (e) {

        if ($(this).is(':checked')) {
            var id = $(this).val();
        }

        if (typeof id !== 'undefined' || typeof id !== null) {

            $.ajax({
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                },
                method: "POST",
                data: {
                    "tasks_row_selectors": [ id ]
                },
                url: '/task/st/bulk-disable-items',
            })
                .done(function (response) {
                    location.reload(true);
                })
                .fail(function (response) {
                    handleAjaxResponse(response);
                });

        }

    });

    $('label[for="task_reminder_responsible"]').css('font-weight', 'bold');
    $('label[for="task_reminder_owner"]').css('font-weight', 'bold');
    $('label[for="task_reminder_customer"]').css('font-weight', 'bold');

    //$('ul.navbar-nav').hide();

    /*if ( $('a.btn-sm').attr('href').match("/edit/") !== null ) {
        console.log("btn-sm-sm");
    }*/

    if ($('input#business_potential').length > 0) {
        let inputVal = $('input#business_potential').val();

        // Remove all non-digit characters
        inputVal = inputVal.replace(/\D/g, '');

        // Prevent leading zeros
        inputVal = inputVal.replace(/^0+/, '');

        // Format the number with spaces as thousand separators
        let formattedVal = inputVal.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

        // Set the formatted value back to the input
        $('input#business_potential').val(formattedVal);
    }

    $('input#business_potential').on('input paste', function (e) {
        // Get the input value
        let inputVal = $(this).val();

        // Remove all non-digit characters
        inputVal = inputVal.replace(/\D/g, '');

        // Prevent leading zeros
        inputVal = inputVal.replace(/^0+/, '');

        // Format the number with spaces as thousand separators
        let formattedVal = inputVal.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

        // Set the formatted value back to the input
        $(this).val(formattedVal);
    });

}); // /Document Ready

function customCheckbox() {

    $('a.custom-edit').attr('title','Szerkesztés');
    $('a.custom-complex').attr('title','Komplex feladat');
    $('a.custom-delete').attr('title','Törlés');

    $('a.custom-edit').on('click', function (e) {
        $('input#task_name').attr('name', 'task_name_edit');
        let href = $(this).attr('href');
        let hrefAr = href.split("/");
        let updateId = hrefAr[hrefAr.length-1];

        $('<input>').attr({type: 'hidden', name: 'update_id', value: updateId}).appendTo('form');
    });

    $('.fa-calendar-alt').on('click', function (e) {
        let taskId = this.id;
        let width = $(window).width();

        if ( typeof taskId !== 'undefined' ) {
            var flatPickrInput = $('#'+taskId+'');
        }
        console.log("flatPickrInput");
        console.log(flatPickrInput);

        console.log("window.width");
        console.log(width);

        if(width <= 991) {
            //lesser than viewport with -> mobile
            flatPickrInput.flatpickr({
                dateFormat: "Y-m-d",
                enableTime: false,
                altInputClass: 'task_term_'+taskId+''
            });

            $('.task_term_'+taskId+'').show();

            var dateChange = $('.task_term_'+taskId+'');
            //var dateChange = $('.flatpickr-mobile');
        } else {
            flatPickrInput.flatpickr({
                dateFormat: "Y-m-d",
                enableTime: false
            });

            $('.task_term_'+taskId+'').show();

            var dateChange = $('.task_term_'+taskId+'');
        }

        /*$('.task_term_'+taskId+'').on('change',function (e) {*/
        dateChange.on('change',function (e) {
            var taskTermDate =  $('.task_term_'+taskId+'').val();

            if (typeof taskId !== 'undefined' || typeof taskId !== null ||
                typeof taskTermDate !== 'undefined' || typeof taskTermDate !== null
            ) {
                $.ajax({
                    beforeSend: function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                    },
                    method: "POST",
                    data: {
                        "id": taskId,
                        "term_date": taskTermDate
                    },
                    url: '/task/st/post-term-date',
                })
                    .done(function (response) {
                        window.alert('Sikeres módosítás!');
                    })
                    .fail(function (response) {
                        handleAjaxResponse(response);
                    });

            }
        });

    });

    //modify btn-detail-active button view
    $('input[name="task_time_filter"]').on('change', function (e) {

        let dateFilter = $(this).val();

        dataTable.ajax.url = window.location.href;
        dataTable.ajax.data = {
            'date_filter': dateFilter
        };

        dataTable.draw();

        //request to task/st/detail - @indexDetail method
        /*$.ajax({
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "GET",
            data: {
                "date_filter": dateFilter
            },
            url: '/task/st/detail',
        })
            .done(function (response) {
                dataTable = getdT('tableList-tasks');

                dataTable.clear();
                dataTable.rows().remove().draw();
                dataTable.rows.add(response.data).draw();
            })
            .fail(function (response) {
                handleAjaxResponse(response);
            });*/
    });

    $('div.custom-checkbox').find('input[type="checkbox"]').not('#tasks_list_select_all').on('click', function (e) {

        if ($(this).is(':checked')) {
            var id = $(this).val();
        }

        if (typeof id !== 'undefined' || typeof id !== null) {

            $.ajax({
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                },
                method: "POST",
                data: {
                    "tasks_row_selectors": [ id ]
                },
                url: '/task/st/bulk-disable-items',
            })
                .done(function (response) {
                    location.reload(true);
                })
                .fail(function (response) {
                    handleAjaxResponse(response);
                });

        }

    });
}

function customCheckboxDisabledEvent() {

    $('div.custom-checkbox').find('input[type="checkbox"]').not('#tasks_list_select_all').on('click', function (e) {

        if ($(this).is(':checked')) {
            var id = $(this).val();
        }

        if (typeof id !== 'undefined' || typeof id !== null) {

            $.ajax({
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
                },
                method: "POST",
                data: {
                    "tasks_row_selectors": [ id ]
                },
                url: '/task/st/bulk-disable-items',
            })
                .done(function (response) {
                    location.reload(true);
                })
                .fail(function (response) {
                    handleAjaxResponse(response);
                });

        }

    });

}

function colvisSet() {
    $('a.nav-link').on('click', function (e) {
        if( $(this)[0].href.match(/\/task\/st/) ) {
            $('li.colvis').hide();
        } else if ( $(this)[0].href.match(/\/task\/ct/) ) {
            $('li.colvis').show();
        }
        if ( $(this)[0].href.match(/\/task\/ct/) ) {
            $('li.colvis').show();
        }
    })
}

function saveAndGoToNewModal() {
    if(typeof $('form.frmtasks').attr('action') !== 'undefined') {
        var updateId = $('form.frmtasks').attr('action').split("/")[7];
    }

    if (typeof $('input#task_name').val() !== 'undefined') {
        var taskNameData = $('input#task_name').val();
    }
    if (typeof $('input#task_name_custom').val() !== 'undefined') {
        var taskNameData = $('input#task_name_custom').val();
    }
    if (typeof $('input#task_name_mobile').val() !== 'undefined') {
        var taskNameData = $('input#task_name_mobile').val();
    }
    if(typeof $('input#task_responsible').val() !== 'undefined') {
        var taskResponsible = $('input#task_responsible').val();
    }
    if(typeof $('input#task_status').val() !== 'undefined') {
        var taskStatus = $('input#task_status').val();
    }
    if(typeof $('textarea#task_description').val() !== 'undefined') {
        var taskDescription = $('textarea#task_description').val();
    }
    if(typeof $('input#task_flag').val() !== 'undefined') {
        var taskFlag = $('input#task_flag').val();
    }
    if(typeof $('input[name=task_priority]:checked').val() !== 'undefined') {
        var taskPriority = $('input[name=task_priority]:checked').val();
    }
    if(typeof $('input[name=task_term]:checked').val() !== 'undefined') {
        var taskTerm = $('input[name=task_term]:checked').val();
    }
    if(typeof $('input[name=task_reminder]:checked').val() !== 'undefined') {
        var taskReminder = $('input[name=task_reminder]:checked').val();
    }

    if( (typeof $('input#task_name').val() !== 'undefined' && $('input#task_name').val().length == 0) ||
        (typeof $('input#task_name_custom').val() !== 'undefined' && $('input#task_name_custom').val().length == 0) ||
        (typeof $('input#task_name_mobile').val() !== 'undefined' && $('input#task_name_mobile').val().length == 0)
    ) {
        alert("Hiányos beviteli érték!");
    } else {
        $.ajax({
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
            },
            method: "POST",
            data: {
                "task_name": taskNameData,
                "task_responsible": taskResponsible,
                "task_status": taskStatus,
                "task_description": taskDescription,
                "task_flag": taskFlag,
                "task_priority": taskPriority,
                "task_term": taskTerm,
                "task_reminder": taskReminder
            },
            url: '/task/st/edit/'+updateId
        })
            .done(function (response) {
                /*location.reload(true);*/
                let appendUrl = `/task/ct/newModal?${updateId}`;
                window.location.replace(appendUrl);
            })
            .fail(function (response) {
                handleAjaxResponse(response);
            });
    }
}

function fileView(file_url, file_display_name, file_name, task_id) {
    $.ajax({
        beforeSend: function (request) {
            request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
        },
        method: "POST",
        url: "/task/preview",
        data: {
            url: file_url,
            display_name: file_display_name,
            name: file_name,
            task_id: task_id
        }
    })
        .done(function (response) {
            bootbox.dialog({
                title: 'Fájl megtekintés',
                message: response.data.preview,
                size: 'extra-large',
                onEscape: true,
                backdrop: true,
                centerVertical: true,
                onShown: function (e) {
                    switch (response.data.type) {
                        case 'image': {
                            imagePreview();
                            break;
                        }
                        case 'document': {
                            officeToHtml(response.data.file_url);
                            break;
                        }
                        case 'text': {
                            $('div#file_content').text(response.data.text);
                            break;
                        }
                        default : break;
                    }
                }
            });
        })

        .fail(function (response) {
            handleAjaxResponse(response);
        });
}

function fileDelete(file_name, task_id) {
    $.ajax({
        beforeSend: function (request) {
            request.setRequestHeader("X-CSRF-TOKEN", csrf_token());
        },
        method: "POST",
        url: "/task/file/delete",
        data: {
            task_id: task_id,
            name: file_name
        }
    })
        .done(function (response) {
            refreshFilesTable('files-table', response.filesWithData);
        })

        .fail(function (response) {
            handleAjaxResponse(response);
        });
}

function officeToHtml (file_url) {
    $("#resolte-contaniner").officeToHtml({
        url: file_url,
        docxSetting: {
            includeEmbeddedStyleMap: true,
            includeDefaultStyleMap: true,
            convertImage: mammoth.images.imgElement(function(image) {
                return image.read("base64").then(function(imageBuffer) {
                    return {
                        src: "data:" + image.contentType + ";base64," + imageBuffer
                    };
                });
            }),
            ignoreEmptyParagraphs: false,
        },
        pptxSetting: {
            slidesScale: "50%", //Change Slides scale by percent
            slideMode: true, /** true,false*/
            slideType: "revealjs", /*'divs2slidesjs' (default) , 'revealjs'(https://revealjs.com) */
            revealjsPath: "{{ asset('vendor/laravel-file-viewer/revealjs/') }}", /*path to js file of revealjs. default:  './revealjs/reveal.js'*/
            keyBoardShortCut: true,  /** true,false ,condition: slideMode: true*/
            mediaProcess: true, /** true,false: if true then process video and audio files */
            jsZipV2: false,
            slideModeConfig: {
                first: 1,
                nav: true, /** true,false : show or not nav buttons*/
                navTxtColor: "black", /** color */
                keyBoardShortCut: false, /** true,false ,condition: */
                showSlideNum: true, /** true,false */
                showTotalSlideNum: true, /** true,false */
                autoSlide:1, /** false or seconds , F8 to active ,keyBoardShortCut: true */
                randomAutoSlide: false, /** true,false ,autoSlide:true */
                loop: true,  /** true,false */
                background: false, /** false or color*/
                transition: "default", /** transition type: "slid","fade","default","random" , to show transition efects :transitionTime > 0.5 */
                transitionTime: 1 /** transition time between slides in seconds */
            },
            revealjsConfig: {} /*revealjs options. see https://revealjs.com */
        },
        sheetSetting: {
            jqueryui: false,
            activeHeaderClassName: "",
            allowEmpty: true,
            autoColumnSize: true,
            autoRowSize: false,
            columns: false,
            columnSorting: true,
            contextMenu: false,
            copyable: true,
            customBorders: false,
            fixedColumnsLeft: 0,
            fixedRowsTop: 0,
            language: "en-EN",
            search: false,
            selectionMode: "single",
            sortIndicator: false,
            readOnly: false,
            startRows: 1,
            startCols: 1,
            rowHeaders: true,
            colHeaders: true,
            width: false,
            height: false
        },
        imageSetting: {
            frame: ["100%", "100%", false],
            maxZoom: "900%",
            zoomFactor: "10%",
            mouse: true,
            keyboard: true,
            toolbar: true,
            rotateToolbar: true
        }
    });
}

function imagePreview() {
    const viewer = new Viewer(document.getElementById('preview-image'), {
        inline: true,
        backdrop: false,
        navbar:false,
        minWidth: 400,
        minHeight: 200
    });
}

function setTaskDetailVars() {

    let taskReminder01 = $('#task-reminder-div-01');
    let taskReminder02 = $('#task-reminder-div-02');
    let taskReminder03 = $('#task-reminder-div-03');
    let taskReminder04 = $('#task-reminder-div-04');
    let taskReminder05 = $('#task-reminder-div-05');

    let returnAr = [];

    returnAr.push(
        taskReminder01,
        taskReminder02,
        taskReminder03,
        taskReminder04,
        taskReminder05
    );

    if(typeof returnAr !== null) {
        return returnAr;
    }
}