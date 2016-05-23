<?php

module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail_messages');

/**
 * Use this function to send ONE email immediately,
 * rather than use the cron mail queue
 * Do not use when the user is waiting for a response in the UI, instead use
 * 			  vals_soc_send_emails_cron($items)
 *
 * Example of what an $item is...
 * $item[0] = array('key' => 'vals_soc_invite_new_user', 'to' => $email, 'from' => NULL, 'subject' => $subject, 'body' => $body);
 * when $from is null, the system admin emails address is used
 */
function vals_soc_send_single_email_now($item) {
    if (!isset($item['subject'])) { // system messages currently dont have subjects attached at this stage.
        $mail_results = '';
        $message = drupal_mail('vals_soc', $item['key'], $item['to'], language_default());
        if (!empty($message['result'])) {
            @$mail_results .= 'Message ' . $item['message_type'] . ' sent!!.' . '<br/>';
        }
        watchdog('debug', $mail_results);
    } else { // Preferred method for sending out mails based on events.
        $message = drupal_mail('vals_soc', $item['key'], $item['to'], language_default(), array(
            'body' => $item['body'],
            'subject' => $item['subject'],
            $item['from'],
            TRUE,
            'plain' => isset($item['plain']) ? $item['plain'] : false)
        );

        if (!empty($message['result'])) {
            watchdog('debug', 'Email sent. Result: ' . $message['result']);
            return true;
        } else {
            watchdog('debug', 'Problem sending email.');
            return false;
        }
    }
}

/**
 * Use this function to send an array of emails immediately,
 * Do not use when the user is waiting for a response in the UI.
 *
 * Example of what an $items are...
 * $items[0] = array('key' => 'vals_soc_invite_new_user', 'to' => $email, 'from' => NULL, 'subject' => $subject, 'body' => $body);
 * $items[1] = array('key' => 'vals_soc_invite_new_user', 'to' => $email2, 'from' => NULL, 'subject' => $subject, 'body' => $body);
 * etc....
 * when $from is null, the system admin emails address is used
 */
function vals_soc_send_emails_now($items) {
    $result = TRUE;
    //The smtp module has the nasty habit of ouputting messages directly
    ob_start();
    foreach ($items as $item) {
        if (!vals_soc_send_single_email_now($item)) {
            $result = FALSE;
            drupal_set_message(tt('Could not send mail to: "%1$s"', $item['to']), 'error');
        }
    }
    $messages = ob_get_clean();
// 	if (!$result){
// 		drupal_set_message($messages, 'error');
// 	}
    return $result;
}

/**
 * Use this function to send an array of emails on the cron email
 * queue which means they will be sent later.
 * Use this function when the UI has called for an email to sent
 *
 * Example of what an $items are...
 * $items[0] = array('key' => 'vals_soc_invite_new_user', 'to' => $email, 'from' => NULL, 'subject' => $subject, 'body' => $body);
 * $items[1] = array('key' => 'vals_soc_invite_new_user', 'to' => $email2, 'from' => NULL, 'subject' => $subject, 'body' => $body);
 * etc....
 * when $from is null, the system admin emails address is used
 */
function vals_soc_send_emails_cron($items) {
    // put them on the queue...
    $queue = DrupalQueue::get('vals_soc_cron_email');
    foreach ($items as $item) {
        $queue->createItem($item);
    }
}

/**
 * This is the callback function for all entries posted on to the
 * 'vals_soc_cron_email' queue
 * and any email sent via 'vals_soc_send_emails_cron($items)' above
 * 
 * We don't call this directly ourselves as each email item is
 * dequeued one by one from 'DrupalQueue::get('vals_soc_cron_email');'
 * and this function is then executed.
 */
function vals_soc_cron_email_handler($item) {
    // because cron has run this function
    // we can send any email now...
    vals_soc_send_single_email_now($item);
}

/**
 * TODO (Paul) - can we refactor this out
 * Handle mail requests
 * @param unknown $key
 * @param unknown $message
 * @param unknown $params
 */
