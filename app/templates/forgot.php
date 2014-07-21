{% extends 'header.php' %}

	{% block pagecontent %}

	<div class="row">
		<div class="login-wrap col-md-4 col-md-offset-4">
			<img src="/images/logo.png" class="login-logo" />
			<div class="login-box">
			{% if email_found %}
			
				<p class="small">Check your inbox for reset password link</p>
			
			{% elseif pw_otk_found %}
			
				<p class="small">Enter your new password</p>
				<form action="/login/reset" method="POST" class="login-form">
					<div class="form-group">
						<label for="password">Password</label><br />
						<input type="password" name="password" id="password" value="" class="form-control" />
					</div>
					{% if password_error is not null %}
					<p><span class="error">{{ password_error }}</span></p>
					{% endif %}
					<input type="submit" class="btn btn-primary" value="Login" />
				</form>			
			
			{% else %}
			
				<p class="small">Please enter your email to reset password.</p>
				<form action="/login/forgot" method="POST" class="login-form">
					<div class="form-group">
						<label for="email">Email</label><br />
						<input type="text" name="email" id="email" value="{{ email_value }}" class="form-control" />
					</div>
					{% if email_error %}
					<p><span class="error">ERROR: Invalid e-mail.</span></p>
					{% endif %}
					<input type="submit" class="btn btn-primary" value="Login" />
					<em class="small redirect-notice text-right"><a href="/login/forgot">Forgot password?</a></em>
				</form>

				{% if email_error %}
				<!--jQuery to focus on email input-->
				{% endif %}
				
			{% endif %}
			</div>
		</div>
	</div>
  
	{% endblock %}