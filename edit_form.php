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
 * Newblock block caps.
 *
 * @package    block_gflacso_text_viewmore
 * @copyright  Cooperativa GENEOS <info@geneos.com.ar>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_gflacso_text_viewmore_edit_form extends block_edit_form {

    protected function specific_definition($mform) {

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // A sample string variable with a default value.
	    $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_gflacso_text_viewmore'));
	    $mform->setType('config_title', PARAM_TEXT);    

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->block->context);
       
        $mform->addElement('editor', 'config_text', get_string('configcontent', 'block_gflacso_text_viewmore'), null, $editoroptions);
        $mform->addRule('config_text', null, 'required', null, 'client');
        $mform->setType('config_text', PARAM_RAW); // XSS is prevented when printing the block contents and serving files
 

        $mform->addElement('editor', 'config_textmore', get_string('configcontentmore', 'block_gflacso_text_viewmore'), null, $editoroptions);
        $mform->addRule('config_textmore', null, 'required', null, 'client');
        $mform->setType('config_textmore', PARAM_RAW);

    }

    function set_data($defaults) {
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $text = $this->block->config->text;
            $draftid_editor = file_get_submitted_draft_itemid('config_text');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_text['text'] = file_prepare_draft_area($draftid_editor, $this->block->context->id, 'block_gflacso_text_viewmore', 'content', 0, array('subdirs'=>true), $currenttext);
            $defaults->config_text['itemid'] = $draftid_editor;
            $defaults->config_text['format'] = $this->block->config->format;

            $textmore = $this->block->config->textmore;
            $draftid_editormore = file_get_submitted_draft_itemid('config_textmore');
            if (empty($textmore)) {
                $currenttextmore = '';
            } else {
                $currenttextmore = $textmore;
            }
            $defaults->config_textmore['text'] = file_prepare_draft_area($draftid_editormore, $this->block->context->id, 'block_gflacso_text_viewmore', 'contentmore', 0, array('subdirs'=>true), $currenttextmore);
            $defaults->config_textmore['itemid'] = $draftid_editormore;
            $defaults->config_textmore['format'] = $this->block->config->formatmore;

        } else {
            $text = '';
            $textmore = '';
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
        unset($this->block->config->textmore);

        parent::set_data($defaults);
        // restore $text
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        $this->block->config->text = $text;
        $this->block->config->textmore = $textmore;
        if (isset($title)) {
            // Reset the preserved title
            $this->block->config->title = $title;
        }
    }

}
