<?php

/*
 * Put any email message formatting in here rather than vals_soc.mail.inc 
 *
 */

function get_proposal_rejection_email_body($record, $user_type) {
    $server = _VALS_SOC_FULL_URL;

    $student_username = $record->name; //student userid
    $student_email = $record->mail; // student mail
    $student_institute = $record->i_name; //Institute name where student is from

    $supervisor_username = $record->supervisor_user_name;
    $supervisor_email = $record->supervisor_user_mail;

    $mentor_username = $record->mentor_user_name;
    $mentor_email = $record->mentor_user_mail;
    $mentor_organisation = $record->o_name;

    $reason = $record->reason;

    $project_title = $record->pr_title; //project title
    $proposal_title = $record->title; //proposal title
    $proposal_synopsis = $record->solution_short;
    $alert = '';

    if ($user_type == 'student') {
        $recipient_username = $student_username;
        $alert .= t('A proposal you have written has been rejected by the mentor who created the project idea. ');
        $alert .= '<br/><br/>';
        $alert .= $reason;
        $alert .= "<br/>";
    } else if ($user_type == 'supervisor') {
        $recipient_username = $supervisor_username;
        $alert .= t('A proposal which one of your students previously wrote has been rejected by the mentor who created the project idea. ');
        $alert .= '<br/><br/>';
        $alert .= $reason;
        $alert .= "<br/>";
    }

    $output = '';
    $output .= '<p>';
    $output .= t('Hello') . ' ' . $recipient_username . ',';
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= $alert;
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= t('Here is an overview...');
    $output .= '<br/>';

    $output .= "<p>";
    $output .= "<b>" . t('Project idea') . "</b>";
    $output .= "<br/>";
    $output .= $project_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal title') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal synopsis') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_synopsis;
    $output .= "</p>";


    $output .= "<b>" . t('Contact details') . "</b>";
    $output .= '<ul class=\'grouplist\'>';

    $output .= "<li>";
    $output .= t('Student username') . ": <i>" . $student_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student email') . ": <i>" . $student_email . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Student supervisors username') . ": <i>" . $supervisor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student supervisors email') . ": <i>" . $supervisor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Students Institution') . ": <i>" . $student_institute . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Initial mentors username') . ": <i>" . $mentor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors email') . ": <i>" . $mentor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors Organisation') . ": <i>" . $mentor_organisation . "</i>";
    $output .= "</li>";

    $output .= "</ul>";

    $output .= "<p>";
    $output .= tt('Go to <a href="%1$s/dashboard" target="_blank">%1$s/dashboard</a> to view more details.', $server);
    $output .= "</p>";

    $output .= '<br/>';
    $output .= t('Many Thanks,');
    $output .= '<br/>';
    $output .= t('The VALS team');
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= sprintf('<img src="%1$s/sites/all/themes/media/logo.png">', $server);
    $output .= '<p>';
    return $output;
}

