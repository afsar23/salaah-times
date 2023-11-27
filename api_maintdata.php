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
	
// entity speciifc api methods


function api_maintdata(\WP_REST_Request $request = null) {

	global $wtk, $pdb, $wpdb, $current_user;
	
	$params = $request->get_params();
	if (isset($_REQUEST['request'])) {				// w2ui sends postData in a request variable!
		$params = array_merge(json_decode(stripslashes($_REQUEST['request']),true), $params);
		unset($params['request']);
	}
	$params = stripslashes_deep($params);

	$rec = $params["record"];
	$recid = (isset($rec["recid"])) ? $rec["recid"] : 0;
	
		// this is the only table specific code - rest of the proc is table agnostic!
			$data = [	"usergroup"		=>	$rec["usergroup"],
						"seqno"			=>	$rec["seqno"],
						"date_created" 	=> 	($recid == 0) ? date('Y-m-d H:i:s') : $rec["date_created"],
						"date_updated" 	=> 	date('Y-m-d H:i:s') 				
					];
			
	// also remove html special chars???
	$data = array_map(function($v){
				return trim(strip_tags($v));
			}, $data);
	
	
	global $db;
	if ($recid == 0) {
		$msg = $pdb->sql_insert(prefix($params["table"]), $data);
	} else {
		$msg = $pdb->sql_update(prefix($params["table"]), $data, "where id = ".$recid);
	}
	
	if ($msg == "") {
		if ($recid == 0) {
			$rec["id"] = $db->lastInsertId();
			$rec["recid"] = $rec["id"]; 
		}
		$response = ["status"=>"success","message"=>"Data saved successfully", "record"=>$rec];
	} else {
		$response = ["status"=>"error","message"=>"Data saved successfully"];
	}
		
	return rest_ensure_response($response,200);
}




