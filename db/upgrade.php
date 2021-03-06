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
 * Plugin upgrade.
 *
 * @package    assignsubmission_fileregex
 * @copyright  2017 University of Portland
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function.
 *
 * @param int $oldversion Old version.
 * @return true
 */
function xmldb_assignsubmission_fileregex_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2017120601) {

        // Similar to result from assignsubmission_fileregex\admin_setting_mimetypes::get_default_config_value().
        $defaultypes = [
            'application/x-javascript',
            'application/x-latex',
            'application/x-sh',
            'application/xhtml+xml',
            'application/xml',
            'text/css',
            'text/csv',
            'text/html',
            'text/plain',
            'text/tab-separated-values',
            'text/x-component',
            'text/x-scss',
            'text/xml',
        ];
        set_config('mimetypes', implode("\n", $defaultypes), 'assignsubmission_fileregex');

        // Plugin savepoint reached.
        upgrade_plugin_savepoint(true, 2017120601, 'assignsubmission', 'fileregex');
    }

    return true;

}
