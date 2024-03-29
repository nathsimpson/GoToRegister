<?php
/**
* Plugin Name: GoToRegister
* Version: 1.0.0
* Description: A GoToWebinar Registration Form Generator Plugin, using Shortcodes.
* Author: Nathan Simpson
* Author URI: http://www.nathansimpson.design
*/


if ( ! defined( 'ABSPATH' ) )exit;


$token = "";
$username = get_option( 'GoToRegister_username' );
$password = get_option( 'GoToRegister_password' );
$clientID = get_option( 'GoToRegister_apiClientId' );
$organiserKey = get_option( 'GoToRegister_organiserKey' );
$theme = get_option( 'GoToRegister_theme' );

//generates an access token for a gotowebinar api request
function generateToken(){
	global $username;
	global $password;
	global $clientID;
	global $token;
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://api.getgo.com/oauth/access_token",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "grant_type=password&user_id=".$username."&password=".$password."&client_id=".$clientID,
	  CURLOPT_HTTPHEADER => array(
	    "accept: application/json",
	    "cache-control: no-cache",
	    "content-type: application/x-www-form-urlencoded",
	    "postman-token: 9485b0bd-cd06-6c49-a72b-e0befdf6ab30"
	  ),
	));
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  $decodedjson = json_decode($response);
	  $token = $decodedjson->access_token;
	}
}

//the HTML markup of the form that will be displayed.
function outputForm(){
	global $theme;

	echo get_option( 'GoToRegister_title' );

	if($theme == "Bootstrap"){
		require('includes/form-Bootstrap.php');
		echo $formHTML;
	}elseif($theme == "BootstrapWide"){
		require('includes/form-BootstrapWide.php');
		echo $formHTML;
	}elseif($theme == "Simple"){
		require('includes/form-Simple.php');
		echo $formHTML;
	}else{
		echo "Error. Theme not specified.";
	}

}


// generates a form using shortcodes and displays it on the page.
function generateFormFunc($atts){
	global $organiserKey;
	global $token;

	$a = shortcode_atts(array(
		'webinar_key' => "123",
		'organiser_key' => $organiserKey,
	), $atts, 'generateForm' );

	if (isset($_POST['registration-submission'])) {
		generateToken();

		$vals['body'] = (object) array(
			'firstName' => $_POST['fname'],
			'lastName' => $_POST['lname'],
			'email' => $_POST['email']
		);

		$long_url = 'https://api.getgo.com/G2W/rest/organizers/'.$a['organiser_key'].'/webinars/'.$a['webinar_key'].'/registrants';

		$header = array();
		$header[] = 'Content-type: application/json';
		$header[] = 'Accept: application/vnd.citrix.g2wapi-v1.1+json';
		$header[] = 'Authorization:'. $token;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $long_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($vals['body']));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		$decoded_response = json_decode($response);

		if ($decoded_response->status == 'APPROVED') {
			echo '<div class="alert alert-success" role="alert"><strong>Success!</strong> You have been registered for this webinar. Expect an email shortly!</div>';
			$register_result = true;
		} else {
			echo '<div class="alert alert-danger" role="alert"><strong>Whoops!</strong> Something has gone wrong. Please try registering <a href="https://register.gotowebinar.com/rt/'.$a['webinar_key'].'" target="_blank">here</a> instead.</div>';
			$register_result = false;
			echo curl_errno($ch);
		}
	}

	ob_start();
	outputForm();
	$output = ob_get_clean();
	return $output;
}

require('includes/settings.php');
require('includes/tinymce.php');
add_shortcode('gotoregister', 'generateFormFunc');
?>