function get_project_rejection_email_body($record, $is_interim, $user_type) {
    //echo var_dump($record);
    $server = _VALS_SOC_FULL_URL;

    $student_username = $record->name; //student userid
    $student_email = $record->mail; // student mail
    $student_institute = $record->i_name; //Institute name where student is from

    $supervisor_username = $record->supervisor_user_name;
    $supervisor_email = $record->supervisor_user_mail;

    $mentor_username = $record->mentor_user_name;
    $mentor_email = $record->mentor_user_mail;
    $mentor_organisation = $record->o_name;

    $project_title = $record->pr_title; //project title
    $proposal_title = $record->title; //proposal title
    $proposal_synopsis = $record->solution_short;
    $alert = '';

    if ($user_type == 'student') {
        $recipient_username = $student_username;
        $alert .= t('A project idea for which you have previously written a proposal for, has now reopened. ');
        $alert .= '<br/><br/>';
        $alert .= t("This is because the project was offered to another student who subsequently chose another. ");
        $alert .= t("If you have not already received a project offer from another project, then it may be worthwhile ");
        $alert .= t("to email or at least add a comment to your exising proposal to say that you are still interested.");
        $alert .= "<br/>";
    } else if ($user_type == 'supervisor') {
        $recipient_username = $supervisor_username;
        $alert .= t('A project idea for which one of your students has previously written a proposal for has now reopened. ');
        $alert .= '<br/><br/>';
        $alert .= t("This is because the project was offered to another student who subsequently chose another. ");
        $alert .= t("If your student has not already received a project offer, then it may be worthwhile for him/her ");
        $alert .= t("to email or at least add a comment to their exising proposal, to say that you are still interested.");
        $alert .= "<br/>";
    } else if ($user_type == 'mentor') {
        $recipient_username = $mentor_username;
        if ($is_interim) {// email mentor only because it wasn't final - rejected PREFERRED INTERIM 
            $alert .= t('A proposal has been withdrawn by a student on the project idea below by a student. This means he/she has now chosen another ' .
                    'offer as their final choice of project for the VALS semester of code. ' .
                    'You had previously marked this proposal as your preferred interim.');
            $alert .= '<br/><br/>';
            $alert .= t('This means that you no longer have a preferred intermin proposal for this project. ' .
                    'However, you can still login and select another interim preferred or make a final offer ' .
                    '(subject to student acceptance) ');
            $alert .= "<br/>";
        } else {// email (mentor) - rejected OFFER - project is therefore reopened and he should choose another proposal
            $alert .= t('A project offer has been rejected on the project idea below by a student you offered it to. This means he/she has now chosen another ' .
                    'offer as their final choice of project for the VALS semester of code. ');
            $alert .= '<br/><br/>';
            $alert .= t("This means that your project idea has reopened and you can select a second choice proposal. ");
            $alert .= "<br/>";
        }
    }

    $output = '';
    $output .= '<p>';
    $output .= t('Hello') . ' ' . $recipient_username . ',';
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= $alert;
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= t('Here is an overview...');
    $output .= '<br/>';

    $output .= "<p>";
    $output .= "<b>" . t('Project idea') . "</b>";
    $output .= "<br/>";
    $output .= $project_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal title') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal synopsis') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_synopsis;
    $output .= "</p>";


    $output .= "<b>" . t('Contact details') . "</b>";
    $output .= '<ul class=\'grouplist\'>';

    $output .= "<li>";
    $output .= t('Student username') . ": <i>" . $student_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student email') . ": <i>" . $student_email . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Student supervisors username') . ": <i>" . $supervisor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student supervisors email') . ": <i>" . $supervisor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Students Institution') . ": <i>" . $student_institute . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Initial mentors username') . ": <i>" . $mentor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors email') . ": <i>" . $mentor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors Organisation') . ": <i>" . $mentor_organisation . "</i>";
    $output .= "</li>";

    $output .= "</ul>";

    $output .= "<p>";
    $output .= tt('Go to <a href="%1$s/dashboard" target="_blank">%1$s/dashboard</a> to view more details.', $server);
    $output .= "</p>";

    $output .= '<br/>';
    $output .= t('Many Thanks,');
    $output .= '<br/>';
    $output .= t('The VALS team');
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= sprintf('<img src="%1$s/sites/all/themes/media/logo.png">', $server);
    $output .= '<p>';
    return $output;
}

