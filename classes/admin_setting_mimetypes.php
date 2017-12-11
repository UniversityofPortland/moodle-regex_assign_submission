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
 * Admin setting mimetypes.
 *
 * @package    assignsubmission_fileregex
 * @copyright  2017 University of Portland
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_fileregex;
defined('MOODLE_INTERNAL') || die();

use ArrayObject;
use admin_setting_configtextarea;
use core_filetypes;
use lang_string;
use moodle_url;

/**
 * Admin setting mimetypes.
 *
 * @package    assignsubmission_fileregex
 * @copyright  2017 University of Portland
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_mimetypes extends admin_setting_configtextarea {

    /**
     * Constructor.
     */
    public function __construct() {
        // We do not set a default value as it does not appear nicely in the UI, instead we
        // pre-populate the value during the initial installation.
        $filetypesurl = new moodle_url('/admin/tool/filetypes/index.php');
        parent::__construct('assignsubmission_fileregex/mimetypes',
            new lang_string('acceptedmimetypes', 'assignsubmission_fileregex'),
            new lang_string('acceptedmimetypes_help', 'assignsubmission_fileregex', [
                'filetypesurl' => $filetypesurl
            ]), '');
    }

    /**
     * Validate data before storage.
     *
     * @param string $data The data.
     * @return mixed True when ok, else error message.
     */
    public function validate($data) {
        $mimes = explode("\n", $data);
        $types = self::get_all_mimetypes();

        $invalids = [];
        foreach ($mimes as $mime) {
            $mime = trim($mime);
            if (empty($mime) || strpos($mime, '#') === 0) {
                continue;
            }
            if (!array_key_exists($mime, $types)) {
                $invalids[] = $mime;
            }
        }

        if (!empty($invalids)) {
            return get_string('invalidtypesfound', 'assignsubmission_fileregex', implode(', ', $invalids));
        }

        return true;
    }

    /**
     * Default mimetypes value.
     *
     * @return string
     */
    public static function get_default_config_value() {
        $types = core_filetypes::get_types();
        $mimes = array_unique(array_map(function($type) {
            return $type['type'];
        }, $types));
        $mimes = array_filter($mimes, function($mime) {
            return (strpos($mime, 'text/') === 0
                || $mime == 'application/x-javascript'
                || $mime == 'application/x-latex'
                || $mime == 'application/x-sh'
                || $mime == 'application/xml');
        });
        sort($mimes);
        return implode("\n", $mimes);
    }

    /**
     * Get mimetypes.
     *
     * @return array Where keys are types.
     */
    public static function get_all_mimetypes() {
        $types = core_filetypes::get_types();
        return array_flip(array_unique(array_map(function($type) {
            return $type['type'];
        }, $types)));
    }

}
