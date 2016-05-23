/*
Activatables -- Make sets of elements active/inactive through anchors.
Copyright (c) 2009 Andreas Blixt
MIT license

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/*
 * The software has been slightly modified by E. Veenendaal, Raycom BV in 2014
 * Functionality is provided to cope with tabs that need to do an update of content: with a click
 * the content can be filled with an ajaxcall and in these cases the links are recognised as well as
 * tab links. By making use of the preventDefault js call, we can alternatively create a link with the 
 * href being an anchor and just adding an onclick event.
 * 
!!! Usage notes !!!
* All the tabs are contained in a div with class tabs_container
* The tabs are formed by an ordered list with the class 'toc' and within each li an 
* anchor with either: (assuming a prefix 'tab_' )
****   as href a url of the form #tab_<id> where <id>= the id of the corresponding content pane
*      as href javascript:void(0); and as onclick an ajaxcall with as target the content pane id
* In the latter case the target is specified in a data-target attribute of the form #tab_<id>
* The content pane is a div with as class 'content'.
* 
* Include this file and after the html is loaded, call activetabs('tab_', [id1, id2, id3,...])
* If ids are specified that are not in the page this will be reported in development modus, but will
* cause a failure in production, so it should be fixed. Divs in the page that are not in the list will just
* be shown.
*/

// Wrapped in a function so as to not pollute the global scope.
var activatetabs = (function () {
// The CSS classes to use for active/inactive elements.
    var anchors = {};

function showObject(obj, d){
    var depth = d ? d : 3;
    var s ='';
    for (var o in obj){
        s = s + "OBJ: " + o + "=";
        if ((typeof obj[o] == 'object') && depth > 0){
            s = s + showObject(obj[o], d - 1);
        } else {
            s += obj[o];
        }
    }
    return s;
}

// Takes an array of either element IDs or a hash with the element ID as the key
// and an array of sub-element IDs as the value.
// When activating these sub-elements, all parent elements will also be
// activated in the process.
// When no initial tab id is given, the first tab will be made active
function makeTabsActive(tab_name, active_tabs, initial, anchors_found) {
    var all = {},
        first = (typeof initial != 'undefined') ? initial : '',
        anchors_found = (typeof anchors_found != 'undefined') ? anchors_found : false,
        current = null;
        
    var activeClass = 'active';
    var inactiveClass = 'inactive';

    // Adds/removes the active/inactive CSS classes depending on whether the
    // element is active or not.
    function setClass(elem, active) {
        var classes = elem.className.split(/\s+/);
        var cls = active ? activeClass : inactiveClass, found = false;
        for (var i = 0; i < classes.length; i++) {
            if (classes[i] == activeClass || classes[i] == inactiveClass) {
                if (!found) {
                    classes[i] = cls;
                    found = true;
                } else {
                    delete classes[i--];
                }
            }
        }

        if (!found) classes.push(cls);
        elem.className = classes.join(' ');
    }
    
    // Find all anchors (<a href="#something">.)
    function findTabs(){
        var temp = document.getElementsByTagName('a');
        var regex = new RegExp('#'+ tab_name +'([A-Za-z0-9:._-]+)$');
        var reg_js = new RegExp('^[j,J]avascript');
        var $match = false;
        var target = '';
        
        for (var i = 0; i < temp.length; i++) {
            var a = temp[i];
            if (reg_js.test(a.href)){
                if (a.hasAttribute('data-target')){
                    //this attribute serves as anchor now
                    target = a.getAttribute("data-target");
                } else {
                	//this is not a tab with javascript
                    continue;
                }
            } else {
                // Make sure the anchor isn't linking to another page.
                if ((a.pathname != location.pathname &&
                    '/' + a.pathname != location.pathname) ||
                    a.search != location.search) continue;
                target = a.href;
            }
            // Make sure the anchor has a hash part.
            $match = regex.exec(target);
            if($match){
                var id = $match[1];
             } else {
                 continue;
             }
            
            // Add the 'anchor' to a lookup table.
            if (id in anchors) {
                anchors[id].push(a);
            } else {
                anchors[id] = [a];
            }
        }
    }

	// Activates all elements for a specific id (and inactivates the others.)
	function activate(id) {
        if (current && current === id){
        	return;
        }
		if (!(id in all)) {
			return false;
		}

        //set all targets and anchors (except id) on inactive (== invisible in case of targets)
		for (var cur in all) {
			if (cur == id) continue;
			for (var i = 0; i < all[cur].length; i++) {
				setClass(all[cur][i], false);
			}
		}
		//Set all targets from the current tab and the current tab (all in the lookup table all[id])
		// on active class
		for (var i = 0; i < all[id].length; i++) {
			setClass(all[id][i], true);
		}
        current = id;
		return true;
	}


    //Expects an id or an array of ids
	function attach(item) {
        if (item instanceof Array) {
			for (var i = 0; i < item.length; i++) {
				attach(item[i]);
			}
		} else if (typeof item === 'string') {
            var path = [];
            var e = document.getElementById(item);
			if (!e) {
                if (debugging){
                    alert('Could not find the target '+ item);
                    return;
                } else {
                    throw 'Could not find "' + item + '".';
                }
            }
			path.push(e);
			if (!first) {
                first = item;
            }

			// Attach a function that will activate the appropriate element
			// to all anchors.
			if (item in anchors) {
                // Create a function that will call the 'activate' function with
				// the proper parameters. It will be used as the event callback.

                var func = function (e) {
						activate(item);
						if (!e) e = window.event;
						if (e.preventDefault) e.preventDefault();
						e.returnValue = false;
						return false;
					};
				for (var i = 0; i < anchors[item].length; i++) {
					var a = anchors[item][i];

					if (a.addEventListener) {
						a.addEventListener('click', func, false);
					} else if (a.attachEvent) {
						a.attachEvent('onclick', func);
					} else {
						throw 'Unsupported event model.';
					}
                    //push the link too to set its class
					path.push(a);
				}
			}
			// Store the elements in a lookup table: all is an array of arrays of elements (tab links and 
			// content div element objects
			all[item] = path;
		} else {
			throw 'Unexpected type.';
		}
	}
    
    //Get the tabs object (we only need to do that once)
    if (!anchors_found) {
        findTabs();
    } else {
    	//TODO alertdev('de tabs zouden er moeten zijn ');
    }
    //Get the corresponding targets in the lookup table and attach the activating 
    //event to the link in the tab
	attach(active_tabs);

	// Activate an element if there is any.
	if (first) {
        activate(first);
    }
}
//We just return the main function, but the intended use is to call makeTabsActive
return makeTabsActive;
})();