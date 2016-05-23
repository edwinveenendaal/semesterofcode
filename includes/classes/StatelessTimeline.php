<?php

define('PROGRAM_NOT_YET_STARTED', 0);
define('PRE_ORG_SIGNUP_PERIOD', 10);
define('ORG_SIGNUP_PERIOD', 20);
define('PRE_ORGS_ANNOUNCED_PERIOD', 30);
define('POST_ORGS_ANNOUNCED_PERIOD', 40);
define('STUDENT_SIGNUP_PERIOD', 50);
define('PRE_ORGS_REVIEW_APPLICATIONS_DEADLINE', 60);
define('PRE_PROPOSAL_MATCHED_DEADLINE', 70);
define('PRE_STUDENTS_ANNOUNCED_DEADLINE', 80);
define('PRE_BONDING_PERIOD', 90);
define('PRE_CODING_PERIOD', 100);
define('PRE_SUGGESTED_CODING_END_DATE', 110);
define('PRE_CODING_DEADLINE', 120);
define('OUT_OF_SEASON', 130);
define('PROGRAM_UNAVAILABLE', 140);

class StatelessTimeline
{

    private static $instance;
    private $cached_program_available;
    private $cached_program_start_date;
    private $cached_program_end_date;
    private $cached_org_signup_start_date;
    private $cached_org_signup_end_date;
    private $cached_accepted_org_announced_date;
    private $cached_student_signup_start_date;
    private $cached_student_signup_end_date;
    private $cached_org_review_student_applications_date;
    private $cached_students_matched_to_mentors_deadline_date;
    private $cached_accepted_students_announced_deadline_date;
    private $cached_finalcall_notifications_sent;
    private $cached_coding_start_date;
    private $cached_coding_end_date;
    private $cached_suggested_coding_deadline;
    private $dummy_test_date = NULL;
    private $is_current_program;
    private $is_available_program;

