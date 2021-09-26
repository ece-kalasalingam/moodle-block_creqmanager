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
 * COURSE REQUEST MANAGER  MAIN PAGE
 *
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/creqmanager/lib.php');
$PAGE->requires->js("/blocks/creqmanager/js/creqmanagerjs.js");
require_login();
// Stop guests from making requests!
if (isguestuser()) {
    throw new moodle_exception('noguest');
}
$context = context_system::instance();
$PAGE->set_context($context);
if (!(has_capability('block/creqmanager:viewrecord', $context))) {
    throw new moodle_exception('nopermissions', 'error', '', get_string('cannotviewrecords', 'block_creqmanager'));
}

global $CFG, $USER, $DB, $SITE;

$sort         = optional_param('sort', 'createdate', PARAM_ALPHANUMEXT);
$dir          = optional_param('dir', 'DESC', PARAM_ALPHA);
$page         = optional_param('page', 0, PARAM_INT);
$status       = optional_param('status', null, PARAM_ALPHANUMEXT);
$createdbyid  = $USER->id;
$perpage      = 1;
$recordscount = get_cmanager_records(false, $status, $createdbyid);
$records = get_cmanager_records_listing($sort, $dir, $page, $perpage, $status, $createdbyid);
$toggleid = optional_param('toggleid', null, PARAM_INT);
if (isset($toggleid) ) {
    $type = required_param('type', PARAM_TEXT);
    if ($type == 'delete') {
        block_creqmanager_toggledeletion(1, $toggleid);
    } else if ($type == 'restore') {
        block_creqmanager_toggledeletion(0, $toggleid);
    }
    exit();
}

$html = '<table class="table table-sm table-hover table-striped">';
$html2 = '';
foreach ($records as $record) {
    $field1 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'entryfield1'");
    $field2 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'entryfield2'");
    $optfield1 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'optfield1'");
    $optfield2 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'optfield2'");
    $recordtime = userdate($record->createdate, get_string('strftimerecentfull', 'core_langconfig'));
    $coursestatus = ($record->status == null) ? ' PENDING ' : $record->status;
    $deletestatus = ($record->deleted) ? ' - DELETED ' : '';
    if ($createdbyid < 3) {
        $coursestatus .= $deletestatus;
    }
    $user = $DB->get_record('user', array('id' => $record->createdbyid, 'deleted' => 0), '*', MUST_EXIST);
    $fullname = fullname($user);
    $html .= '<caption>'.get_string('courserequest'). ' # ' . $record->id . '</caption>';
    $html .= '<thead><tr><th scope="col">'.get_string('name'). '</th>';
    $html .= '<th scope="col">' . get_string('values', 'block_creqmanager') . '</th></tr></thead>';
    $html .= '<tbody>';
    $html .= '<tr>';
    $html .= '<th scope="row">' . $field1 . '</th>';
    $html .= '<td>'. $record->field1 . '</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<th scope="row">' . $field2 . '</th>';
    $html .= '<td>'. $record->field2 . '</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<th scope="row">' . $optfield1 . '</th>';
    $html .= '<td>'. $record->optfield1 . '</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<th scope="row">' . $optfield2 . '</th>';
    $html .= '<td>'. $record->optfield2 . '</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<th scope="row">' . get_string('status', 'block_creqmanager') . '</th>';
    $html .= '<td>'. $coursestatus . '</td>';
    $html .= '</tr>';
    if (isset($record->courseid)) {
        $url = new moodle_url('/blocks/creqmanager/coursedetails.php', array('courseid' => $record->courseid));
        $html .= '<tr>';
        $html .= '<th scope="row">' .  get_string('coursedetails') . '</th>';
        $html .= '<td>'.  html_writer::link($url, get_string('coursedetails'),
        array( 'aria-label' => get_string('coursedetails'),
        'title' => get_string('coursedetails') )) . '</td>';
        $html .= '</tr>';
    }
    $html .= '<tr>';
    $html .= '<th scope="row">' . get_string('timecreatedcourse') . '</th>';
    $html .= '<td>'. $recordtime . '</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<th scope="row">' . get_string('eventusercreated') . '</th>';
    $html .= '<td>'. $fullname . '</td>';
    $html .= '</tr>';
    if ((!(isset($record->status))) && (!(isset($record->courseid)))  && (!($record->deleted))) {
        $url = new moodle_url('/blocks/creqmanager/courserequest.php', array('recordid' => $record->id));
        $html2 .= html_writer::link($url, get_string('edit'),
        array( 'class' => 'btn btn-sm btn-secondary mr-1 mb-3',
        'aria-label' => get_string('edit'),
        'title' => get_string('edit') ));
    }
    if ($createdbyid < 3) {
        if (!($record->deleted)) {
            $label = 'return deleteRecord('.$record->id.', "delete")';
            $html2 .= html_writer::div(get_string('delete'),  'btn btn-sm btn-secondary mr-1 mb-3', array('onclick' => $label));
        } else {
            $label = 'return deleteRecord('.$record->id.', "restore")';
            $html2 .= html_writer::div(get_string('restore'),  'btn btn-sm btn-secondary mr-1 mb-3', array('onclick' => $label));
        }
    }
}
$html .= '</table>';

// Navigation Bar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'block_creqmanager'), new moodle_url('/blocks/creqmanager/manager.php'));
$baseurl = new moodle_url('/blocks/creqmanager/manager.php', array('status' => $status));
$PAGE->set_url($baseurl);

$PAGE->set_title($SITE->shortname.': '.   get_string('pluginname', 'block_creqmanager'));
$PAGE->set_heading($SITE->fullname);


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'block_creqmanager'), 2);
if ($status == null) {
    echo  html_writer::div(get_string('creqmanagerwelcome', 'block_creqmanager'));
    $url = new moodle_url('/blocks/creqmanager/courserequest.php');
    echo html_writer::link($url, get_string('blockrequest', 'block_creqmanager'),
    array( 'class' => 'btn btn-sm btn-secondary mr-1 mt-3',
    'aria-label' => get_string('blockrequest', 'block_creqmanager'),
    'title' => get_string('blockrequest', 'block_creqmanager') ));
}
echo $html;
echo $html2;

echo $OUTPUT->paging_bar($recordscount, $page, $perpage, $baseurl);
flush();
echo $OUTPUT->footer();

