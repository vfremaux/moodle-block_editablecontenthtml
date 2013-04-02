<?php

	include '../../config.php';
	require 'edit_form.php';

    $courseid = required_param('course', PARAM_INT);
    $id = required_param('id', PARAM_INT);
    if (!$instance = get_record('block_instance', 'id', $id)){
        print_error('badblockinstance', 'block_contact_form');
    }

    $theBlock = block_instance('contact_form', $instance);
    $blockcontext = get_context_instance(CONTEXT_BLOCK, $id);
    
    require_capability('block/editablecontenthtml:editcontent', $blockcontext);

	$mform = new EditableContentHtmlEditForm();
	
	if ($mform->is_cancelled()){
		if ($courseid != SITEID){
			redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
		} else {
			redirect($CFG->wwwroot.'/index.php');
		}
	}
	
	if ($data = $mform->get_data()){
		$theBlock->config->text = stripslashes($data->text);
		$theBlock->instance_config_commit();

		if ($courseid != SITEID){
			redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
		} else {
			redirect($CFG->wwwroot.'/index.php');
		}
	}
	
	print_header($SITE->fullname, $SITE->fullname, '');
	
	$data->text = $theBlock->config->text;
	$data->id = $id;
	$data->course = $courseid;
	$mform->set_data($data);
	$mform->display();
	print_footer();

?>