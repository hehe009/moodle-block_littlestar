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
 *
 * @package   blocks_littlestar
 * @copyright Max Kan
 * @author    Max Kan <max_kan@hotmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_littlestar extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_littlestar');
    }

    function get_content() {
        global $CFG;

        $coursecontext = context::instance_by_id($this->instance->parentcontextid);

        if ($this->content !== NULL) {
            return $this->content;
        }

        //init content
        $this->content =  new stdClass;
        $this->content->text = '<link href="'.$CFG->wwwroot.'/blocks/littlestar/ratingfiles/ratings.css" rel="stylesheet" type="text/css" />'
                                .'<script src="'.$CFG->wwwroot.'/blocks/littlestar/ratingfiles/ratings.js" type="text/javascript"></script>';

        // context is module level
        if ($this->page->context->contextlevel == 70) {
            $this->content->text .= '<br>'.get_string('ratethis', 'block_littlestar').$this->page->cm->modname;
            $this->content->text .= '<div class="srtgs" id="rt_'.$this->page->context->contextlevel.'_'.$this->page->cm->id.'"></div>';
        } else if ($this->page->context->contextlevel == 50) { // context is course level
            $this->content->text .= get_string('ratecourse', 'block_littlestar');
            $this->content->text .= '<div class="srtgs" id="rt_'.$this->page->context->contextlevel.'_'.$this->page->course->id.'"></div>';
        }

        if (has_capability('block/littlestar:exportrates', $coursecontext)) {
            $url = new moodle_url('/blocks/littlestar/export.php', array('course'=>$this->page->course->id, 'sesskey'=>sesskey()));
            $this->content->footer = '<a href="'.$url.'">'.get_string('export', 'block_littlestar').'</a>';
        }

        return $this->content;
    }

    public function instance_delete() {
        global $DB;

        $DB->delete_records('block_littlestar_item', array('course'=>$this->page->course->id));

        $sqlselect = 'SELECT item FROM {block_littlestar_item}';
        $DB->delete_records_select('block_littlestar_user', 'item NOT IN ('.$sqlselect.')');
    }
}
