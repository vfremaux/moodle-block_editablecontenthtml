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
 * Form for editing HTML block instances.
 *
 * @package    block_editablecontenthtml
 * @category   blocks
 * @copyright  2012 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class block_editablecontenthtml_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_editablecontenthtml'));
        $mform->setType('config_title', PARAM_MULTILANG);

        $mform->addElement('checkbox', 'config_lockcontent', get_string('configlockcontent', 'block_editablecontenthtml'));
        $mform->setType('config_lockcontent', PARAM_INT);
        $mform->setDefault('config_lockcontent', 0);

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context);
        $mform->addElement('editor', 'config_text', get_string('configcontent', 'block_editablecontenthtml'), null, $editoroptions);
        $mform->setType('config_text', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.
    }

    public function set_data($defaults, &$files = null) {
        if (!empty($this->block->config) && is_object($this->block->config)) {
            if (is_array($this->block->config->text)) {
                $text = $this->block->config->text['text'];
            } else {
                $text = $this->block->config->text;
            }
            $draftideditor = file_get_submitted_draft_itemid('config_text');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_text['text'] = file_prepare_draft_area($draftideditor, $this->block->context->id,
                                                                     'block_editablecontenthtml', 'content', 0,
                                                                     array('subdirs' => true), $currenttext);
            $defaults->config_text['itemid'] = $draftideditor;
            $defaults->config_text['format'] = @$this->block->config->format;
        } else {
            $text = '';
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely.
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        /*
         * Have to delete text here, otherwise parent::set_data will empty content
         * of editor.
         */
        unset($this->block->config->text);
        parent::set_data($defaults, $files);
        // Restore text.
        if (!isset($this->block->config)) {
            $this->block->config = new StdClass();
        }
        $this->block->config->text = $text;
        if (isset($title)) {
            // Reset the preserved title.
            $this->block->config->title = $title;
        }
    }
}
