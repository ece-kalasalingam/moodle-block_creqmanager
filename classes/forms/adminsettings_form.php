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
 * Admin settings Form
 *
 * Main form for the admin settings
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL')||die();
require_once($CFG->libdir.'/formslib.php');
class block_creqmanager_adminsettings_form extends moodleform {
    public function definition() {
        global $CFG, $USER, $DB;
        $id = $this->_customdata['id'];
        $mform =& $this->_form; // Don't forget the underscore!
        if ($id === 1) {
            $entryfield1 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'entryfield1'");
            $entryfield2 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'entryfield2'");
            $optfield1 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'optfield1'");
            $optfield1status = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'optfield1status'");
            $optfield2 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'optfield2'");
            $optfield2status = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'optfield2status'");
            $entryfield3 = $DB->get_field_select('block_creqmanager_config', 'value', "varname = 'entryfield3'");
            $namingoptions = array('1' => $entryfield2,
            '2' => $entryfield1.' - '.$entryfield2,
            '3' => $entryfield2 . ' ('. $entryfield1 . ')');
            $snamingoptions = array('1' => $entryfield1, '2' => $entryfield1.' ('. $entryfield3.')');
            if ($optfield1status == 'enabled') {
                $snamingoptions['3'] = $entryfield1.' - '.$optfield1.' ('.$entryfield3.')';
            }
            if ($optfield2status == 'enabled') {
                $snamingoptions['4'] = $entryfield1.' - '.$optfield2.' ('.$entryfield3.')';
            }
            if (($optfield1status == 'enabled')&&($optfield2status == 'enabled')) {
                $snamingoptions['5'] = $entryfield1.' - '.$optfield2.' - '.$optfield1.'('.$entryfield3.')';
            }
            $enrolkeyoptions = array(get_string('creqmanagerenrolmentoption0', 'block_creqmanager'),
            get_string('creqmanagerenrolmentoption1', 'block_creqmanager'));
            $wherequery = "varname = 'entryfield3value'";
            $entryield3items = $DB->get_recordset_select('block_creqmanager_config', $select = $wherequery);
            foreach ($entryield3items as $item) {
                   $entryfield3options[$item->value] = $item->value;
            }
            $entryield3items->close();
            $termyearpos = block_creqmanager_termyearpos();
            $termyearseparation = block_creqmanager_termyearseparation();

            $mform->addElement('select', 'naming', get_string('namingconvention', 'block_creqmanager'), $namingoptions);
            $mform->addHelpButton('naming', 'namingconvention', 'block_creqmanager');
            $mform->addElement('select', 'snaming', get_string('snamingconvention', 'block_creqmanager'), $snamingoptions);
            $mform->addHelpButton('snaming', 'snamingconvention', 'block_creqmanager');
            if (enrol_is_enabled('self')) {
                $mform->addElement('select', 'autokey', get_string('password', 'enrol_self'), $enrolkeyoptions);
                $mform->addHelpButton('autokey', 'configureenrolmentkey', 'block_creqmanager');
            }
            $mform->addElement('selectyesno', 'selfcat', get_string('allowselfcategorization', 'block_creqmanager'));
            $mform->addHelpButton('selfcat', 'allowselfcategorization', 'block_creqmanager');
            $mform->addElement('select', 'entryfield3opted',
            get_string('termname', 'block_creqmanager', $entryfield3 ), $entryfield3options);
            $mform->addHelpButton('entryfield3opted', 'termname', 'block_creqmanager');
            $mform->addElement('select', 'termyearpos', get_string('termyearpos', 'block_creqmanager', $entryfield3), $termyearpos);
            $mform->addHelpButton('termyearpos', 'termyearpos', 'block_creqmanager');
            $mform->addElement('select', 'termyearseparation',
            get_string('termyearseparation', 'block_creqmanager', $entryfield3), $termyearseparation);
            $mform->addHelpButton('termyearseparation', 'termyearseparation', 'block_creqmanager');
            $dateoptions = array( 'startyear' => date("Y") - 5, 'stopyear' => date("Y") + 5);
            $mform->addElement('date_selector', 'aystartdate',
            get_string('aystartdate', 'block_creqmanager'), $dateoptions);
            $mform->addHelpButton('aystartdate', 'aystartdate', 'block_creqmanager');
            $mform->addElement('date_selector', 'coursedate',
            get_string('coursedate', 'block_creqmanager'), $dateoptions);
            $mform->addHelpButton('coursedate', 'coursedate', 'block_creqmanager');
            $id = 1;
        }
        if ($id === 2) {
            $mform->addElement('header', 'header', get_string('configurecourseformfields', 'block_creqmanager'));
            $mform->addElement('text', 'entryfield1', get_string('entryfield1', 'block_creqmanager'));
            $mform->addHelpButton('entryfield1', 'entryfield1', 'block_creqmanager');
            $mform->addRule('entryfield1',
            get_string('request_rule1', 'block_creqmanager'), 'required', null, 'server', false, false);
            $mform->setType('entryfield1', PARAM_TEXT);
            $mform->addElement('text', 'entryfield2', get_string('entryfield2', 'block_creqmanager'));
            $mform->addHelpButton('entryfield2', 'entryfield2', 'block_creqmanager');
            $mform->addRule('entryfield2',
            get_string('request_rule1', 'block_creqmanager'), 'required', null, 'server', false, false);
            $mform->setType('entryfield2', PARAM_TEXT);
            $mform->addElement('text', 'entryfield3', get_string('entryfield3', 'block_creqmanager'));
            $mform->addHelpButton('entryfield3', 'entryfield3', 'block_creqmanager');
            $mform->addRule('entryfield3',
            get_string('request_rule1', 'block_creqmanager'), 'required', null, 'server', false, false);
            $mform->setType('entryfield3', PARAM_TEXT);
            $mform->addElement('text', 'optfield1', get_string('optfield1', 'block_creqmanager'));
            $mform->addHelpButton('optfield1', 'optfield1', 'block_creqmanager');
            $mform->addRule('optfield1',
            get_string('request_rule1', 'block_creqmanager'), 'required', null, 'server', false, false);
            $mform->setType('optfield1', PARAM_TEXT);
            $mform->addElement('select', 'optfield1status', get_string('status', 'block_creqmanager'),
            array('enabled' => get_string('Enabled', 'block_creqmanager'),
            'disabled' => get_string('Disabled', 'block_creqmanager')));
            $mform->addRule('optfield1status',
            get_string('request_rule2', 'block_creqmanager'), 'required', null, 'server', false, false);
            $mform->addElement('text', 'optfield2', get_string('optfield2', 'block_creqmanager'));
            $mform->addHelpButton('optfield2', 'optfield2', 'block_creqmanager');
            $mform->setType('optfield2', PARAM_TEXT);
            $mform->addRule('optfield2',
            get_string('request_rule1', 'block_creqmanager'), 'required', null, 'server', false, false);
            $mform->addElement('select', 'optfield2status', get_string('status', 'block_creqmanager'),
            array('enabled' => get_string('Enabled', 'block_creqmanager'),
            'disabled' => get_string('Disabled', 'block_creqmanager')));
            $mform->addRule('optfield2status',
            get_string('request_rule2', 'block_creqmanager'), 'required', null, 'server', false, false);
            $id = 2;
        }
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $id);
        $this->add_action_buttons();
    }
}
