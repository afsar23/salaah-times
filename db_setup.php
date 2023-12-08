<?php
namespace Afsar\wtk;
use Afsar\wtk;

defined('ABSPATH') or die("Cannot access pages directly.");   


/*
    The dbdelta function is picky:
        You must put each field on its own line in your SQL statement.
        You must have two spaces between the words PRIMARY KEY and the definition of your primary key.
        You must use the key word KEY rather than its synonym INDEX and you must include at least one KEY.
        KEY must be followed by a SINGLE SPACE then the key name then a space then open parenthesis with the field name then a closed parenthesis.
        You must not use any apostrophes or backticks around field names.
        Field types must be all lowercase.
        SQL keywords, like CREATE TABLE and UPDATE, must be uppercase.
        You must specify the length of all fields that accept a length parameter. int(11), for example.
*/


function CreateOrUpdateDbSchema() {

    global $wpdb;
   
    $version = get_option( 'wtk_db_version', '1.0' );    
	$charset_collate = $wpdb->get_charset_collate();

	$sql = [];

	$sql[] = "CREATE TABLE ".prefix("apicalls")." (
		id bigint(9) NOT NULL AUTO_INCREMENT,
		start_time datetime,
		end_time datetime,
		api_user longtext,
		api_request longtext,
        api_response longtext,
		UNIQUE KEY id (id)
	) $charset_collate;";

	$sql[] = "CREATE TABLE ".prefix("masajid")." (
		id bigint(18) NOT NULL AUTO_INCREMENT,
		masjid_id int(9) NOT NULL,
		masjid_name varchar(120),
		alt_name varchar(120),
		address varchar(150),
		city varchar(50),
		postcode varchar(10),
		what3words varchar(100),
		phone_no varchar(15),
		website varchar(50),
		email varchar(50),
		affiliation_id int(10),
		masjid_desc longtext,
		datecreated datetime,
		dateupdated datetime,
		UNIQUE KEY id (id)
	) $charset_collate;";

	$sql[] = "CREATE TABLE ".prefix("salaahtimes")." (
		id bigint(18) NOT NULL AUTO_INCREMENT,
		masjid_id int(9) NOT NULL,
		d_date date NOT NULL,
		fajr_begins time NULL,
		fajr_jamah time NULL,
		sunrise time NULL,
		zuhr_begins time NULL,
		zuhr_jamah time NULL,
		asr_mithl_1 time NULL,
		asr_mithl_2 time NULL,
		asr_jamah time NULL,
		maghrib_begins time NULL,
		maghrib_jamah time NULL,
		isha_begins time NULL,
		isha_jamah time NULL,
		is_ramadan smallint(6) NULL,
		hijri_date varchar(256) NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";	
		
	$sql[] = "CREATE TABLE ".prefix("contactus")." (
		id bigint(9) NOT NULL AUTO_INCREMENT,
		fullname varchar(50),
        email varchar(50),
        subject varchar(50),
        message varchar(4000),
        datecreated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	    UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	dbDelta( $sql );

    if ( version_compare( $version, '2.0' ) < '3.0' ) {
		$sql = [];
		
		$sql[] = "CREATE TABLE ".prefix("contactus")." (
			id bigint(9) NOT NULL AUTO_INCREMENT,
			fullname varchar(50),
			email varchar(50),
			subject varchar(50),
			message varchar(4000),
			datecreated datetime,
			UNIQUE KEY id (id)
		) $charset_collate;";
		
        dbDelta( $sql );
	  	update_option( 'my_plugin_version', '3.0' );		
	}

}


