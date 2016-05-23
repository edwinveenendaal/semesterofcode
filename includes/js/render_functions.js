//helper function that we need to copy objects not by reference which is the default
function clone(obj) {
    var copy;
    // Handle the 3 simple types, and null or undefined
    if (null == obj || "object" != typeof obj) return obj;

    // Handle Date
    if (obj instanceof Date) {
        copy = new Date();
        copy.setTime(obj.getTime());
        return copy;
    }

    // Handle Array
    if (obj instanceof Array) {
        copy = [];
        for (var i = 0, len = obj.length; i < len; i++) {
            copy[i] = clone(obj[i]);
        }
        return copy;
    }

    // Handle Object
    if (obj instanceof Object) {
        copy = {};
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = clone(obj[attr]);
        }
        return copy;
    }

    throw new Error("Unable to copy obj! Its type isn't supported.");
}

function chooseProposalForProject(project_id, proposal_id, is_final) {
    var content = '';
    if (is_final) {
        content += Drupal.t('Note: This is a final decision but not irreversible.');
        content += '\n\n';
        content += '1. ' + Drupal.t('If instead, you want to only mark this proposal as your preferred one and still have the ability to change at a ') +
                Drupal.t('later date, then press \'cancel\' and choose "Accept interim" instead.') + '\n';
        content += '2. ' + Drupal.t('This student will be offered to accept this project idea based on his/her proposal.') + '\n';
        content += '3. ' + Drupal.t('The student and his/her supervisor will be informed by email that you have decided to offer this proposal.') + '\n';
        content += '4. ' + Drupal.t('This will mean all other proposals are rejected for now.') + '\n';
        content += '5. ' + Drupal.t('All other candidates will be informed by email that their proposal has been offered to another student. ') + '\n';
        content += '6. ' + Drupal.t('This offer will only become final once the student accepts it. ') +
                Drupal.t('If the student decides to accept another project offer, this project idea will once again become available to other students ') + '\n';
        Drupal.t('which means you can then select an alternative proposal.') + '\n';

        content += '\n';
    }
    else {
        content += Drupal.t('Note: This is not a final decision.');
        content += '\n\n';
        content += '1. ' + Drupal.t('If instead, you want to mark this proposal as your final choice, then press "cancel" and choose "Accept final" instead.') + '\n';
        content += '2. ' + Drupal.t('This proposal will be flagged in the system as your interim choice for this project idea.') + '\n';
        content += '3. ' + Drupal.t('You may change from this proposal to another one if a student writes a proposal which you prefer over this one. ') + '\n';
        content += '4. ' + Drupal.t('The Student and his/her supervisor will be informed by email that you have decided to select (as interim) this proposal.') + '\n';
        content += '5. ' + Drupal.t('This will mean all other proposals for this project are still valid in the system. ') + '\n';
        content += '6. ' + Drupal.t('Other students will not know which candidate proposal you have chosen in the interim, ') +
                Drupal.t('but they will recieve an email to let them know it is not their own, meaning they still have a chance if they improve it.') + '\n';
        content += '\n';
    }
    if (confirm(content)) {
        var url = module_url + "actions/proposal_actions.php?action=mark_proposal";
        $jq.post(url, {'proposal_id': proposal_id, 'project_id': project_id, 'is_final': is_final}, function (data, status) {
            if (!is_final) {
                ajaxInsert(data, 'proposal-interim-markup-' + proposal_id + '-button');
                $jq('#proposal-reject-markup-' + proposal_id).hide();//disable this button if final
            }
            else {
                ajaxInsert(data, 'proposal-final-markup-' + proposal_id + '-button');
                $jq('#proposal-interim-markup-' + proposal_id).hide();//disable this button if final
                $jq('#proposal-reject-markup-' + proposal_id).hide();//disable this button if final
            }
        });
    }
}

