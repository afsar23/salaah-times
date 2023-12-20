<?php
namespace Afsar\wtk;
use Afsar\wtk;

use \PDO;
use \PDOException;
use \Exception;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
	

##########################   
### 
###   REST API CONTROLLER
###
##########################

define("MY_TEXT_DOMAIN", "wtk");
define("API_NAMESPACE","wtk/v1");

// change nonce lifetime to 1 hour
//add_filter( 'nonce_life', function () { return 1 * HOUR_IN_SECONDS; } );


add_action( 'rest_api_init', 'Afsar\wtk\api_register_routes' );


require_once plugin_dir_path( __FILE__ ) . 'api_Upload.php';
require_once plugin_dir_path( __FILE__ ) . 'api_Mosques.php';
				
function api_register_routes($request = null) {

	//$pdata  = json_decode(file_get_contents("php://input"));   // all the posted data required to perform the method

	//emergency disabling of all APIs!!!
	//echo json_encode(["status"=>"error","message"=>"Better luck next time, matey!"]);
	//exit;

	register_rest_route( API_NAMESPACE, '/testapi', array(
		[   
		'methods'  => \WP_REST_Server::ALLMETHODS ,
		'callback' => 'Afsar\wtk\testapi',
		'permission_callback' => 'Afsar\wtk\wtk_api_permissions_check',
		'access_type'=>'LOGGED_IN'
		]
	) );	
	
	register_rest_route( API_NAMESPACE, '/register', array(
		[      
		'methods'  => \WP_REST_Server::CREATABLE ,
		'callback' => 'Afsar\wtk\api_users_register',
		'permission_callback' => 'Afsar\wtk\wtk_api_permissions_check',	
		'access_type'=>'NONCE'			
		]			
    ) );	
	    
	register_rest_route( API_NAMESPACE, '/password_reset', array(
		[      
		'methods'  => \WP_REST_Server::CREATABLE ,
		'callback' => 'Afsar\wtk\send_reset_password_link',
		'permission_callback' => 'Afsar\wtk\wtk_api_permissions_check',	
		'access_type'=>'NONCE'			
		]		
    ) );	

	register_rest_route( API_NAMESPACE, '/email_verification_code', array(
		[      
		'methods'  => \WP_REST_Server::CREATABLE ,
		'callback' => 'Afsar\wtk\api_email_verification_code',
		'permission_callback' => 'Afsar\wtk\wtk_api_permissions_check',	
		'access_type'=>'NONCE'			
		]		
    ) );
	
	register_rest_route( API_NAMESPACE, '/update_password', array(
		[      
        'methods'  => \WP_REST_Server::ALLMETHODS ,
        'callback' => 'Afsar\wtk\updatepassword',
		'permission_callback' => 'Afsar\wtk\wtk_api_permissions_check',	
		'access_type'=>'NONCE'	
		]
		
    ) );	
	
	
	register_rest_route( API_NAMESPACE, '/contactus', array(
		[      
		'methods'  => \WP_REST_Server::ALLMETHODS ,
		'callback' => 'Afsar\wtk\api_contactus',		
        'permission_callback' => 'Afsar\wtk\wtk_api_permissions_check',	
		'access_type'=>'NONCE'	
		]		
    ) );

	register_rest_route( API_NAMESPACE, '/listdata', array(			
		[      
		'methods'  => \WP_REST_Server::ALLMETHODS ,
		'callback' => 'Afsar\wtk\api_listdata',		// defined in separate script file
		'permission_callback' => 'Afsar\wtk\wtk_api_permissions_check',	
		'access_type'=>'LOGGED_IN'	
		]
    ) );	

	register_rest_route( API_NAMESPACE, '/maintdata', array(
		[      
        'methods'  => \WP_REST_Server::ALLMETHODS ,
        'callback' => 'Afsar\wtk\wtk_maintdata',
        'permission_callback' => 'Afsar\wtk\wtk_api_permissions_check',	
		'access_type'=>'LOGGED_IN'		
		]		
    ) );
	
	register_rest_route( API_NAMESPACE, '/mosques', array(
		[      
		'methods'  => [ 'POST' ],			
		'callback' => 'Afsar\wtk\api_mosques',
		'permission_callback' => 'Afsar\wtk\wtk_api_permissions_check',	
		'access_type'=>'NONCE'
		]			
	) );
	
	register_rest_route( API_NAMESPACE, '/import_csv', [
		'methods' => [ 'POST' ],
		'callback' => 'Afsar\wtk\importCSVHandler',
		'permission_callback' => 'Afsar\wtk\wtk_api_permissions_check',
		'access_type'=>'LOGGED_IN'
	] );	

}


