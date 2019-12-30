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
 * CLI script to list current locks
 *
 * @package    tool_lockstats
 * @author     Brendan Heywood <brendan@catalyst-au.net>
 * @copyright  2019 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../../config.php');
require_once($CFG->libdir.'/clilib.php');
require_once(__DIR__.'/../classes/proxy_lock_factory.php');

list($options, $unrecognized) = cli_get_params([
    'help' => false,
], [
    'h' => 'help',
]);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if ($options['help']) {
    // The indentation of this string is "wrong" but this is to avoid a extra whitespace in console output.
    $help = <<<EOF
List current Moodle locks

Options:
-h, --help            Print out this help

Example:
\$ sudo -u www-data php admin/tool/lockstats/cli/list_locks.php

EOF;

    echo $help;
    exit(0);
}

$current = new tool_lockstats\table\locks();
$records = $current->get_current_locks();

$format = "%7s %-12s %-8s %-8s %-20s %-40s\n";
printf ($format,
    'PID',
    'HOST',
    'TYPE',
    'TIME',
    'KEY',
    'NAME'
);

foreach ($records as $record) {

    $adhocid = $current::get_adhoc_id_by_task($record->resourcekey);
    if ($adhocid != null) {
        $adhocrecord = $current->get_adhoc_record($adhocid);
        $name = $adhocrecord->classname;
    } else {
        $name = '';
    }

    $maxadhoclimit = get_config('core', 'task_adhoc_concurrency_limit');
    $maxcronlimit = get_config('core', 'task_scheduled_concurrency_limit');

    switch ($record->type) {
        case LOCKSTAT_ADHOC:
            $type = 'adhoc';
            break;
        case LOCKSTAT_MAXADHOC:
            $type = 'maxadhoc';
            $name = 'One of ' . $maxadhoclimit .  ' $CFG->task_adhoc_concurrency_limit';
            break;
        case LOCKSTAT_SCHEDULED:
            $type = 'cron';
            break;
        case LOCKSTAT_MAXSCHEDULED:
            $type = 'maxcron';
            $name = 'One of ' . $maxcronlimit . ' $CFG->task_scheduled_concurrency_limit';
            break;
        default:
            $type = 'unknown';
            break;
    }

    printf ($format,
        $record->pid,
        substr($record->host, 0, 12),
        $type,
        gmdate("H:i:s", (time() - $record->gained)),
        substr($record->resourcekey, 0, 20),
        substr($name, 0, 40)
    );

}

echo "\nFound " . count($records) . " lock(s)\n";

