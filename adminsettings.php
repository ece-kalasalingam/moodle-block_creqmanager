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
 * COURSE REQUEST MANAGER SETTINGS
 *
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/creqmanager/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/blocks/creqmanager/classes/forms/adminsettings_form.php'
);
$id = optional_param('id', null, PARAM_INT);
$type = optional_param('type', null, PARAM_TEXT);
// Navigation Bar .
$PAGE->set_url('/blocks/creqmanager/adminsettings.php', array( 'id' => $id, 'type' => $type));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'block_creqmanager'), new moodle_url(
'/blocks/creqmanager/manager.php'));
$PAGE->navbar->add(get_string('configureadminsettings', 'block_creqmanager'));
require_login();
$context = context_system::instance();
require_capability('block/creqmanager:editconfig', $context);
$title = get_string('pluginname', 'block_creqmanager');
$PAGE->requires->js("/blocks/creqmanager/js/creqmanagerjs.js");
$htmlfragment1 = '';
$htmlfragment2 = '';
if (isset($id)) {
    $args = array(
    'id' => $id
    );
    $url = new moodle_url('/blocks/creqmanager/adminsettings.php', array('id' => 0));
    $icon = html_writer::tag('i', '', array('class' => 'fa fa-home fa-lg mr-2'));
    $content = '<p>'. html_writer::link($url, $icon.get_string(
    'configurecoursemanagersettings', 'block_creqmanager'),
    array('class' => 'btn btn-default ml-1', 'aria-label' => get_string(
    'configurecoursemanagersettings', 'block_creqmanager'),
    'title' => get_string('configurecoursemanagersettings', 'block_creqmanager') )) .
    '</p>';
    if ($id === 1) {
        $title .= ': '.get_string('configureadminsettings', 'block_creqmanager');
        $htmlfragment1 .= $content;
        $autokey = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'autokey'");
        $snaming = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'snaming'");
        $selfcat = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'selfcat'");
        $entryfield3opted = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'entryfield3opted'");
        $termyearpos = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'termyearpos'");
        $termyearseparation = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'termyearseparation'");
        $timestampcoursedate = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'coursedate'");
        $timestampaystartdate = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'aystartdate'");
        $coursedate = getdate($timestampcoursedate);
        $aystartdate = getdate($timestampaystartdate);
        $datedefaults = array('day' => $coursedate['mday'], 'month' => $coursedate['mon'], 'year' => $coursedate['year']);
        $aydate = array('day' => $aystartdate['mday'], 'month' => $aystartdate['mon'], 'year' => $aystartdate['year']);
        $formvalues = array('naming' => $snaming, 'snaming' => $snaming, 'selfcat' => $selfcat,
        'autokey' => $autokey, 'entryfield3opted' => $entryfield3opted,
        'coursedate' => $datedefaults, 'aystartdate' => $aydate,
        'termyearpos' => $termyearpos, 'termyearseparation' => $termyearseparation,
        'id' => $id);
        $mform = new block_creqmanager_adminsettings_form(null, $args);
        $htmlfragment2 .= '
            <h3 class = "mt-3">' . get_string('clearhistorytitle', 'block_creqmanager') . '</h3>
            <input class = "btn btn-default" type = "button" onClick = "deleteAll()" value = "'
            .get_string('deleteallrequests', 'block_creqmanager').'">
            <input class = "btn btn-default" type = "button" onClick = "deleteArchOnly()"
            value = "'.get_string('deleteonlyarch', 'block_creqmanager').'">
        ';
    } else if ($id === 2) {
        $title .= ': '.get_string('configurecourseformfields', 'block_creqmanager');
        $htmlfragment1 .= $content;
        if (isset($type) ) {
            if ($type == 'add') {
                $valuetoadd = required_param('valuetoadd', PARAM_TEXT);
                block_creqmanager_add_new_item('optfield2value', $valuetoadd);
            }
            if ($type == 'addterm') {
                $valuetoadd = required_param('valuetoadd', PARAM_TEXT);
                block_creqmanager_add_new_item('entryfield3value', $valuetoadd);
            }
            if ($type == 'addcohort') {
                $valuetoadd = required_param('valuetoadd', PARAM_TEXT);
                block_creqmanager_add_new_item('customint1value', $valuetoadd);
            }
            if ($type == 'addgroupname') {
                $valuetoadd = required_param('valuetoadd', PARAM_TEXT);
                block_creqmanager_add_new_item('groupname', $valuetoadd);
            }
            if ($type == 'delete') {
                $deleteid = required_param('deleteid', PARAM_INT);
                block_creqmanager_delete_item($deleteid);
            }
            exit();
        } else {
            // Get the field values.
            $entryfield1 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'entryfield1'");
            $entryfield2 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'entryfield2'");
            $entryfield3 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'entryfield3'");
            $optfield2 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'optfield2'");
            $optfield2status = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'optfield2status'");
            $optfield1 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'optfield1'");
            $optfield1status = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'optfield1status'");
            $formvalues = array('entryfield1' => $entryfield1, 'entryfield2' =>
            $entryfield2, 'entryfield3' => $entryfield3,
            'optfield2' => $optfield2, 'optfield2status' => $optfield2status,
            'optfield1' => $optfield1, 'optfield1status' => $optfield1status,
            'id' => $id);
            $htmlfragment1 .= get_string('entryfieldsinstruction1', 'block_creqmanager');
            $mform = new block_creqmanager_adminsettings_form(null, $args);
            $htmlfragment2 .= '<hr /><div class = "mform">';
            $entryfield3itemshtml = '';
            $selectquery = "varname = 'entryfield3value'";
            $entryfield3items = $DB->get_recordset_select('block_creqmanager_config', $select = $selectquery);
            foreach ($entryfield3items as $item) {
                $entryfield3itemshtml  .= '<div class = "row" id = "' . $item->id. '">';
                $entryfield3itemshtml  .= '<div class = "col-6">' .$item->value . '</div>';
                $entryfield3itemshtml  .= '<div class = "col-2"><a href = "#"
                onclick = "return deleteItem('.$item->id.')" data-id = "'.$item->id;
                $entryfield3itemshtml  .= '"aria-label = "' . get_string('confirmdelete', 'block_creqmanager');
                $entryfield3itemshtml  .= '" title = "' . get_string('confirmdelete', 'block_creqmanager');
                $entryfield3itemshtml  .= '"><i class = "icon fa fa-trash fa-fw" aria-hidden = "true"></i></a></div>';
                $entryfield3itemshtml  .= '</div>';
            }
            $entryfield3items->close();
            $htmlfragment2  .= ' <div class = "row mb-2">
            <div class = "col-sm-2">' .get_string('entryfield3values', 'block_creqmanager', $entryfield3, true) . '</div>
            <div id = "entryfield3values">' . $entryfield3itemshtml . '</div>
            </div>';
            $htmlfragment2  .= '<div class = "input-group mb-3">';
            $htmlfragment2  .= '<input type = "text" id = "newterm" name = "newterm"
            class = "form-control col-md-2" size = "30">';
            $url = new moodle_url('/blocks/creqmanager/adminsettings.php', array( 'id' => $id));
            $url->out(false);
            $label = 'return addNewTerm("'.get_string('confirmdelete', 'block_creqmanager').'")';
            $htmlfragment2  .= html_writer::link($url, get_string('addnew', 'block_creqmanager'),
            array('class' => 'btn btn-secondary ml-1',
            'aria-label' => get_string('addnew', 'block_creqmanager'),
            'title' => get_string('addnew', 'block_creqmanager'), 'onclick' => $label));
            $htmlfragment2  .= '</div><hr />';
            $optfield2itemshtml = '';
            $selectquery = "varname = 'optfield2value'";
            $optfield2items = $DB->get_recordset_select('block_creqmanager_config', $select = $selectquery);
            foreach ($optfield2items as $item) {
                $optfield2itemshtml  .= '<div class = "row" id = "' . $item->id. '">';
                $optfield2itemshtml  .= '<div class = "col-6">' .$item->value . '</div>';
                $optfield2itemshtml  .= '<div class = "col-2"><a href = "#" onclick = "return
                deleteItem('.$item->id.')" data-id = "'.$item->id;
                $optfield2itemshtml  .= '"aria-label = "' . get_string('confirmdelete', 'block_creqmanager');
                $optfield2itemshtml  .= '" title = "' . get_string('confirmdelete', 'block_creqmanager');
                $optfield2itemshtml  .= '"><i class = "icon fa fa-trash fa-fw"aria-hidden = "true"></i></a></div>';
                $optfield2itemshtml  .= '</div>';
            }
            $optfield2items->close();
            $htmlfragment2  .= ' <div class = "row mb-2">
            <div class = "col-sm-2">' .get_string('optfield2values', 'block_creqmanager') . '</div>
            <div id = "optfield2values">' . $optfield2itemshtml . '</div>
            </div>';
            $htmlfragment2  .= '<div class = "input-group mb-3">';
            $htmlfragment2  .= '<input type = "text" id = "newitem" name = "newitem"
            class = "form-control col-md-2" size = "30">';
            $url = new moodle_url('/blocks/creqmanager/adminsettings.php', array( 'id' => $id));
            $url->out(false);
            $label = 'return addNewItem("'.get_string('confirmdelete', 'block_creqmanager').'")';
            $htmlfragment2  .= html_writer::link($url, get_string('addnew', 'block_creqmanager'),
            array('class' => 'btn btn-secondary ml-1',
            'aria-label' => get_string('addnew', 'block_creqmanager'),
            'title' => get_string('addnew', 'block_creqmanager'), 'onclick' => $label));
            $htmlfragment2  .= '</div><hr />';
            $groupnameshtml = '';
            $selectquery = "varname = 'groupname'";
            $groupnames = $DB->get_recordset_select('block_creqmanager_config', $select = $selectquery);
            foreach ($groupnames as $groupname) {
                $groupnameshtml  .= '<div class = "row" id = "' . $groupname->id. '">';
                $groupnameshtml  .= $groupname->value;
                $groupnameshtml  .= '<div class = "col-2"><a href = "#" onclick = "return
                deleteItem('.$groupname->id.')" data-id = "'.$groupname->id;
                $groupnameshtml  .= '"aria-label = "' . get_string('confirmdelete', 'block_creqmanager');
                $groupnameshtml  .= '" title = "' . get_string('confirmdelete', 'block_creqmanager');
                $groupnameshtml  .= '"><i class = "icon fa fa-trash fa-fw" aria-hidden = "true"></i></a></div>';
                $groupnameshtml  .= '</div>';
            }
            $groupnames->close();
            $htmlfragment2  .= ' <div class = "row mb-2">
            <div class = "col-sm-2">' .get_string('groupname', 'group') . '</div>
            <div id = "groupnames">' . $groupnameshtml . '</div>
            </div>';
            $htmlfragment2  .= '<div class = "input-group mb-3">';
            $htmlfragment2  .= '<input type = "text" id = "newgroupname"
            name = "newgroupname" class = "form-control col-md-2" size = "30">';
            $url = new moodle_url('/blocks/creqmanager/adminsettings.php', array( 'id' => $id));
            $url->out(false);
            $label = 'return addNewGroupName("'.get_string('confirmdelete', 'block_creqmanager').'")';
            $htmlfragment2  .= html_writer::link($url, get_string('addnew', 'block_creqmanager'),
            array('class' => 'btn btn-secondary ml-1',
            'aria-label' => get_string('addnew', 'block_creqmanager'),
            'title' => get_string('addnew', 'block_creqmanager'), 'onclick' => $label));
            $htmlfragment2  .= '</div><hr />';
            $customint1itemshtml = '';
            $customint1array = array();
            $selectquery = "varname = 'customint1value'";
            $customint1items = $DB->get_recordset_select('block_creqmanager_config', $select = $selectquery);
            foreach ($customint1items as $item) {
                $customint1array[$item->id] = $item->value;
            }
            $customint1items->close();
            $cohorts = array('' => get_string('choosedots'));
            $allcohorts = cohort_get_available_cohorts(\context_course::instance(1), 5, 0, 0);
            foreach ($allcohorts as $c) {
                    $cohorts[$c->id] = format_string($c->name);
            }
            foreach ($customint1array as $key => $value) {
                $customint1itemshtml  .= '<div  id = "' . $key. '">';
                $customint1itemshtml  .= $cohorts[$value];
                $customint1itemshtml  .= '<a href = "#" onclick = "return deleteItem('.$key.')" data-id = "'.$key;
                $customint1itemshtml  .= '"aria-label = "' . get_string('confirmdelete', 'block_creqmanager');
                $customint1itemshtml  .= '" title = "' . get_string('confirmdelete', 'block_creqmanager');
                $customint1itemshtml  .= '"><i class = "icon fa fa-trash fa-fw" aria-hidden = "true"></i></a>';
                $customint1itemshtml  .= '</div>';
            }
            $htmlfragment2  .= ' <div class = "row mb-2">
            <div class = "col-sm-2 mr-2">' .get_string('cohort', 'cohort') . '</div>
            <div id = "customint1values">' . $customint1itemshtml . '</div>
            </div>';
            $htmlfragment2  .= '<div class = "input-group mb-3">';
            $htmlfragment2  .= '<select name = "customint1" id = "customint1" class = "form-control col-md-2"  >';
            foreach ($cohorts as $key => $val) {
                if (is_array($customint1array)) {
                    if (in_array($key, $customint1array) == false) {
                        $htmlfragment2  .= '<option value = "'. $key .'">'.$val.'</option>';
                    }
                }
            }
            $htmlfragment2  .= '</select>';
            $url = new moodle_url('/blocks/creqmanager/adminsettings.php', array( 'id' => $id));
            $url->out(false);
            $label = 'return addNewCohort("'.get_string('confirmdelete', 'block_creqmanager').'")';
            $htmlfragment2  .= html_writer::link($url, get_string('addnew', 'block_creqmanager'),
            array('class' => 'btn btn-secondary ml-1', 'aria-label' => get_string('addnew', 'block_creqmanager'),
            'title' => get_string('addnew', 'block_creqmanager'), 'onclick' => $label));
            $htmlfragment2  .= '</div><hr />';
            $htmlfragment2  .= '</div>';
        }
    } else {
        $title  .= get_string('configurecoursemanagersettings', 'block_creqmanager');
        $url = new moodle_url('/blocks/creqmanager/adminsettings.php', array('id' => '1'));
        $icon = html_writer::tag('i', '', array('class' => 'fa fa-wrench fa-lg mr-2'));
        $htmlfragment1  .= '<div>'. html_writer::link($url, $icon.get_string('configureadminsettings', 'block_creqmanager'),
        array('class' => 'btn btn-default ml-1 font-weight-bold',
        'aria-label' => get_string('configureadminsettings', 'block_creqmanager'),
        'title' => get_string('configureadminsettings', 'block_creqmanager') ));
        $htmlfragment1  .= '<p class = "col-12">'. get_string('configureadminsettings_desc', 'block_creqmanager'). '</p></div>';
        $url = new moodle_url('/blocks/creqmanager/adminsettings.php', array('id' => '2'));
        $icon = html_writer::tag('i', '', array('class' => 'fa fa-cogs fa-lg mr-2'));
        $htmlfragment1  .= '<div>'. html_writer::link($url, $icon.get_string('configurecourseformfields', 'block_creqmanager'),
        array('class' => 'btn btn-default ml-1 font-weight-bold',
        'aria-label' => get_string('configurecourseformfields', 'block_creqmanager'),
        'title' => get_string('configurecourseformfields', 'block_creqmanager') ));
        $htmlfragment1  .= '<p class = "col-12">'. get_string('configurecourseformfields_desc', 'block_creqmanager'). '</p></div>';
    }
    if (isset($mform)) {
        if ($mform->is_cancelled()) {
            redirect(new moodle_url('/blocks/creqmanager/adminsettings.php', array('id' => '0')));
        } else if ($fromform = $mform->get_data()) {
            if ($id === 1) {
                if (enrol_is_enabled('self')) {
                    $autokey = required_param('autokey', PARAM_TEXT);
                }
                $naming = required_param('naming', PARAM_TEXT);
                $selfcat = required_param('selfcat', PARAM_TEXT);
                $snaming = required_param('snaming', PARAM_TEXT);
                $entryfield3opted = required_param('entryfield3opted', PARAM_TEXT);
                $termyearseparation = required_param('termyearseparation', PARAM_TEXT);
                $termyearpos = required_param('termyearpos', PARAM_TEXT);
                // Retrieve updated date and convert to timestamp.
                $coursedate = required_param_array('coursedate', PARAM_TEXT);
                $coursedate = mktime (0, 0, 0, $coursedate['month'], $coursedate['day'], $coursedate['year']);
                $aystartdate = required_param_array('aystartdate', PARAM_TEXT);
                $aystartdate = mktime (0, 0, 0, $aystartdate['month'], $aystartdate['day'], $aystartdate['year']);
                $newrec = new stdClass();
                // Update autokey.
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'autokey'");
                $newrec->id = $rowid;
                $newrec->varname = 'autokey';
                $newrec->value = $autokey;
                $DB->update_record('block_creqmanager_config', $newrec);
                // Update naming.
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'naming'");
                $newrec->id = $rowid;
                $newrec->varname = 'naming';
                $newrec->value = $naming;
                $DB->update_record('block_creqmanager_config', $newrec);
                // Update selfcat.
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'selfcat'");
                $newrec->id = $rowid;
                $newrec->varname = 'selfcat';
                $newrec->value = $selfcat;
                $DB->update_record('block_creqmanager_config', $newrec);
                // Update snaming.
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'snaming'");
                $newrec->id = $rowid;
                $newrec->varname = 'snaming';
                $newrec->value = $snaming;
                $DB->update_record('block_creqmanager_config', $newrec);
                // Update termname.
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'entryfield3opted'");
                $newrec->id = $rowid;
                $newrec->varname = 'entryfield3opted';
                $newrec->value = $entryfield3opted;
                if (!$rowid) {
                    $DB->insert_record('block_creqmanager_config', $newrec);
                } else {
                    $DB->update_record('block_creqmanager_config', $newrec);
                }
                // Update termyearposition.
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'termyearpos'");
                $newrec->id = $rowid;
                $newrec->varname = 'termyearpos';
                $newrec->value = $termyearpos;
                if (!$rowid) {
                    $DB->insert_record('block_creqmanager_config', $newrec);
                } else {
                    $DB->update_record('block_creqmanager_config', $newrec);
                }
                // Update termyearseparation.
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'termyearseparation'");
                $newrec->id = $rowid;
                $newrec->varname = 'termyearseparation';
                $newrec->value = $termyearseparation;
                if (!$rowid) {
                    $DB->insert_record('block_creqmanager_config', $newrec);
                } else {
                    $DB->update_record('block_creqmanager_config', $newrec);
                }
                // Add the new course start date to the config.
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'coursedate'");
                $newrec->id = $rowid;
                $newrec->varname = 'coursedate';
                $newrec->value = $coursedate;
                if (!$rowid) {
                    $DB->insert_record('block_creqmanager_config', $newrec);
                } else {
                    $DB->update_record('block_creqmanager_config', $newrec);
                }
                // Add the new academic year start date to the config.
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'aystartdate'");
                $newrec->id = $rowid;
                $newrec->varname = 'aystartdate';
                $newrec->value = $aystartdate;
                if (!$rowid) {
                    $DB->insert_record('block_creqmanager_config', $newrec);
                } else {
                    $DB->update_record('block_creqmanager_config', $newrec);
                }
            }
            if ($id === 2) {
                $entryfield1 = required_param('entryfield1', PARAM_TEXT);
                $entryfield2 = required_param('entryfield2', PARAM_TEXT);
                $entryfield3 = required_param('entryfield3', PARAM_TEXT);
                $optfield1 = required_param('optfield1', PARAM_TEXT);
                $optfield1status = required_param('optfield1status', PARAM_TEXT);
                $optfield2 = required_param('optfield2', PARAM_TEXT);
                $optfield2status = required_param('optfield2status', PARAM_TEXT);
                $newrec = new stdClass();
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'entryfield1'");
                $newrec->id = $rowid;
                $newrec->varname = 'entryfield1';
                $newrec->value = $entryfield1;
                $DB->update_record('block_creqmanager_config', $newrec);
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'entryfield2'");
                $newrec->id = $rowid;
                $newrec->varname = 'entryfield2';
                $newrec->value = $entryfield2;
                $DB->update_record('block_creqmanager_config', $newrec);
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'entryfield3'");
                $newrec->id = $rowid;
                $newrec->varname = 'entryfield3';
                $newrec->value = $entryfield3;
                $DB->update_record('block_creqmanager_config', $newrec);
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'optfield1'");
                $newrec->id = $rowid;
                $newrec->varname = 'optfield1';
                $newrec->value = $optfield1;
                $DB->update_record('block_creqmanager_config', $newrec);
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'optfield1status'");
                $newrec->id = $rowid;
                $newrec->varname = 'optfield1status';
                $newrec->value = $optfield1status;
                $DB->update_record('block_creqmanager_config', $newrec);
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'optfield2'");
                $newrec->id = $rowid;
                $newrec->varname = 'optfield2';
                $newrec->value = $optfield2;
                $DB->update_record('block_creqmanager_config', $newrec);
                $rowid = $DB->get_field_select('block_creqmanager_config', 'id', "varname = 'optfield2status'");
                $newrec->id = $rowid;
                $newrec->varname = 'optfield2status';
                $newrec->value = $optfield2status;
                $DB->update_record('block_creqmanager_config', $newrec);
            }
            \core\notification::add(get_string('savedchanges', 'block_creqmanager'), \core\output\notification::NOTIFY_SUCCESS);
        } else {
            $mform->set_data($formvalues);
        }
    }
}
$PAGE->set_title($SITE->shortname.': '.   $title);
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($title, $level = 2);
echo html_writer::tag('div', $htmlfragment1);
if (isset($mform)) {
    $mform->focus();
    $mform->display();
}
echo html_writer::tag('div', $htmlfragment2);
echo $OUTPUT->footer();
