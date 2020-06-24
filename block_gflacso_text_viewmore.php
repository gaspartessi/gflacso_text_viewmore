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
 * @package    block_text_viewmore
 * @copyright  Daniel Neis <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_gflacso_text_viewmore extends block_base {

    function init() {
        $this->title = get_string('defaulttitle', 'block_gflacso_text_viewmore');
    }


    public function get_content() {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        if ($this->content_is_trusted()) {
            // fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }

        $this->content = new stdClass;
        $id = $this->context->id;
        $this->content->footer = $script = '
            <script> 
               function textViewMore'.$id.'(){
                    var full = document.getElementById("content_more'.$id.'");
                    var resumen = document.getElementById("content_'.$id.'");
                    var seemoreless = document.getElementById("seemorelesscontent_'.$id.'");
                    if (full.style.display == \'block\'){
                        full.style.display = \'none\';
                        resumen.style.display = \'block\';
                        seemoreless.innerHTML = \''.get_string('seemorelabel', 'block_gflacso_text_viewmore').'\';
            seemoreless.className = \'seemoreless more\';
                    }
                    else {
                        full.style.display = \'block\';
                        resumen.style.display = \'none\';
                        seemoreless.innerHTML = \''.get_string('seelesslabel', 'block_gflacso_text_viewmore').'\';
            seemoreless.className = \'seemoreless less\';
                    }
               }
            </script>';
        if (isset($this->config->text)) {
            // rewrite url
            $this->config->text = file_rewrite_pluginfile_urls($this->config->text, 'pluginfile.php', $this->context->id, 'block_gflacso_text_viewmore', 'content', NULL);
            // Default to FORMAT_HTML which is what will have been used before the
            // editor was properly implemented for the block.
            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            $this->content->text = '<div id="content_'.$this->context->id.'"" >'.format_text($this->config->text, $format, $filteropt).'</div>';
        } else {
            $this->content->text = '';
        }

        if (isset($this->config->textmore)) {
            // rewrite url
            $this->config->textmore = file_rewrite_pluginfile_urls($this->config->textmore, 'pluginfile.php', $this->context->id, 'block_gflacso_text_viewmore', 'contentmore', NULL);
            // Default to FORMAT_HTML which is what will have been used before the
            // editor was properly implemented for the block.
            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config
            if (isset($this->config->formatmore)) {
                $format = $this->config->formatmore;
            }
            $this->content->text .= '<div id="content_more'.$this->context->id.'" style="display:none">'.format_text($this->config->textmore, $format, $filteropt).'</div>';
        } else {
            $this->content->text .= '';
        }

        if (isset($this->config->formatmore) && !empty($this->config->formatmore))
            $this->content->text .= '<a class="seemoreless more" id="seemorelesscontent_'.$id.'" onclick="textViewMore'.$id.'()">'.get_string('seemorelabel', 'block_gflacso_text_viewmore').'</a>';

        unset($filteropt); // memory footprint

        return $this->content;
    }


    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('defaulttitle', 'block_gflacso_text_viewmore');            
            } else {
                $this->title = $this->config->title;
            }
        }
    }

    public function instance_allow_multiple() {
      return true;
    }

     /**
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match
        $config->text = file_save_draft_area_files($data->text['itemid'], $this->context->id, 'block_gflacso_text_viewmore', 'content', 0, array('subdirs'=>true), $data->text['text']);
        $config->format = $data->text['format'];

        $config->textmore = file_save_draft_area_files($data->textmore['itemid'], $this->context->id, 'block_gflacso_text_viewmore', 'contentmore', 0, array('subdirs'=>true), $data->textmore['text']);
        $config->formatmore = $data->textmore['format'];


        parent::instance_config_save($config, $nolongerused);
    }

    function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_gflacso_text_viewmore');
        return true;
    }

    function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        //find out if this block is on the profile page
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // this is exception - page is completely private, nobody else may see content there
                // that is why we allow JS here
                return true;
            } else {
                // no JS on public personal pages, it would be a big security issue
                return false;
            }
        }

        return true;
    }
       
}
