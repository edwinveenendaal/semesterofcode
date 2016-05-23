<?php
include_once(_VALS_SOC_ROOT . '/includes/functions/tab_functions.php'); //it is sometimes included after administration.php which does the same
include_once(_VALS_SOC_ROOT . '/includes/functions/render_functions.php'); //it is sometimes included after administration.php which does the same

function showMyProposalPage() {
    $role = getRole();
    if (!Users::isStudent()) {
        echo t('You can only see this page as a student');
        return;
    }
    //Get my groups
    $my_proposals = Proposal::getInstance()->getMyProposals(); //::getGroups(_ORGANISATION_GROUP);
    if (!$my_proposals) {
        echo t('You have no proposal at the moment.') . '<br/>';
        echo "<a href='" . _WEB_URL . "/projects/browse'>" . t('Please find yourself a project') . "</a>.";
    } else {
        $current_tab = getRequestVar('new_tab', 0);
        showMyProposals($my_proposals, $current_tab);
    }
}

function showMyProposals($proposals, $current_tab_propid = 0) {
    $nr = 0;
    $apply_projects = $apply_projects = vals_soc_access_check('dashboard/projects/apply') ? 1 : 0;
    $rate_projects = Users::isSuperVisor();
    $tab_id_prefix = "proposal_page";
    $data = array();
    $activating_tabs = array();
    $current_tab = 1;
    $current_tab_id = "$tab_id_prefix$current_tab";
    $current_tab_content = '';

    foreach ($proposals as $proposal) {
        $nr++;
        if (((!$current_tab_propid) && $nr == 1) || ($proposal->proposal_id == $current_tab_propid)) {
            //$id = $proposal->pid;
            $current_tab = $nr;
            $current_tab_id = "$tab_id_prefix$current_tab";
            $current_tab_content = renderProposal(Proposal::getInstance()->getProposalById(
                            $proposal->proposal_id, TRUE), $current_tab_id, 'myproposal_page');
        }
        $activating_tabs[] = "'$tab_id_prefix$nr'";
        $data[] = array(0, $proposal->title, 'view', _PROPOSAL_OBJ, $proposal->proposal_id);
    }

    echo renderTabs($nr, 'Proposal', $tab_id_prefix, _PROPOSAL_OBJ, $data, 0, TRUE, $current_tab_content, $current_tab, _PROPOSAL_OBJ);
    ?>
    <script type="text/javascript">
        window.view_settings = {};
        window.view_settings.apply_projects = <?php echo $apply_projects ? 1 : 0; ?>;
        window.view_settings.rate_projects = <?php echo $rate_projects ? 1 : 0; ?>;
        activatetabs('tab_', [<?php echo implode(', ', $activating_tabs); ?>], '<?php echo $current_tab_id; ?>');
    </script>
    <?php
}