function vals_soc_mail_handler($key, &$message, $params) {
    //All these messages are saved with rte textareas and so already in html format
    //We expect all drupal_mail calls or vals_soc_cron_email_handler, or vals_soc_send_emails_cron calls to be with
    //items having a 'subject', 'body', 'to' field as a minimum. Optional are key and from. From will be the system 
    //return address (variable_get('system_mail')) and the key is only important to create a body here.
    $html_format = true;
//    $message['from'] = constant('_VALS_ADMIN_EMAIL_ADDRESS');
//    $default_from = variable_get('site_mail', ini_get('sendmail_from'));
//
//    if (TRUE || $message['from'] == $default_from){
//      $message['from'] = '"'. variable_get('site_name', 'Vals VPS Admin') .'" <'. $default_from .'>';
//      $message['headers']['From'] = $message['headers']['Sender'] = $message['headers']['Return-Path'] = $message['headers']['Errors-To'] = $message['headers']['Reply-To'] = $message['from'];
//    }

    $message['from'] = $message['headers']['From'] = '"Vals VPS Admin" <no-reply@' . $_SERVER['HTTP_HOST'] . '>';
    switch ($key) {
        case 'vals_soc_notify_new_comment':
            $message['subject'] = $params['subject'];
            $message['body'][] = $params['body'];
            break;
        case 'vals_soc_notify_project_status_change':
            $message['subject'] = $params['subject'];
            $message['body'][] = $params['body'];
            break;
        case 'vals_soc_notify_new_proposal':
            $message['subject'] = $params['subject'];
            $message['body'][] = $params['body'];
            break;
        case 'vals_soc_invite_new_user':
            $message['subject'] = $params['subject'];
            $message['body'][] = $params['body'];
            break;
        case 'vals_soc_email_accepted_organisation':
            $message['subject'] = t('Organisation application for VALS summer of code');
            $message['body'][] = variableGetFromStruct('vals_accepted_organisations_message', 'value');
            if (isset($params['headers']) && is_array($params['headers'])) {
                $message['headers'] += $params['headers'];
            }
            break;
        case 'vals_soc_email_rejected_organisation':
            $message['subject'] = t('Organisation application for VALS summer of code');
            $message['body'][] = variableGetFromStruct('vals_rejected_organisations_message', 'value');
            break;

        case 'vals_soc_email_mentor_welcome':
            $message['subject'] = t('Welcome mentor to the VALS summer of code');
            $message['body'][] = variableGetFromStruct('vals_mentor_welcome_message', 'value');
            break;

        case 'vals_soc_email_student_welcome':
            $message['subject'] = t('Welcome student to the VALS summer of code');
            $message['body'][] = variableGetFromStruct('vals_student_welcome_message', 'value');
            break;

        case 'vals_soc_accepted_students':
            $message['subject'] = t('Your project proposal to the VALS summer of code');
            $message['body'][] = variableGetFromStruct('vals_accepted_students_message', 'value');
            break;

        case 'vals_soc_rejected_students':
            $message['subject'] = t('Your project proposal to the VALS summer of code');
            $message['body'][] = variableGetFromStruct('vals_rejected_students_message', 'value');
            break;
        default:
            if ($message['module'] == 'vals_soc') {
                $message['subject'] = $params['subject'];
                $message['body'][] = $params['body'];
                if (isset($params['plain']) && $params['plain']) {
                    $html_format = false;
                }
            } else {
                $html_format = false;
            }
            break;
    }
    if ($html_format) {
        //change header format for html formatted emails (in effect all the vals_soc emails now)
        //So the headers below are all the same as set by the Drupal mail system except for the 
        //content-type which is html.
        $message['headers'] = array(
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=iso-8859-1; format=flowed',
            'Content-Transfer-Encoding' => '8Bit',
            'X-Mailer' => 'Drupal',
        );
    }
}

/**
 * Add params to a messages header
 * @param $message the parameters for the message: array(headers, body, subject, to, ....)
 */
function vals_soc_mail_alter_handler(&$message) {
    //We now directly apply these header changes for the vals_soc mails only in the function
    //vals_soc_mail_handler.
}

/**
 * Email Notifies ALL people who are owner or have commented previously on an entity (project/proposal)
 * that a new comment has beeen added by someone.
 * @param unknown $entity_details
 * @param unknown $thread_details
 * @param unknown $properties
 */
