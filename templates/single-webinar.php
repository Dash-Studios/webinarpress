<?php
$template = wbinr_get_template();

define( 'WEBINER_CURRENT_TEMPLATE_PATH', WEBINER_TEMPLATE_PATH.'/'.$template  );

/* Include global header */
include ( WEBINER_TEMPLATE_PATH . '/partial/header.php' ); 

/* Include selected temaplte body */
include ( WEBINER_CURRENT_TEMPLATE_PATH.'/index.php' );

/* Include global footer */
include ( WEBINER_TEMPLATE_PATH . '/partial/footer.php' ); 
?>