<!DOCTYPE html>
<html>
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
		<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
		<script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>		
		<link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/jquery.validate.min.js"></script>
		<script type="text/javascript" src="/scripts/jquery-validation/src/additional/pattern.js"></script>
		
		<script type="text/javascript">
			$(function() {
				var dataTable = $('#advertise_data').DataTable({
                    language: {"url":"http://cdn.datatables.net/plug-ins/1.10.20/i18n/Russian.json"},
                    processing: true,
                    serverSide: true,
                    order: [],
                    ajax: {
                        url:"/api/Advertises",
                        type:"GET"
                    },
                    columns: [
                        { data: 'UserName' },
                        { data: 'Title' },
                        { data: 'Content' },
                        { data: 'DateTime' },
                        {
                            data: 'ID',
                            render: function(data, type) {
                                return '<button type="button" name="update" id="'
                                    + data + '" class="btn btn-warning btn-xs update">Редактировать</button>';
                            }
                        },
                        {
                            data: 'ID',
                            render: function(data, type) {
                                return '<button type="button" name="delete" id="'
                                    + data + '" class="btn btn-danger btn-xs delete">Удалить</button>';
                            }
                        },
                    ],
                    columnDefs: [
                        {
                            "targets": [4, 5], // Столбцы, по которым не нужна сортировка
                            "orderable": false,
                        },
                    ],
				});	
				
				$(document).on('submit', '#advertise_form', function(event){
					event.preventDefault();					
					
					var advertise_info = {
						"user_id":$("#user_id").val(),
						"title":$("#Title").val(),
						"content":$("#Content").val(),
						"datetime":$("#DateTime").val()
					}
					
					var method="PUT";
					if($("#operation").val()==1) {
						method="POST";
						advertise_info.ID = $("#advertise_ID").val();						
					}					
					
					$.ajax({
                        url:"/api/Advertises",
                        method: method,
                        data: JSON.stringify(advertise_info),
                        headers: {
                            "Content-type":"application/json"
                        },
                        success:function(data)
                        {									
                            $('#advertise_form')[0].reset();
                            $('#advertiseModal').modal('hide');
                            dataTable.ajax.reload();
                        }
                    });
				});
				
				$(document).on('click', '.update', function(event){
					//Режим редактирования (кнопка Редактировать)
					var ID = $(this).attr("ID");					
					
					$.ajax({
                        url:"/api/advertises/"+ID,
                        method:'GET',
                        dataType: "json",								
                        success:function(data)
                        {
                            //Заголовок окна
                            $('.modal-title').text("Редактировать рекламу");
                            
                            $("#user_id").val(data.user_id);
                            $("#title").val(data.title);
                            $("#datetime").val(data.datetime);
                            $("#content").val(data.content);
                            $('#advertise_ID').val(ID);									
                            
                            //Флаг операции (1 - редактирование)
                            $("#operation").val("1");
                            
                            //Текст на кнопке
                            $("#action").val("Сохранить изменения");
                            
                            //Отобразить форму
                            $('#advertiseModal').modal('show');									
                        }
                    });
					
					event.preventDefault();
				});
				
				$("#add_button").click(function() {
					//Режим добавления (кнопка Добавить)
									
					$("#user_id").val("");
                    $("#title").val("");
                    $("#datetime").val("");
                    $("#content").val("");
                    $('#advertise_ID').val("");	
					
					//Заголовок окна
					$('.modal-title').text("Добавить рекламу");
					//Текст на кнопке
					$("#advertiseModal #action").val("Добавить");
					//Флаг операции (0- добавление)
					$("#advertiseModal #operation").val("0");
				});
				
				$(document).on("click",".delete",function() {
					//Режим удаления (кнопка Удалить)
					var advertise_ID = $(this).attr("ID");					
					
					if(confirm("Действительно удалить?"))
					{
						$.ajax({
							url:"/api/Advertises/"+advertise_ID+"/delete",
							method:"POST",							
							success:function(data)
							{								
								dataTable.ajax.reload();
							}
						});
					}
					else
					{
						return false;	
					}
				});
				
				$( "#advertise_form" ).validate({
					rules: {
							user_id: "required",
							title: "required",
							datetime:{
								required: true,
								number: true,
								min: 1900,
								max: new Date().getFullYear()
							},
							contet: "required"
					},
					messages: {
						user_id: "Пожалуйста укажите ваше имя",
						title: {
							required: "Пожалуйста укажите заголовок",
						},
				
						datetime: {
							required: "Пожалуйста укажите год",
							number: "Год должен быть числом",
							min: "Год должен быть не ранее 1900",
							max: "Год не может быть больше текущего"
						},
						content: "Пожалуйста укажите описание"
					},
					errorElement: "em",
					errorPlacement: function ( error, element ) {
						// Add the `help-block` class to the error element
						error.addClass( "help-block" );

						if ( element.prop( "type" ) === "checkbox" ) {
							error.insertAfter( element.parent( "label" ) );
						} else {
							error.insertAfter( element );
						}
					},
					highlight: function ( element, errorClass, validClass ) {
						$( element ).parents( ".field" ).addClass( "has-error" ).removeClass( "has-success" );
					},
					unhighlight: function (element, errorClass, validClass) {
						$( element ).parents( ".field" ).addClass( "has-success" ).removeClass( "has-error" );
					}
				});

				$('#advertiseModal').on('hidden.bs.modal',function(){
					//Очистка полей формы
					$(".form-control").val("");
					$( "#advertiseModal .field" ).removeClass( "has-success" ).removeClass( "has-error" );
					$(this).find("em").remove();
				});
			});
		</script>
	</head>
	<body>
		<div class="container box">
			<div class="table-responsive">
				<br />
				<div align="right">
					<button type="button" id="add_button" data-toggle="modal" data-target="#advertiseModal" class="btn btn-info btn-lg">Добавить</button>
				</div>
				<br /><br />
				<table id="advertise_data" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th width="10%">Пользователь</th>
							<th width="10%">Заголовок</th>
							<th width="10%">Описание</th>
							<th width="10%">Дата</th>
							<th width="10%"></th>
							<th width="10%"></th>
						</tr>
					</thead>
				</table>				
			</div>
		</div>
		
		<div id="advertiseModal" class="modal fade">
			<div class="modal-dialog">
				<form method="post" id="advertise_form" enctype="multipart/form-data">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Добавить товар</h4>
						</div>
						<div class="modal-body">
							<div class="field">
								<label>Пользователь</label>
								<input type="text" name="user_id" id="user_id" class="form-control" />
							</div>
							<div class="field">
								<label>Заголовок</label>
								<input type="text" name="title" id="title" class="form-control" />
							</div>
							<div class="field">
								<label>Описание</label>
								<input type="text" name="content" id="content" class="form-control" />
							</div>
							<div class="field">
								<label>Дата</label>
								<input type="text" name="ditetime" id="ditetime" class="form-control" />
							</div>
							<!--br />
							<label>Select User Image</label>
							<input type="file" name="user_image" id="user_image" />
							<span id="user_uploaded_image"></span-->
						</div>
						<div class="modal-footer">
							<input type="hidden" name="advertise_ID" id="advertise_ID" />
							<input type="hidden" name="operation" id="operation" />
							<input type="submit" name="action" id="action" class="btn btn-success" value="Добавить" />
							<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</body>
</html>