function refreshTabs(json_data, args) {
    var targ = '';
    var type = '';
    var handler = '';
    var container = '';
    if (args.length == 0) {
        alertdev('There are missing arguments to refresh the tabs');
        return;
    }
    if (arguments.length > 1 && args) {
        type = args[0];
        targ = (args.length > 1) && args[1] ? 'msg_' + args[1] : '';
        handler = (args.length > 2 && args[2]) ? args[2] : type;
        container = ((args.length > 3) && args[3]) ? args[3] : 'admin_container';
        follow_action = ((args.length > 4) && args[4]) ? args[4] : 'show';
    }
    if (!handler) {
        alertdev('No handler supplied in target function');
        return false;
    }

    if (json_data && (json_data.result !== 'error')) {
        var show_action = altSub(json_data, 'show_action', 'administer');
        var new_tab = altSub(json_data, 'new_tab', false);
        if (!new_tab)
            new_tab = 0;
        post_data = {type: type, show_action: show_action, new_tab: new_tab};
        if (json_data.extra) {
            for (var p in json_data.extra) {
                post_data[p] = json_data.extra[p];
            }
        }
        ajaxCall(handler, follow_action, post_data, 'handleContentAndMessage', 'html',
                [container, 'ajax_msg', json_data.msg]);
    } else {
        //We found an error. Try to show it
        if ((!targ) || !$jq('#' + targ).length) {
            targ = 'ajax_msg';
            alertdev('You forgot to place an error div in the content of the tab: required msg_<target> ' +
                    'or it could not be found. Searched actually for ' + targ);
        } else {
            //targ = 'msg_'+targ;
        }

        if (json_data && typeof json_data.error != 'undefined') {
            ajaxError(targ, json_data.error);
        } else {
            alertdev('Some error occured but no message was set.');
        }
    }
}

function refreshSingleTab(json_data, args) {
    var target = '';
    var type = '';
    var handler = '';
    if (arguments.length > 1 && args && (args.length > 2)) {
        type = args[0];
        target = args[1];
        handler = args[2];
    }
    if (!handler) {
        alertdev('No handler supplied in target function');
        return false;
    }
    //Get the id and the type at first from the 
    var id = json_data.id;
    var type = json_data.type;
    console.log('hoe zit het ', json_data);
    var follow_up_action = json_data.show_action ? json_data.show_action : 'view';
    if (json_data && (json_data.result !== 'error')) {
        ajaxCall(handler, follow_up_action, {id: id, type: type, target: target}, 'handleContentAndMessage',
                'html', [target, 'msg_' + target, json_data.msg]);
    } else {
        if (json_data && typeof json_data.error != 'undefined') {
            ajaxError('msg_' + target, json_data.error);
        } else {
            alertdev('Some error occured but no "error" message was set.');
        }

    }
}

function refreshSingleComment(json_data, args) {
    var target = '';
    var type = '';
    var handler = '';
    if (arguments.length > 1 && args && (args.length > 2)) {
        type = args[0];
        target = args[1];
        handler = args[2];
        parent_id = args[3];
    }
    if (!handler) {
        alertdev('No handler supplied in target function');
        return false;
    }
    //Get the id and the type
    var id = json_data.id;
    var type = json_data.type;
    if (json_data && (json_data.result !== 'error')) {
        showText = 'show';
        ajaxCall(handler, 'view', {id: id, type: type, target: target}, 'handlePostDOMUPdate',
                'html', [json_data.msg, id, parent_id, type, json_data.entity_id]);
    } else {
        if (json_data && typeof json_data.error != 'undefined') {
            ajaxError('msg_' + target, json_data.error);
        } else {
            alertdev('Some error occured but no message was set.');
        }
    }
}

function handlePostDOMUPdate(result, args) {
    var msg_content = args[0];
    var id = args[1];
    var parent_id = args[2];
    var entity_type = args[3];
    var entity_id = args[4];
    // try to increment the thread count on this entity.
    try {
        $total_count = $jq('#comment-total-' + entity_type + "-" + entity_id);
        $total_count.text(parseInt($total_count.text()) + 1);
    } catch (err) {
        console.log('In handlePostDOMUPdate:' + err);
    }
    if (parent_id == '0' || parent_id == '') {
        //$jq('.existing-comments-container').append(result);
        $jq('#existing-comments-container-' + entity_id + '-' + entity_type).append(result);
        ajaxMessage('msg_threaded-comment-wrapper-' + id, msg_content);
    } else {
        $jq('#threaded-comment-wrapper-' + parent_id).append(result);
        $jq('#reply-comment-form-' + parent_id).toggle();
        ajaxMessage('msg_threaded-comment-wrapper-' + id, msg_content);
    }
}

