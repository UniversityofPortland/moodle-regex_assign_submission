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
 * Form settings module.
 *
 * @package    assignsubmission_fileregex
 * @copyright  2017 University of Portland
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/log'], function($, Log) {
    function toggleConstructor(form, field) {
        return function() {
            var visible = field.prop('checked') && !field.prop('disabled');
            var fields = form
                .find('[name^=assignsubmission_fileregex_]')
                .filter(':not([name=assignsubmission_fileregex_enabled])');
            var groups = fields.closest('.fcontainer > .fitem');

            if (visible) {
                groups.show();
            } else {
                groups.hide();
            }
        };
    }

    return /** @alias module:assignsubmission_fileregex/form_settings */ {
        init: function() {
            var fileRegexField = $('input[name=assignsubmission_fileregex_enabled]');
            if (!fileRegexField) {
                Log.warn('File regex field not found.');
                return;
            }

            var formNode = fileRegexField.closest('form')[0];
            if (!formNode) {
                Log.warn('Form not found.');
            }

            var form = $(formNode);
            var toggle = toggleConstructor(form, fileRegexField);

            // We observe changes in all file plugin values, they must be set as dependencies in
            // the form definition and disable the fileregex plugin when relevant.
            $('[name^=assignsubmission_file_]').on('change', function() {
                // Use timeout to allow the form to update the disabled property before we check it.
                setTimeout(toggle, 16);
            });
            fileRegexField.on('change', function() {
                toggle();
            });
            toggle();
        }
    };
});
