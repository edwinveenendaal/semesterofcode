/*
 * Some form testing functions -only defined once.
 * by having a #vals_soc_attached attribute in the form we can set extra tests in the variable
 * vals_extra_tests like: 
 * Note that you could specify to stop on first false test
 * $form['#vals_soc_attached']['js'] = array(
 array(
 'type'  => 'file',
 'data' => '/includes/js/test_functions.js',
 ),
 // 		array(
 // 			'type' => 'direct',	
 // 			'data'=> 'var vals_extra_tests = ["test2"]; var vals_doall_tests = false;',
 // 		),
 );
 */

var testing = testing || {
    default_tests: ['test_required_fields'],
    doall: true,
    msgs: [],
    warning_style: {'border-style': 'solid', 'border-color': 'orange'},
    none_style: {'border-style': '', 'border-color': ''},
    createDate: function (str1) {
        // str1 format should be dd/mm/yyyy. Separator can be anything e.g. / or -. It wont effect
        var d = parseInt(str1.substring(0, 2));
        var m = parseInt(str1.substring(3, 5));
        var y = parseInt(str1.substring(6, 10));
        var date1 = new Date(y, m - 1, d);
        return date1;
    },
    run: function (form_selector, msg_target) {
        var tests = this.default_tests;
        var all_good = true;
        if (typeof vals_extra_tests != 'undefined') {
            tests = tests.concat(vals_extra_tests);
        }
        if (typeof vals_doall_tests != 'undefined') {
            this.doall = vals_doall_tests;
        }
        var nr_tests = tests.length;
        for (var i = 0; i < nr_tests && (this.doall || all_good); i++) {
            //calling the next test: test[i]
            all_good = testing[tests[i]](form_selector, msg_target) && all_good;
            console.log('doing a test ', tests[i], i);
        }
        if (all_good === false) {
            if (!this.msgs) {
                this.msgs = ['Your form is not correct'];
            }
            ajaxError(msg_target, this.msgs.join("<br/>"));
            var message_div = document.getElementById(msg_target);
            if (message_div) {
                message_div.scrollIntoView(true);
            } else {
                console.log('could not find ' + msg_target);
                alertdev('could not find ' + msg_target);
            }
        }
        this.msgs = [];
        return all_good;
    },
    test2: function (form_selector, msg_target) {
        this.msgs.push(' Test 2 done');
        return false;
    },
    test_project_date_fields: function (form_selector, msg_target) {
        var all_good = true;
        var self = this;
        var selected = $jq("#" + form_selector + " [type='radio'][name='available']:checked");
        var selectedVal = 0;
        if (selected.length > 0) {
            selectedVal = selected.val();
        }

        if (selectedVal == '1') {
            var begin_o = $jq("[name='begin']");
            var begin = begin_o.val();

            if (self.isBlank(begin)) {
                self.msgs.push('You cannot have no start date for available projects.');
                begin_o.css(self.warning_style);
                all_good = false;
            } else {
                var end_o = $jq("[name='end']");
                var end = end_o.val();
                if (!self.isBlank(end)) {
                    var date_b = this.createDate(begin);
                    var date_e = this.createDate(end);
                    if (date_b > date_e) {
                        end_o.css(self.warning_style);
                        all_good = false;
                        self.msgs.push('You cannot have an end date before the start date.');
                    }
                }
            }
        }

        return all_good;
    },
    test_required_fields: function (form_selector, msg_target) {
        var all_good = true;
        var self = this;
        $jq("#" + form_selector + " .required").each(function (index) {
            if ($jq(this).prop("tagName") == 'TEXTAREA') {
                var rte_id = '#cke_' + $jq(this).prop("id");
                if (self.isBlank($jq(this).val())) {
                    $jq(rte_id).css(self.warning_style);
                    all_good = false;
                } else {
                    $jq(rte_id).css(self.none_style);
                }
            } else {
                if (self.isBlank($jq(this).val())) {
                    $jq(this).css(self.warning_style);
                    all_good = false;
                } else {
                    $jq(this).css(self.none_style);
                }
            }

        });
        if (all_good === false) {
            this.msgs.push('You have left some required fields open.');
        }
        return all_good;
    },
    //For checking if a string is empty, null or undefined I use:
    isEmpty: function (str) {
        return (!str || 0 === str.length);
    },
    //For checking if a string is blank, null or undefined I use:
    isBlank: function (str) {
        return (!str || /^\s*$/.test(str));
    }
};