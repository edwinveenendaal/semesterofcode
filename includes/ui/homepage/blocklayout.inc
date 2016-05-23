<?php
module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.access');
//drupal_add_library('system','ui.datepicker');

function generateValsFrontPageLayout(){
	// use this for testing the timeline and testing with other users

	if (defined('_DEBUG') && _DEBUG ){
		module_load_include('inc', 'vals_soc', 'includes/ui/homepage/testfunctions');
	}

	echo '<div id="vals_block_content" class="">';
	
	// get the intro div
	module_load_include('inc', 'vals_soc', 'includes/ui/homepage/intro');
	
    //Get the news and timeline in one block (the timeline is included within the news)
	echo "<div id='vals_block_content_bottom' class='block'>";
        module_load_include('inc', 'vals_soc', 'includes/ui/homepage/news');
	echo '</div>';	
	// get the program div
	module_load_include('inc', 'vals_soc', 'includes/ui/homepage/program');

	echo '</div>';
}

generateValsFrontPageLayout();