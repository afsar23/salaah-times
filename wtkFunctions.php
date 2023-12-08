<?php
namespace Afsar\wtk;
use Afsar\wtk;

// encode/decode json web token
require_once 'php-jwt-master/src/BeforeValidException.php';
require_once 'php-jwt-master/src/ExpiredException.php';
require_once 'php-jwt-master/src/SignatureInvalidException.php';
require_once 'php-jwt-master/src/JWT.php';

use \Firebase\JWT\JWT;

use \PDO;
use \PDOException;
use \Exception;

defined('ABSPATH') or die("Cannot access pages directly.");   

/**
 * returns the physical name of a given app table with the prefix
 * alternatively returns the prefix only if no table name specified
 */
function prefix($tabname="") {
    global $wpdb;
    return $wpdb->prefix."wtk_".$tabname;
}



// useful source: https://www.hongkiat.com/blog/wordpress-custom-loginpage/

	$check_page_exist = get_page_by_title("My Account", 'OBJECT', 'page');
	// Check if the page already exists
	if (!empty($check_page_exist)) {
	
		add_action('init','Afsar\wtk\redirect_login_page');
		function redirect_login_page() {
			$login_page  = home_url( '/my-account?fnc=login' );
			$page_viewed = basename($_SERVER['REQUEST_URI']);
			if( $page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
				wp_redirect($login_page);
			exit;
			}		  
		}

		add_action( 'wp_login_failed', 'Afsar\wtk\login_failed' );
		function login_failed() {
		  $login_page  = home_url( '/my-account?fnc=login' );
		  wp_redirect( $login_page . '&login=failed' );
		  exit;
		}

		add_filter( 'authenticate', 'Afsar\wtk\verify_username_password', 1, 3);
		function verify_username_password( $user, $username, $password ) {
		  $login_page  = home_url( '/my-account?fnc=login' );
			if( $username == "" || $password == "" ) {
				wp_redirect( $login_page . "&login=empty" );
				exit;
			}
		}

		add_filter('login_redirect', 'Afsar\wtk\wtk_login_redirect', 10, 3 );
		function wtk_login_redirect( $url, $request, $user ){ 
			if( $user && is_object( $user ) &&  is_a( $user, 'WP_User' ) ) {
				if( $user->has_cap( 'administrator') ) {
					$url = admin_url();
				} else {
					$url = home_url();
				}
			}
			$url = home_url();
			return $url;
		}
	}
	
add_action( 'wp_logout', 'Afsar\wtk\after_logout' );
function after_logout() {

	// remove jwt cookie after the normal wp logout
	remove_jwt_cookie();
	
	$login_page  = home_url( '/my-account?fnc=login' );
	wp_redirect( $login_page );
	exit;	
	
}

add_action('wp_login', '\Afsar\wtk\after_login', 10, 2);
function after_login( $user_login, $user ) {

	// add jwt cookie to supplement the wp auth cookie
	add_jwt_cookie($user);

}



function add_jwt_cookie($user) {

	// generate jwt
	$expiry = time() + (14 * 24 * 60 * 60);
	$jwt = JWTToken($user);
	setcookie('jwt_token', $jwt, $expiry,"/");	// root level (wordpress root)
	setcookie('jwt_token', $jwt, $expiry);		// and api level ('root' for api controller !!!
	
	$response = [ 	"status"        =>  "ok",
					"message"       =>  "*** Login successful ***!",
					"data"          =>  array(
											"ID" => $UserData->ID,
											"user_login" => $UserData->user_login,
											"email" => $UserData->user_email
										)
			];

	return $response;
	
}

function wtkNonceKey() {
	return "Some nonsence key!";
}
	
function JWTToken(\WP_User $UserData) {

	// variables used for jwt
	$jwt_key = "example_key";
	$jwt_alg = 'HS256';
	$jwt_issued_at = time();
	$jwt_expiration_time = $jwt_issued_at + (14 * 24 * 60 * 60); // valid for 14 days
	$jwt_issuer = plugin_dir_url( __FILE__ );
  	  
	$token = array(
		"iat" => $jwt_issued_at,
		"exp" => $jwt_expiration_time,
		"iss" => $jwt_issuer,
		"data" => array(
			"ID" => $UserData->ID,
			"user_login" => $UserData->user_login,
			"email" => $UserData->user_email
		)
	);

	// generate jwt
	return JWT::encode($token, $jwt_key, $jwt_alg);
	
}

