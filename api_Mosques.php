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
	
function api_mosques(\WP_REST_Request $request = null) {

	global $app, $db, $wpdb, $current_site;
	
	$params = $request->get_params();
	if (isset($_REQUEST['request'])) {				// w2ui sends postData in a request variable!
		$params = array_merge(json_decode(stripslashes($_REQUEST['request']),true), $params);
		unset($params['request']);
	}
	$params = stripslashes_deep($params);
	$masjid_name 	= (isset($params["masjid_name"])) 	? '%'.$params["masjid_name"].'%' : "%%";
	$postcode 		= (isset($params["postcode"])) 	? $params["postcode"].'%'			: "%";
	$city 			= (isset($params["city"])) 	? $params["city"]			: "";

	if (is_null($masjid_name)) { $masjid_name = "%%"; }
	if (is_null($masjid_name)) { $postcode = "%"; }
	if (is_null($masjid_name)) { $city = ""; }
	

	$limit 		= (isset($params["limit"])) 	? $params["limit"]			: 0;
	$offset 	= (isset($params["offset"])) 	? $params["offset"]			: 0;
	
	$table 		= prefix("masajid");


		$sql = "Select * from ".$table."
				where masjid_name like :masjid_name
				and postcode like :postcode ";
		if ($city!="") { $sql .= " and city = :city"; }			// *************************************************


	//$sql = str_replace(":masjid_name","'" . $masjid_name . "'", $sql);
	//$sql = str_replace(":postcode","'" . $postcode . "'", $sql);
	//$sql = str_replace(":city","'" . $city . "'", $sql);	

	$pm = ["masjid_name"=>$masjid_name,"postcode"=>$postcode,"city"=>$city];
//return rest_ensure_response(["error"=>"success","message"=>$sql, "records"=>$pm],200);
	
	try {
		$stmt = $db->prepare( $sql );
			
		
					// bind variable values
					$stmt->bindParam(":masjid_name", $masjid_name, PDO::PARAM_STR);
					$stmt->bindParam(":postcode", $postcode, PDO::PARAM_STR);
					if ($city!="") { $stmt->bindParam(":city", $city, PDO::PARAM_STR); }
			
		// execute query
		$stmt->execute();
		$datarows 	= [];
		$datarows	=	$stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt = null;

		$response = 
			[
				"status"=>"success",
				"message"=>"Data retrieved succesfully",
				"sql"=>$sql,
				"total"=>count($datarows),  // can be -1 (or unset) to indicate that total number is unknown
				"records" => stripslashes_deep($datarows)
			];
	} catch (\Throwable $e) {
		$response = [ "status"=> "error",  "message"    => $e->getMessage() ];
	}
	
	return rest_ensure_response($response,200);
}
