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
 * Language.
 *
 * @package    assignsubmission_fileregex
 * @copyright  2017 University of Portland
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['acceptedmimetypes'] = 'Accepted mimetypes';
$string['acceptedmimetypes_help'] = 'Submission of files of a different mimetype than the ones listed here will automatically be considered unparseable, and will be rejected. One mimetype per line. Lines starting with _#_ are ignored. Entries must exist in the [file types list]({$a->filetypesurl}). Leave empty to accept all types.';
$string['addmorefields'] = 'Add more fields';
$string['contentregex'] = 'Content regex';
$string['contentregex_help'] = 'The regular expressions to test the file content against. All expressions must be true. The 3rd field is used to display a message when the validation fails. Expressions are case-sensitive and do not support multi-lines.';
$string['enabled'] = 'Enabled';
$string['enabled_help'] = 'When enabled, the file submission of a student will have to match regular expression rules. This requires file submissions to be enabled and limited to a single file.';
$string['errorexpectedzipwithsinglefile'] = 'The zip file should not contain more than one file.';
$string['feedback'] = 'Validation feedback';
$string['filecontentdoesnotpasscontentvalidation'] = 'The file content does not pass the content validation rules.';
$string['filemustendincorcpp'] = 'File must end in .c or .cpp';
$string['filenameisnotvalid'] = 'The file name is not valid.';
$string['filenameregex'] = 'File name regex';
$string['filenameregex_help'] = 'The regular expression that the file name must comply to. Leave empty if not required.';
$string['functionmainnotfound'] = 'Function main() not found';
$string['ignorevalidationerrors'] = 'Ignore file validation warnings and proceed with the submission';
$string['invalidtypesfound'] = 'Invalid types found: {$a}.';
$string['matchmode'] = 'Match mode';
$string['mustmatch'] = 'Must match';
$string['mustnotmatch'] = 'Must NOT match';
$string['pluginimproperlyconfigured'] = 'The plugin \'File regex validation\' is not configured properly and will not work, it should be ordered after the \'File submissions\' plugin. Please notify your administrator.';
$string['pluginname'] = 'File regex validation';
$string['regex'] = 'Regex';
$string['typeoffilenotsupported'] = 'This type of file is not supported.';
