<?php
namespace Afsar\wtk;
use Afsar\wtk;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'pubScriptsStyles.php';

	
add_shortcode( 'wtk_contactus', 'Afsar\wtk\wtk_ContactUs');
function wtk_ContactUs($pg_atts = [], $pg_content = null, $pg_tag = '') {
   // normalize attribute keys, lowercase
    $pg_atts = array_change_key_case((array)$pg_atts, CASE_LOWER);
	//die("tag=".$pg_tag);
	require_once plugin_dir_path( __FILE__ ) . 'pubContactUs.php';
	ContactUs();
}


add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'dashicons' );
} );	


function SubTitle($sub) {
	echo '<div class="subtitle"><h3>'.$sub.'</h3></div>';
}


add_shortcode( 'wtk_appadmin', 'Afsar\wtk\wtk_appadmin');
function wtk_appadmin() {
	
	require_once plugin_dir_path( __FILE__ ) . 'pubAppAdmin.php';
	
	ob_start();   // start buffering content
	
	AppAdmin();
	$content = ob_get_clean(); // store buffered output content.

    return $content; // Return the content.
}


add_shortcode( 'wtk_maint_salaahtimes', 'Afsar\wtk\wtk_maint_salaahtimes');
function wtk_maint_salaahtimes() {
	
	require_once plugin_dir_path( __FILE__ ) . 'pubMaintSalaahTimes.php';
	
	ob_start();   // start buffering content
	
	//echo "<h1>HellooooooOO!</h1>";
	tabMaintSalaahTimes();
	$content = ob_get_clean(); // store buffered output content.

    return $content; // Return the content.
}


add_shortcode( 'wtk_viewanytable', 'Afsar\wtk\wtk_viewanytable');
function wtk_viewanytable() {
	
	require_once plugin_dir_path( __FILE__ ) . 'pubViewAnyTable.php';
	
	ob_start();   // start buffering content
	
	ViewAnyTable();
	$content = ob_get_clean(); // store buffered output content.

    return $content; // Return the content.
}


add_shortcode( 'wtk_apilogs', 'Afsar\wtk\wtk_api_logs');
function wtk_api_logs() {
	
	require_once plugin_dir_path( __FILE__ ) . 'pubApiLogs.php';
	
	ob_start();   // start buffering content
	
	ApiLogs();
	$content = ob_get_clean(); // store buffered output content.

    return $content; // Return the content.
}






////////////////////////////////////////////////////////////////////
			/*
				add_shortcode( 'wtk_listmaint', 'Afsar\wtk\listmaint');
				function listmaint($pg_atts = [], $pg_content = null, $pg_tag = '') {
					
					ob_start();

					// normalize attribute keys, lowercase
					$pg_atts = array_change_key_case((array)$pg_atts, CASE_LOWER);
					
					//die("tag=".$pg_tag);
					require_once plugin_dir_path( __FILE__ ) . 'pubListMaint.php';

					if ( is_user_logged_in() ) {
						//echo '<h4>Show list of tables to maintain</h4>';
						$default_fnc = "UserGroups";
					} else {
						return '<h4>Sorry, you need to login to maaintain data lists</h4>';
					}

					$fnc = (isset($_REQUEST["fnc"])) ? $_REQUEST["fnc"] : $default_fnc;

					switch ($fnc) {
						case "UserGroups": 			$subtitle = "User Groups"; 			break;
					}
					$fnc = 'Afsar\\wtk\\' . $fnc;	
					SubTitle($subtitle);
					$fnc([ $pg_atts ]);

					$content = ob_get_clean(); // store buffered output content.

					return $content; // Return the content.
				}
			*/


	
add_shortcode( 'wtk_myaccount', 'Afsar\wtk\my_account' ); 
function my_account($pg_atts = [], $pg_content = null, $pg_tag = '') {
	
    // normalize attribute keys, lowercase
    $pg_atts = array_change_key_case((array)$pg_atts, CASE_LOWER);
	
	//die("tag=".$pg_tag);
	require_once plugin_dir_path( __FILE__ ) . 'pubUser.php';

	if ( is_user_logged_in() ) {
		$default_fnc = 'profile';
	} else {
		$default_fnc = 'login';
	}

	$subtitle = "No subtitle";
	$fnc = (isset($_REQUEST["fnc"])) ? $_REQUEST["fnc"] : $default_fnc;

	switch ($fnc) {
		case "login": 				$subtitle = "Login"; 				break;
		case "forgot_password": 	$subtitle = "Forgot Password"; 		break;
		case "register": 			$subtitle = "Register";				break;
		case "profile": 			$subtitle = "Edit Profile"; 		break;
		case "settings":			$subtitle = "Settings";				break;
		case "change_password": 	$subtitle = "Change Password"; 		break;
		case "reset_password":		$subtitle = "Reset Your Password";	break;
		case "welcome":				$subtitle = "Welcome!";	break;		
		//case "logout":				$subtitle = "Logout";				break;										
	}
	$fnc = 'Afsar\\wtk\\' . $fnc;	
	
	SubTitle($subtitle);
	
	$fnc([ $pg_atts ]);
	//my_profile( $pg_atts );
}
	
