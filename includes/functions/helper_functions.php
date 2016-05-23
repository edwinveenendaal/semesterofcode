<?php

/**
 * Returns the module version as defined in the .info file for this module
 * @return unknown
 */
function get_vals_version() {
    $info_data = system_get_info('module', 'vals_soc');
    return $info_data['version'];
}

function tableName($type) {
    return "soc_${type}s";
}

function debugDbQuery($query) {
    echo ($query->queryString);
}

function debugDbSelect($query) {
    echo(toSql($query));
}

function toSql(SelectQuery $obj) {

    $_string = $obj->__toString();
    $_conditions = $obj->arguments();
    $_tables = $obj->getTables();
    $_fields = $obj->getFields();

    foreach ($_tables as $k => $t) {
        if (!empty($t['alias'])) {
            $_string = str_replace('{' . $t['table'] . '}', $t['table'] . ' as', $_string);
        } else {
            $_string = str_replace('{' . $t['table'] . '}', $t['table'], $_string);
        }
    }

    foreach ($_conditions as $k => $v) {
        if (is_int($v)) {
            $_string = str_replace($k, $v, $_string);
        } else {
            $_string = str_replace($k, "'$v'", $_string);
        }
    }

    return $_string;
}

function startException($error_msg) {
    throw new Exception($error_msg);
}

function testInput($arr, $fields) {
    $test = TRUE;
    foreach ($fields as $f) {
        if (!isset($arr[$f])) {
            drupal_set_message(t('The input value @f is not set.', array('@f' => $f)), 'error');
            $test = FALSE;
        }
    }
    return $test;
}

function altValue($val, $default = '', $forbidden = '') {
    if ($val && (!isset($forbidden) || ($forbidden !== $val))) {
        return $val;
    } else {
        return $default;
    }
}

function altSubValue($arr, $field, $default = '') {
    if ($arr && isset($arr[$field])) {
        return $arr[$field];
    } else {
        return $default;
    }
}

function altPropertyValue($obj, $property, $default = '') {
    if (isset($obj->$property) && $obj->$property) {
        return $obj->$property;
    } else {
        return $default ? t($default) : '';
    }
}

function deriveTypeAndAction($derive_type = true) {
    $current_path = explode('/', $_SERVER['HTTP_REFERER']);
    $action = array_pop($current_path);
    $derived = array('show_action' => $action);
    if ($derive_type) {
        $type = array_pop($current_path);
        $derived['type'] = $type;
    }
    return $derived;
}

function getQueryString(SelectQueryInterface $query) {
    $string = (string) $query;
    $arguments = $query->arguments();

    if (!empty($arguments) && is_array($arguments)) {
        foreach ($arguments as $placeholder => &$value) {
            if (is_string($value)) {
                $value = "'$value'";
            }
        }

        $string = strtr($string, $arguments);
    }

    return $string;
}

function getRequestVar($field, $default = '', $request = 'all') {
    if ($request == 'post') {
        return altSubValue($_POST, $field, $default);
    } elseif ($request == 'get') {
        return altSubValue($_GET, $field, $default);
    } else {
        return (($return = altSubValue($_GET, $field, $default)) && ($return != $default)) ? $return : altSubValue($_POST, $field, $default);
    }
}

function pretendUser() {
    //the session_status function is only available from php 5.4 on. We just suppress the possible warning on 
    //double started sessions. In fact this is harmless and php will just start the session it already had (if this is 
    //the case, but still fires a warning. This function will and should nver be called in production, so it is not
    //an issue there.
    //if (session_status() == PHP_SESSION_NONE) {
    drupal_session_start();
    //}
    $user_id = getRequestVar('pretend', altSubValue($_SESSION, 'pretend_user', 0));
    if ($user_id) {
        if (!((isset($_SESSION['pretend_user']) &&
                ($_SESSION['pretend_user'] && ($_SESSION['pretend_user'] == $user_id))) ||
                verifyUser($user_id))) {
            return array(0, 0);
        }
        $same_pretend = ($_SESSION['pretend_user'] == $user_id);
        $_SESSION['pretend_user'] = $user_id;
        $original_user = $GLOBALS['user'];
        $old_state = drupal_save_session();
        //drupal_save_session(FALSE);
        if (isset($_SESSION['pretend_user_obj']) && $_SESSION['pretend_user_obj'] &&
                $same_pretend) {
            $GLOBALS['user'] = $_SESSION['pretend_user_obj'];
        } else {
            $GLOBALS['user'] = user_load($user_id);
            $GLOBALS['user']->roles = repairRoles($GLOBALS['user']->roles);
            $_SESSION['pretend_user_obj'] = $GLOBALS['user'];
        }
        return array($original_user, $old_state);
    } else {
        $_SESSION['pretend_user_obj'] = $_SESSION['pretend_user'] = 0;
        return array(0, 0);
    }
}

