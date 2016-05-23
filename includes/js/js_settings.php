<?php
define('DRUPAL_ROOT', realpath(getcwd().'/../../../../../..'));
include(DRUPAL_ROOT.'/initial.php');//Needed to derive the _WEB_URL which will be '' or '/vals'
$scheme = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https': 'http');
$base_url = $scheme. '://'.$_SERVER['HTTP_HOST']._WEB_URL; //This seems to be necessary to get to the user object: see
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);

echo "var base_url = '".$_SESSION['site_settings']['base_url']."/';\n";
echo "var module_url = base_url + 'sites/all/modules/vals_soc/';\n";
echo "var logged_in = ".($_SESSION['site_settings']['is_logged_in'] ? 1 :0).";\n";
echo "var debugging = ".($_SESSION['site_settings']['debug'] ? 'true' : 'false').";\n";
?>
var console_Jquery_migrate_warnings_silent = true;
var event_counter = 1;

/* TODO: we placed these functions here for the time being. Should have a separate file actually */

//Some settings for ajax calls
function getMouseXY(e) {
    if (document.all) { // grab the x-y pos.s if browser is IE
            tempX = event.clientX + document.body.scrollLeft;
            tempY = event.clientY + document.body.scrollTop;
    }
    else {  // grab the x-y pos.s if browser is NS

            tempX = e.pageX;
            tempY = e.pageY;
    }  
    if (tempX < 0){tempX = 0;}
    if (tempY < 0){tempY = 0;}  
    var arr = new Array(1);
    arr[0] = tempX;
    arr[1] = tempY;
    return arr;
}

function setStyleById(i, p, v) {
    var n = document.getElementById(i);
    n.style[p] = v;
}

function setStyleOnObject(o, p, v) {
    try {
            o.style[p] = v;
    } catch (err) {
            console.log('In setStyleOnObject '+ err);
    }
}

function startWait(event, counter, target){
    //arguments[0] = array(ajax_event, target, counter, action, xml_error_copy, tinyMceActive)

    var positions = getMouseXY(event);//get mouse position from event
    setWait(1, 'wait_'+counter, positions[0], positions[1], target);
}

function stopWait(counter){
    setWait(0, 'wait_'+counter);
}

function setWait(state, wait_name, x, y, target){
    if (state == 0){
            var waitobj = Obj(wait_name);
            if (waitobj) {
                    waitobj.remove();
            }
    }  else {
            waitingIcon(wait_name, x, y, target);
    }
}

function waitingIcon(wait_name, x, y, container){
    try {
            var obj = createDiv(wait_name, container, '', true);
            setStyleOnObject(obj, 'position', 'fixed');
            setStyleOnObject(obj, 'zIndex', 1002);
            setStyleOnObject(obj, 'top', (y - ajax_settings.ajax_waiting_half_width) +'px');
            setStyleOnObject(obj, 'left',(x - ajax_settings.ajax_waiting_half_height)  +'px');
            obj.innerHTML = ajax_settings.ajax_waiting_icon;
    } catch (err) {
            console.log('In waitingIcon ' + err);
    }
}

ajax_settings = {
        ajax_waiting_half_width:20,
        ajax_waiting_half_height:20,
        ajax_waiting_icon : "<img src='" + module_url+ "includes/js/resources/ajax-loader_old.gif' " +
            " width='20px'"  +
            " height='20px'"+
            "alt='waiting'></img>"
};


//Not appropriate at all, but here for testing the user form at the moment
function makeVisible(id){
    jQuery("#"+ id).removeClass('invisible');
}

function makeInvisible(id){
    jQuery("#"+ id).addClass('invisible');
}