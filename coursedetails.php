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
 * SUMMARY OF CREATED COURSE
 *
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/creqmanager/classes/output/renderer.php');
require_once($CFG->libdir . '/tablelib.php');
require_login();
global $CFG,  $DB, $USER, $SITE, $PAGE;
// Stop guests from making requests!
if (isguestuser()) {
    throw new moodle_exception('noguest');
}
$context = context_system::instance();
$PAGE->set_context($context);
require_capability('block/creqmanager:viewrecord', $context);
$courseid = required_param('courseid', PARAM_INT);
$PAGE->set_url('/blocks/creqmanager/coursedetails.php', array('courseid' => $courseid));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('plugindesc', 'block_creqmanager'), new moodle_url(
'/blocks/creqmanager/manager.php'));
$navstring = get_string('coursedetails');
$PAGE->navbar->add($navstring);
$title = "$SITE->shortname: $navstring";
$PAGE->set_title($title);
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($navstring);
if (!($DB->record_exists('course', array('id' => $courseid)))) {
    throw new moodle_exception('invalidcourseid');
}
$record = get_course($courseid);
$course = new core_course_list_element($record);
$gridclass = 'col-sm col-md-12 grid_column_start';
echo html_writer::start_div($gridclass);
$renderer = $PAGE->get_renderer('block_creqmanager');
echo $renderer->course_detail($course);
echo html_writer::end_div();
echo $OUTPUT->footer();