function hideOtherDivsAfterProposalReject(proposal_id) {
    $jq('#proposal-interim-markup-' + proposal_id).hide();//disable this button if final
    $jq('#proposal-final-markup-' + proposal_id).hide();//disable this button if final
}

function rejectProposalForm(proposal_id, target) {
    var url = module_url + "actions/proposal_actions.php?action=reject_form&id=" + proposal_id + "&target=" + target;
    $jq.get(url, function (data, status) {
        ajaxInsert(data, target);
    });
}

function getRejectProposalMarkup(proposal, option) {
    var content = "";
    content += '	<div>';
    content += '		' + option + Drupal.t(' Click the button below to reject this proposal. Please give a reason why you do so.');
    content += '		<br/>';
    content += '		<br/>';
    content += '		<div id="proposal-reject-markup-' + proposal.proposal_id + '-button">';
    content += '			<input style="margin-left:20px" type="button" value="' +
            Drupal.t('Reject this proposal') + '" onclick="rejectProposalForm(' + proposal.proposal_id +
            //', \'rejectformdiv\');"/>';
            ', \'proposal-reject-markup-' + proposal.proposal_id + '-button\');\"/>';
    content += '			<div id="rejectformdiv"></div>';
    content += '		</div>';
    content += '	</div>';
    return content;
}

function getAcceptProposalInterimMarkup(proposal, option) {
    var content = "";
    content += '	<div>';
    content += '		' + option + Drupal.t(' Click the button below to select this proposal as your preferred interim solution.');
    content += '		<br/>';
    content += '		<br/>';
    content += '		<div id="proposal-interim-markup-' + proposal.proposal_id + '-button">';
    content += '			<input style="margin-left:20px" type=\"button\" value=\"' + Drupal.t('Accept interim') + '\" onclick=\"chooseProposalForProject(' + proposal.pid + ',' + proposal.proposal_id + ', 0);\"/>';
    content += '			<br/>';
    content += '		</div>';
    content += '	</div>';
    return content;
}

function getAcceptProposalFinalMarkup(proposal, option) {
    var content = "";
    content += '	<div>';
    content += '		' + option + Drupal.t(' Click the button below to offer this project idea to this students proposal.');
    content += '		<br/>';
    content += '		<br/>';
    content += '		<div id="proposal-final-markup-' + proposal.proposal_id + '-button">';
    content += '			<input style="margin-left:20px" type=\"button\" value=\"' + Drupal.t('Accept final') + '" onclick=\"chooseProposalForProject(' + proposal.pid + ',' + proposal.proposal_id + ', 1);\"/>';
    content += '			<br/>';
    content += '		</div>';
    content += '	</div>';
    return content;
}

