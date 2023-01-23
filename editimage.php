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
/* No idea, copied this from an example. Sets form data options but I don't know what they all do exactly */
$formdata = new stdClass();
$formdata->userid = required_param('userid', PARAM_INT);
$formdata->offset = optional_param('offset', null, PARAM_INT);
$formdata->forcerefresh = optional_param('forcerefresh', null, PARAM_INT);
$formdata->mode = optional_param('mode', null, PARAM_ALPHA);

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
    die();
}
$context = context_course::instance($courseid);
$PAGE->set_url($url);
$PAGE->set_context($context);

global $DB;
$sections = $DB->get_records("course_sections",array("course"=>$courseid));
$data = new \stdClass();
$data->returnurl = $originalreturnurl;
$mform = new mapedimage_image_form($url,  $context, $sections,$data);
$mform->set_data($data);
if ($mform->is_cancelled()) {
    // Someone has hit the 'cancel' button.
    redirect(new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $course->id));
} else if ($formdata = $mform->get_data()) { // Form has been submitted.
    var_dump($formdata );
    die("a");
    if ($formdata->deleteimage == 1) {
        // Delete the old images....
        $courseformat = course_get_format($course);
        $courseformat->delete_image($sectionid, $context->id);
    } else if ($newfilename = $mform->get_new_filename('imagefile')) {
        $fs = get_file_storage();

        // We have a new file so can delete the old....
        $courseformat = course_get_format($course);
        $sectionimage = $courseformat->get_image($course->id, $sectionid);
        if (isset($sectionimage->image)) {
            if ($file = $fs->get_file($context->id, 'course', 'section', $sectionid, '/', $sectionimage->image)) {
                $file->delete();
            }
        }

        // Resize the new image and save it...
        $storedfilerecord = $courseformat->create_original_image_record($contextid, $sectionid, $newfilename);

        $tempfile = $mform->save_stored_file(
                'imagefile',
                $storedfilerecord['contextid'],
                $storedfilerecord['component'],
                $storedfilerecord['filearea'],
                $storedfilerecord['itemid'],
                $storedfilerecord['filepath'],
                'temp.' . $storedfilerecord['filename'],
                true);

        $courseformat->create_section_image($tempfile, $storedfilerecord, $sectionimage);
    }
    redirect($CFG->wwwroot . "/course/view.php?id=" . $course->id);
}

/* Draw the form */
echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
