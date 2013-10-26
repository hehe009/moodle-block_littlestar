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

require_once('../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');

require_login();
$course = required_param('course', PARAM_INT); //if no courseid is given
$course = $DB->get_record('course', array('id' => $course), 'id, shortname', MUST_EXIST);

$context = context_course::instance($course->id);

require_capability('block/littlestar:exportrates', $context);

//check sesskey
if(!confirm_sesskey()){
    print_error('sesskey');
}

$fields = array('type'      => 'type',
                'item'      => 'item',
                'rating'    => 'rating',
                'votes'      => 'votes');

$filename = get_string('export', 'block_littlestar').$course->id;
$filename = clean_filename($filename);

$csvexport = new csv_export_writer();
$csvexport->set_filename($filename);
$csvexport->add_data($fields);

// get name of course module for mapping the rate
$sql = 'SELECT cm.id, cm.course, m.name
    FROM {course_modules} cm, {modules} m
    WHERE cm.module = m.id
    AND cm.course = :courseid';
$params['courseid'] = $course->id;
$cm = $DB->get_records_sql($sql, $params);

if ($rates = $DB->get_records('block_littlestar_item', array('course'=>$course->id), 'id,item,totalrate')) {
    $row = array();
    foreach ($rates as $rate) {
        $rating = $DB->get_records('block_littlestar_user', array('item'=>$rate->item), 'id, user');
        if (strpos($rate->item,'_50_')) {
            $row['type'] = 'course';
            $row['item'] = $course->shortname;
            $row['rating'] = $rate->totalrate / count($rating);
            $row['votes'] = count($rating);
        } else {
            $cmid = substr($rate->item, 6);
            $row['type'] = 'module';
            $row['item'] = $cm[$cmid]->name.'_'.$cmid;
            $row['rating'] = $rate->totalrate / count($rating);
            $row['votes'] = count($rating);
        }
        $csvexport->add_data($row);
    }
}

add_to_log($course->id, 'blocks', 'block_littlestar export rating', 'block_littlestar.php?course='.$course->id, '', '', $USER->id);
$csvexport->download_file();