function JWTTokenValidation($token) {

	$jwt_key = "example_key";
	$jwt_alg = 'HS256';
	
	try {        
		$decoded_jwt = JWT::decode($token, $jwt_key, [$jwt_alg]);       // validates token and will throw errors if token is not valid
		$UserInfo = (array) $decoded_jwt->data;     				// populates use info from id retrieved from token                        
		$token_validation = ["status"=>"success", "message"=>"Valid token", "userinfo"=>$UserInfo]; 
	}
	catch (\Throwable $e) {
		// simply store the token validation results to allow uses cases to decide their own course of action
		$token_validation = [ "status"=> "error",  "message"    => "Invalid Token - ".$e->getMessage() ];
		//print_r($this->token_validation);
		//die;
	}
	
	return $token_validation;
}

function GetUserFromToken($token) {

	$jwt_key = "example_key";
	$jwt_alg = 'HS256';
      
	$decoded_jwt = JWT::decode($token, $jwt_key, [$jwt_alg]);       // validates token and will throw errors if token is not valid
	$UserInfo = (array) $decoded_jwt->data;     				// populates use info from id retrieved from token                        
	
	return $UserInfo;
}


	

function remove_jwt_cookie() {

	setcookie("jwt_token", '', time() - 1);
	setcookie("jwt_token", '', time() - 1, "/");
	
	return [ 	"status"        =>  "ok",
				"message"       =>  "Logged out!"
			];	

}











/**
 * Make a stored item array into a menu item object
 *
 * @param array $item
 *
 * @return mixed
 */
function wtk_make_item_obj( $pitem ) {

	// generic object made to look like a post object
	$item                   = new \stdClass();
	$item->ID               = $pitem['ID'];
	$item->title            = $pitem['title'];
	$item->url              = $pitem['url'];
	$item->menu_order       = (isset($pitem['menu_order'])) ? (int)$pitem['menu_order'] : 0;
	$item->menu_item_parent = (isset($pitem['menu_item_parent'])) ? (int)$pitem['menu_item_parent'] : 0;
	$item->post_parent		= $item->menu_item_parent;

	// menu specific properties
	$item->db_id            = $item->ID;
	$item->type             = !empty( $pitem['type'] ) ? $pitem['type'] : '';
	$item->object           = !empty( $pitem['object'] ) ? $pitem['object'] : '';
	$item->object_id        = !empty( $pitem['object_id'] ) ? $pitem['object_id'] : '';

	// output attributes
	$item->classes          = array();
	$item->target           = '';
	$item->attr_title       = '';
	$item->description      = '';
	$item->xfn              = '';
	$item->status           = '';

	return $item;
}


	# HELPERS & UTILITY
	function wtk_printr($data) {
		# aids in debugging by not making you have to type all of 
		# this nonsense out each time you want to print_r() something
		echo wtk_printable($data);
	}

	function wtk_printable($data) {
		# aids in debugging by not making you have to type all of 
		# this nonsense out each time you want to print_r() something
		
		$out = "";
		if($data === 'post') {
			$out .= '<tt><pre>'.print_r($_POST,true).'</pre></tt>';
		}
		elseif($data === 'get') {
			$out .= '<tt><pre>'.print_r($_GET,true).'</pre></tt>';
		} else {
			$out .= '<tt><pre>'.print_r($data,true).'</pre></tt>';
		}
		return $out;
	}


/**
 * Generate breadcrumbs
 * @author CodexWorld
 * @authorURL www.codexworld.com
 */
function wtk_breadcrumbs() {
    echo '<a href="'.home_url().'" rel="nofollow">Home</a>';
    if (is_category() || is_single()) {
        echo "&nbsp;&nbsp;&#187;&nbsp;&nbsp;";
        the_category(' &bull; ');
            if (is_single()) {
                echo " &nbsp;&nbsp;&#187;&nbsp;&nbsp; ";
                the_title();
            }
    } elseif (is_page()) {
        echo "&nbsp;&nbsp;&#187;&nbsp;&nbsp;";
        echo the_title();
    } elseif (is_search()) {
        echo "&nbsp;&nbsp;&#187;&nbsp;&nbsp;Search Results for... ";
        echo '"<em>';
        echo the_search_query();
        echo '</em>"';
    }
}


/*******************************************************************************************************************************************
 * 
 *   FUNCTIONS
 *   Try to split them into spearate files by some common themes
 *   eg lib_api
 *    
 */


 
