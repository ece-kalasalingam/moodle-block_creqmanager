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
 * COURSE REQUEST PAGE
 *
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
global $CFG, $USER, $DB;
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->libdir.'/formslib.php');
if ($CFG->branch < 36) {
    require_once($CFG->libdir.'/coursecatlib.php');
}
require_once($CFG->dirroot . '/blocks/creqmanager/lib.php');
require_once($CFG->dirroot . '/blocks/creqmanager/classes/forms/courserequest_form.php'
);
require_login();
// Stop guests from making requests!
if (isguestuser()) {
    throw new moodle_exception('noguest');
}
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/creqmanager/courserequest.php');
$ccancel = optional_param('t', '', PARAM_TEXT);
if ((isset($ccancel)) && ($ccancel == 'coursecancel')) {
    block_creqmanager_canceldraftrequest();
    $courseid = optional_param('courseid', -1, PARAM_INT);
    if ((isset($courseid)) && ($courseid > 0)) {
        redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
    }
    if ((isset($courseid)) && ($courseid == 0)) {
        redirect(new moodle_url('/', array('redirect' => 0)));
    }
}
// Main variable for storing the current session id.
$currentsess = optional_param('recordid', 0, PARAM_INT);
if ($currentsess != 0) {
    $_SESSION['creqmanager_session'] = $currentsess;
    $_SESSION['creqmanager_editingmode'] = 'true';
} else {
    $select = 'createdbyid='. $USER->id . ' AND status IS NULL AND deleted = 0 AND courseid IS NULL ';
    $draftrecord = $DB->get_record_select('block_creqmanager_records', $select, null,
    $fields = 'id', IGNORE_MULTIPLE);
    if (isset($draftrecord->id)) {
        $currentsess = $draftrecord->id;
        $_SESSION['creqmanager_session'] = $currentsess;
        $_SESSION['creqmanager_editingmode'] = 'true';
    }
}
// Keep ready the values needed for form appearance.
if ((isset ($_SESSION['creqmanager_session'])) && ($_SESSION['creqmanager_session'] != 0)
&& ($_SESSION['creqmanager_editingmode'] == 'true')) {
    if (has_capability('block/creqmanager:addrecord', $context)) {
        $currentsess = $_SESSION['creqmanager_session'];
        $currentrecord = $DB->get_record('block_creqmanager_records', array('id' => $currentsess),
        $fields = 'id, field2, field1, optfield1, optfield2, enrolkey,
        coursecategory,cohortcustomint1,timeless', IGNORE_MULTIPLE);
        $pagetitle = 'editrequest';
    } else {
        throw new moodle_exception('nopermissions', 'error', '', get_string('cannoteditrequest', 'block_creqmanager'));
    }
} else {
    if (has_capability('block/creqmanager:addrecord', $context)) {
        $_SESSION['creqmanager_editingmode'] = 'false';
        $currentsess = 0;
        $_SESSION['creqmanager_session'] = $currentsess;
        $pagetitle = 'blockrequest';
    } else {
        throw new moodle_exception('nopermissions', 'error', '', get_string('cannotrequestcourse', 'block_creqmanager'));
    }
}
$mform = new block_creqmanager_courserequest_form();
if ($mform->is_cancelled()) {
    block_creqmanager_canceldraftrequest();
    redirect(new moodle_url('/blocks/creqmanager/manager.php'));
} else if ($fromform = $mform->get_data()) {
    $newrec = new stdClass();
    $newrec->id = $currentsess;
    $field2 = required_param('field2', PARAM_TEXT);
    $newrec->field2 = $field2;
    $field1 = required_param('field1', PARAM_TEXT);
    $newrec->field1 = $field1;
    $coursecategory = optional_param('coursecategory', $CFG->defaultrequestcategory, PARAM_INT);
    $newrec->coursecategory = $coursecategory;
    $enrolkey = optional_param('enrolkey', null, PARAM_TEXT);
    $newrec->enrolkey = $enrolkey;
    $optfield1 = optional_param('optfield1', null, PARAM_TEXT);
    $newrec->optfield1 = $optfield1;
    $optfield2 = optional_param('optfield2', null, PARAM_TEXT);
    $newrec->optfield2 = $optfield2;
    $cohortcustomint1 = optional_param('cohortcustomint1', null, PARAM_INT);
    $newrec->cohortcustomint1 = $cohortcustomint1;
    $timeless = optional_param('timeless', null, PARAM_INT);
    $newrec->timeless = $timeless;
    $shortname = block_creqmanager_shortname($newrec);
    if (!empty($shortname)) {
        if ($DB->record_exists('course', array('shortname' => $shortname))) {
             redirect(new moodle_url('courseexists.php', array('shortname' => $shortname)));
        }
    }
    if ($_SESSION['creqmanager_editingmode'] == 'true') {
        $DB->update_record('block_creqmanager_records', $newrec);
    } else {
        $newrec->createdbyid = $USER->id;
        $newrec->deleted = 0;
        $date = new DateTime();
        $newrec->createdate = $date->getTimestamp();
        $currentsess = $DB->insert_record('block_creqmanager_records', $newrec, true);
        $_SESSION['creqmanager_session'] = $currentsess;
        $_SESSION['creqmanager_editingmode'] = 'true';
    }
    var_dump($currentsess);
    redirect(new moodle_url('coursenew.php', array('creqmanagerid' => $currentsess)));
} else {
    if ($_SESSION['creqmanager_editingmode'] == 'true') {
        $mform->set_data($currentrecord);
    }
}
// Navigation Bar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'block_creqmanager'), new moodle_url('/blocks/creqmanager/manager.php'));
$PAGE->navbar->add(get_string($pagetitle, 'block_creqmanager'));
$PAGE->set_title($SITE->shortname.': '. get_string($pagetitle, 'block_creqmanager'));
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($pagetitle, 'block_creqmanager'), $level = 2);
if ($_SESSION['creqmanager_editingmode'] == 'true') {
    echo html_writer::tag('p', get_string('courserequestline2', 'block_creqmanager'));
} else {
    echo html_writer::tag('p', get_string('courserequestline1', 'block_creqmanager'));
}
$mform->focus();
$mform->display();
echo $OUTPUT->footer();
