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
 * CONFIRM AND CREATE NEW COURSE
 *
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/creqmanager/courserequestlib.php');
require_once($CFG->dirroot . '/blocks/creqmanager/lib.php');
require_once($CFG->dirroot . '/blocks/creqmanager/classes/forms/coursenew_form.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/blocks/enrolcode/locallib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
global $CFG,  $DB, $USER, $SITE, $PAGE;
require_login();
// Stop guests from making requests!
if (isguestuser()) {
    throw new moodle_exception('noguest');
}
$context = context_system::instance();
$PAGE->set_context($context);
require_capability('block/creqmanager:addrecord', $context);
$returnurl = new moodle_url('/');
$creqmanagerid = required_param('creqmanagerid', PARAM_INT);
$PAGE->set_url('/blocks/creqmanager/coursenew.php', array('creqmanagerid' => $creqmanagerid));
$select = 'id ='. $creqmanagerid. ' AND status IS NULL';
$currentrecord = $DB->get_record_select('block_creqmanager_records', $select, null, $fields = '*');
if ((!$currentrecord) || ($currentrecord->status != null)) {
    throw new moodle_exception('invalidrequest');
}
// Create the form.
$course = null;
$category = core_course_category::get_default();
$catcontext = context_coursecat::instance($category->id);
$returnto = 0;
// Prepare course and the editor.
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
$overviewfilesoptions = creqmanager_course_overviewfiles_options($course);
// Editor should respect category context if course context is not set.
$editoroptions['context'] = $catcontext;
$editoroptions['subdirs'] = 0;
$course = file_prepare_standard_editor($course, 'summary', $editoroptions, null, 'course', 'summary', null);
if ($overviewfilesoptions) {
    file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, null, 'course', 'overviewfiles', 0);
}
$args = array(
    'course' => $course,
    'category' => $category,
    'editoroptions' => $editoroptions,
    'returnto' => $returnto,
    'returnurl' => $returnurl,
    'creqmanagerid' => $creqmanagerid
);
$mform = new coursenew_form(null, $args);
if ($mform->is_cancelled()) {
    // The form has been cancelled, take them back to what ever the return to is.
    $url = '/blocks/creqmanager/courserequest.php';
    $returnurl = new moodle_url($url, array('t' => 'coursecancel', 'courseid' => 0));
    redirect($returnurl);
} else {
    if (($currentrecord->courseid > 1) && ($currentrecord->status != null)) {
        $returnurl = new moodle_url('/course/view.php', array('id' => $currentrecord->courseid));
        redirect($returnurl, get_string('existingcourse'), null, \core\output\notification::NOTIFY_INFO );
    }
    $_SESSION['creqmanager_session'] = $creqmanagerid;
    if ($data = $mform->get_data()) {
        $user = $DB->get_record('user', array('id' => $currentrecord->createdbyid, 'deleted' => 0), '*', MUST_EXIST);
        $courseconfig = get_config('moodlecourse');
        // Process data if submitted.
        $data->format             = $courseconfig->format;
        $data->newsitems          = $courseconfig->newsitems;
        $data->showgrades         = $courseconfig->showgrades;
        $data->showreports        = $courseconfig->showreports;
        $data->showactivitydates  = $courseconfig->showactivitydates;
        $data->maxbytes           = $courseconfig->maxbytes;
        $data->groupmode          = $courseconfig->groupmode;
        $data->groupmodeforce     = $courseconfig->groupmodeforce;
        $data->visible            = $courseconfig->visible;
        $data->visibleold         = $data->visible;
        $data->lang               = $courseconfig->lang;
        $data->numsections        = $courseconfig->numsections;
        $data->startdate          = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'coursedate'");
        if ($courseconfig->courseenddateenabled) {
            $data->enddate = $data->startdate + $courseconfig->courseduration;
        }
        if (empty($course->id)) {
            // In creating the course.
            $course = creqmanager_create_course($data, $editoroptions);
            if (isset($_SESSION['creqmanager_editingmode'])) {
                unset($_SESSION['creqmanager_editingmode']);
            }
            if (isset($_SESSION['creqmanager_session'])) {
                unset($_SESSION['creqmanager_session']);
            }
            // Get the context of the newly created course.
            $context = context_course::instance($course->id, MUST_EXIST);
            $enrol = enrol_get_plugin('guest');
            if ( ($enrol != null)) {
                if (enrol_is_enabled('guest')) {
                    $instances = enrol_get_instances($course->id, true);
                    $enrolinstance = null;
                    foreach ($instances as $instance) {
                        if ($instance->enrol == 'guest') {
                            $enrolinstance = $instance;
                            break;
                        }
                    }
                    if (empty($enrolinstance->id)) {
                        $instanceid = $enrol->add_default_instance($course);
                        if ($instanceid === null) {
                            $instanceid = $enrol->add_instance($course);
                        }
                    }
                }
            }
            $enrol = enrol_get_plugin('manual');
            if ( ($enrol != null)) {
                if (enrol_is_enabled('manual')) {
                    $instances = enrol_get_instances($course->id, true);
                    $enrolinstance = null;
                    foreach ($instances as $instance) {
                        if ($instance->enrol == 'manual') {
                            $enrolinstance = $instance;
                            break;
                        }
                    }
                    if (empty($enrolinstance->id)) {
                        $instanceid = $enrol->add_default_instance($course);
                        if ($instanceid === null) {
                            $instanceid = $enrol->add_instance($course);
                        }
                    }
                }
            }
            if (!empty($CFG->creatornewroleid) and !is_viewing($context, null, 'moodle/role:assign')
            and !is_enrolled($context, null, 'moodle/role:assign')) {
                // Deal with course creators - enrol them internally with default role.
                // Note: This does not respect capabilities, the creator will be assigned the default role.
                // This is an expected behaviour. See MDL-66683 for further details.
                enrol_try_internal_enrol($course->id, $user->id, $CFG->creatornewroleid);
            }
            $enrol = enrol_get_plugin('manual');
            if ( ($enrol != null) && (enrol_is_enabled('manual'))) {
                $instances = enrol_get_instances($course->id, true);
                $enrolinstance = null;
                foreach ($instances as $instance) {
                    if ($instance->enrol == 'manual') {
                        $enrolinstance = $instance;
                        break;
                    }
                }
                if ((!empty($enrolinstance->id)) && (isset($data->cohortcustomint1))) {
                    $cohortid = $data->cohortcustomint1;
                    if (($cohortid > 0) && (cohort_get_cohort($cohortid, $context))) {
                        $participantscount = $enrol->enrol_cohort($enrolinstance, $cohortid, 5, $data->startdate, 0, null, null);
                    }
                }
            }
            $fullpluginname = 'enrolcode';
            $enabledlist = core\plugininfo\block::get_enabled_plugins();
            $pluginmanagr = \core_plugin_manager::instance();
            $plugininfo = $pluginmanagr->get_plugin_info('block_'.$fullpluginname);
            $status = $plugininfo->get_status();
            $enrocodepluginexists = (in_array($fullpluginname, $enabledlist))
            && (!($status === core_plugin_manager::PLUGIN_STATUS_MISSING));
            if ($enrocodepluginexists) {
                $table = 'block_enrolcode';
                $selectquery = 'courseid='.$course->id. ' AND groupid = 0 AND roleid =';
                if ($DB->record_exists_select($table, $selectquery.'5')) {
                    $datarecord = $DB->get_record_select($table, $selectquery.'5', null, '*', IGNORE_MULTIPLE);
                    $dataobject = new stdClass();
                    $dataobject->id = $datarecord->id;
                    $dataobject->maturity = $data->enddate;
                    $DB->update_record($table, $dataobject);
                } else {
                    $codes = block_enrolcode_lib::create_code($course->id, 5, null, 1, $data->enddate);
                }
                if ($DB->record_exists_select($table, $selectquery.'3')) {
                    $datarecord = $DB->get_record_select($table, $selectquery.'3', null, '*', IGNORE_MULTIPLE);
                    $dataobject = new stdClass();
                    $dataobject->id = $datarecord->id;
                    $dataobject->maturity = $data->enddate;
                    $DB->update_record($table, $dataobject);
                } else {
                    $codes = block_enrolcode_lib::create_code($course->id, 3, null, 1, $data->enddate);
                }
            }
            $enrol = enrol_get_plugin('self');
            if ($courseconfig->groupmode != 0) {
                $newgroupid = array();
                $gdata = new stdClass();
                $gdata->courseid = $course->id;
                $selectquery = "varname = 'groupname'";
                $groupnames = $DB->get_recordset_select('block_creqmanager_config', $select = $selectquery);
                $groupcount = -1;
                foreach ($groupnames as $groupname) {
                    $groupnamevalue = $groupname->value;
                    $words = preg_split("/\s+/", $groupnamevalue);
                    $acronym = "";
                    foreach ($words as $w) {
                        $acronym .= $w[0];
                    }
                    $groupcount++;
                    if (!(groups_get_group_by_idnumber($course->id, $course->shortname.$acronym))) {
                        $gdata->idnumber = $course->shortname.$acronym;
                        $gdata->name = $groupnamevalue;
                        $gdata->description = $groupnamevalue;;
                        $gdata->descriptionformat = FORMAT_HTML;
                        $gdata->enrolmentkey = (($enrol != null) &&
                        (enrol_is_enabled('self')) &&
                        ($currentrecord->enrolkey != null)) ? $currentrecord->enrolkey.$acronym : null;
                        $newgroupid[$groupcount] = groups_create_group($gdata);
                    } else {
                        $newgroupid[$groupcount] = groups_get_group_by_idnumber($course->id, $course->shortname.$acronym)->id;
                    }
                }
                $groupnames->close();
                if ((!empty($newgroupid)) && ($enrocodepluginexists) && ($groupcount > -1)) {
                    foreach ($newgroupid as $key => $value) {
                        $table = 'block_enrolcode';
                        $selectquery = 'courseid='.$course->id. ' AND roleid = 5 AND groupid =';
                        if ($DB->record_exists_select($table, $selectquery.$value)) {
                            $datarecord = $DB->get_record_select($table, $selectquery.$value, null, '*', IGNORE_MULTIPLE);
                            $dataobject = new stdClass();
                            $dataobject->id = $datarecord->id;
                            $dataobject->maturity = $data->enddate;
                            $DB->update_record($table, $dataobject);
                        } else {
                            $codes = block_enrolcode_lib::create_code($course->id, 5, (int)$value, 1, $data->enddate);
                        }
                    }
                }
            }
            if ( ($enrol != null) && (enrol_is_enabled('self'))) {
                $customint1 = (!empty($newgroupid)) && ($groupcount > -1) ? 1 : 0;
                $password = '';
                if (($enrol->get_config('requirepassword'))&& ($currentrecord->enrolkey != null)) {
                    $password = $currentrecord->enrolkey;
                }
                $instances = enrol_get_instances($course->id, true);
                $enrolinstance = null;
                foreach ($instances as $instance) {
                    if ($instance->enrol == 'self') {
                        $enrolinstance = $instance;
                        break;
                    }
                }
                if (empty($enrolinstance->id)) {
                    $fields = $enrol->get_instance_defaults();
                    $fields['customint1'] = $customint1;
                    if ($password != '') {
                        $fields['password'] = $password;
                    }
                    $instanceid = $enrol->add_instance($course, $fields);
                } else {
                    $enroldata = new stdClass();
                    $enroldata->customint1 = $customint1;
                    $enroldata->expirynotify = $enrolinstance->expirynotify;
                    $enroldata->expirythreshold = $enrolinstance->expirythreshold;
                    if ($password != '') {
                        $enroldata->password = $password;
                    }
                    $instanceid = $enrol->update_instance($enrolinstance, $enroldata);
                }
            }
            $newcourseid = (int)$course->id;
            if ($newcourseid > 0) {
                $currentrecord->status = 'COMPLETE';
                $currentrecord->courseid = $newcourseid;
                $DB->update_record('block_creqmanager_records', $currentrecord);
                $returnurl = new moodle_url('/blocks/creqmanager/coursedetails.php', array('courseid' => $newcourseid));
                if (isset($_SESSION['creqmanager_editingmode'])) {
                    unset($_SESSION['creqmanager_editingmode']);
                }
                if (isset($_SESSION['creqmanager_session'])) {
                    unset($_SESSION['creqmanager_session']);
                }
                redirect($returnurl, get_string('eventcoursecreated'), null, \core\output\notification::NOTIFY_SUCCESS);
            }
            redirect($returnurl, get_string('courserequestfailed'), null, \core\output\notification::NOTIFY_ERROR);
            die();
        }
    }
}
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'block_creqmanager'), new moodle_url('/blocks/creqmanager/manager.php'));
$navstring = get_string("addnewcourse");
$PAGE->navbar->add($navstring);
$title = "$SITE->shortname: $navstring";
$PAGE->set_title($title);
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($navstring);
$mform->focus();
$mform->display();
echo $OUTPUT->footer();
