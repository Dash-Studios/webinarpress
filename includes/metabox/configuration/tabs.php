<?php 
	global $webinerpress;
	$tabs = $webinerpress->tabs;
		
	$tabs = apply_filters( 'wbinr_tabs', $tabs );
?>

<div class="wbinr_tabs_wrap">
	<ul class="wbinr_tabs">
		<?php 
			foreach( $tabs as $tab_id => $tab_name ) {
				echo '<li><a href="#'.$tab_id.'">'.$tab_name.'</a></li>';
			}
		?>
	</ul>
	<ul class="wbinr_tab_content">
		<?php 
			foreach( $tabs as $tab_id => $tab_name ) {
				echo '<li id="wbinr_tab_'.$tab_id.'">';
					do_action( 'wbinr_tab_'.$tab_id, $post->ID, $tab_id);
				echo'</li>';
			}
		?>
	</ul>
</div>