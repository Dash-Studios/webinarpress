<?php
/*
Plugin Name: Webinarpress
Plugin URI: http://www.innovion.in
Description: Webinar Plugin for WordPress
Version: 0.0.1
Author: Sudip Das
Author URI: http://www.innovion.in
*/
define('WEBINER_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ));
define('WEBINER_LIB_PATH', WEBINER_PATH.'/lib' );
define('WEBINER_TEMPLATE_PATH', WEBINER_PATH.'/templates' );
define('WEBINER_TABLE', WEBINER_PATH.'/templates' );

include( WEBINER_LIB_PATH.'/index.php' );

class WebinarPress{
	var $version = '0.0.1';

	var $url;
	
	var $post_type = 'webinar';
	
	var $table_reg_users;
	
	var $db;
	
	var $tabs = array(
		'basic'			=> 'Basic',
		'pages'			=> 'Pages',
		'notification' 	=> 'Notification',
		'integrations'	=> 'Integrations'
	);

	function __construct(){
		global $wpdb; 
		
		$this->url = plugins_url( '', __FILE__ );	
		
		$this->db = $wpdb;
		
		$this->table_reg_users = $wpdb->prefix."webinarpress_reg_users";

		register_activation_hook(__FILE__, array($this, 'plugin_activate') );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts'), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts'));
		add_action( 'init', array( $this, 'webinar_post_type'),5 );
		add_action( 'admin_init', array( $this, 'add_webinar_custom_metabox' ) );
		add_action( 'save_post', array( $this, 'save_webinar_custom_metabox' ) );
		
		add_action( 'wp_ajax_wbinrAjax', array( $this, 'handle_ajax_request') );
		add_action( 'wp_ajax_nopriv_wbinrAjax', array( $this, 'handle_ajax_request') );
		
		add_filter( 'single_template', array( $this, 'filter_webinar_template' ) );
		
		foreach( $this->tabs as $tab_id=> $tab_name ){
			add_action( 'wbinr_tab_'.$tab_id, array( $this, 'wbinr_tab_content' ), 10, 2 );
		}
	}
	
	function plugin_activate(){
		$sql = '
			CREATE TABLE IF NOT EXISTS `'.$this->table_reg_users.'` (
  				`ID` int(11) NOT NULL AUTO_INCREMENT,
				`webinar_id` int(11) NOT NULL,
				`user_id` int(11) NOT NULL,
				`user_email` varchar(100) NOT NULL,
				`webinar_time` varchar(30) NOT NULL,
				`reg_date_time` datetime NOT NULL,
  				PRIMARY KEY (`ID`)
			)';
		$this->db->query( $sql );
	}

	function enqueue_scripts(){
		global $post;
		if(is_admin()){
			//includes scripts and css in admin head
			wp_enqueue_script( 'jquery-ui-datepicker' );
			
			wp_register_script( 'webinar-timepicker', plugins_url( '/js/wickedpicker.min.js', __FILE__ ) ,array('jquery'), $this->version);
			wp_enqueue_script('webinar-timepicker');
			
			wp_register_script( 'webinar-script', plugins_url( '/js/admin-script.js', __FILE__ ) ,array('jquery','jquery-ui-datepicker'), $this->version);
			wp_enqueue_script('webinar-script');
			
			wp_register_style( 'font-awesome', plugins_url( '/css/font-awesome.min.css', __FILE__ ), '4.7.0' );
			wp_enqueue_style( 'font-awesome' );
			
			wp_register_style( 'webinar-timepicker-style', plugins_url( '/css/wickedpicker.min.css', __FILE__ ), $this->version );
			wp_enqueue_style( 'webinar-timepicker-style' );
			
			wp_register_style( 'jquery-ui', '//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
    		wp_enqueue_style( 'jquery-ui' ); 
			
			wp_register_style( 'webinar-style', plugins_url( '/css/admin-style.css', __FILE__ ), $this->version );
			wp_enqueue_style( 'webinar-style' );
			
		}else{
			//includes scripts and css in frontend
			if( $post->post_type == $this->post_type ) { // only for webinars
				
				global $webinar;
				
				wp_register_style( 'font-awesome', plugins_url( '/css/font-awesome.min.css', __FILE__ ), '4.7.0' );
				wp_enqueue_style( 'font-awesome' );
				
				wp_register_style( 'jquery-countdown', plugins_url( '/css/jquery.countdown.css', __FILE__ ), '4.7.0' );
				wp_enqueue_style( 'jquery-countdown' );
			
				$template = wbinr_get_template();
				wp_register_style( 'webinar-style', plugins_url( '/css/style.css', __FILE__ ), $this->version );
				wp_enqueue_style( 'webinar-style' );
				
				wp_register_style( 'webinar-template-style', plugins_url( '/templates/'.$template.'/css/style.css', __FILE__ ), $this->version );
				wp_enqueue_style( 'webinar-template-style' );	
				
				wp_register_script( 'jquery-countdown-script', plugins_url( '/js/jquery.countdown.js', __FILE__ ),array('jquery'), $this->version);
				wp_enqueue_script('jquery-countdown-script');			
				
				wp_register_script( 'webinar-script', plugins_url( '/js/script.js', __FILE__ ) ,array('jquery'), $this->version);
				wp_enqueue_script('webinar-script');
				
				wp_localize_script( 
					'webinar-script', 
					'wbinr', 
					array( 
						'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
						'next_schedule' 	=> $webinar->get_next_schedule_remaining_time()
					)
				);	
				
			}
		}
	}
	
	function handle_ajax_request(){
		$action = $_POST['wbinrAction'];
		global $current_user;
	
		$result = array( 'error'=>1, 'msg'=>'Sorry! we are unable process your Request','field'=>'' );
		$data=json_decode(rawurldecode(stripslashes( $_POST['data']) ));
						
		switch($action){
			case 'Register':
				
				$schedule 	= $data->wbinr_schedules_dropdown;
				$name		= $data->wbinr_user_name;
				$email		= $data->wbinr_user_email;
				$id 		= $data->webinar_id;
				
				$webinar	= new Webinar( $id );
				
				if( $webinar->is_registered( $email, $schedule ) ){
					$result['msg'] = 'You are already registered.Please login to see the webinar.';
				}else{
					$args = array(
						'name'		=> $name,
						'email'		=> $email,
						'schedule' 	=> $schedule
					);
					$reg_id = $webinar->register( $args );
					if( $reg_id ){
						$result['error'] = 0; 
						$result['msg'] = 'You are successfully registered.';
						$result['redirect_url'] = $webinar->ty_page_url;
					}else{
						$result['msg'] = 'Error! Please try again later.'.$webinar->reg_error;
					}
				}				
			break;
		}
		echo json_encode($result);	
		exit;
	}
	
	// register webinar post type
	function webinar_post_type() {	
		register_post_type( $this->post_type ,
			array(
				'labels' => array(
					'name' => __( 'Webinar' ),
					'singular_name' => __( 'Webinar' ),
					'add_new' => __( 'Add New' ),
					'add_new_item' => __( 'Add New Webinar' ),
					'edit' => __( 'Edit' ),
					'edit_item' => __( 'Edit Webinar' ),
					'new_item' => __( 'New Webinar' ),
					'view' => __( 'View Webinar' ),
					'view_item' => __( 'View Webinar' ),
					'search_items' => __( 'Search Webinar' ),
					'not_found' => __( 'No Webinar found' ),
					'not_found_in_trash' => __( 'No Webinar found in Trash' ),
					'parent' => __( 'Parent Webinar' ),
				),
				'piblic' => true,
				'show_ui' => true,
				'publicly_queryable' => true,
				'show_in_nav_menus' => true,
				'hierarchical' => false,
				'rewrite'=>array('slug'=>'webinar'),
				'menu_position'=>5,
				'menu_icon' =>'dashicons-video-alt2',			
				'supports' => array('title', 'thumbnail')
			)
		);
		flush_rewrite_rules();
	}
	
	
	//add meta box for webinar settings
	function add_webinar_custom_metabox() {
		add_meta_box( 'webinar-metabox', __( 'Configuration' ), array( $this, 'webinar_custom_metabox' ), 'webinar', 'normal', 'low' );
	}
	
	
	function webinar_custom_metabox() {
		global $post, $webinar;
		$webinar = new Webinar( $post->ID );
		include ( WEBINER_PATH. '/includes/metabox/configuration/tabs.php');
	}
	
	function wbinr_tab_content( $webinar_id, $tab_id){
		global $webinar;		
		include ( WEBINER_PATH. '/includes/metabox/configuration/'.$tab_id.'.php');
	}
	
	function save_webinar_custom_metabox( $post_id ) {
		global $post;	
		if( $_POST && $post->post_type == 'webinar' ) {
			$wbinrObj = new Webinar();
			$schedules = array();
			
			$schedule_type = $_POST['schedule_type'];
			
			if( is_array( $schedule_type ) ){
				foreach( $schedule_type as $index => $val ){
					if(	!empty( $_POST['schedule_time'][$index] ) && 
						( !empty( $_POST['schedule_day'][$index]) || !empty($_POST['schedule_date'][$index]) )
					){
						$schedule = array(
							'type' 	=> $val,
							'day'	=> $_POST['schedule_day'][$index],
							'date'	=> $_POST['schedule_date'][$index],
							'time'	=> $_POST['schedule_time'][$index],
						);
						$schedules[] = $schedule;
					}					
				}
			}else{
				if(	!empty( $_POST['schedule_time']) && 
						( !empty( $_POST['schedule_day']) || !empty($_POST['schedule_date']) )
				){
					$schedules[] = array(
						'type' 	=> $val,
						'day'	=> $_POST['schedule_day'],
						'date'	=> $_POST['schedule_date'],
						'time'	=> $_POST['schedule_time'],
					);
				}
			}
			//print_r($schedules);
			//exit;
			$ty_page = ($_POST['wbinr_thankyou_page'] == 'internal')? $_POST['webinar_thankyou_page_id'] : $_POST['webinar_thankyou_page_url'];
			$args = array(
				'ID'	=> $post_id,
				'description' 			=> $_POST['wbinr_description'],
				'presenter' 			=> $_POST['webinar_presenter'],
				'video_url' 			=> $_POST['webinar_video'],
				'duration' 				=> $_POST['webinar_video_hr'] .':'.$_POST['webinar_video_min'].':'.$_POST['webinar_video_sec'] ,
				'schedules' 			=> $schedules,
				'time_to_display' 		=> $_POST['wbinr_time_to_display'],
				'timezone' 				=> $_POST['wbinr_timezone'],
				'ty_page_type' 			=> $_POST['wbinr_thankyou_page'],
				'ty_page' 				=> $ty_page
			);
			$wbinrObj->update_details( $args );
		}
	}
	
	function wbinr_register_form( $args = array() ){
		global $webinar, $current_user;
	?>	
		<div class="wbinr_box_wrap" id="wbinr_register_box_wrap" >
			<div class="wbinr_registration_box" id="wbinr_register_box">
				<a href="#wbinr_register_box_wrap" class="wbinr_close"><i class="fa fa-times" aria-hidden="true"></i></a>
				<div id="wbinr_register_error" class="wbinr_fld_error"></div>
				<form action="" method="post" id="wbinr_register_form">
					<div class="wbinr_box_left">
						<h3>Webinar schedule</h3>
						<div class="form_row">
							<?php next_avaialbe_schedules_dropdown(); ?>							
						</div>
					</div>
					<div class="wbinr_box_right">
						<h3>Your details</h3>
						<div class="form_row">
							<input type="text" placeholder="Your Name" name="wbinr_user_name" id="wbinr_user_name" class="wbinr_input_fld" value="<?php echo $current_user->display_name; ?>" />
							<div id="wbinr_user_name_error" class="wbinr_fld_error"></div>
						</div>
						<div class="form_row">
							<input type="email" placeholder="Your Email" name="wbinr_user_email" id="wbinr_user_email" class="wbinr_input_fld" value="<?php echo $current_user->user_email; ?>" />
							<div id="wbinr_user_email_error" class="wbinr_fld_error"></div>
						</div>
						<div class="form_row">
							<input type="submit" class="wbinr-button" name="wbinr_register_btn" value="Register Now" id="wbinr_register" />
							<input type="hidden" name="webinar_id" value="<?php echo $webinar->id; ?>" id="webinar_id" />
						</div>
					</div>
				</form>
			</div>
		</div>
	<?php
	}
			
	// overwrite post default template
	function filter_webinar_template( $single_template ) {
		global $post, $webinar;		
     	if ($post->post_type == $this->post_type ) {
			$webinar = new Webinar( $post->ID );
        	$single_template = WEBINER_PATH . '/templates/single-webinar.php';
     	}
     	return $single_template;
	}
	
}
global $webinerpress;
$webinerpress = new WebinarPress();
?>