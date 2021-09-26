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
 * New Course Form for the admin settings
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/completionlib.php');
/**
 * The form for handling editing a course.
 */
class coursenew_form extends moodleform {
    protected $course;
    protected $context;
    /**
     * Form definition.
     */
    public function definition() {
        global $CFG,  $DB, $USER, $SITE, $PAGE;
        $mform    = $this->_form;
        $course        = $this->_customdata['course']; // This contains the form data !
        $category      = $this->_customdata['category'];
        $editoroptions = $this->_customdata['editoroptions'];
        $returnto = $this->_customdata['returnto'];
        $returnurl = $this->_customdata['returnurl'];
        $creqmanagerid = $this->_customdata['creqmanagerid'];
        $categorycontext = context_coursecat::instance($category->id);
        $coursecontext = null;
        $context = $categorycontext;
        $select = 'id ='. $creqmanagerid. ' AND status IS NULL AND deleted = 0';
        if (isset($_SESSION['creqmanager_session'])) {
            $select = 'id ='. $_SESSION['creqmanager_session'];
        }
        $currentrecord = $DB->get_record_select('block_creqmanager_records', $select, null, $fields = '*');
        $field1title = $DB->get_field('block_creqmanager_config', 'value', array('varname' => 'entryfield1'));
        $field2title = $DB->get_field('block_creqmanager_config', 'value', array('varname' => 'entryfield2'));
        $courseconfig = get_config('moodlecourse');
        $this->course  = $course;
        $this->context = $context;
        if ($currentrecord) {
            $mform->addElement('hidden', 'creqmanagerid', $creqmanagerid);
            $mform->setType('creqmanagerid', PARAM_INT);
            // Form definition with new course defaults.
            $mform->addElement('hidden', 'returnto', null);
            $mform->setType('returnto', PARAM_ALPHANUM);
            $mform->setConstant('returnto', $returnto);
            $mform->addElement('hidden', 'returnurl', null);
            $mform->setType('returnurl', PARAM_LOCALURL);
            $mform->setConstant('returnurl', $returnurl);
            $mform->addElement('text', 'field1', format_string($field1title));
            $mform->addRule('field1', get_string('request_rule1', 'block_creqmanager'), 'required', null, 'server', false, false);
            $mform->setType('field1', PARAM_TEXT);
            $mform->hardFreeze('field1');
            $mform->setConstant('field1', $currentrecord->field1);
            $mform->addElement('text', 'field2', format_string($field2title));
            $mform->addRule('field2', get_string('request_rule1', 'block_creqmanager'), 'required', null, 'server', false, false);
            $mform->setType('field2', PARAM_TEXT);
            $mform->hardFreeze('field2');
            $mform->setConstant('field2', $currentrecord->field2);
            $mform->addElement('text', 'fullname', get_string('fullnamecourse'), 'maxlength="254" size="50"');
            $mform->addHelpButton('fullname', 'fullnamecourse');
            $mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
            $mform->setType('fullname', PARAM_TEXT);
            $fullname = block_creqmanager_fullname($currentrecord);
            $mform->hardFreeze('fullname');
            $mform->setConstant('fullname', $fullname);
            $mform->addElement('text', 'shortname', get_string('shortnamecourse'), 'maxlength="100" size="50"');
            $mform->addHelpButton('shortname', 'shortnamecourse');
            $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
            $mform->setType('shortname', PARAM_TEXT);
            $shortname = block_creqmanager_shortname($currentrecord);
            $mform->hardFreeze('shortname');
            $mform->setConstant('shortname', $shortname);
             // Change course category or keep current.
            if ((empty($course->id)) && (is_null($currentrecord->coursecategory))) {
                $mform->addElement('hidden', 'category', null);
                $mform->setType('category', PARAM_INT);
                $mform->setConstant('category', $category->id);
            } else {
                // Keep current.
                $mform->addElement('hidden', 'category', null);
                $mform->setType('category', PARAM_INT);
                $mform->setConstant('category', $currentrecord->coursecategory);
            }
            // Cohort Selection.
            $cohortidcustomint1 = ((int)$currentrecord->cohortcustomint1);
            if (($cohortidcustomint1 != null) && ($cohortidcustomint1 > 0 )) {
                $mform->addElement('hidden', 'cohortcustomint1', $cohortidcustomint1);
                $mform->setType('cohortcustomint1', PARAM_INT);
                $mform->setConstant('cohortcustomint1', $cohortidcustomint1);
            }
            // Course Summary Description.
            $mform->addElement('editor', 'summary_editor', get_string('coursesummary'), null, $editoroptions);
            $mform->addHelpButton('summary_editor', 'coursesummary');
            $mform->setType('summary_editor', PARAM_RAW);
            $summaryfields = 'summary_editor';
            if ($overviewfilesoptions = creqmanager_course_overviewfiles_options($course)) {
                $mform->addElement('filemanager', 'overviewfiles_filemanager',
                get_string('courseoverviewfiles'), null, $overviewfilesoptions);
                $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
                $summaryfields = 'overviewfiles_filemanager';
            }
            // Completion tracking.
            if (completion_info::is_enabled_for_site()) {
                $mform->addElement('hidden', 'enablecompletion', $courseconfig->enablecompletion);
                $showcompletionconditions = $courseconfig->showcompletionconditions ?? COMPLETION_SHOW_CONDITIONS;
                $mform->addElement('hidden', 'showcompletionconditions', $showcompletionconditions);
            } else {
                $mform->addElement('hidden', 'enablecompletion');
                $mform->setDefault('enablecompletion', 0);
            }
            $mform->setType('enablecompletion', PARAM_INT);
            $mform->setType('showcompletionconditions', PARAM_INT);
            $mform->addElement('hidden', 'id', null);
            $mform->setType('id', PARAM_INT);
            $this->add_action_buttons(true, get_string("addnewcourse"));
            // Prepare custom fields data.
            // Finally set the current form data.
            $this->set_data($course);
        }
    }
}
