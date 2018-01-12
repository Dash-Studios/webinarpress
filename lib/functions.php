<?php
	include( WEBINER_PATH. "/packages/sendgrid-php/sendgrid-php.php" );
	
	function wbinr_get_template(){
		global $webinar;
		return 'basic';
	}
	
	
	
	function dateDifference($date1, $date2){
		$d1 = (is_string($date1) ? strtotime($date1) : $date1);
		$d2 = (is_string($date2) ? strtotime($date2) : $date2);
	
		$diff_secs = abs($d1 - $d2);
		$base_year = min(date("Y", $d1), date("Y", $d2));
	
		$diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
	
		return array
		(
			"years"         => abs(substr(date('Ymd', $d1) - date('Ymd', $d2), 0, -4)),
			"months_total"  => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1,
			"months"        => date("n", $diff) - 1,
			"days_total"    => floor($diff_secs / (3600 * 24)),
			"days"          => date("j", $diff) - 1,
			"hours_total"   => floor($diff_secs / 3600),
			"hours"         => date("G", $diff),
			"minutes_total" => floor($diff_secs / 60),
			"minutes"       => (int) date("i", $diff),
			"seconds_total" => $diff_secs,
			"seconds"       => (int) date("s", $diff)
		);
	}
	
	function is_active(){
		global $webinar;
		return $webinar->is_active();
	}
	
	function get_the_presenter(){
		global $webinar;
		return $webinar->presenter;
	}
	
	function the_presenter(){
		echo  get_the_presenter();
	}
	
	
	function next_avaialbe_schedules(){
		global $webinar;		
		echo  $webinar->get_next_avaialbe_schedules_list();
	}
	
	function next_avaialbe_schedules_dropdown( $args = array() ){
		global $webinar;		
		echo  $webinar->next_avaialbe_schedules_dropdown( $args );
	}
	
	function wbinr_show_timer(){
		global $webinar;
		if( !$webinar->is_active() ){
			echo '<div class="timer countdown" id="countdown"></div>';
		}
	}
	
	function wbinr_register_form(){
		global $webinerpress;
		$webinerpress->wbinr_register_form();
	}
	
	function wbinr_show (){
		global $webinar, $current_user;
		$active_scedule = $webinar->is_active();
		if( $active_scedule ){
			if( $webinar->is_registered( $current_user->user_email, $active_scedule ) ){
				$embed_url = $webinar->getEmbedUrl();
				echo '<iframe id="wbinr_player" width="100%" height="400" src="'.$embed_url.'?rel=0&controls=0&showinfo=0&autoplay=1&enablejsapi=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
				
				<script type="text/javascript">
					  var tag = document.createElement("script");
					  tag.id = "iframe-yt";
					  tag.src = "https://www.youtube.com/iframe_api";
					  var firstScriptTag = document.getElementsByTagName("script")[0];
					  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
					
					  var player;
					  function onYouTubeIframeAPIReady() {
						player = new YT.Player("wbinr_player", {
							events: {
							  "onStateChange": onPlayerStateChange
							}
						});
					  }
					  
					  function changeBorderColor(playerStatus) {
						var color;
						if (playerStatus == 0) {
						  document.location.reload();
						}
					  }
					  function onPlayerStateChange(event) {
						changeBorderColor(event.data);
					  }
					</script>';
			}
		}
	}
?>