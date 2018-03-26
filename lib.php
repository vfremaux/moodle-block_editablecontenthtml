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
 * @copyright  2012 Valery Fremaux (http://www.ethnoinformatique.fr)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * This function is not implemented in this plugin, but is needed to mark
 * the vf documentation custom volume availability.
 */
function block_editablecontenthtml_supports_feature($feature) {
    assert(1);
}

function block_editablecontenthtml_pluginfile($course, $birecordorcm, $context, $filearea, $args, $forcedownload) {

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    require_course_login($course);

    if ($filearea !== 'content') {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (!($file = $fs->get_file($context->id, 'block_editablecontenthtml', 'content', 0, $filepath, $filename)) ||
            $file->is_directory()) {
        send_file_not_found();
    }

    if ($parentcontext = context::instance_by_id($birecordorcm->parentcontextid)) {
        if ($parentcontext->contextlevel == CONTEXT_USER) {
            /*
             * force download on all personal pages including /my/
             * because we do not have reliable way to find out from where this is used
             */
            $forcedownload = true;
        }
    } else {
        // Weird, there should be parent context, better force dowload then.
        $forcedownload = true;
    }

    \core\session\manager::write_close();
    send_stored_file($file, 60 * 60, 0, $forcedownload);
}