function notify_all_of_new_comment($entity_details, $thread_details, $properties) {
    /*
      echo '<br/>****************entity_details*************************<br/>';
      echo var_dump($entity_details);
      echo '<br/>****************thread_details*************************<br/>';
      echo var_dump($thread_details);
      echo '<br/>****************properties*************************<br/>';
      echo var_dump($properties);
     */
    $items = array();
    $number_of_emails_count = 0;

    // email the owner of this entity
    $subject = tt('VALS Notification: New comment added to your %1$s', $properties['entity_type']);
    $owner_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $entity_details['mail']);
    $recipients = array();
    array_push($recipients, $entity_details['mail']); // push the owners mail in first
    $message_body_owner = get_new_comment_email_body($entity_details, $properties, $entity_details['name']);
    $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_new_comment', 'to' => $owner_recipient, 'from' => NULL,
        'subject' => $subject, 'body' => $message_body_owner);

    //if the entity is a proposal then the owner is mailed above (who is a student), but not the 
    //mentor or supervisor unless they have already commented in the loop below
    //so manually add them in here so that they know that someone has commented on this proposal.
    // This does not affect project comments
    if ($properties['entity_type'] == 'proposal') {
        // mail the mentor of the original project idea
        $subject = tt('VALS Notification: New comment added to a %1$s based on one of your project ideas', $properties['entity_type']);
        $mentor_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $entity_details['mentor_user_mail']);
        array_push($recipients, $entity_details['mentor_user_mail']); // push the mentors mail
        $message_body_owner = get_new_comment_email_body($entity_details, $properties, $entity_details['mentor_user_name']);
        $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_new_comment', 'to' => $mentor_recipient, 'from' => NULL,
            'subject' => $subject, 'body' => $message_body_owner);

        // now mail the supvisor of the student
        $subject = tt('VALS Notification: New comment added to a %1$s submitted by one of your students', $properties['entity_type']);
        $supervisor_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $entity_details['supervisor_user_mail']);
        array_push($recipients, $entity_details['supervisor_user_mail']); // push the supervisors mail
        $message_body_owner = get_new_comment_email_body($entity_details, $properties, $entity_details['supervisor_user_name']);
        $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_new_comment', 'to' => $supervisor_recipient, 'from' => NULL,
            'subject' => $subject, 'body' => $message_body_owner);
    }

    // now loop around all other people who have previously commented on this entity
    $subject = tt('VALS Notification: New comment added to a %1$s you have previously commented on', $properties['entity_type']);
    foreach ($thread_details as $thread_detail) {
        // make sure we only send one email per user
        if (!in_array($thread_detail['mail'], $recipients)) {
            array_push($recipients, $thread_detail['mail']);
            $participant_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $thread_detail['mail']);
            $message_body = get_new_comment_email_body($entity_details, $properties, $thread_detail['name']);
            $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_new_comment', 'to' => $participant_recipient, 'from' => NULL,
                'subject' => $subject, 'body' => $message_body);
        }
    }
    vals_soc_send_emails_cron($items);
}

/**
 * Emails anybody who has put a proposal forward for a project, when the mentor sets a particular
 * proposal to preferred or sets it finally chosen. (Both the student & his/her supervisor receives the email)
 * as well as any rejected/not preferred proposal owners/supervisors
 * @param unknown $all_proposals
 * @param unknown $successful_proposal_id
 * @param unknown $is_final
 */
function notify_students_and_supervisors_of_project_status_update($all_proposals, $successful_proposal_id, $is_final) {
    $items = array();
    $number_of_emails_count = 0;
    $subject = t('VALS Notification: A prospective mentor has set a proposal status for a project');
    foreach ($all_proposals as $proposal) {
        if ($proposal->proposal_id != $successful_proposal_id) {
            // unsuccessful ones
            $message_body_student = get_project_status_update_email_body($proposal, $is_final, true, false);
            $message_body_supervisor = get_project_status_update_email_body($proposal, $is_final, false, false);
        } else {
            // the successful one
            $message_body_student = get_project_status_update_email_body($proposal, $is_final, true, true);
            $message_body_supervisor = get_project_status_update_email_body($proposal, $is_final, false, true);
        }
        $student_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->mail);
        $supervisor_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->supervisor_user_mail);
        $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_project_status_change', 'to' => $student_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_student);
        $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_project_status_change', 'to' => $supervisor_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_supervisor);
    }

    vals_soc_send_emails_cron($items);
}