function initBrowseProposalsLayout() {
    $org_id = 0;
    $apply_projects = vals_soc_access_check('dashboard/projects/apply') ? 1 : 0;
    $rate_projects = Users::isSuperVisor();
    $browse_proposals = vals_soc_access_check('dashboard/proposals/browse') ? 1 : 0;
    $proposal_tabs = array();
    if (isset($_GET['organisation'])) {
        $org_id = $_GET['organisation'];
    }
    if ($apply_projects && !$browse_proposals) {
        //A student may only browse their own proposals
        $student_id = $GLOBALS['user']->uid;
        $student = Users::getStudentDetails($student_id);
        $inst_id = $student->inst_id;
        $student_section_class = 'invisible';
    } else {
        $student_section_class = '';
        $student_id = 0;
        if (isset($_GET[_STUDENT_TYPE])) {
            $student_id = $_GET[_STUDENT_TYPE];
        }
        $inst_id = 0;
        if (isset($_GET['institute'])) {
            $inst_id = $_GET['institute'];
        }
    }
    ?>
    <div class="filtering" style="width: 800px;">
        <span id="infotext" style="margin-left: 34px"></span>
        <form id="proposal_filter">
            <?php echo t('Select the proposals'); ?>:
            <?php // echo t('Organisations');?>
            <select id="organisation" name="organisation">
                <option <?php echo (!$org_id) ? 'selected="selected"' : ''; ?>
                    value="0"><?php echo t('All Organisations'); ?></option><?php
        $result = Organisations::getInstance()->getOrganisationsLite();
        foreach ($result as $record) {
            $selected = ($record->org_id == $org_id ? 'selected="selected" ' : '');
            echo '<option ' . $selected . 'value="' . $record->org_id . '">' . $record->name . '</option>';
        }
            ?>
            </select>
            <span id='student_section'
                  class='<?php echo $student_section_class; ?>'> <select id="institute"
                                                                  name="institute">
                    <option <?php echo (!$inst_id) ? 'selected="selected"' : ''; ?>
                        value="0"><?php echo t('All Institutes'); ?></option><?php
            $result = Groups::getGroups(_INSTITUTE_GROUP, 'all');
            foreach ($result as $record) {
                $selected = ($record->inst_id == $inst_id ? 'selected="selected" ' : '');
                echo '<option ' . $selected . 'value="' . $record->inst_id . '">' . $record->name . '</option>';
            }
            ?>
                </select> <select id="student" name="student">
                    <option <?php echo (!$student_id) ? 'selected="selected"' : ''; ?>
                        value="0"><?php echo t('All Students'); ?></option><?php
                $result = Users::getUsers(_STUDENT_TYPE, ($inst_id ? _INSTITUTE_GROUP : 'all'), $inst_id);
                foreach ($result as $record) {
                    $selected = ($record->uid == $student_id ? 'selected="selected" ' : '');
                    echo '<option ' . $selected . 'value="' . $record->uid . '">' . $record->name . ':' . $record->mail . '</option>';
                }
            ?>
                </select>
            </span>
        </form>
    </div>
    <div id="TableContainer" style="width: 800px;"></div>
    <script type="text/javascript">

        jQuery(document).ready(function ($) {

            //We make the ajax script path absolute as the language module might add a language code
            //to the path
            window.view_settings = {};
            window.view_settings.apply_projects = <?php echo $apply_projects ? 1 : 0; ?>;
            window.view_settings.rate_projects = <?php echo $rate_projects ? 1 : 0; ?>;

            function loadFilteredProposals() {
                $("#TableContainer").jtable("load", {
                    student: $("#student").val(),
                    organisation: $("#organisation").val(),
                    institute: $("#institute").val()
                });
            }

            //Prepare jTable
            $("#TableContainer").jtable({
                //title: "Table of proposals",
                paging: true,
                pageSize: 10,
                sorting: true,
                defaultSorting: "pid ASC",
                actions: {
                    listAction: module_url + "actions/proposal_actions.php?action=list_proposals"
                },
                fields: {
                    proposal_id: {
                        key: true,
                        create: false,
                        edit: false,
                        list: false
                    },
                    pid: {
                        width: "2%",
                        title: "Project",
                        sorting: true,
                        display: function (data) {
                            return "<a title=\"View project details\" href=\"javascript:void(0);\" onclick=\"getProjectDetail(" + data.record.pid + ");\">" +
                                    "<span class=\"ui-icon ui-icon-info\"></span></a>";
                        },
                        create: false,
                        edit: false
                    },
                    owner_id: {
                        title: "Student",
                        width: "30%",
                        display: function (data) {
                            if (data.record.name) {
                                return data.record.name;
                            } else {
                                return data.record.u_name;
                            }
                        }
                    },
                    inst_id: {
                        title: "Institute",
                        width: "26%",
                        create: false,
                        edit: false,
                        display: function (data) {
                            return data.record.i_name;
                        }
                    },
                    org_id: {
                        title: "Organisation",
                        width: "20%",
                        display: function (data) {
                            return data.record.o_name;
                        }
                    },
                    solution_short: {//the key of the object is misused to show this column
                        //width: "2%",
                        title: "Proposal details",
                        sorting: false,
                        display: function (data) {
                            return "<a title=\"See this Proposal\" href=\"javascript:void(0);\" " +
                                    "onclick=\"getProposalDetail(" + data.record.proposal_id + ")\">" +
                                    "<span class=\"ui-icon ui-icon-info\">See details</span></a>";
                        },
                        create: false,
                        edit: false
                    },
                },
            });

            //Load proposal list from server on initial page load
            loadFilteredProposals();

            $("#organisation").change(function (e) {
                e.preventDefault();
                loadFilteredProposals();
            });

            $("#institute").change(function (e) {
                e.preventDefault();
                loadFilteredProposals();
            });

            $("#student").change(function (e) {
                e.preventDefault();
                loadFilteredProposals();
            });

            $("#proposal_filter").submit(function (e) {
                e.preventDefault();
                loadFilteredProposals()
            });

            // define these at the window level so that they can still be called once loaded in the modal
            //window.getProposalFormForProject = getProposalFormForProject;
            //window.getProjectDetail = getProjectDetail;
            //window.getProposalDetail = getProposalDetail;

        });
    </script><?php
}

