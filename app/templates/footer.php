		{% if user_type == "admin" %}
		<!-- USER ADD MODAL -->
		<div class="modal fade" id="add_user" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times-circle"></i><i class="fa fa-times"></i></button>
		        <h4 class="modal-title" id="myModalLabel">Add User <i class="fa fa-user"></i></h4>
		      </div>
		      <div class="modal-body">
		        <form action="/users/add" method="POST" role="form" id="add_user">
		        	<div class="row">
		        		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
		        			<div class="form-group">
										<label for="first_name">First Name</label>
										<input type="text" class="form-control" id="first_name" name="first_name" required data-validation-engine="validate[required]">
									</div>
		        		</div>
		        		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
		        			<div class="form-group">
										<label for="last_name">Last Name</label>
										<input type="text" class="form-control" id="last_name" name="last_name" required data-validation-engine="validate[required]">
									</div>
		        		</div>
							</div>
							<div class="form-group">
								<label for="email">Email</label>
								<input type="email" class="form-control" id="email" name="email" required data-validation-engine="validate[required]">
							</div>
							<div class="form-group">
								<label for="email">Password</label>
								<input type="password" class="form-control" id="password" name="password" required data-validation-engine="validate[required]">
							</div>
							<div class="form-group">
								<label for="type">User Type</label>
								{% if user_types is not null %}
							  	<select name="user_type" class="form-control">
							  		{% for user_type in user_types %}
							  			<option value="{{ user_type.type_id }}">{{ user_type.type_name }}</option>
							  		{% endfor %}
							  	</select>
								{% else %}
									No user types created
								{% endif %}
							</div>
							
							<input type="submit" value="Create User" class="btn btn-success">

							<div class="alert alert-danger" style="display: none;" id="add_user_error">
								<ul id="add_user">
								</ul>
							</div>

		        </form>
		      </div>
		    </div><!-- /.modal-content -->
		  </div><!-- /.modal-dialog -->
		</div><!-- /.modal -->

				{% endif %}

	</div><!-- #app-body -->
	<!--END BOOTSTRAP CONTAINER-->
    </div><!-- #app-body-wrap -->

  <footer id="app-footer">
  	<div class="container">
  		<div class="row">
          	<div class="col-md-12 text-center copyright">
          		<em class="small">&copy; Copyright {{ year }} WebMechanix | All Rights Reserved</em>
			</div>
  		</div>
      </div>
  </footer>
    
  <!--load local javascript-->
	<script src="/js/scripts.js"></script>
	<script src="/js/image-picker.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/datatables/1.9.4/jquery.dataTables.min.js"></script>
	<script src="/js/jquery.serialize-object.js"></script>
	<link rel="stylesheet" href="/js/datepicker/css/datepicker3.css">
	<script src="/js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="/js/bootstrap-multiselect/css/bootstrap-multiselect.css">
	<link rel="stylesheet" href="/js/bootstrap-select/bootstrap-select.css">
	<script src="/js/bootstrap-select/bootstrap-select.js"></script>
	<link rel="stylesheet" href="/js/zozo.tabs/css/zozo.tabs.min.css">
	<link rel="stylesheet" href="/js/zozo.tabs/css/zozo.tabs.flat.min.css">
	<script src="/js/zozo.tabs/js/zozo.tabs.js"></script>
</body>
</html>