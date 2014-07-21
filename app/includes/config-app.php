<?php
/*****************************
 * Session settings
 *****************************/
//create directory to hold sessions
if( !file_exists('../sessions') ){
  mkdir('../sessions', fileperms(__DIR__), true);
}
//set the PHP session options
session_save_path('../sessions');
ini_set('session.gc_probability', 1);
ini_set('session.hash_function','sha256');
ini_set('session.name','webmechanix_dashboard');
ini_set('session.cookie_httponly',true);
ini_set('session.entropy_length',128);
ini_set('session.gc_maxlifetime',36000);

/*****************************
 * Timezone settings
 *****************************/
date_default_timezone_set('America/New_York');

/*****************************
 * Error settings
 *****************************/
//enable errors for logging
if( $app->config('debug') === true ){
  ini_set('display_errors',1);
  ini_set('display_startup_errors',1);
}
error_reporting(-1);
ini_set('log_errors',1);
ini_set('error_log','../errors/php.log');

//email fatal errors
register_shutdown_function('email_errors');

//set the error handler for Slim
$app->error(function (\Exception $error) use ($app){
  if( $app->config('debug') === false ){
    email_errors();
    $app->redirect('/errors');
  }
});

email_errors();
//handle PHP errors to troubleshoot
function email_errors($database_error = ''){
  
  //only email errors if we're not in the live environment
  if( $_SERVER['HTTP_HOST'] === PRODUCTION ){
    $error = error_get_last();

    // set the importance of the email using email headers
    $flag = false;
    // get the error type
    switch($error['type']){
      case 1: $error_type = "Fatal Run-Time";$flag = true;
      break;
      case 2: $error_type = "Warning";
      break;
      case 4: $error_type = "Parse";
      break;
      case 8: $error_type = "Notice";
      break;
      case 16: $error_type = "Fatal Core Initial Starup";$flag = true;
      break;
      case 32: $error_type = "Warning Core Initial Startup";
      break;
      case 64: $error_type = "Fatal Compile";$flag = true;
      break;
      case 128: $error_type = "Warning Compile";
      break;
      case 256: $error_type = "User Generated";
      break;
      case 512: $error_type = "User Generated Warning Message";
      break;
      case 1024: $error_type = "User Generated Notice Message";
      break;
      case 4096: $error_type = "Catchable Fatal Error";$flag = true;
      break;
      case 8192: $error_type = "Deprecated";
      break;
      case 16384: $error_type = "User Deprecated";
      break;
      default: $error_type = "Any";
    }

    if ( !is_null($error) ||  !empty($database_error) ) {
      $mail = new PHPMailer();
      //send an email to be logged
      $mail->From = "trksit@humana.com";
      $mail->FromName = "Humana trks.it server";
      $mail->addAddress('debug@trks.it', 'trks.it');
      $mail->isHTML(true);
      $mail->Subject = "Humana Healthcare - ".$error_type." Error";
      
      if( empty($database_error) ){
        $mail->Body = "<h2>$error_type occured</h2>"."<table border='1'><tr><td>File</td><td>".$error['file']."</td></tr><tr><td>Message</td><td>".$error['message']."</td></tr><tr><td>Line</td><td>".$error['line']."</td></tr><tr><td></tr>";
      }else{
        $mail->Body = $database_error;
      }
    
      //grab the POST parameters
      if( $_SERVER['REQUEST_METHOD'] === "POST" ){
        $post_data = $_POST;
        $post_string = null;
        foreach($post_data as $key=>$value){
          $post_string .= "key: ".$key. "value: ".$value."\n";
        }
         $mail->Body .= "<tr><td>Post Data</td><td>".$post_string."</td></tr>";
      }
      //grab the GET parameters
      if( $_SERVER['REQUEST_METHOD'] === "GET" ){
        $get_data = $_GET;
        $get_string = null;
        foreach($get_data as $key=>$value){
          $get_string .= "key: ".$key. "value: ".$value."\n";
        }
        $mail->Body .= "<tr><td>Get Data</td><td>".$get_string."</td></tr>";
      }

      $mail->Body .= "</table><p>Stack Trace</p><p>".debug_backtrace()."</p>";

      if( $flag === true ){
        $mail->Priority = 1;
        // MS Outlook custom header
        // May set to "Urgent" or "Highest" rather than "High"
        $mail->AddCustomHeader("X-MSMail-Priority: High");
        // Not sure if Priority will also set the Importance header:
        $mail->AddCustomHeader("Importance: High");
      }

      $mail->send();
    }
  }
}