// $fields is querystring or post data fields
// $method = "GET" or "POST"
function ApiResults($url,$fields,$method) {
	
	$post = ($method=="POST") ? true : false;

	if (isset($fields) and is_array($fields)) {
		if (!$post) $url .= "?" . http_build_query($fields);
	}
	
	// set HTTP header
	$headers = array(
		'Content-Type: application/json'
	);

	// Open connection
	$ch = curl_init();

	// Set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, $post);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	if ($post) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	
	// Execute post
	$result = curl_exec($ch);
	// Close connection
	curl_close($ch);

	$out = json_decode($result, true);		

	return $out;
	
}


######################
####
#### 	Make interanl rest api call - without http
####
####   	https://wpscholar.com/blog/internal-wp-rest-api-calls/
####
######################
##
## 	$method, $route, $param
##  eg
##  $method = "GET"
##  $route  = '/wp/v2/posts'
##  $param  = [ 'per_page' => 12 ] 
##
function intApiCall($method="GET",$route="/wp/v2/posts/128",$param) {
		
	$request = new WP_REST_Request( $method, $route );
	
	if (isset($param)) $request->set_query_params( $param );
	
	$response = rest_do_request( $request );
	$server = rest_get_server();
	$data = $server->response_to_data( $response, false );
	$json = wp_json_encode( $data );	
	
	return $data;
	
}


function GetSetDebugInfo($valuetoset=null,$tag = "DEBUG") {

	
	if (!isset($this->debug_info)) $this->debug_info=[];
	
	if (isset($valuetoset)) {
		$this->debug_info[] = array($tag=>$valuetoset);
	} else {
		return $this->printable($this->debug_info);
	}
}


# HELPERS & UTILITY
function printr($data) {
	# aids in debugging by not making you have to type all of 
	# this nonsense out each time you want to print_r() something
	$this->printable($data);
}

function printable($data) {
	# aids in debugging by not making you have to type all of 
	# this nonsense out each time you want to print_r() something
	
	$out = "";
	if($data === 'post') {
		$out .= '<tt><pre>'.print_r($_POST,true).'</pre></tt>';
	}
	elseif($data === 'get') {
		$out .= '<tt><pre>'.print_r($_GET,true).'</pre></tt>';
	} else {
		$out .= '<tt><pre>'.print_r($data,true).'</pre></tt>';
	}
	return $out;
}	


#######################

// the end-most action hook!
//add_action( 'shutdown', 'wtk_action_shutdown');

function wtk_action_shutdown() {

	global $wpdb;
	global $wtk;	
	global $wp_filter;
	
	// for some reason, this stops connections to upates, and stuff...(themes, plugins)
	// use query monitor plugin - super plugin!!!
	
	if (!is_admin()) {
		echo "
			<hr/>
				<p>Database  : ".get_num_queries()." queries</p>
				<p>Load Time : ".timer_stop(0)." seconds</p>
			<hr/>
		";	

		//place this inside the function that you want to back trace
		//ie sequence of calls that arrived here
		//echo $fvc->printable(wp_debug_backtrace_summary());

		foreach( $GLOBALS['wp_actions'] as $action => $count )
    	    printf( '%s (%d) <br/>' . PHP_EOL, $action, $count );


		//list tables in database
		$mytables=$wpdb->get_results("SHOW TABLES");
		foreach ($mytables as $mytable)
		{
			foreach ($mytable as $t) 
			{       
				echo $t . "<br>";
			}
		}

	}

}



/**
 * Simple helper function for make menu item objects
 * 
 * @param $title      - menu item title
 * @param $url        - menu item url
 * @param $order      - where the item should appear in the menu
 * @param int $parent - the item's parent item
 * @return \stdClass
 */ 