function get_project_acceptance_email_body($record, $was_successful, $user_type) {
    //echo var_dump($record);
    $server = _VALS_SOC_FULL_URL;

    $student_username = $record->name; //student userid
    $student_email = $record->mail; // student mail
    $student_institute = $record->i_name; //Institute name where student is from

    $supervisor_username = $record->supervisor_user_name;
    $supervisor_email = $record->supervisor_user_mail;

    $mentor_username = $record->mentor_user_name;
    $mentor_email = $record->mentor_user_mail;
    $mentor_organisation = $record->o_name;

    $project_title = $record->pr_title; //project title
    $proposal_title = $record->title; //proposal title
    $proposal_synopsis = $record->solution_short;
    $alert = '';

    if ($user_type == 'student') {
        $recipient_username = $student_username;
        if ($was_successful) { // This student has ACCEPTED this OFFER
            $alert .= t('Congratulations, you have accepted the project offer below. This means you have chosen this ' .
                    'offer as your final choice of project for the VALS semester of code. ');
            $alert .= '<br/><br/>';
            $alert .= t("You can now optionally complete an agreement between you, your supervisor and your project mentor. ");
            $alert .= t("Your supervisor and mentor will be able to tell you what is required if an agreement is needed.");
            $alert .= "<br/><br/>";
            $alert .= t("You can access your accepted project details ");
            $alert .= "<a href='" . $server . "/dashboard/projects/mine'>" . t('here') . "</a>.";
            $alert .= "<br/>";
            $alert .= t("Or in the future by using the dashboard and clicking the 'My project' link.");
            $alert .= "<br/>";
        } else {// project ACCEPTED BY ANOTHER, project taken
            $alert .= t('Sorry, but unfortunately another student has accepted a project offer based on the project below. This means that this ' .
                    'particular project idea is now taken.');
            $alert .= '<br/><br/>';
            $alert .= t("If you haven't already, it may be worthwhile to try to improve an existing proposal you have made on another project idea, ");
            $alert .= t("and ask for feedback from that mentor. Alternatively, if you wish, you can still create another proposal for a different project idea.");
            $alert .= "<br/><br/>";
        }
    } else if ($user_type == 'supervisor') {
        $recipient_username = $supervisor_username;
        if ($was_successful) { // This student has ACCEPTED this OFFER
            $alert .= t('One of your students has accepted the project offer below. This means he/she has chosen this ' .
                    'offer as their final choice of project for the VALS semester of code. ');
            $alert .= '<br/><br/>';
            $alert .= t("You can now optionally complete an agreement between you, your student and your project mentor. ");
            $alert .= t("This is designed to allow all parties to agree what will be done, major milestones and completion dates. (optional only)");
            $alert .= "<br/><br/>";
            $alert .= t("You can access this accepted project at ");
            $alert .= "<a href='" . $server . "/dashboard/projects/mine'>" . t('here') . "</a>.";
            $alert .= "<br/>";
            $alert .= t("Or in the future by using the dashboard and clicking the 'My projects' link.");
            $alert .= "<br/>";
        } else {// project ACCEPTED BY ANOTHER, project taken
            $alert .= t('Sorry, but unfortunately another student has accepted a project offer based on the project below. One of your students had also created a ' .
                    'proposal for this idea. This means that this particular project idea is now taken and your student will have to seek an alternative. ');
            $alert .= '<br/><br/>';
            $alert .= t("If your student hasn't already, it may be worth while for him/her try to improve an existing proposal they have made on another project idea, ");
            $alert .= t("and ask for feedback from that mentor. Alternatively, if they wish, they can still create another proposal for a different project idea.");
            $alert .= "<br/><br/>";
        }
    } else if ($user_type == 'mentor') {
        $recipient_username = $mentor_username;
        // mentor only gets this when its been accepted - no need to send lots of rejected duplicate emails.
        $alert .= t('A project offer has been accepted on the project idea below by a student you offered it to. This means he/she has now chosen this ' .
                'offer as their final choice of project for the VALS semester of code. ');
        $alert .= '<br/><br/>';
        $alert .= t("You can now optionally complete an agreement between you, your student and your students supervisor. ");
        $alert .= t("This is designed to allow all parties to agree what will be done, major milestones and completion dates. (optional only)");
        $alert .= "<br/><br/>";
        $alert .= t("You can access this accepted project at ");
        $alert .= "<a href='" . $server . "/dashboard/projects/mine'>" . t('here') . "</a>.";
        $alert .= "<br/>";
        $alert .= t("Or in the future by using the dashboard and clicking the 'My projects' link.");
        $alert .= "<br/>";
    }

    $output = '';
    $output .= '<p>';
    $output .= t('Hello') . ' ' . $recipient_username . ',';
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= $alert;
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= t('Here is an overview...');
    $output .= '<br/>';

    $output .= "<p>";
    $output .= "<b>" . t('Project idea') . "</b>";
    $output .= "<br/>";
    $output .= $project_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal title') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal synopsis') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_synopsis;
    $output .= "</p>";


    $output .= "<b>" . t('Contact details') . "</b>";
    $output .= '<ul class=\'grouplist\'>';

    $output .= "<li>";
    $output .= t('Student username') . ": <i>" . $student_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student email') . ": <i>" . $student_email . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Student supervisors username') . ": <i>" . $supervisor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student supervisors email') . ": <i>" . $supervisor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Students Institution') . ": <i>" . $student_institute . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Initial mentors username') . ": <i>" . $mentor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors email') . ": <i>" . $mentor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors Organisation') . ": <i>" . $mentor_organisation . "</i>";
    $output .= "</li>";

    $output .= "</ul>";

    $output .= "<p>";
    $output .= tt('Go to <a href="%1$s/dashboard" target="_blank">%1$s/dashboard</a> to view more details.', $server);
    $output .= "</p>";

    $output .= '<br/>';
    $output .= t('Many Thanks,');
    $output .= '<br/>';
    $output .= t('The VALS team');
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= sprintf('<img src="%1$s/sites/all/themes/media/logo.png">', $server);
    $output .= '<p>';
    return $output;
}

