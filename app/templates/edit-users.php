{% extends 'header.php' %}

{% block pagecontent %}

	{% if user is not empty %}
		{# SHOW USER INFO IF USING GET METHOD #}
		{% if http_method == "GET" %}
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 section-header">
				<h1><i class="fa fa-user header-icon"></i> {% if user_type == "admin" %}{% if user_id == user.user_id %}My Profile{% else %}Edit User: {{ user.first_name }} {{ user.last_name }}{% endif %}{% else %} My Profile{% endif %}</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

			<form class="row" action="{{ submit_url }}" method="POST">
				<div class="form-group col-lg-6 col-md-6 col-sm-6">
					<label for="first_name">First Name</label>
					<input type="text" name="first_name" id="first_name" class="form-control" value="{{ user.first_name }}"/>
				</div>
				<div class="form-group col-lg-6 col-md-6 col-sm-6">
					<label for="last_name">Last Name</label>
					<input type="text" name="last_name" id="last_name" class="form-control" value="{{ user.last_name }}" required/>
				</div>
			  <div class="form-group col-lg-6 col-md-6 col-sm-6">
				  <label for="email">Email</label> 
				  <input type="email" name="email" id="email" class="form-control" value="{{ user.email }}" required/> 
				  <span class="error">{{ email_error }}</span>
			  </div>
			  <div class="form-group col-lg-6 col-md-6 col-sm-6">
				  <label for="password">Password (fill out if changing password)</label> 
				  <input type="password" name="password" id="password" class="form-control" value="" /> 
				  <span class="error">{{ email_error }}</span>
			  </div>
			  <div class="form-group col-lg-6 col-md-6 col-sm-6" style="clear:both;">
				  <label for="user_type">User Type</label>
				  {% if user_type == "admin" %}
				  	{% if all_user_types is not null %}
					  	<select name="user_type[]" id="user_type" class="form-control" required>
					  		{% for all_user_type in all_user_types %}
					  			<option value="{{ all_user_type.type_id }}" {% if found_user_type.type_id == all_user_type.type_id %} selected {% endif %}>{{ all_user_type.type_name }}</option>
					  		{% endfor %}
					  	</select>
						{% else %}
							<div class="alert alert-info">User {{ user.first_name }} {{ user.last_name }} has no type</div>
						{% endif %}
			  	{% else %}
						<input type="text" disabled value="{{ user_type }}" class="form-control">
			  	{% endif %}
				</div>
			  <div class="form-group col-lg-12 col-md-12 col-sm-12" style="clear:both;">
			  	<input type="submit" value="Update User" class="btn btn-primary"/> 
			  	<button type="submit" name="_METHOD" value="DELETE" class="btn btn-danger">Delete User</button>
			  </div>
			</form>
			</div>
		</div>
		{% elseif http_method == "DELETE" %}
			{% if last_user == false %}
				<div class="form-group col-lg-6 col-md-6 col-sm-6">Select user to assign <strong>{{ user.first_name }} {{ user.last_name }}</strong> links and scripts to?</div>
				{% if all_users is not null %}
			  	<form action="/users/delete/{{ user.user_id }}" method="POST">
				  	<select name="new_user">
				  		{% for all_user in all_users %}
				  			<option value="{{ all_user.user_id }}">{{ all_user.first_name }} {{ all_user.last_name }}</option>
				  		{% endfor %}
				  	</select>
				  	<input type="submit" class="btn btn-danger" value="Delete {{ all_user.first_name }} {{ all_user.last_name }}">
			  	</form>
				{% else %}
					<div class="alert alert-info">No other users found. Create more users before deleting this one</div>
				{% endif %}
			{% endif %}

		{% endif %}
	{% endif %}

{% endblock %}