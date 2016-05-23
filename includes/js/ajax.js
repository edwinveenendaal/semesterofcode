//var $jq = jquery.noConflict();
//These settings are now included in a general settings file
//var debugging = true;
//var console_Jquery_migrate_warnings = false;
//var base_url = '/vals/';
//var module_url = base_url + 'sites/all/modules/vals_soc/';

var $jq = jQuery;

function alertdev(m) {
	if (debugging)
		alert(m);
}
function url(category, action) {
	return module_url + "actions/"+category+"_actions.php?action="+ action;
}

function altSub(arr_obj, prop, def){
	if (arr_obj && (typeof arr_obj[prop] != 'undefined')){
		return arr_obj[prop];
	} else {
		return def ? def : '';
	}
}

function ajaxInsert(msg, target) {
	var tar = $jq('#' + target);
	if (tar.length) {
		tar.html(msg);
		Drupal.attachBehaviors();
		return true;
	} else {
		alertdev('Could not find target ' + target);
		return false;
	}
	
}

function isObject(mixed_var){
	if (mixed_var instanceof Array) {
		return false;
	} else {
		return (mixed_var !== null) && (typeof( mixed_var ) == 'object');
	}
}

function isArray(mixed_var){
	return (mixed_var instanceof Array) && (typeof( mixed_var ) == 'object');
}

function Obj(name_or_object, return_dom){
	if (typeof return_dom == 'undefined') return_dom = false;
	
	if (isObject(name_or_object)){
		if (return_dom){
			return isJquery(name_or_object) ? name_or_object[0] : name_or_object;
		} else {
			return name_or_object;
		}
	} else {
		var obj = $jq('#'+name_or_object);
		if (obj.length == 0){
			return false;
		} else {
			return return_dom ? obj[0] : obj;
		}
	}
}

function isJquery(obj){
	return obj && (typeof obj.jquery != 'undefined') && obj.jquery;
}

function createDiv(div_name, container, before, returnDom){
	if (typeof returnDom == ' undefined') {
		returnDom = false;
	}
	var div = Obj(div_name, returnDom);
	if(div){
		return div;
	} else{
		var new_obj = document.createElement('div');
		if (new_obj) new_obj.setAttribute("id", div_name); else console.log('could not create the div');
		var cont_obj = Obj(container, true);
		if (cont_obj) {
			if (arguments.length > 2 && arguments[2] !== ''){
				//will be the argument before
				//Insert before an element inside container
				//we use the normal objects, so we ask Obj to return a dom element
				//NOTE: if this insertBefore gives an error it is likely that the before is not a direct descendent
				//if the container object. It might be that a nice wrapper has been inserted around the before for
				//styling etc. So div A > div B > before with createDiv(new, A, before) fails (should be 
				//createDiv(new, B, before))
				var first_obj = Obj(arguments[2], true);
				if (first_obj){
					cont_obj.insertBefore(new_obj, first_obj);
				} else {
					cont_obj.insertBefore(new_obj, cont_obj.childNodes[0]);
				}
			} else {
				cont_obj.appendChild(new_obj);
			}
			return Obj(div_name, returnDom);
		} else {
			alertdev('Could not find parent object '+ container);
			return null;
		}
	}
}

function is_string(arg){
	return (typeof arg) == 'string';
}

function ajaxAppend(msg, container, err, before){
	var t = '';
    var err = err ? err : '';//could be 'error'
	var targ = container + err;
	var new_class = "messages "+ err;
	if (typeof before == 'undefined') {
		var cont_obj = Obj(container);
		if (cont_obj){
			before = cont_obj[0].childNodes[0];
		} else {
			before = '';
			alertdev('Could not append message to container '+ container+ '. It could not be found.');
			return false;
		}
		t = createDiv(targ, cont_obj[0], before);
	} else {
		console.log('hierlangs met '+ targ + ' en '+  container + ' en '+ before);
		t = createDiv(targ, container, before);
	}

	if (t) {
		var click = "onclick='$jq(\"#" +targ+"\").html(\"\").removeClass(\""+new_class+ "\");'";
		var msg2 = "<span class='lefty'>"+msg + "</span><span class='close_button' "+ click+ ">X</span>";
		t.addClass(new_class);
		t.html(msg2);
		window.location = '#'+targ;
		//t.scrollIntoView(true);Does not work
		//t.focus();does not work either
	}

}

