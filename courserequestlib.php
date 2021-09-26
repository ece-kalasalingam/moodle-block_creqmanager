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
 * Library of useful functions
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 */
defined('MOODLE_INTERNAL') || die;
use core_courseformat\base as course_format;
require_once($CFG->dirroot.'/course/format/lib.php');
/**
 * Returns options to use in course overviewfiles filemanager
 * @param null|stdClass|core_course_list_element|int $course either object that has 'id' property or just the course id;
 * may be empty if course does not exist yet (course create form)
 * @return array|null array of options such as maxfiles, maxbytes, accepted_types, etc.
 *     or null if overviewfiles are disabled
 */
function creqmanager_course_overviewfiles_options($course) {
    global $CFG;
    if (empty($CFG->courseoverviewfileslimit)) {
        return null;
    }
    // Create accepted file types based on config value, falling back to default all.
    $acceptedtypes = (new \core_form\filetypes_util)->normalize_file_types($CFG->courseoverviewfilesext);
    if (in_array('*', $acceptedtypes) || empty($acceptedtypes)) {
        $acceptedtypes = '*';
    }
    $options = array(
        'maxfiles' => $CFG->courseoverviewfileslimit,
        'maxbytes' => $CFG->maxbytes,
        'subdirs' => 0,
        'accepted_types' => $acceptedtypes
    );
    if (!empty($course->id)) {
        $options['context'] = context_course::instance($course->id);
    } else if (is_int($course) && $course > 0) {
        $options['context'] = context_course::instance($course);
    }
    return $options;
}
/**
 * Create a course and either return a $course object
 *
 * Please note this functions does not verify any access control,
 * the calling code is responsible for all validation (usually it is the form definition).
 *
 * @param array $editoroptions course description editor options
 * @param object $data  - all the data needed for an entry in the 'course' table
 * @return object new course instance
 */
