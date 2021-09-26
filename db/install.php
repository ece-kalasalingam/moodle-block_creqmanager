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
defined('MOODLE_INTERNAL') || die();

function xmldb_block_creqmanager_install() {
    global $CFG, $DB;
    $newrec = new stdClass();
    $newrec->varname = 'selfcat';
    $newrec->value = '0';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'autokey';
    $newrec->value = '1';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'naming';
    $newrec->value = '1';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'snaming';
    $newrec->value = '1';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'coursedate';
    $newrec->value = strtotime('today midnight');
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'aystartdate';
    $newrec->value = strtotime('today midnight');
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'entryfield1';
    $newrec->value = 'Short Name';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'entryfield2';
    $newrec->value = 'Full Name';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'optfield1status';
    $newrec->value = 'enabled';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'optfield1';
    $newrec->value = 'Class';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'entryfield3';
    $newrec->value = 'Term';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'entryfield3value';
    $newrec->value = 'Summer';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'entryfield3value';
    $newrec->value = 'Winter';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'optfield2status';
    $newrec->value = 'disabled';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'optfield2';
    $newrec->value = 'Mode';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'groupname';
    $newrec->value = 'Emerging Learners';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'groupname';
    $newrec->value = 'Proficient Learners';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
    $newrec = new stdClass();
    $newrec->varname = 'groupname';
    $newrec->value = 'Normal Learners';
    $DB->insert_record('block_creqmanager_config', $newrec, false);
}