/**
 * Message body for when a new proposal has been added by a student & published
 * @param unknown $record
 * @param unknown $is_update
 * @param unknown $user_type
 * @return string
 */
function get_proposal_email_body($record, $is_update, $user_type) {
    //echo var_dump($record);
    $server = _VALS_SOC_FULL_URL;

    $student_username = $record->name; //student userid
    $student_email = $record->mail; // student mail
    $student_institute = $record->i_name; //Institute name where student is from

    $supervisor_username = $record->supervisor_user_name;
    $supervisor_email = $record->supervisor_user_mail;

    $mentor_username = $record->mentor_user_name;
    $mentor_email = $record->mentor_user_mail;
    $mentor_organisation = $record->o_name;

    $project_title = $record->pr_title; //project title
    $proposal_title = $record->title; //proposal title
    $proposal_synopsis = $record->solution_short;
    $alert = '';

    if ($user_type == 'supervisor') {
        $recipient_username = $record->supervisor_user_name;
        if ($is_update) {
            if ($record->state != 'published') {
                $alert .= t('An existing proposal has been updated by one of your students.');
            } else {
                $alert .= t('An existing proposal has been marked as final version by one of your students.');
            }
        } else {
            if ($record->state != 'published') {
                $alert .= t('A new proposal has been made available by one of your students.');
            } else {
                $alert .= t('A new proposal has been marked as final version by one of your students.');
            }
        }
    } else {
        $recipient_username = $record->mentor_user_name;
        if ($is_update) {
            if ($record->state != 'published') {
                $alert .= t('An existing proposal for one of your projects, has been updated by the student.');
            } else {
                $alert .= t('An existing proposal for one of your projects, has been marked as final by the student.');
            }
        } else {
            if ($record->state != 'published') {
                $alert .= t('A new proposal for one of your projects, has been made available .');
            } else {
                $alert .= t('A new proposal for one of your projects, has been marked as final by the student.');
            }
        }
    }
    $output = '';
    $output .= '<p>';
    $output .= t('Hello') . ' ' . $recipient_username . ',';
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= $alert;
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= t('Here is an overview...');
    $output .= '<br/>';

    $output .= "<p>";
    $output .= "<b>" . t('Project idea') . "</b>";
    $output .= "<br/>";
    $output .= $project_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal title') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal synopsis') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_synopsis;
    $output .= "</p>";


    $output .= "<b>" . t('Contact details') . "</b>";
    $output .= '<ul class=\'grouplist\'>';

    $output .= "<li>";
    $output .= t('Student username') . ": <i>" . $student_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student email') . ": <i>" . $student_email . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Student supervisors username') . ": <i>" . $supervisor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student supervisors email') . ": <i>" . $supervisor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Students Institution') . ": <i>" . $student_institute . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Initial mentors username') . ": <i>" . $mentor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors email') . ": <i>" . $mentor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors Organisation') . ": <i>" . $mentor_organisation . "</i>";
    $output .= "</li>";

    $output .= "</ul>";

    $output .= "<p>";
    $output .= tt('Go to <a href="%1$s/dashboard" target="_blank">%1$s/dashboard</a> to view more details.', $server);
    $output .= "</p>";

    $output .= '<br/>';
    $output .= t('Many Thanks,');
    $output .= '<br/>';
    $output .= t('The VALS team');
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= sprintf('<img src="%1$s/sites/all/themes/media/logo.png">', $server);
    $output .= '<p>';
    return $output;
}

/**
 * This is used to format the email message when inviting new participants
 * @param unknown $entity
 * @param string $subtype
 * @param string $organisation_name
 * @return string
 */