function renderProposalStatus(proposal) {
    var content = '<div class="totheright">(' + Drupal.t('Status: ') + proposal.state + ')</div>' +
            "<h2>" + proposal.title + "</h2>";
    content += Drupal.t('Submitted by the student') + " <i>" + (proposal.student_name ? proposal.student_name : proposal.name) + " </i>";
    content += '<br/>';
    content += '<br/>';
    if (proposal.state == 'rejected') {
        if (proposal.is_project_owner || proposal.is_project_mentor) {
            content += Drupal.t('You have rejected this proposal for the solution of your project. ');
        } else {
            content += Drupal.t('The project owner rejected this proposal for the solution of your project idea. ');
        }
    } else {
        if ((proposal.selected == 1) && (proposal.pr_proposal_id == proposal.proposal_id)) {
            content += (proposal.is_project_owner ?
                Drupal.t('You have selected this proposal as your final choice of solution for your project idea. You cannot change this.') :
                Drupal.t('The project mentor selected this proposal as the final choice of solution for this project idea.'));
        } else {
            if ((proposal.selected == 1) && (proposal.pr_proposal_id != proposal.proposal_id)) {
                content += ((proposal.is_project_owner || proposal.is_project_mentor) ?
                    Drupal.t('You have already selected another proposal as your final choice of solution for your project idea. You cannot change this.') :
                    Drupal.t('The project mentor selected another proposal as the final choice of solution for this project idea.'));
            } else {
                //so selected = 0
                if ((proposal.pr_proposal_id == proposal.proposal_id)) {
                    if (proposal.is_project_owner || proposal.is_project_mentor) {
                        content += Drupal.t('You have selected this proposal as your preferred interim choice of solution for your project idea. ');
                        content += Drupal.t('This is not final and you may change it to another proposal before the end of the student signup period.');
                    } else {
                        content += Drupal.t('The project owner selected this proposal as the preferred interim choice of solution for this project idea. ');
                        content += Drupal.t('This is not final and the owner may change to another proposal before the end of the student signup period.');
                    }
                    if (proposal.is_project_owner || proposal.is_project_mentor) {
                        content += '<div class="prop-mini-form-wrapper" id="proposal-final-markup-' + proposal.proposal_id + '">';
                        content += getAcceptProposalFinalMarkup(proposal, '');
                        content += '</div>';
                    }
                } else {
                    console.log('hier het proposal ding object', proposal);
                    if ((proposal.pr_proposal_id != null) && (proposal.pr_proposal_id != "0")) {
                        content += ((proposal.is_project_owner || proposal.is_project_mentor) ? Drupal.t('You have already selected another proposal as your preferred choice of solution for your project idea.') :
                                Drupal.t('The project mentor has selected another proposal as the preferred interim choice of solution for this project idea.'));
                    } else {
                        content += ((proposal.is_project_owner || proposal.is_project_mentor) ? Drupal.t('You haven\'t yet selected a proposal as your preferred choice of solution for your project idea.') :
                                Drupal.t('The project mentor hasn\'t yet selected a proposal as the preferred interim choice of solution for this project idea.'));
                    }
                    if (proposal.is_project_owner || proposal.is_project_mentor) {
                        content += '<div><br/>';
                        content += '	<div>' + Drupal.t('You have the following choices') + '</div>';
                        content += '	<br/>';
                        content += '	<div class="prop-mini-form-wrapper" id="proposal-reject-markup-' + proposal.proposal_id + '">';
                        content += getRejectProposalMarkup(proposal, '1.');
                        content += '	</div>';
                        content += '	<div class="prop-mini-form-wrapper" id="proposal-interim-markup-' + proposal.proposal_id + '">';
                        content += getAcceptProposalInterimMarkup(proposal, '2.');
                        content += '	</div>';
                        content += '	<div class="prop-mini-form-wrapper" id="proposal-final-markup-' + proposal.proposal_id + '">';
                        content += getAcceptProposalFinalMarkup(proposal, '3.');
                        content += '	</div>';
                        content += '</div>';
                    }
                }
            }
        }
     }
     return content;
}

function renderProposalOverview(proposal) {
    var content = "<h2>" + proposal.title + "</h2>";
    content += Drupal.t('Submitted by student') + " '" + (proposal.student_name ? proposal.student_name : proposal.name);
    content += '&nbsp;<i>(' + proposal.state + ')</i>';
    content += '<br/>';
    content += '<ul>';
    content += '	<li>';
    content += '		' + Drupal.t('This is the Overview tab, where you can also add comments to this proposal.');
    content += '	</li>';
    content += '	<li>';
    content += '		' + Drupal.t('The "Student Details" tab lists both the students, the institution he/she is attending and his/her supervisor.');
    content += '	</li>';
    content += '	<li>';
    content += '	' + Drupal.t('The "Solution Summary" tab is where the student was asked to provide a brief synopsis of the solution.');
    content += '	</li>';
    content += '	<li>';
    content += '		' + Drupal.t('The "Solution Description" tab is where the student was asked to provide a more detailed description of the solution.');
    content += '	</li>';
    content += '	<li>';
    content += '		' + Drupal.t('The "Project" tab is for your reference and links to the original project idea.');
    content += '	</li>';
    content += '	<li>';

    if (proposal.is_project_owner) {
        content += '		' + Drupal.t('The "Status" tab is to allow you to set the proposals current status.');
    } else {
        content += '		' + Drupal.t('The "Status" tab is to allow you to view the proposals current status.');
    }
    content += '	</li>';

    content += '</ul>';
    // comments
    content += "<div id=\"comments-proposal-" + proposal.proposal_id + "\"></div>";
    // go and get the comments asynch...
    getCommentsForEntity(proposal.proposal_id, 'proposal', 'comments-proposal-' + proposal.proposal_id);
    //

    return content;
}
/*
 * This function is called to render the project retrieved with an ajax call to project_detail always
 */
