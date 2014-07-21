<?php
//authenticate that the user is logged in
$authenticate = function ($app,$session) {
	return function () use ($app,$session) {

		if( $session->has('user_type') ){
			if ($session->get('user_type') === "admin")
				$app->admin = true;
			elseif ($session->get('user_type') === "user")
				$app->user = true;

		}else{
			$session->put('redirect',$app->request()->getPathInfo());
			$session->put('login_error', true);
			$app->redirect('/login');
		}
	};
};