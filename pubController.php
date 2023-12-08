<?php
namespace Afsar\wtk;
use Afsar\wtk;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'pubScriptsStyles.php';

		/* Modify page title without affecting other titles (eg menus) */

			add_filter( 'the_title', 'Afsar\wtk\wtk_title_update', 10, 2 );
			function wtk_title_update( $title, $id = null ) {
				
				$post = get_post( $id );
				if( $title=="Home" ) {
					return "";  //"My new Title!";
				}

				return $title;
			}

			// this filter fires just before the nav menu item creation process
			add_filter( 'pre_wp_nav_menu', 'Afsar\wtk\wtk_remove_title_filter_nav_menu', 10, 2 );
			function wtk_remove_title_filter_nav_menu( $nav_menu, $args ) {
				// we are working with menu, so remove the title filter
				remove_filter( 'the_title', 'Afsar\wtk\wtk_title_update', 10, 2 );
				return $nav_menu;
			}

			// this filter fires after nav menu item creation is done
			add_filter( 'wp_nav_menu_items', 'Afsar\wtk\wtk_add_title_filter_non_menu', 10, 2 );
			function wtk_add_title_filter_non_menu( $items, $args ) {
				// we are done working with menu, so add the title filter back
				add_filter( 'the_title', 'Afsar\wtk\wtk_title_update', 10, 2 );
				return $items;
			}


add_shortcode( 'wtk_app_home', 'Afsar\wtk\wtk_appHome');
function wtk_appHome($pg_atts = [], $pg_content = null, $pg_tag = '') {
   // normalize attribute keys, lowercase
    $pg_atts = array_change_key_case((array)$pg_atts, CASE_LOWER);
	//die("tag=".$pg_tag);
	require_once plugin_dir_path( __FILE__ ) . 'pubAppHome.php';
	appHome($pg_atts);
}

	
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
	//mosquelist();
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

/********************************************
*********************************************/

function mosquelist() {
	
	$list = [];	
	
	for ($pg=1; $pg<=1; $pg++) {
	
		$curlSession = curl_init();
		curl_setopt($curlSession, CURLOPT_URL, 'https://www.nearestmosque.com/search?page='.$pg.'&country=uk');
		curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

		$tmp = curl_exec($curlSession);
		$data = $tmp;
		curl_close($curlSession);
		
		while (strpos($tmp, '<i class="fa-solid fa-mosque"></i> <a href="',0)) {
			$item = [];
			
			$c1 = strpos($tmp, '<i class="fa-solid fa-mosque"></i> <a href="',0) + strlen('<i class="fa-solid fa-mosque"></i> <a href="');
			$c2 = strpos($tmp, '" title="',$c1) - $c1;
			$href = substr($tmp, $c1, $c2);
			
			$tmp = substr($tmp,$c1+$c2);
			
			$c1 = strpos($tmp, '>',0)+1;
			$c2 = strpos($tmp, '</a>',$c1) - $c1;
			$item["masjid_name"] = substr($tmp, $c1, $c2);	

			$tmp = substr($tmp,$c1+$c2);

			$c1 = strpos($tmp, '<div class="mb-2"><i class="fa-solid fa-location-dot"></i> ',0) + strlen('<div class="mb-2"><i class="fa-solid fa-location-dot"></i> ');
			$c2 = strpos($tmp, '</div>',$c1) - $c1;
			
		/*
		echo "<pre>". htmlspecialchars($tmp). "</pre>";	
		echo "<div>c1 = ".$c1."</div>";
		echo "<div>c2 = ".$c2."</div>";
		echo "<pre>". htmlspecialchars($tmp). "</pre>";	
		*/
			
			
			$item["address"] = substr($tmp, $c1, $c2);
			
			$tmp = substr($tmp,$c1+$c2);

			$c1 = strpos($tmp, '<a href="',0) + strlen('<a href="');
			$c2 = strpos($tmp, ' title="',$c1) - $c1;
			$tmp = substr($tmp,$c1+$c2);
			
			$c1 = strpos($tmp, '>',0) + 1;
			$c2 = strpos($tmp, '</div>',$c1) - $c1;
			$item["city"] = substr($tmp, $c1, $c2);	
					
			$c1 = strpos($tmp, '<div class="mb-2"><i class="fa-solid fa-map"></i> ',0) + strlen('<div class="mb-2"><i class="fa-solid fa-map"></i> ');
			$c2 = strpos($tmp, '</div>',$c1) - $c1;
			$item["postcode"] = substr($tmp, $c1, $c2);		
			
			$tmp = substr($tmp,$c1+$c2);
			
			$dets = mosquedetails($href);
			$item["what3words"] = $dets["what3words"];
			$item["phone_no"] = $dets["phone_no"];
				
			$list[]=$item;

			//echo count($list).",".$item["masjid_name"].",".$item["address"].",".$item["city"].",".$item["postcode"].",".$item["what3words"].",".$item["phone_no"]."<br/>";
			echo '"'.implode('","',$item).'"<br/>';
		}	
	
	}

	//echo "<pre>".htmlspecialchars(printable($list))."</pre>";
	//echo "<pre>".htmlspecialchars($data)."</pre>";	
	
}

function mosquedetails($href) {
	
	$url = 'https://www.nearestmosque.com'.$href;
	
	$curlSession = curl_init();
    curl_setopt($curlSession, CURLOPT_URL, $url);
    curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

    $tmp = curl_exec($curlSession);
    curl_close($curlSession);

	$c1 = strpos($tmp, '<a id="nmWhat3Words" class="word-break" title="',0) + strlen('<a id="nmWhat3Words" class="word-break" title="');
	if (strpos($tmp, '<a id="nmWhat3Words" class="word-break" title="',0)) {
		$c2 = strpos($tmp, '" target="',$c1) - $c1;
		$what3words = ($c1==true) ? substr($tmp, $c1, $c2): '';			
	} else {
		$c1 = 0;	
		$c2 =0;
		$what3words = "";
	}
	$tmp = substr($tmp,$c1+$c2);
	
	
	$c1 = strpos($tmp, 'a id="nmTelephone" href="tel:',0) + strlen('a id="nmTelephone" href="tel:');
	if (strpos($tmp, 'a id="nmTelephone" href="tel:',0)) {
		$c2 = strpos($tmp, '" title="',$c1) - $c1;
		$phone_no = substr($tmp, $c1, $c2);
	} else {
		$phone_no = '0000 000 0000';
	}
	
	return ["what3words"=>$what3words, "phone_no"=>$phone_no];
}