function renderProject(project, apply_projects) {
    var navigation = true;
    var alert_taken = ((project.state == 'preselected') || (project.state == 'active')) ? ' alert' : '';
    var content = '<div id="project_status" class="totheright' + alert_taken + '">(status: ' + project.state + ')';
    if (navigation) {
        if (typeof project.nav != 'undefined') {
            //content +="<div class='"+ navigation_class + "'>";
            content += "<BR>";
            content += (project.nav.prev_pid ?
                    "<input id='vals-btn-prev' type='button' onclick='ajaxCall(\"project\", " +
                    "\"project_detail\", {project_id: " + project.nav.prev_pid + ", index: " + project.nav.prev_nr + "}" +
                    ", \"populateModal\", \"json\", [renderProject, " + apply_projects + ", 1]);' " + //3rd arg is true denoting that result will be parsed arg to populate fun
                    " value='" + Drupal.t('Prev') + "'/>" : "") +
                    (project.nav.next_pid ? "<input id='vals-btn-next' type='button' onclick='ajaxCall(\"project\", " +
                            "\"project_detail\", {project_id: " + project.nav.next_pid + ", index: " + project.nav.next_nr + "}" +
                            ", \"populateModal\", \"json\", [renderProject, " + apply_projects + ", 1]);' " + //3rd arg is true denoting that result will be parsed arg to populate fun
                            " value='" + Drupal.t('Next') + "'/>" : "");
            content += "</div>";
        }
        ;
    }
    content += '</div>' +
            "<h2>" + project.title + "</h2>";
    content += project.description;
    if (project.url) {
        content += "<br/><a target='_blank' class='external' href='" + project.url + "'>" + project.url + "</a>";
    }
    
    content += "<h2>" + Drupal.t('Statistics') + "</h2>";
    if (project.proposal_count != "0") {
        var has_preferred_proposal = (project.proposal_id != "0");
        var is_selected_proposal = (project.selected == "1");
        if (typeof project.proposal_count != 'undefined'){ 
            content += Drupal.t('Number of other proposals already submitted to this project: ') 
              + (project.proposal_count - 1) ;
        }
        if (has_preferred_proposal && !is_selected_proposal) {
            message = Drupal.t('The project mentor has selected an interim preferred proposal already, however this is not final and may change.');
        }
        else if (has_preferred_proposal && is_selected_proposal) {
            message = Drupal.t('The project mentor has marked an existing proposal as final solution.');
        }
        else {
            message = Drupal.t('The project mentor has not marked any proposal as his/her preferred solution yet.');
        }
        content += '<br/>' + message + '<br/>';
    } else {
        content += Drupal.t('This project has no proposals yet.');
    }
    var rate_projects = window.view_settings.rate_projects;

    if ((typeof rate_projects !== 'undefined') && rate_projects) {
        var rate = -2;
        if (typeof project.rate !== 'undefined') {
            rate = project.rate;
        }
        content += "<h2>" + Drupal.t('Your Opinion') + "</h2>" +
                renderRecommendation(project.pid);
        content += "<br/>";
        content += renderSupervisorLike(project.pid, rate);
    }
    // comments
    content += "<div style= \"height:32px;\" id=\"comments-project-" + project.pid + "\"></div>";
    if (window.view_settings.comment_projects){        
        // go and get the comments asych...
        getCommentsForEntity(project.pid, 'project', 'comments-project-' + project.pid);
    }

    //Students can mark a project as favourite
    if (apply_projects) {
        content += "<div class=\"totheright\">";//style=\"display:none\"
        var favourite = false;
        if (typeof project.favourite != 'undefined') {
            favourite = project.favourite;
        }
        content += renderStudentLike(project.pid, favourite);
        content += "<input id='vals-btn-submit-proposal' type='button' onclick='getProposalFormForProject(" + project.pid + ")' value='" + Drupal.t('Create proposal for this project') + "'/>";
        content += "</div>";
    }

    return content;
}

