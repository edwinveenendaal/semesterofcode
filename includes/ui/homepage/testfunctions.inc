<?php
drupal_add_library('system','ui.datepicker');
drupal_add_js(_VALS_SOC_PATH .'/includes/js/ajax.js');


function getTimelineTester()
{
    $today_datetime = Timeline::getInstance()->getNow();
    $today_ymd = $today_datetime->format('Y-m-d');
    echo '
                <div>
                <h1 id="dateNow">' . $today_datetime->format('F j, Y, g:i a') . '</h1>
                        <script>
                        jQuery(document).ready(function(){
                                $jq( "#datepicker" ).datepicker({
                                        dateFormat: "yy-mm-dd",
                                        defaultDate: "' . $today_ymd . '",
                                        onClose: function(dateText, inst){
                                                $jq.post( url("timeline","setdate"), { date: dateText+" 00:00:00" },
                                                        function(result) {
                                                                if(result){
                                                                        console.log("In timelinetester: " + result);
                                                                        $jq("#dateNow").html(result);
                                                                }
                                                });
                                        }
                                }).datepicker("setDate", "' . $today_ymd . '");
                        });
                        </script>
                        <p>System Date today: <input type="text" id="datepicker"></p>
                </div>
';
}

function getUserPretendTester(){
        $users = Users::getAllUsers()->fetchAll();
        $options = array(0 => 'No pretending');
        foreach($users as $user){
                if ($user->uid){
                        $options[$user->uid] = $user->name. ',  Name:'.$user->fullname. ", type:".$user->type;
                }
        }

        echo "
        <div>
        <h1 >Choose a user</h1>
        <form action='' method='post'>
        <select id='pretend' name='pretend'>";
        foreach ($options as $uid => $name){
                echo "<option value='$uid'>$name</option>";
        }

        echo "
        </select>
        <input type='submit' value='Pretend'>
        </form>
        </div>";
}
$debug = defined('_DEBUG') && _DEBUG;
$test_env = defined('_VALS_SOC_TEST_ENV') && _VALS_SOC_TEST_ENV;
if ($debug && $test_env){
        getTimelineTester();
}
if (($debug || (isset($GLOBALS['ip_condition']) &&
                $GLOBALS['ip_condition'])) &&
    isset($GLOBALS['vals_soc_pretend_possible']) &&
        $GLOBALS['vals_soc_pretend_possible']){
        getUserPretendTester();
}