function ajaxError(targ, msg) {
	if (msg){
		var err_target = Obj(targ);
		var click = "onclick='$jq(\"#" +targ+"\").html(\"\").removeClass(\"messages error\");'";
		var msg2 = "<span class='lefty'>"+msg + "</span><span class='close_button' "+ click+ ">X</span>";
		//var msg2 = "<a href=javascript:void(0); onclick='$jq(\"#" +targ+"\").html(\"\").removeClass(\"messages error\");'>"+
		//msg+ "</a>";
		if (err_target.length){
			err_target.html(msg2);
			err_target.addClass('messages error');
			window.location = '#'+targ;
		} else {
			alertdev('Target for error '+ targ+ ' could not be found.');
		}
	}
}

function ajaxMessage(targ, msg) {
	if (msg){
		var msg_target = $jq('#'+targ);
		var click = "onclick='$jq(\"#" +targ+"\").html(\"\").removeClass(\"messages status\");'";
		var msg2 = "<span class='lefty'>"+msg + "</span><span class='close_button' "+ click+ ">X</span>";
		//"<a href=javascript:void(0); "+ click+ ">X</a>";
		if (msg_target.length){
			msg_target.html(msg2);
			msg_target.addClass('messages status');
			window.location = '#'+targ;
		} else {
			alertdev('Target '+ targ+ ' for the message "'+msg+'" could not be found.');
		}
	}
}

function sanitizeTarget(target){
	return target.replace(/[\(,\),;,\,]/g, '');
}

function isFunction(func){
	//Since func could also be a string of the form action=modules&sub=.... the eval might raise an error if we
	//do not enclose it in quotes. We just want to return false in that case
	//Doing return (func !== null) && (typeof( func ) == 'function'); does not work, so we have to use eval
	try {
		return (
          typeof func == 'function') ||
          eval("typeof " + sanitizeTarget(func) + " == 'function'");
	} catch (e){
		return false;
	}
}

//Determines where to send messages to, based on the target (function or dom element)
//and the arguments. Note that in all ajax calls the target is non-mepty
function ajaxGetMessageTarget(target, data, args){
	var msg_target = '';
	if (isFunction(target)){
		if (typeof data.target != 'undefined' && data.target){
			msg_target = 'msg_'+ data.target;
		} else {
			if (typeof args != 'undefined' && args){
				if (typeof args[1] != 'undefined' && args[1]){
					msg_target = 'msg_'+ args[1];
				} else {
					msg_target = 'msg_'+ args[0];
				}
			} else {
				msg_target = 'ajax_msg';
			}
		}
	} else {
		msg_target = 'msg_'+ target;
	}
	
	return msg_target;
}

