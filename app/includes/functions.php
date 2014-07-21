<?php
//Navigation :)
function get_navigation($route, $session, $mobile = false){
	//Setting the variable for later use.
	global $navigation;
	$navigation = '';
	$dashboard = $campaigns = $scripts = $groups = $users = '';
	$user_type = $session->has('user_type') ? $session->get('user_type') : null;
	$first_name = $session->get('first_name');
	$last_name = $session->get('last_name');
	$user_email = $session->get('user');
	$user_id = $session->get('user_id');
	
	
	//setting current page by each <li> name
	$route = explode("/", substr($route, 1));
	$route = current($route);
	$$route = "active";
	if(!$route) $dashboard = "active";
	
	//Setting the div or nav output
	if($mobile){
		$navigation .= '<div class="navmenu navmenu-default navmenu-fixed-left offcanvas" id="main-mobile-nav">';
	} else {
		$navigation .= '<nav class="col-lg-9 col-md-9 col-sm-9 hidden-xs" id="main-nav">';	
	}	

	//Everyone gets the dashboard
	$navigation .= '
	<ul>
		<li class="'.$dashboard.'">
		    <a href="/"><i class="nav-icon fa fa-dashboard"></i> Dashboard</a>
		</li>
	';
	
	//Only admins get the rest of the menu
	if($user_type == "admin"){
		$navigation .= '
	        <li class='.$users.'>
	            <a href="/users"><i class="nav-icon fa fa-users"></i> Users</a>
	        </li>
	        <li class="dropdown">
	            <a href="#" class="dropdown-toggle add" data-toggle="dropdown"><i class="nav-icon fa fa-plus"></i> New<b class="caret"></b></a>
	            <ul class="dropdown-menu add pull-right">
	                <li><a href="/users/add" data-action="add user"><i class="dropdown-icon fa fa-user"></i> Add User</a></li>
				</ul>
			</li>
		';
	}
            
	$navigation .= '
			<li class="dropdown settings">
	            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="nav-icon fa fa-cog"></i> <span class="hidden-sm">Hi ' . $first_name . '!</span><b class="caret"></b></a>
	            <div class="dropdown-menu pull-right">
	            	<div id="user-details-panel">
            		<p>' . $first_name . ' ' . $last_name . '</p>
            		<p>' . $user_email . '</p>
            		<p><a class="btn btn-sm btn-default" href="/users/' . $user_id . '"><i class="fa fa-pencil"></i> Edit Profile</a> <a class="btn btn-sm btn-danger pull-right" href="/logout"><i class="fa fa-power-off"></i> Logout</a></p>
								</div>
							</div>
			</li>
	';
            
	$navigation .= '</ul>';
	
	//ending divs & navs
	if($mobile){$navigation .= '</div>';} else {$navigation .= '</nav>';}
	
	return $navigation;	
}

