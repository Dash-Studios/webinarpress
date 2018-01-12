<?php
function webinar_include_dir($path){
	if ($dir=opendir($path."/")) {
		while (false !== ($entry = readdir($dir))) {
			$filename=explode(".",$entry);
			$ext=end($filename);
			if(!in_array($entry,array('index.php','..','.'))){				
				if(is_dir($path.'/'.$entry)){
					webinar_include_dir($path.'/'.$entry);
				}elseif($ext=="php"){
					require_once($path.'/'.$entry);					
				}
			}
		}
	}
}
webinar_include_dir( WEBINER_LIB_PATH );
?>