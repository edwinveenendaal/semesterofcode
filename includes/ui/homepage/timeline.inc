<?php 

module_load_include('php', 'vals_soc', 'includes/classes/Timeline');
drupal_add_js(_VALS_SOC_PATH .'/includes/js/melange.timeline.js');
drupal_add_js(_VALS_SOC_PATH .'/includes/js/raphael.js');
drupal_add_js(_VALS_SOC_PATH .'/includes/js/countdown/jquery.plugin.js');
drupal_add_js(_VALS_SOC_PATH .'/includes/js/countdown/jquery.countdown.js');

/**
 * Function which will setup the timeline graphic showing the timeline as
 * a chart over the year. Additionally if a particular date is less than
 * 24 hours away, it will also show a clock counting down to that time. 
 * @param unknown $timeline_args
 * @return string
 */
function getScript($timeline_args){
	$date_insertions = '';
	foreach ($timeline_args['program_dates'] as $timeline_event){
		$date_insertions .= '{"to": "'.$timeline_event[0].'", "from": "'.$timeline_event[1].'", "title": "'.$timeline_event[2].'"},';
	}
	// depending on how the timezone is set in drupal, we need to get the current
	// timezone and replace the values for the melange pie chart with a 'T' character
	$date_insertions = str_replace(date('T'), 'T', $date_insertions);
	
	// TODO - timezone handling can be improved - for now stick to UTC and disable users able to change it in their account settings
	// if server set to BST replace GMT which can occur later in the year - remove this once timezones better handled
	$date_insertions = str_replace('GMT', 'T', $date_insertions);
	//$tz = drupal_get_user_timezone();
	
	$script = '
		<script>
		jQuery(document).ready(function($){
			var timeline_data = {
				"title_selector": "#timeline-head-title",
				"now": "'.$timeline_args['now']->format('Y-m-dTH:i:s').'",
				"timerange_selector": "#timeline-head-timerange",
				"slices": [' . rtrim($date_insertions, ",") . ']
				};
			jQuery("#timeline-body").timeline(timeline_data);
		';
		// if the time left is less than a day, start a countdown timer
		if(isDateLessThanADayAway($timeline_args['countdown'])){
			$script .= '
				var d_day = new Date();
				// note month in js is oddly 0-11 based rather than 1-12
				d_day = new Date('.$timeline_args['countdown']->format('Y,m-1,d,H,i,s').');
			    // TODO - the following does not work on older versions of webkit - results in NAN error
				$("#vals-timeline-countdown").countdown({until: d_day});
			';
		}
		// otherwise just list as text
		else{
			$dDate = formatDateDiff($timeline_args['now'], $timeline_args['countdown']);
			$script .= '
				$("#vals-timeline-countdown").html("'.$dDate.'");
			';
		}
		$script .= '	
		});
		</script>
	';
	
	return $script;
}

/**
 * Gets the html & js code for the timeline block
 * @param $timeline_args - a multidimensional array of configuration and date info
 * @param string $show_countdown - boolean whether to show a count down or not
 * @return string - the html to render
 */
function getLayout($timeline_args, $show_countdown = true){
	$outputBuf = '';
	$countRemaining = '';
	if($show_countdown){
		$outputBuf = getScript($timeline_args);
		$countRemaining = t('Remaining');
	}
	return $outputBuf . '
		<div id="program-map" class="block block-project-timeline block-padding" style="height:100%;" >
			<div id="block-program-timeline-content" class="block-content">
				<div class="block-program-timeline-new">
					<div class="timeline-head">
						<div id="timeline-head-title" class="title"></div>
						<div id="timeline-head-timerange" class="timerange"></div>
					</div>
					<div id="timeline-body" class="timeline-body"></div>
				</div>
				<div id="block-program-timeline-content-countdown" class="block-project-timeline-countdown">
					<strong class="deadline">'.$timeline_args['deadline_one'].'</strong>
					<strong class="deadline">'.$timeline_args['deadline_two'].'</strong>
					<strong id="vals-timeline-countdown" class="countdown"></strong>
					<strong class="countdown">'.$countRemaining.'</strong>
				</div>
			</div>
		</div>
';
}

/**
 * Works out how long it is from a given date from now and returns
 * a pretty version of the time remaining
 * @param $start - should be now()
 * @param string $end - the date to compare against
 * @return string - pretty date
 */
