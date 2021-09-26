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
 * COURSE REQUEST MANAGER
 *
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_creqmanager extends block_base {
    /** Init for the block */
    public function init() {
        $this->title = get_string('plugindesc', 'block_creqmanager');
        $plugin = new stdClass();
    }
    /** Get the content for the block */
    public function get_content() {
        global $CFG;
        global $COURSE;
        global $DB;
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass();
        $this->content->text = block_creqmanager_get_html_content();
        $this->content->footer = '';
        return $this->content;
    }
    // Authorized Personnel Only.
    public function applicable_formats() {
        return array('site-index' => true, 'my' => true);
    }
    /**
     * prevent addition of more than one block instance
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        return false;
    }
    /**
     * prevent user from hiding the block
     *
     * @return boolean
     */
    public function instance_can_be_hidden() {
        return false;
    }
}
/**
 * This is the main content generation function that is responsible for
 * returning the relevant content to the user depending on what status
 * they have (admin / regular staff).
 */
function block_creqmanager_get_html_content() {
    global $USER, $DB, $CFG;
    $context = context_system::instance();
    $adminhtml = '';
    $numrequestspending = $DB->count_records('block_creqmanager_records', array('status' => null,  'deleted' => 0));
    $requestspendingforuser = $DB->count_records('block_creqmanager_records',
    array('status' => null, 'createdbyid' => $USER->id, 'courseid' => null, 'deleted' => 0));
    // Admin UI elements.
    if (has_capability('block/creqmanager:editconfig', $context)) {
        if ($numrequestspending > 0 ) {
            $url = new moodle_url('/blocks/creqmanager/admin.php');
            $icon = html_writer::tag('i', '', array('class' => 'fa fa-list-ul mr-2'));
            $adminhtml .= '<p>'. html_writer::link($url, $icon.get_string('blockadmin', 'block_creqmanager').
            ' ['.$numrequestspending.']',
            array('class' => 'btn btn-default ml-1',
            'aria-label' => get_string('blockadmin', 'block_creqmanager'),
            'title' => get_string('blockadmin', 'block_creqmanager') )) . '</p>';
        }
        $url = new moodle_url('/blocks/creqmanager/adminsettings.php',  array('id' => '0'));
        $icon = html_writer::tag('i', '', array('class' => 'fa fa-cog mr-2'));
        $adminhtml .= '<p>'. html_writer::link($url, $icon.get_string('configureadminsettings', 'block_creqmanager'),
        array('class' => 'btn btn-default ml-1', 'aria-label' => get_string('configureadminsettings', 'block_creqmanager'),
        'title' => get_string('configureadminsettings', 'block_creqmanager') )) . '</p>';
    }
    // UI components for regular users of the block.
    // to allow requests to be made.
    $blockcontent = '';
    if ((isloggedin() && $USER->id != 1)) {
        $url = new moodle_url('/blocks/creqmanager/courserequest.php');
        $icon = html_writer::tag('i', '', array('class' => 'fa fa-file-o mr-2'));
        $blockcontent .= '<hr /><p>'. html_writer::link($url, $icon.get_string('blockrequest', 'block_creqmanager'),
        array('class' => 'btn btn-default ml-1', 'aria-label' => get_string('blockrequest', 'block_creqmanager'),
        'title' => get_string('blockrequest', 'block_creqmanager') )) . '</p>';
        if ($requestspendingforuser > 0) {
            $url = new moodle_url('/blocks/creqmanager/manager.php');
            $icon = html_writer::tag('i', '', array('class' => 'fa fa-file-text mr-2'));
            $blockcontent .= '<p>'.html_writer::link($url, $icon.get_string('blockadmin', 'block_creqmanager').
            ' ['.$requestspendingforuser.']',
            array('class' => 'btn btn-default ml-1',
            'aria-label' => get_string('blockadmin', 'block_creqmanager'),
            'title' => get_string('blockadmin', 'block_creqmanager') )) . '</p>';
        }
        $url = new moodle_url('/blocks/creqmanager/manager.php',  array('status' => 'COMPLETE'));
        $icon = html_writer::tag('i', '', array('class' => 'fa fa-hdd-o mr-2'));
        $blockcontent .= '<p>'. html_writer::link($url, $icon.get_string('allarchivedrequests', 'block_creqmanager'),
        array('class' => 'btn btn-default ml-1', 'aria-label' => get_string('allarchivedrequests', 'block_creqmanager'),
        'title' => get_string('allarchivedrequests', 'block_creqmanager') )) . '</p>';
        $blockcontent .= "<hr />". $adminhtml;
    }
    return $blockcontent;
}
