<?php

/**
 *  GET: /logout
 **/
$app->get("/logout", function () use ($app,$session) {
		$session->flush();
		//$app->view()->setData('user_type', null);
		$session->flash('success',array('Successfully logged out'));
		$app->redirect('/login');
	});


/**
 *  GET: /login,
 ** POST: /login
 **/
$app->map("/login", function () use ($app,$session) {

		if($session->has('user_type')){
			$app->redirect('/');
		}

		//show login page if it's a GET request
		if( $app->request()->isGet() ){
			$login_error = $session->get('login_error');
			$email_error = $session->get('email_error');
			$email_value = $session->get('email_value');
			$redirect = $session->get('redirect');
			
			$session->flush();
			
			$app->render('login.php', array(
					'bodyclass' => 'login',
					'login_error' => $login_error,
					'email_error' => $email_error,
					'email_value' => $email_value,
					'redirect' => $redirect,
					'title' => 'Login'
				));
		}
		//log in user if a POST request
		elseif( $app->request()->isPost() ){
			$email = $app->request()->post('email');
			$password = $app->request()->post('password');
			$redirect = $app->request()->post('redirect');

			try{
				$sql = $app->db->prepare('SELECT user_id,first_name,last_name,password,users_types.type_name FROM users,users_types WHERE email = :email AND type_id = user_type LIMIT 1');
				$sql->execute(array('email'=>$email));
				$result = $sql->fetch();

				if( $result != false and password_verify($password,$result->password) ){
					$session->flush();
					$session->put('first_name',$result->first_name);
					$session->put('last_name',$result->last_name);
					$session->put('user_type',$result->type_name);
					$session->put('user',$email);
					$session->put('user_id',$result->user_id);
				}else{
					$session->flush();
					$session->put('email_error', true);
					$session->put('email_value', $email);
					$session->put('redirect', $redirect);
					$app->redirect('/login');
				}
			}catch (PDOException $e) {
				//log database errors
				database_errors($e);
				$errors['system_error'] = "System error. Please contact system administrator";
			}

			//redirect user to the URL they were trying to go to after a successful login
			if ( $redirect ) {
				$app->redirect($redirect);
			}else{
				$app->redirect('/');
			}
		}

	})->via('GET','POST')->name('login');


/**
 *  GET: /login/forgot
 ** POST: /login/forgot
 ** Forgot password functions
 **/

$app->map("/login/forgot", function () use ($app,$session) {

		if($session->has('user_type')){
			$app->redirect('/');
		}

		//Setting control variables
		$email_error = false;
		$email_found = false;
		$pw_otk = false;
		
		if( $app->request()->isPost() ){
			//get email from post
			$email = $app->request()->post('email');
		
			//search DB for email
			try{
				$sql = $app->db->prepare('SELECT email FROM users WHERE email = :email LIMIT 1');
				$sql->execute(array('email'=>$email));
				$result = $sql->fetch();

				//if we found a valid email address
				if( $result != false ){
				
					//set control variable
					$email_found = true;
					
					//generate pw_otk, save to DB
					$pw_otk = md5(time() + 121985 . 'cMaL12114ShO0t!nGz0oRw7lLm4K3uDie' . $email);
					
					$sql = $app->db->prepare('UPDATE users SET pw_otk = :pw_otk WHERE email = :email');
					$sql->execute(array(
						'email' => $email,
						'pw_otk' => $pw_otk
					));

					//send link to change password to email
					email_pw_reset($email, $pw_otk);
					
				//if NOT found
				}else{
					//error (no email found)
					$email_error = true;
				}
			}catch (PDOException $e) {
				//log database errors
				database_errors($e);
				$errors['system_error'] = "System error. Please contact system administrator";
				$session->flash('errors', $errors);
			}
			
		}	
			
		$app->render('forgot.php', array(
			'bodyclass' => 'login',
			'email_error' => $email_error,
			'email_found' => $email_found,
			'pw_otk' => $pw_otk
		));

})->via('GET','POST');

/**
 *  GET: /login/reset
 ** POST: /login/reset
 ** Reset password pw_otk check & getting new PW from user
 **/

$app->get("/login/reset/:pw_otk", function ($pw_otk) use ($app,$session) {

	if($session->has('user_type')){
		$app->redirect('/');
	}

	//Setting control variables
	$pw_otk_found = false;
	
	//if pw_otk exists in DB
	try{
		
		//set otk in session, will use this to reset the right user
		//offer user password box to reset
		$sql = $app->db->prepare('SELECT email FROM users WHERE pw_otk = :pw_otk LIMIT 1');
		$sql->execute(array('pw_otk'=>$pw_otk));
		$result = $sql->fetch();

		//if we found a valid email address
		if( $result != false ){
		
			//set control variable
			$pw_otk_found = true;
			$session->put('pw_otk', $pw_otk);

		//if NOT found
		}else{
			//error redriect to login page 
			$app->redirect('/login');
		}
	}catch (PDOException $e) {
		//log database errors
		database_errors($e);
		$errors['system_error'] = "System error. Please contact system administrator";
		$session->flash('errors', $errors);
	}
	
				
	
	$app->render('forgot.php', array(
			'bodyclass' => 'login',
			'pw_otk_found' => $pw_otk_found,
			'pw_otk' => $pw_otk
		));
});

/**
 *  GET: /login/reset
 ** POST: /login/reset
 ** Reset password pw_otk check & getting new PW from user
 **/

$app->post("/login/reset", function () use ($app,$session) {

	if($session->has('user_type')){
		$app->redirect('/');
	}

	//Setting control variables
	$password =  $app->request()->post('password',null);
	$pw_reset = false;
	
	//hash new password
	$password = password_hash($password,PASSWORD_BCRYPT,array("cost" => 10));
	
	//update password on user record (search with $session pw_otk
	try{
		$sql = $app->db->prepare('UPDATE users SET password = :password WHERE pw_otk = :pw_otk');
		$sql->execute(array(
			'password' => $password,
			'pw_otk' => $session->get('pw_otk')
		));
		
		//remove pw_otk on user record
		$sql = $app->db->prepare('UPDATE users SET pw_otk = NULL WHERE pw_otk = :pw_otk');
		$sql->execute(array(
			'pw_otk' => $session->get('pw_otk')
		));

		//forget the session variable :)
		$session->forget('pw_otk');
		
		//Inform the user
		$pw_reset = true;
		
	} catch (PDOException $e) {
		//log database errors
		database_errors($e);
		$errors['system_error'] = "System error. Please contact system administrator";
		$session->flash('errors', $errors);
	}
	
	//render the login screen 	
	$app->render('login.php', array(
			'bodyclass' => 'login',
			'pw_reset' => $pw_reset
		));
});