function formResult(data, args) {
    var target = args[0];
    //todo: we want jquery to wait for the dom to be ready until ckeditor call
    if (Obj(target).html(data)) {
        //replaces only the textareas inside the target
        transform_into_rte(target);
    }
}

function jsonFormResult(data, args) {
    var target = args[0];
    if (data.result == "error") {
        ajaxAppend(data.error, target, 'error');
    } else {
        //todo: we want jquery to wait for the dom to be ready until ckeditor call
        if (Obj(target).html(data.result)) {
            //replaces only the textareas inside the target
            transform_into_rte(target);
        }
    }
}

function transform_into_rte() {
    if (arguments.length) {
        target = arguments[0];
        $jq('#' + target + ' form textarea').each(function (index, element) {
            CKEDITOR.replace(element);
        });
    } else {
        CKEDITOR.replaceAll();
    }
}

//Gets content and waits for content to be placed to handle the messages which target is inside the new content
function handleContentAndMessage(result, args) {
    var content_target = args[0];
    var inserting = ajaxInsert(result, content_target);
    if (inserting && args.length > 1) {
        var msg_target = args[1];
        var msg_content = args[2];
        ajaxMessage(msg_target, msg_content);
    }
}

//Gets content and waits for content to be placed to handle the messages which target is inside the new content
function handleMessage(result, args) {
    var target = args[0];
    if (result) {
        if (result.result == "error") {
            ajaxError(target, result.error);
        } else {
            ajaxMessage(target, result.msg);
        }
    } else {
        alertdev('Not a valid result');
    }
}

function handleMessagesForm(result, args) {
    var target = args[0];
    var formid = args[1];
    if (result) {
        if (result.result == "error") {
            ajaxError(target, result.error);
        } else {
            ajaxMessage(target, result.msg);
            $jq("#" + formid).remove();
        }
    } else {
        alertdev('Not a valid result');
    }
}

function handleResult(result, args) {
    var target = args[0];
    var before = (args.length > 1 ? args[1] : '');
    var replace_target = (args.length > 2 ? args[2] : false);
    if (result) {
        if (result.result == "error") {
            ajaxAppend(result.error, target, 'error', before);
        } else {
            if (is_string(result.result)) {
                ajaxInsert(result.result, target);
            } else {
                if (typeof result.msg != 'undefined') {
                    if (replace_target) {
                        Obj(target).html('');
                        before = '';
                    }
                    ajaxAppend(result.msg, target, 'status', before);
                } else {
                    alertdev('The action succeeded.');
                }
            }
        }
    } else {
        alertdev('Not a valid result');
    }
}

function handleDeleteResult(result, args) {
    if (result) {
        if (result.result == "error") {
            Drupal.CTools.Modal.dismiss();//In some cases an admin wants to delete a proposal
            ajaxAppend(result.error, result.target, 'error', result.before);
        } else {
            if (is_string(result.result)) {
                ajaxInsert(result.result, result.target);
            } else {
                console.log('komt in dit geval');
                console.log('in handleDeleteResult result en args', result, args);
                if (typeof result.msg != 'undefined') {
                    ajaxAppend(result.msg, result.target, 'status', result.before);
                }

                if (result.replace_target) {
                    if (arguments.length > 1 && args && (args.length > 0)) {
                        //[\'our_content\', \'proposal\', \'myproposal_page\']);"
                        target = args[0];
                        handler = args[1];
                        follow = args[2];
                        if (follow) {
                            ajaxCall(handler, follow, {}, target);
                        }

                    } else {
                        alertdev('No target supplied in target function');
                        Obj(result.target).html('');
                        before = '';
                    }
                } else {
                    alertdev('The action succeeded.');
                    Drupal.CTools.Modal.dismiss();
                    var row = $jq("tr[data-record-key='" + result.id + "']");
                    if (row) {
                        row.remove();
                    } else {
                        alertdev('it could not find the row : data-record-key = ' + result.id);
                    }
                }
            }

        }
    } else {
        alertdev('Not a valid result');
    }
}