/**
 * This is our callback function that embeds our resource in a WP_REST_Response
 * generic permissions check for all custom routes!
 *
 */
function wtk_api_permissions_check(\WP_REST_Request $request = null) {
	
	$params = $request->get_params();
	if (isset($_REQUEST['request'])) {				// w2ui sends postData in a request variable!
		$params = array_merge(json_decode(stripslashes($_REQUEST['request']),true), $params);
		unset($params['request']);
	}
	$params = stripslashes_deep($params);

	$this_route = $request->get_attributes();
	$access_type = (isset($this_route['access_type'])) ? $this_route['access_type']: "DENY";			
	//return ["status"=>"error","message"=>$this_route];

	switch($access_type) {
		case "NONCE":	
			$nonce = isset($params['_wpnonce']) ? $params['_wpnonce'] : ''; 
			if (!wp_verify_nonce($nonce, wtkNonceKey())) {
				$api_response = ["status"=>"error","message"=>"NONCE is not valid!"];
			}
			break;
		case "LOGGED_IN":				// define different roles/capabailities
			$JWTToken = getBearerToken();
			$tokenvalidation = JWTTokenValidation($JWTToken);
			if ( $tokenvalidation["status"] =="error" ) {
				$api_response = rest_ensure_response(["status"=>"error","message"=>"Invalid token. Permission denied!"],200);
			}
			break;
		case "DENY":		// deny access as the wtk api hasn't been assigned access type, so the default is to deny! 
			$api_response = ["status"=>"error","message"=>"Access is denied to this API"];
			break;
		default: 			// deny access, as the access type in registering the route has been mis-spelt 
			$api_response = ["status"=>"error","message"=>"Invalid Access Type!"];
			break;
	}

	global $wtk;
	$wtk->api_authorised = (isset($api_response)) ? false : true;
	$wtk->api_forbidden_response = (isset($api_response)) ? $api_response : '';
	
	return $wtk->api_authorised; 

}
 
 
 
/**
 * Callback function to authorize each api requests
 * 
 * @see \WP_REST_Request
 * 
 * @param                  $response
 * @param                  $handler
 * @param \WP_REST_Request $request
 *
 * @return mixed|\WP_Error
 */
// authorize each requests
add_filter( 'rest_request_before_callbacks', 'Afsar\wtk\api_before_callback', 10, 3 );
function api_before_callback( $response, $handler, \WP_REST_Request $request ) {

	// do some generic validation for any route before the api handler is called

	global $app, $wpdb, $current_site;
	    
	$params = $request->get_params();
	if (isset($_REQUEST['request'])) {				// w2ui sends postData in a request variable!
		$params = array_merge(json_decode(stripslashes($_REQUEST['request']),true), $params);
		unset($params['request']);
	}
	$params = stripslashes_deep($params);

	$JWTToken = getBearerToken();
	$tokenvalidation = JWTTokenValidation($JWTToken);
	
	$api_request = [
			"params"=>$params,
			"user_agent"=>$request->get_headers("user_agent")["user_agent"][0],
			"referer"=>$request->get_header('referer'), 
			"route"=>$request->get_route(), 
			"callback"=>$request->get_attributes()["callback"]
			];
	
	global $current_user;
	$uid = (isset($tokenvalidation["userinfo"])) ? $tokenvalidation["userinfo"]["ID"] : 0;
	$current_user = wp_set_current_user($uid);
    
		//$api_user = ["user_id"=>$current_user->ID,"user_login"=>$current_user->user_login, "user_name"=>$current_user->display_name];
		$api_user = $current_user;
		
	global $wtk;

	$wtk->api_call_id = LogApiStart($api_user,$api_request); 


    return $response;

}


