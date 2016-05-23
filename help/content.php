<?php
$content = array(
		array('General', array(
				array('welcome', 'Welcome to VALS', ''),
				array('participate', 'How do I participate?', ''),
				array('roles', 'Roles in the system', ''),
				array('timeline', 'Timeline & workflow', ''))),
		array('Institution admins', array(
				array('register-institute', 'Registering yourself', ''),
				array('register-institute2', 'Adding additional supervisors/admins', ''),
				array('register-institute3', 'Adding students', ''),
		)),
		array('Organisation admins', array(
				array('register-organisation', 'Registering yourself', ''),
				array('register-organisation2', 'Adding additional mentors/admins', ''),
		)),
		array('Mentors', array(
				array('register-mentor', 'Registering yourself', ''),
				array('mentor-adding-a-project', 'Adding a project idea', ''),
				array('mentor-browse-proposals', 'Browsing proposals made by students', ''),
				array('mentor-proposal-status', 'Proposal status and offering the project to a student', ''),
				array('mentor-browse-accepted-projects', 'Your accepted projects', ''))),
		array('Supervisors', array(
				array('register-supervisor', 'Registering yourself', ''),
				array('register-institute3', 'Adding students', ''),
				array('supervisor-browse-proposals', 'Browsing proposals made by students', ''),
				array('supervisor-browse-accepted-projects', 'Your accepted projects', ''))),
		array('Students', array(
				array('register-student', 'Registering yourself', ''),
				array('student-browse-projects', 'Browsing the project ideas', ''),
				array('student-write-proposal', 'Writing a proposal', ''),
				array('student-accept-project-offer', 'Accepting an offer', ''),
				array('student-after-accepted', 'Your accepted project', ''),
		)),

)
;
$space = 10;
$extra = "";
$extension = 'htm';
$id = isset($_GET['id']) ?  $_GET['id'] : 0;
$name = isset($_GET['name']) ?  $_GET['name'] : '';

$files = array();
foreach ($content as $c => $chapter){
	$chapter_title = $chapter[0];
	echo "<div class='menu-title'>$chapter_title</div>
	<ul class='nav'>";
	foreach ($chapter[1] as $m => $menu){
		$menu_nr = ($c * $space) + $m;
		$class = (($menu_nr == $id) || ($name == $menu[0])) ? " class='current' ": "";
		echo "<li><a href='?id=$menu_nr'$class>${menu[1]}</a></li>";
		$files[$menu[0]] = $menu_nr;
	}
	echo "</ul>";
}

if ($name && isset($files[$name])) {
	$id = $files[$name];
}

if ($id){
	$chapter = round($id/$space);
	$menu_item = $id % $space;
	$file = (isset($content[$chapter]) && isset($content[$chapter][1][$menu_item])) ? $content[$chapter][1][$menu_item][0] : null;
	if ($file){
		$prev = $menu_item ? ($chapter * $space) + ($menu_item -1) :
		($content[$chapter -1][1] ?  (($chapter -1) * $space) + count($content[$chapter -1][1]) -1: -1);
		$next = isset($content[$chapter][1][$menu_item + 1]) ? ($chapter * $space) + ($menu_item + 1) :
		((isset($content[$chapter][1]) && $content[$chapter][1]) ?  (($chapter + 1) * $space) : null);
	}
	$file = $file ? "$file.$extension" :  "welcome.$extension";
} else {
	$file = "welcome.$extension";
	$prev = -1;
	$next = 1;
	$chapter = 0;
	$menu_item = 0;

}
?>