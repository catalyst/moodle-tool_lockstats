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

namespace tool_lockstats\check;
use core\check\check;
use core\check\result;

/**
 * Form for resetting the history.
 *
 * @package    tool_lockstats
 * @author     Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stale_lock extends check {
    /**
     * Link to lockstats summary page.
     *
     * @return \action_link|null
     */
    public function get_action_link(): ?\action_link {
        $url = new \moodle_url('/tool/lockstats/index.php');
        return new \action_link($url, get_string('pluginname', 'tool_lockstats'));
    }

    /**
     * Check for locks older than 24 hours, which are stale or stuck.
     *
     * @return result
     */
    public function get_result() : result {
        global $DB;
        $timeout = time() - DAYSECS;
        $select = 'gained < :timeout AND released IS NULL';
        $stale = $DB->record_exists_select('tool_lockstats_locks', $select, ['timeout' => $timeout]);

        $status = $stale ? result::ERROR : result::OK;
        $summary = $stale ? get_string('stalelocks', 'tool_lockstats') : get_string('nostalelocks', 'tool_lockstats');

        return new result($status, $summary);
    }
}