add_filter( 'rest_request_after_callbacks', 'Afsar\wtk\api_after_callback', 10, 3 );
function api_after_callback( $api_response, $handler, \WP_REST_Request $request ) {

	// chance to modify the response here before returning to the client
	
	global $wtk;
			
			
	if (strpos($request->get_route(),API_NAMESPACE)) {
		if (!$wtk->api_authorised) {
			$api_response = $wtk->api_forbidden_response;
		}
	} else {  
		//wordpress api was called, so we can decide to override the response if the referer was some other domain!
		if ( ! (substr($request->get_header('referer'), 0, strlen(home_url())) == home_url())) {	
			$api_response = ["status"=>"error","message"=>"Wordpress API access is disabled!"];
		}
	}	



	LogApiEnd($wtk->api_call_id ,$api_response); 
				
	return $api_response;
	
}


    /*******
     * Audit log for an api call
     * Inserts a new log entry and retuns the id)
     */
    function LogApiStart($api_user, $api_request) {

        global $db;
        
		// only want to log intial requests to the grid, not subsequent scolling requests
		if (isset($api_request["params"]["offset"])) {
			if ($api_request["params"]["offset"] > 0) {
				return 0;
			}
		}
		
		// delete all logs keeping only the latest 500
		$query = "
			DELETE 
			FROM 
				" . prefix("apicalls") . " 
			WHERE id < 
				( SELECT id 
					FROM " . prefix("apicalls") . " 
					ORDER BY id desc       
					LIMIT 1 OFFSET 100
				);
		";

		$stmt = $db->prepare($query);
		$result = $stmt->execute();
  
		$table = ( isset($api_request["params"]["table"])) ? $api_request["params"]["table"] : "";
		if ($table == "apicalls") { 
			return 0;			//  >>>>>>>>>>>>>>>>>>>>
		} 
  

        $query = "INSERT INTO " . prefix("apicalls") . "
        SET
            start_time = now(),
            api_user = :userinfo,
            api_request = :requestinfo
            ;
        ";

        // prepare the query
        $stmt = $db->prepare($query);
        $api_user       = stripslashes(json_encode($api_user));
        $api_request  	= stripslashes(json_encode($api_request));        

        // sanitize and bind params
        $stmt->bindParam(':userinfo',	$api_user);
        $stmt->bindParam(':requestinfo',$api_request);
        
		// execute the query, also check if query was successful
		$result = $stmt->execute();
        $id = $db->lastInsertId();
        $stmt = null;

        return $id;

    }

    /*******
     * Audit log for an api call
     * Updates a given entry with the response and end time
     */
    function LogApiEnd($id, $api_response) {

        global $db;
        if ($id==0) {
            return;
        }

        $query = "UPDATE " . prefix("apicalls") . "
            SET
                api_response 	= :api_response,
				end_time 		= now()
            WHERE id = :id;
        ";

        // prepare the query
        $stmt = $db->prepare($query);

        // sanitize and bind params
		if (isset($api_response->data)) {
			$api_response = stripslashes(json_encode($api_response->data));
        } else {
			$api_response = stripslashes(json_encode($api_response));
		}
		
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':api_response', $api_response);
        
		// execute the query, also check if query was successful
		$result = $stmt->execute();
        $id = $db->lastInsertId();

        $stmt = null;
    }



function wtk_maintdata(\WP_REST_Request $request = null) {

	require_once plugin_dir_path( __FILE__ ) . 'api_maintdata.php';
	return api_maintdata($request);	
	
}	