/**
 * Emails anybody (student/mentor) who has to do with the proposals passed when it is about time to round up
 * the project and evaluate it.
 * @param unknown $all_proposals
 */
function notify_all_of_project_deadline_approaching($all_proposals) {
    $items = array();
    $number_of_emails_count = 0;
    $subject = t('VALS Notification: The project below is near to end');
    foreach ($all_proposals as $proposal) {
        if (!$proposal['state'] == 'finalised') {
            $message_body_student = get_project_final_call_email_body($proposal, _STUDENT_TYPE);
            $message_body_mentor = get_project_final_call_email_body($proposal, _MENTOR_TYPE);

            $student_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->mail);
            $mentor_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->mentor_user_mail);
            $items[$number_of_emails_count++] = array('to' => $student_recipient,
                'from' => NULL, 'subject' => $subject, 'body' => $message_body_student);
            $items[$number_of_emails_count++] = array('to' => $supervisor_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_mentor);
        }
    }

    vals_soc_send_emails_cron($items);
}

function notify_all_of_project_finalisation($proposal, $signer_role) {
    $items = array();
    $number_of_emails_count = 0;
    $subject = t('VALS Notification: Someone has marked a project (you are involved in) as ready');
    switch ($signer_role) {
        case _STUDENT_TYPE:
            $m_body_1 = get_project_finalisation_email_body($proposal, _SUPERVISOR_TYPE);
            $m_body_2 = get_project_finalisation_email_body($proposal, _MENTOR_TYPE);
            $r_1 = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->supervisor_user_mail);
            $r_2 = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->mentor_user_mail);
            break;
        case _SUPERVISOR_TYPE:
            $m_body_1 = get_project_finalisation_email_body($proposal, _STUDENT_TYPE);
            $m_body_2 = get_project_finalisation_email_body($proposal, _MENTOR_TYPE);
            $r_1 = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->mail);
            $r_2 = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->mentor_user_mail);
            break;
        case _MENTOR_TYPE:
            $m_body_1 = get_project_finalisation_email_body($proposal, _SUPERVISOR_TYPE);
            $m_body_2 = get_project_finalisation_email_body($proposal, _STUDENT_TYPE);
            $r_1 = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->supervisor_user_mail);
            $r_2 = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->mail);
            break;
    }
    $items[] = array('key' => 'vals_soc_notify_project_finalisation', 'to' => $r_1,
        'from' => NULL, 'subject' => $subject, 'body' => $m_body_1, 'module' => 'vals_soc');
    $items[] = array('key' => 'vals_soc_notify_project_finalisation', 'to' => $r_2,
        'from' => NULL, 'subject' => $subject, 'body' => $m_body_2, 'module' => 'vals_soc');

    vals_soc_send_emails_cron($items);
}

