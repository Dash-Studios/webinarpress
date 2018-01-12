<div class="wbinr_page_wrap">
	<div class="wbinr_container">
		<?php while( have_posts() ) : the_post(); ?>
		<h1><?php the_title(); ?></h1>
		<div class="wbinr_timer">
			<?php wbinr_show_timer(); ?>
		</div>		
		<div class="wbinr_live">
			<?php wbinr_show(); ?>
		</div>
		<div class="wbinr_content">
			<div class="wbinr_presenter">
				Presented By: <?php the_presenter(); ?>
			</div>	
			<div class="wbinr_schedules">
				<h3>Upcoming Live Sessions</h3>
				<?php next_avaialbe_schedules(); ?>
			</div>
			<a href="#wbinr_register_box_wrap" class="wbinr-button wbinr_register_trigger">Register</a>
		</div>
		<?php  endwhile; ?>
	</div>
</div>
<?php wbinr_register_form(); ?>