//////////////////////
// understand the various request params that come into the API handler
// main sources are:
// 		html form data
//		formr data
//		w2ui request
//		postman
// at the end of it there's a whole range of data available to the API handler to return an appropriate response
// for this function, we're just returning back everthing that comes in
//
function testapi(\WP_REST_Request $request = null) {

	global $app, $wpdb, $current_site;
	global $current_user;
	 
	$params = $request->get_params();
	if (isset($_REQUEST['request'])) {				// w2ui sends postData in a request variable!
		$params = array_merge(json_decode(stripslashes($_REQUEST['request']),true), $params);
		unset($params['request']);
	}
	$params = stripslashes_deep($params);
	$nonce = isset($params['_wpnonce']) ? $params['_wpnonce'] : ''; 
	$valid_nonce = (wp_verify_nonce($nonce, wtkNonceKey())) ? "YES" : "NO";

	$JWTToken = getBearerToken();
	$tokenvalidation = JWTTokenValidation($JWTToken);
	//echo printable($tokenvalidation);
	if ($tokenvalidation["status"] =="error") {
		$response = ["status"=>"error","message"=>"Invalid token. Permission denied!"];
		//return rest_ensure_response($response,200); 
	}
	
	$api_request = [
			"user_agent"=>$request->get_headers("user_agent")["user_agent"][0],
			"referer"=>$request->get_header('referer'), 
			"route"=>$request->get_route(), 
			"callback"=>$request->get_attributes()["callback"],
			"params"=>$params
			];
	$api_user = ["user_id"=>$current_user->ID,"user_login"=>$current_user->user_login, "user_name"=>$current_user->display_name];
	
	$api_response = [
			"api_request"=>$api_request,
			"api_user"=>$tokenvalidation,
			"valid_nonce"=>$valid_nonce,
			"status"=>"success",
			"message"=>"API accessed succesfully",
		];
	
			// perform dummy insert just so I can check if this bit of code was entered
			global $db;
			$query = "INSERT INTO " . prefix("apicalls") . " SET start_time = now(), api_user = 'Test Insert', api_request = 'Test Insert'";
			$stmt = $db->prepare($query);
			$result = $stmt->execute();
			$stmt = null;
	
	return rest_ensure_response($api_response,200);
}



// generic api handler for a generic endpoint
// specifically for requests coming from w2ui components 
// 
// requests will include the following properties so approppriate json response can be served:
//		requestor	- 'w2uigrid',	'w2uiform', 'postman', 'direct', etc  ('direct' is when someone accesses the endpoint by typing it in the browser's address bar)
//		entity		- name of the table with the appropriate prefix
//		operation	- 'create', 'read', 'update', 'delete'
//		recid		- which record id(s) to perform the operation
// 	all of the above yet to be implemented fully

