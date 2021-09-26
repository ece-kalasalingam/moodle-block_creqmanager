<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// COURSE REQUEST MANAGER BLOCK FOR MOODLE
// ---------------------------------------------------------.
/**
 * Course request form
 *
 * Main course request form
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL')||die();
require_once($CFG->libdir.'/formslib.php');
global $CFG, $USER, $DB;
class block_creqmanager_courserequest_form extends moodleform {
    public function definition() {
        global $CFG, $DB;
        $mform =& $this->_form; // Don't forget the underscore!
        // Get the field values.
        $field1title = $DB->get_field('block_creqmanager_config', 'value', array('varname' => 'entryfield1'), IGNORE_MULTIPLE);
        $field2title = $DB->get_field('block_creqmanager_config', 'value', array('varname' => 'entryfield2'), IGNORE_MULTIPLE);
        $field3title = $DB->get_field('block_creqmanager_config', 'value', array('varname' => 'optfield1'), IGNORE_MULTIPLE);
        $field4title = $DB->get_field('block_creqmanager_config', 'value', array('varname' => 'optfield2'), IGNORE_MULTIPLE);
        // Get field 3 status.
        $field3status = $DB->get_field('block_creqmanager_config', 'value', array('varname' => 'optfield1status'), IGNORE_MULTIPLE);
        $field4status = $DB->get_field('block_creqmanager_config', 'value', array('varname' => 'optfield2status'), IGNORE_MULTIPLE);
        // Get the value for autokey - the config variable that determines enrolment key auto or prompt.
        $autokey = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'autokey'");
        // Get the value for category selection - the config variable that determines if user can choose a category.
        $selfcat = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'selfcat'");
        $snaming = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'snaming'");
        // Course shortname.
        $mform->addElement('text', 'field1', format_string($field1title));
        $mform->addRule('field1', get_string('request_rule1', 'block_creqmanager'), 'required', null, 'server', false, false);
        $mform->setType('field1', PARAM_TEXT);
        // Course fullname.
        $mform->addElement('text', 'field2', format_string($field2title));
        $mform->addRule('field2', get_string('request_rule1', 'block_creqmanager'), 'required', null, 'server', false, false);
        $mform->setType('field2', PARAM_TEXT);
        // Optional text box.
        if ($field3status == 'enabled') {
            $mform->addElement('text', 'optfield1', format_string($field3title));
            if ($snaming == 3 || $snaming == 5) {
                $mform->addRule('optfield1',
                get_string('request_rule1', 'block_creqmanager'),
                'required', null, 'server', false, false);
            }
            $mform->setType('optfield1', PARAM_TEXT);
        }
        // Enrolment key.
        if ((!$autokey) && (enrol_is_enabled('self'))) {
            $passattribs = array('maxlength' => '50');
            $mform->addElement('passwordunmask', 'enrolkey', get_string('password', 'enrol_self'), $passattribs);
            $mform->addHelpButton('enrolkey', 'password', 'enrol_self');
            $mform->addRule('enrolkey', get_string('request_rule3', 'block_creqmanager'), 'required', null, 'server', false, false);
            $mform->setDefault('enrolkey', block_creqmanager_generate_password(2));
            $mform->setType('enrolkey', PARAM_TEXT);
        }
        // Optional Dropdown.
        if ($field4status == 'enabled') {
            $options = [];
            $selectquery = "varname = 'optfield2value'";
            $field4items = $DB->get_recordset_select('block_creqmanager_config', $select = $selectquery);
            foreach ($field4items as $item) {
                $value = $item->value;
                if ($value != '') {
                    $options[$value] = format_string($value);
                }
            }
            $field4items->close();
            $mform->addElement('select', 'optfield2', format_string($field4title), $options);
            if ($snaming == 4 || $snaming == 5) {
                $mform->addRule('optfield2',
                get_string('request_rule2', 'block_creqmanager'), 'required', null, 'server', false, false);
            }
        }
        // If enabled, give the user the option to select a category location for the course.
        if ($selfcat) {
            if ($CFG->branch > 35) {
                $options = core_course_category::make_categories_list();
            } else {
                $options = coursecat::make_categories_list();
            }
            $mform->addElement('select', 'coursecategory', get_string('coursecategory'), $options);
            $mform->addHelpButton('coursecategory', 'coursecategory');
            $mform->setDefault('coursecategory', $CFG->defaultrequestcategory);
        }
        // Hidden form element to pass the key.
        if ($_SESSION['creqmanager_editingmode'] == 'true') {
            $mform->addElement('hidden', 'editingmode');
            $mform->setType('editingmode', PARAM_TEXT);
        }
        // Hidden form element to pass the key.
        if (isset($_SESSION['creqmanager_session'])) {
            $mform->addElement('hidden', 'recordid');
            $mform->setType('recordid', PARAM_INT);
            $mform->setDefault('recordid', $_SESSION['creqmanager_session']);
        }
        // Cohort selection.
        $selectquery = "varname = 'customint1value'";
        $customint1items = $DB->get_recordset_select('block_creqmanager_config', $select = $selectquery);
        foreach ($customint1items as $item) {
            $customint1array[$item->id] = $item->value;
        }
        $customint1items->close();
        $cohorts = array('' => get_string('choosedots'));
        $allcohorts = cohort_get_available_cohorts(\context_course::instance(1), 5, 0, 0);
        $cohortslist = array();
        foreach ($allcohorts as $c) {
                $cohortslist[$c->id] = format_string($c->name);
        }
        if (isset($customint1array)) {
            if ((count($customint1array) > 0) && (count($cohortslist) > 0)) {
                foreach ($cohortslist as $key => $value) {
                    if (in_array($key, $customint1array) == true) {
                        $cohorts[$key] = $value;
                    }
                }
            }
        }
        $mform->addElement('select', 'cohortcustomint1', get_string('cohort', 'cohort'), $cohorts);
        // For setting the semester of course.
        $mform->addElement('advcheckbox', 'timeless',
        get_string('timeless', 'block_creqmanager'), get_string('yes'), [], array(0, 1) );
        $this->add_action_buttons(true, get_string("next"));
    }
}
