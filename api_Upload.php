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
function enforceAdminPermissions() {
	//if ( ! ( current_user_can( 'manage_options' ) || current_user_can( 'administrator' ) ) ) {
	//	return new WP_Error( 'rest_forbidden', esc_html__( 'Private', 'myplugin' ), array( 'status' => 401 ) );
	//}
	return true;
}

function importCSVPostRequestHandler( \WP_REST_Request $request ) {

	$permittedExtension = 'csv';
	$permittedTypes = ['text/csv', 'text/plain', 'application/csv'];

	$files = $request->get_file_params();
	$headers = $request->get_headers();
	  
	  
	if ( !empty( $files ) && !empty( $files['file'] ) ) {
	  $file = $files['file'];
	}

	  // smoke/sanity check
	  if (! $file ) {
		return rest_ensure_response( ["status" => "error", "message"=>"Some error!"], 200 );   //rest_ensure_response( 'Error' );
	  }
	  // confirm file uploaded via POST
	  if (! is_uploaded_file( $file['tmp_name'] ) ) {
		return rest_ensure_response( ["status" => "error", "message"=>"File upload check failed "], 200 );
	  }
	  // confirm no file errors
	  if (! $file['error'] === UPLOAD_ERR_OK ) {
		return rest_ensure_response( ["status" => "error", "message"=>"Upload error"], 200 );
	  }
	  // confirm extension meets requirements
	  $ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
	  if ( $ext !== $permittedExtension ) {
		return rest_ensure_response( ["status" => "error", "message"=>"Invalid extension"], 200 );
	  }
	  // check type
	  $mimeType = mime_content_type($file['tmp_name']);
	  if ( !in_array( $file['type'], $permittedTypes )
		  || !in_array( $mimeType, $permittedTypes ) ) {
			return rest_ensure_response( ["status" => "error", "message"=>"Invalid mime type: ".$mimeType], 200 );
	  }
	
	// we've passed our checks, now read and process the file
	$handle = fopen( $file['tmp_name'], 'r' );
	$headerFlag = true;
	$r = 0;
	$data = [];
	while ( ( $rowdata = fgetcsv( $handle, 1000, ',' ) ) !== FALSE ) { // next arg is field delim e.g. "'"
    	// skip csv's header row / first iteration of loop
		if ( $headerFlag ) {
			$headerFlag = false;
			$fld = $rowdata;
			continue;
		}
		// process rows in csv body
		if ( $rowdata[0] ) {
			for ($f = 0; $f < count($fld); $f++) {
				$data[$r][$fld[$f]] = sanitize_text_field($rowdata[$f]);
			}			
			// your code here to do something with the data
			// such as put it in the database, write it to a file, send it somewhere, etc.
		}
		$r++;
		//if ($r>4) break;
	}
	
	fclose( $handle );
	// return any necessary data in the response here
	return rest_ensure_response( ["status"=>"success", "message" => "File uploaded ok!", "records"=> $data], 200 );
  }