// just develop the function gradually...in the meantime
function api_listdata(\WP_REST_Request $request = null) {

	global $app, $db, $wpdb, $current_site;
	
	$params = $request->get_params();
	if (isset($_REQUEST['request'])) {				// w2ui sends postData in a request variable!
		$params = array_merge(json_decode(stripslashes($_REQUEST['request']),true), $params);
		unset($params['request']);
	}
	$params = stripslashes_deep($params);

	$limit 		= (isset($params["limit"])) 	? $params["limit"]			: 0;
	$offset 	= (isset($params["offset"])) 	? $params["offset"]			: 0;
	$table 		= (isset($params["table"])) 	? prefix($params["table"])	: "";
	
	// dela with tabulator request...
	if (isset($params["size"])) {	
		$limit = $params["size"];
		$offset = $params["size"] * ($params["page"]-1);
	}
	
	try {
		//action any delete request first
		if (isset($params['action'])) {
			if ($params['action']=="delete") {
				$sql = "DELETE FROM ".$table." WHERE id IN (".implode(', ', $params["id"]).")";
				//return rest_ensure_response(["status"=>"error","message"=>$sql],200);
				$stmt = $db->prepare( $sql );
				$stmt->execute();
			}
		}	

		/*** STIL TO IMPLEMENT search --- find out what params are posted with the search!! --- ***/
		$sql = "Select * from ".$table;
		//$params["filter"] = "1=0";				// to force no data to be returned
		if (isset($params["filter"])) { 
			$sql .= " WHERE " . $params["filter"]; 
		}
		
		// apply any sorting...
		if (isset($params['sort'])) {
			$sql .= " ORDER BY ".implode(", ", array_map(function($item) { return $item["field"]." ".$item["direction"]; }, $params['sort']) );
		}
				
			// first get total records without the limit/OFFSET
			$stmt = $db->prepare( "SELECT COUNT(1) total FROM (".$sql.") qry" );
			$stmt->execute();
			$datarows	=	$stmt->fetchAll(PDO::FETCH_ASSOC);
			$total = $datarows[0]["total"];
			$stmt = null;		
		
		//now return data....
		$sql .= " LIMIT :limit OFFSET :offset";

		$stmt = $db->prepare( $sql );
		// bind variable values
		$stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
		$stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
		// execute query
		$stmt->execute();
		$datarows	=	$stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt = null;

		$response = 
			[
				"status"=>"success",
				"message"=>"Data retrieved succesfully",
				"total"=>$total,  // can be -1 (or unset) to indicate that total number is unknown
					// tabulator expects these...
					"last_row"=>$total,
					"last_page"=>intdiv($total,$limit)+1,
					"data"=>stripslashes_deep($datarows),
				"records" => stripslashes_deep($datarows)
			];
	} catch (\Throwable $e) {
		$response = [ "status"=> "error",  "message"    => $e->getMessage() ];
	}
	
	return rest_ensure_response($response,200);
}


