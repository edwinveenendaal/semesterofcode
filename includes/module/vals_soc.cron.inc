<?php
/**
 * Here we specify any custom tasks we want to do when cron normally runs
 * An example of this is checking the timeline to see if we need to
 * send out any system wide messages
 */
function vals_soc_cron_handler(){
	module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail');
    module_load_include('php', 'vals_soc', 'includes/classes/StatelessTimeline');
    $timeline = StatelessTimeline::getInstance();
    if ($timeline->isProgramDeadlineNear()){
        $proposals = Proposal::getProposalsPerOrganisation('', '', 'active', true);
        notify_all_of_project_finalisation($proposals);
    }
	/*
	// Pseudo example below - on cron run add an email to the cron email queue...
	//
	// Alternatively we'd not use the queue but send them out directly on the current
	// cron run. But by adding them to the queue should mean that once the current
	// cron finishes, any unsent messages will still be unconsumed on the 
	// queue and so run on the next cron period.
	
	drupal_set_message(t('vals_soc_cron_handler called'));
	$period = Timeline::getInstance()->getCurrentPeriod();
	variable_set('vals_soc_current_period', $period);
	
	$to = "example@example.com";
	$items = array();
	$items[0] = array('to' => $to, 'message_type' => 'vals_soc_email_accepted_organisation');
	
	$queue = DrupalQueue::get('vals_soc_cron_email');
	foreach($items as $item) {
		$queue->createItem($item);
	}
	vals_soc_cron_email_handler($items[0]);
	*/
}


/**
 * This function uses a queue and is fired by other events when they want to batch run
 * a number of emails to be sent using cron , rather than just send them in
 * bulk in one go, which can cause the server to run slowly..
 * 
 * @return multitype:string number
 */
function vals_soc_cron_queue_info_handler() {
	//drupal_set_message(t('vals_soc_cron_queue_info_handler called'));
	module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail');
	$queues['vals_soc_cron_email'] = array( 'worker callback' => 'vals_soc_cron_email_handler', // This is the callback function for each queue item.
		'time' => 180); // This is the max run time per cron run in seconds. 
		return $queues;
}

