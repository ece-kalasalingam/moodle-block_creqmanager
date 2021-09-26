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
 * COURSE REQUEST MANAGER LIBRARY
 *
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Return HTML displaying the names of lecturers linked to email addresses.
 *
 */
defined('MOODLE_INTERNAL') || die();
function block_creqmanager_get_lecturers($courseid) {
    global $DB, $CFG;
    if (! $course = $DB->get_record("course", array("id" => $courseid))) {
        throw new moodle_exception('invalidcourseid');
    }
    $course = $DB->get_record("course", array("id" => $courseid));
    $contextid = $DB->get_field('context', 'id', array ('instanceid' => $courseid,
    'contextlevel' => 50), $strictness = IGNORE_MULTIPLE);
    $userids = $DB->get_records('role_assignments', array('roleid' => '3', 'contextid' => $contextid));
    $lecturerhtml = '';
    foreach ($userids as $singleuser) {
        $user = $DB->get_record('user', array('id' => $singleuser->userid), $fields = '*', $strictness = IGNORE_MULTIPLE);
        $lecturerhtml .= '<i class="fa fa-envelope-o" aria-hidden="true"></i> <a href = "mailto:' . $user->email . '">' .
        $user->firstname . ' ' . $user->lastname . '</a><br>';
    }
    return $lecturerhtml;
}
/**
 * Get a collection of teacher ids (role 3)
 *
 * for a specific course, separated by spaces.
 */
function block_creqmanager_get_lecturer_ids_space_sep($courseid) {
    global $DB, $CFG;
    if (! $course = $DB->get_record("course", array("id" => $courseid))) {
        throw new moodle_exception('invalidcourseid');
    }
    $contextid = $DB->get_field('context', 'id', array ('instanceid' => $courseid, 'contextlevel' => 50),
    $strictness = IGNORE_MULTIPLE);
    $userids = $DB->get_records('role_assignments', array('roleid' => '3' , 'contextid' => $contextid));
    $lecturerhtml = '';
    foreach ($userids as $singleuser) {
        $userrecord = $DB->get_record('user', array('id' => $singleuser->userid), $fields = '*', $strictness = IGNORE_MULTIPLE);
        $lecturerhtml .= $userrecord->id . ' ';
    }
    return $lecturerhtml;
}
/**
 * Add a new item in config table
 * @params varname representing the row to be updated in table
 * @params valuetoadd the varchar to be updated in database table
 */
function block_creqmanager_add_new_item($varname, $valuetoadd) {
    global $CFG, $DB;
    $lastinsertid = 0;
    $object = new stdClass();
    $object->varname = $varname;
    $object->value = addslashes($valuetoadd);
    $lastinsertid = $DB->insert_record('block_creqmanager_config', $object);
    if ($lastinsertid == 0) {
        echo json_encode(array("success" => "0", "id" => $lastinsertid));
    }
    echo json_encode(array("success" => "1", "valueadded" => $object->value, "id" => $lastinsertid));
}
/**
 * Delete an item
 * @params integer value of the id to be deleted in config database table
 */
function block_creqmanager_delete_item ($deleteid) {
    global $CFG, $DB;
    $DB->delete_records('block_creqmanager_config', array('id' => $deleteid));
    echo json_encode(array("success" => "1"));
}
/**
 * Return the Academic Term string
 */
function block_creqmanager_academic_term($timeless = 0) {
    global $CFG, $DB;
    if ($timeless == 1) {
        return '';
    }
    $termtitle = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'entryfield3'");
    $termname = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'termname'");
    $aystartdate = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'aystartdate'");
    $termyearpos = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'termyearpos'");
    $termyearseparation = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'termyearseparation'");
    $aydigit1 = date('Y', $aystartdate);
    $aydigit2 = $aydigit1 + 1;
    if (substr( $aydigit2, 2) == substr( $aydigit2, 2)) {
        $aydigit2 = substr($aydigit2, -2);
    }
    switch ($termyearseparation) {
        case 1:
            $ay = $aydigit1 . '-' . $aydigit2;
            break;
        case 2:
            $ay = $aydigit1 . '/' . $aydigit2;
            break;
        case 3:
            $ay = $aydigit1 . '_' . $aydigit2;
            break;
        default:
            $ay = $aydigit1;
            break;
    }
    switch ($termyearpos) {
        case 1:
            $term = $ay. ' ' . $termname. ' ' . $termtitle;
            break;
        case 2:
            $term = $ay . ' ' . $termname;
            break;
        case 3:
            $term = $termname. ' ' . $termtitle .' ' . $ay;
            break;
        default:
            $term = $termname. ' ' . $ay;
            break;
    }
    return strtoupper($term);
}
/**
 * Returns the course shortname as per Admin settings
 * @params array data
 * @return string shortname
 */
