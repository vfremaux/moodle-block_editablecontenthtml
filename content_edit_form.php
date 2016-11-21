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
 * @package     block_editablecontenthtml
 * @category    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require $CFG->libdir.'/formslib.php';

class EditableContentHtmlEditForm extends moodleform {

    protected $block;
    protected $editoroptions;

    public function __construct(&$block) {
        $this->block = $block;
        parent::__construct();
    }

    public function definition() {
        global $CFG, $COURSE;

        $maxbytes = $COURSE->maxbytes; // TODO: add some setting.
        $this->editoroptions = array('trusttext' => true,
                                     'subdirs' => false,
                                     'maxfiles' => EDITOR_UNLIMITED_FILES,
                                     'maxbytes' => $maxbytes,
                                     'context' => $this->block->context);
        $mform =& $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);

        $label = get_string('configcontent', 'block_editablecontenthtml');
        $mform->addElement('editor', 'config_text_editor', $label, null, $this->editoroptions);
        $mform->setType('course', PARAM_CLEANHTML);

        $this->add_action_buttons();
    }

    public function set_data($defaults) {

        if (!empty($this->block->config) && is_object($this->block->config)) {
            $draftid_editor = file_get_submitted_draft_itemid('config_text_editor');
            $defaults->config_text = @$this->block->config->text;
            $defaults->config_textformat = @$this->block->config->format;
            $currenttext = file_prepare_draft_area($draftid_editor, $this->block->context->id, 'block_editablecontenthtml',
                                                   'config_text_editor', 0, array('subdirs' => true), $defaults->config_text);
            $defaults = file_prepare_standard_editor($defaults, 'config_text', $this->editoroptions, $this->block->context,
                                                     'block_editablecontenthtml', 'content', 0);
            $defaults->config_text = array('text' => $currenttext, 'format' => $defaults->config_textformat, 'itemid' => $draftid_editor);
        } else {
            $defaults->config_text = '';
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely.
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        /*
         * have to delete text here, otherwise parent::set_data will empty content
         * of editor
         */
        unset($this->block->config->text);
        parent::set_data($defaults);
        // Restore $text
        $this->block->config->text = $defaults->config_text;
        if (isset($title)) {
            // Reset the preserved title.
            $this->block->config->title = $title;
        }
    }
}