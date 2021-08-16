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

namespace block_library_statistics;

use stdClass;

/**
 * Event observers
 *
 * @package   library_statistics
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class observer
{

    /**
     * Triggered when user read a library record.
     *
     * @param \mod_data\event\record_viewed $event
     */
    public static function get_record_viewed(\mod_data\event\record_viewed $event)
    {
        global $DB, $USER;

        $hasViewed = $DB->record_exists(
            'block_library_sta_log_access',
            [
                'courseid' => $event->courseid,
                'dataid'=> $event->other['dataid'],
                'recordid'=> $event->other['recordid'],
                'userid'=> $USER->id
            ]
        );

        if (!$hasViewed) {
            $log = new \stdClass();
            $log->courseid = $event->courseid;
            $log->userid= $USER->id;
            $log->dataid = $event->other['dataid'];
            $log->recordid = $event->other['recordid'];
            $log->timecreated = time();
            $DB->insert_record('block_library_sta_log_access', $log, true);
        }
    }
}
