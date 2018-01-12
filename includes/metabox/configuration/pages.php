<div class="wbinr_from_row">
	<?php 
		$pages = get_posts( array( 'post_type'=> 'page', 'post_per_page' => -1, 'post_status'=> 'publish') );
	?>
	<label>Thank you page URL</label>
	<div class="wbinr_from_field">
		<select name="wbinr_thankyou_page" id="wbinr_thankyou_page" data-page="thankyou" class="wbinr_page_select">
			<option value="internal" <?php selected( $webinar->ty_page_type, 'internal' ); ?> >Internal Page</option>
			<option value="external" <?php selected( $webinar->ty_page_type, 'external' ); ?> >External URL</option>			
		</select>
		<?php
			if( $webnar->ty_page_type == 'external' ){
				$ty_page_url = $webinar->ty_page;
				$ty_page_id = 0;
			}else{
				$ty_page_url = '';
				$ty_page_id = $webinar->ty_page;
			}
		?>
		<div class="wbinr_page_url external thankyou">
			<input type="text" class="wbinr_input" name="webinar_thankyou_page_url" id="webinar_thankyou_page_url" placeholder="http://" value="<?php echo $ty_page_url; ?>" >
		</div>
		<div class="wbinr_page_url internal thankyou" style="display:block;">
			<select name="webinar_thankyou_page_id" id="webinar_thankyou_page_id">
				<option value="">Select a page</option>
				<?php 
					foreach( $pages as $page ){
						$selected = ( $page->ID == $ty_page_id )? 'selected="selected"' : '';
						echo '<option value="'.$page->ID.'" '.$selected.'s>'.$page->post_title.'</option>';
					}
				?>
			</select>
		</div>
	</div>
</div>
<input type="button" value="Next Step" id="step3" class="button-primary nextstep" />