/*****************************
 * Config
 *****************************/
function cwdurl(){
  if(isset($_SERVER['HTTPS'])){
      $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
  }
  else{
      $protocol = 'http';
  }
  return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

define ('TRKSIT', 'trks.it');
define ('TRKSIT_URL', cwdurl());
define ('TRKSIT_GO', '/go');
define ('TRKSIT_API', 'https://api.trks.it');

//set the application name
$app->setName('WebMechanix Dashboard');

/*****************************
 * App hooks
 *****************************/
$app->hook('slim.before.dispatch', function() use ($app,$session) {
 
  $app->view()->setData('year', date('Y') );
  //pass a variable to the view based on session value
  $app->view()->setData('user_type', ($session->has('user_type')?$session->get('user_type'):null) );
  //pass the user_id to the view
  $app->view()->setData('user_id', ($session->has('user_id')?$session->get('user_id'):null) );
  //pass the page project name to the view
  $app->view()->setData('projectname', $app->getName() );
  
  //pass the header navigation to view
  $currentRoute = $app->request()->getResourceUri();
  $app->view()->setData('navigation', get_navigation($currentRoute, $session, false));
  $app->view()->setData('mobile_navigation', get_navigation($currentRoute, $session, true));
  //pass the HTTP method to the view
  $app->view()->setData('http_method', $app->request()->getMethod() );
  
  //pass the first name, last name and email address in each template
  if( $session->has('user_type') ){
    $app->view()->setData('first_name', ($session->has('user_type')?$session->get('first_name'):null) );
    $app->view()->setData('last_name', ($session->has('user_type')?$session->get('last_name'):null));
    $app->view()->setData('user_email', ($session->has('user_type')?$session->get('user'):null));
  }

  

});

$app->hook('slim.before', function() use ($app,$session) {

  //Database functions
  if( file_exists('../app/includes/config.php') ){

    //grab the groups from the database to show them in a modal window
    /*
    $get_groups = $app->db->query('SELECT * FROM groups');
    $groups = $get_groups->fetchAll();
    $app->view()->setData('groups', (!empty($groups)?$groups:null) );

    //grab the user types from the database to show them in a modal window
    $types = $app->db->query('SELECT * FROM users_types');
    $user_types = $types->fetchAll();
    $i = 0;;
    foreach( $user_types as $type ){
      $user_types[$i]->type_name = ucfirst($type->type_name);
      $i++;
    }
    //pass the user types to the template
    $app->view()->setData('user_types', (!empty($user_types)?$user_types:null) );

    //get all users in database so admin can add users to groups from modal box
    $sql = $app->db->query('SELECT user_id,first_name,last_name FROM users');
    $result = $sql->fetchAll();

    if( $result != false ){
      $users = $result;
    }
    $app->view()->setData('users', (!empty($users)?$users:null) );
    */

    //pass success messages to the views
    $app->view()->setData('success', ($session->has('success')?$session->get('success'):null) );
    //pass success messages to the views
    $app->view()->setData('errors', ($session->has('errors')?$session->get('errors'):null) );

    //destroy the session keys that hold the success, error messages, and page title
    if( $session->has('success') )
      $session->forget('success');
    if( $session->has('errors') )
      $session->forget('errors');
    if( $session->has('title') )
      $session->forget('title');

  }

});
