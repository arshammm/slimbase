<?php
//load composer classes
require "../app/vendor/autoload.php";

//set production/live environment host
define('PRODUCTION','trksit.kindred.com');

// Load Slim and Twig template path
$app = new \Slim\Slim(array(
	'templates.path' => '../app/templates',
  'debug' => ($_SERVER['HTTP_HOST'] === PRODUCTION?false:true),
  'log.enabled' => true
));

// Prepare view 
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
  'charset' => 'utf-8',
  'cache' => ($_SERVER['HTTP_HOST'] === PRODUCTION?true:false),
  'auto_reload' => true,
  'strict_variables' => false,
  'autoescape' => false
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

//load Laravel's session library
use Illuminate\Session\Store as Session;
$session = new Session;

/*
 * load library files (classes, functions, etc...)
 */
//sensitive data that should not be synced with Git
@include "../app/includes/config.php";
@include "../app/includes/config-app.php";
require "../app/includes/functions.php";
require "../app/includes/auth.php";

//load the update script if we're not in the go or cookies routes
$path = parse_url(full_url($_SERVER),PHP_URL_PATH);
if( is_string($path) ){
  $route = explode('/', $path);
  $route = $route[1];

  if( !in_array($route,array('go,cookies')) ){
    require "../app/includes/updates.php";//store updates to app (database table modifications, etc...)
  }
}

//Database functions
if( file_exists('../app/includes/config.php') ){
  require "../app/includes/db.php"; 
}elseif( !file_exists('../app/includes/config.php') AND $_SERVER["REQUEST_URI"] !== "/install" ){
  //redirect to the install script if the config file has not been created or if the user is not currently at the install page
  header('Location: '.cwdurl().'/install',true,302);
  exit;
}


//Redirect to login if there is not a user type in the session
//Otherwise, PASS so we hit the dashboard route (/)
$app->get("/", $authenticate($app,$session), function () use ($app,$session) {
  if( $session->has('user_type') ){
  	$app->pass();	  
  } else {
    $app->redirect('/login');  
  }
});

//route to show when errors occur on the live site
$app->get('/errors',function () use ($app,$session){

  $session->flash('errors',array('We just found an error with trks.it. The problem has been logged and will be fixed ASAP. Thank you for your patience.'));
  $app->render('errors.php');
});

//route to show when we reach a route that's NOT defined
$app->notFound(function () use ($app,$session) {
  $app->view()->setData('year', date('Y') );
  //pass a variable to the view based on session value
  $app->view()->setData('user_type', ($session->has('user_type')?$session->get('user_type'):null) );
  //pass the page project name to the view
  $app->view()->setData('projectname', $app->getName() );
  
  //pass the header navigation to view
  $currentRoute = $app->request()->getResourceUri();
  $app->view()->setData('navigation', get_navigation($currentRoute, $session, false));
  $app->view()->setData('mobile_navigation', get_navigation($currentRoute, $session, true));
  //pass the HTTP method to the view
  $app->view()->setData('http_method', $app->request()->getMethod() );
  
  //pass the first anme, last name and email address in each template
  if( $session->has('user_type') ){
    $app->view()->setData('first_name', ($session->has('user_type')?$session->get('first_name'):null) );
    $app->view()->setData('last_name', ($session->has('user_type')?$session->get('last_name'):null));
    $app->view()->setData('user_email', ($session->has('user_type')?$session->get('user'):null));
  }

  if( $session->has('user_type') ){
    $app->render('404.php',array(
      'title'=>'Page Not Found'
    ));
  }else{
    $session->put('login_error', true);
    $app->redirect('/login');
  }
});

$app->post('/hits/:url_id',function($url_id) use ($app){
  //TLD is false until there's an error.. then true, which redirects to the TLD set by admin
  $TLD = false;
  
  //ERROR: is the $urlID an int?
  if(!is_numeric($url_id)){$TLD = true;}
  //increment hit counter
  if(!$TLD){
  
    $today = date('Y-m-d');
    
    try{
      $sql = $app->db->prepare('INSERT INTO hit_dates (hit_date, hits, short_id) VALUES (:today, 1, :url_id) ON DUPLICATE KEY UPDATE hits = hits + 1');
      $sql->execute(array(
        'today' => $today,
        'url_id' => $url_id
      ));
      echo "go";
    }catch ( PDOException $e ){
      database_errors($e);
      $TLD = true;
    }
  }
});

/*
 * load routes based on the URL file path
 */
$path = parse_url(full_url($_SERVER),PHP_URL_PATH);
if( is_string($path) ){
  $route = explode('/', $path);
  $route = $route[1];

  if( in_array($route,array('login','logout')) ){
    require "../app/routes/login.php";//route to login/logout
  }elseif( in_array($route,array('')) ){
    require "../app/routes/home.php";//route to control cookies
  }elseif( in_array($route,array('users')) ){
    require "../app/routes/users.php";//route to manage users
  }
}elseif( is_null($path) ){
  //load all the routes if the path wasn't found
  require "../app/routes/login.php";//route to login/logout
  require "../app/routes/home.php";//route to control cookies
  require "../app/routes/users.php";//route to manage users
}

//Run it!
$app->run();