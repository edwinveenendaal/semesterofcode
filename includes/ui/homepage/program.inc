<?php

drupal_add_css(drupal_get_path('module', 'vals_soc') . '/includes/css/dashboard/css/dashboard.css', array(
    'type' => 'file',
    'group' => CSS_THEME
));

module_load_include('php', 'vals_soc', 'includes/classes/StatelessTime');

function getTimelineEnabledMessage($period = NULL) {
    $return_args = array();
    if (!$period) {
        $period = Timeline::getInstance()->getCurrentPeriod();
    }
    if (!_VALS_USE_TIME_SCHEDULE && !in_array($period, array(
                PROGRAM_NOT_YET_STARTED, OUT_OF_SEASON, PROGRAM_UNAVAILABLE))) {
        $year = (int) date('y');
        $year_next = $year + 1;
        return array("Program Academic year $year/$year_next has started",
            'You can subscribe/login now. We hope we can offer a suitable platform '
            . 'to match mentors and their projects to the students participating.');
    }
    switch ($period) {
        case PROGRAM_NOT_YET_STARTED:

            $return_args[0] = t("Program starts") . ": " . Timeline::getInstance()->getProgramStartDate()->format('F j, Y, g:i a');
            $return_args[1] = t("Organisations and Academic partner admins can login and start to create their profiles from: ") .
                    Timeline::getInstance()->getOrgsSignupStartDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case PRE_ORG_SIGNUP_PERIOD:

            $return_args[0] = t("Program started on") . " " . Timeline::getInstance()->getProgramStartDate()->format('F j, Y, g:i a');
            $return_args[1] = t("Organisations and Academic partner admins can login and start to create their profile from: ") .
                    Timeline::getInstance()->getOrgsSignupStartDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case ORG_SIGNUP_PERIOD:

            $return_args[0] = t("Partner signup period started") . " " . Timeline::getInstance()->getOrgsSignupStartDate()->format('F j, Y, g:i a');
            $return_args[1] = t("Organisations and Academic partner admins can now login and start to create their profile.") . ' '
                    . '<br/">' .
                    t("You have until the following date to complete your Organisation and Academic partner details: ") . ' ' .
                    Timeline::getInstance()->getOrgsSignupEndDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case PRE_ORGS_ANNOUNCED_PERIOD:

            $return_args[0] = t("Partner signup ended") . " " . Timeline::getInstance()->getOrgsSignupEndDate()->format('F j, Y, g:i a');
            $return_args[1] = t("Organisations and Academic partner registration has ended.") . ' '
                    . '<br/><br/>' .
                    t("Partners announced system wide: ") . ' ' .
                    Timeline::getInstance()->getOrgsAnnouncedDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case POST_ORGS_ANNOUNCED_PERIOD:

            $return_args[0] = t("Student signup period starts: ") . " " . Timeline::getInstance()->getStudentsSignupStartDate()->format('F j, Y, g:i a');
            $return_args[1] = t("Organisations and Academic partner registration has ended.") . ' '
                    . '<br/><br/>' .
                    t("Student signup period begins: ") . ' ' .
                    Timeline::getInstance()->getStudentsSignupStartDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case STUDENT_SIGNUP_PERIOD:

            $return_args[0] = t("Student signup period") . " " . Timeline::getInstance()->getStudentsSignupStartDate()->format('F j, Y, g:i a');
            $return_args[1] = t("Academic supervisors should now invite students to participate by sending them an email containing their groups activation code. " .
                            " They can then browse the project ideas.")
                    . '<br/>' .
                    t("Student signup period ends: ") . ' ' .
                    Timeline::getInstance()->getStudentsSignupEndDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case PRE_ORGS_REVIEW_APPLICATIONS_DEADLINE:

            $return_args[0] = t("Student signup period ended") . " " . Timeline::getInstance()->getStudentsSignupEndDate()->format('F j, Y, g:i a');
            $return_args[1] = t("Students can no longer submit project proposals.") .
                    '<br/>' .
                    t("Organisations review project applications deadline: ") . ' ' .
                    Timeline::getInstance()->getOrgsReviewApplicationsDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case PRE_PROPOSAL_MATCHED_DEADLINE:

            $return_args[0] = t("Student signup period ended") . " " . Timeline::getInstance()->getStudentsSignupEndDate()->format('F j, Y, g:i a');
            $return_args[1] = t("Students can no longer submit project proposals.") .
                    '<br/>' .
                    t("Mentors and tutors deadline to match accepted projects to mentors: ") . ' ' .
                    //Timeline::getInstance()->getOrgsReviewApplicationsDate()->format('F j, Y, g:i a');
                    Timeline::getInstance()->getStudentsMatchedToMentorsDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case PRE_STUDENTS_ANNOUNCED_DEADLINE:

            $return_args[0] = t("Student signup period ended: ") . " " . Timeline::getInstance()->getStudentsSignupEndDate()->format('F j, Y, g:i a');
            $return_args[1] = t("Students can no longer submit project proposals.") .
                    '<br/>' .
                    t("Community bonding period soon.") .
                    '<br/>' .
                    t("Accepted Students and projects announced on: ") . ' ' .
                    Timeline::getInstance()->getAcceptedStudentsAnnouncedDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case PRE_BONDING_PERIOD:

            $return_args[0] = t("Accepted students now announced.") . " " . Timeline::getInstance()->getAcceptedStudentsAnnouncedDate()->format('F j, Y, g:i a');
            $return_args[1] = t("Students can no longer submit project proposals.") .
                    '<br/>' .
                    t("The Bonding period starts on the following date: ") .
                    Timeline::getInstance()->getCommunityBondingPeriodStart()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case PRE_CODING_PERIOD:

            $return_args[0] = t("Community bonding period.");
            $return_args[1] = t("This period is intended to allow all participants to communicate and and ask questions in advance of actually starting the tasks.") .
                    '<br/>' .
                    '<br/>' .
                    t(" Coding starts on the following date: ") .
                    Timeline::getInstance()->getCodingStartDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case PRE_SUGGESTED_CODING_END_DATE:

            $return_args[0] = t("Coding period started on") . ' ' . Timeline::getInstance()->getCodingStartDate()->format('F j, Y, g:i a');
            $return_args[1] = t("The suggested date students finish their work.") . ' ' . Timeline::getInstance()->getSuggestedCodingDeadline()->format('F j, Y, g:i a') .
                    '<br/>' .
                    '<br/>' .
                    t("The deadline date for students to finish their work: ") . Timeline::getInstance()->getCodingEndDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case PRE_CODING_DEADLINE:

            $return_args[0] = t("Coding period started on") . ' ' . Timeline::getInstance()->getCodingStartDate()->format('F j, Y, g:i a');
            $return_args[1] = t("The suggested date students finish their work has expired.") . ' ' . Timeline::getInstance()->getSuggestedCodingDeadline()->format('F j, Y, g:i a') .
                    '<br/>' .
                    '<br/>' .
                    t("The deadline date for students to finish their work: ") . Timeline::getInstance()->getCodingEndDate()->format('F j, Y, g:i a');
            return $return_args;

            break;
        case OUT_OF_SEASON:

            $return_args[0] = t("Out of season");
            $return_args[1] = t("The program is currently out of season.");
            return $return_args;

            break;
        case PROGRAM_UNAVAILABLE:
        default:

            $return_args[0] = t("Program unavailable");
            $return_args[1] = t("The program is currently unavailable.");
            return $return_args;

            break;
    }
}

function renderFrontProgram() {
    $program_strings = getTimelineEnabledMessage();

    echo '
		<div class="vals-program block-padding">
			<div class="block" style="width:100%">
				<h4>' . $program_strings[0] . '</h4>
					<div class="block-padding-no-border">
						<p>' . $program_strings[1] . '</p>
					</div>
			</div>
		</div>
';
}

renderFrontProgram();