function wtk_custom_nav_menu_item( $title, $url, $order, $parent = 0 ){

	$offset = 100000;
	$item = new \stdClass();
	$item->ID = $offset + $order;
	$item->db_id = $item->ID;
	$item->title = $title;
	$item->url = $url;
	$item->menu_order = $offset + $order;
	$item->menu_item_parent = ($parent==0) ? 0 : $offset + $parent;
		
	$item->post_parent = $item->menu_item_parent;
	$item->type = 'custom';
	$item->object = '';
	$item->object_id = '';
	$item->classes = array();
	$item->target = '';
	$item->attr_title = '';
	$item->description = '';
	$item->xfn = '';
	$item->status = '';

	return $item;
}

	/**
	 * Make a stored item array into a menu item object
	 *
	 * @param array $item
	 *
	 * @return mixed
	 */
	function make_item_obj( $pitem ) {

		// generic object made to look like a post object
		$item                   = new \stdClass();
		$item->ID               = $pitem['ID'];
		$item->title            = $pitem['title'];
		$item->url              = $pitem['url'];
		$item->menu_order       = (isset($pitem['menu_order'])) ? (int)$pitem['menu_order'] : 0;
		$item->menu_item_parent = (isset($pitem['menu_item_parent'])) ? (int)$pitem['menu_item_parent'] : 0;
		$item->post_parent		= $item->menu_item_parent;

		// menu specific properties
		$item->db_id            = $item->ID;
		$item->type             = !empty( $pitem['type'] ) ? $pitem['type'] : '';
		$item->object           = !empty( $pitem['object'] ) ? $pitem['object'] : '';
		$item->object_id        = !empty( $pitem['object_id'] ) ? $pitem['object_id'] : '';

		// output attributes
		$item->classes          = array();
		$item->target           = '';
		$item->attr_title       = '';
		$item->description      = '';
		$item->xfn              = '';
		$item->status           = '';

		return $item;
	}

///////////////
// adds dynamic menu entries (not committed to database)
// only to the front-end!!!

///////////////
// adds dynamic menu entries (not committed to database)
// only to the front-end!!!

add_filter( 'wp_get_nav_menu_items', 'Afsar\wtk\custom_nav_menu_items', 20, 2 );
function custom_nav_menu_items( $items, $menu ){
    
	// abstractify the options, mapping them to the plungin functions (fnc=zzzzz)

	if (!is_admin() and isset($menu->slug)) {		
		for( $i = 0; $i < count( $items ); $i++ ){
			$items[ $i ] = make_item_obj((array)$items[$i]);
		}
		
		// extend the menu with dynamic items..
		$dynamic_items = wtk_menu_items($menu->name);
		foreach ($dynamic_items as &$item) {
			$items[] = $item;
		}
			
	}
	
	return $items;
}

