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
$string['pluginname'] = 'Course Request Manager';
$string['plugindesc'] = 'Course Request Manager';

// Block links.
$string['blockadmin'] = 'Request queue';
$string['blockconfig'] = 'Configuration';
$string['blockrequest'] = 'Make a new request';
$string['blockmanage'] = 'Manage your requests';
$string['allarchivedrequests'] = 'All archived requests';

// New course form.
$string['editrequest'] = 'Edit a draft request';
$string['courserequestline1'] = 'Please refer to in-house guidelines for naming courses.';
$string['courserequestline2'] = 'A draft course request is already available. Click Next to complete it. Or Click Cancel to start a new request.';


// Generic.
$string['configurecoursemanagersettings'] = ' Configure settings';
$string['confirmdelete'] = 'Do you want to delete this?';
$string['addnew'] = 'Add';
$string['Enabled'] = 'Enabled';
$string['Disabled'] = 'Disabled';
$string['values'] = 'Values ';
$string['status'] = 'Status';
$string['savedchanges'] = 'Changes saved successfully.';

// Cofigure Admin Home.
$string['configureadminsettings'] = 'Admin settings';
$string['configureadminsettings_desc'] = 'Additional settings for Course Request Manager';
$string['configurecourseformfields'] = '  Configure the request form';
$string['configurecourseformfields_desc'] = 'The request form will prompt the user for the shortname and long name of the course to be created. This link will allow you to name those fields and also enable optional settings.';

// Admin Settings.
$string['namingconvention'] = 'Course Full naming convention';
$string['namingconvention_help'] = 'Please choose a full name format for newly created courses';
$string['snamingconvention'] = 'Course Short naming convention';
$string['snamingconvention_help'] = 'Please choose a short name format for newly created courses';

$string['configureenrolmentkey'] = 'Enrolment key';
$string['configureenrolmentkey_help'] = 'Course Request Manager can generate an automatic enrolment key or you may choose to prompt the user for an enrolment key of their choice. If you choose to prompt the user, a field for entering the enrolment key will be added to the first page of the request form.';
$string['creqmanagerenrolmentoption1'] = 'Automatically generated key';
$string['creqmanagerenrolmentoption0'] = 'Prompt user for key';
$string['creqmanagerenrolmentoption2'] = 'Do not ask for key';
$string['allowselfcategorization'] = 'Allow user to select category';
$string['allowselfcategorization_help'] = 'When enabled, the user will be prompted to select a location in the Moodle catalogue to place their course';

