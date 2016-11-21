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

require('../../config.php');
require_once($CFG->dirroot.'/blocks/editablecontenthtml/content_edit_form.php');

$courseid = required_param('course', PARAM_INT);
$id = required_param('id', PARAM_INT);

$PAGE->set_url(new moodle_url('/blocks/editablecontenthtml/edit.php', array('course' => $courseid, 'id' => $id)));

if (!$instance = $DB->get_record('block_instances', array('id' =>  $id))) {
    print_error('errorbadblockinstance', 'block_editablecontenthtml');
}

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

// Security.
require_login($course);

$theblock = block_instance('editablecontenthtml', $instance);
$blockcontext = context_block::instance($id);

require_capability('block/editablecontenthtml:editcontent', $blockcontext);

$mform = new EditableContentHtmlEditForm($theblock);

if ($mform->is_cancelled()) {
    if ($course->id != SITEID) {
        redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
    } else {
        redirect($CFG->wwwroot.'/index.php');
    }
}

if ($data = $mform->get_data()) {
    if (empty($theblock->config->lockcontent)) {

        $draftid_editor = file_get_submitted_draft_itemid('config_text_editor');
        $data->config_text = file_save_draft_area_files($draftid_editor, $blockcontext->id, 'block_editablecontenthtml',
                                                        'content', 0, $mform->editoroptions, $data->config_text_editor['text']);
        $config = file_postupdate_standard_editor($data, 'config_text', $mform->editoroptions, $blockcontext,
                                                  'block_editablecontenthtml', 'content', 0);

        if (!isset($theblock->config)) {
            $theblock->config = new StdClass();
        }

        $theblock->config->text = $config->config_text;
        unset($theblock->config->config_text);
        unset($theblock->config->config_texttrust);
        unset($theblock->config->config_textformat);
        $theblock->instance_config_save($theblock->config);
    }

    if ($courseid != SITEID) {
        redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
    } else {
        redirect($CFG->wwwroot.'/index.php');
    }
}

$PAGE->navbar->add(get_string('pluginname', 'block_editablecontenthtml'), null);
$PAGE->navbar->add(get_string('editcontent', 'block_editablecontenthtml'), null);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->shortname);

echo $OUTPUT->header();

$data = new StdClass();
$data->id = $id;
$data->course = $courseid;

if (!isset($theblock->config)) {
    $theblock->config = new StdClass();
}

// Change proposed by jcockrell.
$data->text = @$theblock->config->text;

if (!empty($theblock->config->lockcontent)) {
    echo $OUTPUT->box(get_string('contentislocked', 'block_editablecontenthtml'));
    echo '<br/>';
    echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
} else {
    $mform->set_data($data);
    $mform->display();
}
echo $OUTPUT->footer($course);