//set original source, medium, campaign cookie
function original_cookies($party = false, $notgo = false){
	if( isset($_POST['utmz']) ){
		list($source,$campaign,$medium) = explode('|',$_POST['utmz']);
		//source
		$source = explode('=',$source);
		$source = $source[1];
		//campaign
		$campaign = explode('=',$campaign);
		$campaign = $campaign[1];
		//medium
		$medium = explode('=',$medium);
		$medium = $medium[1];
	}else{
		$ga_parse = new GA_Parse($_COOKIE);
		$source = (isset($_GET['utm_source'])?$_GET['utm_source']:$ga_parse->campaign_source);
		$medium = (isset($_GET['utm_medium'])?$_GET['utm_medium']:$ga_parse->campaign_medium);
		$campaign = (isset($_GET['utm_campaign'])?$_GET['utm_campaign']:$ga_parse->campaign_name);
	}

	//set original source, medium and campaign
	setcookie('original_source',$source,time()+400000,'/','.kindred.com',false);
	setcookie('original_medium',$medium,time()+400000,'/','.kindred.com',false);
	setcookie('original_campaign',$campaign,time()+400000,'/','.kindred.com',false);

	//set converting source, medium and campaign
	converting_cookies($party, $notgo);
}
//set converting source, medium, campaign cookie
function converting_cookies($party = false, $notgo = false){
	if( isset($_POST['utmz']) ){
		list($source,$campaign,$medium) = explode('|',$_POST['utmz']);
		//source
		$source = explode('=',$source);
		$source = $source[1];
		//campaign
		$campaign = explode('=',$campaign);
		$campaign = $campaign[1];
		//medium
		$medium = explode('=',$medium);
		$medium = $medium[1];
	}else{
		$ga_parse = new GA_Parse($_COOKIE);
		$source = (isset($_GET['utm_source'])?$_GET['utm_source']:$ga_parse->campaign_source);
		$medium = (isset($_GET['utm_medium'])?$_GET['utm_medium']:$ga_parse->campaign_medium);
		$campaign = (isset($_GET['utm_campaign'])?$_GET['utm_campaign']:$ga_parse->campaign_name);
	}
	
	//set converting source, medium and campaign
	setcookie('converting_source',$source,time()+400000,'/','.kindred.com',false);
	setcookie('converting_medium',$medium,time()+400000,'/','.kindred.com',false);
	setcookie('converting_campaign',$campaign,time()+400000,'/','.kindred.com',false);

	if($party){
		//check trksit_party cookie
		$trksit_party = isset($_COOKIE['trks_party']) ? $_COOKIE['trks_party'] : false;

		//if has value AND it's not both parties already
		if($trksit_party AND $trksit_party != 'Both'){
			//if value != $party
			if($trksit_party != $party){
				//set cookie trksit_party value(Both Parties)
				setcookie('trks_party','Both',time()+400000,'/','.kindred.com',false);
			} 	
		} else if(!$trksit_party) {
			//set cookie trksit_party value($party)
			setcookie('trks_party',$party,time()+400000,'/','.kindred.com',false);
		}
			
	}
	
	if($notgo)
		echo isset($_COOKIE['trks_party']) ? $_COOKIE['trks_party'] : "";
		
}



//log database errors
function database_errors($error = null){
	//create error logging folder if it doesn't exist already
	if( !file_exists('../errors') ){
		mkdir('../errors', fileperms(__DIR__), true);
		$fp = fopen('../errors/database.log', 'wb');
		fwrite($fp, '');
		fclose($fp);
	}
	error_log( "===".date('m-d-Y g:i:sa')."=== \n".$error."\n", 3, "../errors/database.log");

  //email errors if in production mode
	if( $_SERVER['HTTP_HOST'] === PRODUCTION ){
		email_errors('Database Error',$error);
	}else{
    trigger_error($error,E_USER_NOTICE);
  }

}
//recursive, multidimensional array search
function in_array_r($needle, $haystack, $strict = false) {
  foreach ($haystack as $item) {
    if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
      return true;
    }
  }

  return false;
}

//verify that database tables/columns exists. create columns if they don't exist
//@link http://stackoverflow.com/questions/12096830/php-pdo-check-for-multiple-columns-in-mysql-table-and-create-the-ones-that-dont
function verify_tables($database_object,$table,$fields,$db_name){
  foreach($fields as $field => $type){
    $check_column = $database_object->query('SELECT COUNT(*) as table_columns FROM information_schema.tables WHERE table_schema = "'.$db_name.'" AND table_name = "'.$table.'" AND column_name = "'.$field.'"');
    $result = $status_table->fetch();
    if( (int) $result->table_columns === 0 ){
      $verify = $database_object->prepare("ALTER TABLE `$table` ADD `$field` $type;");
      try {
        $verify->execute();
      } catch (PDOException $e) {
        database_errors($e);
      }
    }
  }

  return 1;
}

//check if a string is a JSON object (if communicationg with APIs)
function isJson($string) {
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}
//catch fatal errors
function fatal_error_handling(){
	$errfile = "unknown file";
  $errstr  = "shutdown";
  $errno   = E_CORE_ERROR;
  $errline = 0;

  $error = error_get_last();

  if( $error !== NULL) {
    $errno   = $error["type"];
    $errfile = $error["file"];
    $errline = $error["line"];
    $errstr  = $error["message"];
  }

}

//redirect to error page on live site
function error_redirect($app){
	if( $app->config('debug') === false ){
		$app->redirect('/errors');
	}
}