function formatDateDiff($start, $end=null){
	if(!($start instanceof DateTime)) {
		//$start = new DateTime($start);
		$start = Timeline::getInstance()->getDate($start);
	}
	 
	if($end === null){
		//$end = new DateTime();
		$end = Timeline::getInstance()->getNow();
	}
	 
	if(!($end instanceof DateTime)){
		//$end = new DateTime($start);
		$end = Timeline::getInstance()->getDate($start);
	}
	 
	$interval = $end->diff($start);
	$doPlural = function($nb,$str){return $nb>1?$str.'s':$str;};
	 
	$format = array();
	if($interval->y !== 0){
		$format[] = "%y ".$doPlural($interval->y, t('year'));
	}
	if($interval->m !== 0){
		$format[] = "%m ".$doPlural($interval->m, t('month'));
	}
	if($interval->d !== 0){
		$format[] = "%d ".$doPlural($interval->d, t('day'));
	}
	if($interval->h !== 0){
		$format[] = "%h ".$doPlural($interval->h, t('hour'));
	}
	if($interval->i !== 0){
		$format[] = "%i ".$doPlural($interval->i, t('minute'));
	}
	if($interval->s !== 0){
		if(!count($format)){
			return t('less than a minute ago');
		} 
		else {
			$format[] = "%s ".$doPlural($interval->s, "second");
		}
	} 
	// We use the two biggest parts
	if(count($format) > 1) {
		$format = array_shift($format)." and ".array_shift($format);
	} 
	else {
		$format = array_pop($format);
	}

	return $interval->format($format);
}

/**
 * Checks to see if given date is less than a day away
 * @param unknown $dateToCheck
 * @return boolean 
 */
function isDateLessThanADayAway($dateToCheck){
	$dateToCheck_t = clone $dateToCheck;
	$dateToCheck_t -> modify('-1 day');
	//if(strtotime('now') > strtotime($dateToCheck_t->format('Y-m-d H:i'))){
	if(strtotime(Timeline::getInstance()->getNow()->format('Y-m-d H:i')) > strtotime($dateToCheck_t->format('Y-m-d H:i'))){
		return true;
	}
	return false;
}

