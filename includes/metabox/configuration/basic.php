<?php $days = array( 'Day', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ); ?>
<div class="wbinr_from_row">
	<label>Description</label>
	<div class="wbinr_from_field">
		<input type="text" class="wbinr_input" name="wbinr_description" id="wbinr_description" placeholder="Enter Description" required="" value="<?php echo $webinar->description; ?>">
		<span class="wbinr_description"></span>
	</div>
</div>
<div class="wbinr_from_row">
	<label>Webinar Presenter </label>
	<div class="wbinr_from_field">
		<input type="text" class="wbinr_input" name="webinar_presenter" id="webinar_presenter" placeholder="Enter Presenter Name" required=""  value="<?php echo $webinar->presenter; ?>">
		<span class="wbinr_description">Enter Presenter Name of the webinar.</span>
	</div>
</div>
<div class="wbinr_from_row">
	<label>URL to your video file </label>
	<div class="wbinr_from_field">
		<input type="text" class="wbinr_input" name="webinar_video" id="webinar_video" placeholder="http://" required="" value="<?php echo $webinar->video_url; ?>">
		<span class="wbinr_description">Allowed formats: YouTube or Vimeo link, or direct link to a mp4 file.</span>
	</div>
</div>
<div class="wbinr_from_row">
	<label>Duration of your video </label>
	<div class="wbinr_from_field">
		<?php $duration = explode(":", $webinar->duration); ?>
		<div class="wbinr_vid_len_fld">
			<input type="number" class="wbinr_input" name="webinar_video_hr" id="webinar_video_hr" placeholder="0" value="<?php echo $duration[0]; ?>">
			hr
		</div>
		<div class="wbinr_vid_len_fld">
			<input type="number" class="wbinr_input" name="webinar_video_min" id="webinar_video_min" placeholder="0" required="" min="0" max="59"  value="<?php echo $duration[1]; ?>" >
			min
		</div>
		<div class="wbinr_vid_len_fld">
			<input type="number" class="wbinr_input" name="webinar_video_sec" id="webinar_video_sec" placeholder="0" required="" min="0" max="59" value="<?php echo $duration[2]; ?>">
			sec
		</div>		
		<span class="wbinr_description"></span>
	</div>