// exit point of an api request - can be called from anywhere within the api processing cycle
// receives an object and turns it into a json response with appropriate http headers
function XXXXXXsend_api_response($response) {
	// required headers
	//
	//header("Access-Control-Allow-Origin: http://localhost/rest-api-authentication-example-level-2/");
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");
	header("Access-Control-Allow-Methods: POST");
	header("Access-Control-Max-Age: 0");
	header("Cache-Control: no-store");
	//header("Cache-Control: private");
	header("Access-Control-Allow-Headers: Cache-Control, Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

	// set response code
	// always postively send a an ok status (200)
	http_response_code(200);           // eg 200 success; 401 - fail...
	
	$rtn = rest_ensure_response($response);
	echo json_encode($rtn); 
	exit;
	
	return;
}


/**
 * Validate a request argument based on details registered to the route.
 *
 * @param  mixed            $value   Value of the argument.
 * @param  WP_REST_Request  $request The current request object.
 * @param  string           $param   Key of the parameter (argument name, eg 'country').
 * @return WP_Error|boolean
 */
function wtk_api_arg_validate_callback( $value, $request, $param ) {

    // If the 'country' argument is not a string return an error.
    if ( ! is_string( $value ) ) {
        return new WP_Error( 'rest_invalid_param', esc_html__( 'The country argument must be a string.', MY_TEXT_DOMAIN ), array( 'status' => 400 ) );
    }
}
 
/**
 * Sanitize a request argument based on details registered to the route.
 *
 * @param  mixed            $value   Value of the argument.
 * @param  WP_REST_Request  $request The current request object.
 * @param  string           $param   Key of the argument (argument name, eg 'country')
 * @return WP_Error|boolean
 */
function wtk_api_arg_sanitize_callback( $value, $request, $param ) {
    // It is as simple as returning the sanitized value.
	 return sanitize_text_field( $value );
}
 
/**
 * We can use this function to contain our arguments for the example product endpoint.
 */
function wtk_api_arguments() {
	
    $args = array();

    // Here we add our PHP representation of JSON Schema.
    $args['country'] = array(
        'description'       => esc_html__( 'This is the argument our endpoint returns.', MY_TEXT_DOMAIN ),
        'type'              => 'string',
        'validate_callback' => 'mtl_api_arg_validate_callback',
        'sanitize_callback' => 'mtl_api_arg_sanitize_callback',
        'required'          => true,
    );	
	
	return $args;
	
}





###### END REST API ######

// NOTES - To do/consider

// adds rest api end point for user registration
// need to consider generating a random captcha code 
// to present on front-end form and validate in the
// end-point function - secuirty against bots calling the api
// registration must be done through the front-end form
//

// ****** BUILD SECURITY / SOME TOKEN to ensure api request is coming from the same domain!!!!!   ****//

function api_users_register(\WP_REST_Request $request = null) {

	$parameters = $request->get_json_params();  //this returns null when called by postman!
	if (is_null( $parameters )) {
		$parameters = $_REQUEST;
	} else {
		$queryParams = $request->get_query_params();
		$parameters = array_merge($queryParams, $parameters);
	}


	$username = sanitize_text_field($parameters['user_name']) ;;
	$email = sanitize_text_field($parameters['email']) ;;
	$password = sanitize_text_field($parameters['user_pass']) ;	 
	$usergroup = sanitize_text_field($parameters['user_group']) ;
	$referral_code = sanitize_text_field($parameters['referral_code']);
	$token_raw = sanitize_text_field($parameters['token_raw']);
	$token_hash = sanitize_text_field($parameters['token_hash']);

	$response = array();
	
	// two key validations to guard against spam registrations
	// ensure email code was correct
	// check verification code matches the hash
	if (!password_verify($email . $token_raw, $token_hash)) {
		$response = [	"status"		=> "error",	"message"		=> "Invalid Verification Code"];	
	} elseif (false) { // ($referral_code <> get_option("wtk_referral_code","Oops!")) {
		$response = [	"status"		=> "error",	"message"		=> "Invalid Referral Code"];	
	} else { // all good so far....
		
		$RegUser["user_login"] 		= $username;
		$RegUser["user_email"] 		= $email;
		$RegUser["user_pass"]		= $password; 
				
		$newUserID = wp_insert_user($RegUser);
		if ( is_wp_error( $newUserID  ) ) {
			$response = [	"status"		=> "error",
								"message"		=> "Registration failed - ".$newUserID ->get_error_message()   // error object
							];
		} else {
			$response = [	"status"		=> "success",
								"message"		=> "Registration succesful (user id = ".$newUserID .")",
								"redirect" => home_url("/my-account?fnc=welcome")
							];							
			//update_user_meta( $newUserID, 'user_group', $usergroup );					
			wp_signon(["user_login"=>$username, "user_password"=>$password,"remember"=>true]);		
		}
	}
	
	return rest_ensure_response($response,200);
}

function api_email_verification_code(\WP_REST_Request $request = null) {

	global $app, $wpdb, $current_site;
	
	$parameters = $request->get_json_params();  //this returns null when called by postman!
	if (is_null( $parameters )) {
		$parameters = $_REQUEST;
	} else {
		$queryParams = $request->get_query_params();
		$parameters = array_merge($queryParams, $parameters);
	}
	
	$email = (isset($parameters["email"])) ? $parameters["email"] : "";

	$verification_code = random_int(100000, 999999);
	$msg = "Verification Code: " . $verification_code . ". To complete your registration.". "\r\n\r\n";		 
	$title = sprintf( ('[%s] Verification Code'), get_bloginfo('name') );
	
	if ( wp_mail($email, $title, $msg) ) {
		$verify_hash = password_hash($email . $verification_code, PASSWORD_DEFAULT);
		$response = ["status"=>"success", "message" => "Please input the verification code emailed to you!", "hash_token"=>$verify_hash];
	} else {
		if (strpos($request->get_header('referer'),"localhost/")) {
			$verify_hash = password_hash($email . "23031968", PASSWORD_DEFAULT);
			$response = ["status"=>"success", "message" => "Please input the verification code emailed to you!", "hash_token"=>$verify_hash];
		} else {		
			$status = "error";
			$msg 	=  'Sorry, the verification e-mail could not be sent for some reasson';
			$response = ["status"=>$status, "message"=>$msg];
		}
	}	
	return rest_ensure_response($response,200);
}


function send_reset_password_link(\WP_REST_Request $request = null) {
	
	global $app, $wpdb, $current_site;


	$parameters = $request->get_json_params();  //this returns null when called by postman!
	if (is_null( $parameters )) {
		$parameters = $_REQUEST;
	} else {
		$queryParams = $request->get_query_params();
		$parameters = array_merge($queryParams, $parameters);
	}

	$login_or_email = (isset($parameters["login_or_email"])) ? $parameters["login_or_email"] : "";

		//return rest_ensure_response($parameters,200);
	
	
	
	$userdata = get_user_by('email', $login_or_email);
    if (empty($userdata)) {
        $userdata = get_user_by('login', $login_or_email);
    }

    if (!empty($userdata)) {
    
		$user = new \WP_User(intval($userdata->ID));

		// generate jwt
		$jwt = JWTToken($userdata);
	
		$message = 'Someone requested that the password be reset for the following account:' . "\r\n\r\n";
		$message .= home_url() . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
		$message .= 'If this was a mistake, just ignore this email and nothing will happen.' . "\r\n\r\n";
		$message .= 'To reset your password, visit the following address:' . "\r\n\r\n";
		$message .= home_url()."/my-account/?fnc=reset_password&token=" . $jwt . "\r\n";

		$title = sprintf( ('[%s] Password Reset'), get_bloginfo('name') );
		
		if ( wp_mail($user->user_email, $title, $message) ) {
			$response = ["status"=>"success", "message" => "Email link to reset your password has been sent"];
		} else {
			$status = "error";
			$msg 	=  'The e-mail could not be sent.' . "<br />\n" . 'Possible reason: your host may have disabled the mail() function...';
			$response = ["status"=>$status, "message"=>$msg];
		}	

	} else {
		$response = ["status"=>"error", "message" => "User not found"];
	}

	return rest_ensure_response($response,200);
	
}


function updatepassword(\WP_REST_Request $request = null) {

	global $wtk;
	global $wpdb;
	global $db;
	
	$parameters = $request->get_json_params();  //this returns null when called by postman!
	if (is_null( $parameters )) {
		$parameters = $_REQUEST;
	} else {
		$queryParams = $request->get_query_params();
		$parameters = array_merge($queryParams, $parameters);
	}

	try {       
		$user = GetUserFromToken($parameters["token"]);                      
	}
	catch (\Throwable $e) {
		// simply store the token validation results to allow uses cases to decide their own course of action
		return [ "status"=> "error",  "message"    => "Failed to update password - invalid token" ];
	}

	// ok, we're good to update the password...
	// insert query
	$query = "UPDATE " . $wpdb->prefix . "users
			SET user_pass 	= :user_pass
			WHERE ID 		= :ID
			";

	// prepare the query
	$stmt = $db->prepare($query);		
			
	// bind the values
	$stmt->bindParam(':ID', $user["ID"]);
	// hash the password before saving to database

	//$password_hash = password_hash($this->user_pass, PASSWORD_BCRYPT);
	$password_hash = wp_hash_password($parameters["user_pass"]);
	
	$stmt->bindParam(':user_pass', $password_hash);

	// execute the query, also check if query was successful
	$result = $stmt->execute();

	if($result) {
		$response = [	"status"		=> "success",
							"message"		=> "Password updated successfully"
						];
	} else {
		$response = [	"status"		=> "error",
							"message"		=> "Failed to update password"
						];
	}

	$stmt = null;	
	$db = null;

	return rest_ensure_response($response,200);

}



function generate_random_content(\WP_REST_Request $request = null) {
	
	global $app, $wpdb, $current_site;


	$parameters = $request->get_json_params();  //this returns null when called by postman!
	if (is_null( $parameters )) {
		$parameters = $_REQUEST;
	} else {
		$queryParams = $request->get_query_params();
		$parameters = array_merge($queryParams, $parameters);
	}
	
	$r1 = wp_create_category( "Random One");
	$r2 = wp_create_category( "Random Two");

	for ($x = 0; $x <= 25; $x++) {
		$c = wp_create_category( "Categ No ".$r1);
		$c = wp_create_category( "Categ No ", $r2 );
	}
	
	
}


function delete_random_content(\WP_REST_Request $request = null) {
	
	global $app, $wpdb, $current_site;


	$parameters = $request->get_json_params();  //this returns null when called by postman!
	if (is_null( $parameters )) {
		$parameters = $_REQUEST;
	} else {
		$queryParams = $request->get_query_params();
		$parameters = array_merge($queryParams, $parameters);
	}
	
	
}

function api_contactus(\WP_REST_Request $request = null) {

	global $wtk;
	global $db;

/*
	Need a robust means of retrieving inputs to API (request, form, postman)
*/
	$current_user = wp_get_current_user();

	$parameters = $request->get_json_params();  //this returns null when called by postman!
	if (is_null( $parameters )) {
		$parameters = $_REQUEST;
	} else {
		$queryParams = $request->get_query_params();
		$parameters = array_merge($queryParams, $parameters);
	}
	
	if ( !is_user_logged_in() ) {
		// ensure math captcha has been answered
		if ($parameters["f_banda"] != $parameters["f_jawab"]) {
			return [ "status"=>"error", "message" => "Incorrect answer to human check!"];
		}            
		// ensure valid email is provided

		$email 	=	htmlspecialchars(stripslashes(strip_tags($parameters["f_email"])));
		// validate email here
		
		$validemail = true; //validemail();
		
		if (!$validemail) {
			return [	"status" => "error", "message" => "Invalid email address" ];
		}            


	} else {
		$email = $current_user->email; 
	}
		
	// now check subject and message...
	$fullname 	=	htmlspecialchars(stripslashes(strip_tags($parameters["f_fullname"])));
	$subject 	=	htmlspecialchars(stripslashes(strip_tags($parameters["f_subject"])));
	$message 	=	htmlspecialchars(stripslashes(strip_tags($parameters["f_message"])));

	If (!($subject) or !($message)) {
		return [ "status"=>"error", "message" => "Please complete all the fields"];            
	}

	// all good to go, save stuff to db table and also email the stuff to admins
	$now = date('Y-m-d H:i:s');
	$sql = "
		INSERT INTO ".prefix("contactus")."
			SET userid          = $current_user->ID,
				fullname		= :fullname,
				email    		= :email,
				subject         = :subject,
				message         = :message,
				datecreated     = :now
		";
		  
	// prepare the query
	$stmt = $db->prepare($sql);
	// bind the values
	$stmt->bindParam(':fullname', $fullname);
	$stmt->bindParam(':email', $email);
	$stmt->bindParam(':subject', $subject);
	$stmt->bindParam(':message', $message);
	$stmt->bindParam(':now', $now);

	// execute the query, also check if query was successful
	$result = $stmt->execute();            
	if ($result) {
		
		// now try emailing to admins but that is not critical...we do have a log of the feedback in the database!
		//$to_email = ConfigValue("ADMIN_EMAIL");
		//$subject = "Website Feedback: ".$subject;
		//wp_mail($to_email, $subject, $message);
		
		$response = [	"status"		=> "success",
							"message"		=> "Thank you for your message / feedback."
						];                     

	} else {
		$response = [	"status"		=> "error",
							"message"		=> "Registration failed",
							"userid"		=> $db->errorInfo()
						];            
	}

	//return $response_data;
	return rest_ensure_response($response,200);

}	

