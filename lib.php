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

/**
 * Proxy lock factory, locks detail page.
 *
 * @package    tool_lockstats
 * @author     Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Hook point for status check returns.
 *
 * @return array
 */
function tool_lockstats_status_checks() {
    global $CFG;

    if (!empty($CFG->lock_factory) && $CFG->lock_factory === "\\tool_lockstats\\proxy_lock_factory") {
        return [
            new \tool_lockstats\check\stale_lock()
        ];
    } else {
        return [];
    }
}