function notify_all_of_project_offer_acceptance($proposal, $proposal_id, $was_successful) {

    $student_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->mail);
    $supervisor_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->supervisor_user_mail);
    $mentor_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->mentor_user_mail);
    $previously_rejected = ($proposal->state == "rejected" ? true : false);

    $items = array();
    $number_of_emails_count = 0;

    if ($was_successful) { // email (student, supervisor, mentor) that this project has now been accepted by this student
        $subject = t('VALS Notification: Congratulations, you have accepted a project offer');
        $message_body_student = get_project_acceptance_email_body($proposal, $was_successful, 'student');
        $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_project_acceptance_change', 'to' => $student_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_student);
        //echo '<hr/><br/>student:'. $proposal_title . ' : ' . $project_title . ' : ' . $student_recipient . ' :ACCEPTED OFFER';

        $subject = t('VALS Notification: Congratulations, one of your students has accepted a project offer');
        $message_body_supervisor = get_project_acceptance_email_body($proposal, $was_successful, 'supervisor');
        $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_project_acceptance_change', 'to' => $supervisor_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_supervisor);
        //echo '<hr/><br/>supervisor:'. $proposal_title . ' : ' . $project_title . ' : ' . $supervisor_recipient . ' :ACCEPTED OFFER';

        $subject = t('VALS Notification: A student has accepted your project offer');
        $message_body_mentor = get_project_acceptance_email_body($proposal, $was_successful, 'mentor');
        //echo '<hr/><br/>mentor:'. $proposal_title . ' : ' . $project_title . ' : ' . $mentor_recipient . ' :ACCEPTED OFFER';
        $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_project_acceptance_change', 'to' => $mentor_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_mentor);
    } else { // email (student & supervisor) that this project has now been accepted by another student and is finally gone!
        if (!$previously_rejected) {
            $subject = t('VALS Notification: Another student has now taken a project you were interested in');
            $message_body_student = get_project_acceptance_email_body($proposal, $was_successful, 'student');
            $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_project_acceptance_change', 'to' => $student_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_student);
            //echo '<hr/><br/>student:'. $proposal_title . ' : ' . $project_title . ' : ' . $student_recipient . ' :ACCEPTED BY ANOTHER, project taken';

            $subject = t('VALS Notification: Another student has now taken a project which one your students was interested in');
            $message_body_supervisor = get_project_acceptance_email_body($proposal, $was_successful, 'supervisor');
            $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_project_acceptance_change', 'to' => $supervisor_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_supervisor);
            //echo '<hr/><br/>supervisor:'. $proposal_title . ' : ' . $project_title . ' : ' . $supervisor_recipient . ' :ACCEPTED BY ANOTHER, project taken';
        }
    }
    vals_soc_send_emails_cron($items);
}

function notify_all_of_project_offer_rejection($proposal, $proposal_id, $is_interim) {
    $student_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->mail);
    $supervisor_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->supervisor_user_mail);
    $mentor_recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $proposal->mentor_user_mail);
    $previously_rejected = ($proposal->state == "rejected" ? true : false);

    $items = array();
    $number_of_emails_count = 0;

    if ($is_interim) {  // email mentor only because it wasn't final - rejected PREFERRED INTERIM 
        $subject = t('VALS Notification: A student whose proposal was your preferred has accepted another project offer');
        $message_body_mentor = get_project_rejection_email_body($proposal, $is_interim, 'mentor');
        //echo '<hr/><br/>mentor:'. $proposal_title . ' : ' . $project_title . ' : ' . $mentor_recipient . ' :WITHDRAWN, choose another INTRIM';
        $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_project_rejection_change', 'to' => $mentor_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_mentor);
    } else {  // email (mentor) - rejected OFFER - project is therefore reopened and he should choose another proposal
        // email this proposal (student & supervisor) to say that the project has reopended and the mentor can choose another, possibly theirs
        if (!$previously_rejected) {
            $subject = t('VALS Notification: A project idea you submitted a proposal for has now reopened');
            $message_body_student = get_project_rejection_email_body($proposal, $is_interim, 'student');
            $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_project_rejection_change', 'to' => $student_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_student);
            //echo '<hr/><br/>student:'. $proposal_title . ' : ' . $project_title . ' : ' . $student_recipient . ' :REJECTED OFFER';

            $subject = t('VALS Notification: A project has reopened for which one of your students was previously interested in');
            $message_body_supervisor = get_project_rejection_email_body($proposal, $is_interim, 'supervisor');
            $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_project_rejection_change', 'to' => $supervisor_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_supervisor);
            //echo '<hr/><br/>supervisor:'. $proposal_title . ' : ' . $project_title . ' : ' . $supervisor_recipient . ' :REJECTED OFFER';
            $subject = t('VALS Notification: A student has rejected your project offer in favour of another');
            $message_body_mentor = get_project_rejection_email_body($proposal, $is_interim, 'mentor');
            //echo '<hr/><br/>mentor:'. $proposal_title . ' : ' . $project_title . ' : ' . $mentor_recipient . ' :REJECTED OFFER, choose another FINAL';
            $items[$number_of_emails_count++] = array('key' => 'vals_soc_notify_project_rejection_change', 'to' => $mentor_recipient, 'from' => NULL, 'subject' => $subject, 'body' => $message_body_mentor);
        }
    }
    vals_soc_send_emails_cron($items);
}