function ajaxCall(handler_type, action, data, target, return_type, extra_args, 
    extra_fun_fail, extra_fun_success, msg_container) {
        
	if (!return_type)
		return_type = 'html';// possible return_types are html, json, xml, text, script,
						// jsonp
	var call = {
		type : "POST",
		url : url(handler_type, action),
		data : data,
		dataType : return_type
	};
	// Handling a successfull call which can be a programmed returned error too.
	// As long as the returned val is corresponding with dataType and in time.
	// If the success function is not speicifed, a target is necessary to show
	// the result
//	$jq("#admin_container input[type='button']").prop(
//			{'disabled': true, 'style': "background-color:grey"});
	ajax_event = (ajaxCall.caller && ajaxCall.caller.arguments  && ajaxCall.caller.arguments[0]) || window.event ;	
	var show_waiting = (typeof ajax_event != 'undefined');
	if (show_waiting){
		startWait(ajax_event, event_counter, 'our_content');
		//event_counter ++;//For now we assume only one waiting icon
	}
    
    //define success function
	if (target) {
		if (isFunction(target)) {
			var args = [];
			if (arguments.length >= 6){
				if (isArray(extra_args))
					args = extra_args;
				else
					args = [extra_args];
			}
			call.success = function(msg){
				window[target](msg, args);
				stopWait(1);
				if (msg.result == 'error'){
					if ((typeof extra_fun_fail == 'function')){
						extra_fun_fail.call();
					}
				} else {
					if ((typeof extra_fun_success == 'function')){
						extra_fun_success.call();
					}
				}
							
				return true;
			};
		} else {
			call.success = function(msg) {
				//The way it is implemented a possible form is overwritten anyway, so no need to perform extra_fun
				if (return_type == 'json') {
					if (msg.result == "html") {
						ajaxInsert(msg.html, target);
					} else if (msg.result == "error") {
						ajaxInsert(msg.error, target);
					} else {
						if (typeof msg.msg != 'undefined') {
							ajaxInsert(msg.msg, target);
						} else {
							alertdev('The action '
									+ action
									+ ' succeeded. Specify a success message or some function');
						}
					}
				} else {// assume text or html, we don't care: all can be valid
					ajaxInsert(msg, target);
				}
				stopWait(1);
				return true;
			};
		}
	} else {
		alertdev('No target or function has been specified: see console for details.');
		stopWait(1);
        ajaxMessage('Problems with javascript handling of call. Call not well specified');
		return false;
	}
    
    //define fail function
	call.fail = function(jqXHR, textStatus, errorThrown) {
		console.log('AjaxCall failed with some error. Redirected to its fail function with: '
			+ errorThrown);
		stopWait(1);
        if (msg_container){
            ajaxError(msg_container,'There were problems with javascript handling of call. ' + 
                errorThrown + textStatus);
        } else {
            alertdev('There was a problem with javascript, but no msg container was specified');
        }
		if (extra_fun_fail && isFunction(extra_fun_fail)){
            console.log('Extra function is being called');
			extra_fun_fail.call();
		}
		return false;
	};

	//This seems reasonable but it looks like it does not work
//	return $jq.when($jq.ajax(call)).done(function(a1){
//		return a1;
//	});
	return $jq.ajax(call);
}

function ajaxFormCall(frm_selector, handler_type, action, data, target, return_type, args) {
	CKupdate();
    var msg_target = ajaxGetMessageTarget(target, data, args);
    
    //We assume the form is contained in a container with an id, or just the form id is passed (if it is unique)
	//this is possible otherwise we need a unique container (mostly the target where the form is put)
	ajaxDisableForm(frm_selector, 'background-color:grey');

	//testing (if included) is a global object with testing functions for forms
	if (testing && isFunction('testing.run')){
		if (!testing.run(frm_selector, msg_target)){
            ajaxEnableForm(frm_selector);
			return false;
        }
	} else {
        console.log('No test functions executed on form.');
		//no test needs to be done or forgotten to include test_functions.js
	}
    
	var call_args = $jq('#' + frm_selector).serialize();
	if (data) {
		if (data instanceof Object) {
			var ds = '';
			for ( var k in data) {
				ds += (ds ? '&' : '') + k + '=' + data[k];
			}
			call_args = call_args.concat('&' + ds);
		} else {
			call_args = call_args.concat('&' + data);
		}
	}
	var enable_this_form_on_fail = function(){ajaxEnableForm(frm_selector);};
	var enable_this_form_on_success = '';
	if(handler_type == 'comment'){
		console.log('Comment, form ' + '#' + frm_selector + " is reset");
		$jq('#' + frm_selector)[0].reset();//reset the form for comments
		enable_this_form_on_success = enable_this_form_on_fail; //comments keep the form even after successfull save
	}
	
	ajaxCall(handler_type, action, call_args, target, return_type, args, 
		enable_this_form_on_fail, enable_this_form_on_success, msg_target);
}

function ajaxDisableForm(frm_selector, disable_style){
	console.log('disable now the form '+ frm_selector);
	$jq("#" + frm_selector + " input[type='button']").prop(
			{'disabled': true, 'style': disable_style});
}

function ajaxEnableForm(frm_selector){
	console.log('enable now the form '+ frm_selector);
	$jq("#" + frm_selector + " input[type='button']").prop(
			{'disabled': false, 'style': ''});
}