//detect if the request is from ajax (a library that sends these variables)
function is_ajax(){
	if( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' )
			return true;
		else
			return false;
}

//generate random number
function generate_random($length=10) {
  $salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  $len = strlen($salt);
  $random = null;

  mt_srand(10000000*(double)microtime());
  for ($i = 0; $i < $length; $i++) {
     $random .= $salt[mt_rand(0,$len - 1)];
  }

  return $random;
}


function url_origin($s, $use_forwarded_host=false){
  $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
  $sp = strtolower($s['SERVER_PROTOCOL']);
  $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
  $port = $s['SERVER_PORT'];
  $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
  $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME']);
  return $protocol . '://' . $host . $port;
}

//get the current full URL
function full_url($s, $use_forwarded_host=false){
  return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

/*
 * Send email to reset password function
 */
function email_pw_reset($email, $pw_otk){

	$mail = new PHPMailer();
  //send an email to be logged
  $mail->From = "kindred@trks.it";
  $mail->FromName = "Kindred trks.it";
  $mail->addAddress($email);
  $mail->isHTML(true);
  $mail->Subject = "Reset Password - Kindred trks.it";
  $mail->Body = '
  <h2>Password Reset</h2>
  <p>Hello, please click below to change your password on Kindred trks.it (trksit.kindred.com):</p>
  <a href="'.cwdurl().'/login/reset/'.$pw_otk.'">'.cwdurl().'/login/reset/'.$pw_otk.'</a>
  <p>If you did not make this request, please do NOT click the link</p>
  ';
  $mail->send();

}

/*
 * TOKEN FUNCTIONS
 */
function resetToken($app){
	//Set the API URL
	$apiURL = TRKSIT_API . '/token?grant_type=creds';

	//Get keys from database	
	$sql = $app->db->query('SELECT * FROM trks_keys WHERE key_id = 1');
	$result = $sql->fetchAll();

	$curl = curl_init($apiURL);
	$data = array(
		'client_id' => $result[0]->public_key,
		'client_secret' => $result[0]->private_key
	);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
  //don't send chuncked data to API. Send all data as one request
  curl_setopt( $curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
  //load the SSL certificate verifier file
  curl_setopt ($curl, CURLOPT_CAINFO, dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."certificates".DIRECTORY_SEPARATOR."cacert.pem");
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
  //load the SSL certificate verifier file
  curl_setopt ($curl, CURLOPT_CAINFO, dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."certificates".DIRECTORY_SEPARATOR."cacert.pem");
	$result = curl_exec( $curl );
	curl_close($curl);

	if( isJson($result) === true ){

		//Decode the result
		$output = json_decode($result);
		//If there's an error, return the error message -- else, return false (no error).
		if($output->error){
			return $output->msg;
		} else {
			//set access token & expiration date
			$access_token = $output->code->access_token;
			$expiration_date = $output->code->expires;
			
			//save token to database
			$sql = $app->db->prepare('UPDATE trks_keys SET access_token = :access_token, expiration_date = :expiration_date WHERE key_id = 1');
			$sql->execute(array(
				'access_token' => $access_token,
				'expiration_date' => $expiration_date,
			));
			
			return false;
		}
	}
	
}

//decode chunked data. Sometimes neccessary for cURL requests
function decode_chunked($str) {
  for ($res = ''; !empty($str); $str = trim($str)) {
    $pos = strpos($str, "\r\n");
    $len = hexdec(substr($str, 0, $pos));
    $res.= substr($str, $pos + 2, $len);
    $str = substr($str, $pos + 2 + $len);
  }
  return $res;
}

//have a list of valid top-level domains to use for cookie tracking
function get_base_domain($domain){
	$domain = strtolower($domain);
	
	//get the pieces of the domain
	$URL_parts = parse_url($domain);
	
	//find the extension
	$host_parts = explode(".", $URL_parts["host"]);
	
	//get the extension
	$extension = end($host_parts);
	
	//get the TLD without extension
	$tld = prev($host_parts);
	
	return "." . $tld . "." . $extension;
	
}


function check_token($app, $session){
	
	//Get expiration date from trks_keys table in DB
	try{
		$sql = $app->db->query('SELECT expiration_date FROM trks_keys WHERE key_id = 1');
		$result = $sql->fetchAll();

		if($result != false){
			$expiration_date = $result[0];
		} else {
			$session->flash('errors', array('No trks.it keys founds in database'));
			$app->redirect('/');
		}
	} catch ( PDOException $e ){
		database_errors($e);
		$errors['system_error'] = "System error. Please contact system administrator";
	}

	//Check against NOW()
	//IF NOW() > expiration, continue
	//Otherwise, call resetToken()
	//If true is returned, then continue
	
	if(time() > intval($expiration_date->expiration_date)){

		//reset the token
		$tokenReset = resetToken($app);

		//if there's an error resetting token, set error
		if($tokenReset){

			//Otherwise, display error returned.
			$session->flash('errors', array($tokenReset));
			$app->redirect('/');
		}
	}
	
}


//list of all domains that are allowed to set cookies for this domain
function allowed_domains($domain){
	$allowed_domains = array('.kindredhealthcare.com','.kindredathome.com','.rehabcare.com','.averycrossings.com','.villageatlaurellake.com','.villagecrossings.com','.themonarchcenter.com','.fountainsonthegreens.com','.khrehabnortheasthouston.com','.khrehabclearlake.com','.khrehabarlington.com','.khrehabcentraltexas.com','.kindredbns.com','.khmorriscounty.com','.khlouisville-jewish.com','.khwayne.com','.kh-havertown.com','.khnatick.com','.khstoughton.com','.kindrednorthshore.com','.kindredcleveland.com','.khthepalmbeaches.com','.kindredhospitallvds.com','.kindredrahway.com','.kindredsouth.com','.kindredocala.com','.kindredbos.com','.kindredatlanta.com','.khmelbourne.com','.khphoenixnw.com','.khchicagonorth.com','.kindredhospitalla.com','.kindredlouisville.com','.kindredmansfield.com','.kindredhospitalpittsburgh.com','.kindredoklahoma.com','.khontario.com','.khbayareahouston.com','.kindredphila.com','.khphoenix.com','.khsanantonio.com','.kindredsandiego.com','.kindredhospitalsfba.com','.kindredhospitalseattle.com','.kindredstlouis.com','.kindredstpete.com','.kindredhospitalsyc.com','.kindredcentraltampa.com','.khtampa.com','.khtucson.com','.khwestminster.com','.kindredhospitalbrea.com','.kindredalbuquerque.com','.kindredhospitalarl.com','.khdallas.com','.khwhiterock.com','.kindredhospitalnola.com','.khnashville.com','.kindredhospitallvf.com','.kindredchattanooga.com','.kindredlakeshore.com','.khchicagocentral.com','.kindrednorthlake.com','.khcoralgables.com','.kh-denver.com','.kindredfortworth.com','.khfortlauderdale.com','.khnorthflorida.com','.khindysouth.com','.khsfhollywood.com','.khgreensboro.com','.khhoustonnw.com','.kindredhospitalfwsw.com','.khhouston.com','.kindredhospitalindy.com','.kindredhospitalkc.com','.kindredhospitallvs.com','.kindredgateway.com','.kindredlamirada.com','.kindredhospitalwv.com','.kindredhospitalhv.com','.kindredsantaana.com','.kindredsangabriel.com','.khdayton.com','.khstanthonys.com','.khseattlefirsthill.com','.khbaldwinpark.com','.khriverside.com','.khsouthbay.com','.kindreddetroit.com','.khnorthland.com','.khcentralohio.com','.khlima.com','.khsugarland.com','.khtownandcountry.com','.khtomball.com','.khheights.com','.khmidtown.com','.khnwindiana.com','.khelpaso.com','.khdallascentral.com','.kheasthouston.com','.khbaytown.com','.khnorthhouston.com','.khsouthbaytricity.com','.khaurora.com','.khclearlake.com','.kheaston.com','.khrancho.com','.khsouthphilly.com','.khnorthernindiana.com','.kindredrome.com','.kindredspring.com','.peoplefirsthh.com','.acclaimhospice.com','.signaturehealth.org','.professionalhc.com','.victorianhc.com','.havenhh.com','.suhomecareandhospice.com','.snhomecareandhospice.com','.virginvalleyhomecare.com','.gmhomecareandhospice.com','.virginvalleyhomemedical.com','.illinoisfamilyhhs.com','.integracarehh.com','.hhadvantage.com','.rollinghillshc.com','.southwoodrehab.com','.ardenrehab.com','.pettigrewhc.com','.nwcontinuum.com','.harrisonrehab.com','.sunnybrookhc.com','.wasatchcare.com','.raleighrehabhc.com','.rosemanorhc.com','.villagesquarerehab.com','.tunnellrehab.com','.nineteenthave.com','.rainiervistacc.com','.canyonwoodnursing.com','.lakewoodhc.com','.loudonhealthcare.com','.vancouverhealthcare.com','.maryvillehealthcare.com','.cypresspointehc.com','.fairparkhc.com','.silascreekhc.com','.eaglecreekhrc.com','.harringtonrehab.com','.sellersburgrehab.com','.kindredlivermore.com','.plazaatridgmar.com','.plazaatmansfield.com','.kindredgrapevine.com','.valleyviewkindred.com','.wildwoodrehab.com','.caldwellcare.com','.canyonw.com','.lewistonrehab.com','.nampacarecenter.com','.weisercarecenter.com','.rp-castleton.com','.aspenparkhealthcare.com','.regencyplacedyer.com','.newarkhealthcare.com','.regencygreenwood.com','.stgeorgecare.com','.meadowvalerehab.com','.heritagemanorhc.com','.smithcountyhc.com','.rosewoodhealthcare.com','.riversidemhc.com','.maplehealthrehab.com','.bridgewatertransitional.com','.indiancreekhrc.com','.sanluismedrehab.com','.bremenhealthcare.com','.kindredkokomo.com','.lincolnrehab.com','.lawtonhealthcare.com','.valleygardenshealth.com','.santacruzhealthcare.com','.regencygreenfield.com','.mountainvalleycare.com','.parkplacehealthcare.com','.parkviewacres.com','.mttowersrehab.com','.sunnysidectr.com','.kindredrawlins.com','.windriverhealthcare.com','.bluehillsalzheimers.com','.brighammanor.com','.crawfordnursing.com','.hallmarknursing.com','.hammersmithhouse.com','.oakwoodrehab.com','.timberlynheights.com','.hillcrestcenter.com','.cntrygrdns.com','.quincyrehab.com','.westborohealthcare.com','.denmarrnc.com','.westgatemanor.com','.birchwoodterrace.com','.franklinwoods.com','.kindredcrossingswest.com','.windsorrehab.com','.kindredcrossingseast.com','.parkwaypavilion.com','.pickeringtonnursing.com','.logannursing.com','.winchesterctr.com','.eaglepond.com','.blueberryhillrehab.com','.franklinskilled.com','.greatbarringtonrnc.com','.riverterracehc.com','.waldenrehab.com','.greenbriarterrace.com','.hanoverterrace.com','.kindredwalnutcreek.com','.bayberrycarectr.com','.kindredsouthmarin.com','.medicalhillrehab.com','.pacificcoastcarectr.com','.smithranchcarectr.com','.ygnaciovalleycarectr.com','.sienacarectr.com','.cambridgenursing.com','.kindredabercorn.com','.countryestatesagawam.com','.highgatemanorcenter.com','.averymanor.com','.towerhillcenter.com','.harborlightsnursing.com','.braintreemanor.com','.forestviewwareham.com','.highlandernursing.com','.laurellakerehab.com','.goddardnursing.com','.wedgewoodhc.com','.gchenderson.com','.monroehc.com','.gczebulon.com','.gcrockymount.com','.gcelizabeth.com','.kindrednorthwest.com','.cherryhillshc.com','.auroracarecenter.com','.eastviewmedrehab.com','.colonialmanormrc.com','.nrmrc.com','.mtcarmelrehab.com','.kindredmilwaukee.com','.sheridanmedrehab.com','.woodstockhealth.com','.columbushrc.com','.bashfordeast.com','.danvillecentre.com','.hillcresthealthcare.com','.woodlandhc.com','.northhavenhc.com','.whitesburggardens.com','.greensrehab.com','.haciendarcc.com','.nansemondhc.com','.riverpointehc.com','.baypointemedical.com','.harrodsburghealth.com','.lebanonmanor.com','.community-hc.com','.stratfordcommons.com','.mastershealthcare.com','.goldengatehcc.com','.victorianhealthcare.com','.ledgewoodrehab.com','.seacoastrehab.com','.foothillnursing.com','.clarkhousefhv.com','.starrfarmnc.com','.middletonvillage.com','.bsscc.com','.lafayetterehab.com','.lakemednursing.com','.oakhillrehab.com','.wyomissingnursing.com','.seniorhomecare.net','.westernreserveseniorcare.com', '.kindredhospitals.com');

	return in_array(get_base_domain($domain),$allowed_domains);
}