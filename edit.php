<?php

	include_once '../../config.php';
	require_once 'content_edit_form.php';

    $courseid = required_param('course', PARAM_INT);
    $id = required_param('id', PARAM_INT);

    if (!$instance = $DB->get_record('block_instances', array('id' =>  $id))){
        print_error('errorbadblockinstance', 'block_editablecontenthtml');
    }
    
    if (!$course = $DB->get_record('course', array('id' => $courseid))){
        print_error('invalidcourseid');
    }

	require_login($course);

    $theBlock = block_instance('editablecontenthtml', $instance);
    $blockcontext = context_block::instance($id);

    require_capability('block/editablecontenthtml:editcontent', $blockcontext);
    
	$mform = new EditableContentHtmlEditForm($theBlock);
	
	if ($mform->is_cancelled()){
		if ($course->id != SITEID){
			redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
		} else {
			redirect($CFG->wwwroot.'/index.php');
		}
	}
	
	if ($data = $mform->get_data()){
	    if(empty($theBlock->config->lockcontent)){
			// change proposed by jcockrell 
			// $theBlock->config->text = $data->config_text;

			$draftid_editor = file_get_submitted_draft_itemid('config_text_editor');
			$data->config_text = file_save_draft_area_files($draftid_editor, $blockcontext->id, 'block_editablecontenthtml', 'content', 0, $mform->editoroptions, $data->config_text_editor['text']);
	    	$config = file_postupdate_standard_editor($data, 'config_text', $mform->editoroptions, $blockcontext, 'block_editablecontenthtml', 'content', 0);
			$theBlock->config->text = $config->config_text;
			unset($theBlock->config->config_text);
			unset($theBlock->config->config_texttrust);
			unset($theBlock->config->config_textformat);
			$theBlock->instance_config_save($theBlock->config);
		}

		if ($courseid != SITEID){
			redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
		} else {
			redirect($CFG->wwwroot.'/index.php');
		}
	}

	$PAGE->navbar->add(get_string('pluginname', 'block_editablecontenthtml'), null);	
	$PAGE->navbar->add(get_string('editcontent', 'block_editablecontenthtml'), null);	
	$PAGE->set_url($CFG->wwwroot.'/blocks/editablecontenthtml/edit.php?course='.$courseid.'&id='.$id);
	$PAGE->set_title($SITE->fullname);
	$PAGE->set_heading($SITE->shortname);
	echo $OUTPUT->header();
	
	$data->id = $id;
	$data->course = $courseid;
	// change proposed by jcockrell 
	$data->text = $theBlock->config->text;
    if(!empty($theBlock->config->lockcontent)){
		echo $OUTPUT->box(get_string('contentislocked', 'block_editablecontenthtml'));
		echo '<br/>';
		echo $OUTPUT->continue_button($CFG->wwwroot.'/course/view.php?id='.$courseid);  	
    } else {
		$mform->set_data($data);
		$mform->display();
	}
	echo $OUTPUT->footer($course);

?>