$jq(document)
    .ajaxError(
        function(event, jqxhr, settings, exception) {
            var str_exception = ''+ exception;
            if (jqxhr.status === 0 || jqxhr.readyState === 0) {
                return false;
            }
            if (jqxhr.status == 404) {
                alertdev('Requested page not found. [404]');
            } else if (jqxhr.status == 500) {
                alertdev('Internal Server Error [500].');
            } else if (jqxhr.status == 302) {// error produced by
                                                // bjyauthorize
                // //The user returns to the page after a long time and
                // is logged out becauses of session expiration
                if (confirm('It seems you are no longer logged in. Do you want to log in now?')) {
                    window.location.replace(base_url + "/user/login");
                    return;
                } else {
                    return;
                }
            } else {
                if (str_exception.indexOf('Unexpected token') !== false) {
                    //alertdev('Requested JSON parse failed.');
                    alertdev('Requested JSON parse failed. Probably Server execution aborted by die, exit or Fatal error.\n'
                            + jqxhr.responseText + ' causing exception: '+str_exception);
                } else if (str_exception.indexOf('timeout') !== false) {
                    alertdev('Time out error.');
                } else if (str_exception.indexOf('abort') !== false){
                    alertdev('Ajax request aborted.');
                } else {
                   alertdev('Uncaught Error. Probably Server execution aborted by die, exit or Fatal error.\n'
                            + jqxhr.responseText + ' causing exception: '+str_exception);
                }
            }
            if (!(debugging)){
                alert('Some error occurred at the server during the Ajax call. Please contact the development team to sort this out.');

            }
            stopWait(1);
            if (settings.fail) {
                settings.fail(jqxhr, '', exception);
            }
            if (!(debugging && confirm("Do you want to open a window with some extra info?"))){						
                return;
            }

            // Test on debugging status == TRUE, TODO more depending on
            // logged in status etc
            if (debugging){
                
                myWindow = window.open('', '');
                myWindow.document.open();
                myWindow.focus();
                var txt = '';
                if (((typeof jqxhr.responseText) != 'undefined')) {
                    try {
                        var json_txt = decodeURIComponent(jqxhr.responseText);
                        var parsed = JSON.parse(json_txt);
                        txt = parsed.error;
                    } catch (e) {
                        txt = jqxhr.responseText;
                        if (console) {
                            console.log("Parsing error: " + e);//, e
                            //console.error("Parsing error: " + e);//, e
                        }
                    }
                } else {
                    txt = 'No response';
                }
                myWindow.document
                    .write('Some error occured in the ajax call for '
                            + settings.url
                            + '<br>Exception:'
                            + exception + '<br>Response:' + txt);
                myWindow.document.close();
                myWindow.focus();
            } else {
                return {result:'error', error: 'Unexpected result, please contact the administrator'};
            }
        }
);

function inspect(arg){
	alertdev('Just inspecting the object two level deep');
	for (a in arg){
		if (typeof (arg[a]) == 'object'){
			alert('het is een object '+ a);
			for (b in arg[a]){
				if (typeof (arg[a][b]) == 'object'){
					alert('het is een object '+ b);

				} else
				alert('laat zien '+ b + arg[a][b]);
			}
		} else
		alert('laat zien '+ a + arg[a]);
	}
}
/* Returns a js object to process. Note that the json has been parsed by Jquery as the input comes in the
 * success function. I do the request with post as this seems slightly faster than get (1.38 s vs 1.7 s)*/
jQuery.extend({
	getJsonObject : function(category, action, data_obj) {
		var result = null;
		$jq.ajax({
			url : url(category, action),
			type : 'post',
			dataType : 'json',
			data : data_obj,
			async : false,
			success : function(data) {
				result = data;
			}
		});
		return result;
	}
});

/*
 * Used for ckeditor: the hidden textarea fields will be filled with the actual code just before sending
 * the ajax. This function will be called automatically for ajaxFormCall.
 */
function CKupdate(){
	if (CKEDITOR && ! jQuery.isEmptyObject(CKEDITOR.instances)){
		for (instance in CKEDITOR.instances){
	        CKEDITOR.instances[instance].updateElement();
	    }
	}
}