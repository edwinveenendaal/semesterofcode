<?php
include('include.php');//Includes the necessary bootstrapping and the ajax functions
module_load_include('php', 'vals_soc', 'includes/classes/Timeline'); // old version
switch ($_GET['action']){
	case 'setdate':
		if(isset($_POST['date'])){
			$now = Timeline::getInstance($_POST['date'])->getNow();
			echo $now->format('F j, Y, g:i a');
		}
		else{
			echo t("No date submitted!");
		}
		break;
	default: echo "No such action: ".$_GET['action'];
}