function renderRecommendation(pid) {
    return "<div id='recommend_msg'></div>" +
            Drupal.t('Recommend this project to:') + "&nbsp;&nbsp;<input type='text' id='recommend_email' name='recommend_email'/>" +
            "<input type='button' value='" + Drupal.t('Recommend') + "' onclick='ajaxCall(\"project\", \"recommend\", {id: " +
            pid + ", email: $jq(\"#recommend_email\").val()}, \"recommend_msg\");' />";
}

function renderSupervisorLike(pid, current) {
    return "<div id='preference_msg'></div>" +
            Drupal.t('Could you or do you want to be the supervisor for this project for one of this institutes students?') + "<br>" +
            "<label><input type='radio' value='-1' id='project_like_1' name='project_like' " +
            ((-1 == current) ? 'checked="checked"' : '') + "/>" + Drupal.t('Not for me') + "</label>&nbsp;" +
            "<label><input type='radio' value=0 id='project_like0' name='project_like' " +
            ((0 == current) ? 'checked="checked"' : '') + "/>" + Drupal.t('Maybe') + "</label>&nbsp;" +
            "<label><input type='radio' value=1 id='project_like1' name='project_like' " +
            ((1 == current) ? 'checked="checked"' : '') + "/>" + Drupal.t('Would suit me') + "</label>&nbsp;<input type='button' value='" + Drupal.t('Save Preference') + "' onclick='ajaxCall(\"project\", \"rate\", {id: " + pid +
            ", rate: $jq(\"input:radio[name=project_like]:checked\").val()}, \"handleMessage\", \"json\", [\"preference_msg\"]);'/>"
            ;
}

        function renderStudentLike(pid, is_marked) {
            return "<div id='favourite_msg'>" +
                    //Drupal.t('You can mark this project as one of your favourites?')+
                    (is_marked ?
                            "<img src='" + module_url + "includes/js/resources/heart_blue.png' title= '" +
                            Drupal.t('You marked this project as one of your favourites') + "' />" :
                            "<input type='button' value='" + Drupal.t('Mark this project') +
                            "' id='project_favour' name='project_favour' " +
                            " onclick='ajaxCall(\"project\", \"mark\", {id: " + pid +
                            "}, \"handleMessage\", \"json\", [\"favourite_msg\"]);'/>") + "</div>";
        }

        function renderOrganisation(org, contact_possible) {
            var content = "<h1>" + org.name + "</h1>";
            content += "<h3>" + Drupal.t('Information') + "</h3>" + org.description;
            content += "<br/><h3>" + Drupal.t('Website') + "</h3><a target='_blank' class='external' href='" + org.url + "'>" + org.url + "</a>";
            if (typeof contact_possible !== 'undefined' && contact_possible) {
                content += "<h3>" + Drupal.t('Contact information') + "</h3>" +
                        '<div style="padding-left:5px;">' +
                        Drupal.t('Name: ') + org.contact_name + '<br/>' +
                        Drupal.t('Email: ') + org.contact_email + '</div>';
            }
            return content;
        }

        function renderInstitute(ins, contact_possible) {
            var content = "<h1>" + ins.name + "</h1>";
            if (typeof contact_possible !== 'undefined' && contact_possible) {
                content += "<h3>" + Drupal.t('Contact information') + "</h3>" +
                        '<div style="padding-left:5px;">' +
                        Drupal.t('Name: ') + ins.contact_name + '<br/>' +
                        Drupal.t('Email: ') + ins.contact_email + '</div>';
            }
            return content;
        }

        function renderStudent(data) {
            var s = '<ol>';
            s += '<li>' + Drupal.t('Name: ') + (data.student_name ? data.student_name : data.name) + '</li>';
            s += '<li>' + Drupal.t('Email: ') + data.mail + '</li>';
            s += '<li>' + Drupal.t('Institute: ') + data.i_name + '</li>';
            s += '<li>' + Drupal.t('Supervisor: ') + (data.supervisor_name ? data.supervisor_name : data.supervisor_user_name) + '</li>';
            s += '<li>' + Drupal.t('Supervisor email: ') + data.supervisor_user_mail + '</li>';
            s += '</ol>';
            return s;
        }

        /*This function renders the proposal as tabs and places it also in the right target */
        function getProposalDetail(proposal_id, target, msg) {
            var tabs = [
                {tab: 'overview', label: Drupal.t('Overview')},
                {tab: 'student', label: Drupal.t('Student Details')},
                //{tab: 'cv', label: 'Cv'},
                {tab: 'summary', label: Drupal.t('Solution Summary')},
                {tab: 'solution', label: Drupal.t('Solution Description')},
                {tab: 'project', label: Drupal.t('Project')},
                {tab: 'status', label: Drupal.t('Status')}
                //{tab: 'modules', label: 'Modules and Libraries'}
            ];
            var content_tabs = [
                'tab_overview',
                'tab_student',
                //'tab_cv',
                'tab_summary',
                'tab_solution',
                'tab_project',
                'tab_status'
              //,'tab_modules'
            ];
            var url = module_url + "actions/proposal_actions.php?action=proposal_detail&proposal_id=" +
                    proposal_id;

            if (window.view_settings.apply_projects) {
                tabs.push({tab: 'edit', label: Drupal.t('Edit')});
                content_tabs.push('tab_edit');
                tabs.push({tab: 'delete', label: Drupal.t('Delete')});
                content_tabs.push('tab_delete');
            }

            if ((typeof target == 'undefined')) {
                target = 'modal';
            }
            //Get the details and render with renderProposalTabs
            $jq.get(url, function (data, status) {
                if (data.result == 'error') {
                    alert(Drupal.t('Could not retrieve well the saved data'));
                    return;
                }
                var msg_container = 'modal-content';
                var tabs_created = false;
                var before = 'toc';
                switch (target) {
                    case 'modal':
                        tabs_created = generateAndPopulateModal(data, renderProposalTabs, tabs);
                        break;
                    case 'our_content' : //this case should not occur anymore
                        var data2 = jQuery.parseJSON(data);
                        if (data2.result == 'error') {
                            ajaxAppend(result.error, 'our_content', 'error');
                        } else {
                            var content = renderProposalTabs(data2.result, tabs, 'our_content');
                            msg_container = 'our_content';
                            if (Obj('our_content').html(content)) {
                                tabs_created = true;
                                //activatetabs('tab_', content_tabs);
                            }
                            ;

                        }
                        break;
                    default:
                        var data2 = jQuery.parseJSON(data);
                        if (data2.result == 'error') {
                            ajaxAppend(result.error, 'our_content', 'error');
                        } else {
                            msg_container = 'admin_container';
                            before = 'toc';//'msg_' + target;
                            ajaxCall('proposal', 'view', {id: proposal_id, target: target}, target);
//					var content = render  ProposalTabs(data2.result, tabs, 'our_content');
//
//					if (Obj(target).html(content)) {
//						console.log('doing the tabs first?');
//						tabs_created = true;
//						//activatetabs('tab_', content_tabs);
//					};
                        }
                        //TODO this case should be covered too:  tabs_created = populateModal(data, renderProposalTabs, tabs);
                }
                if (tabs_created) {
                    //TODO: these activate tabs should be done for the case where tabs are created
                    //console.log('doing the tabs second' + content_tabs);
                    activatetabs('tab_', content_tabs);
                }
                if (typeof msg != 'undefined' && msg) {
                    ajaxAppend(msg, msg_container, 'status', before);
                }
                ;

            });

        }

        function renderProposalTabs(result, labels, container) {
            var s = '<ol id="toc">';
            var count = labels.length;
            var target = '';
            var onclick = '';

            //passed data
            var ney = Drupal.t('Nothing entered yet');
            var project_state = result.pr_state;
            var proposal_state = result.state;
            var edit_possible = ((proposal_state == 'draft') || (proposal_state == 'open'));
            //console.log(proposal_state + ' proposal state and project state' + project_state);

            if (typeof container == 'undefined') {
                container = 'tab_edit';
            }
            for (var t = 0; t < count; t++) {
                target = labels[t].tab;
                if (target == 'edit') {
                    if (!edit_possible) {
                        onclick = "\" ";
                    } else {
                        onclick = "\" onclick=\"ajaxCall('proposal', 'edit', {proposal_id:" +
                                result.proposal_id + ", target:'" + container + "'}, 'jsonFormResult', 'json', ['" +
                                container + "']);\"";
                    }
                } else {
                    onclick = '" ';
                }
                if (target == 'delete' || target == 'edit') {
                    class_str = ' class="right"';
                } else {
                    class_str = '';
                }
                s += '<li' + class_str + '><a id="tab_tab_' + target + '" href="#tab_tab_' + target + onclick +
                        '><span>' + labels[t].label + '</span></a></li>';

            }
            s += '</ol>';
            s += '<div class="tabs_container">';

            for (var t = 0; t < count; t++) {
                target = labels[t].tab;
                s += '<div id="tab_' + target + '" class="content">';
                s += "<div id='msg_" + target + "'></div>";

                switch (target) {
                    case 'project':
                        //Paul - there was a bug here which meant that the project description
                        // was being replaced by the organisation description. The value to use
                        // from this resultset is 'pr_description' rather than 'description' which
                        // is used in the renderProject() function above
                        var result_project = clone(result);
                        result_project.title = result.pr_title;
                        result_project.description = result.pr_description;
                        result_project.proposal_id = result.pr_proposal_id;
                        result_project.url = result.pr_url;
                        result_project.state = project_state;
                        s += renderProject(result_project, false);
                        break;
                    case 'overview':
                        s += renderProposalOverview(result);
                        break;
                    case 'student':
                        s += renderStudent(result);
                        break;
                    case 'title':
                        s += (result.title ? result.title : ney);
                        break;
                    case 'summary':
                        s += (result.solution_short ? result.solution_short : ney);
                        break;
                    case 'solution':
                        s += (result.solution_long ? result.solution_long : ney);
                        break;
                    case 'status':
                        s += renderProposalStatus(result);
                        break;
                    case 'edit':
                        s += edit_possible ?
                                Drupal.t('Wait please') :
                                'This proposal is already published and so cannot be edited anymore';
                        break;
                    case 'delete':
                        s += edit_possible ?
                                Drupal.t('Are you sure you want to delete this proposal?<br>') +
                                '<input type="button" value="' + Drupal.t('Yes') + '" onclick="ajaxCall(\'proposal\', \'delete\', ' +
                                '{proposal_id:' + result.proposal_id + ', target: \'' + container + '\' }, ' +
                                '\'handleDeleteResult\', \'json\', [\'our_content\', \'proposal\', \'myproposal_page\']);"/>' :
                                'This proposal is already published and so cannot be deleted anymore';
                        break;
                }
                s += "</div>";

            }
            s += "</div>";
            return s;
        }

        function getFinalisation(agreementId) {
            ajaxCall("agreement", "render_finalisation_for_id", {id: agreementId, target: 'our_content'}, "formResult", 'html', 'our_content');
        }

        function getAgreement(agreementId) {
            ajaxCall("agreement", "render_agreement_for_id", {id: agreementId, target: 'our_content'}, "formResult", 'html', 'our_content');
        }

        function getAcceptedProjectOverview(agreementId) {
            ajaxCall("agreement", "render_project_for_id", {id: agreementId, target: 'our_content'}, "formResult", 'html', 'our_content');
        }

        function getProposalsForProject(projectId, show_mine_only) {
            ajaxCall("proposal", "render_proposals_for_id", {id: projectId, target: 'our_content', mine_only: show_mine_only}, "formResult", 'html', 'our_content');
        }

        function getProposalsForStudent(studentId, show_mine_only) {
            ajaxCall("institute", "render_proposals_for_student", {id: studentId, target: 'our_content', mine_only: show_mine_only}, "formResult", 'html', 'our_content');
        }

        function acceptProjectOffer(proposalId, ptitle, projectId) {
            var confirm_text = '';
            confirm_text += Drupal.t('Are you sure that you want to choose this one?') + '\n\n';
            confirm_text += ptitle + '\n\n';
            confirm_text += Drupal.t('By clicking okay you agree to be the chosen student for this project idea and cannot change to another.') + '\n';
            confirm_text += Drupal.t('Any other project offers will marked as rejected by you and made available to other students again');
            if (confirm(confirm_text)) {
                ajaxCall("proposal", "accept_proposal_offer", {proposal_id: proposalId, target: 'our_content', project_id: projectId}, "formResult", 'html', 'our_content');
            }
        }

        function getProposalFormForProject(projectId) {
            Drupal.CTools.Modal.dismiss();
            //With formResult it will turn all textareas in rte fields and with handleResult, it just copies the
            //form and places everything in the target content
            //possible formats:
            //   ajaxCall(module, action, data, handleResult, json, args)
            //   ajaxCall(module, action, data, target, html)
            //Note that formResult and jsonFormResult store the call in the target and convert the textareas
            ajaxCall('proposal', 'proposal_form', {id: projectId, target: 'our_content'}, "formResult", 'html', 'our_content');
        }

        function getProjectDetail(project_id) {
            var url = module_url + "actions/project_actions.php?action=project_detail&project_id=" + project_id;
            //TODO: currently the apply projects is passed around as global. not so elegant
            $jq.get(url, function (data, status) {
                generateAndPopulateModal(data, renderProject, window.view_settings.apply_projects);
            });
        }

        function getCommentsForEntity(id, entityType, target) {
            var url = module_url + "actions/comment_actions.php?action=viewall&id=" + id + "&type=" + entityType;
            $jq.get(url, function (data, status) {
                ajaxInsert(data, target);
            });
        }

        function getOrganisationDetail(org_id) {
            var url = module_url + "actions/organisation_actions.php?action=organisation_detail&orgid=" + org_id;
            $jq.get(url, function (data, status) {
                generateAndPopulateModal(data, renderOrganisation, true);
            });
        }

        function getInstituteDetail(id) {
            var url = module_url + "actions/institute_actions.php?action=detail&instid=" + id;
            $jq.get(url, function (data, status) {
                generateAndPopulateModal(data, renderInstitute, true);
            });
        }

        function testTagInput() {
            var filter = /^[a-z0-9+_.\,\s]+$/i;
            if (filter.test($jq("#tags").val()) || $jq("#tags").val() == "") {
                $jq("#tags").removeClass("error");
                $jq("#infotext").removeClass("error");
                $jq("#infotext").text("");
                return true;
            }
            else {
                $jq("#tags").addClass("error");
                $jq("#infotext").addClass("error");
                $jq("#infotext").text("Invalid character/s entered");
                return false;
            }
        }