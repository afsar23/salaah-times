<?php
namespace Afsar\wtk;
use Afsar\wtk;

defined('ABSPATH') or die("Cannot access pages directly.");   


//Override check sidebar widgets filter in child theme
add_filter('sidebars_widgets','Afsar\wtk\wtk_widget_areas_keep' );   // this ensures theme doesn't reset custom widget areas
function wtk_widget_areas_keep($sidebars_widgets) {
    return $sidebars_widgets;
}


// this bit registers custom widget areas, so widgets can be added to the area
add_action( 'widgets_init', 'Afsar\wtk\register_wtk_widget_areas' );
function register_wtk_widget_areas() {
   
   register_sidebar(
        array(
        'id' => 'wtk_broadcast_widget_area',
        'name' => esc_html__( 'Broadcast Area', 'theme-domain' ),
        'description' => esc_html__( 'Displays before content', 'theme-domain' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<div class="widget-title-holder"><h3 class="widget-title">',
        'after_title' => '</h3></div>'
        )
    );

}



// this bit will actually display the custom widget area(s) at the desired juncture
add_action( 'loop_start', 'Afsar\wtk\wtk_show_broadcast_widget_area');
function wtk_show_broadcast_widget_area() {
	
	if (!is_admin()) {
        if ( is_active_sidebar( 'wtk_broadcast_widget_area' ) ) {
            echo '<div id="secondary-sidebar" class="wtk_broadcast_widget_area">';
			dynamic_sidebar( 'wtk_broadcast_widget_area' );
            echo '</div>';
        } 
    }
}


####################   PLUGIN WIDGETS #######################
#####################################

// Register and load the widget
// generic widget wrapper that allows you to choose which widget to diplay 
function wtk_load_widget() {

    register_widget( 'Afsar\wtk\wtk_widget' );
	
}
add_action( 'widgets_init', 'Afsar\wtk\wtk_load_widget' );


// Creating the widget 
class wtk_widget extends \WP_Widget {
 
	function __construct() {
		parent::__construct(
	 
			// Base ID of your widget
			'wtk_widget', 
			 
			// Widget name will appear in UI
			__('Afsar Widgets', 'wtk_widget_domain'), 
			 
			// Widget description
			array( 'description' => __( 'Afsar Widget Set', 'wtk_widget_domain' ), ) 
		);
	}
	 
	// Creating widget front-end
	 
	public function widget( $args, $instance ) {
		
		//$title = apply_filters( 'widget_title', $instance['title'] );
		$title = ( isset( $instance[ 'title' ] ) ) ? $instance[ 'title' ] : '';
		$wtype = ( isset( $instance[ 'widget_type' ] ) ) ? $instance[ 'widget_type' ] : '';
		$disp_title = (isset( $instance[ 'disp_title' ] ) and $instance[ 'disp_title' ]); //? 'true' : 'false';
		
		// This is where you run the code and display the output
		switch ($wtype) {
			case 'Dummy'		: $content = $this->fncDummy(); break;
			case 'LogInOut'		: $content = $this->fncLilo(); break;
			default				: $content = ""; break;
		}

		if ($content!="") {			
			echo $args['before_widget'];
			if ( ! empty( $title ) and ($disp_title) ) echo $args['before_title'] . $title . $args['after_title'];
			echo __( $content, 'wtk_widget_domain' );
			echo $args['after_widget'];
		}
		
	}
				 
	// Widget Backend 
	public function form( $instance ) {
		
		 // PART 1: Extract the data from the instance variable
		 
		 //echo ("<i>instance = ".print_r($instance,true)."</i><hr/>");
		 $instance = wp_parse_args( (array) $instance, array( 'title' => '', 
															  'widget_type' => '',
															  'disp_title' => true
															 )
									 );
		 			 
		 $title = $instance['title'];
		 $widget_type = $instance['widget_type'];
		 $disp_title = $instance['disp_title'];

		 // Widget Title field
		echo '
			<p>
			<label for="'.$this->get_field_id('title').'">Title: 
			<input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.attribute_escape($title).'" />
			</label>
			</p>
		';
		
		// Widget Type field
		echo '
			 <p>
			  <label for="'.$this->get_field_id('widget_type').'">Widget Type:
				<select class="widefat" id="'.$this->get_field_id('widget_type').'" name="'.$this->get_field_name('widget_type').'">
				  '.$this->widget_types($widget_type).'
				 </select>                
			  </label>
			 </p>
		';
		
		// Widget Display Title field 
		$checked = ($disp_title) ? "checked" : "";
		echo '
			 <p>
				<input class="checkbox" type="checkbox" '.$checked.' id="'.$this->get_field_id('disp_title').'" name="'.$this->get_field_name('disp_title').'"/>
				<label for="'.$this->get_field_id('disp_title' ).'">Display Title</label>
			 </p>
		';
			
	}
	

	function widget_types($wt) {
	
		$types = array(	
					array("value"=>"","label"=>"--Select--"),
					array("value"=>"LogInOut","label"=>"LogInOut"),
					array("value"=>"Dummy","label"=>"Dummy"),
				);
		
		$out = "";
		
		foreach($types as $opt) {
			$out .= '<option value="'.$opt['value'].'"';
			$out .= ($wt==$opt['value']) ? ' selected="selected">' : '>';
			$out .= $opt['label'];
			$out .= '</option>';		
		}	
		return $out;

	}

	
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['widget_type'] = ( ! empty( $new_instance['widget_type'] ) ) ? strip_tags( $new_instance['widget_type'] ) : '';
		$instance[ 'disp_title' ] = ($new_instance[ 'disp_title' ]) ? true : false;
		return $instance;
	}
		

	public function fncDummy() {
	
		return "<blockquote>Dummy Widget</blockquote>";
		
	}
	


	function fncLilo() {
		
		global $post;
		$post_slug = $post->post_name;
	
		$lilo = "";
		if ($post_slug != "my-account") {
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				if ( ($current_user instanceof \WP_User) ) {
					$lilo .= '<span class="dashicons dashicons-admin-users"></span>';
					//$lilo .= get_avatar( $current_user->ID, 32 );
					$lilo .= ' <strong>'.esc_html( $current_user->display_name );
					$lilo .= ' [<a href="'.wp_logout_url('my-account?fnc=login').'">Logout</a>]</strong>';  
				}
			} else {
				$lilo .= '<span class="dashicons dashicons-admin-users"></span>';
				$lilo .= ' [<a href="'.home_url('/my-account?fnc=login').'">Login</a>]</strong>';  
			}			
		}
		
		return "<div style='text-align:right'>".$lilo."</div>";
	}


	
}