function notify_student_and_supervisor_of_proposal_rejection_by_mentor($record) {
    $subject = t('VALS Proposal Notification');
    $items = array();
    if (isset($record->mail) && isset($record->name)) {
        $student_body = get_proposal_rejection_email_body($record, 'student');
        $recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $record->mail);
        $items[0] = array('key' => 'vals_soc_notify_new_proposal', 'to' => $recipient, 'from' => NULL, 'subject' => $subject, 'body' => $student_body);
    } else {
        drupal_set_message(t('unable to send student proposal rejection notification email: missing email or username'));
    }

    if (isset($record->supervisor_user_mail) && isset($record->supervisor_user_name)) {
        $supervisor_body = get_proposal_rejection_email_body($record, 'supervisor');
        $recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $record->supervisor_user_mail);
        $items[1] = array('key' => 'vals_soc_notify_new_proposal', 'to' => $recipient, 'from' => NULL, 'subject' => $subject, 'body' => $supervisor_body);
    } else {
        drupal_set_message(t('unable to send supervisor proposal rejection notification email: missing email or username'));
    }

    vals_soc_send_emails_cron($items);
}

function notify_mentor_and_supervisor_of_proposal_update($record, $is_update) {
    // TODO - fix the name missing on one of the emails
    $subject = t('VALS Proposal Notification');
    $items = array();
    if (isset($record->supervisor_user_mail) && isset($record->supervisor_user_name)) {
        $supervisor_body = get_proposal_email_body($record, $is_update, 'supervisor');
        $recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $record->supervisor_user_mail);
        $items[0] = array('key' => 'vals_soc_notify_new_proposal', 'to' => $recipient, 'from' => NULL, 'subject' => $subject, 'body' => $supervisor_body);
    } else {
        drupal_set_message(t('Unable to send supervisor proposal notification email: missing email or username'));
    }

    if (isset($record->mentor_user_mail) && isset($record->mentor_user_name)) {
        if ($record->state != 'draft') { // dont send the mentor a mail if the proposal is only in draft state
            $mentor_body = get_proposal_email_body($record, $is_update, 'mentor');
            $recipient = (_DEBUG ? _TEST_EMAIL_ADDRESS : $record->mentor_user_mail);
            $items[1] = array('key' => 'vals_soc_notify_new_proposal', 'to' => $recipient, 'from' => NULL, 'subject' => $subject, 'body' => $mentor_body);
        }
    } else {
        drupal_set_message(t('Unable to send supervisor proposal notification email: missing email or username'));
    }

    vals_soc_send_emails_cron($items);
}

/* * ****************************************************************************************************
 *  TESTING - used by the admin at present to send the system email messages out to a test email address 
 * **************************************************************************************************** */

/**
 * Fire out the system test emails
 * @param $to - user email to send the test mails to
 */
function vals_soc_handle_test_emails($to, $use_cron = TRUE) {
    drupal_set_message(t('vals_soc_handle_cron_test_emails called'));
    $from = '"Vals VPS Admin" <no-reply@' . $_SERVER['HTTP_HOST'] . '>';

    $items = array();
    $items[0] = array('to' => $to, 'from' => $from, 'key' => 'vals_soc_email_accepted_organisation');
    $items[1] = array('to' => $to, 'from' => $from, 'key' => 'vals_soc_email_rejected_organisation');
    $items[2] = array('to' => $to, 'from' => $from, 'key' => 'vals_soc_email_mentor_welcome');
    $items[3] = array('to' => $to, 'from' => $from, 'key' => 'vals_soc_email_student_welcome');
    $items[4] = array('to' => $to, 'from' => $from, 'key' => 'vals_soc_accepted_students');
    $items[5] = array('to' => $to, 'from' => $from, 'key' => 'vals_soc_rejected_students');
    if ($use_cron) {
        vals_soc_send_emails_cron($items);
    } else {
        vals_soc_send_emails_now($items);
    }
}
