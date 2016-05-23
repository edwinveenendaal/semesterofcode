<?php
include('include.php');
include(_VALS_SOC_ROOT.'/includes/classes/Project.php');//action:proposal,...
include(_VALS_SOC_ROOT.'/includes/classes/Institutes.php');
include(_VALS_SOC_ROOT.'/includes/classes/Organisations.php');
include(_VALS_SOC_ROOT.'/includes/classes/Proposal.php');

switch ($_GET['action']){
	//Moved the student_actions to proposal_actions. We keep this file for student actions only. At the moment there are none

	default: echo "No such action: ".$_GET['action'];
	}