function repairRoles($user_load_roles) {
    if ($user_load_roles) {
        $rid = getUserRoleId($user_load_roles);
        $current_role = getUserRoleName('', '', $rid);
        return ($rid != _USER_ROLE_ID) ?
                array(_USER_ROLE_ID => _USER_TYPE, $rid => $current_role) :
                array(_USER_ROLE_ID => _USER_TYPE);
    } else {
        return array(_ANONYMOUS_ROLE_ID => _ANONYMOUS_TYPE);
    }
}

function restoreUser($u, $o_s) {
    if ($u) {
        $GLOABALS['user'] = $u;
        drupal_save_session($o_s);
    }
}

function verifyUser($user_id) {
    if ($user_id) {
        return Users::getParticipant($user_id);
    } else {
        $_SESSION['pretend_user'] = false;
        return false;
    }
}

//This function was necessary to remove the syntax error in vals_soc_mail_handler
/*
 * Gets a system variable with variable_get which is expected to be an array with
 * the field supplied as a key, if not: a default will be returned
 */

function variableGetFromStruct($var, $field, $default = '', $set = false) {
    $arr = variable_get($var);
    if ($arr && isset($arr[$field])) {
        return $arr[$field];
    } else {
        if ($default && $set) {
            variable_set($var, $default);
        }
        return $default;
    }
}

function src_getJs($src) {
    static $included_srcs = array();

    if (isset($included_srcs[$src])) {
        return "";
    } else {
        $included_srcs[$src] = TRUE;
        return "<script type='text/javascript' src='$src' class='file'></script>";
    }
}

function script_getJs($script) {
    return "<script type='text/javascript' class='direct'>$script</script>";
}

function errorDiv($msg) {
    $id = 'errorbox_' . rand(0, 100);
    $close_action = "onclick='\$jq(\"#$id\").html(\"\").removeClass(\"messages status\");'";
    return "<div id='$id' class='messages error'><span class='lefty'>$msg</span>" .
            "<span $close_action class='close_button'>X</span></div>";
}

function successDiv($msg) {
    $id = 'messagebox_' . rand(0, 100);
    $close_action = "onclick='\$jq(\"#$id\").html(\"\").removeClass(\"messages status\");'";
    return "<div id='$id' class='messages status'><span class='lefty'>$msg</span>" .
            "<span $close_action class='close_button'>X</span></div>";
}

/*
 * ffunction ajaxMessage(targ, msg) {
  if (msg){
  var err_target = $jq('#'+targ);
  var click = "onclick='$jq(\"#" +targ+"\").html(\"\").removeClass(\"messages status\");'";
  var msg2 = "<span class='lefty'>"+msg + "</span><span class='close_button' "+ click+ ">X</span>";
  //"<a href=javascript:void(0); "+ click+ ">X</a>";
  if (err_target.length){
  err_target.html(msg2);
  err_target.addClass('messages status');
  } else {
  alertdev('Target '+ targ+ ' for the message "'+msg+'" could not be found.');
  }
  }
  }
  }
 */

function tt($str) {
    $args = func_get_args();
    $args[0] = t($str);
    return call_user_func_array('sprintf', $args);
}

function t_type($type) {
    switch ($type) {
        case _INSTITUTE_GROUP: return t('institute');
            break;
        case _ORGANISATION_GROUP: return t('organisation');
            break;
        case _STUDENT_GROUP: return t('studentgroup');
            break;
        case _PROJECT_OBJ: return t('project');
            break;
        default: return $type;
    }
}

