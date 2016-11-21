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
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class block_editablecontenthtml extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_editablecontenthtml');
    }

    public function applicable_formats() {
        return array('all' => true);
    }

    public function specialization() {
        $this->title = !empty($this->config->title) ? format_string($this->config->title) : format_string(get_string('newhtmlblock', 'block_editablecontenthtml'));
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function get_content() {
        global $CFG, $USER, $COURSE;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        if ($this->content_is_trusted()) {
            // Fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        if (isset($this->config->text)) {
            // Rewrite url.
            $text = file_rewrite_pluginfile_urls($this->config->text['text'], 'pluginfile.php', $this->context->id, 'block_editablecontenthtml', 'content', NULL);
            /*
             * Default to FORMAT_HTML which is what will have been used before the
             * editor was properly implemented for the block.
             */
            $format = $this->config->text['format'];
            // Check to see if the format has been properly set on the config.
            $this->content->text = format_text($text, $format, $filteropt);
        } else {
            $this->content->text = '';
        }

        unset($filteropt); // Memory footprint.

        $context = context_block::instance($this->instance->id);
        $streditcontent = get_string('editcontent', 'block_editablecontenthtml');

        if (has_capability('block/editablecontenthtml:editcontent', $context, $USER->id) && !@$this->config->lockcontent){
            $linkurl = new moodle_url('/blocks/editablecontenthtml/edit.php', array('id' => $this->instance->id, 'course' => $COURSE->id));
            $this->content->footer = '<a href="'.$linkurl.'">'.$streditcontent.'</a>';
        } else {
            $this->content->footer = '';
        }

        return $this->content;
    }

    /**
     * Serialize and store config data
     */
    public function instance_config_save($data, $nolongerused = false) {
        global $DB;

        // Why do i need do that ?
        if (!isset($_POST['config_lockcontent'])) {
            unset($data->lockcontent);
        }

        $config = clone($data);
        if (empty($config->lockcontent)) $config->lockcontent = false;
        // Move embedded files into a proper filearea and adjust HTML links to match change proposed by jcockrell.
        $config->format = FORMAT_HTML;

        parent::instance_config_save($config, $nolongerused);
    }

    public function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_editablecontenthtml');
        return true;
    }

    public function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid)) {
            return false;
        }
        // Find out if this block is on the profile page.
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // This is exception - page is completely private, nobody else may see content there that is why we allow JS here.
                return true;
            } else {
                // No JS on public personal pages, it would be a big security issue.
                return false;
            }
        }

        return true;
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return (!empty($this->config->title) && parent::instance_can_be_docked());
    }
}