    private function __construct()
    {
        $this->fetchDates();
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self ();
        }
        return self::$instance;
    }

    private function fetchDates()
    {
        $this->cached_program_available = variable_get('vals_timeline_program_available', 0);
        $this->sanityCheck($this->cached_program_start_date, variable_get('vals_timeline_program_start_date'));
        $this->sanityCheck($this->cached_program_end_date, variable_get('vals_timeline_program_end_date'));
        $this->sanityCheck($this->cached_org_signup_start_date, variable_get('vals_timeline_org_app_start_date'));
        $this->sanityCheck($this->cached_org_signup_end_date, variable_get('vals_timeline_org_app_end_date'));
        $this->sanityCheck($this->cached_accepted_org_announced_date, variable_get('vals_timeline_accepted_org_announced_date'));
        $this->sanityCheck($this->cached_student_signup_start_date, variable_get('vals_timeline_student_signup_start_date'));
        $this->sanityCheck($this->cached_student_signup_end_date, variable_get('vals_timeline_student_signup_end_date'));
        $this->sanityCheck($this->cached_org_review_student_applications_date, variable_get('vals_timeline_org_review_student_applications_date'));
        $this->sanityCheck($this->cached_students_matched_to_mentors_deadline_date, variable_get('vals_timeline_students_matched_to_mentors_deadline_date'));
        $this->sanityCheck($this->cached_accepted_students_announced_deadline_date, variable_get('vals_timeline_accepted_students_announced_deadline_date'));
        $this->sanityCheck($this->cached_coding_start_date, variable_get('vals_timeline_coding_start_date'));
        $this->sanityCheck($this->cached_coding_end_date, variable_get('vals_timeline_coding_end_date'));
        $this->sanityCheck($this->cached_suggested_coding_deadline, variable_get('vals_timeline_suggested_coding_deadline'));
        $this->sanityCheck($this->cached_finalcall_notifications_sent, variable_get('vals_timeline_finalcall_notifications_sent'));
    }

    /*     * *********************************
     * 		Getter methods
     * *********************************
     */

    public function getProgramAvailable()
    {
        return $this->cached_program_available;
    }

    public function getProgramStartDate()
    {
        return $this->cached_program_start_date;
    }

    public function getProgramEndDate()
    {
        return $this->cached_program_end_date;
    }

    public function getOrgsSignupStartDate()
    {
        return $this->cached_org_signup_start_date;
    }

    public function getOrgsSignupEndDate()
    {
        return $this->cached_org_signup_end_date;
    }

    public function getOrgsAnnouncedDate()
    {
        return $this->cached_accepted_org_announced_date;
    }

    public function getStudentsSignupStartDate()
    {
        return $this->cached_student_signup_start_date;
    }

    public function getStudentsSignupEndDate()
    {
        return $this->cached_student_signup_end_date;
    }

    public function getOrgsReviewApplicationsDate()
    {
        return $this->cached_org_review_student_applications_date;
    }

    public function getStudentsMatchedToMentorsDate()
    {
        return $this->cached_students_matched_to_mentors_deadline_date;
    }

    public function getAcceptedStudentsAnnouncedDate()
    {
        return $this->cached_accepted_students_announced_deadline_date;
    }

    public function getCodingStartDate()
    {
        return $this->cached_coding_start_date;
    }

    public function getCodingEndDate()
    {
        return $this->cached_coding_end_date;
    }

    public function getSuggestedCodingDeadline()
    {
        return $this->cached_suggested_coding_deadline;
    }

    public function setDummyTestDate($dummy)
    {
        $this->dummy_test_date = $dummy;
    }

    /*     * *********************************
     * 		Helper methods
     * *********************************
     */

    /**
     * Put this in one place, which makes it easier to test the timeline
     * @return DateTime
     */
    public function getNow()
    {
        if (!isset($this->dummy_test_date)) {
            $now = new DateTime();
        } else {
            $now = new DateTime($this->dummy_test_date);
        }
        return $now;
    }

    public function getDate($date_format)
    {
        $date = new DateTime($date_format);
        return $date;
    }

    public function organisationSignupPeriodOpened()
    {
        if ($this->cached_org_signup_start_date < $this->getNow()) {
            return true;
        }
        return false;
    }

    public function isOrganisationSignupPeriod()
    {
        if ($this->cached_org_signup_start_date < $this->getNow() && $this->cached_org_signup_end_date > $this->getNow()) {
            return true;
        }
        return false;
    }

    public function isPreOrganisationSignupPeriod()
    {
        if ($this->cached_org_signup_start_date > $this->getNow()) {
            return true;
        }
        return false;
    }

    public function isAfterOrganisationSignupPeriod()
    {
        if ($this->cached_org_signup_end_date < $this->getNow()) {
            return true;
        }
        return false;
    }

    public function isStudentsSignupPeriod()
    {
        if ($this->isProgramActive()) {
            $now = $this->getNow();
            return ($this->cached_student_signup_start_date <= $now) && ($this->cached_student_signup_end_date >= $now);
        }
        return false;
    }

    public function hasStudentSignupPeriodOpened()
    {
        if ($this->isProgramActive()) {
            $now = $this->getNow();
            return ($this->cached_student_signup_start_date <= $now);
        }
        return false;
    }

    /**
     * The pre-community bonding period is worked out by comparing the end of student signup
     * period and until the students announced date starts
     * @return boolean
     */
    public function isPreCommunityBondingPeriod()
    {
        if ($this->cached_student_signup_end_date < $this->getNow() && $this->cached_accepted_students_announced_deadline_date > $this->getNow()) {
            return true;
        }
        return false;
    }

    public function isCodingPeriod()
    {
        if ($this->cached_coding_start_date < $this->getNow() && $this->cached_coding_end_date > $this->getNow()) {
            return true;
        }
        return false;
    }

    /**
     * The community bonding period is worked out by comparing the when the student list was announced
     * and until the coding start date is due to start
     * @return boolean
     */
    public function isCommunityBondingPeriod()
    {
        if ($this->cached_accepted_students_announced_deadline_date < $this->getNow() && $this->cached_coding_start_date > $this->getNow()) {
            return true;
        }
        return false;
    }

    public function getCommunityBondingPeriodStart()
    {
        return $this->cached_accepted_students_announced_deadline_date;
    }

    public function isAfterOrgsAnnouncedDate()
    {
        if ($this->cached_accepted_org_announced_date < $this->getNow() && $this->isProgramActive()) {
            return true;
        }
        return false;
    }

    public function finalisationNotificationsSent()
    {
        return $this->cached_finalcall_notifications_sent;
    }

    public function isProgramDeadlineNear()
    {
        if ($this->getSuggestedCodingDeadline() < $this->getNow() &&
            !$this->finalisationNotificationsSent()) {
            return true;
        }
        return false;
    }

    public function isProgramActive()
    {
        if ($this->cached_program_available && $this->hasProgramStarted() &&
            !$this->hasProgramFinished()) {
            return true;
        }
        return false;
    }

    public function hasProgramStarted()
    {
        if ($this->cached_program_start_date < $this->getNow()) {
            return true;
        }
        return false;
    }

    public function hasProgramFinished()
    {
        if ($this->cached_program_end_date < $this->getNow()) {
            return true;
        }
        return false;
    }

    private function sanityCheck(&$localCache, $value)
    {
        if (isset($value) && $this->validateDate($value)) {
            $localCache = new DateTime($value);
        } else {
            // TODO - what do we do when these are not set.
            //for now we'll just drop NOW in there.
            $localCache = new DateTime();
        }
    }

    private function validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d H:i', $date);
        return $d && $d->format('Y-m-d H:i') == $date;
    }

    public function resetCache()
    {
        $this->fetchDates();
    }

    public function getCurrentPeriod()
    {
        $now = $this->getNow();
        if ($this->getProgramAvailable()) {
            // has it started?
            if (!$this->hasProgramStarted()) {
                return PROGRAM_NOT_YET_STARTED;
            }
            // its started so where are we?
            else if ($this->getOrgsSignupStartDate() > $now) {
                // programme is running but orgs cant register yet
                return PRE_ORG_SIGNUP_PERIOD;
            } else if ($this->isOrganisationSignupPeriod()) {
                // programme is running orgs can now register
                return ORG_SIGNUP_PERIOD;
            } else if ($this->getStudentsSignupStartDate() > $now) {
                // before student applications start
                // check to see if the org announced date is pending..
                $orgs_announced_date = $this->getOrgsAnnouncedDate();
                // accepted orgs not yet announced
                if ($orgs_announced_date > $now) {
                    return PRE_ORGS_ANNOUNCED_PERIOD;
                }
                // accepted orgs already announced
                else {
                    return POST_ORGS_ANNOUNCED_PERIOD;
                }
            } else if ($this->isStudentsSignupPeriod()) {
                // student registration period
                return STUDENT_SIGNUP_PERIOD;
            } else if ($this->isPreCommunityBondingPeriod()) {
                //before community bonding period starts and after student signup period
                $orgs_review_student_apps_date = $this->getOrgsReviewApplicationsDate();
                $students_matched_deadline = $this->getStudentsMatchedToMentorsDate();
                $accepted_students_announced_date = $this->getAcceptedStudentsAnnouncedDate();

                if ($orgs_review_student_apps_date > $now) {
                    return PRE_ORGS_REVIEW_APPLICATIONS_DEADLINE;
                } else if ($students_matched_deadline > $now) {
                    return PRE_PROPOSAL_MATCHED_DEADLINE;
                } else if ($accepted_students_announced_date > $now) {
                    return PRE_STUDENTS_ANNOUNCED_DEADLINE;
                } else {
                    return PRE_BONDING_PERIOD;
                }
            } else if ($this->isCommunityBondingPeriod()) {
                return PRE_CODING_PERIOD;
            } else if ($this->isCodingPeriod()) {
                $suggested_coding_end_date = $this->getSuggestedCodingDeadline();
                $coding_end_date = $this->getCodingEndDate();
                if ($suggested_coding_end_date > $now) {
                    return PRE_SUGGESTED_CODING_END_DATE;
                } else {
                    return PRE_CODING_DEADLINE;
                }
            } else {
                return OUT_OF_SEASON;
            }
        } else {
            return PROGRAM_UNAVAILABLE;
        }
    }

    //Get specific visibility variables per role
    //Start with a default shared set of vars
    public static function getInitialTimelineVars()
    {
        $timeline = Timeline::getInstance();
        $period = $timeline->getCurrentPeriod();
        $timeline->is_current_program = $timeline->isProgramActive();
        $timeline->is_available_program = ($period < PROGRAM_UNAVAILABLE);
        $timeline_args = array();
        //Initialise the visibility params depending on whether we want a time scheme
        //to be used in a SOC program
        $time_free_application = (!_VALS_USE_TIME_SCHEDULE) && $timeline->is_available_program;
        
        //First all possible variables
        //students only
        $timeline_args['viewOrganisations'] = $time_free_application;
        $timeline_args['viewProjectIdeas'] = $time_free_application; // look at ALL of the project ideas
        $timeline_args['myInstitutionVisible'] = $time_free_application;
        $timeline_args['myProposalsVisible'] = $time_free_application; // proposals I have submitted. Just see my proposals - not yet accepted
        $timeline_args['myOffersVisible'] = $time_free_application; // allow students to see their project offers
        $timeline_args['myAcceptedProjectsVisible'] = $time_free_application;

        //organisations only
        $timeline_args['browseProjectIdeasVisible'] = $time_free_application;
        $timeline_args['managedOrganisationsVisible'] = $time_free_application;
        //mentor only
        //orgadmin only
        //Not used at the moment
        $timeline_args['matchedProjectsVisible'] = $time_free_application;

        //institutions only
        $timeline_args['groupsVisible'] = $time_free_application;
        $timeline_args['managedInstitutesVisible'] = $time_free_application;

        //non student
        $timeline_args['manageProjectIdeasVisible'] = $time_free_application;
        $timeline_args['organisationMembersVisible'] = $time_free_application;
        $timeline_args['proposalsVisible'] = $time_free_application;
        $timeline_args['myAcceptedProjectsVisible'] = $time_free_application;

        //mentor and student
        $timeline_args['myOrganisationsVisible'] = $time_free_application; // a list of organisations i participate in
        //all
        $timeline_args['dashboardLegend'] = "";
        //$timeline_args['connectionsVisible'] = $time_free_application; // allow students to communicate with other users - perhaps ask questions of mentors etc
        //Some settings that are common to different roles

        switch ($period) {
            case PROGRAM_NOT_YET_STARTED:
                $timeline_args['dashboardLegend'] = t("Program has not started yet. Menu options will be available from the following date. ") .
                    $timeline->getProgramStartDate()->format('F j, Y, g:i a');
                break;
            case PRE_ORG_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Program is available, however you must wait until the following date to register your organization/s. ") .
                    $timeline->getOrgsSignupStartDate()->format('F j, Y, g:i a');
                break;
            case ORG_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Enter your organisation details and project ideas. You have until the following date when you can no longer add or delete entries. ") .
                    $timeline->getOrgsSignupEndDate()->format('F j, Y, g:i a');
                break;
            case PRE_ORGS_ANNOUNCED_PERIOD:
                $timeline_args['dashboardLegend'] = t("Modify your organisation details and project ideas. You have until the following date when your organisations and project " .
                        "ideas become visible to students. ") .
                    $timeline->getOrgsAnnouncedDate()->format('F j, Y, g:i a');
                break;
            case POST_ORGS_ANNOUNCED_PERIOD:
                $timeline_args['dashboardLegend'] = t("Your organisations and project ideas are now visible to other users of the system.");
                break;
            case STUDENT_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Student signup period. Students can now submit project proposals");
                break;
            case PRE_ORGS_REVIEW_APPLICATIONS_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Please review your project applications before the following date. ") .
                    $timeline->getOrgsReviewApplicationsDate()->format('F j, Y, g:i a');
                break;
            case PRE_PROPOSAL_MATCHED_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Please ensure you have matched all students projects to mentors before the following date. ") .
                    $timeline->getStudentsMatchedToMentorsDate()->format('F j, Y, g:i a');
                break;
            case PRE_STUDENTS_ANNOUNCED_DEADLINE:
                $timeline_args['dashboardLegend'] = t("The list of students and projects will become visable to everyone after the following date. ") .
                    $timeline->getAcceptedStudentsAnnouncedDate()->format('F j, Y, g:i a');
                break;
            case PRE_BONDING_PERIOD:
                $timeline_args['dashboardLegend'] = t("The list of students and projects is now visible to other users of the system. " .
                        "The Bonding period starts on the following date. ") .
                    $timeline->getCommunityBondingPeriodStart()->format('F j, Y, g:i a');
                break;
            case PRE_CODING_PERIOD:
                $timeline_args['dashboardLegend'] = t("Community bonding period.  Coding starts on the following date. ") .
                    $timeline->getCodingStartDate()->format('F j, Y, g:i a');
                break;
            case PRE_SUGGESTED_CODING_END_DATE:
                $timeline_args['dashboardLegend'] = t("Coding period. The following is the suggested end date for coding. ") .
                    $timeline->getSuggestedCodingDeadline()->format('F j, Y, g:i a');
                break;
            case PRE_CODING_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Coding period. The following is the deadline date for coding. ") .
                    $timeline->getCodingEndDate()->format('F j, Y, g:i a');
                break;
            case OUT_OF_SEASON:
                $timeline_args['dashboardLegend'] = t("The program is currently out of season.");
                break;
            case PROGRAM_UNAVAILABLE:
            default:
                $timeline_args['dashboardLegend'] = t("No program currently available.");
                break;
        }

        return array($period, $timeline_args, $timeline);
    }

    public static function getStudentTimelineVars()
    {
        list($period, $timeline_args, $timeline) = self::getInitialTimelineVars();
        if (_VALS_USE_TIME_SCHEDULE && $timeline->is_available_program) {
            $timeline_args['viewOrganisations'] = $period >= POST_ORGS_ANNOUNCED_PERIOD; // look at ALL of the organisations
            $timeline_args['myInstitutionVisible'] = $timeline_args['viewOrganisations']; // see my institution details
            $timeline_args['viewProjectIdeas'] = $timeline_args['viewOrganisations'];
            //Here is where student sees his accepted projects
            //a list of organisations i participate in
            $timeline_args['myOrganisationsVisible'] = $period >= PRE_BONDING_PERIOD;
            $timeline_args['myProposalsVisible'] = $period >= STUDENT_SIGNUP_PERIOD; // proposals I have submitted
            $timeline_args['myOffersVisible'] = $timeline_args['myProposalsVisible']; //The projects I was offered
            $timeline_args['myAcceptedProjectsVisible'] = $timeline_args['myProposalsVisible']; // this appears once a student has selected a project
        }

        switch ($period) {
            case PROGRAM_NOT_YET_STARTED:
                break;
            case PRE_ORG_SIGNUP_PERIOD:
            case ORG_SIGNUP_PERIOD:
            case PRE_ORGS_ANNOUNCED_PERIOD:
                $timeline_args['dashboardLegend'] = t("Program has now started.  Menu options will be available once the student signup period begins. ") .
                    $timeline->getStudentsSignupStartDate()->format('F j, Y, g:i a');
                break;
            case POST_ORGS_ANNOUNCED_PERIOD: // orgs announced so lets student look at the project ideas - cant apply yet
                $timeline_args['dashboardLegend'] = t("You can now browse the organisations and project ideas. " .
                        "You can start to apply for projects once the student sign period begins. ") .
                    $timeline->getStudentsSignupStartDate()->format('F j, Y, g:i a');
                break;
            case STUDENT_SIGNUP_PERIOD: // Students can now apply for projects
                $timeline_args['dashboardLegend'] = t("Student signup period. You can now submit project proposals." .
                        "Please complete any project proposals before the following date. ") .
                    $timeline->getStudentsSignupEndDate()->format('F j, Y, g:i a');
                break;
            case PRE_ORGS_REVIEW_APPLICATIONS_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Project proposals due to be evaluated by. ") .
                    $timeline->getOrgsReviewApplicationsDate()->format('F j, Y, g:i a');
                break;
            case PRE_PROPOSAL_MATCHED_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Students projects matched to mentors on the following date. ") .
                    $timeline->getStudentsMatchedToMentorsDate()->format('F j, Y, g:i a');
                break;
                break;
            case PRE_STUDENTS_ANNOUNCED_DEADLINE:
                $timeline_args['dashboardLegend'] = t("The list of students and projects will become visable to everyone after the following date. ") .
                    $timeline->getAcceptedStudentsAnnouncedDate()->format('F j, Y, g:i a');
                break;
                break;
            case PRE_BONDING_PERIOD:
                $timeline_args['dashboardLegend'] = t("The list of students and projects is now visible to other users of the system. " .
                        "The Bonding period starts on the following date. ") .
                    $timeline->getCommunityBondingPeriodStart()->format('F j, Y, g:i a');
                break;
            case PRE_CODING_PERIOD:
                $timeline_args['dashboardLegend'] = t("Community bonding period.  Coding starts on the following date. ") .
                    $timeline->getCodingStartDate()->format('F j, Y, g:i a');
                break;
            case PRE_SUGGESTED_CODING_END_DATE:
                $timeline_args['dashboardLegend'] = t("Coding period. The following is the suggested end date for coding. ") .
                    $timeline->getSuggestedCodingDeadline()->format('F j, Y, g:i a');
                break;
            case PRE_CODING_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Coding period. The following is the deadline date for coding. ") .
                    $timeline->getCodingEndDate()->format('F j, Y, g:i a');
                break;
            case OUT_OF_SEASON:
                break;
            case PROGRAM_UNAVAILABLE:
            default:
                break;
        }
        return $timeline_args;
    }

    public static function getSupervisorTimelineVars()
    {
        list($period, $timeline_args, $timeline) = self::getInitialTimelineVars();
        if (_VALS_USE_TIME_SCHEDULE && $timeline->is_available_program) {
            $timeline_args['managedInstitutesVisible'] = $period >= ORG_SIGNUP_PERIOD;
            $timeline_args['manageProjectIdeasVisible'] = $timeline_args['managedInstitutesVisible'];
            $timeline_args['groupsVisible'] = $timeline_args['managedInstitutesVisible'];
            $timeline_args['organisationMembersVisible'] = $timeline_args['managedInstitutesVisible'];
            $timeline_args['proposalsVisible'] = $period >= STUDENT_SIGNUP_PERIOD;
            $timeline_args['myAcceptedProjectsVisible'] = $period >= STUDENT_SIGNUP_PERIOD;
            $timeline_args['matchedProjectsVisible'] = $period >= PRE_BONDING_PERIOD; //- MENU OPTION - PROJECTS FOR myOrgs - these are the matched proper projects
        }

        switch ($period) {
            case PROGRAM_NOT_YET_STARTED:
                break;
            case PRE_ORG_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Program is available, however you must wait until the following date to register your institute/s. ") .
                    $timeline->getOrgsSignupStartDate()->format('F j, Y, g:i a');
                break;
            case ORG_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Enter your institute details and groups. You have until the following date when you can no longer add or delete entries. ") .
                    $timeline->getOrgsSignupEndDate()->format('F j, Y, g:i a');
                break;
            case PRE_ORGS_ANNOUNCED_PERIOD:
                $timeline_args['dashboardLegend'] = t("Modify your institute details and groups. You have until the following date when your organisations and project " .
                        "ideas become visible to students. ") .
                    $timeline->getOrgsAnnouncedDate()->format('F j, Y, g:i a');
                break;
            case POST_ORGS_ANNOUNCED_PERIOD:
                $timeline_args['dashboardLegend'] = t("Your institute and groups are now available to your students. You should distribute the group codes to the corresponding students.");
                break;
            case STUDENT_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Student signup period. Students can now submit project proposals.");
                break;
            case PRE_ORGS_REVIEW_APPLICATIONS_DEADLINE:
                //We will skip the pending state in the future
                $timeline_args['dashboardLegend'] = t("Please review the projects entered before the following date. ") .
                    $timeline->getOrgsReviewApplicationsDate()->format('F j, Y, g:i a');
                break;
            case PRE_PROPOSAL_MATCHED_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Please ensure you have matched all students projects to mentors before the following date. ") .
                    $timeline->getStudentsMatchedToMentorsDate()->format('F j, Y, g:i a');
                break;
            case PRE_STUDENTS_ANNOUNCED_DEADLINE:
                $timeline_args['dashboardLegend'] = t("The list of students and projects will become visible to everyone after the following date. ") .
                    $timeline->getAcceptedStudentsAnnouncedDate()->format('F j, Y, g:i a');
                break;
            case PRE_BONDING_PERIOD:
                $timeline_args['dashboardLegend'] = t("The list of students and projects is now visible to other users of the system. " .
                        "The Bonding period starts on the following date. ") .
                    $timeline->getCommunityBondingPeriodStart()->format('F j, Y, g:i a');
                break;
            case PRE_CODING_PERIOD:
                $timeline_args['dashboardLegend'] = t("Community bonding period.  Coding starts on the following date. ") .
                    $timeline->getCodingStartDate()->format('F j, Y, g:i a');
                break;
            case PRE_SUGGESTED_CODING_END_DATE:
                $timeline_args['dashboardLegend'] = t("Coding period. The following is the suggested end date for coding. ") .
                    $timeline->getSuggestedCodingDeadline()->format('F j, Y, g:i a');
                break;
            case PRE_CODING_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Coding period. The following is the deadline date for coding. ") .
                    $timeline->getCodingEndDate()->format('F j, Y, g:i a');
                break;
            case OUT_OF_SEASON:
                break;
            case PROGRAM_UNAVAILABLE:
            default:
                break;
        }
        return $timeline_args;
    }

    public static function getInstadminTimelineVars()
    {
        list($period, $timeline_args, $timeline) = self::getInitialTimelineVars();
        if (_VALS_USE_TIME_SCHEDULE && $timeline->is_available_program) {
            $timeline_args['managedInstitutesVisible'] = $period >= ORG_SIGNUP_PERIOD;
            $timeline_args['manageProjectIdeasVisible'] = $period >= ORG_SIGNUP_PERIOD;
            $timeline_args['organisationMembersVisible'] = $period >= ORG_SIGNUP_PERIOD;
            $timeline_args['proposalsVisible'] = $period >= STUDENT_SIGNUP_PERIOD; //Proposals submitted to my Organisations
            $timeline_args['myAcceptedProjectsVisible'] = $period >= STUDENT_SIGNUP_PERIOD;
            $timeline_args['matchedProjectsVisible'] = $period >= PRE_BONDING_PERIOD; //PROJECTS FOR myOrgs - these are the matched proper projects
        }

        switch ($period) {
            case PROGRAM_NOT_YET_STARTED:
                break;
            case PRE_ORG_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Program is available, however you must wait until the following date to register your institute/s. ") .
                    $timeline->getOrgsSignupStartDate()->format('F j, Y, g:i a');
                break;
            case ORG_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Enter your organisation details and project ideas. You have until the following date when you can no longer add or delete entries. ") .
                    $timeline->getOrgsSignupEndDate()->format('F j, Y, g:i a');
                break;
            case PRE_ORGS_ANNOUNCED_PERIOD:
                $timeline_args['dashboardLegend'] = t("Modify your organisation details and project ideas. You have until the following date when your organisations and project " .
                        "ideas become visible to students. ") .
                    $timeline->getOrgsAnnouncedDate()->format('F j, Y, g:i a');
                break;
            case POST_ORGS_ANNOUNCED_PERIOD:
                $timeline_args['dashboardLegend'] = t("Your organisations and project ideas are now visible to other users of the system.");
                break;
            case STUDENT_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Student signup period. Students can now submit project proposals");
                break;
            case PRE_ORGS_REVIEW_APPLICATIONS_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Please review your project applications before the following date. ") .
                    $timeline->getOrgsReviewApplicationsDate()->format('F j, Y, g:i a');
                break;
            case PRE_PROPOSAL_MATCHED_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Please ensure you have matched all students projects to mentors before the following date. ") .
                    $timeline->getStudentsMatchedToMentorsDate()->format('F j, Y, g:i a');
                break;
            case PRE_STUDENTS_ANNOUNCED_DEADLINE:
                $timeline_args['dashboardLegend'] = t("The list of students and projects will become visable to everyone after the following date. ") .
                    $timeline->getAcceptedStudentsAnnouncedDate()->format('F j, Y, g:i a');
                break;
            case PRE_BONDING_PERIOD:
                $timeline_args['dashboardLegend'] = t("The list of students and projects is now visible to other users of the system. " .
                        "The Bonding period starts on the following date. ") .
                    $timeline->getCommunityBondingPeriodStart()->format('F j, Y, g:i a');
                $timeline_args['matchedProjectsVisible'] = TRUE; //- MENU OPTION - PROJECTS FOR myOrgs - these are the matched proper projects
                break;
            case PRE_CODING_PERIOD:
                $timeline_args['dashboardLegend'] = t("Community bonding period.  Coding starts on the following date. ") .
                    $timeline->getCodingStartDate()->format('F j, Y, g:i a');
                break;
            case PRE_SUGGESTED_CODING_END_DATE:
                $timeline_args['dashboardLegend'] = t("Coding period. The following is the suggested end date for coding. ") .
                    $timeline->getSuggestedCodingDeadline()->format('F j, Y, g:i a');
                break;
            case PRE_CODING_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Coding period. The following is the deadline date for coding. ") .
                    $timeline->getCodingEndDate()->format('F j, Y, g:i a');
                break;
            case OUT_OF_SEASON:
                break;
            case PROGRAM_UNAVAILABLE:
            default:
                break;
        }

        return $timeline_args;
    }

    public static function getMentorTimelineVars()
    {
        list($period, $timeline_args, $timeline) = self::getInitialTimelineVars();

        //From here on the more direct approach
        if (_VALS_USE_TIME_SCHEDULE && $timeline->is_available_program) {
            $pre_org_announced = $period >= PRE_ORGS_ANNOUNCED_PERIOD;
            $timeline_args['manageProjectIdeasVisible'] = $pre_org_announced; // manage the project ideas?
            $timeline_args['browseProjectIdeasVisible'] = $pre_org_announced;
            $timeline_args['myOrganisationsVisible'] = $pre_org_announced; // a list of organisations i participate in
            $timeline_args['organisationMembersVisible'] = $pre_org_announced;
            $timeline_args['proposalsVisible'] = $period >= STUDENT_SIGNUP_PERIOD;
            $timeline_args['myAcceptedProjectsVisible'] = $period >= STUDENT_SIGNUP_PERIOD;
            $timeline_args['projectsIamMentorForVisible'] = $period >= PRE_BONDING_PERIOD;
            $timeline_args['matchedProjectsVisible'] = $period >= PRE_SUGGESTED_CODING_END_DATE; //PROJECTS FOR myOrgs - these are the matched proper projects
        }

        switch ($period) {
            case PROGRAM_NOT_YET_STARTED:
                //mentor starts to do things after orgs are announced
                break;
            case PRE_ORG_SIGNUP_PERIOD:
            case ORG_SIGNUP_PERIOD:
            case PRE_ORGS_ANNOUNCED_PERIOD:
                $timeline_args['dashboardLegend'] = t("Program has started.  More menu options will be available from the following date. ") .
                    $timeline->getOrgsAnnouncedDate()->format('F j, Y, g:i a');
                $timeline_args['organisationMembersVisible'] = TRUE;
                break;
            case POST_ORGS_ANNOUNCED_PERIOD:
                $timeline_args['dashboardLegend'] = t("Here you can view your organisations and see the project ideas.");
                break;
            case STUDENT_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Student signup period. Students can now submit project proposals");
                break;
            case PRE_ORGS_REVIEW_APPLICATIONS_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Please review your project applications before the following date. ") .
                    $timeline->getOrgsReviewApplicationsDate()->format('F j, Y, g:i a');
                break;
            case PRE_PROPOSAL_MATCHED_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Please ensure you have matched all students projects to mentors before the following date. ") .
                    $timeline->getStudentsMatchedToMentorsDate()->format('F j, Y, g:i a');
                break;
            case PRE_STUDENTS_ANNOUNCED_DEADLINE:
                $timeline_args['dashboardLegend'] = t("The list of students and projects will become visable to everyone after the following date. ") .
                    $timeline->getAcceptedStudentsAnnouncedDate()->format('F j, Y, g:i a');
                break;
            case PRE_BONDING_PERIOD:
                $timeline_args['dashboardLegend'] = t("The list of students and projects is now visible to other users of the system. " .
                        "The Bonding period starts on the following date. ") .
                    $timeline->getCommunityBondingPeriodStart()->format('F j, Y, g:i a');
                break;
            case PRE_CODING_PERIOD:
                $timeline_args['dashboardLegend'] = t("Community bonding period.  Coding starts on the following date. ") .
                    $timeline->getCodingStartDate()->format('F j, Y, g:i a');
                break;
            case PRE_SUGGESTED_CODING_END_DATE:
                $timeline_args['dashboardLegend'] = t("Coding period. The following is the suggested end date for coding. ") .
                    $timeline->getSuggestedCodingDeadline()->format('F j, Y, g:i a');
                break;
            case PRE_CODING_DEADLINE:
                $timeline_args['dashboardLegend'] = t("Coding period. The following is the deadline date for coding. ") .
                    $timeline->getCodingEndDate()->format('F j, Y, g:i a');
                break;
            case OUT_OF_SEASON:
                $timeline_args['dashboardLegend'] = t("The program is currently out of season.");
                break;
            case PROGRAM_UNAVAILABLE:
            default:
                $timeline_args['dashboardLegend'] = t("No program currently available.");
                break;
        }

        return $timeline_args;
    }

    public static function getOrgadminTimelineVars(){
        list($period, $timeline_args, $timeline) = self::getInitialTimelineVars();

        //From here on the more direct approach
        if (_VALS_USE_TIME_SCHEDULE && $timeline->is_available_program) {
            $pre_org_signup = $period >= PRE_ORG_SIGNUP_PERIOD;
            $timeline_args['managedOrganisationsVisible'] = $pre_org_signup;
            $timeline_args['manageProjectIdeasVisible'] = $pre_org_signup;
            $timeline_args['browseProjectIdeasVisible'] = $pre_org_signup;
            $timeline_args['organisationMembersVisible'] = $pre_org_signup;
            $timeline_args['proposalsVisible'] = $period >= STUDENT_SIGNUP_PERIOD; //menu options - Proposals submitted to my Organisations
            $timeline_args['myAcceptedProjectsVisible'] = $period >= STUDENT_SIGNUP_PERIOD;
            $timeline_args['matchedProjectsVisible'] = $period >= PRE_BONDING_PERIOD; //- MENU OPTION - PROJECTS FOR myOrgs - these are the matched proper projects
        }

        //Most of the dashboard legend messages have moved to the initialisation function
        switch ($period) {
            case PROGRAM_NOT_YET_STARTED:
                break;
            case PRE_ORG_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Program is available, however you must wait until the following date to register your organisation/s. ") .
                    $timeline->getOrgsSignupStartDate()->format('F j, Y, g:i a');
                break;
            case ORG_SIGNUP_PERIOD:
                $timeline_args['dashboardLegend'] = t("Enter your organisation details and project ideas. You have until the following date when you can no longer add or delete entries. ") .
                    $timeline->getOrgsSignupEndDate()->format('F j, Y, g:i a');

                break;
            case PRE_ORGS_ANNOUNCED_PERIOD:
                $timeline_args['dashboardLegend'] = t("Modify your organisation details and project ideas. You have until the following date when your organisations and project " .
                        "ideas become visible to students. ") .
                    $timeline->getOrgsAnnouncedDate()->format('F j, Y, g:i a');
                break;
            case POST_ORGS_ANNOUNCED_PERIOD:
                $timeline_args['dashboardLegend'] = t("Your organisations and project ideas are now visible to other users of the system.");
                break;
            case STUDENT_SIGNUP_PERIOD:
                break;
            case PRE_ORGS_REVIEW_APPLICATIONS_DEADLINE:
                break;
            case PRE_PROPOSAL_MATCHED_DEADLINE:
                break;
            case PRE_STUDENTS_ANNOUNCED_DEADLINE:
                break;
            case PRE_BONDING_PERIOD:
                break;
            case PRE_CODING_PERIOD:
                break;
            case PRE_SUGGESTED_CODING_END_DATE:
                break;
            case PRE_CODING_DEADLINE:
                break;
            case OUT_OF_SEASON:
                break;
            case PROGRAM_UNAVAILABLE:
            default:

                break;
        }

        return $timeline_args;
    }

    function __destruct()
    {
        
    }

}
