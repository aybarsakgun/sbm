<?php
define('VAL1', TRUE);
require_once("top.php");
if (!isset($page_request)) {
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <?php
                    if ($uyerol == "teacher") {
                        $sorgudavet = $DB_con->prepare("SELECT id,class,inviting_by FROM invited_teachers WHERE invited = :invited");
                        $sorgudavet->execute(array(":invited"=>$uyevtid));
                        if($sorgudavet->rowCount() > 0)
                        {
                            while($yazdavet = $sorgudavet->fetch(PDO::FETCH_ASSOC))
                            {
                                $sorguogretmen = $DB_con->prepare("SELECT users.id FROM users WHERE role = :role AND schools = :schools AND users.id = :teacherid AND (SELECT classes.id FROM classes WHERE classes.id = :classid AND FIND_IN_SET(users.id, classes.teachers))");
                                $sorguogretmen->execute(array(":role"=>"teacher",":schools"=>$uyeokul,":teacherid"=>$uyevtid,":classid"=>$yazdavet['class']));
                                if($sorguogretmen->rowCount() == 0)
                                {
                                    $sorgusinifad = $DB_con->prepare("SELECT name FROM classes WHERE id = :classid");
                                    $sorgusinifad->execute(array(":classid"=>$yazdavet["class"]));
                                    $yazsinifad = $sorgusinifad->fetch(PDO::FETCH_ASSOC);
                                    $sorgudaveteden = $DB_con->prepare("SELECT name FROM users WHERE id = :invitor");
                                    $sorgudaveteden->execute(array(":invitor"=>$yazdavet["inviting_by"]));
                                    $yazdaveteden = $sorgudaveteden->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12 inviteCard">
                                        <div class="card animated shake">
                                            <div class="header">
                                                <h2>
                                                    You have been invited to <strong><?=$yazsinifad["name"]?></strong> as a teacher.<small>Invited by: <strong><?=$yazdaveteden["name"]?></strong></small>
                                                </h2>
                                                <button type="button" class="btn btn-success btn-circle waves-effect waves-circle waves-float m-t-20 acceptInvite" data-invite-id="<?=$yazdavet["id"]?>">
                                                    <i class="material-icons">check</i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-circle waves-effect waves-circle waves-float m-t-20 declineInvite" data-invite-id="<?=$yazdavet["id"]?>">
                                                    <i class="material-icons">close</i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                } else {
                                    $sorgusil = $DB_con->prepare("DELETE FROM invited_teachers WHERE id = :id");
                                    $sorgusil->execute(array(":id"=>$yazdavet["id"]));
                                }
                            }
                        }
                        ?>
                        <div class="col-lg-8">
                            <div class="row">
                        <?php
                        $sorgu = $DB_con->prepare("SELECT id,name,color,status FROM classes WHERE FIND_IN_SET(:uyeid,teachers) AND school = :school ORDER BY id ASC");
                        $sorgu->execute(array(":uyeid" => $uyevtid, ":school" => $uyeokul));
                        if ($sorgu->rowCount() > 0) {
                            while ($yaz = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                                ?>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="card border-radius-6">
                                        <div class="header border-radius-6 bg-<?= $yaz["color"] ?>">
                                            <?php
                                            $sinifogrencisay = $DB_con->prepare("SELECT COUNT(id) as sinifogrencisay FROM users WHERE role = :role AND FIND_IN_SET(:sinifid, classes)");
                                            $sinifogrencisay->execute(array(":role" => "student", ":sinifid" => $yaz["id"]));
                                            $yazsinifogrencisay = $sinifogrencisay->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                            <h2 class="nowrapwithellipsis">
                                                <?php if($yaz['status'] == 1) { ?>
                                                <a href="class-<?= seo($yaz["name"]) ?>-<?= $yaz["id"] ?>"
                                                   class="font-bold text-color-white"><?= $yaz["name"] ?></a>
                                                <?php } else if($yaz['status'] == 2) { ?>
                                                    <a href="javascript:;"
                                                       class="font-bold text-color-white"><?= $yaz["name"] ?></a>
                                                <?php } ?>
                                                <small><?= $yazsinifogrencisay["sinifogrencisay"] ?> Öğrenci<?php if($yaz['status'] == 2) {?> - (Archived)<?php } ?></small>
                                            </h2>
                                            <ul class="header-dropdown m-r--5">
                                                <li class="dropdown">
                                                    <a href="javascript:void(0);" class="dropdown-toggle"
                                                       data-toggle="dropdown" role="button" aria-haspopup="true"
                                                       aria-expanded="false">
                                                        <i class="material-icons">more_vert</i>
                                                    </a>
                                                    <ul class="dropdown-menu pull-right">
                                                        <?php if($yaz['status'] == 1) { ?>
                                                        <li><a href="class-<?= seo($yaz["name"]) ?>-<?= $yaz["id"] ?>"
                                                               class=" waves-effect waves-block">View</a></li>
                                                        <?php } ?>
                                                        <li><a href="edit-class-<?= $yaz["id"] ?>"
                                                               class=" waves-effect waves-block">Settings</a></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="alert alert-danger">Henüz öğretmeni olduğunuz sınıf bulunmamakta.</div>
                            <?php
                        }
                        ?>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card card-announcements">
                                <div class="header">
                                    <h2 style="font-weight:bold;">Announcements</h2>
                                </div>
                                <div class="body">
                                    <ul>
                                        <?php
                                        $announcements = $DB_con->prepare("SELECT * FROM announcements WHERE school = :school AND date(date) = CURDATE() ORDER BY date DESC");
                                        $announcements->execute(array(":school"=>$uyeokul));
                                        if ($announcements->rowCount() > 0) {
                                            while ($ann = $announcements->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                <li>
                                                    <div class="title">
                                                        <time class="icon">
                                                            <em><?=date('D', strtotime($ann["date"]))?></em>
                                                            <strong><?=date('F', strtotime($ann["date"]))?></strong>
                                                            <span><?=date('d', strtotime($ann["date"]))?></span>
                                                        </time>
                                                        <div class="content"><?= $ann["detail"] ?></div>
                                                    </div>
                                                </li>
                                                <?php
                                            }
                                        } else {
                                            $announcements = $DB_con->prepare("SELECT * FROM announcements WHERE school = :school ORDER BY date DESC");
                                            $announcements->execute(array(":school"=>$uyeokul));
                                            while ($ann = $announcements->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                <li>
                                                    <div class="title" style="display:flex">
                                                        <time class="icon">
                                                            <em><?=date('D', strtotime($ann["date"]))?></em>
                                                            <strong><?=date('F', strtotime($ann["date"]))?></strong>
                                                            <span><?=date('d', strtotime($ann["date"]))?></span>
                                                        </time>
                                                        <div class="content"><?= $ann["detail"] ?></div>
                                                    </div>
                                                </li>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                            <?php
                    } else if ($uyerol == "admin") {
                        $sorguogretmenler = $DB_con->prepare("SELECT id FROM users WHERE role = :role AND schools = :school");
                        $sorguogretmenler->execute(array(":role" => "teacher", ":school" => $uyeokul));
                        $ogretmen_say = $sorguogretmenler->rowCount();
                        $sorguogrenciler = $DB_con->prepare("SELECT id FROM users WHERE role = :role AND schools = :school");
                        $sorguogrenciler->execute(array(":role" => "student", ":school" => $uyeokul));
                        $ogrenci_say = $sorguogrenciler->rowCount();
                        $sorgusiniflar = $DB_con->prepare("SELECT id FROM classes WHERE school = :school");
                        $sorgusiniflar->execute(array(":school" => $uyeokul));
                        $sinif_say = $sorgusiniflar->rowCount();
                        ?>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="card">
                                <div class="header">
                                    <h2>
                                        Welcome to <strong>SBM School Admin Panel</strong>
                                    </h2>
                                </div>
                                <div class="body">
                                    Here you can manage the your school.
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                    <a href="classes" class="info-kutu">
                                        <div class="info-box hover-zoom-effect">
                                            <div class="icon bg-green">
                                                <i class="material-icons">class</i>
                                            </div>
                                            <div class="content">
                                                <div class="text">Classes</div>
                                                <div class="number"><?= $sinif_say ?></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                    <a href="teachers" class="info-kutu">
                                        <div class="info-box hover-zoom-effect">
                                            <div class="icon bg-blue">
                                                <i class="material-icons">business_center</i>
                                            </div>
                                            <div class="content">
                                                <div class="text">Teachers</div>
                                                <div class="number"><?= $ogretmen_say ?></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                    <a href="students" class="info-kutu">
                                        <div class="info-box hover-zoom-effect">
                                            <div class="icon bg-orange">
                                                <i class="material-icons">school</i>
                                            </div>
                                            <div class="content">
                                                <div class="text">Students</div>
                                                <div class="number"><?= $ogrenci_say ?></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <?php
                                if ($ogretmen_say < 1) {
                                    ?>
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="alert alert-danger" role="alert">
                                            Henüz yöneticisi olduğunuz okula ait sisteme kayıtlı öğretmen bulunmuyor.
                                            Okulunuza öğretmenleri davet ederek onların da sisteme kaydolmalarını
                                            sağlayın!
                                            <br>
                                            <a href="invite-teacher" class="btn btn-default waves-effect btn-xs"><i
                                                        class="material-icons">contact_mail</i><span>Invite Teacher</span></a>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                            $failedLoginAttempts = $DB_con->prepare("SELECT * FROM login_attempts_user WHERE member_id = :userId AND status = :status AND YEARWEEK(date_time, 1) = YEARWEEK(CURDATE(), 1)");
                            $failedLoginAttempts->execute(array(':userId'=>$uyevtid,':status'=>0));
                            if($failedLoginAttempts->rowCount() > 0) {
                                while($fetchFailedLoginAttempts = $failedLoginAttempts->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <div class="alert alert-danger alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                                        <strong>Browser: </strong><?=$fetchFailedLoginAttempts['browser']?><br>
                                        <strong>IP Address: </strong><?=$fetchFailedLoginAttempts['ip']?><br>
                                        <small><?=printDate($DB_con, $fetchFailedLoginAttempts["date_time"], $uyeokul)?> (Unsuccessful login attempt in this week.)</small>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <?php
                    } else if ($uyerol == "student") {
                        $sorgu = $DB_con->prepare("SELECT classes.id,classes.name,classes.color FROM users INNER JOIN classes ON FIND_IN_SET(classes.id, users.classes) WHERE users.id = :uyeid AND role = :role AND schools = :school AND status = :status ORDER BY classes.id ASC");
                        $sorgu->execute(array(":uyeid" => $uyevtid, ":role" => "student", ":school" => $uyeokul , ":status" => 1));
                        if ($sorgu->rowCount() > 0) {
                            while ($yaz = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                                ?>
                                <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">
                                    <div class="card">
                                        <div class="header bg-<?= $yaz["color"] ?>">
                                            <?php
                                            $sinifogrencisay = $DB_con->prepare("SELECT COUNT(id) as sinifogrencisay FROM users WHERE role = :role AND FIND_IN_SET(:sinifid, classes)");
                                            $sinifogrencisay->execute(array(":role" => "student", ":sinifid" => $yaz["id"]));
                                            $yazsinifogrencisay = $sinifogrencisay->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                            <h2>
                                                <a href="class-<?= seo($yaz["name"]) ?>-<?= $yaz["id"] ?>"
                                                   class="font-bold text-color-white"><?= $yaz["name"] ?></a>
                                                <small><?= $yazsinifogrencisay["sinifogrencisay"] ?> Öğrenci</small>
                                            </h2>
                                            <ul class="header-dropdown m-r--5">
                                                <li class="dropdown">
                                                    <a href="javascript:void(0);" class="dropdown-toggle"
                                                       data-toggle="dropdown" role="button" aria-haspopup="true"
                                                       aria-expanded="false">
                                                        <i class="material-icons">more_vert</i>
                                                    </a>
                                                    <ul class="dropdown-menu pull-right">
                                                        <li>
                                                            <a href="class-<?= seo($yaz["name"]) ?>-<?= $yaz["id"] ?>"
                                                               class=" waves-effect waves-block">View</a></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="alert alert-danger">Henüz öğrencisi olduğunuz aktif sınıf bulunmamakta.</div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <?php
    if ($uyerol == "admin") {
        ?>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.js"></script>
        <script>
            $(document).ready(function () {
                $.ajaxSetup({
                    headers: {'sbmtoken': $('meta[name="sbmtoken"]').attr('content')}
                });
                $(".autocomplete").autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: "search-student",
                            data: {
                                searchString: request.term
                            },
                            dataType: 'json'
                        }).done(function(data) {
                            if (data.items && data.items.length) {
                                response($.map(data.items, function(item) {
                                    return item;
                                }));
                            } else if (data.items && !data.items.length) {
                                response([{notFound: true}]);
                            }
                        });
                    },
                    delay: 500,
                    minLength: 1,
                    close: function(event, ui) {
                        var input_length = $('.autocomplete').val().length;
                        if (input_length !== 0) {
                            $("ul.ui-autocomplete, .ui-widget-content").filter(':hidden').show();
                        } else if (input_length === 0) {
                            $('.autocomplete').autocomplete('close');
                        }
                    }
                }).data("ui-autocomplete")._renderItem = function (ul, item) {
                    if (item.notFound) {
                        var inner_html = '<div class="media">Not found</div>';
                    } else {
                        var inner_html = '<a href="report-' + item.class.id + '-' + item.id + '"><div class="media"><div class="media-left"><img src="' + item.avatar + '" class="studentAvatar"></div><div class="media-body"><h4 class="media-heading">' + item.name + '</h4><strong>Class: ' + item.class.name + '</div></div></a>';
                    }
                    return $("<li></li>")
                        .data("ui-autocomplete-item", item)
                        .append(inner_html)
                        .appendTo(ul);
                };
            });
        </script>
        <?php
    } else if($uyerol == "teacher") {
    ?>
        <div class="modal fade in" id="modal-student" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-student-content">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="modal-send-mail-to-parent" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-send-mail-to-parent-content">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="modal-send-message" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-send-message-content">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="modal-edit-student" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-edit-student-content">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="modal-edit-behavior" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-edit-behavior-content">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="modal-add-behavior" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Behavior</h4>
                    </div>
                    <div class="modal-body">
                        <form id="Add-Behavior-Form">
                            <div class="form-group">
                                <label for="name">Behavior Name:</label>
                                <div class="form-line">
                                    <input class="form-control" name="name" id="name" placeholder="Behavior name..."
                                           type="text">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="type">Behavior Type:</label>
                                <select class="form-control" name="type" id="type">
                                    <option value="0">Choose...</option>
                                    <option value="1">Positive</option>
                                    <option value="2">Negative</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="point">Behavior Point(only number):</label>
                                <div class="form-line">
                                    <input class="form-control" name="point" id="point" placeholder="Behavior point..."
                                           type="text">
                                </div>
                            </div>
                            <input type="hidden" name="hidden_student_id" id="hidden_student_id">
                            <input type="hidden" name="hidden_class_id" id="hidden_class_id">
                            <div id="Add-Behavior-Result"></div>
                            <div class="form-group">
                                <button type="submit"
                                        class="btn btn-primary btn-block btn-lg waves-effect Add-Behavior-Button">Add
                                    Behavior
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
        <script src="https://cdn.jsdelivr.net/npm/promise-polyfill@7.1.0/dist/promise.min.js"></script>
        <script src="plugins/jquery-datatable/jquery.dataTables.min.js"></script>
        <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.min.js"></script>
        <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.responsive.min.js"></script>
        <script src="plugins/jquery-datatable/skin/bootstrap/js/responsive.bootstrap.min.js"></script>
        <script src="//cdn.ckeditor.com/4.13.0/basic/ckeditor.js"></script>
        <script src="plugins/bootstrap-select/js/bootstrap-select.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.js"></script>
        <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
        <script src="js/student-point-actions.js"></script>
        <script>
        $(document).ready(function () {
            $(".autocomplete").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "search-student",
                        data: {
                            searchString: request.term
                        },
                        dataType: 'json'
                    }).done(function(data) {
                        if (data.items && data.items.length) {
                            response($.map(data.items, function(item) {
                                return item;
                            }));
                        } else if (data.items && !data.items.length) {
                            response([{notFound: true}]);
                        }
                    });
                },
                delay: 500,
                minLength: 3,
                close: function(event, ui) {
                    var input_length = $('.autocomplete').val().length;
                    if (input_length !== 0) {
                        $("ul.ui-autocomplete, .ui-widget-content").filter(':hidden').show();
                    } else if (input_length === 0) {
                        $('.autocomplete').autocomplete('close');
                    }
                },
                select: function(event, ui) {
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function (ul, item) {
                if (item.notFound) {
                    var inner_html = '<div class="media">Not found</div>';
                } else {
                    var inner_html = '<a href="#" data-toggle="modal" data-target="#modal-student" class="ogrenci-puanla" id="' + item.id + '" class_id="' + item.class.id + '"><div class="media"><div class="media-left"><img src="' + item.avatar + '" class="studentAvatar"></div><div class="media-body"><h4 class="media-heading">' + item.name + '</h4><strong>Class: ' + item.class.name + '</div></div></a>';
                }
                return $("<li></li>")
                    .data("ui-autocomplete-item", item)
                    .append(inner_html)
                    .appendTo(ul);
            };
            $('body').on('click', '.acceptInvite', function (e) {
                e.preventDefault();
                var inviteid = $(this).data("invite-id");
                swal(
                            {
                                title: "Are you sure to accept the invitation?",
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
                                            url: 'invite-answer',
                                            data: 'id=' + inviteid + '&type=1',
                                            success: function (data) {
                                            if (data == 1) {
                                                swal(
                                                        {
                                                            title: "Accepted!",
                                                            text: "You are the teacher of this class now.",
                                                            type: "success",
                                                            confirmButtonText: "OK",
                                                            closeOnConfirm: true
                                                        });
                                                location.reload();
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
            $('body').on('click', '.declineInvite', function (e) {
                e.preventDefault();
                var inviteid = $(this).data("invite-id");
                swal(
                    {
                        title: "Are you sure to decline the invitation?",
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
                                    url: 'invite-answer',
                                    data: 'id=' + inviteid + '&type=2',
                                    success: function (data) {
                                        if (data == 1) {
                                            swal(
                                                {
                                                    title: "Declined!",
                                                    text: "You declined the invitation request.",
                                                    type: "success",
                                                    confirmButtonText: "OK",
                                                    closeOnConfirm: true
                                                });
                                            $(".inviteCard").hide();
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
        });
        </script>
        <?php
    }
    ?>
    </body>
    </html>
    <?php
} else if ($page_request == "create-class") {
    if ($uyerol == "student") {
        echo "Forbidden";
        exit();
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="header">
                                <h4>Create Class</h4>
                            </div>
                            <a href="<?= $uyerol === "teacher" ? "home" : "classes" ?>"
                               class="btn btn-default btn-block btn-lg waves-effect"><i class="material-icons">arrow_back</i><span>Go Back</span></a>
                            <div class="body">
                                <form id="Create-Class-Form">
                                    <div class="form-group mb-3">
                                        <label for="class_name">Class Name:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="class_name" id="class_name"
                                                   placeholder="Class name..." type="text">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="class_color">Color:</label>
                                        <div class="demo-choose-skin renksecx">
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="red">
                                                <div class="red"></div>
                                                <span>Red</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="pink">
                                                <div class="pink"></div>
                                                <span>Pink</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="purple">
                                                <div class="purple"></div>
                                                <span>Purple</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="deep-purple">
                                                <div class="deep-purple"></div>
                                                <span>Deep Purple</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="indigo">
                                                <div class="indigo"></div>
                                                <span>Indigo</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="blue">
                                                <div class="blue"></div>
                                                <span>Blue</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="light-blue">
                                                <div class="light-blue"></div>
                                                <span>Light Blue</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="cyan">
                                                <div class="cyan"></div>
                                                <span>Cyan</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="teal">
                                                <div class="teal"></div>
                                                <span>Teal</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="green">
                                                <div class="green"></div>
                                                <span>Green</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="light-green">
                                                <div class="light-green"></div>
                                                <span>Light Green</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="lime">
                                                <div class="lime"></div>
                                                <span>Lime</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="yellow">
                                                <div class="yellow"></div>
                                                <span>Yellow</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="amber">
                                                <div class="amber"></div>
                                                <span>Amber</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="orange">
                                                <div class="orange"></div>
                                                <span>Orange</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="deep-orange">
                                                <div class="deep-orange"></div>
                                                <span>Deep Orange</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="brown">
                                                <div class="brown"></div>
                                                <span>Brown</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="grey">
                                                <div class="grey"></div>
                                                <span>Grey</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="blue-grey">
                                                <div class="blue-grey"></div>
                                                <span>Blue Grey</span>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                 data-theme="black">
                                                <div class="black"></div>
                                                <span>Black</span>
                                            </div>
                                        </div>
                                        <input type="hidden" name="class_color" id="class_color">
                                    </div>
                                    <?php
                                    if ($uyerol == "admin") {
                                        $sorguxd = $DB_con->prepare("SELECT id,name FROM users WHERE schools = :school AND role = :role");
                                        $sorguxd->execute(array(":school" => $uyeokul, ":role" => "teacher"));
                                        if ($sorguxd->rowCount() > 0) {
                                            echo '<label>Teacher(s):</label>';
                                            while ($yazogretmenlerx = $sorguxd->fetch(PDO::FETCH_ASSOC)) {
                                                echo '<div class="form-group"><input type="checkbox" id="teacher_' . $yazogretmenlerx["id"] . '" name="teachers[]" class="filled-in chk-col-orange" value="' . $yazogretmenlerx["id"] . '"><label for="teacher_' . $yazogretmenlerx["id"] . '">' . $yazogretmenlerx["name"] . '</label></div>';
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="form-group">
                                        <button type="submit"
                                                class="btn btn-success btn-block btn-lg waves-effect Create-Class-Button">
                                            Create Class
                                        </button>
                                    </div>
                                </form>
                                <div id="Create-Class-Result"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.renksecx > .color-column').on('click', function () {
                var bu = $(this);
                var secilenrenk = bu.data('theme');
                $('.renksecx > .color-column').removeClass('active');
                bu.addClass('active');
                $('input#class_color').val(secilenrenk);
            });
            $('body').on('submit', '#Create-Class-Form', function (e) {
                e.preventDefault();

                $('.Create-Class-Button').prop('disabled', true);
                $('.Create-Class-Button').html("Class Creating...");

                $("#Create-Class-Result").empty();
                $.ajax(
                    {
                        url: "create-class-a",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $('.Create-Class-Button').prop('disabled', false);
                                $('.Create-Class-Button').html("Create Class");
                                if (data == 0) {
                                    $("#Create-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data == 1) {
                                    $("#Create-Class-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Sınıf başarıyla oluşturuldu.</div>");
                                    $("#Create-Class-Form").trigger("reset");
                                }
                                if (data == 2) {
                                    $("#Create-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if (data == 3) {
                                    $("#Create-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Sınıf adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                                if (data == 4) {
                                    $("#Create-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Okul yöneticisi olduğunuz için oluşturmak istediğiniz sınıfa ait en az bir adet öğretmen seçmelisiniz. Eğer seçilecek öğretmen görünmüyorsa önce okula öğretmen davet edin.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "add-student") {
    if ($uyerol == "student") {
        echo "Forbidden";
        exit();
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="header">
                                <h4>Add Student</h4>
                            </div>
                            <a href="<?= $uyerol === "teacher" ? "home" : "students" ?>"
                               class="btn btn-default btn-block btn-lg waves-effect"><i class="material-icons">arrow_back</i><span>Go Back</span></a>
                            <div class="body">
                                <form id="Add-Student-Form">
                                    <div class="form-group mb-3">
                                        <label for="class_name">*Student Name:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="student_name" id="student_name"
                                                   placeholder="Student name..." type="text">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="class_name">*Student E-Mail:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="student_email" id="student_email"
                                                   placeholder="Student e-mail..." type="email">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="parentname">Parent Name:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="parentname" id="parentname"
                                                   placeholder="Parent name..." type="text">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="parentemail">Parent Primary E-Mail Address:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="parentemail" id="parentemail"
                                                   placeholder="Parent primary e-mail..." type="email">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="parentemail2">Parent Secondary E-Mail Address:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="parentemail2" id="parentemail2"
                                                   placeholder="Parent secondary e-mail..." type="email">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="parentphone">Parent Primary Phone Number:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="parentphone" id="parentphone"
                                                   placeholder="Parent primary phone..." type="text">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="parentphone2">Parent Secondary Phone Number:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="parentphone2" id="parentphone2"
                                                   placeholder="Parent secondary phone..." type="text">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="homeroom">Homeroom:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="homeroom" id="homeroom" type="text" placeholder="Homeroom...">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="gender">Gender:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="gender" id="gender" type="text" placeholder="Gender...">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="stateID">StateID:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="stateID" id="stateID" type="text" placeholder="State id...">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="grade">Grade:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="grade" id="grade" type="text" placeholder="Grade..." oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                        </div>
                                    </div>
                                    <label>*Classes:</label>
                                    <?php
                                    if ($uyerol == "teacher") {
                                        $sorgu = $DB_con->prepare("SELECT id,name FROM classes WHERE FIND_IN_SET(:uyeid,teachers) AND school = :school AND status = :status");
                                        $sorgu->execute(array(":uyeid" => $uyevtid, ":school" => $uyeokul , ":status" => 1));
                                        while ($yazsiniflar = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                            <div class="form-group">
                                                <input type="checkbox" id="class_<?= $yazsiniflar["id"] ?>"
                                                       name="classes[]" class="filled-in chk-col-orange"
                                                       value="<?= $yazsiniflar["id"] ?>">
                                                <label for="class_<?= $yazsiniflar["id"] ?>"><?= $yazsiniflar["name"] ?></label>
                                            </div>
                                            <?php
                                        }
                                    } else if ($uyerol == "admin") {
                                        $sorgu = $DB_con->prepare("SELECT id,name FROM classes WHERE school = :school");
                                        $sorgu->execute(array(":school" => $uyeokul));
                                        while ($yazsiniflar = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                            <div class="form-group">
                                                <input type="checkbox" id="class_<?= $yazsiniflar["id"] ?>"
                                                       name="classes[]" class="filled-in chk-col-orange"
                                                       value="<?= $yazsiniflar["id"] ?>">
                                                <label for="class_<?= $yazsiniflar["id"] ?>"><?= $yazsiniflar["name"] ?></label>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                    <div class="form-group">
                                        <button type="submit"
                                                class="btn btn-success btn-block btn-lg waves-effect Add-Student-Button">
                                            Add Student
                                        </button>
                                    </div>
                                </form>
                                <div id="Add-Student-Result"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('body').on('submit', '#Add-Student-Form', function (e) {
                e.preventDefault();

                $('.Add-Student-Button').prop('disabled', true);
                $('.Add-Student-Button').html("Student Adding...");

                $("#Add-Student-Result").empty();
                $.ajax(
                    {
                        url: "add-student-a",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $('.Add-Student-Button').prop('disabled', false);
                                $('.Add-Student-Button').html("Add Student");
                                if (data == 0) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data == 1) {
                                    $("#Add-Student-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Öğrenci başarıyla eklendi.</div>");
                                    $("#Add-Student-Form").trigger("reset");
                                }
                                if (data == 2) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen zorunlu alanları(*) doldurunuz.</div>");
                                }
                                if (data == 3) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Öğrenci adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                                if (data == 4) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen geçerli bir öğrenci e-posta adresi giriniz.</div>");
                                }
                                if (data == 5) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen en az bir adet sınıf seçiniz. Eğer seçilecek sınıf görünmüyorsa önce sınıf ekleyin.</div>");
                                }
                                if (data == 6) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Öğrenci için girdiğiniz e-posta adresi ile daha önce sisteme öğretmen veya okul yöneticisi olarak kayıt yapılmış.</div>");
                                }
                                if (data == 7) {
                                    $("#Add-Student-Result").html("<div class='alert alert-success'><strong>Successful:</strong> Öğrenci için girdiğiniz e-posta adresi ile daha önce sisteme kayıt yapılmış, öğrenci belirtilen sınıflara tekrar güncellendi.</div>");
                                }
                                if (data == 8) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Veli adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                                if (data == 9) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Birincil veli e-postası için geçerli bir e-posta adresi giriniz.</div>");
                                }
                                if (data == 10) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> İkincil veli e-postası için geçerli bir e-posta adresi giriniz.</div>");
                                }
                                if (data == 11) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Homeroom minimum 3 characters, maximum 64 characters required.</div>");
                                }
                                if (data == 12) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Gender maximum 32 characters required.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "import-student") {
    if ($uyerol == "student") {
        echo "Forbidden";
        exit();
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="header">
                                <h4>Import Student</h4>
                            </div>
                            <a href="<?= $uyerol === "teacher" ? "home" : "students" ?>"
                               class="btn btn-default btn-block btn-lg waves-effect"><i class="material-icons">arrow_back</i><span>Go Back</span></a>
                            <div class="body">
                                <form id="Add-Student-Form">
                                    <div class="form-group p-b-10">
                                        <label>CSV File:</label>
                                        <input type="file" id="import_file" name="import_file">
                                    </div>
                                    <div class="row clearfix" id="mapTable" style="display:none">
                                        <div class="col-md-3">
                                            <p>
                                                <b>* Student Name</b>
                                            </p>
                                            <select class="form-control show-tick" id="csvColumn" name="name[]" equal="name" multiple>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <b>* E-Mail Address</b>
                                            </p>
                                            <select class="form-control show-tick" id="csvColumn" name="email" equal="email">
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <b>Parent Name</b>
                                            </p>
                                            <select class="form-control show-tick" id="csvColumn" name="parent_name[]" equal="parent_name" multiple>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <b>Parent E-Mail Address</b>
                                            </p>
                                            <select class="form-control show-tick" id="csvColumn" name="parent_email" equal="parent_email">
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <b>Parent E-Mail Address 2</b>
                                            </p>
                                            <select class="form-control show-tick" id="csvColumn" name="parent_email2" equal="parent_email2">
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <b>Parent Phone</b>
                                            </p>
                                            <select class="form-control show-tick" id="csvColumn" name="parent_phone" equal="parent_phone">
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <b>Parent Phone 2</b>
                                            </p>
                                            <select class="form-control show-tick" id="csvColumn" name="parent_phone2" equal="parent_phone2">
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <b>Homeroom</b>
                                            </p>
                                            <select class="form-control show-tick" id="csvColumn" name="homeroom" equal="homeroom">
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <b>Gender</b>
                                            </p>
                                            <select class="form-control show-tick" id="csvColumn" name="gender" equal="gender">
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <b>State</b>
                                            </p>
                                            <select class="form-control show-tick" id="csvColumn" name="stateID" equal="stateID">
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <b>Grade</b>
                                            </p>
                                            <select class="form-control show-tick" id="csvColumn" name="grade" equal="grade">
                                            </select>
                                        </div>
                                    </div>
                                    <?php
                                    if ($uyerol == "teacher") {
                                        echo '<label>Class(es):</label>';
                                        $sorgu = $DB_con->prepare("SELECT id,name FROM classes WHERE FIND_IN_SET(:uyeid,teachers) AND school = :school AND status = :status");
                                        $sorgu->execute(array(":uyeid" => $uyevtid, ":school" => $uyeokul , ":status" => 1));
                                        while ($yazsiniflar = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                            <div class="form-group">
                                                <input type="checkbox" id="class_<?= $yazsiniflar["id"] ?>"
                                                       name="classes[]" class="filled-in chk-col-orange"
                                                       value="<?= $yazsiniflar["id"] ?>">
                                                <label for="class_<?= $yazsiniflar["id"] ?>"><?= $yazsiniflar["name"] ?></label>
                                            </div>
                                            <?php
                                        }
                                    } else if ($uyerol == "admin") {
                                        echo '<label>Class(es):</label>';
                                        $sorgu = $DB_con->prepare("SELECT id,name FROM classes WHERE school = :school");
                                        $sorgu->execute(array(":school" => $uyeokul));
                                        while ($yazsiniflar = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                            <div class="form-group">
                                                <input type="checkbox" id="class_<?= $yazsiniflar["id"] ?>"
                                                       name="classes[]" class="filled-in chk-col-orange"
                                                       value="<?= $yazsiniflar["id"] ?>">
                                                <label for="class_<?= $yazsiniflar["id"] ?>"><?= $yazsiniflar["name"] ?></label>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                    <div class="form-group">
                                        <button type="submit"
                                                class="btn btn-success btn-block btn-lg waves-effect Add-Student-Button">
                                            Import Student(s)
                                        </button>
                                    </div>
                                </form>
                                <div id="Add-Student-Result"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/bootstrap-select/js/bootstrap-select.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        function CSVImportGetHeaders()
        {
            $('select#csvColumn').empty();
            $('select#csvColumn:not([multiple])').append('<option selected="selected" value="">Choose</option>');
            var file = document.getElementById('import_file').files[0];
            var reader = new FileReader();
            reader.readAsArrayBuffer(file);
            reader.onloadend = function (evt) {
                var data = evt.target.result;
                var byteLength = data.byteLength;
                var ui8a = new Uint8Array(data, 0);
                var headerString = '';
                for (var i = 0; i < byteLength; i++) {
                    var char = String.fromCharCode(ui8a[i]);
                    if (char.match(/[^\r\n]+/g) !== null) {
                        headerString += char;
                    } else {
                        break;
                    }
                }
                var headersArray = headerString.split(',');
                for(var i = 0; i < headersArray.length; i++) {
                    $('select#csvColumn').append('<option value='+i+'>'+headersArray[i]+'</option>');
                }
                $('select#csvColumn').selectpicker('refresh');
                $('#mapTable').show();
            };
        }
        $(document).ready(function () {
            $('select#csvColumn').change(function(){
                $('select#csvColumn option').attr('disabled',false);
                $('select#csvColumn').each(function(){
                    var $this = $(this);
                    $('select#csvColumn').not($this).find('option').each(function(){
                        if ($.isArray($this.val())) {
                            for (let i = 0; i < $this.val().length; ++i) {
                                if($(this).attr('value') == $this.val()[i])
                                    $(this).attr('disabled',true);
                            }
                        } else {
                            if ($this.val() === '') return;
                            if($(this).attr('value') == $this.val())
                                $(this).attr('disabled',true);
                        }
                    });
                });
                $('select#csvColumn').selectpicker('refresh');
            });
            $('#import_file').on("change", function(){ CSVImportGetHeaders(); });
            $('body').on('submit', '#Add-Student-Form', function (e) {
                e.preventDefault();
                if ($('#import_file').get(0).files.length === 0) {
                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Aktarılacak herhangi bir CSV dosyası seçmediniz.</div>");
                    return false;
                }
                if ($("select[equal='name']").val() == null) {
                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> You must choose at least one for the Student Name field.</div>");
                    return false;
                }
				
                if ($("select[equal='name']").val() != null && $("select[equal='name']").val().length > 2) {
                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> You can choose a maximum of two for the Student Name field.</div>");
                    return false;
                }
                if ($("select[equal='email']").val() == '') {
                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> You must choose at least one for the E-Mail Address field.</div>");
                    return false;
                }
                if ($("select[equal='parent_name']").val() != null && $("select[equal='parent_name']").val().length > 2) {
                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> You can choose a maximum of two for the Parent Name field.</div>");
                    return false;
                }
                $('.Add-Student-Button').prop('disabled', true);
                $('.Add-Student-Button').html("Student(s) Importing...");

                $("#Add-Student-Result").empty();
                $.ajax(
                    {
                        url: "import-student-a",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                $('.Add-Student-Button').prop('disabled', false);
                                $('.Add-Student-Button').html("Import Student(s)");
                                if (data.sonuc == 0) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data.sonuc == 1) {
                                    $("#Add-Student-Result").html("<div class='alert alert-success'><strong>Successful!</strong> " + data.eklenen + " adet öğrenci sisteme aktarıldı, " + data.duzenlenen + " adet öğrencinin sınıfı düzenlendi.</div>");
                                    $("#Add-Student-Form").trigger("reset");
                                    $('select#csvColumn').empty();
                                    $('select#csvColumn').selectpicker('refresh');
                                    $('#mapTable').hide();
                                }
                                if (data.sonuc == 2) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if (data.sonuc == 3) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> CSV dosyası yüklenirken beklenmedik bir hata oluştu. Lütfen tekrar deneyin.</div>");
                                }
                                if (data.sonuc == 4) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> CSV dosyası olarak seçtiğiniz dosya CSV olarak görünmüyor. Lütfen tekrar deneyin.</div>");
                                }
                                if (data.sonuc == 5) {
                                    $("#Add-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen en az bir adet sınıf seçiniz. Eğer seçilecek sınıf görünmüyorsa önce sınıf ekleyin.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "edit-class") {
    if ($uyerol != "teacher") {
        echo "Forbidden";
        exit();
    }
    $class_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($class_id === false) {
        echo "Hata!";
        exit();
    }
    if (!isset($class_id) || empty($class_id)) {
        echo "Hata!";
        exit();
    }
    $sorgusinif = $DB_con->prepare("SELECT id FROM classes WHERE id = :id");
    $sorgusinif->execute(array(":id" => $class_id));
    if ($sorgusinif->rowCount() != 1) {
        echo "Hata!";
        exit();
    }
    $sorgu = $DB_con->prepare("SELECT id FROM classes WHERE FIND_IN_SET(:uyeid,teachers) AND school = :school AND id = :id");
    $sorgu->execute(array(":uyeid" => $uyevtid, ":school" => $uyeokul, ":id" => $class_id));
    if ($sorgu->rowCount() != 1) {
        echo "Hata!";
        exit();
    }
    $sorgusinifbilgi = $DB_con->prepare("SELECT name,color,student_show,point_show,status,points_by_time FROM classes WHERE id = :id");
    $sorgusinifbilgi->execute(array(":id" => $class_id));
    $yazsinifbilgi = $sorgusinifbilgi->fetch(PDO::FETCH_ASSOC);
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="header">
                                <h4>Edit Class<br><small><a href="class-<?=seo($yazsinifbilgi["name"])?>-<?=$class_id?>">Show this class</a></small></h4>
                            </div>
                            <a href="<?= $uyerol === "teacher" ? "home" : "classes" ?>"
                               class="btn btn-default btn-block btn-lg waves-effect"><i class="material-icons">arrow_back</i><span>Go Back</span></a>
                            <div class="body">
                                <form id="Edit-Class-Form">
                                    <ul class="nav nav-tabs tab-col-orange" role="tablist">
                                        <li role="presentation" class="active">
                                            <a href="#general_tab" data-toggle="tab" aria-expanded="true"
                                               class="font-bold">
                                                General
                                            </a>
                                        </li>
                                        <li role="presentation" class="">
                                            <a href="#display_tab" data-toggle="tab" aria-expanded="false"
                                               class="font-bold">
                                                Display
                                            </a>
                                        </li>
                                        <li role="presentation" class="">
                                            <a href="#teachers_tab" data-toggle="tab" aria-expanded="false"
                                               class="font-bold">
                                                Teachers
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane fade active in" id="general_tab">
                                            <div class="form-group mb-3">
                                                <label for="class_name">Class Name:</label>
                                                <div class="form-line">
                                                    <input class="form-control" name="class_name" id="class_name"
                                                           placeholder="Class name..." type="text"
                                                           value="<?= $yazsinifbilgi["name"] ?>">
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="class_status">Status:</label>
                                                <div class="form-line">
                                                    <select class="form-control" id="class_status"
                                                            name="class_status">
                                                        <option value="1" <?php if($yazsinifbilgi["status"] == 1) { echo "selected"; }?>>Active</option>
                                                        <option value="2" <?php if($yazsinifbilgi["status"] == 2) { echo "selected"; }?>>Archived</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="class_color">Color:</label>
                                                <div class="demo-choose-skin renksecx"
                                                     data-selected-color="<?= $yazsinifbilgi["color"] ?>">
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="red">
                                                        <div class="red"></div>
                                                        <span>Red</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="pink">
                                                        <div class="pink"></div>
                                                        <span>Pink</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="purple">
                                                        <div class="purple"></div>
                                                        <span>Purple</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="deep-purple">
                                                        <div class="deep-purple"></div>
                                                        <span>Deep Purple</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="indigo">
                                                        <div class="indigo"></div>
                                                        <span>Indigo</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="blue">
                                                        <div class="blue"></div>
                                                        <span>Blue</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="light-blue">
                                                        <div class="light-blue"></div>
                                                        <span>Light Blue</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="cyan">
                                                        <div class="cyan"></div>
                                                        <span>Cyan</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="teal">
                                                        <div class="teal"></div>
                                                        <span>Teal</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="green">
                                                        <div class="green"></div>
                                                        <span>Green</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="light-green">
                                                        <div class="light-green"></div>
                                                        <span>Light Green</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="lime">
                                                        <div class="lime"></div>
                                                        <span>Lime</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="yellow">
                                                        <div class="yellow"></div>
                                                        <span>Yellow</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="amber">
                                                        <div class="amber"></div>
                                                        <span>Amber</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="orange">
                                                        <div class="orange"></div>
                                                        <span>Orange</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="deep-orange">
                                                        <div class="deep-orange"></div>
                                                        <span>Deep Orange</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="brown">
                                                        <div class="brown"></div>
                                                        <span>Brown</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="grey">
                                                        <div class="grey"></div>
                                                        <span>Grey</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="blue-grey">
                                                        <div class="blue-grey"></div>
                                                        <span>Blue Grey</span>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 color-column"
                                                         data-theme="black">
                                                        <div class="black"></div>
                                                        <span>Black</span>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="class_color" id="class_color">
                                            </div>
                                            <div class="form-group">
                                                <button type="submit"
                                                        class="btn btn-success btn-block btn-lg waves-effect Edit-Class-Button">
                                                    Edit Class
                                                </button>
                                            </div>
                                            <div class="Edit-Class-Result"></div>
                                        </div>
                                        <div role="tabpanel" class="tab-pane fade" id="display_tab">
                                            <div class="form-group mb-3">
                                                <label for="show_lastname">Last Names:</label>
                                                <div class="form-line">
                                                    <select class="form-control" id="show_lastname"
                                                            name="show_lastname">
                                                        <option value="1" <?php if($yazsinifbilgi["student_show"] == 1) { echo "selected"; }?>>Show last names</option>
                                                        <option value="2" <?php if($yazsinifbilgi["student_show"] == 2) { echo "selected"; }?>>Hide last names</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="show_point">Point Bubbles:</label>
                                                <div class="form-line">
                                                    <select class="form-control" id="show_point" name="show_point">
                                                        <option value="1" <?php if($yazsinifbilgi["point_show"] == 1) { echo "selected"; }?>>Seperate totals</option>
                                                        <option value="2" <?php if($yazsinifbilgi["point_show"] == 2) { echo "selected"; }?>>Combined totals</option>
                                                        <option value="3" <?php if($yazsinifbilgi["point_show"] == 3) { echo "selected"; }?>>Don't show points</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="points_by_time">Points by Time:</label>
                                                <div class="form-line">
                                                    <select class="form-control" id="points_by_time" name="points_by_time">
                                                        <option value="1" <?php if($yazsinifbilgi["points_by_time"] == 1) { echo "selected"; }?>>All time</option>
                                                        <option value="2" <?php if($yazsinifbilgi["points_by_time"] == 2) { echo "selected"; }?>>Daily</option>
                                                        <option value="3" <?php if($yazsinifbilgi["points_by_time"] == 3) { echo "selected"; }?>>Weekly</option>
                                                        <option value="3" <?php if($yazsinifbilgi["points_by_time"] == 4) { echo "selected"; }?>>Monthly</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <button type="submit"
                                                        class="btn btn-success btn-block btn-lg waves-effect Edit-Class-Button">
                                                    Edit Class
                                                </button>
                                            </div>
                                            <div class="Edit-Class-Result"></div>
                                        </div>
                                        <div role="tabpanel" class="tab-pane fade" id="teachers_tab">
                                            <div class="form-group mb-3">
                                                <label for="invite_teacher">Invite Teacher to Class:</label>
                                                <div class="form-line">
                                                    <select class="form-control" id="invite_teacher"
                                                            name="invite_teacher">
                                                        <option value="0">Choose</option>
                                                        <?php
                                                        $query = $DB_con->prepare("SELECT users.id,users.name FROM users WHERE role = :role AND schools = :schools AND (SELECT classes.id FROM classes WHERE classes.id = :classid AND NOT FIND_IN_SET(users.id, classes.teachers))");
                                                        $query->execute(array(":role" => "teacher", ":schools" => $uyeokul, ":classid" => $class_id));
                                                        while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
                                                            echo '<option value="' . $result['id'] . '">' . $result['name'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <button type="button"
                                                        class="btn btn-success btn-block btn-lg waves-effect Invite-Teacher-Button">
                                                    Invite Teacher
                                                </button>
                                            </div>
                                            <div class="Invite-Teacher-Result"></div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            var seciliolanrenk = $('.renksecx').data('selected-color');
            $('.renksecx .color-column[data-theme="' + seciliolanrenk + '"]').addClass("active");
            $('input#class_color').val(seciliolanrenk);
            $('.renksecx .color-column').on('click', function () {
                var bu = $(this);
                var secilenrenk = bu.data('theme');
                $('.renksecx .color-column').removeClass('active');
                bu.addClass('active');
                $('input#class_color').val(secilenrenk);
            });
            $('body').on('submit', '#Edit-Class-Form', function (e) {
                e.preventDefault();

                $('.Edit-Class-Button').prop('disabled', true);
                $('.Edit-Class-Button').html("Class Editing...");

                $(".Edit-Class-Result").empty();
                $.ajax(
                    {
                        url: "edit-class-a-<?=$class_id?>",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $('.Edit-Class-Button').prop('disabled', false);
                                $('.Edit-Class-Button').html("Edit Class");
                                if (data == 0) {
                                    $(".Edit-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data == 1) {
                                    $(".Edit-Class-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Sınıf başarıyla düzenlendi.</div>");
                                }
                                if (data == 2) {
                                    $(".Edit-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if (data == 3) {
                                    $(".Edit-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Sınıf adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
            $('body').on('click', '.Invite-Teacher-Button', function (e) {
                e.preventDefault();

                $('.Invite-Teacher-Button').prop('disabled', true);
                $('.Invite-Teacher-Button').html("Teacher Inviting...");

                $(".Invite-Teacher-Result").empty();
                $.ajax(
                    {
                        url: "invite-teacher-t-c-<?=$class_id?>",
                        type: "POST",
                        data: "invite_teacher="+$("#invite_teacher").val(),
                        success: function (data) {
                            setTimeout(function () {
                                $('.Invite-Teacher-Button').prop('disabled', false);
                                $('.Invite-Teacher-Button').html("Edit Class");
                                if (data == 0) {
                                    $(".Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data == 1) {
                                    $(".Invite-Teacher-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Öğretmen başarıyla sınıfa davet edildi.</div>");
                                }
                                if (data == 2) {
                                    $(".Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen sınıfa davet etmek istediğiniz öğretmeni seçiniz.</div>");
                                }
                                if (data == 3) {
                                    $(".Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Davet etmek istediğiniz öğretmen zaten bu sınıfa daha önce davet edilmiş.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "sync-gc") {
    if ($uyerol == "student") {
        echo "Forbidden";
        exit();
    }
    if(isset($_GET['code']))
    {
        $gClient->authenticate($_GET['code']);
        $_SESSION['token'] = $gClient->getAccessToken();
        $gClient->setAccessToken($_SESSION['token']);
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="header">
                                <h4>Sync Google Classroom Classes</h4>
                            </div>
                            <a href="home" class="btn btn-default btn-block btn-lg waves-effect"><i
                                        class="material-icons">arrow_back</i><span>Go Back</span></a>
                            <div class="body">
                                <div class="btn-wrapper text-center">
                                    <?php
                                    if(strpos($_SESSION['token']['scope'], 'https://www.googleapis.com/auth/classroom.rosters') === false) {
                                        $gClient->addScope("https://www.googleapis.com/auth/classroom.rosters https://www.googleapis.com/auth/classroom.courses.readonly https://www.googleapis.com/auth/classroom.rosters.readonly https://www.googleapis.com/auth/classroom.profile.emails https://www.googleapis.com/auth/classroom.profile.photos");
                                        $auth_url = $gClient->createAuthUrl();
                                        $needAuth = true;
                                    } else { $needAuth = false; }
                                    ?>
                                    <a href="<?=isset($auth_url) && $needAuth == true ? $auth_url : 'javascript:;'?>" class="btn btn-block g-sign-in-button gc-siniflari-getir">
                                        <img src="img/google.svg" width="22"><strong class="p-l-5">Sync with Google
                                            Classroom</strong>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 gc-sync-result"></div>
                </div>
            </div>
        </div>
    </section>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            <?php if(isset($_GET['code'])) { ?>
            history.pushState(null, '', '/sbm/sync-gc');
            setTimeout(function() {
                $('.gc-siniflari-getir').trigger('click');
            }, 1000);
            <?php } ?>
            $(document).on("change", 'input#add_check', function () {
                var id_getir = $(this).data("input");
                var input_getir = $("input[type='text'][data-input='" + id_getir + "']").attr("name");
                if (input_getir !== "") {
                    $("input[type='text'][data-input='" + id_getir + "']").attr("name", "");
                } else {
                    var input_id_getir = $("input[type='text'][data-input='" + id_getir + "']").attr("id");
                    $("input[type='text'][data-input='" + id_getir + "']").attr("name", input_id_getir);
                }
            });
            $(document).on("click", '.gc-siniflari-getir', function () {
                if($(this).attr('href') !== 'javascript:;') { return true; }
                $('.page-loader-wrapper').fadeIn();
                $.ajax(
                    {
                        url: "gclassroom-classes",
                        type: "GET",
                        success: function (data) {
                            $(".gc-sync-result").html(data);
                            $('.page-loader-wrapper').fadeOut();
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            $(".gc-sync-result").html("<div class='alert alert-danger mb-0'>Beklenmedik bir hata oluştu. Lütfen tekrar deneyin.</div>");
                            $('.page-loader-wrapper').fadeOut();
                        }
                    });
            });
            $('body').on('submit', '#Add-Class-Form', function (e) {
                e.preventDefault();
                $('.page-loader-wrapper').fadeIn();
                $("#Add-Class-Result").empty();
                $.ajax(
                    {
                        url: "add-class-a",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        dataType: 'json',
                        success: function (data) {
                            $('.page-loader-wrapper').fadeOut();
                            if (data.sonuc == 0) {
                                $("#Add-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                            }
                            if (data.sonuc == 1) {
                                $("#Add-Class-Result").html("<div class='alert alert-success'><strong>Successful!</strong><ul><li>Google Classroomda öğretmeni olarak göründüğünüz toplam sınıf sayısı: <b>" + data.toplam_sinif + "</b></li><li>Google Classroomdan sisteme kayıdı yapılan toplam sınıf sayısı: <b>" + data.eklenen_sinif + "</b></li><li>Sistemde kayıtı mevcut olupta öğretmeni olarak güncellendiğiniz toplam sınıf sayısı: <b>" + data.duzenlenen_sinif + "</b></li><li>Google Classroomdan sisteme kayıdı yapılan toplam öğrenci sayısı: <b>" + data.eklenen_ogrenci + "</b></li><li>Sistemde kayıtı mevcut olupta sizin sınıfınıza eklenen toplam öğrenci sayısı: <b>" + data.duzenlenen_ogrenci + "</b></li></ul></div>");
                            }
                            if (data.sonuc == 2) {
                                $("#Add-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Google Classroom içerisinde bulunan sınıflarınız ve öğrencileriniz için en az bir adet öğretmen seçiniz. Eğer seçilecek öğretmen görünmüyorsa önce okula öğretmen davet edin.</div>");
                            }
                            if (data.sonuc == 3) {
                                $("#Add-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Google sizi tanıyamadı. Lütfen oturumunuzu sonlandırıp yeniden oturum açın ve tekrar deneyin.</div>");
                            }
                        }
                    });
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "redeem-items") {
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="header">
                                <h4>Redeem Items</h4>
                                <?php
                                if($uyerol != "student") {
                                ?>
                                <br>
                                <button type="button" class="btn btn-success waves-effect" data-toggle="modal"
                                        data-target="#createNewRedeemModal">
                                    <i class="material-icons">add_shopping_cart</i>
                                    <span>Create New Redeem Item</span>
                                </button>
                                <?php } ?>
                            </div>
                            <a href="home" class="btn btn-default btn-block btn-lg waves-effect"><i
                                        class="material-icons">arrow_back</i><span>Go Back</span></a>
                        </div>
                    </div>
                    <?php if($uyerol != "student") { ?>
                    <div class="col-xs-12 ol-sm-12 col-md-12 col-lg-12">
                        <div class="panel-group" id="accordion_3" role="tablist" aria-multiselectable="true">
                            <div class="panel panel-warning">
                                <div class="panel-heading" role="tab" id="headingOne_3">
                                    <h4 class="panel-title">
                                        <a role="button" data-toggle="collapse" data-parent="#accordion_3" href="#collapseOne_3" aria-expanded="false" aria-controls="collapseOne_3">
                                            Redeem History
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseOne_3" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne_3">
                                    <div class="panel-body">
                                        <?php
                                        $sorguHistoryQuery = $uyerol == "teacher" ? "SELECT feedbacks_students.id,description,(SELECT name FROM users WHERE role = :rolestudent AND id = feedbacks_students.student_id) AS student,(SELECT name FROM classes WHERE id = feedbacks_students.class_id) AS class,date,feedbacks_students.name,feedbacks_students.point FROM feedbacks_students WHERE teacher = :teacher AND feedbacks_students.type = :type AND FIND_IN_SET(class_id, (SELECT GROUP_CONCAT(id) FROM classes WHERE FIND_IN_SET(:teacherid, teachers) AND school = :schoolx)) ORDER BY feedbacks_students.id DESC" : "SELECT feedbacks_students.id,description,(SELECT name FROM users WHERE role = :rolestudent AND id = feedbacks_students.student_id) AS student,(SELECT name FROM users WHERE role = :roleteacher AND id = feedbacks_students.teacher) AS teacher,(SELECT name FROM classes WHERE id = feedbacks_students.class_id) AS class,date,feedbacks_students.name,feedbacks_students.point FROM feedbacks_students WHERE feedbacks_students.type = :type ORDER BY feedbacks_students.id DESC";
                                        $sorguHistory = $DB_con->prepare($sorguHistoryQuery);
                                        $sorguHistoryParams = $uyerol == "teacher" ? array(":rolestudent"=>"student",":teacher"=>$uyevtid,":type"=>3,":teacherid"=>$uyevtid,":schoolx"=>$uyeokul) : array(":rolestudent"=>"student",":roleteacher"=>"teacher",":type"=>3);
                                        $sorguHistory->execute($sorguHistoryParams);
                                        if($sorguHistory->rowCount() > 0)
                                        {
                                            ?>
                                            <table class="table table-bordered table-striped table-hover report-redeem-list dataTable nowrap" style="width:100%!important;">
                                                <thead>
                                                <tr>
                                                    <th>Redeem Name</th>
                                                    <th>Point</th>
                                                    <th>Student</th>
                                                    <?php if($uyerol == "admin") { echo '<th>Teacher</th>'; } ?>
                                                    <th>Class</th>
                                                    <th>Date</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                while($yazHistory = $sorguHistory->fetch(PDO::FETCH_ASSOC))
                                                {
                                                    ?>
                                                    <tr>
                                                        <td><?=$yazHistory["name"]?></td>
                                                        <td><?=abs($yazHistory["point"])?></td>
                                                        <td><?=$yazHistory["student"]?></td>
                                                        <?php if($uyerol == "admin") { echo '<td>'.$yazHistory["teacher"].'</td>'; } ?>
                                                        <td><?=$yazHistory["class"]?></td>
                                                        <td><?=printDate($DB_con, $yazHistory["date"], $uyeokul)?></td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                                </tbody>
                                            </table>
                                            <?php
                                        }
                                        else
                                        {
                                            if($uyerol == "teacher") {
                                                ?>
                                                <div class="col-12">
                                                    <div class="alert alert-warning">Henüz hiçbir öğrenciye herhangi bir
                                                        ödül vermediniz.
                                                    </div>
                                                </div>
                                                <?php
                                            } else if($uyerol == "admin") {
                                                ?>
                                                <div class="col-12">
                                                    <div class="alert alert-warning">Henüz hiçbir öğrenciye herhangi bir
                                                        ödül verilmemiş.
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 redeemItems"></div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade in" id="editRedeemModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content editRedeemContent">

            </div>
        </div>
    </div>
    <div class="modal fade in" id="createNewRedeemModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Create New Redeem Item</h4>
                </div>
                <div class="modal-body">
                    <form id="createNewRedeemForm">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <div class="form-line">
                                <input class="form-control" name="name" id="name" type="text">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name">Point:</label>
                            <div class="form-line">
                                <input class="form-control" name="point" id="point" type="text">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name">Image:</label>
                            <div class="form-line">
                                <input class="form-control" name="image" id="image" type="file">
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit"
                                    class="btn btn-primary btn-block btn-lg waves-effect createNewRedeemButton">Create
                                This Item
                            </button>
                        </div>
                    </form>
                    <div id="createNewRedeemResult"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="plugins/jquery-datatable/jquery.dataTables.min.js"></script>
    <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.min.js"></script>
    <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.responsive.min.js"></script>
    <script src="plugins/jquery-datatable/skin/bootstrap/js/responsive.bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {'sbmtoken': $('meta[name="sbmtoken"]').attr('content')}
            });
            $('.report-redeem-list').DataTable({responsive: true});
            $.ajax(
                {
                    url: "get-redeem-items",
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (data) {
                        if (data == 0) {
                            $(".redeemItems").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                        } else if (data == 2) {
                            $(".redeemItems").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Error:</strong> No items available in the system.</div>");
                        } else {
                            $(".redeemItems").html(data);
                        }
                    }
                });
            $('body').on('submit', '#createNewRedeemForm', function (e) {
                e.preventDefault();

                $('.createNewRedeemButton').prop('disabled', true);
                $('.createNewRedeemButton').html("Item Creating...");

                $("#createNewRedeemResult").empty();

                $.ajax(
                    {
                        url: "create-redeem-item",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $('.createNewRedeemButton').prop('disabled', false);
                                $('.createNewRedeemButton').html("Create This Item");
                                if (data == 0) {
                                    $("#createNewRedeemResult").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data == 1) {
                                    $("#createNewRedeemResult").html("<div class='alert alert-success'><strong>Successful!</strong> Item successfully created.</div>");
                                    $.ajax(
                                        {
                                            url: "get-redeem-items",
                                            type: "GET",
                                            contentType: false,
                                            cache: false,
                                            processData: false,
                                            success: function (data) {
                                                if (data == 0) {
                                                    $(".redeemItems").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                                } else if (data == 2) {
                                                    $(".redeemItems").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Error:</strong> No items available in the system.</div>");
                                                } else {
                                                    $(".redeemItems").html(data);
                                                }
                                            }
                                        });
                                    $("#createNewRedeemForm").trigger("reset");
                                }
                                if (data == 2) {
                                    $("#createNewRedeemResult").html("<div class='alert alert-danger'><strong>Error:</strong> Please fill in the form completely.</div>");
                                }
                                if (data == 3) {
                                    $("#createNewRedeemResult").html("<div class='alert alert-danger'><strong>Error:</strong> The item name can have a minimum of 3 characters and a maximum of 64 characters.</div>");
                                }
                                if (data == 4) {
                                    $("#createNewRedeemResult").html("<div class='alert alert-danger'><strong>Error:</strong> Item score can be between 1 and 1000.</div>");
                                }
                                if (data == 5) {
                                    $("#createNewRedeemResult").html("<div class='alert alert-danger'><strong>Error:</strong> The item image can only be in jpeg, png and jpg format.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
            $('body').on('submit', '#editRedeemItemForm', function (e) {
                e.preventDefault();

                $('.editRedeemItemButton').prop('disabled', true);
                $('.editRedeemItemButton').html("Item Editing...");

                $("#editRedeemItemResult").empty();

                var redeemItem = $('.editRedeemItemButton').data("redeem-item-id");

                $.ajax(
                    {
                        url: "edit-redeem-item?id=" + redeemItem,
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $('.editRedeemItemButton').prop('disabled', false);
                                $('.editRedeemItemButton').html("Edit This Item");
                                if (data == 0) {
                                    $("#editRedeemItemResult").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data == 1) {
                                    $.ajax(
                                        {
                                            url: "get-redeem-items",
                                            type: "GET",
                                            contentType: false,
                                            cache: false,
                                            processData: false,
                                            success: function (data) {
                                                if (data == 0) {
                                                    $(".redeemItems").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                                } else if (data == 2) {
                                                    $(".redeemItems").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Error:</strong> No items available in the system.</div>");
                                                } else {
                                                    $(".redeemItems").html(data);
                                                }
                                            }
                                        });
                                    $.ajax(
                                        {
                                            url: "edit-redeem-item-modal?id=" + redeemItem,
                                            type: "GET",
                                            contentType: false,
                                            cache: false,
                                            processData: false,
                                            success: function (data) {
                                                if (data == 0) {
                                                    $(".editRedeemContent").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                                } else {
                                                    $(".editRedeemContent").html(data);
                                                    $("#editRedeemItemResult").html("<div class='alert alert-success'><strong>Successful!</strong> Item successfully edited.</div>");
                                                }
                                            }
                                        });
                                }
                                if (data == 2) {
                                    $("#editRedeemItemResult").html("<div class='alert alert-danger'><strong>Error:</strong> Please fill in the form completely.</div>");
                                }
                                if (data == 3) {
                                    $("#editRedeemItemResult").html("<div class='alert alert-danger'><strong>Error:</strong> The item name can have a minimum of 3 characters and a maximum of 64 characters.</div>");
                                }
                                if (data == 4) {
                                    $("#editRedeemItemResult").html("<div class='alert alert-danger'><strong>Error:</strong> Item score can be between 1 and 1000.</div>");
                                }
                                if (data == 5) {
                                    $("#editRedeemItemResult").html("<div class='alert alert-danger'><strong>Error:</strong> The item image can only be in jpeg, png and jpg format.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
            $('body').on('click', '.editRedeemItemModalButton', function (e) {
                e.preventDefault();
                $('#editRedeemModal').modal('toggle');
                var redeemItem = $(this).data("redeem-item-id");
                $.ajax(
                    {
                        url: "edit-redeem-item-modal?id=" + redeemItem,
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            if (data == 0) {
                                $(".editRedeemContent").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                            } else {
                                $(".editRedeemContent").html(data);
                            }
                        }
                    });
            });
            $('body').on('click', '.deleteRedeemItemButton', function (e) {
                e.preventDefault();
                var redeemItem = $(this).data("redeem-item-id");
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
                                    url: 'delete-redeem-item',
                                    data: 'id=' + redeemItem,
                                    success: function (data) {
                                        if (data == 1) {
                                            $('#editRedeemModal').modal('toggle');
                                            swal(
                                                {
                                                    title: "Deleted!",
                                                    text: "Redeem item has been deleted.",
                                                    type: "success",
                                                    confirmButtonText: "OK",
                                                    closeOnConfirm: true
                                                });
                                            $.ajax(
                                                {
                                                    url: "get-redeem-items",
                                                    type: "GET",
                                                    contentType: false,
                                                    cache: false,
                                                    processData: false,
                                                    success: function (data) {
                                                        if (data == 0) {
                                                            $(".redeemItems").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                                        } else if (data == 2) {
                                                            $(".redeemItems").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Error:</strong> No items available in the system.</div>");
                                                        } else {
                                                            $(".redeemItems").html(data);
                                                        }
                                                    }
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
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "class") {
    $sinifid = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($sinifid === false) {
        echo 404;
        exit();
    }
    if ($uyerol == "teacher") {
        $sorgusinifid = $DB_con->prepare("SELECT id,color FROM classes WHERE FIND_IN_SET(:uyeid, teachers) AND school = :school AND id = :id AND status = :status");
        $sorgusinifid->execute(array(":uyeid" => $uyevtid, ":school" => $uyeokul, ":id" => $sinifid , ":status" => 1));
        if ($sorgusinifid->rowCount() != 1) {
            echo 404;
            exit();
        }
        $yazsinifrenk = $sorgusinifid->fetch(PDO::FETCH_ASSOC);
        ?>
        <section class="content">
            <div class="container-fluid">
                <div class="row clearfix m-b-40">
                    <div class="form-group" style="display:none;">
                        <select class="form-control" id="siralama_ogrenci" name="siralama_ogrenci">
                            <option value="0">By name (A-Z)</option>
                            <option value="1">By name (Z-A)</option>
                            <option value="2">By surname (A-Z)</option>
                            <option value="3">By surname (Z-A)</option>
                            <option value="4">Total behavior points (Highest first)</option>
                            <option value="5">Total behavior points (Lowest first)</option>
                            <option value="6">By register date (Newest first)</option>
                            <option value="7">By register date (Oldest first)</option>
                        </select>
                    </div>
                    <ul class="nav nav-tabs class-tab tab-col-<?=$sinifrenk?> hidden-md hidden-lg" role="tablist">
                        <li role="presentation" class="active"><a href="#students" data-toggle="tab" aria-expanded="true">STUDENTS</a></li>
                        <li role="presentation" class=""><a href="#groups" data-toggle="tab" aria-expanded="false">GROUPS</a></li>
                    </ul>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade active in" id="students">
                            <div class="get-students"></div>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="groups">
                            <button class="btn btn-default btn-block btn-lg waves-effect hidden-md hidden-lg m-b-10" id="createGroup" data-toggle="modal" data-target="#createGroupModal" data-class="<?=$sinifid?>" ><i class="material-icons">add</i><span>Create Group</span></button>
                            <button type="button" class="btn btn-default btn-circle-lg waves-effect waves-circle waves-float visible-lg fixedCreateGroupButton" id="createGroup" data-toggle="modal" data-target="#createGroupModal" data-class="<?=$sinifid?>"><i class="material-icons">add</i></button>
                            <div class="get-groups"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="modal fade in" id="modal-student" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-student-content">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="modal-send-mail-to-parent" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-send-mail-to-parent-content">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="modal-send-message" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-send-message-content">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="modal-edit-student" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-edit-student-content">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="modal-edit-behavior" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-edit-behavior-content">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="createGroupModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content createGroupModalContent">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="editGroupModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content editGroupModalContent">

                </div>
            </div>
        </div>
        <div class="modal fade in" id="modal-students" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-students-content">
                    <div class="modal-header">
                        <h4 class="modal-title"></h4>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs tab-col-orange" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#home_with_icon_title2" data-toggle="tab" aria-expanded="true"
                                   class="font-bold col-green">
                                    <i class="material-icons">thumb_up</i> Positive
                                </a>
                            </li>
                            <li role="presentation" class="">
                                <a href="#profile_with_icon_title2" data-toggle="tab" aria-expanded="false"
                                   class="font-bold col-red">
                                    <i class="material-icons">thumb_down</i> Negative
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade active in" id="home_with_icon_title2">
                                <div class="row row-no-gutters">
                                    <?php
                                    $sorgufeed = $DB_con->prepare("SELECT id,name,point FROM feedbacks WHERE type = :type AND (user = :teacher OR FIND_IN_SET(user, (SELECT GROUP_CONCAT(id) FROM users WHERE role = :roleadmin AND schools = :schools)))");
                                    $sorgufeed->execute(array(":type" => "1",":teacher"=>$uyevtid,":roleadmin"=>"admin",":schools"=>$uyeokul));
                                    if ($sorgufeed->rowCount() > 0) {
                                        while ($yazfeed = $sorgufeed->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 m-b-5">
                                                <button class="btn btn-success btn-sm waves-effect waves-light btn-text-ellipsis give-behavior-multiple"
                                                        type="button" data-class="<?= $sinifid ?>"
                                                        data-behavior="<?= $yazfeed["id"] ?>">
                                                    <i class="material-icons">thumb_up</i>
                                                    <span><?= $yazfeed["name"] ?></span>
                                                    <span class="badge">+<?= $yazfeed["point"] ?></span>
                                                </button>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="alert alert-warning">Henüz sisteme eklenen olumlu davranış notu
                                                bulunmamakta.
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="form-group m-t-15">
                                    <label for="pointLocation">Point Location:</label>
                                    <select class="form-control show-tick" id="point_location_multiple1">
                                        <?php
                                        $getPointLocationsQuery = $DB_con->prepare("SELECT * FROM point_locations WHERE school = :school");
                                        $getPointLocationsQuery->execute(array(':school'=>$uyeokul));
                                        while($fetchPointLocations = $getPointLocationsQuery->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                            <option value="<?=$fetchPointLocations['id']?>" <?php if($fetchPointLocations['id'] == 1) { echo 'selected'; } ?>><?=$fetchPointLocations['name']?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group m-t-15">
                                    <label for="feedback_description">Behavior Description:</label>
                                    <div class="form-line">
                                        <textarea class="form-control no-resize" id="feedback_description_multiple1"
                                                  name="feedback_description_multiple1" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="profile_with_icon_title2">
                                <div class="row row-no-gutters">
                                    <?php
                                    $sorgufeed = $DB_con->prepare("SELECT id,name,point FROM feedbacks WHERE type = :type AND (user = :teacher OR FIND_IN_SET(user, (SELECT GROUP_CONCAT(id) FROM users WHERE role = :roleadmin AND schools = :schools)))");
                                    $sorgufeed->execute(array(":type" => "2",":teacher"=>$uyevtid,":roleadmin"=>"admin",":schools"=>$uyeokul));
                                    if ($sorgufeed->rowCount() > 0) {
                                        while ($yazfeed = $sorgufeed->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 m-b-5">
                                                <button class="btn btn-danger btn-sm waves-effect waves-light btn-text-ellipsis give-behavior-multiple"
                                                        type="button" data-class="<?= $sinifid ?>"
                                                        data-behavior="<?= $yazfeed["id"] ?>">
                                                    <i class="material-icons">thumb_down</i>
                                                    <span><?= $yazfeed["name"] ?></span>
                                                    <span class="badge"><?= $yazfeed["point"] ?></span>
                                                </button>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="alert alert-warning">Henüz sisteme eklenen olumsuz davranış notu
                                                bulunmamakta.
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="form-group m-t-15">
                                    <label for="pointLocation">Point Location:</label>
                                    <select class="form-control show-tick" id="point_location_multiple2">
                                        <?php
                                        $getPointLocationsQuery = $DB_con->prepare("SELECT * FROM point_locations WHERE school = :school");
                                        $getPointLocationsQuery->execute(array(':school'=>$uyeokul));
                                        while($fetchPointLocations = $getPointLocationsQuery->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                            <option value="<?=$fetchPointLocations['id']?>" <?php if($fetchPointLocations['id'] == 1) { echo 'selected'; } ?>><?=$fetchPointLocations['name']?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group m-t-15">
                                    <label for="feedback_description">Behavior Description:</label>
                                    <div class="form-line">
                                        <textarea class="form-control no-resize" id="feedback_description_multiple2"
                                                  name="feedback_description_multiple2" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade in" id="modal-add-behavior" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Behavior</h4>
                    </div>
                    <div class="modal-body">
                        <form id="Add-Behavior-Form">
                            <div class="form-group">
                                <label for="name">Behavior Name:</label>
                                <div class="form-line">
                                    <input class="form-control" name="name" id="name" placeholder="Behavior name..."
                                           type="text">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="type">Behavior Type:</label>
                                <select class="form-control" name="type" id="type">
                                    <option value="0">Choose...</option>
                                    <option value="1">Positive</option>
                                    <option value="2">Negative</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="point">Behavior Point(only number):</label>
                                <div class="form-line">
                                    <input class="form-control" name="point" id="point" placeholder="Behavior point..."
                                           type="text">
                                </div>
                            </div>
                            <input type="hidden" name="hidden_student_id" id="hidden_student_id">
                            <input type="hidden" name="hidden_class_id" id="hidden_class_id">
                            <div id="Add-Behavior-Result"></div>
                            <div class="form-group">
                                <button type="submit"
                                        class="btn btn-primary btn-block btn-lg waves-effect Add-Behavior-Button">Add
                                    Behavior
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <nav class="navbar-bottom-fixed">
            <div class="container">
                <div class="row">
                    <input type="hidden" id="checked_students" name="checked_students" data-class-id="<?= $sinifid ?>">
                    <input type="hidden" id="checked_students2" name="checked_students2" data-class-id="<?= $sinifid ?>">
                    <div class="btn-group btn-group-justified bottom-button-group" role="group"
                         aria-label="Large button group">
                        <a href="javascript:void(0);" role="button"
                           class="btn btn-lg btn-default waves-effect btn-text-ellipsis select-multiple-button"><i
                                    class="material-icons col-<?=$yazsinifrenk["color"]?>">check_box</i><span>Select Multiple</span></a><a
                                href="javascript:void(0);" role="button"
                                class="btn btn-lg btn-default waves-effect btn-text-ellipsis get-random"><i
                                    class="material-icons col-<?=$yazsinifrenk["color"]?>">shuffle</i><span>Get Random</span></a>
                    </div>
                </div>
            </div>
        </nav>
        <script src="plugins/jquery/jquery.min.js"></script>
        <script src="plugins/jquery/jquery-ui.min.js"></script>
        <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
        <script src="plugins/node-waves/waves.min.js"></script>
<!--        <script src="plugins/sweetalert/sweetalert.min.js"></script>-->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
        <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
        <script src="plugins/jquery-sparkline/jquery.sparkline.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/promise-polyfill@7.1.0/dist/promise.min.js"></script>
        <script src="plugins/jquery-datatable/jquery.dataTables.min.js"></script>
        <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.min.js"></script>
        <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.responsive.min.js"></script>
        <script src="plugins/jquery-datatable/skin/bootstrap/js/responsive.bootstrap.min.js"></script>
        <script src="//cdn.ckeditor.com/4.13.0/basic/ckeditor.js"></script>
        <script src="plugins/bootstrap-select/js/bootstrap-select.min.js"></script>
        <script src="js/main.js"></script>
        <script type="text/javascript">
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
                $('body').on('click', '.select-multiple-button', function (e) {
                    $('#modal-students').attr('mode', '');
                    $('.class-tab a[href="#students"]').tab('show');
                    $('.class-tab a[href="#groups"]').addClass('disabledTab');
                    $(".bottom-button-group").addClass("select-multiple-active");
                    $(".ogrenci-puanla").attr("data-toggle", "");
                    $(".bottom-button-group").html('<a href="javascript:void(0);" role="button" class="btn btn-lg btn-default waves-effect btn-text-ellipsis cancel-button"><i class="material-icons col-red">cancel</i><span>Cancel</span></a><a href="javascript:void(0);" role="button" class="btn btn-lg btn-default waves-effect btn-text-ellipsis count-of-selecteds give-points-multiple"><i class="material-icons col-green">note_add</i><span>Give Points <b class="count-selecteds">(0)</b></span></a><a href="javascript:void(0);" role="button" class="btn btn-lg btn-default waves-effect btn-text-ellipsis select-all-button"><i class="material-icons col-<?=$yazsinifrenk["color"]?>">check</i><span>Select All</span></a>');
                });
                $('body').on('click', '.cancel-button', function (e) {
                    $(".bottom-button-group").removeClass("select-multiple-active");
                    $(".ogrenci-puanla").attr("data-toggle", "modal");
                    $(".ogrenci-puanla").removeClass("checked");
                    $(".ogrenci-puanla").children("div.info-box").css({"background-color": "#fff"});
                    $("input#checked_students").val("");
                    $(".bottom-button-group").html('<a href="javascript:void(0);" role="button" class="btn btn-lg btn-default waves-effect btn-text-ellipsis select-multiple-button"><i class="material-icons col-<?=$yazsinifrenk["color"]?>">check_box</i><span>Select Multiple</span></a><a href="javascript:void(0);" role="button" class="btn btn-lg btn-default waves-effect btn-text-ellipsis get-random"><i class="material-icons col-<?=$yazsinifrenk["color"]?>">shuffle</i><span>Get Random</span></a>');
                    $(".count-of-selecteds b.count-selecteds").text("");
                    $('.class-tab a[href="#groups"]').removeClass('disabledTab');
                });
                $('body').on('click', '.select-all-button', function (e) {
                    $('.class-tab a[href="#students"]').tab('show');
                    $(".ogrenci-puanla").children("div.info-box").css({"background-color": "rgba(76, 175, 80, 0.30)"});
                    $(".ogrenci-puanla").addClass("checked");
                    var checkedstudents = $(".ogrenci-puanla.checked").map(function () {
                        return $(this).attr("id");
                    }).get().join(',');
                    $("input#checked_students").val(checkedstudents);
                    $(".count-of-selecteds b.count-selecteds").text("(" + $(".ogrenci-puanla.checked").length + ")");
                });
                $('body').on('click', '.class-tab a[href="#groups"]', function (e) {
                    if($(this).hasClass('disabledTab')) {
                        e.preventDefault();
                        return false;
                    }
                });
                $('body').on('click', '.group-give-points', function (e) {
                    if ($(".bottom-button-group").hasClass('select-multiple-active')) {
                        $('.cancel-button').click();
                    }
                    var thisGroupId = $(this).data('group-id');
                    var thisGroupName  = $(this).data('group-name');
                    var $this = $('ul[data-group-id="'+thisGroupId+'"] > li > a');
                    if($this.length == 0){
                        return;
                    }
                    $this.children("div").css({"background-color": "rgba(76, 175, 80, 0.30)"});
                    if ($this.children('div').hasClass('media')) {
                        $this.find("img.studentAvatar").css({"border": "2px solid #2b982b","padding": "2px"});
                    }
                    $this.addClass("checked");
                    var checkedstudents = $('ul[data-group-id="'+thisGroupId+'"] > li > a.checked').map(function () {
                        return $(this).attr("id");
                    }).get().join(',');
                    $("input#checked_students2").val(checkedstudents);
                    $('#modal-students').modal('show');
                    $('#modal-students').attr('mode', 'group');
                    $('#modal-students .modal-title').text("Give behavior points to group of " + thisGroupName + "'s students:");
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
                $('body').on('click', '.give-points-multiple', function (e) {
                    if ($(".give-points-multiple b.count-selecteds").text() !== "(0)") {
                        $('#modal-students').modal('show');
                        $('#modal-students .modal-title').text("Give behavior points to selected " + $(".give-points-multiple b.count-selecteds").text() + " students:");
                    } else {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'bottom',
                            showConfirmButton: false,
                            timer:3000,
                            timerProgressBar: true,
                            showClass: {
                                popup: 'animated bounceInDown'
                            },
                            onOpen: function onOpen(toast) {
                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                            }
                        });
                        Toast.fire({
                            icon: 'error',
                            title: "Please select at least one student.",
                        });
                    }
                });
                $.ajaxSetup({
                    headers: {'sbmtoken': $('meta[name="sbmtoken"]').attr('content')}
                });
                $.ajax(
                    {
                        url: "get-students?id=<?=$sinifid?>",
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            if (data == 0) {
                                $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                            } else if (data == 2) {
                                $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait öğrenci bulunamadı.</div>");
                            } else {
                                $(".get-students").html(data);
                                $.each($('.chart.chart-pie'), function (i, key) {
                                    $(key).sparkline(undefined, {
                                        disableHiddenCheck: true,
                                        type: 'pie',
                                        height: '50px',
                                        sliceColors: ['#4CAF50', '#F44336']
                                    });
                                });
                            }
                        }
                    });
                $.ajax(
                    {
                        url: "get-groups?id=<?=$sinifid?>",
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            if (data == 0) {
                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div></div>");
                            } else if (data == 2) {
                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait grup bulunamadı.</div></div></div>");
                            } else {
                                $(".get-groups").html(data);
                            }
                        }
                    });
                $(document).ajaxSuccess(function() {
                    $('ul.sortable[id^="sort"]').sortable({
                        connectWith: ".sortable",
                        receive: function (e, ui) {
                            var groupId = $(ui.item).parent(".sortable").data("group-id");
                            var studentId = $(ui.item).data("student-id");
                            $.ajax({
                                url: "change-student-group?group=" + groupId + "&student=" + studentId + "&class=<?=$sinifid?>",
                                type: "GET",
                                contentType: false,
                                cache: false,
                                processData: false,
                                success: function (data) {
                                    if (data == 0) {
                                        Toast.fire({
                                            icon: 'error',
                                            title: "A technical error has occurred. Please try again.",
                                        });
                                    } else if (data == 1) {
                                        $.ajax(
                                            {
                                                url: "get-groups?id=<?=$sinifid?>",
                                                type: "GET",
                                                contentType: false,
                                                cache: false,
                                                processData: false,
                                                success: function (data) {
                                                    if (data == 0) {
                                                        $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div></div>");
                                                    } else if (data == 2) {
                                                        $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait grup bulunamadı.</div></div></div>");
                                                    } else {
                                                        $(".get-groups").html(data);
                                                    }
                                                }
                                            });
                                    }
                                }
                            });
                        }
                    }).disableSelection();
                });
                $('body').on('click', '.sortTriggerButton', function (e) {
                    var forWhat = $(this).attr('id');
                    var settedId = null;
                    settedId = forWhat == "name-sort" ? 0 : (forWhat == "lastname-sort" ? 2 : (forWhat == "point-sort" ? 4 : null));
                    if(settedId != null && (settedId == 0 || settedId == 2 || settedId == 4)) {
                        $('select#siralama_ogrenci').val(settedId);
                        $('select#siralama_ogrenci').trigger("change");
                    } else {
                        return false;
                    }
                });
                $('body').on('change', 'select#siralama_ogrenci', function (e) {
                    $.ajax(
                        {
                            url: "get-students?id=<?=$sinifid?>&siralama=" + $("select#siralama_ogrenci").val(),
                            type: "GET",
                            contentType: false,
                            cache: false,
                            processData: false,
                            success: function (data) {
                                if (data == 0) {
                                    $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                } else if (data == 2) {
                                    $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait öğrenci bulunamadı.</div>");
                                } else {
                                    $(".get-students").html(data);
                                    $.each($('.chart.chart-pie'), function (i, key) {
                                        $(key).sparkline(undefined, {
                                            disableHiddenCheck: true,
                                            type: 'pie',
                                            height: '50px',
                                            sliceColors: ['#4CAF50', '#F44336']
                                        });
                                    });
                                }
                            }
                        });
                });
                $('#modal-add-behavior').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget);
                    var ogrenci = button.data("student");
                    var sinif = button.data("class");
                    var modal = $(this);
                    modal.find('#hidden_student_id').val(ogrenci);
                    modal.find('#hidden_class_id').val(sinif);
                });
                $('body').on('click', '#selectStudent', function (e) {
                    if ($(this).hasClass("selected")) {
                        $(this).find("div.panel-heading").css({"background-color": "#fff"});
                        $(this).find("img.studentAvatar").css({"border": "none","padding": "0"});
                        $(this).removeClass("selected");
                        var selectedStudents = $("#selectStudent.selected").map(function () {
                            return $(this).data("student");
                        }).get().join(',');
                        $("input#selected_students").val(selectedStudents);
                    } else {
                        $(this).find("div.panel-heading").css({"background-color": "rgba(76, 175, 80, 0.30)"});
                        $(this).find("img.studentAvatar").css({"border": "2px solid #2b982b","padding": "2px"});
                        $(this).addClass("selected");
                        var selectedStudents = $("#selectStudent.selected").map(function () {
                            return $(this).data("student");
                        }).get().join(',');
                        $("input#selected_students").val(selectedStudents);
                    }
                });
                $('body').on('click', '#selectStudent2', function (e) {
                    if ($(this).hasClass("selected")) {
                        $(this).find("div.panel-heading").css({"background-color": "#fff"});
                        $(this).find("img.studentAvatar").css({"border": "none","padding": "0"});
                        $(this).removeClass("selected");
                        var selectedStudents = $("#selectStudent2.selected").map(function () {
                            return $(this).data("student");
                        }).get().join(',');
                        $("input#selected_students2").val(selectedStudents);
                    } else {
                        $(this).find("div.panel-heading").css({"background-color": "rgba(244, 67, 54, 0.30)"});
                        $(this).find("img.studentAvatar").css({"border": "2px solid #F44336","padding": "2px"});
                        $(this).addClass("selected");
                        var selectedStudents = $("#selectStudent2.selected").map(function () {
                            return $(this).data("student");
                        }).get().join(',');
                        $("input#selected_students2").val(selectedStudents);
                    }
                });
                $('body').on('click', '.ogrenci-puanla', function (e) {
                    if ($(".bottom-button-group").hasClass("select-multiple-active")) {
                        if ($(this).hasClass("checked")) {
                            $(this).children("div").css({"background-color": "#fff"});
                            if ($(this).children('div').hasClass('media')) {
                                $(this).find("img.studentAvatar").css({"border": "none","padding": "0"});
                            }
                            $(this).removeClass("checked");
                            var checkedstudents = $(".ogrenci-puanla.checked").map(function () {
                                return $(this).attr("id");
                            }).get().join(',');
                            $("input#checked_students").val(checkedstudents);
                            $(".count-of-selecteds b.count-selecteds").text("(" + $(".ogrenci-puanla.checked").length + ")");
                        } else {
                            $(this).children("div").css({"background-color": "rgba(76, 175, 80, 0.30)"});
                            if ($(this).children('div').hasClass('media')) {
                                $(this).find("img.studentAvatar").css({"border": "2px solid #2b982b","padding": "2px"});
                            }
                            $(this).addClass("checked");
                            var checkedstudents = $(".ogrenci-puanla.checked").map(function () {
                                return $(this).attr("id");
                            }).get().join(',');
                            $("input#checked_students").val(checkedstudents);
                            $(".count-of-selecteds b.count-selecteds").text("(" + $(".ogrenci-puanla.checked").length + ")");
                        }
                        return;
                    }
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
                                    $("select.show-tick").selectpicker('refresh');
                                    $('.report-behavior-list').DataTable({responsive: true});
                                    $('.report-redeem-list').DataTable({responsive: true});
                                }
                            }
                        });
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
                $('body').on('submit', '#createGroupForm', function (e) {
                    e.preventDefault();
                    var classId = $(this).data("class");
                    $('.createGroupButton').prop('disabled', true);
                    $('.createGroupButton').html("Group Creating...");

                    $("#createGroupResult").empty();
                    $.ajax(
                        {
                            url: "create-group-a",
                            type: "POST",
                            data: new FormData(this),
                            contentType: false,
                            cache: false,
                            processData: false,
                            success: function (data) {
                                setTimeout(function () {
                                    $('.createGroupButton').prop('disabled', false);
                                    $('.createGroupButton').html("Create This Group");
                                    if (data == 0) {
                                        $("#createGroupResult").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                    }
                                    if (data == 1) {
                                        $.ajax(
                                            {
                                                url: "create-group-modal?id=" + classId,
                                                type: "GET",
                                                contentType: false,
                                                cache: false,
                                                processData: false,
                                                success: function (data) {
                                                    if (data == 0) {
                                                        $(".createGroupModalContent").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                                    } else {
                                                        $(".createGroupModalContent").html(data);
                                                        $("#createGroupResult").html("<div class='alert alert-success'><strong>Successful!</strong> Group successfully created!</div>");
                                                    }
                                                }
                                            });
                                        $.ajax(
                                            {
                                                url: "get-groups?id=<?=$sinifid?>",
                                                type: "GET",
                                                contentType: false,
                                                cache: false,
                                                processData: false,
                                                success: function (data) {
                                                    if (data == 0) {
                                                        $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div></div>");
                                                    } else if (data == 2) {
                                                        $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait grup bulunamadı.</div></div></div>");
                                                    } else {
                                                        $(".get-groups").html(data);
                                                    }
                                                }
                                            });
                                    }
                                    if (data == 2) {
                                        $("#createGroupResult").html("<div class='alert alert-danger'><strong>Error:</strong> Type a name for the group.</div>");
                                    }
                                    if (data == 3) {
                                        $("#createGroupResult").html("<div class='alert alert-danger'><strong>Error:</strong> The group name can have a minimum of 3 characters and a maximum of 64 characters.</div>");
                                    }
                                }, 1000);
                            }
                        });
                });
                $('body').on('submit', '#editGroupForm', function (e) {
                    e.preventDefault();
                    var classId = $(this).data("class");
                    var groupId = $(this).data("group");
                    $('.editGroupButton').prop('disabled', true);
                    $('.editGroupButton').html("Group Editing...");

                    $("#editGroupResult").empty();
                    $.ajax(
                        {
                            url: "edit-group-a",
                            type: "POST",
                            data: new FormData(this),
                            contentType: false,
                            cache: false,
                            processData: false,
                            success: function (data) {
                                setTimeout(function () {
                                    $('.editGroupButton').prop('disabled', false);
                                    $('.editGroupButton').html("Create This Group");
                                    if (data == 0) {
                                        $("#editGroupResult").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                    }
                                    if (data == 1) {
                                        $.ajax(
                                            {
                                                url: "edit-group-modal?id=" + classId + "&group=" + groupId,
                                                type: "GET",
                                                contentType: false,
                                                cache: false,
                                                processData: false,
                                                success: function (data) {
                                                    if (data == 0) {
                                                        $(".editGroupModalContent").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                                    } else {
                                                        $(".editGroupModalContent").html(data);
                                                        $("#editGroupResult").html("<div class='alert alert-success'><strong>Successful!</strong> Group successfully edited!</div>");
                                                    }
                                                }
                                            });
                                        $.ajax(
                                            {
                                                url: "get-groups?id=<?=$sinifid?>",
                                                type: "GET",
                                                contentType: false,
                                                cache: false,
                                                processData: false,
                                                success: function (data) {
                                                    if (data == 0) {
                                                        $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div></div>");
                                                    } else if (data == 2) {
                                                        $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait grup bulunamadı.</div></div></div>");
                                                    } else {
                                                        $(".get-groups").html(data);
                                                    }
                                                }
                                            });
                                    }
                                    if (data == 2) {
                                        $("#editGroupResult").html("<div class='alert alert-danger'><strong>Error:</strong> Type a name for the group.</div>");
                                    }
                                    if (data == 3) {
                                        $("#editGroupResult").html("<div class='alert alert-danger'><strong>Error:</strong> The group name can have a minimum of 3 characters and a maximum of 64 characters.</div>");
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
                $('body').on('click', '#createGroup', function (e) {
                    e.preventDefault();
                    var classId = $(this).data("class");
                    $.ajax(
                        {
                            url: "create-group-modal?id=" + classId,
                            type: "GET",
                            contentType: false,
                            cache: false,
                            processData: false,
                            success: function (data) {
                                if (data == 0) {
                                    $(".createGroupModalContent").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                } else {
                                    $(".createGroupModalContent").html(data);
                                }
                            }
                        });
                });
                $('body').on('click', '#editGroup', function (e) {
                    e.preventDefault();
                    var classId = $(this).data("class");
                    var groupId = $(this).data("group");
                    $.ajax(
                        {
                            url: "edit-group-modal?id=" + classId + "&group=" + groupId,
                            type: "GET",
                            contentType: false,
                            cache: false,
                            processData: false,
                            success: function (data) {
                                if (data == 0) {
                                    $(".editGroupModalContent").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                } else {
                                    $(".editGroupModalContent").html(data);
                                }
                            }
                        });
                });
                $('body').on('click', '#deleteGroupButton', function (e) {
                    e.preventDefault();
                    var classId = $(this).data("class");
                    var groupId = $(this).data("group");
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
                                        url: 'delete-group',
                                        data: 'class=' + classId + '&group=' + groupId,
                                        success: function (data) {
                                            if (data == 1) {
                                                $('#editGroupModal').modal('toggle');
                                                swal(
                                                    {
                                                        title: "Deleted!",
                                                        text: "Group has been deleted.",
                                                        type: "success",
                                                        confirmButtonText: "OK",
                                                        closeOnConfirm: true
                                                    });
                                                $.ajax(
                                                    {
                                                        url: "get-groups?id=<?=$sinifid?>",
                                                        type: "GET",
                                                        contentType: false,
                                                        cache: false,
                                                        processData: false,
                                                        success: function (data) {
                                                            if (data == 0) {
                                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div></div>");
                                                            } else if (data == 2) {
                                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait grup bulunamadı.</div></div></div>");
                                                            } else {
                                                                $(".get-groups").html(data);
                                                            }
                                                        }
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
                $('#createGroupModal').on('shown.bs.modal', function (e) {
                    $('body').addClass('modal-open');
                    $('body').css("padding-right", "");
                    $('#createGroupModal').css("padding-left", "");
                });
                $('#createGroupModal').on('hidden.bs.modal', function (e) {
                    $('body').css("padding-right", "");
                    $('body').addClass('modal-open');
                });
                $('#editGroupModal').on('shown.bs.modal', function (e) {
                    $('body').addClass('modal-open');
                    $('body').css("padding-right", "");
                    $('#editGroupModal').css("padding-left", "");
                });
                $('#editGroupModal').on('hidden.bs.modal', function (e) {
                    $('body').css("padding-right", "");
                    $('body').addClass('modal-open');
                });
                $('#modal-students').on('shown.bs.modal', function (e) {
                    $('body').addClass('modal-open');
                    $('body').css("padding-right", "");
                    $('#modal-students').css("padding-left", "");
                });
                $('#modal-students').on('hidden.bs.modal', function (e) {
                    $('body').css("padding-right", "");
                    $('body').addClass('modal-open');
                    if ($('#modal-students').attr('mode') == 'group') {
                        var $this = $('ul.sortable > li > a');
                        if ($this.hasClass("checked")) {
                            $this.children("div").css({"background-color": "#fff"});
                            if ($this.children('div').hasClass('media')) {
                                $this.find("img.studentAvatar").css({"border": "none", "padding": "0"});
                            }
                            $('#checked_students2').val('');
                            $this.removeClass("checked");
                            $('#modal-students').attr('mode', '');
                        }
                    }
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
                                                $.ajax(
                                                    {
                                                        url: "get-students?id=<?=$sinifid?>&siralama=" + $("select#siralama_ogrenci").val(),
                                                        type: "GET",
                                                        contentType: false,
                                                        cache: false,
                                                        processData: false,
                                                        success: function (data) {
                                                            if (data == 0) {
                                                                $(".get-students").html("<div class='col-lg-12'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div>");
                                                            } else if (data == 2) {
                                                                $(".get-students").html("<div class='col-lg-12'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait öğrenci bulunamadı.</div></div>");
                                                            } else {
                                                                $(".get-students").html(data);
                                                                $.each($('.chart.chart-pie'), function (i, key) {
                                                                    $(key).sparkline(undefined, {
                                                                        disableHiddenCheck: true,
                                                                        type: 'pie',
                                                                        height: '50px',
                                                                        sliceColors: ['#4CAF50', '#F44336']
                                                                    });
                                                                });
                                                            }
                                                        }
                                                    });
                                                $.ajax(
                                                    {
                                                        url: "get-groups?id=<?=$sinifid?>",
                                                        type: "GET",
                                                        contentType: false,
                                                        cache: false,
                                                        processData: false,
                                                        success: function (data) {
                                                            if (data == 0) {
                                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div></div>");
                                                            } else if (data == 2) {
                                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait grup bulunamadı.</div></div></div>");
                                                            } else {
                                                                $(".get-groups").html(data);
                                                            }
                                                        }
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
                                                        url: "get-students?id=<?=$sinifid?>&siralama=" + $("select#siralama_ogrenci").val(),
                                                        type: "GET",
                                                        contentType: false,
                                                        cache: false,
                                                        processData: false,
                                                        success: function (data) {
                                                            if (data == 0) {
                                                                $(".get-students").html("<div class='col-lg-12'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div>");
                                                            } else if (data == 2) {
                                                                $(".get-students").html("<div class='col-lg-12'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait öğrenci bulunamadı.</div></div>");
                                                            } else {
                                                                $(".get-students").html(data);
                                                                $.each($('.chart.chart-pie'), function (i, key) {
                                                                    $(key).sparkline(undefined, {
                                                                        disableHiddenCheck: true,
                                                                        type: 'pie',
                                                                        height: '50px',
                                                                        sliceColors: ['#4CAF50', '#F44336']
                                                                    });
                                                                });
                                                            }
                                                        }
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
                                                $.ajax(
                                                    {
                                                        url: "get-groups?id=<?=$sinifid?>",
                                                        type: "GET",
                                                        contentType: false,
                                                        cache: false,
                                                        processData: false,
                                                        success: function (data) {
                                                            if (data == 0) {
                                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div></div>");
                                                            } else if (data == 2) {
                                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait grup bulunamadı.</div></div></div>");
                                                            } else {
                                                                $(".get-groups").html(data);
                                                            }
                                                        }
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
                                        $.ajax(
                                            {
                                                url: "get-students?id=<?=$sinifid?>&siralama=" + $("select#siralama_ogrenci").val(),
                                                type: "GET",
                                                contentType: false,
                                                cache: false,
                                                processData: false,
                                                success: function (data) {
                                                    if (data == 0) {
                                                        $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                                    } else if (data == 2) {
                                                        $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait öğrenci bulunamadı.</div>");
                                                    } else {
                                                        $(".get-students").html(data);
                                                        $.each($('.chart.chart-pie'), function (i, key) {
                                                            $(key).sparkline(undefined, {
                                                                disableHiddenCheck: true,
                                                                type: 'pie',
                                                                height: '50px',
                                                                sliceColors: ['#4CAF50', '#F44336']
                                                            });
                                                        });
                                                    }
                                                }
                                            });
                                        $.ajax(
                                            {
                                                url: "get-groups?id=<?=$sinifid?>",
                                                type: "GET",
                                                contentType: false,
                                                cache: false,
                                                processData: false,
                                                success: function (data) {
                                                    if (data == 0) {
                                                        $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div></div>");
                                                    } else if (data == 2) {
                                                        $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait grup bulunamadı.</div></div></div>");
                                                    } else {
                                                        $(".get-groups").html(data);
                                                    }
                                                }
                                            });
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
                function shuffle(sourceArray) {
                    for (var i = 0; i < sourceArray.length - 1; i++) {
                        var j = i + Math.floor(Math.random() * (sourceArray.length - i));

                        var temp = sourceArray[j];
                        sourceArray[j] = sourceArray[i];
                        sourceArray[i] = temp;
                    }
                    return sourceArray;
                }

                function rastgeleOgrenciGetir() {
                    var toplam = ogrenciler.length;
                    var ogrencilerKarisik = shuffle(ogrenciler);
                    var rastgeleZaman = Math.floor(Math.random() * (6000 - 3000 + 1) + 3000);
                    var rastgeleSayi = Math.floor(Math.random() * toplam);
                    var baslangicZaman = new Date().getTime();
                    $(".get-random").addClass('disabled');
                    Swal.fire(
                        {
                            position: 'bottom',
                            title: "<h1>" + ogrenciler[0].name + "</h1>",
                            imageUrl: ogrenciler[0].avatar,
                            showClass: {
                                popup: 'randomSwal'
                            },
                            showConfirmButton: false,
                            backdrop: false,
                            allowOutsideClick: false
                        });
                    zamanlayici = setInterval(function () {
                        rastgeleSayi = Math.floor(Math.random() * toplam);
                        if (new Date().getTime() - baslangicZaman > rastgeleZaman) {
                            clearInterval(zamanlayici);
                            $(".swal2-image").attr("src", ogrencilerKarisik[rastgeleSayi].avatar);
                            $(".randomSwal > .swal2-title > h1").text(ogrencilerKarisik[rastgeleSayi].name);
                            randomSound.pause();
                            randomSound.currentTime = 0;
                            Swal.close();
                            Swal.fire(
                                {
                                    position: 'bottom',
                                    title: "<h1>" + ogrencilerKarisik[rastgeleSayi].name + "</h1>",
                                    imageUrl: ogrencilerKarisik[rastgeleSayi].avatar,
                                    showClass: {
                                        popup: 'randomSwal animated bounceInDown'
                                    },
                                    showConfirmButton: false,
                                    timer: 1500,
                                    backdrop: false,
                                    allowOutsideClick: false
                                });
                            positiveSound.currentTime = 0;
                            positiveSound.play();
                            setTimeout(function () {
                                aksiyon(ogrencilerKarisik[rastgeleSayi].id);
                            }, 1500);
                        } else {

                            $(".swal2-image").attr("src", ogrencilerKarisik[rastgeleSayi].avatar);
                            $(".randomSwal .swal2-title > h1").text(ogrencilerKarisik[rastgeleSayi].name);
                            randomSound.currentTime = 0;
                            randomSound.play();
                        }
                    }, 500);
                }

                $('body').on('click', '.get-random', function (e) {
                    randomSound.play();
                    randomSound.pause();
                    positiveSound.play();
                    positiveSound.pause();
                    rastgeleOgrenciGetir();
                });

                function aksiyon(ogrenci) {
                    $(".get-random").removeClass('disabled');
                    $(".get-students a[class='ogrenci-puanla'][id='" + ogrenci + "']").trigger("click");
                }

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
                                    // Swal.fire(
                                    //     {
                                    //         position: 'bottom',
                                    //         title: "Sweet!",
                                    //         html: "<span class='col-green'>"+data.feedback_name+" points is given to "+data.student_name+"</span>",
                                    //         imageUrl: "img/thumbs-up.png",
                                    //         animation: false,
                                    //         customClass: 'animated bounceInDown',
                                    //         showConfirmButton: false,
                                    //         timer: 3000,
                                    //         backdrop: false,
                                    //         allowOutsideClick: false
                                    //     });
                                } else if (data.feedback_type == 2) {
                                    negativeSound.currentTime = 0;
                                    negativeSound.play();
                                    Toast.fire({
                                        background: '#fb483a',
                                        title: "<span class='col-white'><i class='material-icons' style='position:relative;top:6px;right:5px'>thumb_down</i>"+data.feedback_name+" points is given to "+data.student_name+"</span>",
                                    });
                                    // Swal.fire(
                                    //     {
                                    //         position: 'bottom',
                                    //         title: "No Sweet!",
                                    //         text: "The behavior score was given to the student successfully.",
                                    //         imageUrl: "img/thumbs-down.png",
                                    //         animation: false,
                                    //         customClass: 'animated bounceInDown',
                                    //         showConfirmButton: false,
                                    //         timer: 3000,
                                    //         backdrop: false,
                                    //         allowOutsideClick: false
                                    //     });
                                }
                                $.ajax(
                                    {
                                        url: "get-students?id=<?=$sinifid?>&siralama=" + $("select#siralama_ogrenci").val(),
                                        type: "GET",
                                        contentType: false,
                                        cache: false,
                                        processData: false,
                                        success: function (data) {
                                            if (data == 0) {
                                                $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                            } else if (data == 2) {
                                                $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait öğrenci bulunamadı.</div>");
                                            } else {
                                                $(".get-students").html(data);
                                                $.each($('.chart.chart-pie'), function (i, key) {
                                                    $(key).sparkline(undefined, {
                                                        disableHiddenCheck: true,
                                                        type: 'pie',
                                                        height: '50px',
                                                        sliceColors: ['#4CAF50', '#F44336']
                                                    });
                                                });
                                            }
                                        }
                                    });
                                $.ajax(
                                    {
                                        url: "get-groups?id=<?=$sinifid?>",
                                        type: "GET",
                                        contentType: false,
                                        cache: false,
                                        processData: false,
                                        success: function (data) {
                                            if (data == 0) {
                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div></div>");
                                            } else if (data == 2) {
                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait grup bulunamadı.</div></div></div>");
                                            } else {
                                                $(".get-groups").html(data);
                                            }
                                        }
                                    });
                            } else if (data.sonuc == 0) {
                                Toast.fire({
                                    icon: 'error',
                                    title: "There was a technical problem. Behavior could not be given to student. Please try again.",
                                });
                                // Swal.fire(
                                //     {
                                //         type: 'error',
                                //         position: 'bottom',
                                //         title: "Error!",
                                //         text: "There was a technical problem. Behavior could not be given to student. Please try again.",
                                //         animation: false,
                                //         customClass: 'animated bounceInDown',
                                //         showConfirmButton: false,
                                //         timer: 3000,
                                //         backdrop: false,
                                //         allowOutsideClick: false
                                //     });
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
                                                // swal(
                                                //     {
                                                //         title: "Successful!",
                                                //         text: "Redeem item successfully gived to student.",
                                                //         type: "success",
                                                //         confirmButtonText: "OK",
                                                //         closeOnConfirm: true
                                                //     });
                                                $.ajax(
                                                    {
                                                        url: "get-students?id=<?=$sinifid?>&siralama=" + $("select#siralama_ogrenci").val(),
                                                        type: "GET",
                                                        contentType: false,
                                                        cache: false,
                                                        processData: false,
                                                        success: function (data) {
                                                            if (data == 0) {
                                                                $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                                            } else if (data == 2) {
                                                                $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait öğrenci bulunamadı.</div>");
                                                            } else {
                                                                $(".get-students").html(data);
                                                                $.each($('.chart.chart-pie'), function (i, key) {
                                                                    $(key).sparkline(undefined, {
                                                                        disableHiddenCheck: true,
                                                                        type: 'pie',
                                                                        height: '50px',
                                                                        sliceColors: ['#4CAF50', '#F44336']
                                                                    });
                                                                });
                                                            }
                                                        }
                                                    });
                                                $.ajax(
                                                    {
                                                        url: "get-groups?id=<?=$sinifid?>",
                                                        type: "GET",
                                                        contentType: false,
                                                        cache: false,
                                                        processData: false,
                                                        success: function (data) {
                                                            if (data == 0) {
                                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div></div>");
                                                            } else if (data == 2) {
                                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait grup bulunamadı.</div></div></div>");
                                                            } else {
                                                                $(".get-groups").html(data);
                                                            }
                                                        }
                                                    });
                                            } else if(data == 2) {
                                                negativeSound.currentTime = 0;
                                                negativeSound.play();
                                                Toast.fire({
                                                    background: '#fb483a',
                                                    title: "<span class='col-white'><i class='material-icons' style='position:relative;top:4px;right:5px'>error</i>The student's total score is not enough to redeem this item.</span>",
                                                });
                                                // swal(
                                                //     {
                                                //         title: "Error!",
                                                //         text: "The student's total score is not enough to redeem this item.",
                                                //         type: "error",
                                                //         confirmButtonText: "OK",
                                                //         closeOnConfirm: true
                                                //     });
                                            } else {
                                                negativeSound.currentTime = 0;
                                                negativeSound.play();
                                                Toast.fire({
                                                    icon: 'error',
                                                    title: "The student's total score is not enough to redeem this item.",
                                                });
                                                // swal(
                                                //     {
                                                //         title: "Error!",
                                                //         text: "Somethings went wrong. Please try again.",
                                                //         type: "error",
                                                //         confirmButtonText: "OK",
                                                //         closeOnConfirm: true
                                                //     });
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
                $("body").on('click', '.give-behavior-multiple', function (e) {
                    positiveSound.play();
                    positiveSound.pause();
                    negativeSound.play();
                    negativeSound.pause();
                    var students = $('#modal-students').attr('mode') == 'group' ? $("input#checked_students2").val() : $("input#checked_students").val();
                    var classe = $(this).data("class");
                    var behavior = $(this).data("behavior");
                    var description_1 = $("textarea#feedback_description_multiple1").val();
                    var description_2 = $("textarea#feedback_description_multiple2").val();
                    var point_location_1 = $("select#point_location_multiple1").val();
                    var point_location_2 = $("select#point_location_multiple2").val();
                    var behavior_name = $(this).find("span").first().text();
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
                        url: 'give-behavior-multiple',
                        data: 'ids=' + students + '&class=' + classe + '&behavior=' + behavior + '&feedback_description_1=' + description_1 + '&feedback_description_2=' + description_2 + '&point_location_1=' + point_location_1 + '&point_location_2=' + point_location_2,
                        dataType: 'json',
                        success: function (data) {
                            if (data.sonuc == 1) {
                                $('#modal-students').modal('toggle');
                                if (data.feedback_type == 1) {
                                    positiveSound.currentTime = 0;
                                    positiveSound.play();
                                    Toast.fire({
                                        background: '#2b982b',
                                        title: "<span class='col-white'><i class='material-icons' style='position:relative;top:4px;right:5px'>thumb_up</i>"+behavior_name+" points is given to students.</span>",
                                    });
                                    // Swal.fire(
                                    //     {
                                    //         position: 'bottom',
                                    //         title: "Sweet!",
                                    //         text: "The behavior score was given to the students successfully.",
                                    //         imageUrl: "img/thumbs-up.png",
                                    //         animation: false,
                                    //         customClass: 'animated bounceInDown',
                                    //         showConfirmButton: false,
                                    //         timer: 3000,
                                    //         backdrop: false,
                                    //         allowOutsideClick: false
                                    //     });
                                } else if (data.feedback_type == 2) {
                                    negativeSound.currentTime = 0;
                                    negativeSound.play();
                                    Toast.fire({
                                        background: '#fb483a',
                                        title: "<span class='col-white'><i class='material-icons' style='position:relative;top:6px;right:5px'>thumb_down</i>"+behavior_name+" points is given to students.</span>",
                                    });
                                    // Swal.fire(
                                    //     {
                                    //         position: 'bottom',
                                    //         title: "No Sweet!",
                                    //         text: "The behavior score was given to the students successfully.",
                                    //         imageUrl: "img/thumbs-down.png",
                                    //         animation: false,
                                    //         customClass: 'animated bounceInDown',
                                    //         showConfirmButton: false,
                                    //         timer: 3000,
                                    //         backdrop: false,
                                    //         allowOutsideClick: false
                                    //     });
                                }
                                $.ajax(
                                    {
                                        url: "get-students?id=<?=$sinifid?>&siralama=" + $("select#siralama_ogrenci").val(),
                                        type: "GET",
                                        contentType: false,
                                        cache: false,
                                        processData: false,
                                        success: function (data) {
                                            if (data == 0) {
                                                $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                                            } else if (data == 2) {
                                                $(".get-students").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait öğrenci bulunamadı.</div>");
                                            } else {
                                                $(".get-students").html(data);
                                                $.each($('.chart.chart-pie'), function (i, key) {
                                                    $(key).sparkline(undefined, {
                                                        disableHiddenCheck: true,
                                                        type: 'pie',
                                                        height: '50px',
                                                        sliceColors: ['#4CAF50', '#F44336']
                                                    });
                                                });
                                            }
                                        }
                                    });
                                $.ajax(
                                    {
                                        url: "get-groups?id=<?=$sinifid?>",
                                        type: "GET",
                                        contentType: false,
                                        cache: false,
                                        processData: false,
                                        success: function (data) {
                                            if (data == 0) {
                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div></div></div>");
                                            } else if (data == 2) {
                                                $(".get-groups").html("<div class='row'><div class='col-lg-12 m-t-10'><div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Sınıfa ait grup bulunamadı.</div></div></div>");
                                            } else {
                                                $(".get-groups").html(data);
                                            }
                                        }
                                    });
                                if ($('#modal-students').attr('mode') == 'group') {
                                    $("input#checked_students2").val("");
                                } else {
                                    $(".bottom-button-group").removeClass("select-multiple-active");
                                    $("input#checked_students").val("");
                                    $(".bottom-button-group").html('<a href="javascript:void(0);" role="button" class="btn btn-lg btn-default waves-effect btn-text-ellipsis select-multiple-button"><i class="material-icons col-<?=$yazsinifrenk["color"]?>">check_box</i><span>Select Multiple</span></a><a href="javascript:void(0);" role="button" class="btn btn-lg btn-default waves-effect btn-text-ellipsis get-random"><i class="material-icons col-<?=$yazsinifrenk["color"]?>">shuffle</i><span>Get Random</span></a>');
                                    $(".count-of-selecteds b.count-selecteds").text("");
                                    $('.class-tab a[href="#groups"]').removeClass('disabledTab');
                                }
                            } else if (data.sonuc == 0) {
                                Toast.fire({
                                    icon: 'error',
                                    title: "There was a technical problem. Behavior could not be given to students. Please try again.",
                                });
                                // Swal.fire(
                                //     {
                                //         type: 'error',
                                //         position: 'bottom',
                                //         title: "Error!",
                                //         text: "There was a technical problem. Behavior could not be given to student. Please try again.",
                                //         animation: false,
                                //         customClass: 'animated bounceInDown',
                                //         showConfirmButton: false,
                                //         timer: 3000,
                                //         backdrop: false,
                                //         allowOutsideClick: false
                                //     });
                            }
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
        </script>
        </body>
        </html>
        <?php
    } else if ($uyerol == "student") {
        $sorgusinifid = $DB_con->prepare("SELECT id FROM users WHERE FIND_IN_SET(:sinifid, classes) AND schools = :school AND id = :id");
        $sorgusinifid->execute(array(":sinifid" => $sinifid, ":school" => $uyeokul, ":id" => $uyevtid));
        if ($sorgusinifid->rowCount() != 1) {
            echo 404;
            exit();
        }
        $sorgusinifqwe = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND school = :school AND status = :status");
        $sorgusinifqwe->execute(array(":id" => $sinifid, ":school" => $uyeokul, ":status" => 1));
        if ($sorgusinifqwe->rowCount() != 1) {
            echo 404;
            exit();
        }
        $sorgupuans = $DB_con->prepare("SELECT (SELECT SUM(point) FROM feedbacks_students WHERE student_id = :studentid AND type = 1 AND class_id = :classid) as pozitifpuans,(SELECT SUM(point) FROM feedbacks_students WHERE student_id = :studentid2 AND type = 2 AND class_id = :classid2) as negatifpuans,(SELECT SUM(point) FROM feedbacks_students WHERE student_id = :studentid3 AND class_id = :classid3) as toplampuans");
        $sorgupuans->execute(array(":studentid" => $uyevtid, ":classid" => $sinifid, ":studentid2" => $uyevtid, ":classid2" => $sinifid, ":studentid3" => $uyevtid, ":classid3" => $sinifid));
        $yazpuans = $sorgupuans->fetch(PDO::FETCH_ASSOC);
        $a = $yazpuans["negatifpuans"] != NULL ? abs($yazpuans["negatifpuans"]) : 0;
        $b = $yazpuans["pozitifpuans"] != NULL ? $yazpuans["pozitifpuans"] : 0;
        $sorguteachers = $DB_con->prepare("SELECT users.id,users.name,users.avatar,classes.name AS sinifadi FROM users INNER JOIN classes ON FIND_IN_SET(users.id, classes.teachers) > 0 WHERE role = :role AND classes.id = :classid");
        $sorguteachers->execute(array(":role" => "teacher", ":classid" => $sinifid));
        ?>
        <section class="content">
            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="row clearfix">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                            <div class="info-box hover-zoom-effect">
                                <div class="icon bg-light-blue">
                                    <i class="material-icons">school</i>
                                </div>
                                <div class="content">
                                    <div class="text">TOTAL BEHAVIOR POINT</div>
                                    <div class="number"><b
                                                class="col-light-blue"><?= $yazpuans["toplampuans"] != NULL ? $yazpuans["toplampuans"] : 0 ?></b>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                            <div class="info-box">
                                <div class="icon">
                                    <div class="chart chart-pie" data-chartcolor="orange"><?= $b ?>,<?= $a ?></div>
                                </div>
                                <div class="content">
                                    <div class="text">POSITIVE / NEGATIVE BEHAVIOR POINTS</div>
                                    <div class="number"><b class="col-green"><?= $b ?></b> / <b
                                                class="col-red">-<?= $a ?></b> Points
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <div class="panel panel-default panel-post">
                                <div class="panel-heading">
                                    <h4>Teacher(s):</h4>
                                </div>
                                <?php
                                while ($yazteachers = $sorguteachers->fetch(PDO::FETCH_ASSOC)) {
                                    $mesajlink = "";
                                    $mesajlink2 = "";
                                    $mesajlink3 = "";
                                    $sorguKonusma = $DB_con->prepare("SELECT class_name,class_id,first,second,id FROM conversations WHERE (first = :ben AND second = :o AND class_id = :class) OR (first = :o2 AND second = :ben2 AND class_id = :class2)");
                                    $sorguKonusma->bindValue(':ben', $uyevtid, PDO::PARAM_INT);
                                    $sorguKonusma->bindValue(':o', $yazteachers["id"], PDO::PARAM_INT);
                                    $sorguKonusma->bindValue(':o2', $yazteachers["id"], PDO::PARAM_INT);
                                    $sorguKonusma->bindValue(':ben2', $uyevtid, PDO::PARAM_INT);
                                    $sorguKonusma->bindValue(':class', $sinifid, PDO::PARAM_INT);
                                    $sorguKonusma->bindValue(':class2', $sinifid, PDO::PARAM_INT);
                                    $sorguKonusma->execute();
                                    $yazKonusma = $sorguKonusma->fetch(PDO::FETCH_ASSOC);
                                    if ($sorguKonusma->rowCount() > 0) {
                                        if ($yazKonusma["first"] != $uyevtid) $gelenuyexd = $yazKonusma["first"];
                                        else if ($yazKonusma["second"] != $uyevtid) $gelenuyexd = $yazKonusma["second"];
                                        $mesajlink = '<a href="messages-' . seo($yazKonusma["class_name"]) . '-' . $yazKonusma["class_id"] . '-' . $gelenuyexd . '-' . $yazKonusma["id"] . '" role="button" class="btn bg-orange btn-circle waves-effect waves-circle waves-float"><i class="material-icons">forum</i></a>';
                                        $mesajlink2 = '<a href="messages-' . seo($yazKonusma["class_name"]) . '-' . $yazKonusma["class_id"] . '-' . $gelenuyexd . '-' . $yazKonusma["id"] . '"><img src="' . $yazteachers["avatar"] . '"></a>';
                                        $mesajlink3 = '<a href="messages-' . seo($yazKonusma["class_name"]) . '-' . $yazKonusma["class_id"] . '-' . $gelenuyexd . '-' . $yazKonusma["id"] . '">' . $yazteachers["name"] . '</a>';
                                    } else {
                                        $mesajlink = '<button type="button" data-toggle="modal" data-target="#sendMessage" class="btn bg-orange btn-circle waves-effect waves-circle waves-float" data-class-name="' . $yazteachers["sinifadi"] . '" data-class-id="' . $sinifid . '" data-teacher-name="' . $yazteachers["name"] . '" data-teacher-id="' . $yazteachers["id"] . '"><i class="material-icons">forum</i></button>';
                                        $mesajlink2 = '<a href="javascript:;" data-toggle="modal" data-target="#sendMessage" data-class-name="' . $yazteachers["sinifadi"] . '" data-class-id="' . $sinifid . '" data-teacher-name="' . $yazteachers["name"] . '" data-teacher-id="' . $yazteachers["id"] . '"><img src="' . $yazteachers["avatar"] . '"></a>';
                                        $mesajlink3 = '<a href="javascript:;" data-toggle="modal" data-target="#sendMessage" data-class-name="' . $yazteachers["sinifadi"] . '" data-class-id="' . $sinifid . '" data-teacher-name="' . $yazteachers["name"] . '" data-teacher-id="' . $yazteachers["id"] . '">' . $yazteachers["name"] . '</a>';
                                    }
                                    ?>
                                    <div class="panel-heading">
                                        <div class="media">
                                            <div class="media-left">
                                                <?= $mesajlink2 ?>
                                            </div>
                                            <div class="media-body">
                                                <h4 class="media-heading">
                                                    <?= $mesajlink3 ?>
                                                </h4>
                                                Teacher
                                            </div>
                                            <div class="media-right">
                                                <?= $mesajlink ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <?php
                            $sorguHistory = $DB_con->prepare("SELECT id,description,name,point,type,teacher,date FROM feedbacks_students WHERE class_id = :class AND student_id = :student AND type <> :type ORDER BY id DESC");
                            $sorguHistory->execute(array(":class" => $sinifid, ":student" => $uyevtid, ":type"=>3));
                            if ($sorguHistory->rowCount() > 0) {
                                ?>
                                <table class="table table-bordered table-striped table-hover report-behavior-list dataTable nowrap"
                                       style="width:100%">
                                    <thead>
                                    <tr>
                                        <th>Behavior Name</th>
                                        <th>Type</th>
                                        <th>Point</th>
                                        <th>Teacher</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    while ($yazHistory = $sorguHistory->fetch(PDO::FETCH_ASSOC)) {
                                        $sorguTeacher = $DB_con->prepare("SELECT name FROM users WHERE id = :id AND role = :role");
                                        $sorguTeacher->execute(array(":id" => $yazHistory["teacher"], ":role" => "teacher"));
                                        $yazTeacher = $sorguTeacher->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <tr>
                                            <td><?= $yazHistory["name"] ?></td>
                                            <td><?php if ($yazHistory["type"] == 1) {
                                                    echo "<b class='col-green'>Positive</b>";
                                                } else if ($yazHistory["type"] == 2) {
                                                    echo "<b class='col-red'>Negative</b>";
                                                } ?></td>
                                            <td><?= $yazHistory["point"] ?></td>
                                            <td><?= $yazTeacher["name"] ?></td>
                                            <td><?= printDate($DB_con, $yazHistory["date"], $uyeokul) ?></td>
                                            <td><?= $yazHistory["description"] ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                                <?php
                            } else {
                                ?>
                                <div class="col-12">
                                    <div class="alert alert-warning">Henüz size verilen davranış notu bulunmamakta.
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="modal fade in" id="sendMessage" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Send Message to Teacher</h4>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <h6 class="list-group-item-heading">Class Name</h6>
                                <p class="list-group-item-text class-name"></p>
                            </li>
                            <li class="list-group-item">
                                <h6 class="list-group-item-heading">Teacher Name</h6>
                                <p class="list-group-item-text teacher-name"></p>
                            </li>
                        </ul>
                        <form id="Send-Message-Form">
                            <div class="form-group">
                                <label for="name">Message:</label>
                                <div class="form-line">
                                    <textarea class="form-control no-resize" id="message" name="message"
                                              rows="4"></textarea>
                                </div>
                            </div>
                            <input type="hidden" name="hidden_class_id" id="hidden_class_id">
                            <input type="hidden" name="hidden_student_id" id="hidden_student_id"
                                   value="<?= $uyevtid ?>">
                            <input type="hidden" name="hidden_teacher_id" id="hidden_teacher_id">
                            <div class="form-group">
                                <button type="submit"
                                        class="btn btn-success btn-block btn-lg waves-effect Send-Message-Button">Send
                                    Message
                                </button>
                            </div>
                        </form>
                        <div id="Send-Message-Result"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <script src="plugins/jquery/jquery.min.js"></script>
        <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
        <script src="plugins/node-waves/waves.min.js"></script>
        <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
        <script src="plugins/jquery-sparkline/jquery.sparkline.js"></script>
        <script src="plugins/jquery-datatable/jquery.dataTables.min.js"></script>
        <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.min.js"></script>
        <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.responsive.min.js"></script>
        <script src="plugins/jquery-datatable/skin/bootstrap/js/responsive.bootstrap.min.js"></script>
        <script src="js/main.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $.each($('.chart.chart-pie'), function (i, key) {
                    $(key).sparkline(undefined, {
                        disableHiddenCheck: true,
                        type: 'pie',
                        height: '50px',
                        sliceColors: ['#4CAF50', '#F44336']
                    });
                });
                $('.report-behavior-list').DataTable({
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
                $('#sendMessage').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget);
                    var class_name = button.data("class-name");
                    var teacher_name = button.data("teacher-name");
                    var class_id = button.data("class-id");
                    var teacher_id = button.data("teacher-id");
                    var modal = $(this);
                    modal.find('#hidden_class_id').val(class_id);
                    modal.find('#hidden_teacher_id').val(teacher_id);
                    modal.find('p.list-group-item-text.class-name').text(class_name);
                    modal.find('p.list-group-item-text.teacher-name').text(teacher_name);
                });
                $('#sendMessage').on('hidden.bs.modal', function (event) {
                    location.reload();
                });
                $("#Send-Message-Form").on('submit', (function (e) {
                    e.preventDefault();
                    $('.Send-Message-Button').prop('disabled', true);
                    $('.Send-Message-Button').html("Sending...");
                    $("#Send-Message-Result").empty();
                    $.ajax(
                        {
                            url: "start-conversation",
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
                }));
            });
        </script>
        </body>
        </html>
        <?php
    } else {
        echo "Forbidden";
        exit();
    }
} else if ($page_request == "invite-teacher") {
    if ($uyerol != "admin") {
        echo "Forbidden";
        exit();
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="header">
                                <h4>Invite Teacher</h4>
                            </div>
                            <a href="teachers" class="btn btn-default btn-block btn-lg waves-effect"><i
                                        class="material-icons">arrow_back</i><span>Go Back</span></a>
                            <div class="body">
                                <form id="Invite-Teacher-Form">
                                    <div class="form-group">
                                        <label for="teacher_name">Teacher Name:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="teacher_name" id="teacher_name"
                                                   placeholder="Teacher name..." type="text">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="teacher_email">Teacher E-Mail Address:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="teacher_email" id="teacher_email"
                                                   placeholder="Teacher e-mail address..." type="text">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit"
                                                class="btn btn-success btn-block btn-lg waves-effect Invite-Teacher-Button">
                                            Invite Teacher
                                        </button>
                                    </div>
                                </form>
                                <div id="Invite-Teacher-Result"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('body').on('submit', '#Invite-Teacher-Form', function (e) {
                e.preventDefault();

                $('.Invite-Teacher-Button').prop('disabled', true);
                $('.Invite-Teacher-Button').html("Teacher Inviting...");

                $("#Invite-Teacher-Result").empty();

                $.ajax(
                    {
                        url: "invite-teacher-a",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $('.Invite-Teacher-Button').prop('disabled', false);
                                $('.Invite-Teacher-Button').html("Invite Teacher");
                                if (data == 0) {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data == 1) {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Öğretmen başarıyla sisteme davet edildi!</div>");
                                    $("#Invite-Teacher-Form").trigger("reset");
                                }
                                if (data == 2) {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if (data == 3) {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Öğretmen adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                                if (data == 4) {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen öğretmene ait geçerli bir e-posta adresini giriniz.</div>");
                                }
                                if (data == 5) {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Belirtilen e-postaya ait öğretmen zaten daha önceden sisteme davet edilmişti. Öğretmene tekrardan e-posta gönderildi.</div>");
                                    $("#Invite-Teacher-Form").trigger("reset");
                                }
                                if (data == 6) {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Öğretmen için belirttiğiniz e-posta adresi ile sisteme daha önce öğrenci olarak kayıt yapılmış.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "students") {
    if ($uyerol != "admin") {
        echo "Forbidden";
        exit();
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="panel panel-default panel-post">
                    <div class="panel-heading">
                        <h4><strong>Students</strong></h4>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="form-group">
                            <select class="form-control" id="siralama" name="siralama">
                                <option value="0">Tarihe göre (Önce en yeni öğrenci)</option>
                                <option value="1">Tarihe göre (Önce en eski öğrenci)</option>
                                <option value="2">Davranış notları toplamına göre (Önce en yüksek)</option>
                                <option value="3">Davranış notları toplamına göre (Önce en düşük)</option>
                                <option value="4">Adına göre (A-Z)</option>
                                <option value="5">Adına göre (Z-A)</option>
                                <option value="6">Soyadına göre (A-Z)</option>
                                <option value="7">Soyadına göre (Z-A)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="input-group">
                            <div class="form-line">
                                <input type="text" class="form-control" name="arama" id="arama"
                                       placeholder="Tabloda ara..."
                                       onkeyup="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');"
                                       onblur="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');">
                            </div>
                            <a href="javascript:;" class="input-group-addon" id="arama-buton"><i class="material-icons">search</i></a>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="notice notice-info" style="margin-top:0px;">
                            <strong>Bilgi: </strong>Sonuçlar arasından öğrencinin, <b>tam adına ve e-posta adresine</b>
                            göre arama yapabilirsiniz.
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <label>Class:</label>
                        <div class="form-group">
                            <select class="form-control" id="sinif" name="sinif">
                                <option value="0">Seçiniz...</option>
                                <?php
                                $sorgusinifs = $DB_con->prepare("SELECT classes.id,classes.name,group_concat(users.name) AS teachersname FROM classes INNER JOIN users ON FIND_IN_SET(users.id,teachers) > 0 WHERE school = :school GROUP BY classes.id");
                                $sorgusinifs->execute(array(":school" => $uyeokul));
                                while ($yazsinifs = $sorgusinifs->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <option value="<?= $yazsinifs["id"] ?>"><?= $yazsinifs["name"] ?>
                                        (Öğretmenler: <?= $yazsinifs["teachersname"] ?>)
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <label>Puan Türü:</label>
                        <div class="form-group">
                            <select class="form-control" id="puan" name="puan">
                                <option value="0">Total Behavior Points With Redeem Points</option>
                                <option value="4">Total Behavior Points Without Redeem Points</option>
                                <option value="1">Only Positive Behavior Points</option>
                                <option value="2">Only Negative Behavior Points</option>
                                <option value="3">Only Redeem Points</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <label>Kayıt Türü:</label>
                        <div class="form-group">
                            <select class="form-control" id="kayit" name="kayit">
                                <option value="0">Seçiniz...</option>
                                <option value="1">Google Classroom</option>
                                <option value="2">Manuel/CSV</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <label>Kayıt Durumu:</label>
                        <div class="form-group">
                            <select class="form-control" id="kayit2" name="kayit2">
                                <option value="0">Seçiniz...</option>
                                <option value="1">Kaydoldu</option>
                                <option value="2">Kaydolmadı</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" id="students">

                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade in" id="modal-student" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-student-content">

            </div>
        </div>
    </div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {'sbmtoken': $('meta[name="sbmtoken"]').attr('content')}
            });
            $.ajax(
                {
                    url: "students-a",
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (data) {
                        $("#students").html(data);
                    }
                });
            $(document).on("click", '.sayfala-buton', function (event) {
                event.preventDefault();
                var node = this.id;
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?sayfa=" + node.replace(regexp2, '') + "&siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, '') + "&kayit2=" + kayit2.replace(regexp2, '') + "&puan=" + puan.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#siralama', function (event) {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, '') + "&kayit2=" + kayit2.replace(regexp2, '') + "&puan=" + puan.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#kayit', function (event) {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, '') + "&kayit2=" + kayit2.replace(regexp2, '') + "&puan=" + puan.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#puan', function (event) {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, '') + "&kayit2=" + kayit2.replace(regexp2, '') + "&puan=" + puan.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#kayit2', function (event) {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, '') + "&kayit2=" + kayit2.replace(regexp2, '') + "&puan=" + puan.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("click", '#arama-buton', function (event) {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, '') + "&kayit2=" + kayit2.replace(regexp2, '') + "&puan=" + puan.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#sinif', function (event) {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, '') + "&kayit2=" + kayit2.replace(regexp2, '') + "&puan=" + puan.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $('body').on('click', '.ogrenci-duzenle', function (e) {
                e.preventDefault();
                var regexp = /[^0-9]/g;
                var ogrenci = this.id;
                $.ajax(
                    {
                        url: "studentinfos-" + ogrenci.replace(regexp, ''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            if (data == 0) {
                                $(".modal-student-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                            } else {
                                $(".modal-student-content").html(data);
                            }
                        }
                    });
            });
            $('body').on('click', '.Delete-Student-Button', function (e) {
                e.preventDefault();
                var ogrencix = this.id;
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
                                    data: 'student=' + ogrencix,
                                    success: function (data) {
                                        if (data == 1) {
                                            swal(
                                                {
                                                    title: "Deleted!",
                                                    text: "Student has been deleted.",
                                                    type: "success",
                                                    confirmButtonText: "OK",
                                                    closeOnConfirm: true
                                                });
                                            var siralama = $("select#siralama").val();
                                            var sinif = $("select#sinif").val();
                                            var puan = $("select#puan").val();
                                            var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                                            var regexp2 = /[^0-9]/g;
                                            var arama = $("input#arama").val();
                                            var kayit = $("select#kayit").val();
                                            var kayit2 = $("select#kayit2").val();
                                            $.ajax(
                                                {
                                                    url: "students-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, '') + "&kayit2=" + kayit2.replace(regexp2, '') + "&puan=" + puan.replace(regexp2, ''),
                                                    type: "POST",
                                                    contentType: false,
                                                    cache: false,
                                                    processData: false,
                                                    beforeSend: function () {
                                                        $('.page-loader-wrapper').fadeIn(100);
                                                    },
                                                    success: function (data) {
                                                        $("#students").html(data);
                                                        $('.page-loader-wrapper').fadeOut();
                                                        $("html, body").animate({scrollTop: 0}, "slow");
                                                    }
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
            $('body').on('submit', '#Edit-Student-Form', function (e) {
                e.preventDefault();

                $('.Edit-Student-Button').prop('disabled', true);
                $('.Edit-Student-Button').html("Student Editing...");

                $("#Edit-Student-Result").empty();

                $.ajax(
                    {
                        url: "editstudent",
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
                                    var siralama = $("select#siralama").val();
                                    var sinif = $("select#sinif").val();
                                    var puan = $("select#puan").val();
                                    var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                                    var regexp2 = /[^0-9]/g;
                                    var arama = $("input#arama").val();
                                    var kayit = $("select#kayit").val();
                                    var kayit2 = $("select#kayit2").val();
                                    $.ajax(
                                        {
                                            url: "students-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, '') + "&kayit2=" + kayit2.replace(regexp2, '') + "&puan=" + puan.replace(regexp2, ''),
                                            type: "POST",
                                            contentType: false,
                                            cache: false,
                                            processData: false,
                                            beforeSend: function () {
                                                $('.page-loader-wrapper').fadeIn(100);
                                            },
                                            success: function (data) {
                                                $("#students").html(data);
                                                $('.page-loader-wrapper').fadeOut();
                                                $("html, body").animate({scrollTop: 0}, "slow");
                                            }
                                        });
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
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "teachers") {
    if ($uyerol != "admin") {
        echo "Forbidden";
        exit();
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="panel panel-default panel-post">
                    <div class="panel-heading">
                        <h4><strong>Teachers</strong></h4>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="form-group">
                            <select class="form-control" id="siralama" name="siralama">
                                <option value="0">Tarihe göre (Önce en yeni öğretmen)</option>
                                <option value="1">Tarihe göre (Önce en eski öğretmen)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="input-group">
                            <div class="form-line">
                                <input type="text" class="form-control" name="arama" id="arama"
                                       placeholder="Tabloda ara..."
                                       onkeyup="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');"
                                       onblur="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');">
                            </div>
                            <a href="javascript:;" class="input-group-addon" id="arama-buton"><i class="material-icons">search</i></a>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="notice notice-info" style="margin-top:0px;">
                            <strong>Bilgi: </strong>Sonuçlar arasından öğretmenin, <b>tam adına ve e-posta adresine</b>
                            göre arama yapabilirsiniz.
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <label>Class:</label>
                        <div class="form-group">
                            <select class="form-control" id="sinif" name="sinif">
                                <option value="0">Seçiniz...</option>
                                <?php
                                $sorgusinifs = $DB_con->prepare("SELECT classes.id,classes.name,group_concat(users.name) AS teachersname FROM classes INNER JOIN users ON FIND_IN_SET(users.id,teachers) > 0 WHERE school = :school GROUP BY classes.id");
                                $sorgusinifs->execute(array(":school" => $uyeokul));
                                while ($yazsinifs = $sorgusinifs->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <option value="<?= $yazsinifs["id"] ?>"><?= $yazsinifs["name"] ?>
                                        (Öğretmenler: <?= $yazsinifs["teachersname"] ?>)
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <label>Kayıt Durumu:</label>
                        <div class="form-group">
                            <select class="form-control" id="kayit" name="kayit">
                                <option value="0">Seçiniz...</option>
                                <option value="1">Kaydoldu</option>
                                <option value="2">Kaydolmadı</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" id="teachers">

                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade in" id="modal-teacher" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-teacher-content">

            </div>
        </div>
    </div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {'sbmtoken': $('meta[name="sbmtoken"]').attr('content')}
            });
            $.ajax(
                {
                    url: "teachers-a",
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (data) {
                        $("#teachers").html(data);
                    }
                });
            $(document).on("click", '.sayfala-buton', function (event) {
                event.preventDefault();
                var node = this.id;
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "teachers-a?sayfa=" + node.replace(regexp2, '') + "&siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#teachers").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#siralama', function (event) {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "teachers-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#teachers").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#kayit', function (event) {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "teachers-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#teachers").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("click", '#arama-buton', function (event) {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "teachers-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#teachers").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#sinif', function (event) {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var sinif = $("select#sinif").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "teachers-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#teachers").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $('body').on('click', '.ogretmen-duzenle', function (e) {
                e.preventDefault();
                var regexp = /[^0-9]/g;
                var ogretmen = this.id;
                $.ajax(
                    {
                        url: "teacherinfos-" + ogretmen.replace(regexp, ''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            if (data == 0) {
                                $(".modal-teacher-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                            } else {
                                $(".modal-teacher-content").html(data);
                            }
                        }
                    });
            });
            $('body').on('submit', '#Edit-Teacher-Form', function (e) {
                e.preventDefault();

                $('.Edit-Teacher-Button').prop('disabled', true);
                $('.Edit-Teacher-Button').html("Teacher Editing...");

                $("#Edit-Teacher-Result").empty();

                $.ajax(
                    {
                        url: "editteacher",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $('.Edit-Teacher-Button').prop('disabled', false);
                                $('.Edit-Teacher-Button').html("Edit Teacher");
                                if (data == 0) {
                                    $("#Edit-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data == 1) {
                                    $("#Edit-Teacher-Result").html("<div class='alert alert-success'><strong>Successful!</strong> The teacher has been successfully edited.</div>");
                                    var siralama = $("select#siralama").val();
                                    var sinif = $("select#sinif").val();
                                    var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                                    var regexp2 = /[^0-9]/g;
                                    var arama = $("input#arama").val();
                                    var kayit = $("select#kayit").val();
                                    $.ajax(
                                        {
                                            url: "teachers-a?siralama=" + siralama.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&sinif=" + sinif.replace(regexp2, '') + "&kayit=" + kayit.replace(regexp2, ''),
                                            type: "POST",
                                            contentType: false,
                                            cache: false,
                                            processData: false,
                                            beforeSend: function () {
                                                $('.page-loader-wrapper').fadeIn(100);
                                            },
                                            success: function (data) {
                                                $("#teachers").html(data);
                                                $('.page-loader-wrapper').fadeOut();
                                                $("html, body").animate({scrollTop: 0}, "slow");
                                            }
                                        });
                                }
                                if (data == 2) {
                                    $("#Edit-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if (data == 3) {
                                    $("#Edit-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Öğretmen adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "classes") {
    if ($uyerol != "admin") {
        echo "Forbidden";
        exit();
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="panel panel-default panel-post">
                    <div class="panel-heading">
                        <h4><strong>Classes</strong></h4>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="input-group">
                            <div class="form-line">
                                <input type="text" class="form-control" name="arama" id="arama"
                                       placeholder="Tabloda ara..."
                                       onkeyup="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');"
                                       onblur="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');">
                            </div>
                            <a href="javascript:;" class="input-group-addon" id="arama-buton"><i class="material-icons">search</i></a>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="notice notice-info" style="margin-top:0px;">
                            <strong>Bilgi: </strong>Sonuçlar arasından sınıfın, <b>adına</b> göre arama yapabilirsiniz.
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <label>Kayıt Türü:</label>
                        <div class="form-group">
                            <select class="form-control" id="kayit" name="kayit">
                                <option value="0">Seçiniz...</option>
                                <option value="1">Google Classroom</option>
                                <option value="2">Manual</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" id="classes">

                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade in" id="modal-class" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-class-content">

            </div>
        </div>
    </div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {'sbmtoken': $('meta[name="sbmtoken"]').attr('content')}
            });
            $.ajax(
                {
                    url: "classes-a",
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (data) {
                        $("#classes").html(data);
                    }
                });
            $(document).on("click", '.sayfala-buton', function (event) {
                event.preventDefault();
                var node = this.id;
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "classes-a?sayfa=" + node.replace(regexp2, '') + "&arama=" + arama.replace(regexp, '') + "&kayit=" + kayit.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#classes").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#kayit', function (event) {
                event.preventDefault();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "classes-a?arama=" + arama.replace(regexp, '') + "&kayit=" + kayit.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#classes").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $(document).on("click", '#arama-buton', function (event) {
                event.preventDefault();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "classes-a?arama=" + arama.replace(regexp, '') + "&kayit=" + kayit.replace(regexp2, ''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $("#classes").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({scrollTop: 0}, "slow");
                        }
                    });
            });
            $('body').on('click', '.sinif-duzenle', function (e) {
                e.preventDefault();
                var regexp = /[^0-9]/g;
                var sinif = this.id;
                $.ajax(
                    {
                        url: "classinfos-" + sinif.replace(regexp, ''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            if (data == 0) {
                                $(".modal-class-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                            } else {
                                $(".modal-class-content").html(data);
                            }
                        }
                    });
            });
            $('body').on('submit', '#Edit-Class-Form', function (e) {
                e.preventDefault();

                $('.Edit-Class-Button').prop('disabled', true);
                $('.Edit-Class-Button').html("Class Editing...");

                $("#Edit-Class-Result").empty();

                $.ajax(
                    {
                        url: "editclass",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $('.Edit-Class-Button').prop('disabled', false);
                                $('.Edit-Class-Button').html("Edit Class");
                                if (data == 0) {
                                    $("#Edit-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data == 1) {
                                    $("#Edit-Class-Result").html("<div class='alert alert-success'><strong>Successful!</strong> The class has been successfully edited.</div>");
                                    var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                                    var regexp2 = /[^0-9]/g;
                                    var arama = $("input#arama").val();
                                    var kayit = $("select#kayit").val();
                                    $.ajax(
                                        {
                                            url: "classes-a?arama=" + arama.replace(regexp, '') + "&kayit=" + kayit.replace(regexp2, ''),
                                            type: "POST",
                                            contentType: false,
                                            cache: false,
                                            processData: false,
                                            beforeSend: function () {
                                                $('.page-loader-wrapper').fadeIn(100);
                                            },
                                            success: function (data) {
                                                $("#classes").html(data);
                                                $('.page-loader-wrapper').fadeOut();
                                                $("html, body").animate({scrollTop: 0}, "slow");
                                            }
                                        });
                                }
                                if (data == 2) {
                                    $("#Edit-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if (data == 3) {
                                    $("#Edit-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Sınıf adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                                if (data == 4) {
                                    $("#Edit-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Sınıfa ait en az bir adet öğretmen seçmelisiniz.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "announcements") {
    if ($uyerol != "admin") {
        echo "Forbidden";
        exit();
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="panel panel-default panel-post">
                    <div class="panel-heading">
                        <h4><strong>Announcements</strong></h4>
                    </div>
                    <button type="button" class="btn btn-success btn-block waves-effect waves-block" data-toggle="modal" data-target="#createAnnouncementModal"><i class="material-icons">add</i> <span>Create Announcement</span></button>
                </div>
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" id="announcements">

                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade in" id="createAnnouncementModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Create Announcement</h4>
                </div>
                <div class="modal-body">
                    <form id="createAnnouncementForm">
                        <div class="form-group">
                            <textarea name="template" id="template"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit"
                                    class="btn btn-primary btn-block btn-lg waves-effect createAnnouncementButton">Create
                                Announcement
                            </button>
                        </div>
                    </form>
                    <div id="createAnnouncementResult"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade in" id="editAnnouncementModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content announcementContent">

            </div>
        </div>
    </div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="js/main.js"></script>
    <script src="//cdn.ckeditor.com/4.13.0/basic/ckeditor.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {'sbmtoken': $('meta[name="sbmtoken"]').attr('content')}
            });
            $.ajax(
                {
                    url: "announcements-a",
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (data) {
                        $("#announcements").html(data);
                    }
                });

            CKEDITOR.replace('template');

            function CKUpdate() {
                for (instance in CKEDITOR.instances)
                    CKEDITOR.instances[instance].updateElement();
            }

            $('body').on('submit', '#createAnnouncementForm', function (e) {
                CKUpdate();
                e.preventDefault();

                $('.createAnnouncementButton').prop('disabled', true);
                $('.createAnnouncementButton').html("Announcement Creating...");

                $("#createAnnouncementResult").empty();
                $.ajax(
                    {
                        url: "create-announcement",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $('.createAnnouncementButton').prop('disabled', false);
                                $('.createAnnouncementButton').html("Create Announcement");
                                if (data == 0) {
                                    $("#createAnnouncementResult").html("<div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div>");
                                }
                                if (data == 1) {
                                    $("#createAnnouncementResult").html("<div class='alert alert-success'><strong>Successful!</strong> Announcement successfully created.</div>");
                                    $("#createAnnouncementForm").trigger("reset");
                                    CKEDITOR.instances.template.setData('');
                                    $.ajax(
                                        {
                                            url: "announcements-a",
                                            type: "GET",
                                            contentType: false,
                                            cache: false,
                                            processData: false,
                                            success: function (data) {
                                                $("#announcements").html(data);
                                            }
                                        });
                                }
                                if (data == 2) {
                                    $("#createAnnouncementResult").html("<div class='alert alert-danger'><strong>Error:</strong> Please fill in the form completely.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
            $('body').on('click', '.editAnnouncement', function (e) {
                e.preventDefault();
                var regexp = /[^0-9]/g;
                var announcement = this.id;
                $.ajax(
                    {
                        url: "infos-announcement-" + announcement.replace(regexp, ''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            if (data == 0) {
                                $(".announcementContent").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                            } else {
                                $(".announcementContent").html(data);
                                CKEDITOR.replace('templateEdit');

                            }
                        }
                    });
            });
            $('body').on('submit', '#editAnnouncementForm', function (e) {
                CKUpdate();
                e.preventDefault();

                $('.editAnnouncementButton').prop('disabled', true);
                $('.editAnnouncementButton').html("Announcement Editing...");

                $("#editAnnouncementResult").empty();

                $.ajax(
                    {
                        url: "edit-announcement",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $('.editAnnouncementButton').prop('disabled', false);
                                $('.editAnnouncementButton').html("Edit Announcement");
                                if (data == 0) {
                                    $("#editAnnouncementResult").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data == 1) {
                                    $("#editAnnouncementResult").html("<div class='alert alert-success'><strong>Successful!</strong> The announcement has been successfully edited.</div>");
                                }
                                if (data == 2) {
                                    $("#editAnnouncementResult").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
            $('body').on('click', '.deleteAnnouncement', function (e) {
                e.preventDefault();
                var announcementId = this.id;
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
                                    url: 'delete-announcement',
                                    data: 'id=' + announcementId,
                                    success: function (data) {
                                        if (data == 1) {
                                            swal(
                                                {
                                                    title: "Deleted!",
                                                    text: "Announcement has been deleted.",
                                                    type: "success",
                                                    confirmButtonText: "OK",
                                                    closeOnConfirm: true
                                                });
                                            $.ajax(
                                                {
                                                    url: "announcements-a",
                                                    type: "GET",
                                                    contentType: false,
                                                    cache: false,
                                                    processData: false,
                                                    success: function (data) {
                                                        $("#announcements").html(data);
                                                    }
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
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "messages") {
    $user = filter_input(INPUT_GET, 'user', FILTER_VALIDATE_INT);
    if ($user === false) {
        echo 0;
        exit();
    }
    $sorguuser = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND schools = :school");
    $sorguuser->execute(array(":id" => $user, ":school" => $uyeokul));
    if ($sorguuser->rowCount() != 1) {
        echo 0;
        exit();
    }
    $classid = filter_input(INPUT_GET, 'classid', FILTER_VALIDATE_INT);
    if ($classid === false) {
        echo 0;
        exit();
    }
    $classname = filter_input(INPUT_GET, 'classname', FILTER_SANITIZE_STRING);
    $sorguclass = $DB_con->prepare("SELECT id,name,status FROM classes WHERE id = :id AND school = :school");
    $sorguclass->execute(array(":id" => $classid, ":school" => $uyeokul));
    if ($sorguclass->rowCount() != 1) {
        echo 0;
        exit();
    }
    $conversationid = filter_input(INPUT_GET, 'conversation', FILTER_VALIDATE_INT);
    if ($conversationid === false) {
        echo 0;
        exit();
    }
    $ikinci_uye = (int)$user;
    $q = $DB_con->prepare("SELECT id FROM users WHERE id = :ikinciuye AND id != :ben");
    $q->execute(array(":ikinciuye" => $ikinci_uye, ":ben" => $uyevtid));
    if ($q->rowCount() != 1) {
        echo 0;
        exit();
    }
    $conversation_id = (int)$conversationid;
    $conver = $DB_con->prepare("SELECT id FROM conversations WHERE id = :konusmaid AND (first = :benone OR second = :bentwo) AND ((first = :ben3 AND first_deleted != 1) OR (second = :ben4 AND second_deleted != 1)) AND class_id = :class");
    $conver->execute(array(":konusmaid" => $conversation_id, ":benone" => $uyevtid, ":bentwo" => $uyevtid, ":ben3" => $uyevtid, ":ben4" => $uyevtid, ":class" => $classid));
    if ($conver->rowCount() != 1) {
        echo 0;
        exit();
    }
    $sorguikinciuye = $DB_con->prepare("SELECT id,name,avatar,role FROM users WHERE id = :user");
    $sorguikinciuye->execute(array(":user" => $ikinci_uye));
    $yazikinciuye = $sorguikinciuye->fetch(PDO::FETCH_ASSOC);
    $yazsinifad = $sorguclass->fetch(PDO::FETCH_ASSOC);
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">

                <div class="panel panel-default panel-post">
                    <div class="panel-heading">
                        <div class="media">
                            <div class="media-left">
                                <a href="javascript:;">
                                    <img src="<?= $yazikinciuye["avatar"] ?>">
                                </a>
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading">
                                    <a href="javascript:;"><?= $yazikinciuye["name"] ?>
                                        <small>(<?php if ($yazikinciuye["role"] == "teacher") {
                                                echo "Teacher";
                                            } else if ($yazikinciuye["role"] == "student") {
                                                echo "Student";
                                            } ?>)
                                        </small>
                                    </a>
                                </h4>
                                Conversation for: <b><?= $yazsinifad["name"] ?></b>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body chat-history chat">
                        <ul id="asd2">

                        </ul>
                    </div>
                    <?php if($yazsinifad["status"] == 1) { ?>
                    <div class="panel-footer">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="hidden" id="conversation_id"
                                       value="<?php echo base64_encode($conversation_id); ?>">
                                <input type="hidden" id="user_form" value="<?php echo base64_encode($uyevtid); ?>">
                                <input type="hidden" id="user_to" value="<?php echo base64_encode($ikinci_uye); ?>">
                                <textarea id="Mesaj" class="form-control no-resize" placeholder="Type your message..."
                                          rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-success waves-effect" id="Send-Message-Button">
                                    <i class="material-icons">send</i>
                                    <span>Send</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php } else if($yazsinifad["status"] == 2) { ?>
                    <div class="panel-footer">
                        <input type="hidden" id="conversation_id"
                               value="<?php echo base64_encode($conversation_id); ?>">
                        <div class="alert alert-danger">Konuşmaya ait ilgili sınıf arşivlendiğinden dolayı mesajlaşamazsınız.</div>
                    </div>
                    <?php } ?>
                </div>

            </div>
        </div>
    </section>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {'sbmtoken': $('meta[name="sbmtoken"]').attr('content')}
            });
            var c_id = $("#conversation_id").val(), yukseklikilk, yuksekliksonra;
            (function MesajlariGetir() {
                if (c_id != "") {
                    yukseklikilk = $(".chat-history > ul").height();
                    $.ajax(
                        {
                            url: "get-messages-" + c_id,
                            type: "POST",
                            contentType: false,
                            cache: false,
                            processData: false,
                            dataType: 'json',
                            success: function (data) {
                                $(".chat-history > ul").html(data.mesaj);
                            },
                            complete: function () {
                                yuksekliksonra = $(".chat-history > ul").height();
                                if (yukseklikilk < yuksekliksonra) {
                                    $('.chat-history').stop().animate({
                                        scrollTop: $('.chat-history')[0].scrollHeight
                                    }, 800);
                                }
                                setTimeout(MesajlariGetir, 5000);
                            }
                        });
                }
            })();
            $("#Mesaj").keyup(function (e) {
                var message = $.trim($("#Mesaj").val()),
                    conversation_id = $.trim($("#conversation_id").val()),
                    user_form = $.trim($("#user_form").val()),
                    user_to = $.trim($("#user_to").val());
                if ((message != "") && (conversation_id != "") && (user_form != "") && (user_to != "")) {
                    if (e.keyCode == 13) {
                        $("#Send-Message-Button").click();
                    }
                }
            });
            $("#Send-Message-Button").on("click", function () {
                var message = $.trim($("#Mesaj").val()),
                    conversation_id = $.trim($("#conversation_id").val()),
                    user_form = $.trim($("#user_form").val()),
                    user_to = $.trim($("#user_to").val());
                if ((message != "") && (conversation_id != "") && (user_form != "") && (user_to != "")) {
                    $.post("send-message", {
                        message: message,
                        conversation_id: conversation_id,
                        user_form: user_form,
                        user_to: user_to
                    }, function (data) {
                        yukseklikilk = $(".chat-history > ul").height();
                        $.ajax(
                            {
                                url: "get-messages-" + c_id,
                                type: "POST",
                                contentType: false,
                                cache: false,
                                processData: false,
                                dataType: 'json',
                                success: function (data) {
                                    $(".chat-history > ul").html(data.mesaj);
                                },
                                complete: function () {
                                    yuksekliksonra = $(".chat-history > ul").height();
                                    if (yukseklikilk < yuksekliksonra) {
                                        $('.chat-history').stop().animate({
                                            scrollTop: $('.chat-history')[0].scrollHeight
                                        }, 800);
                                    }
                                }
                            });
                        $("#Mesaj").val("");
                    });
                }
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "conversations") {
    if ($uyerol != "admin") {
        ?>
        <section class="content">
            <div class="container-fluid">
                <div class="row clearfix">
                    <?php
                    $sorguKonusmalar = $DB_con->prepare("SELECT conversations.*,MAX(messages.sent) AS sonMesaja FROM conversations JOIN messages ON conversations.id = messages.conversation WHERE ((first = :ben1) OR (second = :ben2)) AND (first = :ben3 AND first_deleted != 1) OR (second = :ben4 AND second_deleted != 1) GROUP BY conversations.id ORDER BY sonMesaja DESC");
                    $sorguKonusmalar->bindValue(':ben1', $uyevtid, PDO::PARAM_INT);
                    $sorguKonusmalar->bindValue(':ben2', $uyevtid, PDO::PARAM_INT);
                    $sorguKonusmalar->bindValue(':ben3', $uyevtid, PDO::PARAM_INT);
                    $sorguKonusmalar->bindValue(':ben4', $uyevtid, PDO::PARAM_INT);
                    $sorguKonusmalar->execute();
                    if ($sorguKonusmalar->rowCount() > 0) {
                        ?>
                        <div class="panel panel-default panel-post">
                            <div class="panel-heading">
                                <h4>Conversation(s):</h4>
                            </div>
                            <?php
                            while ($yazKonusmalar = $sorguKonusmalar->fetch(PDO::FETCH_ASSOC)) {
                                if ($yazKonusmalar["first"] != $uyevtid) {
                                    $sorguUye = $DB_con->prepare("SELECT id,name,avatar,role FROM users WHERE id = :birincix");
                                    $sorguUye->execute(array(":birincix" => $yazKonusmalar["first"]));
                                    $yazUye = $sorguUye->fetch(PDO::FETCH_ASSOC);
                                    $mesajlink = '<a href="messages-' . seo($yazKonusmalar["class_name"]) . '-' . $yazKonusmalar["class_id"] . '-' . $yazKonusmalar["first"] . '-' . $yazKonusmalar["id"] . '" role="button" class="btn bg-orange btn-circle waves-effect waves-circle waves-float"><i class="material-icons">forum</i></a>';
                                    $mesajlink2 = '<a href="messages-' . seo($yazKonusmalar["class_name"]) . '-' . $yazKonusmalar["class_id"] . '-' . $yazKonusmalar["first"] . '-' . $yazKonusmalar["id"] . '"><img src="' . $yazUye["avatar"] . '"></a>';
                                    $mesajlink3 = '<a href="messages-' . seo($yazKonusmalar["class_name"]) . '-' . $yazKonusmalar["class_id"] . '-' . $yazKonusmalar["first"] . '-' . $yazKonusmalar["id"] . '">' . $yazUye["name"] . ' <small>(' . ($yazUye["role"] == "teacher" ? "Teacher" : ($yazUye["role"] == "student" ? "Student" : "")) . ')</small></a>';

                                } else if ($yazKonusmalar["second"] != $uyevtid) {
                                    $sorguUye = $DB_con->prepare("SELECT id,name,avatar,role FROM users WHERE id = :ikincix");
                                    $sorguUye->execute(array(":ikincix" => $yazKonusmalar["second"]));
                                    $yazUye = $sorguUye->fetch(PDO::FETCH_ASSOC);
                                    $mesajlink = '<a href="messages-' . seo($yazKonusmalar["class_name"]) . '-' . $yazKonusmalar["class_id"] . '-' . $yazKonusmalar["second"] . '-' . $yazKonusmalar["id"] . '" role="button" class="btn bg-orange btn-circle waves-effect waves-circle waves-float"><i class="material-icons">forum</i></a>';
                                    $mesajlink2 = '<a href="messages-' . seo($yazKonusmalar["class_name"]) . '-' . $yazKonusmalar["class_id"] . '-' . $yazKonusmalar["second"] . '-' . $yazKonusmalar["id"] . '"><img src="' . $yazUye["avatar"] . '"></a>';
                                    $mesajlink3 = '<a href="messages-' . seo($yazKonusmalar["class_name"]) . '-' . $yazKonusmalar["class_id"] . '-' . $yazKonusmalar["second"] . '-' . $yazKonusmalar["id"] . '">' . $yazUye["name"] . ' <small>(' . ($yazUye["role"] == "teacher" ? "Teacher" : ($yazUye["role"] == "student" ? "Student" : "")) . ')</small></a>';
                                }
                                ?>
                                <div class="panel-heading">
                                    <div class="media">
                                        <div class="media-left">
                                            <?= $mesajlink2 ?>
                                        </div>
                                        <div class="media-body">
                                            <h4 class="media-heading">
                                                <?= $mesajlink3 ?>
                                            </h4>
                                            Conversation for: <b><?= $yazKonusmalar["class_name"] ?></b>
                                            <?php
                                            $sorguMesajx = $DB_con->prepare("SELECT id FROM messages WHERE user_to = :uyeto AND conversation = :konusmaidxd AND seen IS NULL");
                                            $sorguMesajx->bindValue(":uyeto", $uyevtid, PDO::PARAM_INT);
                                            $sorguMesajx->bindValue(":konusmaidxd", $yazKonusmalar["id"], PDO::PARAM_INT);
                                            $sorguMesajx->execute();
                                            if ($sorguMesajx->rowCount() > 0) {
                                                ?>
                                                <br>
                                                <strong class="yanipsonen col-light-green"><?= $sorguMesajx->rowCount() ?>
                                                    new message!</strong>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="media-right">
                                            <?= $mesajlink ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class='notice notice-danger'>Henüz sisteme kayıtlı aktif konuşmanız bulunamadı.</div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </section>
        <script src="plugins/jquery/jquery.min.js"></script>
        <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
        <script src="plugins/node-waves/waves.min.js"></script>
        <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
        <script src="js/main.js"></script>
        </body>
        </html>
        <?php
    } else {
        echo "Forbidden";
        exit();
    }
} else if ($page_request == "report") {
    $sinifid = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($sinifid === false) {
        echo 404;
        exit();
    }
    $ogrenciid = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);
    if ($ogrenciid === false) {
        echo 404;
        exit();
    }
    if ($uyerol == "student") {
        echo "Forbidden";
        exit();
    }
    $sorguogrencixd = $DB_con->prepare("SELECT name,avatar,email FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND role = :role AND schools = :school");
    $sorguogrencixd->execute(array(":id" => $ogrenciid, ":sid" => $sinifid, ":role" => "student", ":school" => $uyeokul));
    if ($sorguogrencixd->rowCount() != 1) {
        echo 404;
        exit();
    }
    $sorgusinifidxd = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND school = :school");
    $sorgusinifidxd->execute(array(":id" => $sinifid, ":school" => $uyeokul));
    if ($sorgusinifidxd->rowCount() != 1) {
        echo 404;
        exit();
    }
    $yazogrencix = $sorguogrencixd->fetch(PDO::FETCH_ASSOC);
    $sorguokulad = $DB_con->prepare("SELECT name FROM schools WHERE id = :id");
    $sorguokulad->execute(array(":id" => $uyeokul));
    $yazokulad = $sorguokulad->fetch(PDO::FETCH_ASSOC);
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">
                        <div class="panel panel-default panel-post">
                            <div class="panel-heading">
                                <h4>Raporu görüntülenen öğrenci:</h4>
                            </div>
                            <div class="panel-heading">
                                <div class="media">
                                    <div class="media-left">
                                        <a href="javascript:;" data-toggle="modal" data-target="#modal-student" class="ogrenci-puanla"
                                           id="<?= $ogrenciid ?>" class_id="<?= $sinifid ?>"><img src="<?= $yazogrencix["avatar"] ?>"></a>
                                    </div>
                                    <div class="media-body">
                                        <h4 class="media-heading">
                                            <a href="javascript:;" data-toggle="modal" data-target="#modal-student" class="ogrenci-puanla"
                                               id="<?= $ogrenciid ?>" class_id="<?= $sinifid ?>"><?= $yazogrencix["name"] ?></a><br>
                                            <small><?= $yazogrencix["email"] ?></small>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-heading">
                                <div class="media">
                                    <div class="media-body">
                                        <h4 class="media-heading">
                                            School: <a href="javascript:;"><?= $yazokulad["name"] ?></a>
                                        </h4>
                                        <h4 class="media-heading m-t-10 p-b-5">
                                            Class(es):
                                        </h4>
                                        <div class="row">
                                            <?php
                                            $sorguogrencisinifs = $DB_con->prepare("SELECT classes.id,classes.name FROM users INNER JOIN classes ON FIND_IN_SET(classes.id, users.classes) WHERE users.id = :student AND role = :role AND schools = :school ORDER BY classes.id ASC");
                                            $sorguogrencisinifs->execute(array(":student" => $ogrenciid, ":role" => "student", ":school" => $uyeokul));
                                            while ($yazogrencisinifs = $sorguogrencisinifs->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <input type="checkbox" id="class_<?= $yazogrencisinifs["id"] ?>"
                                                               class="filled-in chk-col-orange classCheckBox"
                                                               data-class-id="<?= $yazogrencisinifs["id"] ?>"
                                                               value="<?= $yazogrencisinifs["id"] ?>" <?php if ($sinifid == $yazogrencisinifs["id"]) {
                                                            echo "checked";
                                                        } ?>>
                                                        <label for="class_<?= $yazogrencisinifs["id"] ?>"><?= $yazogrencisinifs["name"] ?></label>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <h4 class="media-heading m-t-10 p-b-5">
                                            Filter by time:
                                        </h4>
                                        <select class="form-control" id="timefilter" name="timefilter">
                                            <option value="0">All time</option>
                                            <option value="1">Today</option>
                                            <option value="2">Yesterday</option>
                                            <option value="3">This week</option>
                                            <option value="4">Last week</option>
                                            <option value="5">This month (<?= date("F") ?>)</option>
                                            <option value="6">Last month (<?= date("F", strtotime('-1 month')) ?>)
                                            </option>
                                            <option value="7">Custom date range</option>
                                        </select>
                                        <div class="custom-range-filter m-t-15" style="display:none;">
                                            <h4 class="media-heading p-b-5">
                                                Custom date range:
                                            </h4>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="material-icons">date_range</i>
                                                        </span>
                                                        <div class="form-line">
                                                            <input type="text" class="form-control date1">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="material-icons">compare_arrows</i>
                                                        </span>
                                                        <div class="form-line">
                                                            <input type="text" class="form-control date2">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <button type="button"
                                                            class="btn btn-success btn-block btn-sm waves-effect applytimefilter">
                                                        Apply
                                                    </button>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="apply-alert"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="studentReportContent"></div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8 col-xl-8" id="studentReportContent2"></div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade in" id="modal-student" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-student-content">

            </div>
        </div>
    </div>
    <div class="modal fade in" id="modal-send-mail-to-parent" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-send-mail-to-parent-content">

            </div>
        </div>
    </div>
    <div class="modal fade in" id="modal-send-message" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-send-message-content">

            </div>
        </div>
    </div>
    <div class="modal fade in" id="modal-edit-student" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-edit-student-content">

            </div>
        </div>
    </div>
    <div class="modal fade in" id="modal-edit-behavior" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-edit-behavior-content">

            </div>
        </div>
    </div>
    <div class="modal fade in" id="modal-add-behavior" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Behavior</h4>
                </div>
                <div class="modal-body">
                    <form id="Add-Behavior-Form">
                        <div class="form-group">
                            <label for="name">Behavior Name:</label>
                            <div class="form-line">
                                <input class="form-control" name="name" id="name" placeholder="Behavior name..."
                                       type="text">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="type">Behavior Type:</label>
                            <select class="form-control" name="type" id="type">
                                <option value="0">Choose...</option>
                                <option value="1">Positive</option>
                                <option value="2">Negative</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="point">Behavior Point(only number):</label>
                            <div class="form-line">
                                <input class="form-control" name="point" id="point" placeholder="Behavior point..."
                                       type="text">
                            </div>
                        </div>
                        <input type="hidden" name="hidden_student_id" id="hidden_student_id">
                        <input type="hidden" name="hidden_class_id" id="hidden_class_id">
                        <div id="Add-Behavior-Result"></div>
                        <div class="form-group">
                            <button type="submit"
                                    class="btn btn-primary btn-block btn-lg waves-effect Add-Behavior-Button">Add
                                Behavior
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script src="https://cdn.jsdelivr.net/npm/promise-polyfill@7.1.0/dist/promise.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="plugins/jquery-sparkline/jquery.sparkline.js"></script>
    <script src="plugins/jquery-inputmask/jquery.inputmask.bundle.min.js"></script>
    <script src="plugins/jquery-datatable/jquery.dataTables.min.js"></script>
    <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.min.js"></script>
    <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.responsive.min.js"></script>
    <script src="plugins/jquery-datatable/skin/bootstrap/js/responsive.bootstrap.min.js"></script>
    <script src="plugins/jquery-datepicker/datepicker.min.js"></script>
    <script src="//cdn.ckeditor.com/4.13.0/basic/ckeditor.js"></script>
    <script src="js/student-point-actions.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.date1').datepicker({
                format: 'yyyy-mm-dd'
            });
            $('.date2').datepicker({
                format: 'yyyy-mm-dd'
            });
            $.ajaxSetup({
                headers: {'sbmtoken': $('meta[name="sbmtoken"]').attr('content')}
            });
            $.ajax(
                {
                    url: "get-report-<?=$ogrenciid?>?classes=<?=$sinifid?>",
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData: false,
                    dataType: 'json',
                    success: function (data) {

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
                });
            $('.custom-range-filter').find('.date1').inputmask('yyyy-mm-dd', {
                placeholder: '____-__-__',
                clearIncomplete: true
            });
            $('.custom-range-filter').find('.date2').inputmask('yyyy-mm-dd', {
                placeholder: '____-__-__',
                clearIncomplete: true
            });
            $('body').on('click', '.classCheckBox', function (e) {
                var idSelector = function () {
                    return $(this).data("class-id");
                };
                var checkedClasses = $("input[type='checkbox'].classCheckBox:checked").map(idSelector).get();
                var timefilter = $("select#timefilter").val();
                var regexp = /[^0-9]/g;
                var date1 = $("input.date1").val();
                var date2 = $("input.date2").val();
                $.ajax(
                    {
                        url: "get-report-<?=$ogrenciid?>?classes=" + checkedClasses + "&timefilter=" + timefilter.replace(regexp, '') + "&date1=" + date1 + "&date2=" + date2,
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
            });
            $('body').on("change", 'select#timefilter', function (event) {
                if ($("select#timefilter").val() === "7") {
                    $('.custom-range-filter').show();
                    return false;
                } else {
                    if ($('.custom-range-filter').is(":visible")) {
                        $('.custom-range-filter').hide();
                    }
                }
                var idSelector2 = function () {
                    return $(this).data("class-id");
                };
                var checkedClasses2 = $("input[type='checkbox'].classCheckBox:checked").map(idSelector2).get();
                var timefilter = $("select#timefilter").val();
                var regexp = /[^0-9]/g;
                $.ajax(
                    {
                        url: "get-report-<?=$ogrenciid?>?classes=" + checkedClasses2 + "&timefilter=" + timefilter.replace(regexp, ''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        dataType: 'json',
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $('.page-loader-wrapper').fadeOut();
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
            });
            $('body').on("click", '.applytimefilter', function (event) {
                if ($("select#timefilter").val() !== "7") return false;
                $('#apply-alert').html("");
                if ($("input.date1").val().length === 0 || $("input.date2").val().length === 0) {
                    $('#apply-alert').html("<div class='alert alert-danger m-t-10'>Lütfen filtrelemek istediğiniz zaman aralığını eksiksiz doldurunuz.</div>");
                    return false;
                }
                var idSelector3 = function () {
                    return $(this).data("class-id");
                };
                var checkedClasses3 = $("input[type='checkbox'].classCheckBox:checked").map(idSelector3).get();
                var timefilter = $("select#timefilter").val();
                var regexp = /[^0-9]/g;
                var date1 = $("input.date1").val();
                var date2 = $("input.date2").val();
                $.ajax(
                    {
                        url: "get-report-<?=$ogrenciid?>?classes=" + checkedClasses3 + "&timefilter=" + timefilter.replace(regexp, '') + "&date1=" + date1 + "&date2=" + date2,
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        dataType: 'json',
                        beforeSend: function () {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function (data) {
                            $('.page-loader-wrapper').fadeOut();
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
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "send-messages") {
    if ($uyerol != "teacher") {
        echo "Forbidden";
        exit();
    }
    if (isset($_GET["id"])) {
        $sinifid = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($sinifid === false) {
            echo 404;
            exit();
        }
        $sorgusinifid = $DB_con->prepare("SELECT id,name FROM classes WHERE FIND_IN_SET(:uyeid, teachers) AND school = :school AND id = :id");
        $sorgusinifid->execute(array(":uyeid" => $uyevtid, ":school" => $uyeokul, ":id" => $sinifid));
        if ($sorgusinifid->rowCount() != 1) {
            echo 404;
            exit();
        }
        $yazsinifad = $sorgusinifid->fetch(PDO::FETCH_ASSOC);
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="header">
                                <h4>Send Messages to Parents</h4>
                            </div>
                            <a href="home" class="btn btn-default btn-block btn-lg waves-effect"><i
                                        class="material-icons">arrow_back</i><span>Go Back</span></a>
                            <div class="body">
                                <div class="row">
                                <form id="Send-Messages-Form">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">

                                    <?php
                                    if (isset($_GET["id"])) {
                                        ?>
                                        <div class="form-group p-b-10">
                                            <label>Class: <u><?= $yazsinifad["name"] ?></u></label>
                                        </div>
                                        <?php
                                    } else if (!isset($_GET["id"])) {
                                        echo "<label>Class:</label>";
                                        $sorgusinifsogrt = $DB_con->prepare("SELECT id,name FROM classes WHERE FIND_IN_SET(:uyeid, teachers) AND school = :school AND status = :status");
                                        $sorgusinifsogrt->execute(array(":uyeid" => $uyevtid, ":school" => $uyeokul, ":status" => 1));
                                        if ($sorgusinifsogrt->rowCount() > 0) {
                                            echo '<div class="row">';
                                            while ($yazsinifsogrt = $sorgusinifsogrt->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <input type="checkbox" id="class_<?= $yazsinifsogrt["id"] ?>"
                                                               data-class-id="<?= $yazsinifsogrt["id"] ?>"
                                                               name="classes[]"
                                                               class="filled-in chk-col-orange classCheckBox"
                                                               value="<?= $yazsinifsogrt["id"] ?>">
                                                        <label for="class_<?= $yazsinifsogrt["id"] ?>"><?= $yazsinifsogrt["name"] ?></label>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            echo '</div>';
                                        } else {
                                            echo '<div class="alert alert-danger">Henüz sistemde öğretmeni olarak göründüğünüz sınıf bulunmamakta.</div>';
                                        }
                                    }
                                    echo '<label>Student(s):</label>';
                                    if (isset($_GET["id"])) {
                                        $sorgu = $DB_con->prepare("SELECT id,name FROM users WHERE FIND_IN_SET(:sinifid, classes) AND role = :role AND schools = :school AND (parent_email <> :bos OR parent_email2 <> :bos2)");
                                        $sorgu->execute(array(":sinifid" => $sinifid, ":role" => "student", ":school" => $uyeokul, ":bos" => "", ":bos2" => ""));
                                        if ($sorgu->rowCount() > 0) {
                                            echo '<div class="row">';
                                            while ($yazogrenciler = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <input type="checkbox" id="student_<?= $yazogrenciler["id"] ?>"
                                                               name="students[]"
                                                               class="filled-in chk-col-orange studentCheckBox"
                                                               data-student-id="<?= $yazogrenciler["id"] ?>"
                                                               value="<?= $yazogrenciler["id"] ?>">
                                                        <label for="student_<?= $yazogrenciler["id"] ?>"><?= $yazogrenciler["name"] ?></label>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            echo '</div>';
                                        } else {
                                            echo '<div class="alert alert-danger"><strong>' . $yazsinifad["name"] . '</strong> adlı sınıfa ait öğrenci bulunamadı veya hiç bir öğrencinin veli e-posta bilgisi mevcut değil.</div>';
                                        }
                                    } else if (!isset($_GET["id"])) {
                                        echo '<div id="class_students"><div class="alert alert-info">Henüz herhangi bir sınıf seçmediniz.</div></div>';
                                    }
                                    ?>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                        <div class="form-group p-b-10">
                                            <label>Message Template:</label>
                                            <select class="form-control" id="message_template" name="message_template">
                                                <option value="0">Choose</option>
                                                <?php
                                                $sorgusablon = $DB_con->prepare("SELECT id,name,text FROM message_templates WHERE user_id = :uye");
                                                $sorgusablon->execute(array(":uye" => $uyevtid));
                                                while ($yazsablon = $sorgusablon->fetch(PDO::FETCH_ASSOC)) {
                                                    ?>
                                                    <option value="<?= $yazsablon["id"] ?>"><?= $yazsablon["name"] ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                            <small><a href="javascript:;" data-toggle="modal"
                                                      data-target="#createNewTemplateModal">Create New Template</a> | <a
                                                        href="javascript:;" data-toggle="modal"
                                                        data-target="#manageMessageTemplatesModal"
                                                        class="manageMessageTemplatesButton">Manage Message Templates</a>
                                            </small>
                                        </div>
                                        <div class="form-group p-b-10">
                                            <label>Message Template:</label>
                                            <br>
                                            <small>Available variables: <strong>{{studentName}}</strong></small>
                                            <textarea name="message" id="message"></textarea>
                                        </div>
                                        <input type="hidden" name="checkedStudents" id="checkedStudents">
                                        <div id="sendButtonPlace">
                                        <?php
                                        if (isset($_GET["id"])) {
                                            if ($sorgu->rowCount() > 0) {
                                                ?>
                                                <div class="form-group">
                                                    <button type="submit"
                                                            class="btn btn-success btn-block btn-lg waves-effect Send-Messages-Button">
                                                        Send
                                                    </button>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                        </div>
                                        <div id="Send-Messages-Result"></div>
                                    </div>
                                </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade in" id="createNewTemplateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Create New Message Template</h4>
                </div>
                <div class="modal-body">
                    <form id="createNewTemplateForm">
                        <div class="form-group">
                            <label for="name">Template Name:</label>
                            <div class="form-line">
                                <input class="form-control" name="name" id="name" type="text">
                            </div>
                        </div>
                        <div class="form-group">
                            <textarea name="template" id="template"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit"
                                    class="btn btn-primary btn-block btn-lg waves-effect createNewTemplateButton">Create
                                This Template
                            </button>
                        </div>
                    </form>
                    <div id="createNewTemplateResult"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade in" id="manageMessageTemplatesModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content manageMessageTemplatesContent">

            </div>
        </div>
    </div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="//cdn.ckeditor.com/4.13.0/basic/ckeditor.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        function loadTemplates() {
            $.ajax({
                type: "POST",
                url: "get-message-templates",
                data: "type=0",
                dataType: 'json',
            }).done(function (result) {
                $.each(result, function (order, object) {
                    key = object.id;
                    value = object.name;
                    $('#message_template').append($('<option>', {value: key}).text(value));
                });
            });
        }

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

        $(document).ready(function () {
            CKEDITOR.replace('message');
            CKEDITOR.replace('template');

            function CKUpdate() {
                for (instance in CKEDITOR.instances)
                    CKEDITOR.instances[instance].updateElement();
            }

            $("select#message_template").bind("change", function () {
                loadTemplateText($(this).find(':selected').val());
            });
            $('body').on('submit', '#createNewTemplateForm', function (e) {
                CKUpdate();
                e.preventDefault();

                $('.createNewTemplateButton').prop('disabled', true);
                $('.createNewTemplateButton').html("Template Creating...");

                $("#createNewTemplateResult").empty();
                $.ajax(
                    {
                        url: "create-template",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $('.createNewTemplateButton').prop('disabled', false);
                                $('.createNewTemplateButton').html("Create This Template");
                                if (data == 0) {
                                    $("#createNewTemplateResult").html("<div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div>");
                                }
                                if (data == 1) {
                                    $("#createNewTemplateResult").html("<div class='alert alert-success'><strong>Successful!</strong> Message template successfully created.</div>");
                                    $("#createNewTemplateForm").trigger("reset");
                                    CKEDITOR.instances.template.setData('');
                                    $('#message_template').find('option').remove().end().append('<option value="0">Choose</option>').val('0');
                                    loadTemplates();
                                }
                                if (data == 2) {
                                    $("#createNewTemplateResult").html("<div class='alert alert-danger'><strong>Error:</strong> Please fill in the form completely.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
            $('body').on('click', '.manageMessageTemplatesButton', function (e) {
                e.preventDefault();
                $.ajax(
                    {
                        url: "manage-message-templates",
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            if (data == 0) {
                                $(".manageMessageTemplatesContent").html("<div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div>");
                            } else {
                                $(".manageMessageTemplatesContent").html(data);
                            }
                        }
                    });
            });
            $('body').on('click', '.classCheckBox', function (e) {
                var idSelector = function () {
                    return $(this).data("class-id");
                };
                var checkedClasses = $("input[type='checkbox'].classCheckBox:checked").map(idSelector).get();
                $.ajax(
                    {
                        url: "get-class-students?classes=" + checkedClasses,
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            if (data == 0) {
                                $("#class_students").html("<div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div>");
                            } else {
                                $("#class_students").html(data);
                                $("input[type='hidden']#checkedStudents").val("");
                                /*
                                var getStudents = $("input[type='hidden']#checkedStudents").val().split(", ");
                                for (var j = 0; j < getStudents.length; j++) {
                                    $("input[type='checkbox']#student_"+getStudents[j]).prop('checked', true);
                                }
                                */
                            }
                        }
                    });
            });
            $('body').on('click', '.studentCheckBox', function (e) {
                var idSelectorStudent = function () {
                    return $(this).data("student-id");
                };
                var checkedStudents = $("input[type='checkbox'].studentCheckBox:checked").map(idSelectorStudent).get();
                $("input[type='hidden']#checkedStudents").val(checkedStudents);
            });
            $('body').on('click', '.editMessageTemplateButton', function (e) {
                CKUpdate();
                e.preventDefault();

                var templateId = $(this).attr("id");

                $(".editMessageTemplateButton#" + templateId).prop('disabled', true);
                $(".editMessageTemplateButton#" + templateId).html("Editing...");

                $("#manageMessageTemplatesResult_" + templateId).empty();
                $.ajax(
                    {
                        url: "edit-template",
                        type: "POST",
                        data: new FormData(document.querySelector('#editMessageTemplate_' + templateId)),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            setTimeout(function () {
                                $(".editMessageTemplateButton#" + templateId).prop('disabled', false);
                                $(".editMessageTemplateButton#" + templateId).html("Edit");
                                if (data == 0) {
                                    $("#manageMessageTemplatesResult_" + templateId).html("<div class='alert alert-danger m-t-5'><strong>Error:</strong> There was a technical problem. Please try again.</div>");
                                }
                                if (data == 1) {
                                    $("#manageMessageTemplatesResult_" + templateId).html("<div class='alert alert-success m-t-5'><strong>Successful!</strong> Message template successfully edited.</div>");
                                    CKEDITOR.instances.message.setData('');
                                    $('#message_template').find('option').remove().end().append('<option value="0">Choose</option>').val('0');
                                    loadTemplates();
                                    $("#templateName_" + templateId).text($("#name_" + templateId).val());
                                }
                                if (data == 2) {
                                    $("#manageMessageTemplatesResult_" + templateId).html("<div class='alert alert-danger m-t-5'><strong>Error:</strong> Please fill in the form completely.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
            $('body').on('click', '.deleteMessageTemplateButton', function (e) {
                e.preventDefault();

                var templateId = $(this).attr("id");

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
                                    url: 'delete-template',
                                    data: 'template_id=' + templateId,
                                    success: function (data) {
                                        if (data == 1) {
                                            swal(
                                                {
                                                    title: "Deleted!",
                                                    text: "Message template has been deleted.",
                                                    type: "success",
                                                    confirmButtonText: "OK",
                                                    closeOnConfirm: true
                                                });
                                            CKEDITOR.instances.message.setData('');
                                            $('#message_template').find('option').remove().end().append('<option value="0">Choose</option>').val('0');
                                            loadTemplates();
                                            $("#templateName_" + templateId).text($("#name_" + templateId).val());
                                            $.ajax(
                                                {
                                                    url: "manage-message-templates",
                                                    type: "GET",
                                                    contentType: false,
                                                    cache: false,
                                                    processData: false,
                                                    success: function (data) {
                                                        if (data == 0) {
                                                            $(".manageMessageTemplatesContent").html("<div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div>");
                                                        } else {
                                                            $(".manageMessageTemplatesContent").html(data);
                                                        }
                                                    }
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
            $('body').on('submit', '#Send-Messages-Form', function (e) {
                e.preventDefault();
                CKUpdate();
                $('.Send-Messages-Button').prop('disabled', true);
                $('.Send-Messages-Button').html("Sending...");

                $("#Send-Messages-Result").empty();
                $.ajax(
                    {
                        url: "send-messages-a",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                $('.Send-Messages-Button').prop('disabled', false);
                                $('.Send-Messages-Button').html("Send");
                                if (data.sonuc == 0) {
                                    $("#Send-Messages-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if (data.sonuc == 1) {
                                    $("#Send-Messages-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Toplam " + data.sentMessageCount + " öğrencinin velilerine başarıyla e-posta gönderildi.</div>");
                                    $("#Send-Messages-Form").trigger("reset");
                                    CKEDITOR.instances.message.setData('');
                                }
                                if (data.sonuc == 2) {
                                    $("#Send-Messages-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen e-posta içeriğini doldurunuz.</div>");
                                }
                                if (data.sonuc == 3) {
                                    $("#Send-Messages-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen öğrenci seçiniz.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "stats") {
    if ($uyerol != "admin") {
        echo "Forbidden";
        exit();
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-lg-12 col-md-6 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="header">
                                <h2>Filter statistics</h2>
                            </div>
                            <div class="body">
                                <select class="form-control" id="timefilter" name="timefilter">
                                    <option value="0">All time</option>
                                    <option value="1">Today</option>
                                    <option value="2">Yesterday</option>
                                    <option value="3">This week</option>
                                    <option value="4">Last week</option>
                                    <option value="5">This month (<?= date("F") ?>)</option>
                                    <option value="6">Last month (<?= date("F", strtotime('-1 month')) ?>)
                                    </option>
                                    <option value="7">Custom date range</option>
                                </select>
                                <div class="custom-range-filter m-t-15" style="display:none;">
                                    <h4 class="media-heading p-b-5">
                                        Custom date range:
                                    </h4>
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                            <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="material-icons">date_range</i>
                                                        </span>
                                                <div class="form-line">
                                                    <input type="text" class="form-control date1">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                            <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="material-icons">compare_arrows</i>
                                                        </span>
                                                <div class="form-line">
                                                    <input type="text" class="form-control date2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                            <button type="button"
                                                    class="btn btn-success btn-block btn-sm waves-effect applytimefilter">
                                                Apply
                                            </button>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                            <div id="apply-alert"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="header">
                                <h2>All behavior points of school</h2>
                                <ul class="header-dropdown m-r--5" style="top:15px!important">
                                    <li class="dropdown">
                                        <a href="javascript:void(0);" class="btn btn-default btn-xs" role="button" id="toggleChartSize" data-chart-id="total_behaviors_chart" data-chart-title="All behavior points of school">
                                            <i class="material-icons">fullscreen</i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <canvas id="total_behaviors_chart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="header">
                                <h2>Most given positive behavior of school</h2>
                                <ul class="header-dropdown m-r--5" style="top:15px!important">
                                    <li class="dropdown">
                                        <a href="javascript:void(0);" class="btn btn-default btn-xs" role="button" id="toggleChartSize" data-chart-id="most_positive_behaviors_chart" data-chart-title="Most given positive behavior of school">
                                            <i class="material-icons">fullscreen</i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <canvas id="most_positive_behaviors_chart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="header">
                                <h2>Top best students of school</h2>
                                <ul class="header-dropdown m-r--5" style="top:15px!important">
                                    <li class="dropdown">
                                        <a href="javascript:void(0);" class="btn btn-default btn-xs" role="button" id="toggleChartSize" data-chart-id="top_best_students" data-chart-title="Top best students of school">
                                            <i class="material-icons">fullscreen</i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <canvas id="top_best_students" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="header">
                                <h2>Top worst students of school</h2>
                                <ul class="header-dropdown m-r--5" style="top:15px!important">
                                    <li class="dropdown">
                                        <a href="javascript:void(0);" class="btn btn-default btn-xs" role="button" id="toggleChartSize" data-chart-id="top_worst_students" data-chart-title="Top worst students of school">
                                            <i class="material-icons">fullscreen</i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <canvas id="top_worst_students" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="header">
                                <h2>Points of the school by location</h2>
                                <ul class="header-dropdown m-r--5" style="top:15px!important">
                                    <li class="dropdown">
                                        <a href="javascript:void(0);" class="btn btn-default btn-xs" role="button" id="toggleChartSize" data-chart-id="point_location_chart" data-chart-title="Points of the school by location">
                                            <i class="material-icons">fullscreen</i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <canvas id="point_location_chart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="header">
                                <h2>Most pointer teachers of school</h2>
                                <ul class="header-dropdown m-r--5" style="top:15px!important">
                                    <li class="dropdown">
                                        <a href="javascript:void(0);" class="btn btn-default btn-xs" role="button" id="toggleChartSize" data-chart-id="most_pointer_teachers" data-chart-title="Most pointer teachers of school">
                                            <i class="material-icons">fullscreen</i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <canvas id="most_pointer_teachers" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="header">
                                <h2>Points of school by day</h2>
                                <ul class="header-dropdown m-r--5" style="top:15px!important">
                                    <li class="dropdown">
                                        <a href="javascript:void(0);" class="btn btn-default btn-xs" role="button" id="toggleChartSize" data-chart-id="points_by_day_chart" data-chart-title="Points of school by day">
                                            <i class="material-icons">fullscreen</i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <canvas id="points_by_day_chart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="header">
                                <h2>Points of school by hour</h2>
                                <ul class="header-dropdown m-r--5" style="top:15px!important">
                                    <li class="dropdown">
                                        <a href="javascript:void(0);" class="btn btn-default btn-xs" role="button" id="toggleChartSize" data-chart-id="points_by_hour_chart" data-chart-title="Points of school by hour">
                                            <i class="material-icons">fullscreen</i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <canvas id="points_by_hour_chart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="lightbox">
        <div class="card" style="height:100%;box-shadow:none;margin-bottom:0!important;">
            <div class="header">
                <h2 id="chartName"></h2>
                <ul class="header-dropdown m-r--5" style="top:15px!important">
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="btn btn-default btn-xs" role="button" id="closeChartFullScreen">
                            <i class="material-icons">fullscreen_exit</i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="body" id="fullScreenChart">

            </div>
        </div>
    </div>
    <input type="hidden" id="timeFilterQueryString" value="">
    <div id="chartsContainer"></div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="plugins/chartjs/Chart.bundle.min.js"></script>
    <script src="plugins/jquery-inputmask/jquery.inputmask.bundle.min.js"></script>
    <script src="plugins/jquery-datepicker/datepicker.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: { 'sbmtoken': $('meta[name="sbmtoken"]').attr('content') }
        });
        var chartsTimeout;
        // Chart.defaults.global.animation.duration = 0;
        function getCharts(isFirst) {
            clearTimeout(chartsTimeout);
            $.ajax(
                {
                    url: "stats-a" + $('#timeFilterQueryString').val(),
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData:false,
                    success: function(data)
                    {
                        if (data != 0) {
                            $('#chartsContainer').html(data);
                            if(isFirst == true) {
                                createChart('total_behaviors_chart');
                                createChart('most_positive_behaviors_chart');
                                createChart('top_best_students');
                                createChart('top_worst_students');
                                createChart('point_location_chart');
                                createChart('most_pointer_teachers');
                                createChart('points_by_day_chart');
                                createChart('points_by_hour_chart');
                            } else {
                                window.totalBehaviorsChart.config = getChartJs('doughnut');
                                window.totalBehaviorsChart.options.circumference = Math.PI;
                                window.totalBehaviorsChart.options.rotation = -Math.PI;
                                window.totalBehaviorsChart.update();

                                window.mostPositiveBehaviorsChart.config = getChartJs('bar');
                                window.mostPositiveBehaviorsChart.update();

                                window.topBestStudents.config = getChartJs('bar2');
                                window.topBestStudents.update();

                                window.topWorstStudents.config = getChartJs('bar3');
                                window.topWorstStudents.update();

                                window.pointLocationChart.config = getChartJs('bar4');
                                window.pointLocationChart.update();

                                window.mostPointerTeachersChart.config = getChartJs('bar5');
                                window.mostPointerTeachersChart.update();

                                window.pointsByDay.config = getChartJs('bar6');
                                window.pointsByDay.update();

                                window.pointsByHour.config = getChartJs('bar7');
                                window.pointsByHour.update();
                            }
                        } else {
                            $('#chartsContainer').html('<div class="alert alert-danger">Teknik bir hata oluştu.</div>');
                        }
                    },
                    complete: function() {
                        chartsTimeout = setTimeout(function() { getCharts(); }, 10000);
                    }
                });
        }
        $(document).ready(function() {
            getCharts(true);
            $('.date1').datepicker({
                format: 'yyyy-mm-dd'
            });
            $('.date2').datepicker({
                format: 'yyyy-mm-dd'
            });
            $('.custom-range-filter').find('.date1').inputmask('yyyy-mm-dd', {
                placeholder: '____-__-__',
                clearIncomplete: true
            });
            $('.custom-range-filter').find('.date2').inputmask('yyyy-mm-dd', {
                placeholder: '____-__-__',
                clearIncomplete: true
            });
            $('body').on('click', '#toggleChartSize', function (e) {
                e.preventDefault();
                var getChartId = $(this).data('chart-id');
                var newCanvas = $('<canvas/>',{
                    id: getChartId
                }).prop({
                    width: 150,
                });
                $('.lightbox').show();
                $('html').css('overflow','hidden');
                $(this).closest('div.card').find('div.body').html('');
                $('#chartName').html($(this).data('chart-title'));
                $('#fullScreenChart').html(newCanvas);
                createChart(getChartId);
                $('#closeChartFullScreen').attr('chart-id', getChartId);
            });
            $('body').on('click', '#closeChartFullScreen', function (e) {
                e.preventDefault();
                $('.lightbox').hide();
                $('html').css('overflow','auto');
                $('a[data-chart-id="'+$(this).attr('chart-id')+'"]').closest('div.card').find('div.body').html($('#fullScreenChart').html());
                createChart($(this).attr('chart-id'));
                $('#fullScreenChart').html('');
            });
            $('body').on("change", 'select#timefilter', function (event) {
                if ($("select#timefilter").val() === "7") {
                    $('.custom-range-filter').show();
                    return false;
                } else {
                    if ($('.custom-range-filter').is(":visible")) {
                        $('.custom-range-filter').hide();
                    }
                }
                var timefilter = $("select#timefilter").val();
                var regexp = /[^0-9]/g;
                var queryString = "?timefilter=" + timefilter.replace(regexp, '');
                $('#timeFilterQueryString').val(queryString);
                getCharts();
            });
            $('body').on("click", '.applytimefilter', function (event) {
                if ($("select#timefilter").val() !== "7") return false;
                $('#apply-alert').html("");
                if ($("input.date1").val().length === 0 || $("input.date2").val().length === 0) {
                    $('#apply-alert').html("<div class='alert alert-danger m-t-10'>Lütfen filtrelemek istediğiniz zaman aralığını eksiksiz doldurunuz.</div>");
                    return false;
                }
                var timefilter = $("select#timefilter").val();
                var regexp = /[^0-9]/g;
                var date1 = $("input.date1").val();
                var date2 = $("input.date2").val();
                var queryString = "?timefilter=" + timefilter.replace(regexp, '') + "&date1=" + date1 + "&date2=" + date2;
                $('#timeFilterQueryString').val(queryString);
                getCharts();
            });
        });
    </script>
    </body>
    </html>
    <?php
} else if ($page_request == "security") {
    if ($uyerol != "admin") {
        echo "Forbidden";
        exit();
    }
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-xs-12">
                        <div class="panel panel-default panel-post">
                            <div class="panel-heading">
                                <h4><strong>Logon Records</strong></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="form-group">
                            <select class="form-control" id="siralama2" name="siralama2">
                                <option value="0">By date (Newest entry first)</option>
                                <option value="1">By date (First oldest entry)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <strong>Status: </strong>
                        <br>
                        <input type="radio" class="with-gap radio-col-orange filtre-buton2"
                               name="filtre_durum" id="filtredurumtumu" checked="">
                        <label for="filtredurumtumu">All</label>
                        <input type="radio" class="with-gap radio-col-orange filtre-buton2"
                               name="filtre_durum" id="filtredurumbasarili">
                        <label for="filtredurumbasarili">Successful</label>
                        <input type="radio" class="with-gap radio-col-orange filtre-buton2"
                               name="filtre_durum" id="filtredurumbasarisiz">
                        <label for="filtredurumbasarisiz">Unsuccessful</label>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" id="uye-giris-kayitlari">

                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="plugins/node-waves/waves.min.js"></script>
    <script src="plugins/oldsweetalert/sweetalert.min.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {'sbmtoken': $('meta[name="sbmtoken"]').attr('content')}
            });
            $.ajax(
                {
                    url: "admin-logon-records",
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (data) {
                        $("#uye-giris-kayitlari").html(data);
                    }
                });
            $('body').on("click", '.sayfala-buton2', function (event) {
                var node = this.id;

                var regexp = /[^0-9]/g;
                var regexp2 = /[^a-z]/g;

                var siralama = $("select#siralama2").val();
                var filtre_durum = $("input[name='filtre_durum']:checked").attr("id");

                $.ajax(
                    {
                        url: "admin-logon-records?sayfa=" + node.replace(regexp, '') + "&siralama=" + siralama.replace(regexp, '') + "&filtre_durum=" + filtre_durum.replace(regexp2, ''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            $("#uye-giris-kayitlari").html(data);
                        }
                    });
            });
            $('body').on("change", 'select#siralama2', function (event) {
                var regexp = /[^0-9]/g;
                var regexp2 = /[^a-z]/g;

                var siralama = $("select#siralama2").val();
                var filtre_durum = $("input[name='filtre_durum']:checked").attr("id");

                $.ajax(
                    {
                        url: "admin-logon-records?siralama=" + siralama.replace(regexp, '') + "&filtre_durum=" + filtre_durum.replace(regexp2, ''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            $("#uye-giris-kayitlari").html(data);
                        }
                    });
            });
            $('body').on("click", '.filtre-buton2', function (event) {
                var regexp = /[^0-9]/g;
                var regexp2 = /[^a-z]/g;

                var siralama = $("select#siralama2").val();
                var filtre_durum = $("input[name='filtre_durum']:checked").attr("id");

                $.ajax(
                    {
                        url: "admin-logon-records?siralama=" + siralama.replace(regexp, '') + "&filtre_durum=" + filtre_durum.replace(regexp2, ''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            $("#uye-giris-kayitlari").html(data);
                        }
                    });
            });
        });
    </script>
    </body>
    </html>
    <?php
}
?>