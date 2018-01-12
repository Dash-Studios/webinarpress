<?php
class Webinar{
	
	var $name;
	
	var $next_schedules;
	
	var $table_reg_users;
	
	var $metas = array(
		'description' 			=> 'wbinr_description',
		'presenter' 			=> 'wbinr_presenter',
		'video_url' 			=> 'wbinr_video_url',
		'duration' 				=> 'wbinr_duration',
		'schedules' 			=> 'wbinr_schedule',
		'time_to_display' 		=> 'wbinr_time_to_display',
		'timezone' 				=> 'wbinr_timezone',
		'ty_page_type' 			=> 'wbinr_thankyou_page_type',
		'ty_page' 				=> 'wbinr_thankyou_page'
	);
	
	function __construct( $id = ''  ){
		register_shutdown_function( array( $this, '__destrcut' ) );
		
		global $wpdb; 
		
		$this->db = $wpdb;
		
		$this->table_reg_users = $wpdb->prefix."webinarpress_reg_users";
		
		if( !empty( $id ) ){
			$post = get_post( $id);
			$this->id = $id;
			$this->name = $post->post_title;
			$metas = $this->get_metas( $id );
			foreach( $metas as $key => $val ){
				$this->{$key} = $val;
			}
			
			if( $this->ty_page_type == 'internal'){
				if( $this->ty_page != '' ){
					$this->ty_page_url = get_permalink( $this->ty_page );
				}else{
					$this->ty_page_url = '';
				}
			}else{
				$this->ty_page_url = $this->ty_page;
			}
			
			$timezone_string = $this->get_timezone_string();	
			date_default_timezone_set ( $timezone_string );
			
			$this->next_schedules = $this->get_next_avaialbe_schedules( $this->time_to_display );
			
					
			
		}		
	}
	
	function update_details( $args ){
		if( !is_array( $args ) && !isset( $args['ID'] ) )
			return false;
			
		if( isset( $args['schedules'] ) ){
			$args['schedules'] = serialize(  $args['schedules'] );
		}
			
		foreach( $this->metas as $arg_name => $meta_key ){
			if( isset( $args[$arg_name] ) ){
				update_post_meta( $args['ID'], $meta_key, $args[$arg_name] );
			}
		}
		return $args['ID'];
	}
	
	function get_metas( $id ){
		$metas = array();
		if( $id ){		
			foreach( $this->metas as $arg_name => $meta_key ){
				$metas[$arg_name] = stripslashes(get_post_meta( $id, $meta_key, true ));				
			}
			$metas['schedules'] = unserialize(  $metas['schedules'] ); 			
		}
		//var_dump( $metas );
		return $metas;
	}
	
	
	function get_next_repeat_time( $day, $time, $time_offset){
		$days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
		$offset_day_no = date("N", $time_offset ) - 1 ;
		$next_day_no = array_search($day, $days);
				
		//check whether its same day as offset
		if($offset_day_no  ==  $next_day_no ){
			$next_day_time = date("Y-m-d")." ".str_replace(" ", '', $time);
			$next_date_time = strtotime( $next_day_time ); 
			
			//check whether time is greater than offset time
			if( $next_date_time > $time_offset  ){
				$date_time = $next_date_time;				
			}else{				
				//calculate the next day and time 
				$next_day = date('Y-m-d', strtotime("next ".$day, $time_offset ) );
				$next_day_time = $next_day." ".str_replace(" ", '', $time ) ;
				$date_time = strtotime( $next_day_time ); 
				
			}			
		}else{			
			//calculate the next day and time 
			$next_day = date('Y-m-d', strtotime("next ".$day, $time_offset ) );
			$next_day_time = $next_day." ".str_replace(" ", '', $time ) ;
			$date_time = strtotime( $next_day_time ); 
		}
		return $date_time;
	}
	
