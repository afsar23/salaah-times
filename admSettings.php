<?php
namespace Afsar\wtk;
use Afsar\wtk;


//   Issue - only saves values for a short while before they revert back to defaults!


// https://hugh.blog/2014/02/26/complete-versatile-options-page-class-wordpress-plugin/

defined('ABSPATH') or die("Cannot access pages directly.");   


class admSettings {
    private $dir;
	private $file;
	private $assets_dir;
	private $assets_url;
	private $settings_base;
	private $settings;

	public function __construct( $file ) {
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/images/', $this->file ) ) );
		$this->settings_base = 'wtk_';

		// Initialise settings
		add_action( 'admin_init', array( $this, 'init' ) );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->file ) , array( $this, 'add_settings_link' ) );

		
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init() {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item() {
        global $wtk;
		// uncomment to add the menu item to the WP settings menu
		//$page = add_options_page( $wtk->plugin_name.' - Settings' , $wtk->plugin_name , 'manage_options' , 'wtk_settings' ,  array( $this, 'settings_page' ) );
		//add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets() {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'farbtastic' );

		// We're including the WP media scripts here because they're needed for the image upload field
		// If you're not including an image upload then you can leave this function call out
		wp_enqueue_media();

		wp_register_script( 'wpt-admin-js', plugin_dir_url(__FILE__) . 'js/adm_settings.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
		wp_enqueue_script( 'wpt-admin-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=wtk_settings">' . __( 'Settings', 'plugin_textdomain' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields() {

		$settings['standard'] = array(
			'title'					=> __( 'Basic', 'plugin_textdomain' ),
			'description'			=> __( 'These are fairly basic form input fields.', 'plugin_textdomain' ),
			'fields'				=> array(
				array(
					'id' 			=> 'referral_code',
					'label'			=> __( 'Referral Code' , 'plugin_textdomain' ),
					'description'	=> __( 'Used to restrict new registrations.', 'plugin_textdomain' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'LEEDS', 'plugin_textdomain' )
				),
				array(
					'id' 			=> 'password_field',
					'label'			=> __( 'A Password' , 'plugin_textdomain' ),
					'description'	=> __( 'This is a standard password field.', 'plugin_textdomain' ),
					'type'			=> 'password',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'plugin_textdomain' )
				),
				array(
					'id' 			=> 'secret_text_field',
					'label'			=> __( 'Some Secret Text' , 'plugin_textdomain' ),
					'description'	=> __( 'This is a secret text field – any data saved here will not be displayed after the page has reloaded, but it will be saved.', 'plugin_textdomain' ),
					'type'			=> 'text_secret',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'plugin_textdomain' )
				),
				array(
					'id' 			=> 'text_block',
					'label'			=> __( 'A Text Block' , 'plugin_textdomain' ),
					'description'	=> __( 'This is a standard text area.', 'plugin_textdomain' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text for this textarea', 'plugin_textdomain' )
				),
				array(
					'id' 			=> 'single_checkbox',
					'label'			=> __( 'An Option', 'plugin_textdomain' ),
					'description'	=> __( 'A standard checkbox – if you save this option as checked then it will store the option as \'on\', otherwise it will be an empty string.', 'plugin_textdomain' ),
					'type'			=> 'checkbox',
					'default'		=> ''
				),
				array(
					'id' 			=> 'select_box',
					'label'			=> __( 'A Select Box', 'plugin_textdomain' ),
					'description'	=> __( 'A standard select box.', 'plugin_textdomain' ),
					'type'			=> 'select',
					'options'		=> array( 'drupal' => 'Drupal', 'joomla' => 'Joomla', 'wordpress' => 'WordPress' ),
					'default'		=> 'wordpress'
				),
				array(
					'id' 			=> 'radio_buttons',
					'label'			=> __( 'Some Options', 'plugin_textdomain' ),
					'description'	=> __( 'A standard set of radio buttons.', 'plugin_textdomain' ),
					'type'			=> 'radio',
					'options'		=> array( 'superman' => 'Superman', 'batman' => 'Batman', 'ironman' => 'Iron Man' ),
					'default'		=> 'batman'
				),
				array(
					'id' 			=> 'multiple_checkboxes',
					'label'			=> __( 'Some Items', 'plugin_textdomain' ),
					'description'	=> __( 'You can select multiple items and they will be stored as an array.', 'plugin_textdomain' ),
					'type'			=> 'checkbox_multi',
					'options'		=> array( 'square' => 'Square', 'circle' => 'Circle', 'rectangle' => 'Rectangle', 'triangle' => 'Triangle' ),
					'default'		=> array( 'circle', 'triangle' )
				)
			)
		);

		$settings['extra'] = array(
			'title'					=> __( 'Advanced', 'plugin_textdomain' ),
			'description'			=> __( 'These are some advanced input fields that maybe aren\'t as common as the others.', 'plugin_textdomain' ),
			'fields'				=> array(
				array(
					'id' 			=> 'number_field',
					'label'			=> __( 'A Number' , 'plugin_textdomain' ),
					'description'	=> __( 'This is a standard number field – if this field contains anything other than numbers then the form will not be submitted.', 'plugin_textdomain' ),
					'type'			=> 'number',
					'default'		=> '',
					'placeholder'	=> __( '42', 'plugin_textdomain' )
				),
				array(
					'id' 			=> 'colour_picker',
					'label'			=> __( 'Pick a colour', 'plugin_textdomain' ),
					'description'	=> __( 'This uses WordPress\' built-in colour picker – the option is stored as the colour\'s hex code.', 'plugin_textdomain' ),
					'type'			=> 'color',
					'default'		=> '#21759B'
				),
				array(
					'id' 			=> 'an_image',
					'label'			=> __( 'An Image' , 'plugin_textdomain' ),
					'description'	=> __( 'This will upload an image to your media library and store the attachment ID in the option field. Once you have uploaded an imge the thumbnail will display above these buttons.', 'plugin_textdomain' ),
					'type'			=> 'image',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'multi_select_box',
					'label'			=> __( 'A Multi-Select Box', 'plugin_textdomain' ),
					'description'	=> __( 'A standard multi-select box – the saved data is stored as an array.', 'plugin_textdomain' ),
					'type'			=> 'select_multi',
					'options'		=> array( 'linux' => 'Linux', 'mac' => 'Mac', 'windows' => 'Windows' ),
					'default'		=> array( 'linux' )
				)
			)
		);

		// No idea what this does - so comment out to see what happens (or rather what doesn't happen)
		$settings = apply_filters( 'plugin_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings() {
		if( is_array( $this->settings ) ) {
			foreach( $this->settings as $section => $data ) {

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ),'wtk_settings' );
	
				foreach( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->settings_base . $field['id'];
					register_setting($section, $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this, 'display_field' ), 'wtk_settings', $section, array( 'field' => $field ) );
				}
			}
		}
	}

	public function settings_section( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Generate HTML for displaying fields
	 * @param  array $args Field data
	 * @return void
	 */
	public function display_field( $args ) {

		$field = $args['field'];

		$html = '';

		$option_name = $this->settings_base . $field['id'];
		$option = get_option( $option_name );

		$data = '';
		if( isset( $field['default'] ) ) {
			$data = $field['default'];
			if( $option ) {
				$data = $option;
			}
		}

		switch( $field['type'] ) {

			case 'text':
			case 'password':
			case 'number':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . $data . '"/>' . "\n";
			break;

			case 'text_secret':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value=""/>' . "\n";
			break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . $data . '</textarea><br/>'. "\n";
			break;

			case 'checkbox':
				$checked = '';
				if( $option && 'on' == $option ){
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $option_name ) . '" ' . $checked . '/>' . "\n";
			break;

			case 'checkbox_multi':
				foreach( $field['options'] as $k => $v ) {
					$checked = false;
					if( in_array( $k, $data ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;

			case 'radio':
				foreach( $field['options'] as $k => $v ) {
					$checked = false;
					if( $k == $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;

			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
				foreach( $field['options'] as $k => $v ) {
					$selected = false;
					if( $k == $data ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;

			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
				foreach( $field['options'] as $k => $v ) {
					$selected = false;
					if( in_array( $k, $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '" />' . $v . '</label> ';
				}
				$html .= '</select> ';
			break;

			case 'image':
				$image_thumb = '';
				if( $data ) {
					$image_thumb = wp_get_attachment_thumb_url( $data );
				}
				$html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
				$html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'plugin_textdomain' ) . '" data-uploader_button_text="' . __( 'Use image' , 'plugin_textdomain' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'plugin_textdomain' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'plugin_textdomain' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
			break;

			case 'color':
				?><div class="color-picker" style="position:relative;">
			        <input type="text" name="<?php esc_attr_e( $option_name ); ?>" class="color" value="<?php esc_attr_e( $data ); ?>" />
			        <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
			    </div>
			    <?php
			break;

		}

		switch( $field['type'] ) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
			break;

			default:
				$html .= '<label for="' . esc_attr( $field['id'] ) . '"><span class="description">' . $field['description'] . '</span></label>' . "\n";
			break;
		}

		echo $html;
	}

	/**
	 * Validate individual settings field
	 * @param  string $data Inputted value
	 * @return string       Validated value
	 */
	public function validate_field( $data ) {
		if( $data && strlen( $data ) > 0 && $data != '' ) {
			$data = urlencode( strtolower( str_replace( ' ' , '-' , $data ) ) );
		}
		return $data;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page() {
		
		  //Get the active tab from the $_GET param
		  $default_tab = null;
		  $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

		  ?>
		  <!-- Our admin page content should all be inside .wrap -->
		  <div class="wrap">
			<!-- Print the page title -->
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<!-- Here are our tabs -->
			<nav class="nav-tab-wrapper">
			  <a href="?page=wtk_settings" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">WP Toolkit</a>
			  <a href="?page=wtk_settings&tab=standard" class="nav-tab <?php if($tab==='standard'):?>nav-tab-active<?php endif; ?>">Standard</a>
			  <a href="?page=wtk_settings&tab=extra" class="nav-tab <?php if($tab==='extra'):?>nav-tab-active<?php endif; ?>">Extra</a>
			</nav>
		
			<div class="tab-content">	
				<?php echo $this->tab_content($tab); ?>
			</div>

		  </div>
		  <?php

	}

	private function tab_content($tab) {

		$html = "";
		
		if ($tab==null) {
			$html .= "<h2>Welcome to WP Toolkit Settings</h2>";
			$html .= "<p>&copy; Mohammed Afsar</p>";
			
			return $html;
		}

		$html .= '<div class="wrap" id="plugin_settings">' . "\n";
		$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

		// Get settings fields
		ob_start();
		//settings_fields('wtk_settings');
		settings_fields($tab);
				
        echo '<table class="form-table" role="presentation">';
        do_settings_fields( 'wtk_settings', $tab );
		echo '</table>';		
		
		$html .= ob_get_clean();

		$html .= '<p class="submit">' . "\n";
			$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'plugin_textdomain' ) ) . '" />' . "\n";
		$html .= '</p>' . "\n";
		$html .= '</form>' . "\n";

		$html .= '</div>' . "\n";

		return $html;
	}

}