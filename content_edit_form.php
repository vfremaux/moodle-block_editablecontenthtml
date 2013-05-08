<?php

require $CFG->libdir.'/formslib.php';

class EditableContentHtmlEditForm extends moodleform {
	
	var $block;
	var $editoroptions;
	
	function __construct(&$block){
		$this->block = $block;
		parent::__construct();
	} 
	
	function definition(){
        global $CFG, $COURSE;

		$maxbytes = $COURSE->maxbytes; // TODO: add some setting	
		$this->editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $maxbytes, 'context' => $this->block->context);

        $mform =& $this->_form;

		$mform->addElement('hidden', 'id');
		$mform->addElement('hidden', 'course');
		$mform->addElement('editor', 'config_text_editor', get_string('configcontent', 'block_editablecontenthtml'), null, $this->editoroptions);


		$this->add_action_buttons();    
	}	

    function set_data($defaults) {

        if (!empty($this->block->config) && is_object($this->block->config)) {
            $draftid_editor = file_get_submitted_draft_itemid('config_text_editor');
            $defaults->config_text = $this->block->config->text;
            $defaults->config_textformat = $this->block->config->format;
            $currenttext = file_prepare_draft_area($draftid_editor, $this->block->context->id, 'block_editablecontenthtml', 'config_text_editor', 0, array('subdirs'=>true), $defaults->config_text);
			$defaults = file_prepare_standard_editor($defaults, 'config_text', $this->editoroptions, $this->block->context, 'block_editablecontenthtml', 'content', 0);
			$defaults->config_text = array('text' => $currenttext, 'format' => $this->block->config->format, 'itemid' => $draftid_editor);
        } else {
            $defaults->config_text = '';
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        // have to delete text here, otherwise parent::set_data will empty content
        // of editor
        unset($this->block->config->text);
        parent::set_data($defaults);
        // restore $text
        $this->block->config->text = $defaults->config_text;
        if (isset($title)) {
            // Reset the preserved title
            $this->block->config->title = $title;
        }
    }
}