/* * *********************************************************
 * IN PROGRESS - NEW PROPOSAL VIEWS
 * ******************************************************* */

function initBrowseProposalsByTypeLayout($owner_only = false) {

    $only_mine_query = (bool) $owner_only ? '&mine_only=true' : '';
    $only_mine_js = (bool) $owner_only ? 'true' : 'false';
    // ORG ADMIN & MENTOR VIEWS
    if (hasRole(array(_ORGADMIN_TYPE)) || hasRole(array(_MENTOR_TYPE))) {
        $org_id = 0;
        if (isset($_GET['organisation'])) {
            $org_id = $_GET['organisation'];
        }

        echo "<a href='" . _WEB_URL . "/dashboard/proposals/browsebytype'>" . t('Overview for all projects of my organisation(s)') . "</a>";
        echo " | ";
        echo "<a href='" . _WEB_URL . "/dashboard/proposals/browsebytype/mine'>" . t('Overview for my projects only') . "</a>";
        ?>
        <div class="filtering" style="width: 800px;">
            <span id="infotext" style="margin-left: 34px"></span>
            <form id="proposal_filter">
                <?php echo t('Filter by Organisation'); ?>:
                <?php // echo t('Organisations');?>
                <select id="organisation" name="organisation">
                    <option <?php echo (!$org_id) ? 'selected="selected"' : ''; ?>
                        value="0"><?php echo t('All My Organisations'); ?></option><?php
        $result = Organisations::getInstance()->getMyOrganisations(TRUE);
        foreach ($result as $record) {
            $selected = ($record->org_id == $org_id ? 'selected="selected" ' : '');
            echo '<option ' . $selected . 'value="' . $record->org_id . '">' . $record->name . '</option>';
        }
                ?>
                </select>
            </form>
        </div>
        <div id="TableContainer" style="width: 800px;"></div>
        <script type="text/javascript">

            jQuery(document).ready(function ($) {
                window.view_settings = {};

                function loadFilteredProposals() {
                    $("#TableContainer").jtable("load", {
                        organisation: $("#organisation").val(),
                    });
                }

                //Prepare jTable
                $("#TableContainer").jtable({
                    paging: true,
                    pageSize: 10,
                    sorting: true,
                    defaultSorting: "pid ASC",
                    actions: {
                        listAction: module_url + "actions/project_actions.php?action=list_search_proposal_count<?php echo $only_mine_query; ?>"
                    },
                    fields: {
                        pid: {
                            key: true,
                            create: false,
                            edit: false,
                            list: false
                        },
                        title: {
                            title: "Project",
                            width: "49%",
                            display: function (data) {
                                return "<a title=\"View project details\" href=\"javascript:void(0);\" onclick=\"getProjectDetail(" + data.record.pid + ")\">"
                                        + data.record.title + "</a>";
                            },
                        },
                        org_name: {
                            title: "Organisation",
                            width: "35%",
                            display: function (data) {
                                return data.record.org_name;
                            }
                        },
                        proposal_count: {
                            title: "Number",
                            width: "10%",
                            display: function (data) {
                                return data.record.proposal_count;
                            }
                        },
                        proposal_view: {
                            width: "6%",
                            title: "List",
                            sorting: false,
                            display: function (data) {
                                if (data.record.proposal_count > 0) {
                                    return "<a title=\"View Proposals\" href=\"javascript:void(0);\" " +
                                            "onclick=\"getProposalsForProject(" + data.record.pid + ",<?php echo $only_mine_js; ?>)\">" +
                                            "<span class=\"ui-icon ui-icon-info\">See detail</span></a>";
                                } else {
                                    return "<span>N/A</span>";
                                }
                            },
                            create: false,
                            edit: false
                        },
                    },
                });

                //Load proposal list from server on initial page load
                loadFilteredProposals();

                $("#organisation").change(function (e) {
                    e.preventDefault();
                    loadFilteredProposals();
                });

                $("#proposal_filter").submit(function (e) {
                    e.preventDefault();
                    loadFilteredProposals()
                });

            });
        </script><?php
    } else if (hasRole(array(_INSTADMIN_TYPE)) || hasRole(array(_SUPERVISOR_TYPE))) {

        $studentgroup_id = 0;

        if (isset($_GET['group'])) {
            $studentgroup_id = $_GET['group'];
        }
        echo "<a href='" . _WEB_URL . "/dashboard/proposals/browsebytype'>" . t('Show all proposals from my Institution') . "</a>";
        echo " | ";
        echo "<a href='" . _WEB_URL . "/dashboard/proposals/browsebytype/mine'>" . t('Show only mine') . "</a>";
        ?>
        <div class="filtering" style="width: 800px;">
            <span id="infotext" style="margin-left: 34px"></span>
            <form id="proposal_filter">
                <?php echo t('Filter by Group'); ?>:
                <?php
                // echo t('Organisations');
                $option_text = (bool) $owner_only ? t('All My Groups') : t('All Groups from my Institution');

                if ($owner_only) {
                    $result = Groups::getGroups(_STUDENT_GROUP, $GLOBALS ['user']->uid);
                } else {
                    $institutes = Users::getInstituteForUser($GLOBALS ['user']->uid);
                    if ($institutes->rowCount() > 0) {
                        $result = Groups::getGroups(_STUDENT_GROUP, 'all', $institutes->fetchObject()->inst_id);
                    } else {
                        // give up, just get their own
                        $result = Groups::getGroups(_STUDENT_GROUP, $GLOBALS ['user']->uid);
                    }
                }
                ?>
                <select id="group" name="group">
                    <option
                    <?php echo (!$studentgroup_id) ? 'selected="selected"' : ''; ?>
                        value="0"><?php echo $option_text; ?></option><?php
                        foreach ($result as $record) {
                            $selected = ($record->studentgroup_id == $studentgroup_id ? 'selected="selected" ' : '');
                            echo '<option ' . $selected . 'value="' . $record->studentgroup_id . '">' . $record->name . '</option>';
                        }
                        ?>
                </select>
            </form>
        </div>
        <div id="TableContainer" style="width: 800px;"></div>
        <script type="text/javascript">

            jQuery(document).ready(function ($) {
                window.view_settings = {};

                function loadFilteredProposals() {
                    $("#TableContainer").jtable("load", {
                        group: $("#group").val(),
                    });
                }

                //Prepare jTable
                $("#TableContainer").jtable({
                    paging: true,
                    pageSize: 10,
                    sorting: true,
                    defaultSorting: "pid ASC",
                    actions: {
                        listAction: module_url + "actions/institute_actions.php?action=list_search_proposal_count_student<?php echo $only_mine_query; ?>"
                    },
                    fields: {
                        uid: {
                            key: true,
                            create: false,
                            edit: false,
                            list: false
                        },
                        username: {
                            title: "Student",
                            width: "42%",
                            display: function (data) {
                                return  data.record.username;
                            },
                        },
                        groupname: {
                            title: "Group name",
                            width: "42%",
                            display: function (data) {
                                return data.record.groupname;
                            }
                        },
                        proposal_count: {
                            title: "Proposals",
                            width: "10%",
                            display: function (data) {
                                return data.record.proposal_count;
                            }
                        },
                        proposal_view: {
                            width: "6%",
                            title: "View",
                            sorting: false,
                            display: function (data) {
                                if (data.record.proposal_count > 0) {
                                    return "<a title=\"View Proposals\" href=\"javascript:void(0);\" " +
                                            "onclick=\"getProposalsForStudent(" + data.record.uid + ",<?php echo $only_mine_js; ?>)\">" +
                                            "<span class=\"ui-icon ui-icon-info\">See detail</span></a>";
                                }
                            },
                            create: false,
                            edit: false
                        },
                    },
                });

                //Load proposal list from server on initial page load
                loadFilteredProposals();

                $("#group").change(function (e) {
                    e.preventDefault();
                    loadFilteredProposals();
                });

                $("#proposal_filter").submit(function (e) {
                    e.preventDefault();
                    loadFilteredProposals()
                });

            });
        </script><?php
    } else {
        
    }
}

