<?php

require $CFG->libdir.'/formslib.php';

class EditableContentHtmlEditForm extends moodleform {
	
	function definition(){
        global $CFG;

        $mform =& $this->_form;

		$mform->addElement('hidden', 'id');
		$mform->addElement('hidden', 'course');
		$mform->addElement('htmleditor', 'text', get_string('configcontent', 'block_editablecontenthtml'));

		$this->add_action_buttons();    
	}	
}