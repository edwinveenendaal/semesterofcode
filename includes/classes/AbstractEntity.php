<?php

/**
 * Common abstract base class for all entities requiring the same functionality
 * 
 * @author paul
 *
 */
abstract class AbstractEntity {

    static function keyField($type) {
        switch ($type) {
            case _STUDENT_GROUP: return 'studentgroup_id';
                break;
            case _INSTITUTE_GROUP: return 'inst_id';
                break;
            case _ORGANISATION_GROUP: return 'org_id';
                break;
            case _PROJECT_OBJ: return 'pid';
                break;
            case _PROPOSAL_OBJ: return 'proposal_id';
                break;
            case _AGREEMENT_OBJ: return 'agreement_id';
                break;
            default: return '';
        }
    }

    static function participationGroup($type) {
        switch ($type) {
            case _ORGADMIN_TYPE:
            case _MENTOR_TYPE: $group = _ORGANISATION_GROUP;
                break;
            case _INSTADMIN_TYPE:
            case _SUPERVISOR_TYPE:
            case _STUDENT_TYPE: $group = _INSTITUTE_GROUP;
                break;
            default: $group = '';
        }
        return $group;
    }

    static function filterPostLite($fieldz) {
        $input = array();
        foreach ($fieldz as $prop) {
            if (isset($_POST[$prop])) {
                $input[$prop] = $_POST[$prop];
            }
        }
        return $input;
    }

}