function showProposalsForProject($project_id, $show_only_mine) {
    global $base_url;

    $url_type = (bool) $show_only_mine ? '/mine' : '';
    echo '<div id="baktoprops"><a href=" ' . $base_url . '/dashboard/proposals/browsebytype' . $url_type . '">' . t('Back to proposals overview') . '</a></div>';
    $project = Project::getInstance()->getProjectById($project_id);
    echo '<h2>' . t('Proposals for project idea \'' . $project['title']) . '\'</h2>';
    ?>
    <div id="TableContainer" style="width: 800px;"></div>
    <script type="text/javascript">

        jQuery(document).ready(function ($) {
            window.view_settings = {};

            function loadFilteredProposals() {
                $("#TableContainer").jtable("load", {
                    project: <?php echo $project_id ?>
                });
            }

            //Prepare jTable
            $("#TableContainer").jtable({
                paging: true,
                pageSize: 10,
                sorting: true,
                defaultSorting: "pid ASC",
                actions: {
                    listAction: module_url + "actions/proposal_actions.php?action=list_proposals"
                },
                fields: {
                    proposal_id: {
                        key: true,
                        create: false,
                        edit: false,
                        list: false
                    },
                    owner_id: {
                        title: "Student",
                        width: "30%",
                        display: function (data) {
                            var uname_text = '';
                            if (data.record.name) {
                                uname_text = data.record.name;
                            } else {
                                uname_text = data.record.u_name;
                            }
                            if (data.record.proposal_id == data.record.pr_proposal_id && data.record.selected == 0) {
                                uname_text += "&nbsp;<span style=\"float:left;display:inline;\" class=\"ui-icon ui-icon-link\" title=\"You have set this proposal as your interim favourite for your project idea.\">&nbsp;</span>";
                            }
                            if (data.record.proposal_id == data.record.pr_proposal_id && data.record.selected == 1) {
                                uname_text += "&nbsp;<span style=\"float:left;display:inline;\" class=\"ui-icon ui-icon-check\" title=\"You have chosen to offer this student the project.\">&nbsp;</span>";
                            }

                            return uname_text;
                        }
                    },
                    inst_id: {
                        title: "Institute",
                        width: "26%",
                        create: false,
                        edit: false,
                        display: function (data) {
                            return data.record.i_name;
                        }
                    },
                    solution_short: {
                        //width: "2%",
                        title: "Proposal details",
                        sorting: false,
                        display: function (data) {
                            console.log('Got in proposals:', data.record);
                            if (data.record.state == 'rejected') {
                                return "<a title=\"See this Proposal\" href=\"javascript:void(0);\" " +
                                        "onclick=\"getProposalDetail(" + data.record.proposal_id + ")\">" +
                                        "<span style=\"float:left;display:inline;\" class=\"ui-icon ui-icon-info\">See details</span></a>" +
                                        "&nbsp;<span style=\"float:left;display:inline;\" class=\"\" title=\"You have rejected this proposal.\">" +
                                        "&nbsp;&nbsp;<div style=\"display:inline;\">Rejected</div></span>";
                            }
                            else if (data.record.state == 'archived') {
                                return "<a title=\"See this Proposal\" href=\"javascript:void(0);\" " +
                                        "onclick=\"getProposalDetail(" + data.record.proposal_id + ")\">" +
                                        "<span style=\"float:left;display:inline;\" class=\"ui-icon ui-icon-info\">See details</span></a>" +
                                        "&nbsp;<span style=\"float:left;display:inline;\" class=\"\" title=\"This proposal has been archived because another student accepted your offer or this student accepted another offer.\">" +
                                        "&nbsp;&nbsp;<div style=\"display:inline;\">Archived</div></span>";
                            }
                            else if (data.record.state == 'draft') {
                                return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" +
                                        "<span style=\"display:inline;\" class=\"\" title=\"This proposal is in draft mode and not yet visible.\">" +
                                        "&nbsp;&nbsp;<div style=\"display:inline;\">Draft only</div></span>";
                            }
                            else if (data.record.state == 'accepted') {
                                return "<a title=\"See this Proposal\" href=\"javascript:void(0);\" " +
                                        "onclick=\"getProposalDetail(" + data.record.proposal_id + ")\">" +
                                        "<span style=\"float:left;display:inline;\" class=\"ui-icon ui-icon-info\">See details</span></a>" +
                                        "&nbsp;<span style=\"float:left;display:inline;\" class=\"\" title=\"This proposal has been accepted by the student as their final choice.\">" +
                                        "&nbsp;&nbsp;<div style=\"display:inline;\">Accepted</div></span>";

                            }
                            else if (data.record.proposal_id == data.record.pr_proposal_id && data.record.selected == 0) {
                                return "<a title=\"See this Proposal\" href=\"javascript:void(0);\" " +
                                        "onclick=\"getProposalDetail(" + data.record.proposal_id + ")\">" +
                                        "<span style=\"float:left;display:inline;\" class=\"ui-icon ui-icon-info\">See details</span></a>" +
                                        "&nbsp;<span style=\"float:left;display:inline;\" class=\"\" title=\"You have set this proposal as your interm favourite for your project idea.\">" +
                                        "&nbsp;&nbsp;<div style=\"display:inline;\">Interim</div></span>";
                            }
                            else if (data.record.proposal_id == data.record.pr_proposal_id && data.record.selected == 1) {
                                return "<a title=\"See this Proposal\" href=\"javascript:void(0);\" " +
                                        "onclick=\"getProposalDetail(" + data.record.proposal_id + ")\">" +
                                        "<span style=\"float:left;display:inline;\" class=\"ui-icon ui-icon-info\">See details</span></a>" +
                                        "&nbsp;<span style=\"float:left;display:inline;\" class=\"\" title=\"You have chosen to offer this student the project.\">" +
                                        "&nbsp;&nbsp;<div style=\"display:inline;\">Offered</div></span>";
                            }
                            else {
                                return "<a title=\"See this Proposal\" href=\"javascript:void(0);\" " +
                                        "onclick=\"getProposalDetail(" + data.record.proposal_id + ")\">" +
                                        "<span style=\"float:left;display:inline;\" class=\"ui-icon ui-icon-info\">See details</span></a>" +
                                        "&nbsp;<span style=\"float:left;display:inline;\" class=\"\" title=\"This proposal is " + data.record.state + "\">" +
                                        "&nbsp;&nbsp;<div style=\"display:inline;\">" + data.record.state + "</div></span>";
                            }
                        },
                        create: false,
                        edit: false
                    },
                },
            });
            //Load proposal list from server on initial page load
            loadFilteredProposals();
        });
    </script><?php
}

