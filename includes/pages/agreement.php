<?php
include_once(_VALS_SOC_ROOT . '/includes/functions/tab_functions.php'); //it is sometimes included after propjects.php which does the same

function showAgreement($agreement = '') {

    if (!$agreement) {
        $agreement = Agreement::getInstance()->getSingleStudentsAgreement(true);
    }
    $nr = 1;
    $tab_id_prefix = 'agree_page-';
    $data = array();
    $tabs = array("'${tab_id_prefix}1'");

    //we pass on the buttons=0 since we have the buttons as tabs
    $data[] = array(2, 'Agreement', 'view', _AGREEMENT_OBJ, $agreement->agreement_id, "buttons=0");

    $next_tab = 2;
    //[number of tabs, label start, tab id start, type, data, id, render targets, active target content, active tab]
    echo renderTabs($nr, '', $tab_id_prefix, _AGREEMENT_OBJ, $data, $agreement->agreement_id, TRUE, renderAgreement(_AGREEMENT_OBJ, $agreement, null, "${tab_id_prefix}1", false), 1, _AGREEMENT_OBJ);
    ?>
    <script type="text/javascript">
        activatetabs('tab_', [<?php echo implode(',', $tabs); ?>]);
    </script>
    <?php
}

function showFinalisation($agreement = '') {

    if (!$agreement) {
        $agreement = Agreement::getInstance()->getSingleStudentsAgreement(true);
    }
    $nr = 1;
    $tab_id_prefix = 'final_page-';
    $data = array();
    $tabs = array("'${tab_id_prefix}1'");

    //we pass on the buttons=0 since we have the buttons as tabs
    $data[] = array(2, 'Finalisation', 'finalisation_view', _AGREEMENT_OBJ, $agreement->agreement_id, "buttons=0");

    $next_tab = 2;
    //[number of tabs, label start, tab id start, type, data, id, render targets, active target content, active tab]
    echo renderTabs($nr, '', $tab_id_prefix, _AGREEMENT_OBJ, $data, $agreement->agreement_id, TRUE, renderFinalisation(_AGREEMENT_OBJ, $agreement, null, "${tab_id_prefix}1", false), 1, _AGREEMENT_OBJ);
    ?>
    <script type="text/javascript">
        activatetabs('tab_', [<?php echo implode(',', $tabs); ?>]);
    </script>
    <?php
}
