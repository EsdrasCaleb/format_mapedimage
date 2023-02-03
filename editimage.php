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


require_once('../../../config.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/course/format/mapedimage/editimage_form.php');
require_once($CFG->dirroot . '/course/format/mapedimage/lib.php');

/* Page parameters */
$courseid = required_param('courseid', PARAM_INT);
$id = optional_param('id', null, PARAM_INT);
$image = optional_param('image', null, PARAM_INT);

if($image){

$formdata = new stdClass();
$formdata->userid = required_param('userid', PARAM_INT);
$formdata->offset = optional_param('offset', null, PARAM_INT);
$formdata->forcerefresh = optional_param('forcerefresh', null, PARAM_INT);
$formdata->mode = optional_param('mode', null, PARAM_ALPHA);

$sectionid = 0;

$url = new moodle_url('/course/format/mapedimage/editimage.php', array(
    'courseid' => $courseid,
    'id' => $id,
    'offset' => $formdata->offset,
    'forcerefresh' => $formdata->forcerefresh,
    'userid' => $formdata->userid,
    'mode' => $formdata->mode));

/* Not exactly sure what this stuff does, but it seems fairly straightforward */

require_login($course);
if (isguestuser()) {
    redirect($CFG->wwwroot);
}
$context = context_course::instance($courseid);
$fileoptions = array('subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => -1,'accepted_types' => array('gif', 'jpe', 'jpeg', 'jpg', 'png'),'context' => $context);
$PAGE->set_url($url);
$PAGE->set_context($context);

global $DB;

$args = array(
    'context' => $context,
    'attachmentoptions' => $fileoptions,
	'id'=>$id
);



$mform = new mapedimage_image_form(null,$data);
if ($mform->is_cancelled()) {
    // Someone has hit the 'cancel' button.
    redirect(new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $course->id));
} else if ($formdata = $mform->get_data()) { // Form has been submitted.
    $filearea = 'mapedimage';

    $data = file_postupdate_standard_filemanager($formdata, 'bgimage', $fileoptions, $context, "course", $filearea, 0);    
}
else{

    /* Draw the form */
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    $mform->display();
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    die();
}
}

    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    ?>
    <canvas id="tutorial" width="800" height="600">
        <img src="images/clock.png" width="150" height="150" alt=""/>
    </canvas>

    <?php
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();