add_shortcode( 'wtk_register', 'Afsar\wtk\register' ); 
function register($pg_atts = [], $pg_content = null, $pg_tag = '') {
	
    // normalize attribute keys, lowercase
    $pg_atts = array_change_key_case((array)$pg_atts, CASE_LOWER);
	
	//die("tag=".$pg_tag);
	require_once plugin_dir_path( __FILE__ ) . 'pubUser.php';
	
	registration_form($pg_atts);
	
}

// dispays a context sensitive panel:
// if user is not logged in, shows links to login, register, or forgot password
// if user is logged in shows username and logout link
add_shortcode( 'wtk_login_out', 'Afsar\wtk\login_out' ); 
function login_out($pg_atts = [], $pg_content = null, $pg_tag = '') {
	
    // normalize attribute keys, lowercase
    $pg_atts = array_change_key_case((array)$pg_atts, CASE_LOWER);
	
	//die("tag=".$pg_tag);
	require_once plugin_dir_path( __FILE__ ) . 'pubUser.php';
	
	if (!is_user_logged_in()) {
		login($pg_atts);
	} else {
		logout($pg_atts);
	}
		
}



/*** function to impersonate a user WIP ***/
function ImpersonateUser() {

	if (current_user_can("manage_options")) {
		
		$user_login = (isset($_REQUEST["user_login"])) ? $_REQUEST["user_login"] : "";
	
		//die("HERE!");
		//get user's ID
		$user = get_userdatabylogin($user_login);
		$user_id = $user->ID;

		//login
		wp_set_current_user($user_id, $user_login);
		wp_set_auth_cookie($user_id);
		do_action('wp_login', $user_login);    // this should also kick off the jwt cookie setting?

		wp_redirect(home_url());
		exit;
		
		//also save some setting to say we are in impersonation mode, and allow to switch back to admin!
		///
		//
	} else {
		echo "<h3>You cannote impersonate a user!</h3>";
		
	}
	
}	
	


///////////////////////////////////////////////////




//add_action('after_main', 'Afsar\wtk\wtk_after_main');
function wtk_after_main() {
	//example of using dash-icons in front-en
	echo '<h2><span class="dashicons dashicons-smiley"></span> A Cheerful Headline</h2>';
	
}
	
//add_action('wp_footer','Afsar\wtk\wtk_footer');
function wtk_footer() {

	//example of using dash-icons in front-en
	//echo '<h2><span class="dashicons dashicons-smiley"></span> A Cheerful Headline</h2>';

	
	//UserInfo();
	echo '<h3>COOKIES</h3>';
	echo wtk_printable($_COOKIE);


		// get the the role object
		//$role_object = get_role( "administrator" );

		// add $cap capability to this role object
		//$role_object->add_cap( "customize" );

		// remove $cap capability from this role object
		//$role_object->remove_cap( $capability_name );

}	


function UserInfo() {

	echo "<hr/>";

	if ( is_multisite() ) { echo '<h4>Multisite is enabled</h4>'; }
	
	$user = wp_get_current_user();
	
	if ( empty( $user ) ) {
		// User is logged out, create anonymous user object.
		$user = new \WP_User( 0 );
		$user->init( new \stdClass() );
	}
	
	echo printable($user);
	
}
	
add_action('wp_footer', 'Afsar\wtk\my_footer'); 
function my_footer() { 

	echo '<div style="background: gainsboro; color: gray;">&copy; Afsar Inc, 2023</div>'; 

	//ListAllShortCodes();

	//echo "<h4>REST API - Registered Routes</h4>";
	
	//$r = new \WP_REST_Server;
	//echo printable($r->get_routes());
}


    function ListAllShortCodes(){

        global $shortcode_tags;
		
		echo "<ul>";
		foreach($shortcode_tags as $code => $function) {
			echo "<li>$code</li>";
		}
		echo "</ul>";
    }