function handleSaveResult(result, args) {
    var args_valid = false;
    if (arguments.length > 1 && args && (args.length > 0)) {
        target = args[0];
        args_valid = true;
    } else {
        alertdev('No target supplied in target function');
    }

    if (!args_valid) {
        alertdev('Something wrong with the arguments to handleSaveResult');
        return false;
    }
    if (result) {
        if (result.result == "error") {
            ajaxAppend(result.error, target, 'error', 'edit_proposal');
        } else {
            if (result.result == "OK") {
                if (target == 'our_content') {
                    ajaxCall('proposal', 'myproposal_page', {id: result.id, target: target, new_tab: result.id}, target);
                    ajaxMessage('ajax_msg', result.msg);
                } else {
                    if (target == 'modal') {
                        getProposalDetail(result.id, target, result.msg);
                    } else {
                        ajaxCall('proposal', 'view', {id: result.id, target: target, new_tab: result.id},
                        'handleContentAndMessage', 'html', [target, 'msg_' + target, result.msg]);
                        //ajaxMessage('ajax_msg', result.msg);
                    }
                }
            } else {
                alertdev('The action did not result in error but also not in OK. succeeded??');
            }
        }
    } else {
        alertdev('Not a valid result');
    }

}

function handleSubmitResult(result, args) {
    var args_valid = false;
    if (arguments.length > 1 && args && (args.length > 0)) {
        target = args[0];
        args_valid = true;
    } else {
        alertdev('No target supplied in target function');
    }

    if (!args_valid) {
        alertdev('Something wrong with the arguments to handleSubmitResult');
        return false;
    }
    if (result) {
        if (result.result == "error") {
            ajaxAppend(result.error, target, 'error', 'vals-soc-proposal-form');
        } else {
            if (result.result == "OK") {
                var target = result.target;
                if (target) {
                    if (target == 'our_content') {
                        ajaxCall('proposal', 'myproposal_page', {id: result.id, target: target}, target);
                    } else {
                        console.log('Replacing the following target ' + target + ' with submitted proposal');
                        ajaxCall('proposal', 'view', {id: result.id, target: target}, target);
                    }
                } else {
                    try {
                        Drupal.CTools.Modal.dismiss();
                    } catch (err) {
                        console.log('In handleSubmitResult:' + err);
                    }
                }
                //Right now the content div has no immediate child with an id at the top, so we let ajaxAppend
                //find out the first child to attach a message to
                ajaxAppend(result.msg, 'our_content', 'status');
            } else {
                alertdev('The action did not result in error but also not in OK. succeeded??');
            }
        }
    } else {
        alertdev('Not a valid result');
    }

}

function populateModal(result, args) {
    // TODO : work more on the formatting
    // and add other fields from DB
    var data = null;
    if ((typeof args[2] == 'undefined') || !args[2]) {
        //So the result will be a js object NOT already parsed. So we calculate a parsed data
        try {
            data = jQuery.parseJSON(result);
        } catch (err) {
            console.log('Some program error occured. We could not render the result');
            Obj("modal-title").html("&nbsp;"); // doesnt render unless theres something there!
            if (debugging) {
                Obj("modal-content").html("<div class='messages error'>" + result + "</div>");
            } else {
                Obj("modal-content").html("<div class='messages error'>Some program error occured. Could not parse result.</div>");
            }
            return;
        }
    } else {
        data = result;
    }
    if (data && data.result !== 'error') {
        var content = '';
        var fun = args[0];
        var arg1 = (typeof args[1] !== 'undefined') ? args[1] : null;
        if (typeof fun === 'function') {
            content = fun(data.result, arg1);
        } else {
            content = data.result;
        }
        try {
            Obj("modal-title").html("&nbsp;"); // doesnt render unless theres something there!
            Obj("modal-content").html(content);
        } catch (err) {
            console.log('There was some error: ' + err);
        }
        return true;
    } else {
        console.log('Some identified program error occured. We could not render the result');
        if (data) {
            Obj("modal-title").html("&nbsp;"); // doesnt render unless theres something there!
            Obj("modal-content").html("<div class='messages error'>" + data.error + "</div>");
        }
        return false;

    }
    ;
}

function generateAndPopulateModal(result, fun, arg1) {
    // TODO : work more on the formatting
    // and add other fields from DB
    Drupal.CTools.Modal.show();
    var return_result = populateModal(result, [fun, arg1]);
    Drupal.attachBehaviors();
    return return_result;
}