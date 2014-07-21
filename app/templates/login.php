{% extends 'header.php' %}

	{% block pagecontent %}

<div class="row">
	<div class="login-wrap col-md-4 col-md-offset-4">
		<img src="/images/logo.png" class="login-logo" />

		<div class="login-box">
			{% if pw_reset %}
			<p><span class="error">Your password was successfully reset.</span></p>		  
			{% endif %}
			
			{% if login_error is not null %}
			<p><span class="error">Please login first...</span></p>
			{% endif %}
			
			{% if email_error is not null %}
			<p><span class="error">Email/Password combination failed</span></p>
			{% endif %}
			
			<form action="/login" method="POST" class="login-form">
				<div class="form-group">
					<label for="email">Email</label><br />
					<input type="email" name="email" id="email" value="{{ email_value }}" class="form-control" required/>
				</div>
				
				<div class="form-group">
					<label for="password">Password</label><br /> 
					<input type="password" name="password" id="password" class="form-control" required/> 
				</div>
								
				<input type="hidden" name="redirect" value="{{ redirect }}">
				<input type="submit" class="btn btn-primary" value="Login" />
				<em class="small redirect-notice text-right"><a href="/login/forgot">Forgot password?</a></em>
			</form>

			{% if email_error is not null %}
			<!--jquery to focus on PW field-->
			{% endif %}

		</div>
	</div>
</div>
{% endblock %}