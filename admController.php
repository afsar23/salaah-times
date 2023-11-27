<?php
namespace Afsar\wtk;
use Afsar\wtk;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
global $wtk;

require_once plugin_dir_path( __FILE__ ) . 'admSettings.php';


global $wtkAdmin;
$wtkAdmin = new admSettings( __FILE__ );


	//add_action( 'admin_print_styles-' . $page, 'Afsar\wtk\AdminScriptsStyles' );
	function AdminScriptsStyles() {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		//wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'farbtastic' );

		// We're including the WP media scripts here because they're needed for the image upload field
		// If you're not including an image upload then you can leave this function call out
		wp_enqueue_media();

		wp_register_script( 'wpt-admin-js', plugin_dir_url(__FILE__) . 'js/adm_settings.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
		wp_enqueue_script( 'wpt-admin-js' );
	}


add_action( 'admin_menu', 'Afsar\wtk\addAdminMenuPages' );

function addAdminMenuPages(){
	global $wtk;
	
	add_menu_page(
		'WP Toolkit Admin Page',
		'WP Toolkit',
		'manage_options',
		'wtk_settings',
		'Afsar\wtk\AdminHomeMainPage',
		 plugin_dir_url(__FILE__).'images/wtk_icon.PNG',2
	);

	add_submenu_page(
		'wtk_settings',
		'WP Toolkit Admin Page - Configure Settings',
		' >Settings',
		'manage_options',
		'wtk_settings',
		'Afsar\wtk\AdminHomeMainPage'
	);

	add_submenu_page(
		'wtk_settings',
		'Manage Quotes',
		' >Quotes',
		'manage_options',
		'wtk_manage_quotes',
		'Afsar\wtk\AdminManageQuotes'
	);
	

	add_submenu_page(
		'wtk_settings',
		'Cheat Sheet - Hooks and Shortcodes',
		' >Cheat Sheet',
		'manage_options',
		'wtk_cheat_sheet',
		'Afsar\wtk\AdminCheatSheet'	
	);
}
    

function AdminHomeMainPage() {
    
	//echo "<h2>".esc_html( get_admin_page_title() )." </h2>";
	//echo "<i>Welcome to the WP Toolkit Pulgin - One plugin to tame the whole of Wordpress!</i>";

	global $wtkAdmin;
	$wtkAdmin->settings_page();
}

function AdminCheatSheet () {

	global $wtk;	
	global $wp_filter;
	
    echo "<h2>".esc_html( get_admin_page_title() )." </h2>";

	echo "<blockquote>Install <b>Query Monitor</b> plugin. It is the bees knees!</blockquote>";

	echo "<h3>Registered Shortcodes</h4>";
	global $shortcode_tags;
	echo printable($shortcode_tags); 

	echo '<blockquote>Here!</blockquote>';

}

function AdminManageQuotes() {

	echo '<div class="wrap">';
    echo "<h2>".esc_html( get_admin_page_title() )." </h2>";

	echo '
		<blockquote>
			Add code here using the wp standard wp class to maintain list tables
		</blockquote>
	';
}


//add_action('in_admin_footer', 'Afsar\wtk\wtk_admin_footer');
add_filter('admin_footer_text', 'Afsar\wtk\wtk_admin_footer');
function wtk_admin_footer ($ftr) {

	global $wtk;

	$out = '';
	$out .= '<div class="clear"></div><div style="margin-top:500px">';
	$out .= wtk_printable($wtk->plugin);
	$out .= '</div>';	
	
	//echo $out;
	
	return $ftr;
}


