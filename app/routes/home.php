<?php

/*
 * TRKS.IT Dashboard
 * Viewing the most recent trks.it shortened links by date range for the current user
 * GET: /
 */
$app->get("/", $authenticate($app,$session), function () use ($app,$session) {
	
	
	/*
	try{
		$sql = $app->db->prepare('SELECT * FROM groups WHERE group_id IN (SELECT ug.group_id FROM users_groups ug WHERE user_id = :user_id) ORDER BY group_name ASC');
		$sql->execute(array('user_id'=>$session->get('user_id')));
		$result = $sql->fetchAll();

		if( $result != false ){
			$groups = $result;
		}
	}catch( PDOException $e ){
		database_errors($e);
		$session->flash('errors', array("Database error - Webmaster has been notified. #Sorry!"));
			error_redirect($app);
	}
	*/



	//Render the template with variables needed.
	$app->render('home.php', array());
});