function block_creqmanager_shortname($data) {
    global $DB;
    $snaming = $DB->get_field('block_creqmanager_config', 'value', array('varname' => 'snaming'), IGNORE_MULTIPLE);
    $term = block_creqmanager_academic_term($data->timeless);
    if ($term != '') {
        $term = ' (' . $term . ')';
    }
    switch ($snaming) {
        case 1:
            $shortname = $data->field1;
            break;
        case 2:
            $shortname = $data->field1 . $term;
            break;
        case 3:
            $shortname = $data->field1 . ' - '.$data->optfield1. $term;
            break;
        case 4:
            $shortname = $data->field1 . ' - '.$data->optfield2. $term;
            break;
        case 5:
            $shortname = $data->field1. ' - '.$data->optfield2. ' - ' . $data->optfield1.$term;
            break;
        default:
            $shortname = $data->field1;
            break;
    }
    return strtoupper($shortname);
}
/**
 * Returns the course fullname as per Admin settings
 * @params array data
 * @return string fullname
 */
function block_creqmanager_fullname($data) {
    global $DB;
    $naming = $DB->get_field('block_creqmanager_config', 'value', array('varname' => 'naming'), IGNORE_MULTIPLE);
    switch ($naming) {
        case 1:
            $fullname = ucwords(strtolower($data->field2));
            break;
        case 2:
            $fullname = strtoupper($data->field1) . ' - '. ucwords(strtolower($data->field2));
            break;
        case 3:
            $fullname = ucwords(strtolower($data->field2)) . ' (' . strtoupper($data->field1) . ')';
            break;
        default:
            $fullname = ucwords(strtolower($data->field2));
            break;
    }
    return $fullname;
}
/**
 * Cancels the incomplete requests
 */
function block_creqmanager_canceldraftrequest() {
    global $DB;
    if (isset($_SESSION['creqmanager_editingmode'])) {
        unset($_SESSION['creqmanager_editingmode']);
    }
    if (isset($_SESSION['creqmanager_session'])) {
        $idtocancel = $_SESSION['creqmanager_session'];
        $currentrecord = $DB->get_record('block_creqmanager_records',
        array('id' => $idtocancel), $fields = 'id, status', IGNORE_MULTIPLE);
        if (($currentrecord) && (is_null($currentrecord->status))) {
            $newrec = $currentrecord;
            $newrec->id = $idtocancel;
            $newrec->deleted = 1;
            $DB->update_record('block_creqmanager_records', $newrec);
        }
        unset($_SESSION['creqmanager_session']);
    }
}
/***
 * Generate enrolment key
 * @return string
 */
function block_creqmanager_generate_password($maxlen = 10) {
    global $CFG;
    if (empty($CFG->passwordpolicy)) {
        $fillers = PASSWORD_DIGITS;
        $wordlist = file($CFG->wordlist);
        $word1 = trim($wordlist[rand(0, count($wordlist) - 1)]);
        $word2 = trim($wordlist[rand(0, count($wordlist) - 1)]);
        $filler1 = $fillers[rand(0, strlen($fillers) - 1)];
        $password = $word1 . $filler1 . $word2;
    } else {
        $minlen = 4;
        $digits = $CFG->minpassworddigits;
        $lower = $CFG->minpasswordlower;
        $upper = $CFG->minpasswordupper;
        $total = $lower + $upper + $digits;
        // Var minlength should be the greater one of the two ( $minlen and $total ).
        $minlen = $minlen < $total ? $total : $minlen;
        // Var maxlen can never be smaller than minlen.
        $maxlen = $minlen > $maxlen ? $minlen : $maxlen;
        $additional = $maxlen - $total;
        // Make sure we have enough characters to fulfill complexity requirements.
        $passworddigits = PASSWORD_DIGITS;
        while ($digits > strlen($passworddigits)) {
            $passworddigits .= PASSWORD_DIGITS;
        }
        $passwordlower = PASSWORD_LOWER;
        while ($lower > strlen($passwordlower)) {
            $passwordlower .= PASSWORD_LOWER;
        }
        $passwordupper = PASSWORD_UPPER;
        while ($upper > strlen($passwordupper)) {
            $passwordupper .= PASSWORD_UPPER;
        }
        // Now mix and shuffle it all.
        $password = str_shuffle (substr(str_shuffle ($passwordlower), 0, $lower) .
                                 substr(str_shuffle ($passwordupper), 0, $upper) .
                                 substr(str_shuffle ($passworddigits), 0, $digits)  .
                                 substr(str_shuffle ($passwordlower .
                                                     $passwordupper .
                                                     $passworddigits ), 0 , $additional ));
    }
    return substr ($password, 0, $maxlen);
}
/**
 * Gnerates the strings for select in Termyearposition
 * @return array
 */