function creqmanager_create_course($data, $editoroptions = null) {
    global $DB, $CFG;
    // Check the categoryid - must be given for all new courses.
    $category = $DB->get_record('course_categories', array('id' => $data->category), '*', MUST_EXIST);
    // Check if the shortname already exists.
    if (!empty($data->shortname)) {
        if ($DB->record_exists('course', array('shortname' => $data->shortname))) {
            throw new moodle_exception('shortnametaken', '', '', $data->shortname);
        }
    }
    // Check if the idnumber already exists.
    if (!empty($data->idnumber)) {
        if ($DB->record_exists('course', array('idnumber' => $data->idnumber))) {
            throw new moodle_exception('courseidnumbertaken', '', '', $data->idnumber);
        }
    }
    if (empty($CFG->enablecourserelativedates)) {
        // Make sure we're not setting relative dates when the setting is disabled.
        unset($data->relativedatesmode);
    }
    if ($errorcode = creqmanager_course_validate_dates((array)$data)) {
        throw new moodle_exception($errorcode);
    }
    // Check if timecreated is given.
    $data->timecreated  = !empty($data->timecreated) ? $data->timecreated : time();
    $data->timemodified = $data->timecreated;
    // Place at beginning of any category.
    $data->sortorder = 0;
    if ($editoroptions) {
        // Summary text is updated later, we need context to store the files first.
        $data->summary = '';
        $data->summary_format = FORMAT_HTML;
    }
    // Get default completion settings as a fallback in case the enablecompletion field is not set.
    $courseconfig = get_config('moodlecourse');
    $defaultcompletion = !empty($CFG->enablecompletion) ? $courseconfig->enablecompletion : COMPLETION_DISABLED;
    $enablecompletion = $data->enablecompletion ?? $defaultcompletion;
    // Unset showcompletionconditions when completion tracking is not enabled for the course.
    if ($enablecompletion == COMPLETION_DISABLED) {
        unset($data->showcompletionconditions);
    } else if (!isset($data->showcompletionconditions)) {
        // Show completion conditions should have a default value when completion is enabled. Set it to the site defaults.
        // This scenario can happen when a course is created through data generators or through a web service.
        $data->showcompletionconditions = $courseconfig->showcompletionconditions;
    }
    if (!isset($data->visible)) {
        // Data not from form, add missing visibility info.
        $data->visible = $category->visible;
    }
    $data->visibleold = $data->visible;
    $newcourseid = $DB->insert_record('course', $data);
    $context = context_course::instance($newcourseid, MUST_EXIST);
    if ($editoroptions) {
        // Save the files used in the summary editor and store.
        $data = file_postupdate_standard_editor($data, 'summary', $editoroptions, $context, 'course', 'summary', 0);
        $DB->set_field('course', 'summary', $data->summary, array('id' => $newcourseid));
        $DB->set_field('course', 'summaryformat', $data->summary_format, array('id' => $newcourseid));
    }
    if ($overviewfilesoptions = creqmanager_course_overviewfiles_options($newcourseid)) {
        // Save the course overviewfiles.
        $data = file_postupdate_standard_filemanager($data, 'overviewfiles',
        $overviewfilesoptions, $context, 'course', 'overviewfiles', 0);
    }
    // Update course format options.
    course_get_format($newcourseid)->update_course_format_options($data);
    $course = course_get_format($newcourseid)->get_course();
    fix_course_sortorder();
    // Purge appropriate caches in case fix_course_sortorder() did not change anything.
    cache_helper::purge_by_event('changesincourse');
    // Trigger a course created event.
    $event = \core\event\course_created::create(array(
        'objectid' => $course->id,
        'context' => context_course::instance($course->id),
        'other' => array('shortname' => $course->shortname, 'fullname' => $course->fullname))
        );
    $event->trigger();
    // Setup the blocks.
    blocks_add_default_course_blocks($course);
    // Create default section and initial sections if specified (unless they've already been created earlier).
    // We do not want to call course_create_sections_if_missing() because to avoid creating course cache.
    $numsections = isset($data->numsections) ? $data->numsections : 0;
    $existingsections = $DB->get_fieldset_sql('SELECT section from {course_sections} WHERE course = ?', [$newcourseid]);
    $newsections = array_diff(range(0, $numsections), $existingsections);
    foreach ($newsections as $sectionnum) {
        course_create_section($newcourseid, $sectionnum, true);
    }
    // Save any custom role names.
    save_local_role_names($course->id, (array)$data);
    // Set up enrolments.
    enrol_course_updated(true, $course, $data);
    // Update course tags.
    if (isset($data->tags)) {
        core_tag_tag::set_item_tags('core', 'course', $course->id, context_course::instance($course->id), $data->tags);
    }
    // Save custom fields if there are any of them in the form.
    $handler = core_course\customfield\course_handler::create();
    // Make sure to set the handler's parent context first.
    $coursecatcontext = context_coursecat::instance($category->id);
    $handler->set_parent_context($coursecatcontext);
    // Save the custom field data.
    $data->id = $course->id;
    $handler->instance_form_save($data, true);
    return $course;
}
/**
 * Validates course start and end dates.
 *
 * Checks that the end course date is not greater than the start course date.
 *
 * $coursedata['startdate'] or $coursedata['enddate'] may not be set, it depends on the form and user input.
 *
 * @param array $coursedata May contain startdate and enddate timestamps, depends on the user input.
 * @return mixed False if everything alright, error codes otherwise.
 */
function creqmanager_course_validate_dates($coursedata) {
    // If both start and end dates are set end date should be later than the start date.
    if (!empty($coursedata['startdate']) && !empty($coursedata['enddate']) &&
            ($coursedata['enddate'] < $coursedata['startdate'])) {
        return 'enddatebeforestartdate';
    }
    // If start date is not set end date can not be set.
    if (empty($coursedata['startdate']) && !empty($coursedata['enddate'])) {
        return 'nostartdatenoenddate';
    }
    return false;
}
