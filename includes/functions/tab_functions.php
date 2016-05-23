<?php
/* Expects data for every tab:
 * [translate, label, action, type, id, extra GET arguments, render with rich text area, render tab to the right]
 * translate can be: 0=label with sequence nr, 1=translate the text, 2=use the name etc as label (untranslated)
 */

function renderTabs($count, $tab_label, $target_label, $type, $data, $id = 0, $render_targets = false, $active_content = '', $active_tab = 1, $parent_type = 'administration') {
    ?>
    <ol id="toc"><?php
        $label_start = t($tab_label);
        $title = "";
        $label_nr = 1;
        for ($t = 0; $t < $count; $t++) {
            $target = $target_label . ($t + 1);
            $tab_class = '';
            $delete_tab = false;
            $pre = "";
            $post = "";
            if (isset($data[$t][7]) && $data[$t][7]) {
                $tab_class = ' class="right"';
                if ($data[$t][7] == 'delete') {
                    $delete_tab = true;
                    $pre = "if (confirm('" . tt('Are you sure you want to delete this %1$s', t_type($type)) . "')){";
                    $post = "} else { Obj('$target').html('" . t('You canceled the delete request') . "')}";
                }
            }
            $pre .= "Obj('$target').html('" . t('Loading, please wait...') . "');";
            ?>
            <li<?php echo $tab_class; ?>><a href="#tab_<?php echo $target; ?>" <?php
                //title, the tab is either: translated (arg=1), a numbered prefix (arg=0) or left as it is (arg <> 0,1
                if ($data[$t][0] == 0) {//labels: 0
                    $link_text = "$label_start $label_nr";
                    $label_nr++;
                    $title = " title = '" . $data[$t][1] . "' ";
                } elseif ($data[$t][0] == 1) {//translate 1
                    $link_text = t($data[$t][1]);
                    $title = "";
                } else {//unmodified 2
                    $link_text = $data[$t][1];
                    $title = "";
                }
                echo $title;

                //onclick action
                if (isset($data[$t][2])) {
                    $action = $data[$t][2];
                    $type = isset($data[$t][3]) ? $data[$t][3] : $type;
                    $id = isset($data[$t][4]) ? $data[$t][4] : $id;
                    //add extra get arguments
                    if (isset($data[$t][5])) {
                        $action .= "&" . $data[$t][5];
                    }
                    $call_target = "'$target'";
                    echo "onclick=\"${pre}ajaxCall('$parent_type', '$action', {type:'$type', id:$id, target:'$target'},";
                    if (isset($data[$t][6]) && $data[$t][6]) {
                        $call_target = " 'formResult', 'html', ['$target', '$parent_type']";
                    } elseif ($delete_tab) {
                        $call_target = " 'handleDeleteResult', 'json', ['admin_container', '$parent_type']";
                    }
                    echo " $call_target);$post\"";
                }
                ?>><span><?php echo $link_text; ?></span></a>
            </li>
            <?php }
        ?>
    </ol><?php
    if ($render_targets) {
        for ($i = 1; $i <= $count; $i++) {
            echo "<div id='$target_label$i' class='content'>" .
            "<div id='msg_$target_label$i'></div>" .
            (($i == $active_tab) ? $active_content : '') . "</div>";
        }
    }
}
