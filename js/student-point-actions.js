function loadTemplateText(template_id) {
    $.ajax({
        type: "POST",
        url: "get-message-templates",
        data: "type=1&template=" + template_id,
        dataType: 'json',
    }).done(function (result) {
        $.each(result, function (order, object) {
            value = object.text;
            CKEDITOR.instances.message.setData(value);
        });
    }).fail(function (jqXHR, textStatus) {
        CKEDITOR.instances.message.setData('');
    });
}
function CKUpdate() {
    for (instance in CKEDITOR.instances)
        CKEDITOR.instances[instance].updateElement();
}
$(document).ready(function () {

    var positiveSound = document.createElement('audio');
    var negativeSound = document.createElement('audio');
    var randomSound = document.createElement('audio');
    positiveSound.setAttribute('src', 'https://www.aybarsakgun.com/sbm/sound/positive.mp3');
    negativeSound.setAttribute('src', 'https://www.aybarsakgun.com/sbm/sound/negative.mp3');
    randomSound.setAttribute('src', 'https://www.aybarsakgun.com/sbm/sound/random.mp3');
    $('body').on('click', '.ogrenci-puanla', function (e) {
        e.preventDefault();
        var regexp = /[^0-9]/g;
        var ogrenci = this.id;
        var sinif = $(this).attr("class_id");
        $.ajax(
            {
                url: "student-feedback?id=" + ogrenci.replace(regexp, '') + "&class_id=" + sinif.replace(regexp, ''),
                type: "GET",
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    if (data == 0) {
                        $(".modal-student-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                    } else {
                        $(".modal-student-content").html(data);
                        $('.report-behavior-list').DataTable({responsive: true});
                        $('.report-redeem-list').DataTable({responsive: true});
                    }
                }
            });
    });
    $('body').on('click', '.toggle-edit-behaviors', function (e) {
        $('.toggle-edit-behaviors').attr("checked", !$('.toggle-edit-behaviors').attr("checked"));
        $('.toggle-edit-behaviors').attr("disabled", true);
        if(!$('.toggle-edit-behaviors').attr("checked")) {
            $('.give-behavior.btn-success > .behavior-icon').html('thumb_up').addClass('animated bounceInRight');
            $('.give-behavior.btn-danger > .behavior-icon').html('thumb_down').addClass('animated bounceInRight');
            $('.give-behavior').removeClass('editMode');
        } else {
            $('.behavior-icon').html('edit').addClass('animated bounceInLeft');
            $('.give-behavior').addClass('editMode');
        }
        setTimeout(function(){
            $('.behavior-icon').removeClass('animated bounceInLeft bounceInRight');
            $('.toggle-edit-behaviors').attr("disabled", false);
        },2000);
    });
    $('#modal-add-behavior').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var ogrenci = button.data("student");
        var sinif = button.data("class");
        var modal = $(this);
        modal.find('#hidden_student_id').val(ogrenci);
        modal.find('#hidden_class_id').val(sinif);
    });
    $('body').on('shown.bs.tab', 'a[data-toggle="tab"][href="#home_with_icon_title"]', function (e) {
        $('.addNewBehaviorButton').show();
        $('.editBehaviorsButton').show();
    });
    $('body').on('shown.bs.tab', 'a[data-toggle="tab"][href="#profile_with_icon_title"]', function (e) {
        $('.addNewBehaviorButton').show();
        $('.editBehaviorsButton').show();
    });
    $('body').on('shown.bs.tab', 'a[data-toggle="tab"][href="#history"]', function (e) {
        $('.report-behavior-list').DataTable().columns.adjust().responsive.recalc();
        $('.addNewBehaviorButton').hide();
        $('.editBehaviorsButton').hide();
    });
    $('body').on('shown.bs.tab', 'a[data-toggle="tab"][href="#redeem"]', function (e) {
        $('.report-redeem-list').DataTable().columns.adjust().responsive.recalc();
        $('.addNewBehaviorButton').hide();
        $('.editBehaviorsButton').hide();
    });
    $('body').on('click', '.goStudentGeneralModal', function (e) {
        e.preventDefault();
        $('#modal-student').modal('toggle');
        var studentId = $(this).data("student");
        var classId = $(this).data("class");
        var currentModal = $(this).data("current-modal");
        var regexp = /[^0-9]/g;
        if (currentModal === 'editStudentModal') {
            $('#modal-edit-student').modal('toggle');
        } else if (currentModal === 'sendMailToParentModal') {
            $('#modal-send-mail-to-parent').modal('toggle');
        } else if (currentModal === 'sendMessageModal') {
            $('#modal-send-message').modal('toggle');
        } else if (currentModal === 'editBehaviorModal') {
            $('#modal-edit-behavior').modal('toggle');
        }
        $.ajax(
            {
                url: "student-feedback?id=" + studentId + "&class_id=" + classId,
                type: "GET",
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    if (data == 0) {
                        $(".modal-student-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                    } else {
                        $(".modal-student-content").html(data);
                        $('.report-behavior-list').DataTable({responsive: true});
                        $('.report-redeem-list').DataTable({responsive: true});
                    }
                }
            });
    });
    $('body').on('click', '.send-message', function (e) {
        e.preventDefault();
        $('#modal-student').modal('toggle');
        $('#modal-send-message').modal('toggle');
        var ogrencix = $(this).data("student");
        var sinifx = $(this).data("class");
        $.ajax(
            {
                url: "start-conversation-modal?id=" + ogrencix + "&class_id=" + sinifx,
                type: "GET",
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    if (data == 0) {
                        $(".modal-send-message-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                    } else {
                        $(".modal-send-message-content").html(data);
                    }
                }
            });
    });
    $('body').on('change', 'select#message_template', function (e) {
        loadTemplateText($(this).find(':selected').val());
    });
    $('body').on('click', '.send-mail-to-parent', function (e) {
        e.preventDefault();
        $('#modal-student').modal('toggle');
        $('#modal-send-mail-to-parent').modal('toggle');
        var ogrencix = $(this).data("student");
        var sinifx = $(this).data("class");
        $.ajax(
            {
                url: "send-mail-to-parent-modal?id=" + ogrencix + "&class_id=" + sinifx,
                type: "GET",
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    if (data == 0) {
                        $(".modal-send-mail-to-parent-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                    } else {
                        $(".modal-send-mail-to-parent-content").html(data);
                        CKEDITOR.replace('message');
                    }
                }
            });
    });
    $('body').on('submit', '#Send-Mail-To-Parent-Form', function (e) {
        e.preventDefault();
        CKUpdate();
        $('.Send-Mail-To-Parent-Button').prop('disabled', true);
        $('.Send-Mail-To-Parent-Button').html("Sending...");

        $("#Send-Mail-To-Parent-Result").empty();
        $.ajax(
            {
                url: "send-mail-to-parent-a",
                type: "POST",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    setTimeout(function () {
                        $('.Send-Mail-To-Parent-Button').prop('disabled', false);
                        $('.Send-Mail-To-Parent-Button').html("Send");
                        if (data == 0) {
                            $("#Send-Mail-To-Parent-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                        }
                        if (data == 1) {
                            $("#Send-Mail-To-Parent-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Öğrencinin velisine başarıyla e-posta gönderildi.</div>");
                            $("#Send-Mail-To-Parent-Form").trigger("reset");
                            CKEDITOR.instances.message.setData('');
                        }
                        if (data == 2) {
                            $("#Send-Mail-To-Parent-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen e-posta içeriğini doldurunuz.</div>");
                        }
                    }, 1000);
                }
            });
    });
    $('body').on('click', '.edit-student', function (e) {
        e.preventDefault();
        $('#modal-student').modal('toggle');
        $('#modal-edit-student').modal('toggle');
        var ogrencix = $(this).data("student");
        var sinifx = $(this).data("class");
        $.ajax(
            {
                url: "edit-student-modal?id=" + ogrencix + "&class_id=" + sinifx,
                type: "GET",
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    if (data == 0) {
                        $(".modal-edit-student-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                    } else {
                        $(".modal-edit-student-content").html(data);
                    }
                }
            });
    });
    $('#modal-edit-student').on('shown.bs.modal', function (e) {
        $('body').addClass('modal-open');
        $('body').css("padding-right", "");
        $('#modal-edit-student').css("padding-left", "");
    });
    $('#modal-edit-student').on('hidden.bs.modal', function (e) {
        $('body').css("padding-right", "");
    });
    $('#modal-edit-behavior').on('shown.bs.modal', function (e) {
        $('body').addClass('modal-open');
        $('body').css("padding-right", "");
        $('#modal-edit-behavior').css("padding-left", "");
    });
    $('#modal-edit-behavior').on('hidden.bs.modal', function (e) {
        $('body').css("padding-right", "");
    });
    $('#modal-send-message').on('shown.bs.modal', function (e) {
        $('body').addClass('modal-open');
        $('body').css("padding-right", "");
        $('#modal-send-message').css("padding-left", "");
    });
    $('#modal-send-message').on('hidden.bs.modal', function (e) {
        $('body').css("padding-right", "");
    });
    $('#modal-send-mail-to-parent').on('shown.bs.modal', function (e) {
        $('body').addClass('modal-open');
        $('body').css("padding-right", "");
        $('#modal-send-mail-to-parent').css("padding-left", "");
    });
    $('#modal-send-mail-to-parent').on('hidden.bs.modal', function (e) {
        $('body').css("padding-right", "");
    });
    $('#modal-student').on('shown.bs.modal', function (e) {
        $('body').addClass('modal-open');
        $('body').css("padding-right", "");
        $('#modal-student').css("padding-left", "");
    });
    $('#modal-student').on('hidden.bs.modal', function (e) {
        $('body').css("padding-right", "");
    });
    $('#modal-add-behavior').on('shown.bs.modal', function (e) {
        $('body').addClass('modal-open');
        $('body').css("padding-right", "");
        $('#modal-add-behavior').css("padding-left", "");
    });
    $('#modal-add-behavior').on('hidden.bs.modal', function (e) {
        $('body').css("padding-right", "");
        $('body').addClass('modal-open');
    });
    $('body').on('click', '.Delete-Student-Button', function (e) {
        e.preventDefault();
        var ogrencix = $(this).data("student");
        var sinifx = $(this).data("class");
        swal(
            {
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: false,
                closeOnCancel: false,
                showLoaderOnConfirm: true,
            },
            function (isConfirm) {
                if (isConfirm) {
                    setTimeout(function () {
                        $.ajax({
                            type: 'POST',
                            url: 'delete-student',
                            data: 'student=' + ogrencix + '&class=' + sinifx,
                            success: function (data) {
                                if (data == 1) {
                                    $('#modal-edit-student').modal('toggle');
                                    swal(
                                        {
                                            title: "Deleted!",
                                            text: "Student has been deleted.",
                                            type: "success",
                                            confirmButtonText: "OK",
                                            closeOnConfirm: true
                                        });
                                    // TODO: autocompleteden öğrenciyi kaldır
                                } else {
                                    swal(
                                        {
                                            title: "Error!",
                                            text: "Somethings went wrong. Please try again.",
                                            type: "error",
                                            confirmButtonText: "OK",
                                            closeOnConfirm: true
                                        });
                                }
                            }
                        });
                        if(document.URL.includes("report")) {
                            window.location.href = 'home';
                        }
                    }, 1000);
                } else {
                    swal(
                        {
                            title: "Canceled!",
                            text: "Your request has been canceled.",
                            type: "error",
                            confirmButtonText: "OK",
                            closeOnConfirm: true
                        });
                }
            });
    });
    $('body').on('click', '.Revoke-Point-Button', function (e) {
        e.preventDefault();
        var ogrencix = $(this).data("student");
        var sinifx = $(this).data("class");
        var point = $(this).data("point");
        swal(
            {
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: false,
                closeOnCancel: false,
                showLoaderOnConfirm: true,
            },
            function (isConfirm) {
                if (isConfirm) {
                    setTimeout(function () {
                        $.ajax({
                            type: 'POST',
                            url: 'revoke-point',
                            data: 'point=' + point,
                            success: function (data) {
                                if (data == 1) {
                                    swal(
                                        {
                                            title: "Deleted!",
                                            text: "Behavior point successfully revoked.",
                                            type: "success",
                                            confirmButtonText: "OK",
                                            closeOnConfirm: true
                                        });
                                    $.ajax(
                                        {
                                            url: "student-feedback?id=" + ogrencix + "&class_id=" + sinifx,
                                            type: "GET",
                                            contentType: false,
                                            cache: false,
                                            processData: false,
                                            success: function (data) {
                                                if (data == 0) {
                                                    $(".modal-student-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                                } else {
                                                    $(".modal-student-content").html(data);
                                                    $('.nav-tabs a[href="#history"]').tab('show');
                                                    $('.report-behavior-list').DataTable({responsive: true});
                                                    $('.report-redeem-list').DataTable({responsive: true});
                                                }
                                            }
                                        });
                                    if(document.URL.includes("report")) {
                                            var timefilter = $("select#timefilter").val();
                                            var regexp = /[^0-9]/g;
                                            var date1 = $("input.date1").val();
                                            var date2 = $("input.date2").val();
                                            $.ajax(
                                                {
                                                    url: "get-report-"+ogrencix+"?classes=" + sinifx + "&timefilter=" + timefilter.replace(regexp, '') + "&date1=" + date1 + "&date2=" + date2,
                                                    type: "GET",
                                                    contentType: false,
                                                    cache: false,
                                                    processData: false,
                                                    dataType: 'json',
                                                    success: function (data) {
                                                        if (data == 0) {
                                                            $("#studentReportContent2").html("<div class='col-xs-12 col-sm-12 col-md-12 col-lg-4'><div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div></div>");
                                                        } else {
                                                            $("#studentReportContent").html(data.general);
                                                            $("#studentReportContent2").html(data.table);
                                                            $.each($('.chart.chart-pie'), function (i, key) {
                                                                $(key).sparkline(undefined, {
                                                                    type: 'pie',
                                                                    height: '50px',
                                                                    sliceColors: ['#4CAF50', '#F44336']
                                                                });
                                                            });
                                                            $('.report-behavior-list2').DataTable({
                                                                responsive: {
                                                                    details: {
                                                                        display: $.fn.dataTable.Responsive.display.modal({
                                                                            header: function (row) {
                                                                                return 'Details:';
                                                                            }
                                                                        }),
                                                                        renderer: $.fn.dataTable.Responsive.renderer.tableAll({
                                                                            tableClass: 'table'
                                                                        })
                                                                    }
                                                                }
                                                            });
                                                        }
                                                    }
                                                });
                                    }
                                } else {
                                    swal(
                                        {
                                            title: "Error!",
                                            text: "Somethings went wrong. Please try again.",
                                            type: "error",
                                            confirmButtonText: "OK",
                                            closeOnConfirm: true
                                        });
                                }
                            }
                        });
                    }, 1000);
                } else {
                    swal(
                        {
                            title: "Canceled!",
                            text: "Your request has been canceled.",
                            type: "error",
                            confirmButtonText: "OK",
                            closeOnConfirm: true
                        });
                }
            });
    });
    $('body').on('submit', '#Edit-Student-Form', function (e) {
        e.preventDefault();

        $('.Edit-Student-Button').prop('disabled', true);
        $('.Edit-Student-Button').html("Student Editing...");

        $("#Edit-Student-Result").empty();

        $.ajax(
            {
                url: "editstudent2",
                type: "POST",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    setTimeout(function () {
                        $('.Edit-Student-Button').prop('disabled', false);
                        $('.Edit-Student-Button').html("Edit Student");
                        if (data == 0) {
                            $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                        }
                        if (data == 1) {
                            $("#Edit-Student-Result").html("<div class='alert alert-success'><strong>Successful!</strong> The student has been successfully edited.</div>");
                            if(document.URL.includes("report")) {
                                window.location.reload();
                            }
                        }
                        if (data == 2) {
                            $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                        }
                        if (data == 3) {
                            $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Öğrenci adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                        }
                        if (data == 4) {
                            $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Öğrenciye ait en az bir adet sınıf seçmelisiniz.</div>");
                        }
                        if (data == 5) {
                            $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Veli adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                        }
                        if (data == 6) {
                            $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Birincil veli e-postası için geçerli bir e-posta adresi giriniz.</div>");
                        }
                        if (data == 7) {
                            $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> İkincil veli e-postası için geçerli bir e-posta adresi giriniz.</div>");
                        }
                        if (data == 8) {
                            $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Homeroom minimum 3 characters, maximum 64 characters required.</div>");
                        }
                        if (data == 9) {
                            $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Gender maximum 32 characters required.</div>");
                        }
                    }, 1000);
                }
            });
    });
    $('body').on('submit', '#Send-Message-Form', function (e) {
        e.preventDefault();
        $('.Send-Message-Button').prop('disabled', true);
        $('.Send-Message-Button').html("Sending...");
        $("#Send-Message-Result").empty();
        $.ajax(
            {
                url: "start-conversation2",
                type: "POST",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                dataType: 'json',
                success: function (data) {
                    setTimeout(function () {
                        if (data.sonuc == 1) {
                            $("#Send-Message-Form").fadeOut();
                            $('.Send-Message-Button').prop('disabled', false);
                            $('.Send-Message-Button').html("Send Message");
                            $("#Send-Message-Result").html("<div class='notice notice-success'><strong>Mesajınız başarıyla gönderildi!</strong><br><a href='messages-" + data.class_name + "-" + data.class_id + "-" + data.user_id + "-" + data.conversation_id + "'>Buraya tıklayarak</a> konuşmayı görüntüleyebilirsiniz.</div>");
                        } else if (data == 0) {
                            $('.Send-Message-Button').prop('disabled', false);
                            $('.Send-Message-Button').html("Send Message");
                            $("#Send-Message-Result").html("<div class='notice notice-danger'><strong>Mesajınız gönderilemedi.</strong><br> Teknik bir problemden dolayı mesajınız gönderilemedi. Lütfen tekrar deneyin.</div>");
                        } else if (data == 2) {
                            $('.Send-Message-Button').prop('disabled', false);
                            $('.Send-Message-Button').html("Send Message");
                            $("#Send-Message-Result").html("<div class='notice notice-danger'><strong>Hata!</strong><br> Lütfen bir mesaj yazın.</div>");
                        }
                    }, 1000);
                }
            });
    });
    $('body').on('submit', '#editBehaviorForm', function (e) {
        e.preventDefault();

        $('.editBehaviorButton').prop('disabled', true);
        $('.editBehaviorButton').html("Behavior Editing...");

        $("#editBehaviorResult").empty();

        var behavior = $('.editBehaviorButton').data("behavior");

        $.ajax(
            {
                url: "edit-behavior?id=" + behavior,
                type: "POST",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    setTimeout(function () {
                        $('.editBehaviorButton').prop('disabled', false);
                        $('.editBehaviorButton').html("Edit This Behavior");
                        if (data == 0) {
                            $("#editBehaviorResult").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                        }
                        if (data == 1) {
                            $("#editBehaviorResult").html("<div class='alert alert-success'><strong>Successful!</strong> Behavior successfully edited.</div>");
                        }
                        if (data == 2) {
                            $("#editBehaviorResult").html("<div class='alert alert-danger'><strong>Error:</strong> Please fill in the form completely.</div>");
                        }
                        if (data == 3) {
                            $("#editBehaviorResult").html("<div class='alert alert-danger'><strong>Error:</strong> The behavior name can have a minimum of 3 characters and a maximum of 64 characters.</div>");
                        }
                        if (data == 4) {
                            $("#editBehaviorResult").html("<div class='alert alert-danger'><strong>Error:</strong> Behavior point can be between 1 and 100.</div>");
                        }
                    }, 1000);
                }
            });
    });
    $('body').on('click', '.deleteBehaviorButton', function (e) {
        e.preventDefault();
        var behavior = $(this).data("behavior");
        swal(
            {
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: false,
                closeOnCancel: false,
                showLoaderOnConfirm: true,
            },
            function (isConfirm) {
                if (isConfirm) {
                    setTimeout(function () {
                        $.ajax({
                            type: 'POST',
                            url: 'delete-behavior',
                            data: 'id=' + behavior,
                            success: function (data) {
                                if (data == 1) {
                                    $('.goStudentGeneralModal').trigger('click');
                                    swal(
                                        {
                                            title: "Deleted!",
                                            text: "Behavior has been deleted.",
                                            type: "success",
                                            confirmButtonText: "OK",
                                            closeOnConfirm: true
                                        });
                                } else {
                                    swal(
                                        {
                                            title: "Error!",
                                            text: "Somethings went wrong. Please try again.",
                                            type: "error",
                                            confirmButtonText: "OK",
                                            closeOnConfirm: true
                                        });
                                }
                            }
                        });
                    }, 1000);
                } else {
                    swal(
                        {
                            title: "Canceled!",
                            text: "Your request has been canceled.",
                            type: "error",
                            confirmButtonText: "OK",
                            closeOnConfirm: true
                        });
                }
            });
    });
    $("body").on('click', '.give-behavior', function (e) {
        if($(this).hasClass('editMode')) {
            $('#modal-student').modal('toggle');
            $('#modal-edit-behavior').modal('toggle');
            var ogrencix = $(this).data("student");
            var sinifx = $(this).data("class");
            var behavior = $(this).data("behavior");
            $.ajax(
                {
                    url: "edit-behavior-modal?id=" + behavior + "&sid=" + ogrencix + "&cid=" + sinifx,
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (data) {
                        if (data == 0) {
                            $(".modal-edit-behavior-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                        } else {
                            $(".modal-edit-behavior-content").html(data);
                        }
                    }
                });
            return false;
        }
        positiveSound.play();
        positiveSound.pause();
        negativeSound.play();
        negativeSound.pause();
        var student = $(this).data("student");
        var classe = $(this).data("class");
        var behavior = $(this).data("behavior");
        var description_1 = $("textarea#feedback_description_1").val();
        var description_2 = $("textarea#feedback_description_2").val();
        var point_location_1 = $("select#point_location_1").val();
        var point_location_2 = $("select#point_location_2").val();
        var behavior_name = $(this).find(".btn-inner--text").text();
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom',
            showConfirmButton: false,
            timer:5000,
            timerProgressBar: true,
            showClass: {
                popup: 'animated bounceInDown'
            },
            onOpen: function onOpen(toast) {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'give-behavior',
            data: 'id=' + student + '&class=' + classe + '&behavior=' + behavior + '&feedback_description_1=' + description_1 + '&feedback_description_2=' + description_2 + '&point_location_1=' + point_location_1 + '&point_location_2=' + point_location_2,
            dataType: 'json',
            success: function (data) {
                if (data.sonuc == 1) {
                    $('#modal-student').modal('toggle');
                    if (data.feedback_type == 1) {
                        positiveSound.currentTime = 0;
                        positiveSound.play();
                        Toast.fire({
                            background: '#2b982b',
                            title: "<span class='col-white'><i class='material-icons' style='position:relative;top:4px;right:5px'>thumb_up</i>"+data.feedback_name+" points is given to "+data.student_name+"</span>",
                        });
                    } else if (data.feedback_type == 2) {
                        negativeSound.currentTime = 0;
                        negativeSound.play();
                        Toast.fire({
                            background: '#fb483a',
                            title: "<span class='col-white'><i class='material-icons' style='position:relative;top:6px;right:5px'>thumb_down</i>"+data.feedback_name+" points is given to "+data.student_name+"</span>",
                        });
                    }
                    if(document.URL.includes("report")) {
                        var timefilter = $("select#timefilter").val();
                        var regexp = /[^0-9]/g;
                        var date1 = $("input.date1").val();
                        var date2 = $("input.date2").val();
                        $.ajax(
                            {
                                url: "get-report-"+student+"?classes=" + classe + "&timefilter=" + timefilter.replace(regexp, '') + "&date1=" + date1 + "&date2=" + date2,
                                type: "GET",
                                contentType: false,
                                cache: false,
                                processData: false,
                                dataType: 'json',
                                success: function (data) {
                                    if (data == 0) {
                                        $("#studentReportContent2").html("<div class='col-xs-12 col-sm-12 col-md-12 col-lg-4'><div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div></div>");
                                    } else {
                                        $("#studentReportContent").html(data.general);
                                        $("#studentReportContent2").html(data.table);
                                        $.each($('.chart.chart-pie'), function (i, key) {
                                            $(key).sparkline(undefined, {
                                                type: 'pie',
                                                height: '50px',
                                                sliceColors: ['#4CAF50', '#F44336']
                                            });
                                        });
                                        $('.report-behavior-list2').DataTable({
                                            responsive: {
                                                details: {
                                                    display: $.fn.dataTable.Responsive.display.modal({
                                                        header: function (row) {
                                                            return 'Details:';
                                                        }
                                                    }),
                                                    renderer: $.fn.dataTable.Responsive.renderer.tableAll({
                                                        tableClass: 'table'
                                                    })
                                                }
                                            }
                                        });
                                    }
                                }
                            });
                    }
                } else if (data.sonuc == 0) {
                    Toast.fire({
                        icon: 'error',
                        title: "There was a technical problem. Behavior could not be given to student. Please try again.",
                    });
                }
            }
        });
    });
    $("body").on('click', '.giveRedeem', function (e) {
        e.preventDefault();
        positiveSound.play();
        positiveSound.pause();
        negativeSound.play();
        negativeSound.pause();
        var student = $(this).data("student");
        var classe = $(this).data("class");
        var redeem = $(this).data("redeem");
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom',
            showConfirmButton: false,
            timer:5000,
            timerProgressBar: true,
            showClass: {
                popup: 'animated bounceInDown'
            },
            onOpen: function onOpen(toast) {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        swal(
            {
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: true,
                closeOnCancel: false,
            },
            function (isConfirm) {
                if (isConfirm) {

                    $.ajax({
                        type: 'POST',
                        url: 'give-redeem',
                        data: 'id=' + student + '&class=' + classe + '&redeem=' + redeem,
                        success: function (data) {
                            if (data == 1) {
                                $('#modal-student').modal('toggle');
                                positiveSound.currentTime = 0;
                                positiveSound.play();
                                Toast.fire({
                                    background: '#03A9F4',
                                    title: "<span class='col-white'><i class='material-icons' style='position:relative;top:4px;right:5px'>check_circle</i>Redeem item successfully gived to student.</span>",
                                });
                                if(document.URL.includes("report")) {
                                    var timefilter = $("select#timefilter").val();
                                    var regexp = /[^0-9]/g;
                                    var date1 = $("input.date1").val();
                                    var date2 = $("input.date2").val();
                                    $.ajax(
                                        {
                                            url: "get-report-" + student + "?classes=" + classe + "&timefilter=" + timefilter.replace(regexp, '') + "&date1=" + date1 + "&date2=" + date2,
                                            type: "GET",
                                            contentType: false,
                                            cache: false,
                                            processData: false,
                                            dataType: 'json',
                                            success: function (data) {
                                                if (data == 0) {
                                                    $("#studentReportContent2").html("<div class='col-xs-12 col-sm-12 col-md-12 col-lg-4'><div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div></div>");
                                                } else {
                                                    $("#studentReportContent").html(data.general);
                                                    $("#studentReportContent2").html(data.table);
                                                    $.each($('.chart.chart-pie'), function (i, key) {
                                                        $(key).sparkline(undefined, {
                                                            type: 'pie',
                                                            height: '50px',
                                                            sliceColors: ['#4CAF50', '#F44336']
                                                        });
                                                    });
                                                    $('.report-behavior-list2').DataTable({
                                                        responsive: {
                                                            details: {
                                                                display: $.fn.dataTable.Responsive.display.modal({
                                                                    header: function (row) {
                                                                        return 'Details:';
                                                                    }
                                                                }),
                                                                renderer: $.fn.dataTable.Responsive.renderer.tableAll({
                                                                    tableClass: 'table'
                                                                })
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                }
                            } else if(data == 2) {
                                negativeSound.currentTime = 0;
                                negativeSound.play();
                                Toast.fire({
                                    background: '#fb483a',
                                    title: "<span class='col-white'><i class='material-icons' style='position:relative;top:4px;right:5px'>error</i>The student's total score is not enough to redeem this item.</span>",
                                });
                            } else {
                                negativeSound.currentTime = 0;
                                negativeSound.play();
                                Toast.fire({
                                    icon: 'error',
                                    title: "The student's total score is not enough to redeem this item.",
                                });
                            }
                        }
                    });

                } else {
                    swal(
                        {
                            title: "Canceled!",
                            text: "Your request has been canceled.",
                            type: "error",
                            confirmButtonText: "OK",
                            closeOnConfirm: true
                        });
                }
            });
    });
    $('body').on('submit', '#Add-Behavior-Form', function (e) {
        e.preventDefault();

        $('.Add-Behavior-Button').prop('disabled', true);
        $('.Add-Behavior-Button').html("Behavior Adding...");

        $("#Add-Behavior-Result").empty();

        $.ajax(
            {
                url: "add-behavior",
                type: "POST",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                dataType: 'json',
                success: function (data) {
                    setTimeout(function () {
                        $('.Add-Behavior-Button').prop('disabled', false);
                        $('.Add-Behavior-Button').html("Add Behavior");
                        if (data.sonuc == 0) {
                            $("#Add-Behavior-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                        }
                        if (data.sonuc == 1) {
                            $("#Add-Behavior-Result").html("<div class='alert alert-success'><strong>Successful!</strong> The behavior has been successfully added to the system.</div>");
                            var regexp = /[^0-9]/g;
                            var student = data.student;
                            var classe = data.class;
                            $.ajax(
                                {
                                    url: "student-feedback?id=" + student + "&class_id=" + classe,
                                    type: "GET",
                                    contentType: false,
                                    cache: false,
                                    processData: false,
                                    success: function (data) {
                                        if (data == 0) {
                                            $(".modal-student-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                        } else {
                                            $(".modal-student-content").html(data);
                                            $('.report-behavior-list').DataTable({responsive: true});
                                            $('.report-redeem-list').DataTable({responsive: true});
                                        }
                                    }
                                });
                            $("#Add-Behavior-Form").trigger("reset");
                        }
                        if (data.sonuc == 2) {
                            $("#Add-Behavior-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                        }
                        if (data.sonuc == 3) {
                            $("#Add-Behavior-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Behavior adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                        }
                        if (data.sonuc == 4) {
                            $("#Add-Behavior-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Behavior point 1-100 aralığında olabilir.</div>");
                        }
                    }, 1000);
                }
            });
    });
});