function showProposalsForStudent($student_id, $show_only_mine) {
    global $base_url;
    $url_type = (bool) $show_only_mine ? '/mine' : '';
    echo '<div id="baktoprops"><a href=" ' . $base_url . '/dashboard/proposals/browsebytype' . $url_type . '">' . t('Back to proposals overview') . '</a></div>';
    $student = Users::getStudentDetails($student_id);
    $s_name = $student->student_name;
    echo '<h2>' . t('Project proposals made by \'' . $s_name) . '\'</h2>';
    ?>
    <div id="TableContainer" style="width: 800px;"></div>
    <script type="text/javascript">

        jQuery(document).ready(function ($) {
            window.view_settings = {};

            function loadFilteredProposals() {
                $("#TableContainer").jtable("load", {
                    student: <?php echo $student_id ?>
                });
            }

            //Prepare jTable
            $("#TableContainer").jtable({
                paging: true,
                pageSize: 10,
                sorting: true,
                defaultSorting: "pid ASC",
                actions: {
                    listAction: module_url + "actions/proposal_actions.php?action=list_proposals"
                },
                fields: {
                    proposal_id: {
                        key: true,
                        create: false,
                        edit: false,
                        list: false
                    },
                    pid: {
                        title: "Project",
                        width: "26%",
                        create: false,
                        edit: false,
                        display: function (data) {
                            return data.record.pr_title;
                        }
                    },
                    o_name: {
                        title: "Organisation",
                        width: "30%",
                        display: function (data) {
                            return data.record.o_name;
                        }
                    },
                    state: {
                        title: "Status",
                        width: "10%",
                        display: function (data) {
                            return data.record.state;
                            /*
                             if(data.record.state == 'published'){
                             return 'yes';
                             }else{
                             return 'no';
                             }
                             */
                        }
                    },
                    //if(data.record.state != 'published'){
                    solution_short: {
                        //width: "2%",
                        title: "Proposal details",
                        sorting: false,
                        display: function (data) {
                            return "<a title=\"See this Proposal\" href=\"javascript:void(0);\" " +
                                    "onclick=\"getProposalDetail(" + data.record.proposal_id + ")\">" +
                                    "<span class=\"ui-icon ui-icon-info\">See details</span></a>";

                        },
                        create: false,
                        edit: false
                    },
                },
            });
            //Load proposal list from server on initial page load
            loadFilteredProposals();
        });
    </script><?php
}
