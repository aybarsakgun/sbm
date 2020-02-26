<?php
define('VAL1', TRUE);
require_once("top.php");
if(!isset($page_request))
{
	$sorguokullar = $DB_con->prepare("SELECT id FROM schools");
	$sorguokullar->execute();
	$okul_say = $sorguokullar->rowCount();
	$sorguogretmenler = $DB_con->prepare("SELECT id FROM users WHERE role = :role");
	$sorguogretmenler->execute(array(":role"=>"teacher"));
	$ogretmen_say = $sorguogretmenler->rowCount();
	$sorguogrenciler = $DB_con->prepare("SELECT id FROM users WHERE role = :role");
	$sorguogrenciler->execute(array(":role"=>"student"));
	$ogrenci_say = $sorguogrenciler->rowCount();
    $sorgusiniflar = $DB_con->prepare("SELECT id FROM classes");
    $sorgusiniflar->execute();
    $sinif_say = $sorgusiniflar->rowCount();
?>
		<section class="content">
			<div class="container-fluid">
				<div class="row clearfix">
					<div class="block-header">
						<div class="gri">
							<ol class="breadcrumb">
								<li class="active">
									<i class="material-icons">home</i> Admin Home
								</li>
							</ol>
						</div>
					</div>
					<div class="row clearfix">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="card">
								<div class="header bg-orange">
									<h2>
										Welcome to <strong>SBM Admin Panel</strong>
									</h2>
								</div>
								<div class="body">
									Here you can manage the entire system.
								</div>
							</div>
							<div class="row">
								<div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <a href="schools" class="info-kutu">
                                        <div class="info-box hover-zoom-effect">
                                            <div class="icon bg-red">
                                                <i class="material-icons">business</i>
                                            </div>
                                            <div class="content">
                                                <div class="text">Schools</div>
                                                <div class="number"><?=$okul_say?></div>
                                            </div>
                                        </div>
                                    </a>
								</div>
								<div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <a href="teachers" class="info-kutu">
                                        <div class="info-box hover-zoom-effect">
                                            <div class="icon bg-blue">
                                                <i class="material-icons">business_center</i>
                                            </div>
                                            <div class="content">
                                                <div class="text">Teachers</div>
                                                <div class="number"><?=$ogretmen_say?></div>
                                            </div>
                                        </div>
                                    </a>
								</div>
								<div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <a href="students" class="info-kutu">
                                        <div class="info-box hover-zoom-effect">
                                            <div class="icon bg-orange">
                                                <i class="material-icons">school</i>
                                            </div>
                                            <div class="content">
                                                <div class="text">Students</div>
                                                <div class="number"><?=$ogrenci_say?></div>
                                            </div>
                                        </div>
                                    </a>
								</div>
								<div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <a href="classes" class="info-kutu">
                                        <div class="info-box hover-zoom-effect">
                                            <div class="icon bg-green">
                                                <i class="material-icons">class</i>
                                            </div>
                                            <div class="content">
                                                <div class="text">Classes</div>
                                                <div class="number"><?=$sinif_say?></div>
                                            </div>
                                        </div>
                                    </a>
								</div>
								<?php
								if($okul_say < 1)
								{
								?>
								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<div class="alert alert-danger" role="alert">
										Henüz sisteme kayıtlı bir okul bulunmuyor. Okul oluşturarak sistemi kullanmaya başlayabilirsiniz.
										<br>
										<a href="create-school" class="btn btn-default waves-effect btn-xs"><i class="material-icons">add_circle</i><span>Create School</span></a>
									</div>
								</div>
								<?php
								}
								else if($okul_say > 0)
								{
									if($ogretmen_say < 1)
									{
									?>
									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
										<div class="alert alert-danger" role="alert">
											Henüz sisteme kayıtlı veya davet edilmiş bir öğretmen bulunmuyor. Oluşturduğunuz okullara öğretmen çağırarak onların sisteme kaydolmalarını sağlayın!
											<br>
											<a href="invite-teacher" class="btn btn-default waves-effect btn-xs"><i class="material-icons">contact_mail</i><span>Invite Teacher</span></a>
										</div>
									</div>
									<?php
									}
								}
								?>
							</div>
							<div class="panel panel-default panel-post">
								<div class="panel-heading">
									<h4><strong>Admin Logon Records</strong></h4>
								</div>
							</div>
							<div class="row clearfix">
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
									<input type="radio" class="with-gap radio-col-orange filtre-buton2" name="filtre_durum" id="filtredurumtumu" checked="">
									<label for="filtredurumtumu">All</label> 
									<input type="radio" class="with-gap radio-col-orange filtre-buton2" name="filtre_durum" id="filtredurumbasarili">
									<label for="filtredurumbasarili">Successful</label> 
									<input type="radio" class="with-gap radio-col-orange filtre-buton2" name="filtre_durum" id="filtredurumbasarisiz">
									<label for="filtredurumbasarisiz">Unsuccessful</label>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" id="uye-giris-kayitlari">
									
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<script src="../plugins/jquery/jquery.min.js"></script>
		<script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="../plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
		<script src="../plugins/node-waves/waves.min.js"></script>
		<script src="../js/main-admin.js"></script>
		<script>
		$(document).ready(function()
		{
			$.ajaxSetup({
				headers: { 'sbmtoken': $('meta[name="sbmtoken"]').attr('content') }
			});
			$.ajax(
			{
				url: "admin-logon-records",
				type: "GET",
				contentType: false,
				cache: false,
				processData:false,
				success: function(data)
				{
					$("#uye-giris-kayitlari").html(data);
				}
			});
			$('body').on("click", '.sayfala-buton2', function(event)  
			{
				var node = this.id;
				
				var regexp = /[^0-9]/g;
				var regexp2 = /[^a-z]/g;

				var siralama = $("select#siralama2").val();
				var filtre_durum = $("input[name='filtre_durum']:checked").attr("id");
				
				$.ajax(
				{
					url: "admin-logon-records?sayfa="+node.replace(regexp,'')+"&siralama="+siralama.replace(regexp,'')+"&filtre_durum="+filtre_durum.replace(regexp2,''),
					type: "GET",
					contentType: false,
					cache: false,
					processData:false,
					success: function(data)
					{
						$("#uye-giris-kayitlari").html(data);
					}	 						
				});
			});
			$('body').on("change", 'select#siralama2', function(event)  
			{
				var regexp = /[^0-9]/g;
				var regexp2 = /[^a-z]/g;
				
				var siralama = $("select#siralama2").val();
				var filtre_durum = $("input[name='filtre_durum']:checked").attr("id");
				
				$.ajax(
				{
					url: "admin-logon-records?siralama="+siralama.replace(regexp,'')+"&filtre_durum="+filtre_durum.replace(regexp2,''),
					type: "GET",
					contentType: false,
					cache: false,
					processData:false,
					success: function(data)
					{
						$("#uye-giris-kayitlari").html(data);
					}	 						
				});
			});
			$('body').on("click", '.filtre-buton2', function(event)  
			{
				var regexp = /[^0-9]/g;
				var regexp2 = /[^a-z]/g;
				
				var siralama = $("select#siralama2").val();
				var filtre_durum = $("input[name='filtre_durum']:checked").attr("id");
				
				$.ajax(
				{
					url: "admin-logon-records?siralama="+siralama.replace(regexp,'')+"&filtre_durum="+filtre_durum.replace(regexp2,''),
					type: "GET",
					contentType: false,
					cache: false,
					processData:false,
					success: function(data)
					{
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
else if($page_request == "create-school")
{
?>
		<section class="content">
			<div class="container-fluid">
				<div class="row clearfix">
					<div class="block-header">
						<div class="gri">
							<ol class="breadcrumb">
								<li>
									<a href="home">
										<i class="material-icons">home</i> Home
									</a>
								</li>
								<li>
									<a href="schools">
										<i class="material-icons">business</i> Schools
									</a>
								</li>
								<li class="create-school">
									<i class="material-icons">add</i> Create School
								</li>
							</ol>
						</div>
					</div>
					<div class="row clearfix">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
							<div class="card">
								<div class="header bg-orange">
									<h4>Create School</h4>
								</div>
								<a href="schools" class="btn btn-default btn-block btn-lg waves-effect"><i class="material-icons">arrow_back</i><span>Go Back</span></a>
								<div class="body">
									<form id="Create-School-Form">
										<label for="school_name">School Name:</label>
										<div class="form-group">
											<div class="form-line">
												<input class="form-control" name="school_name" id="school_name" placeholder="School name..." type="text">
											</div>
										</div>
										<div class="form-group">
											<button type="submit" class="btn btn-success btn-block btn-lg waves-effect Create-School-Button">Create School</button>
										</div>
									</form>
									<div id="Create-School-Result"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<script src="../plugins/jquery/jquery.min.js"></script>
		<script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="../plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
		<script src="../plugins/node-waves/waves.min.js"></script>
		<script src="../js/main-admin.js"></script>
		<script type="text/javascript">
		$(document).ready(function()
		{
			$('body').on('submit','#Create-School-Form',function(e)
			{
				e.preventDefault();
				
				$('.Create-School-Button').prop('disabled', true);
				$('.Create-School-Button').html("School Creating...");
				
				$("#Create-School-Result").empty();
				
				$.ajax(
				{
					url: "create-school-a",
					type: "POST",
					data: new FormData(this),
					contentType: false,
					cache: false,
					processData:false,
					success: function(data)
					{
						setTimeout(function()
						{
							$('.Create-School-Button').prop('disabled', false);
							$('.Create-School-Button').html("Create School");
							if(data == 0)
							{
								$("#Create-School-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
							}
							if(data == 1)
							{
								$("#Create-School-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Okul başarıyla eklendi.</div>");
								$("#Create-School-Form").trigger("reset");
							}
							if(data == 2)
							{
								$("#Create-School-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
							}
							if(data == 3)
							{
								$("#Create-School-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Okul adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
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
}
else if($page_request == "invite-teacher")
{
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="block-header">
                    <div class="gri">
                        <ol class="breadcrumb">
                            <li>
                                <a href="home">
                                    <i class="material-icons">home</i> Home
                                </a>
                            </li>
                            <li>
                                <a href="teachers">
                                    <i class="material-icons">business_center</i> Teachers
                                </a>
                            </li>
                            <li class="invite-teacher">
                                <i class="material-icons">contact_mail</i> Invite Teacher
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="header bg-orange">
                                <h4>Invite Teacher</h4>
                            </div>
                            <a href="teachers" class="btn btn-default btn-block btn-lg waves-effect"><i class="material-icons">arrow_back</i><span>Go Back</span></a>
                            <div class="body">
                                <form id="Invite-Teacher-Form">
                                    <div class="form-group">
                                        <label for="school">School:</label>
                                        <select class="form-control" name="school" id="school">
                                            <option value="0">Choose...</option>
                                            <?php
                                            $sorguokullar = $DB_con->prepare("SELECT id,name FROM schools");
                                            $sorguokullar->execute();
                                            while($yazokullar = $sorguokullar->fetch(PDO::FETCH_ASSOC))
                                            {
                                                ?>
                                                <option value="<?=$yazokullar["id"]?>"><?=$yazokullar["name"]?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="teacher_name">Teacher Name:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="teacher_name" id="teacher_name" placeholder="Teacher name..." type="text">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="teacher_email">Teacher E-Mail Address:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="teacher_email" id="teacher_email" placeholder="Teacher e-mail address..." type="text">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success btn-block btn-lg waves-effect Invite-Teacher-Button">Invite Teacher</button>
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
    <script src="../plugins/jquery/jquery.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="../plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="../plugins/node-waves/waves.min.js"></script>
    <script src="../js/main-admin.js"></script>
    <script type="text/javascript">
        $(document).ready(function()
        {
            $('body').on('submit','#Invite-Teacher-Form',function(e)
            {
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
                        processData:false,
                        success: function(data)
                        {
                            setTimeout(function()
                            {
                                $('.Invite-Teacher-Button').prop('disabled', false);
                                $('.Invite-Teacher-Button').html("Invite Teacher");
                                if(data == 0)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if(data == 1)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Öğretmen başarıyla sisteme davet edildi!</div>");
                                    $("#Invite-Teacher-Form").trigger("reset");
                                }
                                if(data == 2)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if(data == 3)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Öğretmen adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                                if(data == 4)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen öğretmene ait geçerli bir e-posta adresini giriniz.</div>");
                                }
                                if(data == 5)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Belirtilen e-postaya ait öğretmen zaten daha önceden sisteme davet edilmişti. Öğretmene tekrardan e-posta gönderildi.</div>");
                                    $("#Invite-Teacher-Form").trigger("reset");
                                }
                                if(data == 6)
                                {
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
}
else if($page_request == "invite-school-admin")
{
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="block-header">
                    <div class="gri">
                        <ol class="breadcrumb">
                            <li>
                                <a href="home">
                                    <i class="material-icons">home</i> Home
                                </a>
                            </li>
                            <li>
                                <a href="schools">
                                    <i class="material-icons">business</i> Schools
                                </a>
                            </li>
                            <li class="invite-teacher">
                                <i class="material-icons">contact_mail</i> Invite School Admin
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="header bg-orange">
                                <h4>Invite School Admin</h4>
                            </div>
                            <a href="schools" class="btn btn-default btn-block btn-lg waves-effect"><i class="material-icons">arrow_back</i><span>Go Back</span></a>
                            <div class="body">
                                <form id="Invite-Teacher-Form">
                                    <div class="form-group">
                                        <label for="school">School:</label>
                                        <select class="form-control" name="school" id="school">
                                            <option value="0">Choose...</option>
                                            <?php
                                            $sorguokullar = $DB_con->prepare("SELECT id,name FROM schools");
                                            $sorguokullar->execute();
                                            while($yazokullar = $sorguokullar->fetch(PDO::FETCH_ASSOC))
                                            {
                                                ?>
                                                <option value="<?=$yazokullar["id"]?>"><?=$yazokullar["name"]?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="teacher_name">School Admin Name:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="teacher_name" id="teacher_name" placeholder="School admin name..." type="text">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="teacher_email">School Admin E-Mail Address:</label>
                                        <div class="form-line">
                                            <input class="form-control" name="teacher_email" id="teacher_email" placeholder="School admin e-mail address..." type="text">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success btn-block btn-lg waves-effect Invite-Teacher-Button">Invite School Admin</button>
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
    <script src="../plugins/jquery/jquery.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="../plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="../plugins/node-waves/waves.min.js"></script>
    <script src="../js/main-admin.js"></script>
    <script type="text/javascript">
        $(document).ready(function()
        {
            $('body').on('submit','#Invite-Teacher-Form',function(e)
            {
                e.preventDefault();

                $('.Invite-Teacher-Button').prop('disabled', true);
                $('.Invite-Teacher-Button').html("School Admin Inviting...");

                $("#Invite-Teacher-Result").empty();

                $.ajax(
                    {
                        url: "invite-school-admin-a",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData:false,
                        success: function(data)
                        {
                            setTimeout(function()
                            {
                                $('.Invite-Teacher-Button').prop('disabled', false);
                                $('.Invite-Teacher-Button').html("Invite School Admin");
                                if(data == 0)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if(data == 1)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Okul yöneticisi başarıyla sisteme davet edildi!</div>");
                                    $("#Invite-Teacher-Form").trigger("reset");
                                }
                                if(data == 2)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if(data == 3)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Okul yöneticisi adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                                if(data == 4)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen okul yöneticisine ait geçerli bir e-posta adresini giriniz.</div>");
                                }
                                if(data == 5)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-success'><strong>Successful!</strong> Belirtilen e-postaya ait okul yöneticisi zaten daha önceden sisteme davet edilmişti. Okul yöneticisine tekrardan e-posta gönderildi.</div>");
                                    $("#Invite-Teacher-Form").trigger("reset");
                                }
                                if(data == 6)
                                {
                                    $("#Invite-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Okul yöneticisi için belirttiğiniz e-posta adresi ile sisteme daha önce kayıt yapılmış.</div>");
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
}
else if($page_request == "schools")
{
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="block-header">
                    <div class="gri">
                        <ol class="breadcrumb">
                            <li>
                                <a href="home">
                                    <i class="material-icons">home</i> Home
                                </a>
                            </li>
                            <li>
                                <i class="material-icons">business</i> Schools
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="panel panel-default panel-post">
                    <div class="panel-heading">
                        <h4><strong>Schools</strong></h4>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" id="schools">

                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade in" id="modal-school-edit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="schoolEditContent">

            </div>
        </div>
    </div>
    <div class="modal fade in" id="modal-school-admin-edit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">View/Edit School Admin</h4>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <h6 class="list-group-item-heading">Yönetici olduğu okul</h6>
                            <p class="list-group-item-text school-name"></p>
                        </li>
                        <li class="list-group-item">
                            <h6 class="list-group-item-heading">Kayıt durumu</h6>
                            <p class="list-group-item-text school-admin-stat"></p>
                        </li>
                    </ul>
                    <form id="Edit-School-Admin-Form">
                        <div class="form-group">
                            <label for="name">School Admin Name:</label>
                            <div class="form-line">
                                <input class="form-control" name="name" id="name" type="text">
                            </div>
                        </div>
                        <input type="hidden" name="hidden_school_admin_id" id="hidden_school_admin_id">
                        <div id="Edit-School-Admin-Result"></div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block btn-lg waves-effect Edit-School-Admin-Button">Edit School Admin</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="../plugins/jquery/jquery.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="../plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="../plugins/node-waves/waves.min.js"></script>
    <script src="../js/main-admin.js"></script>
    <script type="text/javascript">
        $(document).ready(function()
        {
            $.ajaxSetup({
                headers: { 'sbmtoken': $('meta[name="sbmtoken"]').attr('content') }
            });
            $.ajax(
                {
                    url: "schools-a",
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData:false,
                    success: function(data)
                    {
                        $("#schools").html(data);
                    }
                });
            $('body').on("click", '.sayfala-buton2', function(event)
            {
                var node = this.id;

                var regexp = /[^0-9]/g;

                $.ajax(
                    {
                        url: "schools-a?sayfa="+node.replace(regexp,''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData:false,
                        success: function(data)
                        {
                            $("#schools").html(data);
                        }
                    });
            });
            $('body').on('click','.editSchool',function(e)
            {
                e.preventDefault();
                var regexp = /[^0-9]/g;
                var school = this.id;
                $.ajax(
                    {
                        url: "school-infos-"+school.replace(regexp,''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData:false,
                        success: function(data)
                        {
                            if(data == 0)
                            {
                                $("#schoolEditContent").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                            }
                            else
                            {
                                $("#schoolEditContent").html(data);
                            }
                        }
                    });
            });
            // $('#modal-school-edit').on('show.bs.modal', function (event) {
            //     var button = $(event.relatedTarget);
            //     var school_id = button.data("school");
            //     var school_name = button.data("school-name");
            //     var modal = $(this);
            //     modal.find('#hidden_school_id').val(school_id);
            //     modal.find('#name').val(school_name);
            // });

            $('#modal-school-admin-edit').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var school_name = button.data("school-name");
                var school_admin_name = button.data("school-admin-name");
                var school_admin_id = button.data("school-admin-id");
                var school_admin_status = button.data("school-admin-status");
                var modal = $(this);

                if(school_admin_status === 0) { modal.find('p.list-group-item-text.school-admin-stat').text("Davet edildi fakat henüz kayıt olmadı."); } else if(school_admin_status === 1) { modal.find('p.list-group-item-text.school-admin-stat').text("Sisteme kaydoldu."); }

                modal.find('#hidden_school_admin_id').val(school_admin_id);
                modal.find('#name').val(school_admin_name);
                modal.find('p.list-group-item-text.school-name').text(school_name);
            });
            $('body').on('submit','#Edit-School-Form',function(e)
            {
                e.preventDefault();

                $('.Edit-School-Button').prop('disabled', true);
                $('.Edit-School-Button').html("School Editing...");

                $("#Edit-School-Result").empty();

                $.ajax(
                    {
                        url: "edit-school",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData:false,
                        success: function(data)
                        {
                            setTimeout(function()
                            {
                                $('.Edit-School-Button').prop('disabled', false);
                                $('.Edit-School-Button').html("Edit School");
                                if(data == 0)
                                {
                                    $("#Edit-School-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if(data == 1)
                                {
                                    $("#Edit-School-Result").html("<div class='alert alert-success'><strong>Successful!</strong> The school has been successfully edited.</div>");
                                    $.ajax(
                                        {
                                            url: "schools-a",
                                            type: "GET",
                                            contentType: false,
                                            cache: false,
                                            processData:false,
                                            success: function(data)
                                            {
                                                $("#schools").html(data);
                                            }
                                        });
                                }
                                if(data == 2)
                                {
                                    $("#Edit-School-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if(data == 3)
                                {
                                    $("#Edit-School-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Okul adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                            }, 1000);
                        }
                    });
            });
            $('body').on('submit','#Edit-School-Admin-Form',function(e)
            {
                e.preventDefault();

                $('.Edit-School-Admin-Button').prop('disabled', true);
                $('.Edit-School-Admin-Button').html("School Admin Editing...");

                $("#Edit-School-Admin-Result").empty();

                $.ajax(
                    {
                        url: "edit-school-admin",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData:false,
                        success: function(data)
                        {
                            setTimeout(function()
                            {
                                $('.Edit-School-Admin-Button').prop('disabled', false);
                                $('.Edit-School-Admin-Button').html("Edit School Admin");
                                if(data == 0)
                                {
                                    $("#Edit-School-Admin-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if(data == 1)
                                {
                                    $("#Edit-School-Admin-Result").html("<div class='alert alert-success'><strong>Successful!</strong> The school admin has been successfully edited.</div>");
                                    $.ajax(
                                        {
                                            url: "schools-a",
                                            type: "GET",
                                            contentType: false,
                                            cache: false,
                                            processData:false,
                                            success: function(data)
                                            {
                                                $("#schools").html(data);
                                            }
                                        });
                                }
                                if(data == 2)
                                {
                                    $("#Edit-School-Admin-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if(data == 3)
                                {
                                    $("#Edit-School-Admin-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Okul yöneticisi adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
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
}
else if($page_request == "students")
{
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="block-header">
                    <div class="gri">
                        <ol class="breadcrumb">
                            <li>
                                <a href="home">
                                    <i class="material-icons">home</i> Home
                                </a>
                            </li>
                            <li>
                                <i class="material-icons">school</i> Students
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="panel panel-default panel-post">
                    <div class="panel-heading">
                        <h4><strong>Students</strong></h4>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="form-group">
                            <select class="form-control" id="siralama" name="siralama">
                                <option value="0" >Tarihe göre (Önce en yeni öğrenci)</option>
                                <option value="1" >Tarihe göre (Önce en eski öğrenci)</option>
                                <option value="2" >Davranış notları toplamına göre (Önce en yüksek)</option>
                                <option value="3" >Davranış notları toplamına göre (Önce en düşük)</option>
                                <option value="4" >Adına göre (A-Z)</option>
                                <option value="5" >Adına göre (Z-A)</option>
                                <option value="6" >Soyadına göre (A-Z)</option>
                                <option value="7" >Soyadına göre (Z-A)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="input-group">
                            <div class="form-line">
                                <input type="text" class="form-control" name="arama" id="arama" placeholder="Tabloda ara..." onkeyup="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');" onblur="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');">
                            </div>
                            <a href="javascript:;" class="input-group-addon" id="arama-buton"><i class="material-icons">search</i></a>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="notice notice-info" style="margin-top:0px;">
                            <strong>Bilgi: </strong>Sonuçlar arasından öğrencinin, <b>tam adına ve e-posta adresine</b> göre arama yapabilirsiniz.
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <label>School:</label>
                        <div class="form-group">
                            <select class="form-control" id="okul" name="okul">
                                <option value="0">Seçiniz...</option>
                                <?php
                                $getdogrulandi = 0;
                                $sorguOkullar = $DB_con->prepare("SELECT id,name FROM schools");
                                $sorguOkullar->execute();
                                while($yazOkullar = $sorguOkullar->fetch(PDO::FETCH_ASSOC))
                                {
                                    ?>
                                    <option value="<?=$yazOkullar["id"]?>" <?php if(isset($_GET["school"])) { if($_GET["school"] == $yazOkullar["id"]) { $getdogrulandi = 1; echo "selected"; } } ?>><?=$yazOkullar["name"]?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <label>Class:</label>
                        <div class="form-group">
                            <select class="form-control" id="sinif" name="sinif">
                                <option value="0">Seçiniz...</option>
                                <?php
                                if(isset($_GET["school"])) {
                                    if($getdogrulandi == 1) {
                                        $sorgusinifs = $DB_con->prepare("SELECT classes.id,classes.name,group_concat(users.name) AS teachersname FROM classes INNER JOIN users ON FIND_IN_SET(users.id,teachers) > 0 WHERE school = :school GROUP BY classes.id");
                                        $sorgusinifs->execute(array(":school"=>$_GET["school"]));
                                        while($yazsinifs = $sorgusinifs->fetch(PDO::FETCH_ASSOC)){
                                            ?>
                                            <option value="<?=$yazsinifs["id"]?>"><?=$yazsinifs["name"]?> (Öğretmenler: <?=$yazsinifs["teachersname"]?>)</option>
                                            <?php
                                        }
                                    }
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
    <script src="../plugins/jquery/jquery.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="../plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="../plugins/node-waves/waves.min.js"></script>
    <script src="../js/main-admin.js"></script>
    <script type="text/javascript">
        function SinifYukle(id)
        {
            $.ajax({
                type: "POST",
                url: "get-classes",
                data: "id=" + id,
                dataType: 'json',
            }).done(function( result ) {
                $.each(result, function(order, object) {
                    key = object.id;
                    value = object.name;
                    $('#sinif').append($('<option>', { value : key }).text(value+' '+object.teachersname));
                });
            });
        }
        $(document).ready(function()
        {
            $.ajaxSetup({
                headers: { 'sbmtoken': $('meta[name="sbmtoken"]').attr('content') }
            });
            $.ajax(
                {
                    <?php if(isset($_GET["school"]) && $getdogrulandi == 1) { ?>
                    url: "students-a?okul=<?=$_GET["school"]?>",
                    <?php }else{ ?>
                    url: "students-a",
                    <?php } ?>
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData:false,
                    success: function(data)
                    {
                        $("#students").html(data);
                    }
                });
            $("#okul").bind("change", function() {
                $('#sinif').find('option').remove().end().append('<option value="0">Seçiniz...</option>').val('0');
                SinifYukle($(this).find(':selected').val());
            });
            $(document).on("click", '.sayfala-buton', function(event)
            {
                event.preventDefault();
                var node = this.id;
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?sayfa="+node.replace(regexp2,'')+"&siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,'')+"&kayit2="+kayit2.replace(regexp2,'')+"&puan="+puan.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#siralama', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,'')+"&kayit2="+kayit2.replace(regexp2,'')+"&puan="+puan.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#puan', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,'')+"&kayit2="+kayit2.replace(regexp2,'')+"&puan="+puan.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#kayit', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,'')+"&kayit2="+kayit2.replace(regexp2,'')+"&puan="+puan.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#kayit2', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,'')+"&kayit2="+kayit2.replace(regexp2,'')+"&puan="+puan.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("click", '#arama-buton', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,'')+"&kayit2="+kayit2.replace(regexp2,'')+"&puan="+puan.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#okul', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,'')+"&kayit2="+kayit2.replace(regexp2,'')+"&puan="+puan.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#sinif', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var puan = $("select#puan").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                var kayit2 = $("select#kayit2").val();
                $.ajax(
                    {
                        url: "students-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,'')+"&kayit2="+kayit2.replace(regexp2,'')+"&puan="+puan.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#students").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $('body').on('click','.ogrenci-duzenle',function(e)
            {
                e.preventDefault();
                var regexp = /[^0-9]/g;
                var ogrenci = this.id;
                $.ajax(
                    {
                        url: "student-infos-"+ogrenci.replace(regexp,''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData:false,
                        success: function(data)
                        {
                            if(data == 0)
                            {
                                $(".modal-student-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                            }
                            else
                            {
                                $(".modal-student-content").html(data);
                            }
                        }
                    });
            });
            $('body').on('submit','#Edit-Student-Form',function(e)
            {
                e.preventDefault();

                $('.Edit-Student-Button').prop('disabled', true);
                $('.Edit-Student-Button').html("Student Editing...");

                $("#Edit-Student-Result").empty();

                $.ajax(
                    {
                        url: "edit-student",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData:false,
                        success: function(data)
                        {
                            setTimeout(function()
                            {
                                $('.Edit-Student-Button').prop('disabled', false);
                                $('.Edit-Student-Button').html("Edit Student");
                                if(data == 0)
                                {
                                    $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if(data == 1)
                                {
                                    $("#Edit-Student-Result").html("<div class='alert alert-success'><strong>Successful!</strong> The student has been successfully edited.</div>");
                                    var siralama = $("select#siralama").val();
                                    var okul = $("select#okul").val();
                                    var sinif = $("select#sinif").val();
                                    var puan = $("select#puan").val();
                                    var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                                    var regexp2 = /[^0-9]/g;
                                    var arama = $("input#arama").val();
                                    var kayit = $("select#kayit").val();
                                    var kayit2 = $("select#kayit2").val();
                                    $.ajax(
                                        {
                                            url: "students-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,'')+"&kayit2="+kayit2.replace(regexp2,'')+"&puan="+puan.replace(regexp2,''),
                                            type: "POST",
                                            contentType: false,
                                            cache: false,
                                            processData:false,
                                            beforeSend: function()
                                            {
                                                $('.page-loader-wrapper').fadeIn(100);
                                            },
                                            success: function(data)
                                            {
                                                $("#students").html(data);
                                                $('.page-loader-wrapper').fadeOut();
                                                $("html, body").animate({ scrollTop: 0 }, "slow");
                                            }
                                        });
                                }
                                if(data == 2)
                                {
                                    $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if(data == 3)
                                {
                                    $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Öğrenci adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                                if(data == 4)
                                {
                                    $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Öğrenciye ait en az bir adet sınıf seçmelisiniz.</div>");
                                }
                                if(data == 5)
                                {
                                    $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Veli adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                                if(data == 6)
                                {
                                    $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Birincil veli e-postası için geçerli bir e-posta adresi giriniz.</div>");
                                }
                                if(data == 7)
                                {
                                    $("#Edit-Student-Result").html("<div class='alert alert-danger'><strong>Error:</strong> İkincil veli e-postası için geçerli bir e-posta adresi giriniz.</div>");
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
}
else if($page_request == "teachers")
{
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="block-header">
                    <div class="gri">
                        <ol class="breadcrumb">
                            <li>
                                <a href="home">
                                    <i class="material-icons">home</i> Home
                                </a>
                            </li>
                            <li>
                                <i class="material-icons">business_center</i> Teachers
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="panel panel-default panel-post">
                    <div class="panel-heading">
                        <h4><strong>Teachers</strong></h4>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="form-group">
                            <select class="form-control" id="siralama" name="siralama">
                                <option value="0" >Tarihe göre (Önce en yeni öğretmen)</option>
                                <option value="1" >Tarihe göre (Önce en eski öğretmen)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="input-group">
                            <div class="form-line">
                                <input type="text" class="form-control" name="arama" id="arama" placeholder="Tabloda ara..." onkeyup="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');" onblur="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');">
                            </div>
                            <a href="javascript:;" class="input-group-addon" id="arama-buton"><i class="material-icons">search</i></a>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="notice notice-info" style="margin-top:0px;">
                            <strong>Bilgi: </strong>Sonuçlar arasından öğretmenin, <b>tam adına ve e-posta adresine</b> göre arama yapabilirsiniz.
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <label>School:</label>
                        <div class="form-group">
                            <select class="form-control" id="okul" name="okul">
                                <option value="0">Seçiniz...</option>
                                <?php
                                $getdogrulandi = 0;
                                $sorguOkullar = $DB_con->prepare("SELECT id,name FROM schools");
                                $sorguOkullar->execute();
                                while($yazOkullar = $sorguOkullar->fetch(PDO::FETCH_ASSOC))
                                {
                                    ?>
                                    <option value="<?=$yazOkullar["id"]?>" <?php if(isset($_GET["school"])) { if($_GET["school"] == $yazOkullar["id"]) { $getdogrulandi = 1; echo "selected"; } } ?>><?=$yazOkullar["name"]?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <label>Class:</label>
                        <div class="form-group">
                            <select class="form-control" id="sinif" name="sinif">
                                <option value="0">Seçiniz...</option>
                                <?php
                                if(isset($_GET["school"])) {
                                    if($getdogrulandi == 1) {
                                        $sorgusinifs = $DB_con->prepare("SELECT classes.id,classes.name,group_concat(users.name) AS teachersname FROM classes INNER JOIN users ON FIND_IN_SET(users.id,teachers) > 0 WHERE school = :school GROUP BY classes.id");
                                        $sorgusinifs->execute(array(":school"=>$_GET["school"]));
                                        while($yazsinifs = $sorgusinifs->fetch(PDO::FETCH_ASSOC)){
                                            ?>
                                            <option value="<?=$yazsinifs["id"]?>"><?=$yazsinifs["name"]?> (Öğretmenler: <?=$yazsinifs["teachersname"]?>)</option>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
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
    <script src="../plugins/jquery/jquery.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="../plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="../plugins/node-waves/waves.min.js"></script>
    <script src="../js/main-admin.js"></script>
    <script type="text/javascript">
        function SinifYukle(id)
        {
            $.ajax({
                type: "POST",
                url: "get-classes",
                data: "id=" + id,
                dataType: 'json',
            }).done(function( result ) {
                $.each(result, function(order, object) {
                    key = object.id;
                    value = object.name;
                    $('#sinif').append($('<option>', { value : key }).text(value+' '+object.teachersname));
                });
            });
        }
        $(document).ready(function()
        {
            $.ajaxSetup({
                headers: { 'sbmtoken': $('meta[name="sbmtoken"]').attr('content') }
            });
            $.ajax(
                {
                    <?php if(isset($_GET["school"]) && $getdogrulandi == 1) { ?>
                    url: "teachers-a?okul=<?=$_GET["school"]?>",
                    <?php }else{ ?>
                    url: "teachers-a",
                    <?php } ?>
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData:false,
                    success: function(data)
                    {
                        $("#teachers").html(data);
                    }
                });
            $("#okul").bind("change", function() {
                $('#sinif').find('option').remove().end().append('<option value="0">Seçiniz...</option>').val('0');
                SinifYukle($(this).find(':selected').val());
            });
            $(document).on("click", '.sayfala-buton', function(event)
            {
                event.preventDefault();
                var node = this.id;
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "teachers-a?sayfa="+node.replace(regexp2,'')+"&siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#teachers").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#siralama', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "teachers-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#teachers").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#kayit', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "teachers-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#teachers").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("click", '#arama-buton', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "teachers-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#teachers").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#okul', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "teachers-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#teachers").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#sinif', function(event)
            {
                event.preventDefault();
                var siralama = $("select#siralama").val();
                var okul = $("select#okul").val();
                var sinif = $("select#sinif").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "teachers-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#teachers").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $('body').on('click','.ogretmen-duzenle',function(e)
            {
                e.preventDefault();
                var regexp = /[^0-9]/g;
                var ogretmen = this.id;
                $.ajax(
                    {
                        url: "teacher-infos-"+ogretmen.replace(regexp,''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData:false,
                        success: function(data)
                        {
                            if(data == 0)
                            {
                                $(".modal-teacher-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                            }
                            else
                            {
                                $(".modal-teacher-content").html(data);
                            }
                        }
                    });
            });
            $('body').on('submit','#Edit-Teacher-Form',function(e)
            {
                e.preventDefault();

                $('.Edit-Teacher-Button').prop('disabled', true);
                $('.Edit-Teacher-Button').html("Teacher Editing...");

                $("#Edit-Teacher-Result").empty();

                $.ajax(
                    {
                        url: "edit-teacher",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData:false,
                        success: function(data)
                        {
                            setTimeout(function()
                            {
                                $('.Edit-Teacher-Button').prop('disabled', false);
                                $('.Edit-Teacher-Button').html("Edit Teacher");
                                if(data == 0)
                                {
                                    $("#Edit-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if(data == 1)
                                {
                                    $("#Edit-Teacher-Result").html("<div class='alert alert-success'><strong>Successful!</strong> The teacher has been successfully edited.</div>");
                                    var siralama = $("select#siralama").val();
                                    var okul = $("select#okul").val();
                                    var sinif = $("select#sinif").val();
                                    var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                                    var regexp2 = /[^0-9]/g;
                                    var arama = $("input#arama").val();
                                    var kayit = $("select#kayit").val();
                                    $.ajax(
                                        {
                                            url: "teachers-a?siralama="+siralama.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&sinif="+sinif.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                                            type: "POST",
                                            contentType: false,
                                            cache: false,
                                            processData:false,
                                            beforeSend: function()
                                            {
                                                $('.page-loader-wrapper').fadeIn(100);
                                            },
                                            success: function(data)
                                            {
                                                $("#teachers").html(data);
                                                $('.page-loader-wrapper').fadeOut();
                                                $("html, body").animate({ scrollTop: 0 }, "slow");
                                            }
                                        });
                                }
                                if(data == 2)
                                {
                                    $("#Edit-Teacher-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if(data == 3)
                                {
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
}
else if($page_request == "classes")
{
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="block-header">
                    <div class="gri">
                        <ol class="breadcrumb">
                            <li>
                                <a href="home">
                                    <i class="material-icons">home</i> Home
                                </a>
                            </li>
                            <li>
                                <i class="material-icons">class</i> Classes
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="panel panel-default panel-post">
                    <div class="panel-heading">
                        <h4><strong>Classes</strong></h4>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="input-group">
                            <div class="form-line">
                                <input type="text" class="form-control" name="arama" id="arama" placeholder="Tabloda ara..." onkeyup="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');" onblur="this.value=this.value.replace(/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g,'');">
                            </div>
                            <a href="javascript:;" class="input-group-addon" id="arama-buton"><i class="material-icons">search</i></a>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="notice notice-info" style="margin-top:0px;">
                            <strong>Bilgi: </strong>Sonuçlar arasından sınıfın, <b>adına</b> göre arama yapabilirsiniz.
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <label>School:</label>
                        <div class="form-group">
                            <select class="form-control" id="okul" name="okul">
                                <option value="0">Seçiniz...</option>
                                <?php
                                $getdogrulandi = 0;
                                $sorguOkullar = $DB_con->prepare("SELECT id,name FROM schools");
                                $sorguOkullar->execute();
                                while($yazOkullar = $sorguOkullar->fetch(PDO::FETCH_ASSOC))
                                {
                                    ?>
                                    <option value="<?=$yazOkullar["id"]?>" <?php if(isset($_GET["school"])) { if($_GET["school"] == $yazOkullar["id"]) { $getdogrulandi = 1; echo "selected"; } } ?>><?=$yazOkullar["name"]?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
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
    <script src="../plugins/jquery/jquery.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="../plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="../plugins/node-waves/waves.min.js"></script>
    <script src="../js/main-admin.js"></script>
    <script type="text/javascript">
        $(document).ready(function()
        {
            $.ajaxSetup({
                headers: { 'sbmtoken': $('meta[name="sbmtoken"]').attr('content') }
            });
            $.ajax(
                {
                    <?php if(isset($_GET["school"]) && $getdogrulandi == 1) { ?>
                    url: "classes-a?okul=<?=$_GET["school"]?>",
                    <?php }else{ ?>
                    url: "classes-a",
                    <?php } ?>
                    type: "GET",
                    contentType: false,
                    cache: false,
                    processData:false,
                    success: function(data)
                    {
                        $("#classes").html(data);
                    }
                });
            $(document).on("click", '.sayfala-buton', function(event)
            {
                event.preventDefault();
                var node = this.id;
                var okul = $("select#okul").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "classes-a?sayfa="+node.replace(regexp2,'')+"&arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#classes").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#kayit', function(event)
            {
                event.preventDefault();
                var okul = $("select#okul").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "classes-a?arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#classes").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("click", '#arama-buton', function(event)
            {
                event.preventDefault();
                var okul = $("select#okul").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "classes-a?arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#classes").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $(document).on("change", 'select#okul', function(event)
            {
                event.preventDefault();
                var okul = $("select#okul").val();
                var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                var regexp2 = /[^0-9]/g;
                var arama = $("input#arama").val();
                var kayit = $("select#kayit").val();
                $.ajax(
                    {
                        url: "classes-a?arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                        type: "POST",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $("#classes").html(data);
                            $('.page-loader-wrapper').fadeOut();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                    });
            });
            $('body').on('click','.sinif-duzenle',function(e)
            {
                e.preventDefault();
                var regexp = /[^0-9]/g;
                var sinif = this.id;
                $.ajax(
                    {
                        url: "class-infos-"+sinif.replace(regexp,''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData:false,
                        success: function(data)
                        {
                            if(data == 0)
                            {
                                $(".modal-class-content").html("<div class='alert alert-danger mb-0 rounded-0'><strong>Hata!</strong> Teknik bir problem oluştu. Lütfen tekrar deneyin.</div>");
                            }
                            else
                            {
                                $(".modal-class-content").html(data);
                            }
                        }
                    });
            });
            $('body').on('submit','#Edit-Class-Form',function(e)
            {
                e.preventDefault();

                $('.Edit-Class-Button').prop('disabled', true);
                $('.Edit-Class-Button').html("Class Editing...");

                $("#Edit-Class-Result").empty();

                $.ajax(
                    {
                        url: "edit-class",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData:false,
                        success: function(data)
                        {
                            setTimeout(function()
                            {
                                $('.Edit-Class-Button').prop('disabled', false);
                                $('.Edit-Class-Button').html("Edit Class");
                                if(data == 0)
                                {
                                    $("#Edit-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                                }
                                if(data == 1)
                                {
                                    $("#Edit-Class-Result").html("<div class='alert alert-success'><strong>Successful!</strong> The class has been successfully edited.</div>");
                                    var okul = $("select#okul").val();
                                    var regexp = /[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı _]/g;
                                    var regexp2 = /[^0-9]/g;
                                    var arama = $("input#arama").val();
                                    var kayit = $("select#kayit").val();
                                    $.ajax(
                                        {
                                            url: "classes-a?arama="+arama.replace(regexp,'')+"&okul="+okul.replace(regexp2,'')+"&kayit="+kayit.replace(regexp2,''),
                                            type: "POST",
                                            contentType: false,
                                            cache: false,
                                            processData:false,
                                            beforeSend: function()
                                            {
                                                $('.page-loader-wrapper').fadeIn(100);
                                            },
                                            success: function(data)
                                            {
                                                $("#classes").html(data);
                                                $('.page-loader-wrapper').fadeOut();
                                                $("html, body").animate({ scrollTop: 0 }, "slow");
                                            }
                                        });
                                }
                                if(data == 2)
                                {
                                    $("#Edit-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Lütfen formu tamamen doldurunuz.</div>");
                                }
                                if(data == 3)
                                {
                                    $("#Edit-Class-Result").html("<div class='alert alert-danger'><strong>Error:</strong> Sınıf adı en az 3 karakter, en fazla 64 karakterden oluşabilir.</div>");
                                }
                                if(data == 4)
                                {
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
}
else if($page_request == "report")
{
    $sinifid = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if($sinifid === false)
    {
        echo 404;
        exit();
    }
    $ogrenciid = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);
    if($ogrenciid === false)
    {
        echo 404;
        exit();
    }
    $sorguogrencixd = $DB_con->prepare("SELECT name,avatar,email,schools FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND role = :role");
    $sorguogrencixd->execute(array(":id"=>$ogrenciid,":sid"=>$sinifid,":role"=>"student"));
    if($sorguogrencixd->rowCount() != 1)
    {
        echo 404;
        exit();
    }
    $sorgusinifidxd = $DB_con->prepare("SELECT id FROM classes WHERE id = :id");
    $sorgusinifidxd->execute(array(":id"=>$sinifid));
    if($sorgusinifidxd->rowCount() != 1)
    {
        echo 404;
        exit();
    }
    $yazogrencix = $sorguogrencixd->fetch(PDO::FETCH_ASSOC);
    $sorguokulad = $DB_con->prepare("SELECT name FROM schools WHERE id = :id");
    $sorguokulad->execute(array(":id"=>$yazogrencix["schools"]));
    $yazokulad = $sorguokulad->fetch(PDO::FETCH_ASSOC);
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="panel panel-default panel-post">
                            <div class="panel-heading">
                                <h4>Raporu görüntülenen öğrenci:</h4>
                            </div>
                            <div class="panel-heading">
                                <div class="media">
                                    <div class="media-left">
                                        <a href="javascript:;"><img src="../<?=$yazogrencix["avatar"]?>"></a>
                                    </div>
                                    <div class="media-body">
                                        <h4 class="media-heading">
                                            <a href="javascript:;"><?=$yazogrencix["name"]?></a><br><small><?=$yazogrencix["email"]?></small>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-heading">
                                <div class="media">
                                    <div class="media-body">
                                        <h4 class="media-heading">
                                            School: <a href="javascript:;"><?=$yazokulad["name"]?></a>
                                        </h4>
                                        <h4 class="media-heading m-t-10 p-b-5">
                                            Class(es):
                                        </h4>
                                        <div class="row">
                                            <?php
                                            $sorguogrencisinifs = $DB_con->prepare("SELECT classes.id,classes.name FROM users INNER JOIN classes ON FIND_IN_SET(classes.id, users.classes) WHERE users.id = :student AND role = :role AND schools = :school ORDER BY classes.id ASC");
                                            $sorguogrencisinifs->execute(array(":student"=>$ogrenciid,":role"=>"student",":school"=>$yazogrencix["schools"]));
                                            while($yazogrencisinifs = $sorguogrencisinifs->fetch(PDO::FETCH_ASSOC))
                                            {
                                                ?>
                                                <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                                                    <div class="form-group">
                                                        <input type="checkbox" id="class_<?= $yazogrencisinifs["id"] ?>" class="filled-in chk-col-orange classCheckBox" data-class-id="<?= $yazogrencisinifs["id"] ?>"
                                                               value="<?= $yazogrencisinifs["id"] ?>" <?php if($sinifid == $yazogrencisinifs["id"]) { echo "checked"; } ?>>
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
                                            <option value="5">This month (<?=date("F")?>)</option>
                                            <option value="6">Last month (<?=date("F", strtotime( '-1 month'))?>)</option>
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
                                                            <input type="text" class="form-control date1" placeholder="Ex: 2019-11-24">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="material-icons">compare_arrows</i>
                                                        </span>
                                                        <div class="form-line">
                                                            <input type="text" class="form-control date2" placeholder="Ex: 2019-11-30">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <button type="button" class="btn btn-success btn-block btn-sm waves-effect applytimefilter">Apply</button>
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
                    </div>
                    <div id="studentReportContent"></div>
                </div>
            </div>
        </div>
    </section>
    <script src="../plugins/jquery/jquery.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="../plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="../plugins/node-waves/waves.min.js"></script>
    <script src="../plugins/jquery-sparkline/jquery.sparkline.js"></script>
    <script src="../plugins/jquery-inputmask/jquery.inputmask.bundle.min.js"></script>
    <script src="../plugins/jquery-datatable/jquery.dataTables.min.js"></script>
    <script src="../plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.min.js"></script>
    <script src="../plugins/jquery-datatable/skin/bootstrap/js/dataTables.responsive.min.js"></script>
    <script src="../plugins/jquery-datatable/skin/bootstrap/js/responsive.bootstrap.min.js"></script>
    <script src="../js/main-admin.js"></script>
    <script type="text/javascript">
        $(document).ready(function()
        {
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
                    success: function (data) {
                        if (data == 0) {
                            $("#studentReportContent").html("<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'><div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div></div>");
                        } else {
                            $("#studentReportContent").html(data);
                            $.each($('.chart.chart-pie'), function (i, key) {
                                $(key).sparkline(undefined, {
                                    type: 'pie',
                                    height: '50px',
                                    sliceColors: ['#4CAF50', '#F44336']
                                });
                            });
                            $('.report-behavior-list').DataTable({
                                responsive: {
                                    details: {
                                        display: $.fn.dataTable.Responsive.display.modal( {
                                            header: function ( row ) {
                                                return 'Details:';
                                            }
                                        } ),
                                        renderer: $.fn.dataTable.Responsive.renderer.tableAll( {
                                            tableClass: 'table'
                                        } )
                                    }
                                }
                            });
                        }
                    }
                });
            $('.custom-range-filter').find('.date1').inputmask('yyyy-mm-dd', { placeholder: '____-__-__', clearIncomplete: true });
            $('.custom-range-filter').find('.date2').inputmask('yyyy-mm-dd', { placeholder: '____-__-__', clearIncomplete: true });
            $('body').on('click', '.classCheckBox', function (e) {
                var idSelector = function() { return $(this).data("class-id"); };
                var checkedClasses = $("input[type='checkbox'].classCheckBox:checked").map(idSelector).get();
                var timefilter = $("select#timefilter").val();
                var regexp = /[^0-9]/g;
                var date1 = $("input.date1").val();
                var date2 = $("input.date2").val();
                $.ajax(
                    {
                        url: "get-report-<?=$ogrenciid?>?classes="+checkedClasses+"&timefilter="+timefilter.replace(regexp,'')+"&date1="+date1+"&date2="+date2,
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data) {
                            if (data == 0) {
                                $("#studentReportContent").html("<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'><div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div></div>");
                            } else {
                                $("#studentReportContent").html(data);
                                $.each($('.chart.chart-pie'), function (i, key) {
                                    $(key).sparkline(undefined, {
                                        type: 'pie',
                                        height: '50px',
                                        sliceColors: ['#4CAF50', '#F44336']
                                    });
                                });
                                $('.report-behavior-list').DataTable({
                                    responsive: {
                                        details: {
                                            display: $.fn.dataTable.Responsive.display.modal( {
                                                header: function ( row ) {
                                                    return 'Details:';
                                                }
                                            } ),
                                            renderer: $.fn.dataTable.Responsive.renderer.tableAll( {
                                                tableClass: 'table'
                                            } )
                                        }
                                    }
                                });
                            }
                        }
                    });
            });
            $('body').on("change", 'select#timefilter', function(event)
            {
                if($("select#timefilter").val() === "7") {
                    $('.custom-range-filter').show();
                    return false;
                } else {
                    if($('.custom-range-filter').is(":visible"))
                    {
                        $('.custom-range-filter').hide();
                    }
                }
                var idSelector2 = function() { return $(this).data("class-id"); };
                var checkedClasses2 = $("input[type='checkbox'].classCheckBox:checked").map(idSelector2).get();
                var timefilter = $("select#timefilter").val();
                var regexp = /[^0-9]/g;
                $.ajax(
                    {
                        url: "get-report-<?=$ogrenciid?>?classes="+checkedClasses2+"&timefilter="+timefilter.replace(regexp,''),
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $('.page-loader-wrapper').fadeOut();
                            if (data == 0) {
                                $("#studentReportContent").html("<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'><div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div></div>");
                            } else {
                                $("#studentReportContent").html(data);
                                $.each($('.chart.chart-pie'), function (i, key) {
                                    $(key).sparkline(undefined, {
                                        type: 'pie',
                                        height: '50px',
                                        sliceColors: ['#4CAF50', '#F44336']
                                    });
                                });
                                $('.report-behavior-list').DataTable({
                                    responsive: {
                                        details: {
                                            display: $.fn.dataTable.Responsive.display.modal( {
                                                header: function ( row ) {
                                                    return 'Details:';
                                                }
                                            } ),
                                            renderer: $.fn.dataTable.Responsive.renderer.tableAll( {
                                                tableClass: 'table'
                                            } )
                                        }
                                    }
                                });
                            }
                        }
                    });
            });
            $('body').on("click", '.applytimefilter', function(event)
            {
                if($("select#timefilter").val() !== "7") return false;
                $('#apply-alert').html("");
                if($("input.date1").val().length === 0 || $("input.date2").val().length === 0 ) {
                    $('#apply-alert').html("<div class='alert alert-danger m-t-10'>Lütfen filtrelemek istediğiniz zaman aralığını eksiksiz doldurunuz.</div>");
                    return false;
                }
                var idSelector3 = function() { return $(this).data("class-id"); };
                var checkedClasses3 = $("input[type='checkbox'].classCheckBox:checked").map(idSelector3).get();
                var timefilter = $("select#timefilter").val();
                var regexp = /[^0-9]/g;
                var date1 = $("input.date1").val();
                var date2 = $("input.date2").val();
                $.ajax(
                    {
                        url: "get-report-<?=$ogrenciid?>?classes="+checkedClasses3+"&timefilter="+timefilter.replace(regexp,'')+"&date1="+date1+"&date2="+date2,
                        type: "GET",
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function()
                        {
                            $('.page-loader-wrapper').fadeIn(100);
                        },
                        success: function(data)
                        {
                            $('.page-loader-wrapper').fadeOut();
                            if (data == 0) {
                                $("#studentReportContent").html("<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'><div class='alert alert-danger'><strong>Error:</strong> There was a technical problem. Please try again.</div></div>");
                            } else {
                                $("#studentReportContent").html(data);
                                $.each($('.chart.chart-pie'), function (i, key) {
                                    $(key).sparkline(undefined, {
                                        type: 'pie',
                                        height: '50px',
                                        sliceColors: ['#4CAF50', '#F44336']
                                    });
                                });
                                $('.report-behavior-list').DataTable({
                                    responsive: {
                                        details: {
                                            display: $.fn.dataTable.Responsive.display.modal( {
                                                header: function ( row ) {
                                                    return 'Details:';
                                                }
                                            } ),
                                            renderer: $.fn.dataTable.Responsive.renderer.tableAll( {
                                                tableClass: 'table'
                                            } )
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
}
?>