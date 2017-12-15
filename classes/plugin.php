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
 * Plugin main class file.
 *
 * @package    assignsubmission_fileregex
 * @copyright  2017 University of Portland
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_fileregex;
defined('MOODLE_INTERNAL') || die();

use MoodleQuickForm;
use stdClass;
use context_user;

/**
 * Plugin class.
 *
 * @package    assignsubmission_fileregex
 * @copyright  2017 University of Portland
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin extends \assign_submission_plugin {

    /** Must match. */
    const MATCH = 1;
    /** Must not match. */
    const NOT_MATCH = 0;
    /** Bypass field name. */
    const BYPASS_FIELD = 'assignsubmission_fileregex_bypass';

    /** @var bool Whether to fake that the validation wasn't empty. */
    protected $fakenotempty = false;
    /** @var MoodleQuickForm The form. */
    protected $mform;
    /** @var bool Visible cache. */
    protected $visiblecache;

    /**
     * Add bypass field.
     *
     * @param MoodleQuickForm $mform The form.
     */
    protected function add_bypass_field($mform) {
        $name = self::BYPASS_FIELD;
        if (!$mform->elementExists($name)) {
            $el = $mform->createElement('checkbox', $name, get_string('ignorevalidationerrors',
                'assignsubmission_fileregex'));

            if ($mform->elementExists('buttonar')) {
                $mform->insertElementBefore($el, 'buttonar');
            } else {
                $mform->addElement($el);
            }
        }
    }

    /**
     * Get any additional fields for the submission.
     *
     * @param mixed $submission Submission data.
     * @param MoodleQuickForm $mform The form.
     * @param stdClass $data The data.
     * @param int $userid The user ID.
     * @return bool When something was added.
     */
    public function get_form_elements_for_user($submissionorgrade, MoodleQuickForm $mform, stdClass $data, $userid) {
        $this->mform = $mform;
        if (optional_param(self::BYPASS_FIELD, false, PARAM_BOOL)) {
            $this->add_bypass_field($mform);
            return true;
        }
        return false;
    }

    /**
     * Get the plugin's name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignsubmission_fileregex');
    }

    /**
     * Set instance settings.
     *
     * @param MoodleQuickForm $mform The form to add the elements to.
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $PAGE, $OUTPUT;

        $component = 'assignsubmission_fileregex';
        $f = function($name) use ($component) {
            return $component . '_' . $name;
        };

        $defaults = $this->assignment->has_instance() ? $this->get_config() : new stdClass();
        $d = function($name, $else = null) use ($defaults) {
            return isset($defaults->{$name}) ? $defaults->{$name} : $else;
        };

        // Disable the plugin when...
        $mform->disabledIf($f('enabled'), 'assignsubmission_file_enabled', 'notchecked');
        $mform->disabledIf($f('enabled'), 'assignsubmission_file_maxfiles', 'neq', 1);

        // Warn about misconfiguration.
        if (!$this->is_propertly_configured()) {
            $mform->addElement('static', '', '', $OUTPUT->notification(get_string('pluginimproperlyconfigured',
                'assignsubmission_fileregex'), 'error'));
        }

        // Filename validation.
        $els = [
            $mform->createElement('select', $f('filename_matchmode'), get_string('matchmode', $component), [
                self::MATCH => get_string('mustmatch', $component),
                self::NOT_MATCH => get_string('mustnotmatch', $component),
            ]),
            $mform->createElement('text', $f('filename_regex'), get_string('regex', $component),
                ['placeholder' => '\.(c|cpp)$']),
            $mform->createElement('text', $f('filename_feedback'), get_string('feedback', $component),
                ['placeholder' => get_string('filemustendincorcpp', $component)]),
        ];
        $mform->setType($f('filename_regex'), PARAM_RAW);
        $mform->setType($f('filename_feedback'), PARAM_TEXT);
        $mform->addGroup($els, $f('filename'), get_string('filenameregex', $component), ' ', false);
        $mform->addHelpButton($f('filename'), 'filenameregex', $component);
        $mform->setDefault($f('filename_matchmode'), $d('filename_matchmode'));
        $mform->setDefault($f('filename_regex'), $d('filename_regex'));
        $mform->setDefault($f('filename_feedback'), $d('filename_feedback'));

        // File content validation.
        $count = max(1, optional_param($f('content_fields_count'), $d('content_fields_count', 1), PARAM_INT)) +
            (optional_param($f('content_fields_add'), false, PARAM_BOOL) ? 2 : 0);

        for ($i = 0; $i < $count; $i++) {
            $els = [
                $mform->createElement('select', 'matchmode', get_string('matchmode', $component), [
                    self::MATCH => get_string('mustmatch', $component),
                    self::NOT_MATCH => get_string('mustnotmatch', $component),
                ]),
                $mform->createElement('text', 'regex', get_string('regex', $component), ['placeholder' => 'def main\(\):']),
                $mform->createElement('text', 'feedback', get_string('feedback', $component),
                    ['placeholder' => get_string('functionmainnotfound', $component)]),
            ];
            $groupname = 'content[' . $i . ']';
            $mform->addGroup($els, $f($groupname), $i ? '' : get_string('contentregex', $component), ' ', true);
            $mform->setType($f($groupname . '[regex]'), PARAM_RAW);
            $mform->setType($f($groupname . '[feedback]'), PARAM_TEXT);
            $mform->setDefault($f($groupname . '[matchmode]'), $d('content_' . $i . '_matchmode'));
            $mform->setDefault($f($groupname . '[regex]'), $d('content_' . $i . '_regex'));
            $mform->setDefault($f($groupname . '[feedback]'), $d('content_' . $i . '_feedback'));
            $i ? null : $mform->addHelpButton($f($groupname), 'contentregex', $component);
        }

        // Self-manage the repeatable field. Why? Because we don't have access to the moodleform instance.
        $mform->addElement('submit', $f('content_fields_add'), get_string('addmorefields', $component));
        $mform->registerNoSubmitButton($f('content_fields_add'));
        $mform->addElement('hidden', $f('content_fields_count'), $count);
        $mform->setConstant($f('content_fields_count'), $count);
        $mform->setType($f('content_fields_count'), PARAM_INT);

        // Enhance the UI.
        $PAGE->requires->js_call_amd('assignsubmission_fileregex/form_settings', 'init');
    }

    /**
     * Get the submitted files.
     *
     * Return the list of files submitted to the assignsubmission_file plugin.
     *
     * @param stdClass $submission The submission data.
     * @param stdClass $data the data submitted from the form.
     * @return array
     */
    public function get_submitted_files(stdClass $submission, stdClass $data) {
        $fs = get_file_storage();
        return $fs->get_area_files($this->assignment->get_context()->id, 'assignsubmission_file', ASSIGNSUBMISSION_FILE_FILEAREA,
            $submission->id, 'id', false);
    }

    /**
     * Whether the submission is empty.
     *
     * When we fail the validation and remove the submitted file from the file plugin, the assignment module
     * thinks that the submission was empty and displays a validation error. In this case, we will pretend
     * that the submission was not empty in order to only display our validation error.
     *
     * @param stdClass $submission
     */
    public function is_empty(stdClass $submission) {
        if ($this->fakenotempty) {
            return false;
        }
        return true;
    }

    /**
     * Is the plugin properly configured?
     *
     * @return bool
     */
    protected function is_propertly_configured() {
        $fileso = (int) get_config('assignsubmission_file', 'sortorder');
        $fileregexso = (int) get_config('assignsubmission_fileregex', 'sortorder');
        return $fileregexso > $fileso;
    }

    /**
     * Remove a config.
     *
     * @param string $name The config name.
     * @return void
     */
    public function remove_config($name) {
        global $DB;
        $params = [
            'assignment' => $this->assignment->get_instance()->id,
            'subtype' => $this->get_subtype(),
            'plugin' => $this->get_type(),
            'name' => $name
        ];
        $DB->delete_records('assign_plugin_config', $params);
    }

    /**
     * Save a submission.
     *
     * @param stdClass $submission The submission data.
     * @param stdClass $data the data submitted from the form.
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        if (!$this->is_propertly_configured()) {
            // Do not do anything when not propery configured.
            return true;

        } else if (!empty($data->{self::BYPASS_FIELD})) {
            // The user decided to bypass validation.
            return true;
        }

        $files = $this->get_submitted_files($submission, $data);
        $file = reset($files);

        // This should not happen, so ignore everything.
        if (!$file || count($files) > 1) {
            return true;
        }

        // This plugin MUST be ordered after the file submission plugin, so that the submission file can be removed
        // even though the file plugin thought that the file was valid. If we do not remove it, the submission looks
        // like its been completed even though it wasn't. We also need to prevent the submission from looking empty
        // or an error message will be displayed to the user, and that's not what we want. Why a closure?
        // Because it's easier to keep a reference to the original file.
        $validationfailed = function($error) use ($file) {
            $file->delete();
            $this->fakenotempty = true;
            $this->set_regex_validation_error($error);
        };

        // Special case for zip files.
        $mimetype = $file->get_mimetype();
        if ($mimetype === 'application/zip') {
            $packer = get_file_packer('application/zip');
            $zipfiles = $file->list_files($packer);
            if (count($zipfiles) != 1) {
                $validationfailed(get_string('errorexpectedzipwithsinglefile', 'assignsubmission_fileregex'));
                return false;
            }
            $zipfile = reset($zipfiles);
            if ($zipfile->is_directory) {
                $validationfailed(get_string('errorexpectedzipwithsinglefile', 'assignsubmission_fileregex'));
                return false;
            }

            $tempdirectory = get_request_storage_directory();
            $packer->extract_to_pathname($file, $tempdirectory, [$zipfile->pathname]);

            $filename = $zipfile->pathname;
            $content = file_get_contents($tempdirectory . '/' . $filename);

        } else {
            $filename = $file->get_filename();
            $content = $file->get_content();
            $mimetype = mimeinfo('type', $filename);
        }

        $settings = $this->get_config();

        // Validate file name.
        if (!empty($settings->filename_regex)) {
            if (!$this->validate_regex($settings->filename_regex, $filename, $settings->filename_matchmode)) {
                $error = get_string('filenameisnotvalid', 'assignsubmission_fileregex');
                if (!empty($settings->filename_feedback)) {
                    $error = format_string($settings->filename_feedback);
                }
                $validationfailed($error);
                return false;
            }
        }

        // Check whether the file is of an allowed mimetype, but only when the content will be checked.
        if ($settings->content_fields_count > 0 && !$this->validate_mimetype($mimetype)) {
            $validationfailed(get_string('typeoffilenotsupported', 'assignsubmission_fileregex'));
            return false;
        }

        // Validate file content.
        $defaultfeedback = get_string('filecontentdoesnotpasscontentvalidation', 'assignsubmission_fileregex');
        for ($i = 0; $i < $settings->content_fields_count; $i++) {
            $regex = $settings->{'content_' . $i . '_regex'};
            $matchmode = $settings->{'content_' . $i . '_matchmode'};
            $feedbackkey = 'content_' . $i . '_feedback';

            if (!$this->validate_regex($regex, $content, $matchmode)) {
                $feedback = !empty($settings->{$feedbackkey}) ? $settings->{$feedbackkey} : $defaultfeedback;
                $validationfailed($feedback);
                return false;
            }
        }

        return true;
    }

    /**
     * Save the settings.
     *
     * @param stdClass $rawdata
     * @return bool
     */
    public function save_settings(stdClass $rawdata) {
        $fields = array_keys((array) $rawdata);
        $data = array_reduce($fields, function($carry, $key) use ($rawdata) {
            if (strpos($key, 'assignsubmission_fileregex_') !== 0) {
                return $carry;
            }
            $carry[substr($key, 27)] = $rawdata->{$key};
            return $carry;
        }, []);

        // Save file name stuff.
        $this->set_config('filename_matchmode', $data['filename_matchmode']);
        $this->set_config('filename_regex', $data['filename_regex']);
        $this->set_config('filename_feedback', $data['filename_feedback']);

        // Remove previous content config.
        $config = $this->get_config();
        $existing = array_filter(array_keys((array) $config), function($key) { return preg_match('/^content_[0-9]+/', $key); });
        array_walk($existing, function($name) {
            $this->remove_config($name);
        });

        // Save content config.
        $count = array_reduce($data['content'], function($carry, $item) {
            if (empty($item['regex'])) {
                return $carry;
            }
            $this->set_config('content_' . $carry . '_matchmode', $item['matchmode']);
            $this->set_config('content_' . $carry . '_regex', $item['regex']);
            if (!empty($item['feedback'])) {
                $this->set_config('content_' . $carry . '_feedback', $item['feedback']);
            }
            return $carry + 1;
        }, 0);
        $this->set_config('content_fields_count', $count);

        return true;
    }

    /**
     * Set regex validation error.
     *
     * @param string $error The error.
     */
    protected function set_regex_validation_error($error) {
        $this->add_bypass_field($this->mform);
        $this->set_error($error);
    }

    /**
     * Validate with a regex.
     *
     * @param string $regex The regex.
     * @param string $subject The subject.
     * @param int $matchmode The match mode.
     * @return bool
     */
    protected function validate_regex($regex, $subject, $matchmode) {
        $result = preg_match('/' . str_replace('/', '\/', $regex) . '/', $subject);

        // The regex is invalid, we pretend everything is OK.
        if ($result === false) {
            return true;
        }

        return !(($matchmode == self::MATCH && $result === 0) || ($matchmode == self::NOT_MATCH && $result > 0));
    }

    /**
     * Validate the mimetype.
     *
     * @param string $mimetype The mimetype.
     * @return bool
     */
    protected function validate_mimetype($mimetype) {
        global $CFG;
        $rawtypes = get_config('assignsubmission_fileregex', 'mimetypes');
        $allowedtypes = array_filter(array_map('trim', explode("\n", $rawtypes)), function($type) {
            return !empty($type) && $type[0] != '#';
        });
        return empty($allowedtypes) || in_array($mimetype, $allowedtypes);
    }
}