	function sort_schedules( $a, $b ){
		if( $a['timestamp'] > $b['timestamp'] ){
			return true;
		}
		return false;
	}	
	
	
	// returns 
	function get_next_avaialbe_schedules( $num = 1, $time_offset = '' ){
		//var_dump($this->schedules);
		$current_count = 0;
		$current_time = empty( $time_offset ) ? strtotime( date( "Y-m-d H:i:s" ) ) : $time_offset;
		if( is_array( $this->schedules ) && count( $this->schedules ) > 0  ){
			$next_schedules = array();
			$avaialbe_schedules = array();
			foreach ( $this->schedules as $schedule ){
				if( $schedule['type'] == 'on' ){
					$time_tring = $schedule['date']." ".str_replace(" ", '', $schedule['time']);					
					$date_time = strtotime( $time_tring ); 
					if( $date_time > $current_time ){
						$schedule['timestamp'] = $date_time;
						$next_schedules[] = $schedule;
					}
				}else{
					$schedule['timestamp'] = $this->get_next_repeat_time( $schedule['day'], $schedule['time'], $current_time ) ;
					$next_schedules[] = $schedule; 
				}
			}
			
			usort( $next_schedules, array( $this, 'sort_schedules' ) );
			while( $next_schedule = current( $next_schedules )){
				$avaialbe_schedules[] = $next_schedule['timestamp'];
				if(  $next_schedule['type'] == 'every' ){
					$next_schedule['timestamp'] =  	$this->get_next_repeat_time( 
														$next_schedule['day'], 
														$next_schedule['time'],
														$next_schedule['timestamp'] 
													);  
					 $next_schedules[] = $next_schedule;
				}
				
				$current_count++;
				if($current_count == $num ){
					break;
				}
				next( $next_schedules );
			}
			return $avaialbe_schedules;
		}
		return false;
	}
	
	function get_next_avaialbe_schedules_list( $args = array() ){
		$defaults = array(
			'no_schedules_msg' 	=> "No schedule avaialbe.",
			'date_format'		=> "l, j F Y, \a\\t g:iA"
		);
		$args = wp_parse_args( $args, $defaults );
		
		$args = apply_filters( 'next_avaialbe_schedules_list_args', $args );
		
		if( $this->next_schedules == false ){
			return '<div class="no-schedules">'.$args['no_schedules_msg'].'<li>';
		}else{
			$html = '<ul class="wbinr_schedules">';
			foreach( $this->next_schedules as $schedule ){
				$html.= '<li>'.date( $args['date_format'], $schedule).'</li>';
			}
			$html.= '</ul>';
			return $html;
		}
	}
	
	function next_avaialbe_schedules_dropdown( $selected = '', $args = array() ){
		$defaults = array(
			'id' 				=> "wbinr_schedules_dropdown",
			'class'				=> 'wbinr_schedules_dropdown wbinr_input_fld',
			'date_format'		=> "l, j F Y, \a\\t g:iA",
			'default_text'		=> "Select Schedule",
			'multiple'			=> false
		);
		$args = wp_parse_args( $args, $defaults );
		
		$args = apply_filters( 'next_avaialbe_schedules_dropdown_args', $args );
		
		$html = '<select id="'.$args['id'].'"  name="'.$args['id'].'" class="'.$args['class'].'" >';
		$html .= '<option value="">'.$args['default_text'].'</option>';
		if( $this->next_schedules !== false ){
			foreach( $this->next_schedules as $schedule ){
				$html.= '<option value="'.$schedule.'">'.date( $args['date_format'], $schedule).'</option>';
			}			
		}
		$html .= '</select>';
		$html .= '<div id="'.$args['id'].'_error" class="wbinr_fld_error"></div>';
		return $html;
	}
	
	function get_next_avaialbe_schedule( $time_offset = '' ){
		$schedules = $this->get_next_avaialbe_schedules( 1, $time_offset);
		return ($schedules !== false )? $schedules[0] : false;
	}
	
	function get_next_schedule_remaining_time( $time_offset = '' ){
		$next_time = $this->get_next_avaialbe_schedule( $time_offset);
		if( $next_time !== false ){
			$now = strtotime(date('Y-m-d H:i:s')); 
			return $next_time - $now;
		}else{
			return false;
		}
	}
	
	function get_duration_in_seconds(){
		$duration_arr = explode(":", $this->duration );
		$hours 		= empty( $duration_arr[0] ) ? 0 : intval($duration_arr[0]);
		$minutes 	= empty( $duration_arr[1] ) ? 0 : intval($duration_arr[1]);
		$seconds 	= empty( $duration_arr[2] ) ? 0 : intval($duration_arr[2]);
		$total = ( $hours * 3600 ) + ( $minutes * 60 ) + $seconds;
		return $total;
	}
	