function block_creqmanager_termyearpos() {
    // Possible year positions for later use.
    $termyearpos[1] = get_string('termyearposprefixspace_desc', 'block_creqmanager');
    $termyearpos[2] = get_string('termyearposprefixnospace_desc', 'block_creqmanager');
    $termyearpos[3] = get_string('termyearpossuffixspace_desc', 'block_creqmanager');
    $termyearpos[4] = get_string('termyearpossuffixnospace_desc', 'block_creqmanager');
    return $termyearpos;
}
/**
 * Gnerates the strings for select in Termyearseparation
 * @return array
 */
function block_creqmanager_termyearseparation() {
    // Possible year separators for later use.
    $termyearseparation[1] = get_string('termyearseparationhyphen_desc', 'block_creqmanager');
    $termyearseparation[2] = get_string('termyearseparationslash_desc', 'block_creqmanager');
    $termyearseparation[3] = get_string('termyearseparationunderscore_desc',  'block_creqmanager');
    $termyearseparation[4] = get_string('termyearseparationnosecondyear_desc', 'block_creqmanager');
    return $termyearseparation;
}
/**
 * Return list of creqmanger records.
 *
 * @param string $sort An SQL field to sort by
 * @param string $dir The sort direction ASC|DESC
 * @param int $page The page or records to return
 * @param int $recordsperpage The number of records to return per page
 * @return array Array of {@link $USER} records
 */
function get_cmanager_records_listing($sort='createdate', $dir='ASC', $page=0, $recordsperpage=0,
                           $status=null, $userid=1) {
    global $DB, $CFG;
    $select = " id <> 0 ";
    if ($sort) {
        $sort = " ORDER BY $sort $dir";
    }
    if ($userid > 2) {
        if (($status != '') && ($status != ' ') && ($status != null) ) {
            $select .= " AND status = '" . $status . "' ";
        } else {
            $select .= " AND status IS NULL ";
        }
        $select1 = " AND deleted <> 1 ";
        $select2 = " AND createdbyid = " . $userid;
        $select .= $select1 . $select2;
    }
    return $DB->get_records_sql("SELECT * FROM {block_creqmanager_records} WHERE $select $sort ", null, $page, $recordsperpage);
}
/**
 * Returns a subset of cmanager records
 *
 * @global object
 * @uses DEBUG_DEVELOPER
 * @uses SQL_PARAMS_NAMED
 * @param bool $get If false then only a count of the records is returned
 * @param bool $deleted A switch to show/hide deleted status records
 * @param array $exceptions A list of IDs to ignore, eg 2,4,5,8,9,10
 * @param string $sort A SQL snippet for the sorting criteria to use
 * @param string $page The page or records to return
 * @param string $recordsperpage The number of records to return per page
 * @param string $fields A comma separated list of fields to be returned from the chosen table.
 * @return array|int|bool  {@link $USER} records unless get is false in which case the integer count is returned.
 * False is returned if an error is encountered.
 */
function get_cmanager_records($get=true, $status=null, $userid=1,  array $exceptions=null, $sort='createdate ASC',
                  $page='', $recordsperpage='', $fields='*', $extraselect='', array $extraparams=null) {
    global $DB, $CFG;
    $params = null;
    if ($get && !$recordsperpage) {
        debugging('Call  with $get = true no $recordsperpage limit. ' .
                'On large installations, this will probably cause an out of memory error. ' .
                'Please think again and change your code so that it does not try to ' .
                'load so much data into memory.', DEBUG_DEVELOPER);
    }

    $select = " id <> 0 ";
    if ($userid > 2) {
        if (($status != '') && ($status != ' ') && ($status != null) ) {
            $select = " status = '" . $status . "' ";
        } else {
            $select = " status IS NULL ";
        }
        $select1 = " AND deleted <> 1 ";
        $select2 = " AND createdbyid = " . $userid;
        $select .= $select1 . $select2;
    }

    if ($exceptions) {
        list($exceptions, $eparams) = $DB->get_in_or_equal($exceptions, SQL_PARAMS_NAMED, 'ex', false);
        $params = $params + $eparams;
        $select .= " AND id $exceptions";
    }

    if ($extraselect) {
        $select .= " AND $extraselect";
        $params = $params + (array)$extraparams;
    }

    if ($get) {
        return $DB->get_records_select('block_creqmanager_records', $select, $params, $sort, $fields, $page, $recordsperpage);
    } else {
        return $DB->count_records_select('block_creqmanager_records', $select, $params);
    }
}
/**
 * Delete or restore the requests
 */
function block_creqmanager_toggledeletion($deletestatus = 1, $recordid = 0) {
    global $DB;
    if ($recordid > 0) {
        $currentrecord = $DB->get_record('block_creqmanager_records',
        array('id' => $recordid), $fields = 'id', IGNORE_MULTIPLE);
        if ($currentrecord) {
            $newrec = $currentrecord;
            $newrec->id = $recordid;
            $newrec->deleted = $deletestatus;
            $DB->update_record('block_creqmanager_records', $newrec);
        }
    }
}