function get_invite_email_body($entity, $subtype = '', $organisation_name = '') {
    $output = '';

    $entity_name = $organisation_name ? : '<i>' . $entity->name . '</i>';
    $db_code_key = '';
    if ($subtype == _MENTOR_TYPE || $subtype == _SUPERVISOR_TYPE || $subtype == _STUDENT_TYPE) {
        $db_code_key = 'code';
    } else {
        $db_code_key = 'owner_code';
    }
    $invite_key = $entity->$db_code_key;
    $server = _VALS_SOC_FULL_URL;

    $output .= '<p>';
    $output .= t('Hello') . ',';
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= tt('You have been invited to participate in the VALS Semester of code program, as a %1$s of %2$s.', $subtype, $entity_name);
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= t('The first step is to sign up...');
    $output .= '<br/>';
    $output .= '<ol class=\'grouplist\'>';

    $output .= "<li>";
    $output .= tt('Go to <a href="%1$s/user/register/%2$s" target="_blank">%1$s/user/register/%2$s</a> to register.', $server, $invite_key);
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Fill in your basic details.');
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Enter the following unique key if it has not been filled yet: ' . $invite_key);
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Click "Create New Account"');
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('You will recieve an email with a one-time login link.');
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Follow that link, log in and set your password.');
    $output .= "</li>";

    $output .= "</ol>";
    $output .= '<br/>';
    $output .= t('Many Thanks,');
    $output .= '<br/>';
    $output .= t('The VALS team');
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= sprintf('<img src="%1$s/sites/all/themes/media/logo.png">', $server);
    $output .= '<p>';
    return $output;
}