function wtk_menu_items( $menu_name ){

	global $wtk;
	$items = [];
	
	// abstractify the options, mapping them to the plungin functions (fnc=zzzzz)

	// Actually this should only dynamically add menu items which are user sensitive!!!!
			
	switch($menu_name) {
		// only modify specific menu
		case "WTK_Main_Menu":	
			$items[] = wtk_custom_nav_menu_item( 'Home', 				home_url().'/home',			 			10,0 ); 
				/*
				$items[] = wtk_custom_nav_menu_item( 'About Us', 		home_url().'/about-us', 	11,10 );
				$items[] = wtk_custom_nav_menu_item( 'Privacy Policy', 	home_url().'/privacy-policy', 	12,10 ); 
				$items[] = wtk_custom_nav_menu_item( 'Terms of Use', 	home_url().'/terms-conditions', 	13,10 ); 
				$items[] = wtk_custom_nav_menu_item( 'Cookie Policy', 	home_url().'/cookie-policy', 	13,10 ); 
				*/
			$items[] = wtk_custom_nav_menu_item( 'About Us', 			home_url().'/about-us',  		20,0 ); 										
			$items[] = wtk_custom_nav_menu_item( 'Salaah Times', 		home_url().'/salaah-times',  	30,0 ); 										
			
			/*
			$items[] = wtk_custom_nav_menu_item( 'Recruitment', 		home_url().'/recruitment', 		40,0 );
				$items[] = wtk_custom_nav_menu_item( 'Primary Education', 	home_url().'/primary-education',	21,40 );	
				$items[] = wtk_custom_nav_menu_item( 'Secondary Education', 	home_url().'/secondary-education',	22,40 );	
				$items[] = wtk_custom_nav_menu_item( 'Special Education', 	home_url().'/special-education',	23,40 ); 
			$items[] = wtk_custom_nav_menu_item( 'Contact', 			home_url().'/contact', 		50,0 );
			*/
			
			if ( is_user_logged_in() ) {
				$items[] = wtk_custom_nav_menu_item( 'App Admin', 		home_url().'/app-admin', 		98,0 );				
				$items[] = wtk_custom_nav_menu_item( 'Logout', 			wp_logout_url('my-account?fnc=login'), 		99,0 );				
			} else {
				$items[] = wtk_custom_nav_menu_item( 'Login', 			home_url().'/my-account?fnc=login', 		98,0 );
				$items[] = wtk_custom_nav_menu_item( 'Register', 		home_url().'/my-account?fnc=register', 		99,0 );
			}
			
			/*
			$items[] = wtk_custom_nav_menu_item( 'My Account', 			home_url().'/my-account',  		999,0 ); 										
				if ( is_user_logged_in() ) {
					$items[] = wtk_custom_nav_menu_item( 'Profile', 		home_url().'/my-account?fnc=profile',			1,999 ); 						
					$items[] = wtk_custom_nav_menu_item( 'Settings', 		home_url().'/my-account?fnc=settings',		2,999);	
					$items[] = wtk_custom_nav_menu_item( 'Change Password', home_url().'/my-account?fnc=change_password',	3,999 );
					$items[] = wtk_custom_nav_menu_item( 'Logout', 			wp_logout_url('my-account?fnc=login'),	4,999 );
				} else {
					$items[] = wtk_custom_nav_menu_item( 'Login', 			home_url().'/my-account?fnc=login',			1,999 ); 						
					$items[] = wtk_custom_nav_menu_item( 'Register', 		home_url().'/my-account?fnc=register',		2,999 );	
					$items[] = wtk_custom_nav_menu_item( 'Forgot Password', home_url().'/my-account?fnc=forgot_password',	3, 999 );
				}
				*/
			
			break;
			
		case "WTK_User_Menu":	
			if ( is_user_logged_in() ) {
				$items[] = wtk_custom_nav_menu_item( 'Profile', 		home_url().'/my-account?fnc=profile',			1,0 ); 						
				$items[] = wtk_custom_nav_menu_item( 'Settings', 		home_url().'/my-account?fnc=settings',		2,0 );	
				$items[] = wtk_custom_nav_menu_item( 'Change Password', home_url().'/my-account?fnc=change_password',	3,0 );
				$items[] = wtk_custom_nav_menu_item( 'Logout', 			wp_logout_url('my-account?fnc=login'),	4,0 );
			} else {
				$items[] = wtk_custom_nav_menu_item( 'Login', 			home_url().'/my-account?fnc=login',			1,0 ); 						
				$items[] = wtk_custom_nav_menu_item( 'Register', 		home_url().'/my-account?fnc=register',		2,0 );	
				$items[] = wtk_custom_nav_menu_item( 'Forgot Password', home_url().'/my-account?fnc=forgot_password',	3,0 );
			}
			break;				

		case "WTK_Legal_Menu":				
			//$items[] = wtk_custom_nav_menu_item( 'About Us', home_url().'/about-us', 				1,0 ); 
			//$items[] = wtk_custom_nav_menu_item( 'Contact', home_url().'/contact', 				2,0 ); 
			$items[] = wtk_custom_nav_menu_item( 'Privacy Policy', home_url().'/privacy-policy', 	3,0 ); 
			$items[] = wtk_custom_nav_menu_item( 'Terms & Conditions', home_url().'/terms-conditions',  		4,0 ); 
			$items[] = wtk_custom_nav_menu_item( 'Cookie Policy', 	home_url().'/cookie-policy', 	6,0 ); 
			break;				
	}			
	
return $items;
}

// upon plugin activation
function wtk_create_plugin_menus() {
	
	// Create plugin menu stubs
	// actual menu items would be added dynamically through the wp_get_nav_menu_items filter
	$menu_names = array("WTK_Main_Menu","WTK_User_Menu","WTK_Legal_Menu"); 
			
	foreach($menu_names as $menu_name) {
		if (!wp_get_nav_menu_object( $menu_name ))	{ 
			$menu_id = wp_create_nav_menu($menu_name);
		}
	}
}

add_action( 'after_switch_theme', 'Afsar\wtk\assign_menu_locations' );
function assign_menu_locations() {

	$locations = get_theme_mod( 'nav_menu_locations' );
	
	if(!empty($locations)) { 

		$menu_names = array("WTK_Main_Menu","WTK_User_Menu","WTK_Legal_Menu"); 
		$m = 0;
		$newloc = [];
		foreach($locations as $k=>$v) { 

			if ($m<count($menu_names)) {
				$menu = get_term_by('name', $menu_names[$m], 'nav_menu');
				$newloc[$k] = $menu->term_id; 
			} 
			$m++;
		} 
		set_theme_mod('nav_menu_locations', $newloc); 
	}
	
}


function SelectList($sql) {
	
	global $db;

	$stmt = $db->prepare($sql);
	$stmt->execute();
	$data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

	//$data = array_merge(array('0'=>''),$data);				//   Please select...
	return $data; //array_flip($data);
	
}	

