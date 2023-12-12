<?php
namespace Afsar\wtk;
use Afsar\wtk;


class Wptoolkit {

	public $plugin;
	public $pluginfile;
	//public $plugin_name;
	
	protected $version;

	public 	$db;
	public  $db_hits;
	public  $debug_info;
	
	public  $cfg;
	private $app_context;
	private $options;
	public  $debug_level;

	public $api_call_id;
	public $api_authorised;	
	public $api_forbidden_reponse;
	
	/**
	* Define the core functionality of the plugin.
	*
	* Set the plugin name and the plugin version that can be used throughout the plugin.
	* Load the dependencies, define the locale, and set the hooks for the admin area and
	* the public-facing side of the site.
	*/		
	public function __construct($plugin_file) {
		
		if ( defined( 'WTK_VERSION' ) ) {
			$this->version = WTK_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		
		$this->pluginfile = $plugin_file;
		
		add_action( 'admin_init', array( $this, 'action_init' ) );
		//$this->plugin_name = '[WP Toolkit]';

		$api_authorised = false;
		return $this;
	}
	
	// called through an action hook 
	public function action_init() {		
		if ( is_admin() ) {
			$this->plugin	= get_plugin_data( $this->pluginfile );			
		}
	}


	function debug_msg($msg) {
		echo "<div style='text-align:center;border:1px solid red;'><b>".$msg."</b></div>";
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}

	/**
	 * Maint plugin entry point - registers all hooks, etc
	 * ready for short codes to execute the desired funcions on demand
	 */
	public function run() {
		
		// register hooks, css, js
		// read for use whenever

	}


}


function InitSitePages() {

	$pages = [];
	$pages["Home"] = "[wtk_app_home]";
	$pages["About Us"] = "This page contains every thing about us!";
	$pages["Salaah Times"] = "Manage Salaah Times";
	$pages["Contact"] = "Use the details below to contact us";
	$pages["Privacy Policy"] = "Some stuff about privacy polics - data captured, stored and shared with 3rd parties";
	$pages["Terms & Conditions"] = "This page contains all the boring legal stuff";
	$pages["Cookie Policy"] = "What cookies are and how they help improve user experience";
	
	$pages["App Admin"] = "[wtk_appadmin]";
	$pages["My Account"] = "[wtk_myaccount]";

	foreach ($pages as $title => $content) {

		$check_page_exist = get_page_by_title($title, 'OBJECT', 'page');
		// Check if the page already exists
		if(empty($check_page_exist)) {
			$page_id = wp_insert_post(
				array(
				'comment_status' => 'close',
				'ping_status'    => 'close',
				'post_author'    => 1,
				'post_title'     => ucwords($title),
				'post_name'      => strtolower(str_replace(' ', '-', trim($title))),
				'post_status'    => 'publish',
				'post_content'   => $content,
				'post_type'      => 'page',
				'post_parent'    => 0
				)
			);
		}
	}
}



