<?php

class Agreement extends AbstractEntity {

    public static $fields = array('agreement_id', 'student_id', 'supervisor_id',
        'mentor_id', 'proposal_id', 'project_id', 'description',
        'student_signed', 'supervisor_signed', 'mentor_signed',
        'student_completed', 'supervisor_completed', 'mentor_completed', 'evaluation');
    private static $instance;
    public static $type = _AGREEMENT_OBJ;

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self ();
        }
        return self::$instance;
    }

    public function getKeylessFields() {
        // we dont want to return the key fields here
        return array_slice(Agreement::$fields, 1);
    }

    /**
     * The student inserts this initially once he/she accepts a project offer
     * @param unknown $props
     * @param unknown $proposal_id
     * @return boolean|unknown
     */
    static function insertAgreement($props) {
        if (!$props) {
            drupal_set_message(t('Insert requested with empty (filtered) data set'), 'error');
            return false;
        }
        if (!isset($props['proposal_id'])) {
            drupal_set_message(t('Insert requested with no proposal set'), 'error');
            return false;
        }

        global $user;

        $txn = db_transaction();
        try {
            $proposal = objectToArray(Proposal::getInstance()->getProposalById($props['proposal_id']));
            $project = objectToArray(Project::getProjectById($proposal['pid']));
            if (!isset($props['student_id'])) {
                $props['student_id'] = $user->uid;
            }
            if (!isset($props['supervisor_id'])) {
                $props['supervisor_id'] = $proposal['supervisor_id'];
            }
            if (!isset($props['mentor_id'])) {
                $props['mentor_id'] = $project['mentor_id'];
            }


            $props['project_id'] = $proposal['pid'];

            if (!isset($props['description'])) {
                $props['description'] = '';
            }
            if (!isset($props['student_signed'])) {
                $props['student_signed'] = 0;
            }
            if (!isset($props['supervisor_signed'])) {
                $props['supervisor_signed'] = 0;
            }
            if (!isset($props['mentor_signed'])) {
                $props['mentor_signed'] = 0;
            }
            if (!isset($props['student_completed'])) {
                $props['student_completed'] = 0;
            }
            if (!isset($props['supervisor_completed'])) {
                $props['supervisor_completed'] = 0;
            }
            if (!isset($props['mentor_completed'])) {
                $props['mentor_completed'] = 0;
            }
            /*
              if (! testInput($props, array('owner_id', 'org_id', 'inst_id', 'supervisor_id','pid', 'title'))){
              return FALSE;
              }
             */
            try {
                $id = db_insert(tableName(_AGREEMENT_OBJ))->fields($props)->execute();
            } catch (Exception $e) {
                drupal_set_message($e->getMessage(), 'error');
            }
            if ($id) {
                drupal_set_message(t('You have created your agreement: you can continue editing it later.'));
                return $id;
            } else {
                drupal_set_message(t('We could not add your agreement. ') .
                        (_DEBUG ? ('<br/>' . getDrupalMessages()) : ""), 'error');
            }

            return $result;
        } catch (Exception $ex) {
            $txn->rollback();
            drupal_set_message(t('We could not add your agreement.') . (_DEBUG ? $ex->__toString() : ''), 'error');
        }
        return FALSE;
    }

    static public function updateAgreement($props) {
        if (!$props) {
            drupal_set_message(t('Update requested with empty (filtered) data set'), 'error');
            return false;
        }
        $txn = db_transaction();
        try {
            $id = db_update(tableName(_AGREEMENT_OBJ))->fields($props)
                            ->condition(self::keyField(_AGREEMENT_OBJ), $props['agreement_id'])->execute();
            if ($props['student_signed'] && $props['supervisor_signed'] && $props['mentor_signed']) {
                $res = db_update(tableName(_PROJECT_OBJ))->fields(array('state' => 'active'))
                                ->condition(self::keyField(_PROJECT_OBJ), $props['project_id'])->execute();
//Seems to be done elsewhere
//                $res = db_update(tableName(_PROPOSAL_OBJ))->fields(array('state'=>'accepted'))
//				->condition(self::keyField(_PROPOSAL_OBJ), $props['proposal_id'])->execute();
            }

            //Verzend email
            if ($props['student_completed'] || $props['supervisor_completed'] || $props['mentor_completed']) {
                //TODO: send mail here
                module_load_include('php', 'vals_soc', '/includes/classes/Organisations');
                module_load_include('php', 'vals_soc', '/includes/classes/Institutes');
                module_load_include('php', 'vals_soc', '/includes/classes/Proposal');

                $agreement = self::getSingleAgreementById($props['agreement_id']);
                $proposal = Proposal::getProposalById($agreement->proposal_id, true);
                module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail');
                notify_all_of_project_finalisation($proposal, getRole());
                if ($props['student_completed'] && $props['supervisor_completed'] && $props['mentor_completed']) {
                    $res = db_update(tableName(_PROJECT_OBJ))->fields(array('state' => 'finished'))
                                    ->condition(self::keyField(_PROJECT_OBJ), $props['project_id'])->execute();
                    $res = db_update(tableName(_PROPOSAL_OBJ))->fields(array('state' => 'finished'))
                                    ->condition(self::keyField(_PROPOSAL_OBJ), $props['proposal_id'])->execute();
                }
            }
            return TRUE;
        } catch (Exception $ex) {
            $txn->rollback();
            drupal_set_message(t('We could not update the agreement.') . (_DEBUG ? $ex->__toString() : ''), 'error');
        }
        return FALSE;
    }

    static public function getSingleAgreementById($agreement_id, $details = false) {
        return self::getProjectAgreements($agreement_id, '', '', '', '', $details, '', 0, 1000)->fetchObject();
    }

    static public function getSingleStudentsAgreement($details = false) {
        return self::getProjectAgreements('', '', $GLOBALS['user']->uid, '', '', $details, '', 0, 1000)->fetchObject();
    }

    static public function getAgreementsForMentorBySearchCriteria($details = false, $sorting = 'a.agreement_id', $startIndex = 1, $pageSize = 10) {
        return self::getProjectAgreements('', '', '', '', $GLOBALS['user']->uid, $details, $sorting, $startIndex, $pageSize)->fetchAll();
    }

    static public function getAgreementsForSupervisorBySearchCriteria($details = false, $sorting = 'a.agreement_id', $startIndex = 1, $pageSize = 10) {
        return self::getProjectAgreements('', '', '', $GLOBALS['user']->uid, '', $details, $sorting, $startIndex, $pageSize)->fetchAll();
    }

    static public function getProjectAgreements($agreement_id = '', $project_id = '', $student_id = '', $supervisor_id = '', $mentor_id = '', $details = false, $sorting = 'a.agreement_id', $startIndex = 1, $pageSize = 10) {
        $query = db_select('soc_agreements', 'a')->fields('a', self::$fields);

        if ($agreement_id) {
            $query->condition('a.agreement_id', $agreement_id);
        }
        if ($project_id) {
            $query->condition('a.project_id', $project_id);
        }
        if ($student_id) {
            $query->condition('a.student_id', $student_id);
        }
        if ($supervisor_id) {
            $query->condition('a.supervisor_id', $supervisor_id);
        }
        if ($mentor_id) {
            $query->condition('a.mentor_id', $mentor_id);
        }

        if ($details) {// details gets the mentor, supervisor and student names & email addresses also
            $query->leftjoin('users', 'student_user', 'a.student_id = %alias.uid');
            $query->leftjoin('soc_names', 'student', 'a.student_id = %alias.names_uid');

            $query->leftjoin('users', 'mentor_user', 'a.mentor_id = %alias.uid');
            $query->leftjoin('soc_names', 'mentor', 'a.mentor_id = %alias.names_uid');

            $query->leftjoin('users', 'supervisor_user', 'a.supervisor_id = %alias.uid');
            $query->leftjoin('soc_names', 'supervisor', 'a.supervisor_id = %alias.names_uid');

            $query->leftjoin('soc_projects', 'pr', 'a.project_id = %alias.pid');

            $query->fields('student_user', array('mail', 'name'));
            $query->fields('student', array('name'));

            $query->fields('mentor_user', array('mail', 'name'));
            $query->fields('mentor', array('name'));

            $query->fields('supervisor_user', array('mail', 'name'));
            $query->fields('supervisor', array('name'));
            $query->fields('pr', array('title'));
        }
        if ($sorting) {
            $parts = explode(' ', $sorting);
            $sorting = $parts[0];
            $direction = (isset($parts[1]) ? $parts[1] : 'DESC');
            $query->orderBy($sorting, $direction);
        }
        $query->range($startIndex, $pageSize);
        try {
            $result = $query->execute();
        } catch (Exception $ex) {
            if (_DEBUG) {
                print_r($ex);
            }
        }
        return $result;
    }

    static public function getProjectAgreementsRowCount($supervisor_id = '', $mentor_id = '') {

        $query = db_select('soc_agreements', 'a')->fields('a', self::$fields);

        if ($supervisor_id) {
            $query->condition('a.supervisor_id', $supervisor_id);
        }
        if ($mentor_id) {
            $query->condition('a.mentor_id', $mentor_id);
        }

        return $query->execute()->rowCount();
    }

}