function get_project_status_update_email_body($record, $is_final, $is_student, $is_successful) {
    $output = '';
    $server = _VALS_SOC_FULL_URL;

    $student_username = $record->name; //student userid
    $student_email = $record->mail; // student mail
    $student_institute = $record->i_name; //Institute name where student is from

    $supervisor_username = $record->supervisor_user_name;
    $supervisor_email = $record->supervisor_user_mail;

    $mentor_username = $record->mentor_user_name;
    $mentor_email = $record->mentor_user_mail;
    $mentor_organisation = $record->o_name;

    $project_title = $record->pr_title; //project title
    $proposal_title = $record->title; //proposal title
    $proposal_synopsis = $record->solution_short;
    $alert = '';

    if (!$is_student) { // supervisor 
        $recipient_username = $record->supervisor_user_name;
        if ($is_final && !$is_successful) {
            $alert .= t('One of your students has previously made a proposal to the project idea below. ' .
                    'The owner of that project has however decided to offer it to another students proposal. ' .
                    'If the other student decides to accept the offer, this decision becomes final and so unfortunately your students proposal will have been declined. ' .
                    'However, if the other student declines the offer, the project idea will reopen and the mentor may choose another proposal. ' .
                    'This gives you both advance warning in case you wish to submit another proposal for another project idea before the end of the student applications period. ');
        } else if (!$is_final && !$is_successful) {
            $alert .= t('One of your students has previously made a proposal to the project idea below. ' .
                    'The owner of that project has however decided to mark another students proposal as his/her preferred interim one at this time. ' .
                    'This decision is not final however and the owner may in the future decide to change this to another students proposal. ' .
                    'This gives your student an opportunity to revise and/or restructure his/her proposal if they wish to do so. ' .
                    'This also gives you both advance warning in case you wish to submit another proposal for another project idea before the end of the student applications period. ' .
                    'If the project owners decision does become final, you will be informed by email.');
        } else if ($is_final && $is_successful) {
            $alert .= t('One of your students has previously made a proposal to the project idea below. ' .
                    'After reviewing other proposals, the owner of that project has decided to offer your student the chance to undertake that project as their final choice. ' .
                    'This decision is not yet final. The student must now login to the system and confirm he/she wants to accept it. ');
        } else if (!$is_final && $is_successful) {
            $alert .= t('One of your students has previously made a proposal to the project idea below. ' .
                    'The owner of that project has decided to mark your students proposal as his/her preferred interim one at this time. ' .
                    'This decision is not final however and the owner may in the future decide to change this to another students proposal. ' .
                    'If the project owners decision does become final or he/she changes their preferred interim proposal, you will be informed by email.');
        }
    } else {// student 
        $recipient_username = $record->name;
        if ($is_final && !$is_successful) {
            $alert .= t('You have previously made a proposal to the project idea below. ' .
                    'The owner of that project has however decided to offer it to another students proposal. ' .
                    'If the other student decides to accept the offer, this decision becomes final and so unfortunately your proposal will have been declined. ' .
                    'However, if the other student declines the offer, the project idea will reopen and the mentor may choose another proposal. ' .
                    'This gives you advance warning in case you wish to submit another proposal for another project idea before the end of the student applications period. ');
        } else if (!$is_final && !$is_successful) {
            $alert .= t('You have previously made a proposal to the project idea below. ' .
                    'The owner of that project has however decided to mark another students proposal as his/her interim preferred one at this time. ' .
                    'This decision is not final however and the owner may in the future decide to change this to another students proposal. ' .
                    'This gives you an opportunity to revise and/or restructure your proposal if you wish to do so. ' .
                    'This also gives you advance warning in case you wish to submit another proposal for another project idea before the end of the student applications period. ' .
                    'If the project owners decision does become final, you will be informed by email.');
        } else if ($is_final && $is_successful) {
            $alert .= t('You have previously made a proposal to the project idea below. ' .
                    'After reviewing other proposals, the owner of this project has decided to offer you the chance to undertake that project as your final choice. ' .
                    'This decision is not yet final. You must now login to the system and confirm that you want to accept it. ' .
                    'Please login into the VALS semester of code website and go to the dashboard and click "Manage your project offers"' .
                    'Here you can select which offer you wish to take up by clicking on the star icon next to that offer.');
        } else if (!$is_final && $is_successful) {
            $alert .= t('You have previously made a proposal to the project idea below. ' .
                    'The owner of that project has decided to mark your proposal as his/her preferred interim one at this time. ' .
                    'This decision is not final however and the owner may in the future decide to change this to another students proposal. ' .
                    'If the project owners decision does become final or he/she changes their preferred interim proposal, you will be informed by email.');
        }
    }
    $output = '';
    $output .= '<p>';
    $output .= t('Hello') . ' ' . $recipient_username . ',';
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= $alert;
    $output .= '<br/>';
    $output .= '<br/>';
    $text = '';
    if (!$is_student) {
        $text = 'students ';
    }
    $output .= t('For information, here is an overview of your ' . $text . 'submitted proposal...');
    $output .= '<br/>';

    $output .= "<p>";
    $output .= "<b>" . t('Project idea') . "</b>";
    $output .= "<br/>";
    $output .= $project_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal title') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal synopsis') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_synopsis;
    $output .= "</p>";


    $output .= "<b>" . t('Contact details') . "</b>";
    $output .= '<ul class=\'grouplist\'>';

    $output .= "<li>";
    $output .= t('Student username') . ": <i>" . $student_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student email') . ": <i>" . $student_email . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Student supervisors username') . ": <i>" . $supervisor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student supervisors email') . ": <i>" . $supervisor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Students Institution') . ": <i>" . $student_institute . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Initial mentors username') . ": <i>" . $mentor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors email') . ": <i>" . $mentor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors Organisation') . ": <i>" . $mentor_organisation . "</i>";
    $output .= "</li>";

    $output .= "</ul>";

    $output .= "<p>";
    $output .= tt('Go to <a href="%1$s/dashboard" target="_blank">%1$s/dashboard</a> to view more details.', $server);
    $output .= "</p>";

    $output .= '<br/>';
    $output .= t('Many Thanks,');
    $output .= '<br/>';
    $output .= t('The VALS team');
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= sprintf('<img src="%1$s/sites/all/themes/media/logo.png">', $server);
    $output .= '<p>';
    return $output;
}

