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
 * Proxy lock factory settings.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

if ($hassiteconfig) {
    $url = new moodle_url("/admin/tool/lockstats");
    $ADMIN->add('server', new admin_externalpage('tool_lockstats', get_string('pluginname', 'tool_lockstats'), $url));

    // Local plugin settings.
    $settings = new admin_settingpage('tool_lockstats_settings', get_string('pluginname', 'tool_lockstats'));

    $ADMIN->add('tools', $settings);

    if (!during_initial_install()) {

        $settings->add(new admin_setting_configduration('tool_lockstats/cleanup',
            new lang_string('cleanup',     'tool_lockstats'),
            new lang_string('cleanupdesc', 'tool_lockstats'),
            86400 * 30, 86400));

        $settings->add(new admin_setting_configtextarea('tool_lockstats/blacklist',
            new lang_string('blacklist',     'tool_lockstats'),
            new lang_string('blacklistdesc', 'tool_lockstats'),
            'core_cron'));

        $settings->add(new admin_setting_configduration('tool_lockstats/threshold',
           new lang_string('threshold',     'tool_lockstats'),
           new lang_string('thresholddesc', 'tool_lockstats'),
           60 * 5, 60));

        $settings->add(new admin_setting_configcheckbox('tool_lockstats/debug',
            new lang_string('debug',        'tool_lockstats'),
            new lang_string('debugdesc',    'tool_lockstats'),
            '0'));
    }
}
