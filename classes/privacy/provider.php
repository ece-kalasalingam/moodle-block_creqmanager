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
 * Defines {@link \block_creqmanager\privacy\provider} class.
 *
 * @package    block_creqmanager
 * @copyright  2021 Jeya Prakash K
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @category   privacy
 * @copyright  2018 LTS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_creqmanager\privacy;
defined('MOODLE_INTERNAL') || die();
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
/**
 * Privacy API implementation for the COURSE REQUEST MANAGER plugin.
 *
 * @copyright  2018 Karen Holland <karen@lts.ie>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describe all the places where the COURSE REQUEST MANAGER plugin stores some
     * personal data.
     * @param collection $collection Collection of items to add metadata to.
     * @return collection Collection with our added items.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table('block_creqmanager_records', [
           'createdbyid' => 'privacy:metadata:db:block_creqmanager_records:createdbyid',
           'comments' => 'privacy:metadata:db:block_creqmanager_records:comments',
           'createdate' => 'privacy:metadata:db:block_creqmanager_records:createdate',
        ], 'privacy:metadata:db:block_creqmanager_records');
        return $collection;
    }
    /**
     * Get the list of contexts that contain personal data for the specified user.
     *
     * @param int $userid ID of the user.
     * @return contextlist List of contexts containing the user's personal data.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();
        $contextlist->add_system_context();
        return $contextlist;
    }
    /**
     * Export personal data stored in the given contexts.
     *
     * @param approved_contextlist $contextlist List of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        if (!count($contextlist)) {
            return;
        }
        $syscontextapproved = false;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->id == SYSCONTEXTID) {
                $syscontextapproved = true;
                break;
            }
        }
        if (!$syscontextapproved) {
            return;
        }
        $user = $contextlist->get_user();
        $writer = writer::with_context(\context_system::instance());
        $subcontext = [get_string('pluginname', 'block_creqmanager')];
        $query = $DB->get_records('block_creqmanager_records', ['createdbyid' => $user->id], '',
        'id, createdbyid as userid, comments, createdate');
        if ($query) {
            $writer->export_data($subcontext, (object) ['requests' => array_values(array_map(function($record) {
                unset($record->id);
                return $record;
            }, $query))]);
            unset($query);
        }
    }
    /**
     * Delete personal data for all users in the context.
     *
     * @param context $context Context to delete personal data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if (!$context instanceof \context_system) {
            return;
        }
        $DB->delete_records('block_creqmanager_records');
    }
    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }
        // Remove non-system contexts. If it ends up empty then early return.
        $contexts = array_filter($contextlist->get_contexts(), function($context) {
            return $context->contextlevel == CONTEXT_SYSTEM;
        });
        if (empty($contexts)) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        $DB->delete_records('block_creqmanager_records', ['createdbyid' => $userid]);
    }
}