</div>
<div class="wbinr_from_row">
	<label>Webinar schedule</label>
	<div class="wbinr_from_field">
		<div class="wbinr_schedule_form">
			<div class="wbinr_schedule_repeat" id="wbinr_schedule_repeat">
				<div class="wbinr_schedule_fld schedule">
					<select name="schedule_type[]" id="schedule_type" class="schedule_type">
						<option value="every">Every</option>
						<option value="on">On</option>
					</select>
				</div>
				<div class="wbinr_schedule_fld day schedule_day">
					
					<select name="schedule_day[]" id="schedule_day" class="schedule_day" >
						<?php 
							foreach( $days as $day ){
								echo '<option value="'.$day.'">'.$day.'</option>';
							}
						?>
					</select>
					<div class="wbinr_input_error" id="schedule_day_error"></div>
				</div>
				<div class="wbinr_schedule_fld day schedule_date" style="display:none" >
					<input type="text" class="wbinr_input schedule_date" name="schedule_date[]" id="schedule_date" placeholder="Select Date" readonly="readonly">
					<div class="wbinr_input_error" id="schedule_date_error"></div>
				</div>
				<div class="wbinr_schedule_fld time">
					<input type="text" class="wbinr_input schedule_time" name="schedule_time[]" id="schedule_time" placeholder="Time" readonly="readonly">
					<button type="button" class="timepicker-trigger"><i class="fa fa-clock-o" aria-hidden="true"></i></button>
					<div class="wbinr_input_error" id="schedule_time_error"></div>
				</div>
			</div>
			<a href="#" class="wbinr_add_schedule" id="wbinr_add_schedule"><i class="fa fa-plus-square" aria-hidden="true"></i></a>
		</div>
		<div class="wbinr_schedules" id="wbinr_schedules">
			<?php 
				$schedules = $webinar->schedules;
				if(is_array($schedules)):
					foreach( $schedules as $schedule ) :
				?>
				<div class="wbinr_schedule_repeat">
					<div class="wbinr_schedule_fld schedule">
						<select id="schedule_type" class="schedule_type" disabled="disabled">
							<option value="every" <?php selected( $schedule['type'], 'every' ); ?>>Every</option>
							<option value="on" <?php selected( $schedule['type'], 'on' ); ?>>On</option>
						</select>
						<input type="hidden"  name="schedule_type[]" value="<?php echo $schedule['type'] ?>" />
					</div>
					<?php 
					if( $schedule['type'] == 'every' ): 
						$schedule_date ='display:none';
						$schedule_day = '';
					else:
						$schedule_day ='display:none';
						$schedule_date = '';
					endif; 
					?>
					<div class="wbinr_schedule_fld day schedule_day" style="<?php echo $schedule_day ?>">
						<select class="schedule_day" disabled="disabled" >
							<?php 
								foreach( $days as $day ){
									$selected = ($schedule['day'] == $day )? 'selected="selected"' : '';
									echo '<option value="'.$day.'" '. $selected .'>'.$day.'</option>';
								}
							?>
						</select>	
						<input type="hidden"  name="schedule_day[]" value="<?php echo $schedule['day'] ?>" />					
					</div>
					
					<div class="wbinr_schedule_fld day schedule_date" style="<?php echo $schedule_date ?>">
						<input type="text" class="wbinr_input" name="schedule_date[]" placeholder="Select Date" readonly="readonly" value="<?php echo $schedule['date'] ?>">
						<button type="button" class="ui-datepicker-trigger"><i class="fa fa-calendar" aria-hidden="true"></i></button>
					</div>
					
					<div class="wbinr_schedule_fld time">
						<input type="text" class="wbinr_input" name="schedule_time[]" placeholder="Time" readonly="readonly" value="<?php echo $schedule['time'] ?>">
						<button type="button" class="timepicker-trigger"><i class="fa fa-clock-o" aria-hidden="true"></i></button>
						
					</div>
					<a href="#" class="wbinr_remove_schedule"><i class="fa fa-trash" aria-hidden="true"></i></a>
				</div>
				<?php
					endforeach;
				endif;			
			?>
		</div>
	</div>
</div>
<div class="wbinr_from_row">
	<?php 
		$times = array(
			'1'		=> 'Display only the next immediate available schedule',
			'2'		=> 'Display only the next two immediate available schedule',
			'3'		=> 'Display only the next three immediate available schedule',
			'4'		=> 'Display only the next four immediate available schedule',
			'5'		=> 'Display only the next five immediate available schedule',
			'6'		=> 'Display only the next six immediate available schedule',
			'7'		=> 'Display only the next seven immediate available schedule',
			'8'		=> 'Display only the next eight immediate available schedule',
			'9'		=> 'Display only the next ninie immediate available schedule'			
		);
	?>
	<label>Times to Display</label>
	<div class="wbinr_from_field">
		<select name="wbinr_time_to_display" id="wbinr_time_to_display">
			<?php 
				foreach( $times as $key => $t ){
					$selected = ( $webinar->time_to_display == $key ) ? 'selected="selected"' : '';
					echo '<option value="'.$key.'" '.$selected.'>'.$t.'</option>';
				}
			?>
		</select>
	</div>
</div>
<div class="wbinr_from_row">
	<label>Event Timezone</label>
	<div class="wbinr_from_field">
		<select name="wbinr_timezone" id="wbinr_timezone">
			<?php echo wp_timezone_choice( $webinar->timezone, get_user_locale() ); ?>
		</select>
	</div>
</div>
<input type="button" value="Next Step" id="step2" class="button-primary nextstep" />