function get_project_finalisation_email_body($record, $recipient_role) {
    $output = '';
    $server = _VALS_SOC_FULL_URL;

    $student_username = $record->name; //student userid
    $student_email = $record->mail; // student mail
    $student_institute = $record->i_name; //Institute name where student is from

    $supervisor_username = $record->supervisor_user_name;
    $supervisor_email = $record->supervisor_user_mail;

    $mentor_username = $record->mentor_user_name;
    $mentor_email = $record->mentor_user_mail;
    $mentor_organisation = $record->o_name;

    $project_title = $record->pr_title; //project title
    $proposal_title = $record->title; //proposal title
    $proposal_synopsis = $record->solution_short;
    $alert = '';

    if ($recipient_role == _SUPERVISOR_TYPE) {
        $recipient_username = $record->supervisor_user_name;
        $alert .= t('One of your students has finished his/her work for the the project idea below. ' .
                'The mentor can write an evaluation text for the work done. You can finalise the '
                . 'project and reward the student possibly with academic credits.');
    } elseif ($recipient_role == _MENTOR_TYPE) {// student 
        $recipient_username = $record->mentor_user_name; //$record->name;
        $alert .= t('For one of your projects, the student has finished his/her work and signed the '
                . 'projects as completed. Now it is time to review the work done, write an evaluation text, etc.'
                . 'Of course you can contact the student and/or supervisor with'
                . 'the contact details you find below.');
    } else {
        $recipient_username = $record->name;
        $alert .= t('Your mentor and/or supervisor have (also) marked the project '
                . 'you worked on, as completed.');
    }

    $output = '';
    $output .= '<p>';
    $output .= t('Hello') . ' ' . $recipient_username . ',';
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= $alert;
    $output .= '<br/>';
    $output .= '<br/>';
    $text = '';
    if (!$is_student) {
        $text = 'students ';
    }
    $output .= t('For information, here is an overview of your ' . $text . 'submitted proposal...');
    $output .= '<br/>';

    $output .= "<p>";
    $output .= "<b>" . t('Project idea') . "</b>";
    $output .= "<br/>";
    $output .= $project_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal title') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_title;
    $output .= "</p>";

    $output .= "<p>";
    $output .= "<b>" . t('Proposal synopsis') . "</b>";
    $output .= "<br/>";
    $output .= $proposal_synopsis;
    $output .= "</p>";


    $output .= "<b>" . t('Contact details') . "</b>";
    $output .= '<ul class=\'grouplist\'>';

    $output .= "<li>";
    $output .= t('Student username') . ": <i>" . $student_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student email') . ": <i>" . $student_email . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Student supervisors username') . ": <i>" . $supervisor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Student supervisors email') . ": <i>" . $supervisor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Students Institution') . ": <i>" . $student_institute . "</i>";
    $output .= "</li>";

    $output .= '<br/>';

    $output .= "<li>";
    $output .= t('Initial mentors username') . ": <i>" . $mentor_username . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors email') . ": <i>" . $mentor_email . "</i>";
    $output .= "</li>";
    $output .= "<li>";
    $output .= t('Initial mentors Organisation') . ": <i>" . $mentor_organisation . "</i>";
    $output .= "</li>";

    $output .= "</ul>";

    $output .= "<p>";
    $output .= tt('Go to <a href="%1$s/dashboard" target="_blank">%1$s/dashboard</a> to view more details.', $server);
    $output .= "</p>";

    $output .= '<br/>';
    $output .= t('Many Thanks,');
    $output .= '<br/>';
    $output .= t('The VALS team');
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= sprintf('<img src="%1$s/sites/all/themes/media/logo.png">', $server);
    $output .= '<p>';
    return $output;
}

function get_new_comment_email_body($entity_details, $properties, $recipient_username) {
    $output = '';
    $server = _VALS_SOC_FULL_URL;

    $type = $properties['entity_type'];
    $message = $properties['description'];
    $sender_name = $properties['name'];
    $sender_mail = $properties['mail'];

    $title = $entity_details['title']; //entity title
    $owner_name = $entity_details['name'];
    $owner_mail = $entity_details['mail'];

    $legend = tt('A new message was posted for the following %1$s', $type);
    $legend .= '<br/><br/>';
    $legend .= '<i>' . $title . '</i>';

    $output .= t('Hello') . ' ' . $recipient_username . ',';
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= $legend;
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= $message;
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= t('Message posted by') . ' ' . $sender_name . ' (' . $sender_mail . ')';
    $output .= '<br/>';
    $output .= tt('Owner of %1$s', $type) . ' ' . $owner_name . ' (' . $owner_mail . ')';
    $output .= '<br/>';
    $output .= '<br/>';

    $output .= "<p>";
    $output .= tt('Go to <a href="%1$s/dashboard" target="_blank">%1$s/dashboard</a> to view more details.', $server);
    $output .= "</p>";

    $output .= '<br/>';
    $output .= t('Many Thanks,');
    $output .= '<br/>';
    $output .= t('The VALS team');
    $output .= '<br/>';
    $output .= '<br/>';
    $output .= sprintf('<img src="%1$s/sites/all/themes/media/logo.png">', $server);
    $output .= '<p>';
    return $output;
}