####################################################### CUSTOM POST TYPES ###############################################


/*
* Creating a function to create our CPT
*/
/* Hook into the 'init' action so that the function
* Containing our post type registration is not 
* unnecessarily executed. 
*/
//add_action( 'init', 'Afsar\wtk\custom_post_type', 0 );  
function custom_post_type() {
  
// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Advertisers', 'Post Type General Name' ),
        'singular_name'       => _x( 'Advertiser', 'Post Type Singular Name' ),
        'menu_name'           => __( 'Advertisers' ),
        'parent_item_colon'   => __( 'Parent Advertiser' ),
        'all_items'           => __( 'All Advertisers' ),
        'view_item'           => __( 'View Advertiser' ),
        'add_new_item'        => __( 'Add New Advertiser' ),
        'add_new'             => __( 'Add New' ),
        'edit_item'           => __( 'Edit Advertiser' ),
        'update_item'         => __( 'Update Advertiser' ),
        'search_items'        => __( 'Search Advertiser' ),
        'not_found'           => __( 'Not Found' ),
        'not_found_in_trash'  => __( 'Not found in Trash' ),
    );
      
// Set other options for Custom Post Type
      
    $args = array(
        'label'               => __( 'advertisers' ),
        'description'         => __( 'Advertisers - providers of educational resources' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'thumbnail'),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        'taxonomies'          => array( 'genres' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
		'rewrite'    		  => array( 'slug' => 'advertisers', 'with_front' => true ), // my custom slug
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' 		  => true,
  
    );
      
    // Registering your Custom Post Type
    register_post_type( 'advertisers', $args );
  
}
  



//add_shortcode( 'wtk_advertisers', 'Afsar\wtk\adverstisers_list', 0 );
function adverstisers_list() {
	$args = array( 'post_type' => 'advertisers', 'posts_per_page' => 5);
	$the_query = new \WP_Query( $args );
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) : $the_query->the_post(); 
			?>
			<div class="card shadow">
				<div class="card-header">
					<h3><?php the_title(); ?></h3>
				</div>
				<div class="card-body">
					<?php 
						echo '<div style="float:left">'; the_post_thumbnail(); echo '</div>'; 
						echo '<div style="float:left">'; the_content(); echo '</div>'; 
					?>
				</div>
				<div class="card-footer">
					<?php echo "Website: <a href='#'>Visit Use</a>" ?>
				</div>
			</div>			
			<br/><br/>
			<?php
		endwhile;
		wp_reset_postdata();
	} else {
		echo '<p>';
		_e( 'Sorry, no adverstisers matched your criteria.' ); 
		echo '</p>';
	}
}


	// allow pagination...
	/* remote (server-side) pagination automatially sends the following parameters in the query string to the ajax url:
		page - the page number being requested
		size - the number of rows to a page (if paginationSize is set)
	sorters - the first current sorters(if any)
	filter - an array of the current filters (if any)
	*/

	function QryResults($BaseQry,$param=[]) {

		global $db;
		
		$page               = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$records_per_page   = (isset($_GET['page'])) ? $_GET['size'] : 11111111551615;      // default to a very large number
		
		$from_record_num = $records_per_page * ($page-1);				//  !!!!!!!! Must start at ZERO!!!!
		
		$query = $BaseQry."
			LIMIT :from_rec_num, :recs_per_page";

//return "QueryResult:<pre>$query</pre><pre>".print_r($param,true)."</pre>";
//die;
		// prepare query statement
		$stmt = $db->prepare( $query );

		// bind variable values
		$stmt->bindParam(":from_rec_num", $from_record_num, PDO::PARAM_INT);
		$stmt->bindParam(":recs_per_page", $records_per_page, PDO::PARAM_INT);
		
		if(!empty($param) && is_array($param)) {
			foreach ($param as $key => $value) {
				$stmt->bindParam($key, $value);
			}
		}

		// execute query
		$stmt->execute();
		$dataset	=	$stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt = null;

		// return values from database
		if (isset($_GET['page'])) {
				$stmt = $db->prepare( "SELECT count(1) totrows FROM (".$BaseQry.") t" );
				$stmt->execute();
				$rows = $stmt->fetchAll(PDO::FETCH_COLUMN,0);
				$last_page = intdiv(($rows[0]-1),$records_per_page)+1;
				return ["last_page"=>$last_page, "data"=>$dataset];
		} else {
			return $dataset;
		}

	}







/** 
 * Get header Authorization
 * */
function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

/**
 * get access token from header
 * */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}