function generateTimelineOutput(){
	$show_countdown = TRUE;
	$timeline_args = array();
	$now = Timeline::getInstance()->getNow();
	$timeline_args['now'] = $now;
	$period = Timeline::getInstance()->getCurrentPeriod();
	switch ($period) {
		case PROGRAM_NOT_YET_STARTED:
			$timeline_args['deadline_one'] = t('Program starts:');
			$timeline_args['deadline_two'] = Timeline::getInstance()->getProgramStartDate()->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = Timeline::getInstance()->getProgramStartDate();
			break;
		case PRE_ORG_SIGNUP_PERIOD:
			// programme is running but orgs cant register yet
			$timeline_args['deadline_one'] = t('Organisation Registration opens:');
			$timeline_args['deadline_two'] = Timeline::getInstance()->getOrgsSignupStartDate()->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = Timeline::getInstance()->getOrgsSignupStartDate();
			break;
		case ORG_SIGNUP_PERIOD:
			// programme is running orgs can now register
			$timeline_args['deadline_one'] = t('Organisation Registration closes:');
			$timeline_args['deadline_two'] = Timeline::getInstance()->getOrgsSignupEndDate()->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = Timeline::getInstance()->getOrgsSignupEndDate();
			break;
		case PRE_ORGS_ANNOUNCED_PERIOD:
			$orgs_announced_date = Timeline::getInstance()->getOrgsAnnouncedDate();
			$timeline_args['deadline_one'] = t('Accepted Organisations announced on:');
			$timeline_args['deadline_two'] = $orgs_announced_date->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = $orgs_announced_date;
			break;
		case POST_ORGS_ANNOUNCED_PERIOD:
			$timeline_args['deadline_one'] = t('Student Registration opens:');
			$timeline_args['deadline_two'] = Timeline::getInstance()->getStudentsSignupStartDate()->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = Timeline::getInstance()->getStudentsSignupStartDate();
			break;
		case STUDENT_SIGNUP_PERIOD:
			// student registration period
			$timeline_args['deadline_one'] = t('Student Registration closes:');
			$timeline_args['deadline_two'] = Timeline::getInstance()->getStudentsSignupEndDate()->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = Timeline::getInstance()->getStudentsSignupEndDate();
			break;
		case PRE_ORGS_REVIEW_APPLICATIONS_DEADLINE:
			$orgs_review_student_apps_date = Timeline::getInstance()->getOrgsReviewApplicationsDate();
			$timeline_args['deadline_one'] = t('Orgaisations review proposals deadline:');
			$timeline_args['deadline_two'] = $orgs_review_student_apps_date->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = $orgs_review_student_apps_date;
			break;
		case PRE_PROPOSAL_MATCHED_DEADLINE:
			$students_matched_deadline = Timeline::getInstance()->getStudentsMatchedToMentorsDate();
			$timeline_args['deadline_one'] = t('Proposal matched deadline:');
			$timeline_args['deadline_two'] = $students_matched_deadline->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = $students_matched_deadline;
			break;
		case PRE_STUDENTS_ANNOUNCED_DEADLINE:
			$accepted_students_announced_date = Timeline::getInstance()->getAcceptedStudentsAnnouncedDate();
			$timeline_args['deadline_one'] = t('Accepted proposals announced:');
			$timeline_args['deadline_two'] = $accepted_students_announced_date->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = $accepted_students_announced_date;
			break;
		case PRE_BONDING_PERIOD:
			$accepted_students_announced_date = Timeline::getInstance()->getAcceptedStudentsAnnouncedDate();
			$timeline_args['deadline_one'] = t('Bonding period starts:');
			$timeline_args['deadline_two'] = $accepted_students_announced_date->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = $accepted_students_announced_date;
			break;
		case PRE_CODING_PERIOD:
			$coding_period = Timeline::getInstance()->getCodingStartDate();
			$timeline_args['deadline_one'] = t('Coding period starts:');
			$timeline_args['deadline_two'] = $coding_period->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = $coding_period;
			break;
		case PRE_SUGGESTED_CODING_END_DATE:
			$suggested_coding_end_date = Timeline::getInstance()->getSuggestedCodingDeadline();
			$timeline_args['deadline_one'] = t('Suggested coding end:');
			$timeline_args['deadline_two'] = $suggested_coding_end_date->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = $suggested_coding_end_date;
			break;
		case PRE_CODING_DEADLINE:
			$coding_end_date = Timeline::getInstance()->getCodingEndDate();
			$timeline_args['deadline_one'] = t('Coding deadline:');
			$timeline_args['deadline_two'] = $coding_end_date->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = $coding_end_date;
			break;
		case OUT_OF_SEASON:
			$timeline_args['deadline_one'] = t('Out of season');
			$timeline_args['deadline_two'] = $now->format('F j, Y, g:i a') . ' '.date('T');
			$timeline_args['countdown'] = $now;
			break;
		case PROGRAM_UNAVAILABLE:
		default:
			$timeline_args['deadline_one'] = t('There are currently no available programs');
			$timeline_args['deadline_two'] = '';
			$show_countdown = FALSE;
			break;
	}
	
	$program_dates = array(
		array(Timeline::getInstance()->getProgramStartDate()->format('Y-m-dTH:i:s'), 
			Timeline::getInstance()->getCodingEndDate()->format('Y-m-dTH:i:s'), t("Off season")),
		array(Timeline::getInstance()->getOrgsSignupEndDate()->format('Y-m-dTH:i:s'),
			Timeline::getInstance()->getOrgsSignupStartDate()->format('Y-m-dTH:i:s'), t("Organisation applications")),
		array(Timeline::getInstance()->getStudentsSignupEndDate()->format('Y-m-dTH:i:s'),
			Timeline::getInstance()->getStudentsSignupStartDate()->format('Y-m-dTH:i:s'), t("Student applications")),
		array(Timeline::getInstance()->getCodingStartDate()->format('Y-m-dTH:i:s'),
			Timeline::getInstance()->getAcceptedStudentsAnnouncedDate()->format('Y-m-dTH:i:s'), t("Community bonding")),
		array(Timeline::getInstance()->getCodingEndDate()->format('Y-m-dTH:i:s'),
			Timeline::getInstance()->getCodingStartDate()->format('Y-m-dTH:i:s'), t("Students coding"))
		);
	
	$timeline_args['program_dates'] = $program_dates;
	echo getLayout($timeline_args, $show_countdown);
}

//Build!
generateTimelineOutput();