	function is_active(){
		$current_time =  strtotime( date( "Y-m-d H:i:s" ) );	
		$duration_in_seconds = 	$this->get_duration_in_seconds();
		$time_to_check = $current_time - $duration_in_seconds ;
		$next_schedule = $this->get_next_avaialbe_schedule( $time_to_check );
		$next_schedule_end = $next_schedule + $duration_in_seconds;
		
		if( $current_time >= $next_schedule &&  $current_time <= $next_schedule_end ){
			return $next_schedule;
		}
		return false;		
	}
	
	function get_timezone_string() {	
		$timezone = $this->timezone; 
		// if site timezone string exists, return it
		if ( strpos( $timezone, 'UTC' ) )
			return $timezone;

		// get UTC offset, if it isn't set then return UTC
		if ( 'UTC' === $timezone )
			return 'UTC';
	 	
		//remove UTC from timezone string
		$utc_offset = str_replace('UTC','',$timezone);
		
		// adjust UTC offset from hours to seconds
		$utc_offset *= 3600;
	 
		// attempt to guess the timezone string from the UTC offset
		if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
			return $timezone;
		}
	 
		// last try, guess timezone string manually
		$is_dst = date( 'I' );
	 
		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
					return $city['timezone_id'];
			}
		}		 
		// fallback to UTC
		return 'UTC';
	}
	
	function is_registered( $email, $schedule, $id = '' ){
		if( empty( $id ) ){
			$webinar_id = $this->id;
		}else{
			$webinar_id = $id;
		}
		$sql = "select * from ".$this->table_reg_users." where webinar_id = $webinar_id AND user_email = '$email' AND webinar_time = '$schedule'";
		$query = $this->db->get_results( $sql );
		if( $this->db->num_rows > 0 ){
			return true;
		}else{
			return false;
		}		
	}
	
	function register( $args ){
		if( !is_array( $args ) ){
			return false;
		}
		if( empty( $args['name'] ) ){
			$this->reg_error = 'Please enter name.';
			return false;
		}elseif( empty( $args['email'] ) ){
			$this->reg_error = 'Please enter email.';
			return false;
		}elseif( empty( $args['schedule'] ) ){
			$this->reg_error = 'Please select schedule.';
			return false;
		}else{
			$user_id = email_exists( $args['email'] );
			$date_time = date("Y-m-d H:i:s");
			if ( $user_id ) {
    			$args = array(
					'webinar_id'	=> $this->id,
					'user_id'		=> $user_id,
					'user_email'	=> $args['email'],
					'webinar_time'	=> $args['schedule'],
					'reg_date_time'	=> $date_time
				);
			}else{
				$pass = rand(9999, 99999);
				$userdata = array(
					'user_login'  	=> $args['email'],
					'user_email'	=> $args['email'],
					'user_pass'  	=> $pass,
					'display_name'	=> $args['name'] 
				);
				
				$user_id = wp_insert_user( $userdata ) ;
				
				if ( ! is_wp_error( $user_id ) ) {
					$args = array(
						'webinar_id'	=> $this->id,
						'user_id'		=> $user_id,
						'user_email'	=> $args['email'],
						'webinar_time'	=> $args['schedule'],
						'reg_date_time'	=> $date_time
					);
				}
			}
			
			$query = $this->db->insert( $this->table_reg_users, $args );
			if( $this->db->insert_id ){				
				return $this->db->insert_id;
			}else{
				$this->reg_error = 'Error! Could not save data. Try again later.';
				return false;
			}
			
		}
 	}
	
	function getEmbedUrl(){
		return $this->getYoutubeEmbedUrl( $this->video_url ); 
	}
	
	function getYoutubeEmbedUrl( $url ) {
		$shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';
		$longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))(\w+)/i';
	
		if (preg_match($longUrlRegex, $url, $matches)) {
			$youtube_id = $matches[count($matches) - 1];
		}
	
		if (preg_match($shortUrlRegex, $url, $matches)) {
			$youtube_id = $matches[count($matches) - 1];
		}
		return 'https://www.youtube.com/embed/' . $youtube_id ;
	}
	
	function __destrcut(){
		return false;
	}
}
?>