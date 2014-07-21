<?php
/**
 *  GET: /users
 ** show all users to admin, show user info to user
 **/
$app->get("/users",$authenticate($app,$session), function () use ($app,$session){
		if( $app->admin ){
			//create a view of users with distinct groups as concat with , separater 
			$sql = $app->db->query('SELECT u.user_id, u.first_name, u.last_name, u.email, u.user_type, (SELECT type_name FROM users_types WHERE type_id = u.user_type) AS user_permissions
			  	FROM users u
			  	GROUP BY u.user_id ORDER BY u.last_name');
			$users = $sql->fetchAll();

			//ERROR: So long as we have users
			if( $users != false ){

				//get the current route to pass to the template
				$current_route = $app->router()->getCurrentRoute();
				$current_route = $current_route->getPattern();

				$app->render('users.php',array(
					'users'=>(isset($users)?$users:''),
					'current_route'=>$current_route,
					'title'=>'Users'
				));
			}
		}elseif( $app->user ){

		}
	});

/**
 *  GET: /users/add, POST: /users/add
 ** add a user
 **/
$app->map('/users/add',$authenticate($app,$session),function () use ($app,$session){
		//if this is a GET request
		if( $app->request()->isGet() ){
			if( $app->admin ){
				
				$found_types = null;

				//get user types
				try{
					$user_types = $app->db->query('SELECT * FROM users_types');
					$result = $user_types->fetchAll();

					if( $result != false ){
						$found_types = $result;
					}

				}catch( PDOException $e){
					//log database errors
					database_errors($e);
					$errors['system_error'] = "System error. Please contact system administrator";
				}

				$app->render('add-users.php',array(
						'submit_url' => '/users/add',
						'user_types'=>$found_types,
						'title'=>'Add Users'
					));
			}
		}elseif( $app->request()->isPost() ){
			if( $app->admin ){

				$first_name = $app->request()->post('first_name','');
				$last_name = $app->request()->post('last_name','');
				$user_type = $app->request()->post('user_type','');
				$email = $app->request()->post('email','');
				$password = $app->request()->post('password','');
				$user_image = $app->request()->post('user_image','');

				if( empty($first_name) ) $errors[] = "first name required";
				if( empty($last_name) ) $errors[] = "last name required";
				if( empty($email) ) $errors[] = "email required";
				if( empty($password) ) $errors[] = "password required";

				if( isset($errors) ){
					if( is_ajax() ){
						$message['errors'] = $errors;
						header('Content-Type: application/json',true, 400);
						echo json_encode($message);
						exit;
					}else{
						$session->flash('errors',$errors);
						$app->redirect('/users/add');
					}
				}

				try{
					$sql = $app->db->prepare('INSERT INTO users (first_name,last_name,email,password,last_login,user_type) VALUES (:first_name,:last_name,:email,:password,NOW(),:user_type)');
					$result = $sql->execute(array(
							'first_name'=>$first_name,
							'last_name'=>$last_name,
							'email'=>$email,
							'password'=>password_hash($password,PASSWORD_BCRYPT,array("cost" => 10)),
							'user_type'=>$user_type[0]
						));

					if( $result != false ){
						//insert user into groups
						$user_id = $app->db->lastInsertId();
						$success[] = 'User '.$first_name.' '.$last_name.' created';

				
					}

				}catch (PDOException $e) {
					//log database errors
					database_errors($e);
					$errors['system_error'] = "System error. Please contact system administrator";
				}

				$session->flash('success', (isset($success)?$success:null));
				$session->flash('errors', (isset($errors)?$errors:null));

				if( is_ajax() ){
					$message[] = (isset($success)?array('success'=>$success):'');
					$message[] = (isset($errors)?array('error'=>$errors):'');
					header('Content-Type: application/json');
					echo json_encode($message);
					exit;
				}else{
					//redirect to the GET method of the current route
					$app->redirect('/users');
				}
			}
		}
	})->via('GET','POST')->name('addUsers');

/**
 *  GET: /users/edit, POST: /users/edit
 ** edit a user
 **/
$app->map('/users/:user_id',$authenticate($app,$session),function ($user_id) use ($app,$session){
		//if this is a GET request
		if( $app->request()->isGet() ){
			if( $app->admin OR $app->user ){

				//get user details
				try{
					$user = $app->db->prepare('SELECT user_id,first_name,last_name,email,last_login FROM users WHERE user_id = :user_id LIMIT 1');
					$user->execute(array('user_id'=>($app->admin?$user_id:$session->get('user_id'))));
					$result = $user->fetch();

					if( $result != false ){
						$found_user = $result;

						//get all types
						try{
							$user_types = $app->db->query('SELECT * FROM users_types');
							$result = $user_types->fetchAll();

							if( $result != false ){
								$all_user_types = $result;

								//get user type
								try{
									$user_types = $app->db->prepare('SELECT type_id FROM users_types WHERE type_id IN (SELECT b.user_type FROM users b WHERE user_id = :user_id)');
									$user_types->execute(array('user_id'=>$user_id));
									$result = $user_types->fetch();

									if( $result != false ){
										$found_user_type = $result;
									}

								}catch( PDOException $e){
									//log database errors
									database_errors($e);
									$errors['system_error'] = "System error. Please contact system administrator";
								}
							}

						}catch( PDOException $e){
							//log database errors
							database_errors($e);
							$errors['system_error'] = "System error. Please contact system administrator";
						}
					}

				}catch( PDOException $e){
					//log database errors
					database_errors($e);
					$errors['system_error'] = "System error. Please contact system administrator";
				}

				$session->flash('success', (isset($success)?$success:null));
				$session->flash('errors', (isset($errors)?$errors:null));

				$app->render('edit-users.php',array(
						'submit_url' => '/users/'.$user_id,
						'user_groups'=>(isset($found_groups)?$found_groups:null),
						'found_user_type'=>(isset($found_user_type)?$found_user_type:null),
						'all_user_types'=>(isset($all_user_types)?$all_user_types:null),
						'user'=>(isset($found_user)?$found_user:null),
						'title'=>'Update '.(isset($found_user)?$found_user->first_name. " ".$found_user->last_name:null)
					));
			}
		}elseif( $app->request()->isPost() ){
			if( $app->admin OR $app->user ){
				$first_name = $app->request()->post('first_name',null);
				$last_name = $app->request()->post('last_name',null);
				$user_type = $app->request()->post('user_type',null);
				$email = $app->request()->post('email',null);
				$selected_groups = $app->request()->post('groups',null);
				$password = $app->request()->post('password','');

				//get the selected user's current password just in case they're NOT changing there password
				try{
					$get_password = $app->db->prepare('SELECT password FROM users WHERE user_id = :user_id LIMIT 1');
					$get_password->execute(array('user_id'=>($app->admin?$user_id:$session->get('user_id'))));
					$result = $get_password->fetch();

					if( $result != false ){
						$old_password = $result->password;
						//die($old_password);
					}
				}catch( PDOException $e ){
					database_errors($e);
					$errors['system_error'] = "System error. Please contact system administrator";
				}
				//update the users information
				try{
					if( $app->admin ){
						$sql = $app->db->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, user_type = :user_type, password = :password WHERE user_id = :user_id LIMIT 1');
						$result = $sql->execute(array(
								'first_name'=>$first_name,
								'last_name'=>$last_name,
								'email'=>$email,
								'user_type'=>$user_type[0],
								'password'=>(empty($password)?$old_password:password_hash($password,PASSWORD_BCRYPT,array("cost" => 10))),
								'user_id'=>$user_id
							));
					}elseif( $app->user ){
						$sql = $app->db->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, password = :password WHERE user_id = :user_id LIMIT 1');
						$result = $sql->execute(array(
								'first_name'=>$first_name,
								'last_name'=>$last_name,
								'email'=>$email,
								'password'=>(empty($password)?$old_password:password_hash($password,PASSWORD_BCRYPT,array("cost" => 10))),
								'user_id'=>$session->get('user_id')
							));
					}

					//insert or update user groups
					if( $result != false ){
					
						//success message
						$success[] = $first_name . ' ' . $last_name . ' successfully updated.'; 
						
					}

				}catch (PDOException $e) {
					database_errors($e);
					$errors['system_error'] = "System error. Please contact system administrator";
				}
				$session->flash('success', (isset($success)?$success:null));
				$session->flash('errors', (isset($errors)?$errors:null));
				//redirect to the GET method of the current route
				$app->redirect('/users/'.$user_id);
			}
		}
	})->via('GET','POST')->name('editUsers');

/**
 *  DELETE: /users/edit
 ** delete a user
 **/
$app->delete('/users/:user_id',$authenticate($app,$session),function ($user_id) use ($app,$session){
		if( $app->admin ){
			try{
				$get_admin = $app->db->query('SELECT type_id FROM users_types WHERE type_name = "admin" LIMIT 1');
        $get_admin_result = $get_admin->fetch();
        if( $get_admin_result != false ){
          $admin_id = $get_admin_result->type_id;

          //verify that we aren't trying to delete the last admin from the database
          $not_last_admin = $app->db->query('SELECT COUNT(user_id) as num_admin FROM users WHERE user_type = '.$admin_id.' LIMIT 1');
          $not_last_admin = $not_last_admin->fetch();
          if( $not_last_admin->num_admin == 1 ){
          	$errors[] = "Cannot delete the last admin. There must be at least 1 admin user.";
          	$last_user = true;
          	$session->flash('errors', $errors);
          	$app->redirect('/users');
          }elseif( $not_last_admin->num_admin != 1){
  					//get user details
						try{
							$sql = $app->db->prepare('SELECT user_id,first_name,last_name FROM users WHERE user_id = :user_id LIMIT 1');
							$sql->execute(array('user_id'=>$user_id));
							$result = $sql->fetch();

							if( $result != false ){
								$found_user = $result;
								$first_name = $result->first_name;
								$last_name = $result->last_name;
							}

							try{
								//get all users to reassign links
								$sql = $app->db->prepare('SELECT user_id,first_name,last_name FROM users WHERE user_id NOT IN (:user_id)');
								$sql->execute(array('user_id'=>$user_id));
								$result = $sql->fetchAll();

								if( $result != false ){
									$all_users = $result;
								}
							}catch( PDOException $e ){
								database_errors($e);
								$errors['system_error'] = "System error. Please contact system administrator";
							}

						}catch( PDOException $e){
							//log database errors
							database_errors($e);
							$errors['system_error'] = "System error. Please contact system administrator";
						}
          }
        }
			}catch( PDOException $e ){
				database_errors($e);
				$errors['system_error'] = "Error deleting user. System administrator has been notified";
			}

			$app->render('edit-users.php',array(
					'user'=>(isset($found_user)?$found_user:null),
					'all_users'=>(isset($all_users)?$all_users:null),
					'title'=>(isset($first_name)?'Delete user '.$first_name.' '.$last_name:'Cannot delete user'),
					'last_admin'=>(isset($last_admin)?$last_admin:false),
					'success'=>(isset($success)?$success:null),
					'errors'=>(isset($errors)?$errors:null)
				));
		}
	})->name('deleteUsers');

/**
 * POST: /users/edit
 ** delete a user permanently
 **/
$app->post('/users/delete/:user_id',$authenticate($app,$session),function ($user_id) use ($app,$session){
		if( $app->admin ){
			try{
				$get_admin = $app->db->query('SELECT type_id FROM users_types WHERE type_name = "admin" LIMIT 1');
        $get_admin_result = $get_admin->fetch();
        if( $get_admin_result != false ){
          $admin_id = $get_admin_result->type_id;

          //verify that we aren't trying to delete the last admin from the database
          $not_last_admin = $app->db->query('SELECT COUNT(user_id) as num_admin FROM users WHERE user_type = '.$admin_id.' LIMIT 1');
          $not_last_admin = $not_last_admin->fetch();

          if( $not_last_admin->num_admin == 1 ){
          	$errors[] = "Cannot delete the last admin. There must be at least 1 admin user.";
          	$last_user = true;
						$session->flash('errors', $errors);
          	$app->redirect('/users');
          }elseif( $not_last_admin->num_admin != 1){
						$new_user = $app->request()->post('new_user',null);

						//see if the deleted user has any links
						$sql = $app->db->prepare('SELECT short_id FROM short_urls WHERE user_id = :user_id LIMIT 1');
						$sql->execute(array('user_id'=>$user_id));
						$result = $sql->fetch();
						$sql->closeCursor();

						//if the user has links
						if( $result != false ){
							//update the deleted users links
							try{
								$sql2 = $app->db->prepare('UPDATE short_urls SET user_id = :new_user WHERE user_id = :user_id');
								$sql2->execute(array('user_id'=>$user_id,'new_user'=>$new_user));
								$result = $sql2->fetch();

								if( $result != false ){
									$found_user = $result;
									$title .= ' '.$result->first_name.' '.$result->last_name;
								}
							}catch( PDOException $e){
								//log database errors
								database_errors($e);
								$errors['system_error'] = "System error. Please contact system administrator (Updating URLs)";
							}
							
							//update the deleted users scripts
							try{
								$update_scripts = $app->db->prepare('UPDATE scripts SET user_id = :new_user WHERE user_id = :user_id');
								$update_scripts->execute(array('user_id'=>$user_id,'new_user'=>$new_user));
								
							}catch( PDOException $e){
								//log database errors
								database_errors($e);
								$errors['system_error'] = "System error. Please contact system administrator (Updating Scripts)";
							}
							
						}

						//delete the user from groups
						try{
							$sql3 = $app->db->prepare('DELETE FROM `users_groups` WHERE `user_id` = :user_id');
							$result = $sql3->execute(array('user_id'=>$user_id));

						}catch( PDOException $e ){
							database_errors($e);
							$errors['system_error'] = "System error. Please contact system administrator (Deleting Groups)";
						}

						//delete the user from the database
						try{
							$sql4 = $app->db->prepare('DELETE FROM users WHERE user_id = :user_id LIMIT 1');
							$result = $sql4->execute(array('user_id'=>$user_id));

							if( $result != false ){
								$success[] = "User deleted.";
							}
						}catch( PDOException $e ){
							database_errors($e);
							$errors['system_error'] = "System error. Please contact system administrator (Deleting User)";
						}
						$session->flash('success', (isset($success)?$success:null));
						$session->flash('errors', (isset($errors)?$errors:null));

						$app->redirect('/users');
					}
				}
			}catch( PDOException $e ){
				database_errors($e);
			}
		}
	});