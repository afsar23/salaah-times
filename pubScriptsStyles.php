<?php
namespace Afsar\wtk;
use Afsar\wtk;

defined('ABSPATH') or die("Cannot access pages directly.");   

	
	$pluginpath = plugin_dir_path(__FILE__);

	//wp_register_script('jQuery','https://code.jquery.com/jquery-3.5.1.min.js');
	wp_enqueue_script('jquery');  	

	wp_register_script('w2uijs',plugin_dir_url(__FILE__) . 'w2ui/w2ui-2.0.min.js',array('jquery'),filemtime($pluginpath.'w2ui/w2ui-2.0.min.js'));
	wp_enqueue_script('w2uijs'); 
	wp_register_style('w2uicss',plugin_dir_url(__FILE__) . 'w2ui/w2ui-2.0.min.css',array(),filemtime($pluginpath.'/w2ui/w2ui-2.0.min.css'));	
	wp_enqueue_style('w2uicss'); 

	wp_register_script('w2ui_wrapperjs', plugin_dir_url(__FILE__) . 'w2ui_wrapper.js',array('jquery'),filemtime($pluginpath.'w2ui_wrapper.js'), ["in_footer"=>false]);
	wp_enqueue_script('w2ui_wrapperjs');

		// better off using these...
		/*
		wp_register_style('tabulator_css','https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.10/css/tabulator.min.css');	
		wp_enqueue_style('tabulator_css');  
		wp_register_script('tabulator_js','https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.10/js/tabulator.min.js');
		wp_enqueue_script('tabulator_js');
		*/
		wp_register_style('tabulator_css','https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css');	
		wp_enqueue_style('tabulator_css');  
		wp_register_script('tabulator_js','https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js');
		wp_enqueue_script('tabulator_js');

		

	wp_register_style('fontawesome_css','https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');	
	wp_enqueue_style('fontawesome_css'); 

	// bootstrap...
	
		wp_register_style('poppins_css','https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900');	
		wp_enqueue_style('poppins_css');  
		wp_register_style('bootstrap4_css','https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');	
		wp_enqueue_style('bootstrap4_css');  
		
		wp_register_style('bootstrap_icons_css','https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css');	
		wp_enqueue_style('bootstrap_icons_css');  
		
		wp_register_script('popper_js','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js');
		wp_enqueue_script('popper_js');  
		wp_register_script('bootstrap4_js','https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js');
		wp_enqueue_script('bootstrap4_js');  

	
	// bulma
	//wp_register_style('bulma_css','https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css');	
	//wp_enqueue_style('bulma_css');  

	wp_register_script('custom_js', plugin_dir_url( __FILE__ ).'js/custom.js',[],filemtime($pluginpath.'js/custom.js'));		
	//wp_enqueue_script('custom_js'); 	

	wp_register_style('main_css', plugin_dir_url( __FILE__ ).'css/main.css',[],filemtime($pluginpath.'css/main.css'));
	wp_enqueue_style('main_css');  


	wp_register_style('login_reg_pwd_css', plugin_dir_url( __FILE__ ).'css/main.css',[],filemtime($pluginpath.'css/login_reg_pwd.css'));
	wp_enqueue_style('login_reg_pwd_css');  
	
	//wp_register_style('generic_form_css', plugin_dir_url( __FILE__ ).'css/main.css',[],filemtime($pluginpath.'css/generic_form.css'));
	//wp_enqueue_style('generic_form_css');  
	
	
	
add_action( 'wp_enqueue_scripts', 'Afsar\wtk\wpse30583_enqueue' );
function wpse30583_enqueue()
{
    wp_enqueue_script( 'custom_js' );

    // Localize the script
	// wp_localize_script( 'custom_js', 'wpApiSettings', array( 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
     
}