function objectToArray($o) {
    $a = array();
    foreach ($o as $k => $v)
        $a[$k] = (is_array($v) || is_object($v)) ? objectToArray($v) : $v;
    return $a;
}

function simpleObjectToArray($o) {
    return (array) $o;
}

function doAssocQuery($q) {
    return db_query($q)->fetchAllKeyed();
}

function doQuery2($q) {
    return array_map('simpleObjectToArray', db_query($q)->fetchAll());
}

function mapOrganisation($org) {
    switch ($org) {
        case _STUDENT_GROUP: return _STUDENT_GROUP;
            break;
        case _INSTITUTE_GROUP: return _SUPERVISOR_TYPE;
            break;
        case _ORGANISATION_GROUP: return _MENTOR_TYPE;
            break;
        default: return $org;
    }
}

function renderForm(&$form, $target, $return = false) {
    $s = "<div id='msg_$target'></div>";
    // Print $form
    $s .= drupal_render($form);
    $s .= valssoc_form_get_js($form);
    $form['#vals_soc_attached']['js'] = array();
    //Sometimes it seems Drupal uses the same struct to do a rebuild. We do not
    //want Drupal to interprete the attached js at the moment: the path is relative to the module and D does not 
    //know that
    if ($return)
        return $s;
    echo $s;
    return true;
}

function valssoc_form_get_js($form) {
    if ($form['#vals_soc_attached']['js']) {
        $js = '';
        foreach ($form['#vals_soc_attached']['js'] as $incl) {
            if ($incl['type'] == 'file') {
                $js .= src_getJs(_VALS_SOC_REL_URL . $incl['data']); //we assume all paths start with /
            } else {
                $js .= script_getJs($incl['data']);
            }
        }
        return $js;
    } else {
        return '';
    }
}

function createRandomCode($org, $id = 1) {
    $fixed_postfixes = array(
        _ADMINISTRATOR_TYPE => 1,
        _SOC_TYPE => 2,
        _ORGADMIN_TYPE => 3,
        _INSTADMIN_TYPE => 4,
        _SUPERVISOR_TYPE => 5,
        _STUDENT_TYPE => 6,
        _ORGANISATION_GROUP => 7,
        _INSTITUTE_GROUP => 8,
        _STUDENT_GROUP => 9,
        _MENTOR_TYPE => 0
    );
    $int = (int) $id . $fixed_postfixes[$org];
    return my_convert($int);
}

/* Taken from the Opendocument project
 * This function may be used under the Creative Commons license: by-nc-sa
 * Original written by E. Veenendaal, spring 2006
 */

//I have left out the forbidden words and set the desired length to 9, meaning it has max length 10.
//Note that every code produced is unique as long as the int passed in is unique
//The length adapts itself to the number with a length of 5 we already have 26^5 >= 11,000,000 elements to cover
//Note that if the full length of chars -say 5- is needed to code the int, we need an extra char in front telling
//the first char is not a padding indicator, but an included char. In that case we would have a 6-char string
//starting with an A. As an invariant we have that the 10 positions shifted result of base_convert can never start
//with an 'a' since base_convert returns a string with 0-9,[a-p] (so it never starts with 0, except for 0 itself).

function my_convert($int, $wishedlen = 9) {
    $map = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
        'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
        'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
        'Y', 'Z');
    $map2 = array_merge(array(
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'), $map);
    //base_convert uses int chars as well, So the mapping set is 0-9 + a-q, so 15 maps to f and not to p. We correct for
    //that so that we do not have integers in the resulting code
    $start = str_split(strtoupper(base_convert($int, 10, 26)));
    $string = '';
    foreach ($start as $c) {
        $pos = array_search($c, $map2);
        $string .= $map2[$pos + 10];
    }

    $len = count($start);
    if ($len < $wishedlen) {
        $pad = $wishedlen - $len - 1; //Leave one position as pad number indicator
        if ($pad > 0) {
            for ($i = 1; $i <= $pad; $i++) {
                $string = $map[rand(0, 25)] . $string;
            }
        }
        $string = $map[max($pad, 0)] . "$string";
    } elseif ($len == $wishedlen) {
        $string = "A$string";
    }

    return $string;
}