$string['termname_help'] = 'Select the name for the new courses';
$string['termname'] = 'Course {$a}';
$string['coursedate'] = 'Course Start date';
$string['coursedate_help'] = 'Please select a default start date for new courses.';
$string['aystartdate'] = 'Academic Year Start date';
$string['aystartdate_help'] = 'Please select a default start date for the academic year. Will be used in naming the semester.';
$string['termyearpos'] = '{$a} year position';
$string['termyearpos_help'] = 'Define if the year should be added as suffix or prefix';
$string['termyearposprefixspace_desc'] = 'Year is added as prefix with space (Example: "2013 Summer term")';
$string['termyearposprefixnospace_desc'] = 'Year is added as prefix without space (Example: "2013S")';
$string['termyearpossuffixspace_desc'] = 'Year is added as suffix with space (Example: "Summer term 2013")';
$string['termyearpossuffixnospace_desc'] = 'Year is added as suffix without space (Example: "S2013")';
$string['termyearseparation'] = '{$a} second year separation';
$string['termyearseparation_desc'] = 'If the timespan of the {$a} includes New Year\'s day, define how this second year should be separated from the first one';
$string['termyearseparationhyphen_desc'] = 'Separate with a hyphen (Example: "2013-2014")';
$string['termyearseparationslash_desc'] = 'Separate with a slash (Example: "2013/2014")';
$string['termyearseparationunderscore_desc'] = 'Separate with a underscore (Example: "2013_2014")';
$string['termyearseparationnosecondyear_desc'] = 'Don\'t add the second year (Example: "2013")';
$string['termyearseparation_help'] = 'If the timespan includes New Year\'s day, define how this second year should be separated from the first one';
$string['deleteallrequests'] = 'Delete all current and archived requests';
$string['deleteonlyarch'] = 'Delete only archived requests';
$string['clearhistorytitle'] = 'Clear history';
$string['totalrequests'] = 'Total number of requests';
$string['entryfieldsinstruction1'] = 'Configure the first page of the course request form. The first page of the request form is used to accept values from the user for the course short name and the course full name as required by Moodle. These may be described differently by your organisation. For example you may use a course code (short name) and a course name (full name) to describe your courses. For each of the fields below, you may change the name of the field as it appears to the user following your organization naming conventions and guidelines.';
$string['entryfield1'] = 'Configure field for course code';
$string['entryfield1_help'] = 'The course code will be used in framing the short name of a Moodle course. The short name is used in Navigation and Subject line of emails by Moodle.';
$string['entryfield2'] = 'Configure field for course name';
$string['entryfield2_help'] = 'The course name will be used in framing the full name of a Moodle course. The full name is used in Top of the course page and Courses list by Moodle.';
$string['entryfield3'] = 'Configure field for the term name';
$string['entryfield3_help'] = ' The tern name as used in your orgainization.';
$string['entryfield3values'] = '{$a} names ';
$string['optfield1'] = 'Optional text field name';
$string['optfield1_help'] = 'The name used for grouping of students in your organization like Section, Class, Cohort, Team';
$string['optfield2'] = 'Optional dropdown field name';
$string['optfield2_help'] = ' You may wish to add an optional drop down list with some values that will help you categorise the new course. For example your organisation may offer courses in full time mode, part time mode, distance education mode, online only mode etc. You can add these options to the optional dropdown list and allow users to select one when making a new course request.';
$string['optfield2values'] = 'Optional dropdown field values ';
$string['timeless'] = 'Is course a timeless?';

// Coureexists.
$string['courseexists'] = 'Course Exists';
$string['courseexists_desc'] = 'It looks like the course you are requesting may already exists on this site. So, start again making a new request or';
$string['catlocation'] = 'Catalogue location';
$string['lecturingstaff'] = 'Lecturing staff';
$string['viewcourse'] = 'go to the course.';

// General strings.
$string['errors'] = 'Errors';
$string['debuginfo'] = 'Debug info';
$string['exceptionmessage'] = 'Error thrown with {$a}';

// Error messages.
$string['cannotrequestcourse'] = ' Sorry your account does not have sufficient privileges to request a course. You need to be assigned to a system role with sufficient privileges.';
$string['cannoteditrequest'] = ' Sorry your account does not have sufficient privileges to add or edit a record. You need to be assigned to a system role with sufficient privileges.';
$string['cannotviewrecords'] = ' Sorry your account does not have sufficient privileges to view records. You need to be assigned to a system role with sufficient privileges.';
$string['cannoteditconfig'] = ' Sorry your account does not have sufficient privileges to edit the configuration. You need to be assigned to a system role with sufficient privileges.';
$string['request_rule1'] = 'Please enter a value in this field.';
$string['request_rule2'] = 'Please select a value.';
$string['request_rule3'] = 'Please enter an enrolment key.';

// Accessing capabilities.
$string['creqmanager:addrecord'] = 'Add record';
$string['creqmanager:viewrecord'] = 'View record';
$string['creqmanager:editconfig'] = 'Edit config';
$string['cmanager:myaddinstance'] = 'Add instance to Dashboard';
$string['cmanager:addinstance'] = 'Add instance';

// Welcome module_manager.
$string['creqmanagerwelcome'] = 'Welcome to Course Request Manager. Before requesting a new course, please check your local guidelines.';
