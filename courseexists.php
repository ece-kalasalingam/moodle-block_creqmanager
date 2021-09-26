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
 * COURSE REQUEST MANAGER COURSE EXISTS PAGE
 *
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir .'/formslib.php');
require_once($CFG->dirroot . '/blocks/creqmanager/lib.php');
require_login();
global $CFG, $DB, $USER;
$shortname = required_param('shortname', PARAM_TEXT);
// Navigation Bar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('plugindesc', 'block_creqmanager'), new moodle_url('/blocks/creqmanager/manager.php'));
$PAGE->navbar->add(get_string('courseexists', 'block_creqmanager'));
$PAGE->set_url('/blocks/creqmanager/courseexists.php');
$PAGE->set_context(context_system::instance());
$title = $SITE->shortname.': '.get_string('courseexists', 'block_creqmanager');
$PAGE->set_title($title);
$PAGE->set_heading($SITE->fullname);
// Get out record.
$selectquery = "shortname = '".$shortname ."'";
$recordscount = $DB->count_records_select('course', $selectquery, null);
$coursexistslink = '';
$url = '/blocks/creqmanager/courserequest.php';
$table = new html_table();
if ($recordscount > 0) {
    $allrecords = $DB->get_recordset_select('course', $selectquery, null, $sort = '', $fields = '*');
    $table->data = array();
    $table->head = array(get_string('shortnamecourse'), get_string('fullnamecourse'),
    get_string('catlocation', 'block_creqmanager'), get_string('lecturingstaff', 'block_creqmanager'));
    foreach ($allrecords as $record) {
        // Get the full category name.
        $categoryname = $DB->get_record('course_categories', array('id' => $record->category));
        // Check if the category name is blank.
        if (!empty($categoryname->name)) {
            $catlocation = $categoryname->name;
        } else {
            $catlocation = ' ';
        }
        // Get lecturer info.
        $courseid = $record->id;
        $lecturersname = block_creqmanager_get_lecturers( $courseid);
        $table->data[] = array(format_string($record->shortname),
        format_string($record->fullname), format_string($catlocation), $lecturersname);
    }
    $allrecords->close();
    $courseurl = new moodle_url($url, array('t' => 'coursecancel', 'courseid' => $courseid));
    $coursexistslink = get_string('courseexists_desc', 'block_creqmanager').' '.
    html_writer::link($courseurl, get_string('viewcourse', 'block_creqmanager'));
}
echo $OUTPUT->header();
echo html_writer::tag('p', $coursexistslink);
echo html_writer::table($table);
$continueurl = new moodle_url($url, array('t' => 'coursecancel'));
$cebutton = new single_button($continueurl, get_string('blockrequest', 'block_creqmanager'));
$cebutton->primary = true;
echo $OUTPUT->container_start('controls');
echo $OUTPUT->render($cebutton);
echo $OUTPUT->container_end();
echo $OUTPUT->footer();
