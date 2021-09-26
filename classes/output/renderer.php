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
 * Contains renderers for the course request manager summary pages.
 *
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot.'/course/classes/management_renderer.php');
require_once($CFG->libdir . '/tablelib.php');
class block_creqmanager_renderer extends core_course_management_renderer {
    /**
     * Renderers detailed course information.
     *
     * @param core_course_list_element $course The course to display details for.
     * @return string
     */
    public function course_detail(core_course_list_element $course) {
        global $CFG,  $DB, $USER, $SITE;
        $details = \core_course\management\helper::get_course_detail_array($course);
        $coursecontext = context_course::instance($course->id);
        $countparticipants = count_enrolled_users($coursecontext);
        $fullname = $details['fullname']['value'];
        $html = html_writer::start_div('course-detail card');
        $html .= html_writer::start_div('card-header');
        $html .= html_writer::tag('h3', $fullname, array('id' => 'course-detail-title',
                'class' => 'card-title', 'tabindex' => '0'));
        $html .= html_writer::end_div();
        $html .= html_writer::start_div('card-body');
        $html .= $this->course_detail_actions($course);
        foreach ($details as $class => $data) {
            $html .= $this->detail_pair($data['key'], $data['value'], $class);
            if ($data['key'] == 'Short name') {
                $html .= $this->detail_pair(get_string('participants'), $countparticipants, $class);
            }
        }
        $html .= html_writer::tag('h4', get_string('password', 'enrol_self'), array('class' => 'font-weight-bold mt-2'));
        $html .= $this->course_enrolcodestable($course->id);
        $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
        $html .= html_writer::link($courseurl, get_string('continuetocourse'),
        array('class' => 'btn btn-primary ml-1 mt-2',
        'aria-label' => get_string('continuetocourse'),
        'title' => get_string('continuetocourse')));
        $newrequesturl = new moodle_url('/blocks/creqmanager/courserequest.php');
        $html .= html_writer::link($newrequesturl, get_string('blockrequest', 'block_creqmanager'),
        array('class' => 'btn btn-secondary ml-2 mt-2', 'aria-label' => get_string('blockrequest', 'block_creqmanager'),
        'title' => get_string('blockrequest', 'block_creqmanager')));
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        return $html;
    }
    public function course_detail_actions(core_course_list_element $course) {
        $actions = \core_course\management\helper::get_course_detail_actions($course);
        if (empty($actions)) {
            return '';
        }
        $options = array();
        $allowedoptions = array('View', 'Edit', 'Enrolled users', 'Backup', 'Restore');
        foreach ($actions as $action) {
            if (in_array($action['string'], $allowedoptions )) {
                $options[] = $this->action_link($action['url'],
                $action['string'], null, array('class' => 'btn btn-sm btn-secondary mr-1 mb-3'));
            }
        }
        return html_writer::div(join('', $options), 'listing-actions course-detail-listing-actions');
    }
    private function course_enrolcodestable($courseid) {
        global $CFG,  $DB, $USER, $SITE;
        $courseid = (int) $courseid;
        $coursecontext = context_course::instance($courseid);
        $rolenames = role_get_names($coursecontext, ROLENAME_BOTH, true);
        $fullpluginname = 'enrolcode';
        $enabledlist = core\plugininfo\block::get_enabled_plugins();
        $pluginmanagr = \core_plugin_manager::instance();
        $plugininfo = $pluginmanagr->get_plugin_info('block_'.$fullpluginname);
        $status = $plugininfo->get_status();
        $enrocodepluginexists = (in_array($fullpluginname, $enabledlist)) &&
        (!($status === core_plugin_manager::PLUGIN_STATUS_MISSING));
        $table1 = new html_table();
        $select = "courseid ='". $courseid. "'";
        $recordscount = $DB->count_records_select('block_enrolcode', $select, null);
        if ($recordscount > 0) {
            if ($enrocodepluginexists) {
                $enrolcodes = $DB->get_recordset_select('block_enrolcode', $select, null, 'maturity', $fields = '*');
                $table1->data = array();
                $table1->head = array( get_string('code:accesscode', 'block_enrolcode'),
                get_string('code:accesscode', 'block_enrolcode'),
                get_string('role'), get_string('group'), get_string('maturity', 'block_enrolcode'));
                foreach ($enrolcodes as $enrolcode) {
                    $groupname = $DB->get_field('groups', 'name', array('id' => $enrolcode->groupid));
                    $qrcodelink = $CFG->wwwroot . '/blocks/enrolcode/enrol.php?code='. $enrolcode->code;
                    $qrcode = $CFG->wwwroot . '/blocks/enrolcode/pix/qr.php?format=base64&txt=' . base64_encode($qrcodelink);
                    $qrcodehtml = '<img src="'. $qrcode. '" width="50px" heigth="50px" alt="QR Code for Enrol" />';
                    $rolename = $rolenames[$enrolcode->roleid];
                    $groupname = (!empty($groupname) ? $groupname : '-');
                    $maturityhtml = (!empty($enrolcode->maturity)
                    ? date('d-M-Y', $enrolcode->maturity) : get_string('maturity:immediately', 'block_enrolcode'));
                    $table1->data[] = array($qrcodehtml, format_string($enrolcode->code),
                    format_string($rolename), format_string($groupname), $maturityhtml);
                }
                $enrolcodes->close();
            }
        }
        $table2 = new html_table();
        $enrol = enrol_get_plugin('self');
        $groups = groups_get_all_groups($courseid);
        if (($enrol != null) && (enrol_is_enabled('self'))) {
            $instances = enrol_get_instances($courseid, true);
            $selfinstances = null;
            foreach ($instances as $instance) {
                if ($instance->enrol == 'self') {
                    $selfenrolinstances[$instance->id] = $instance;
                }
            }
        }
        if ((isset($selfenrolinstances)) && (is_array($selfenrolinstances))) {
            $table2->head = array(get_string('password', 'enrol_self'),
            get_string('role'), get_string('group'), get_string('enroltimeend', 'enrol'));
            foreach ($selfenrolinstances as $selfenrolinstance) {
                $rolename = $rolenames[$selfenrolinstance->roleid];
                $timeend = (!empty ($selfenrolinstance->enrolenddate) ) ? (date('d-M-Y', $selfenrolinstance->enrolenddate)) : '-';
                $table2->data[] = array(format_string($selfenrolinstance->password),
                format_string($rolename), '-', format_string($timeend) );
            }
            foreach ($groups as $group) {
                if ($group->enrolmentkey) {
                    $table2->data[] = array(format_string($group->enrolmentkey), '-', $group->name, '-' );
                }
            }
        }
        if (count($table1->data) < 1) {
            $table1 = $table2;
        }
        return html_writer::table($table1);
    }
}
