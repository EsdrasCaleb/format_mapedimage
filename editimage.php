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
$context = context_course::instance($courseid);
$url = new moodle_url('/course/format/mapedimage/editimage.php', array(
    'courseid' => $courseid,
    'image' => 1));

if($image){


/* Not exactly sure what this stuff does, but it seems fairly straightforward */

require_login($course);
if (isguestuser()) {
    redirect($CFG->wwwroot);
}
$fileoptions = array('subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => -1,'accepted_types' => array('gif', 'jpe', 'jpeg', 'jpg', 'png'),'context' => $context);
$PAGE->set_url($url);
$PAGE->set_context($context);

global $DB;

$data = array(
    'context' => $context,
    'attachmentoptions' => $fileoptions,
	'id'=>$id,
    'courseid'=>$courseid,
);



$mform = new mapedimage_image_form(null,$data);
$data = new stdClass();
$data->image = 1;
$data->bgimage = 1;
$data->id = 0;
$mform->set_data($data);
if ($mform->is_cancelled()) {
    // Someone has hit the 'cancel' button.
    redirect(new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $course->id));
} else if ($formdata = $mform->get_data()) { // Form has been submitted.
    $filearea = 'section';

    $data = file_postupdate_standard_filemanager($formdata, 'bgimage', $fileoptions, $context, "format_mapedimage", $filearea, 1);    
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
    $imageRecord = $DB->get_record_sql("SELECT * from {files} 
    where contextid={$context->id} and
    component= 'format_mapedimage' and filearea='section' and filename <>'.'");

    $mageUrl = moodle_url::make_pluginfile_url(
        $imageRecord->contextid,
        $imageRecord->component,
        $imageRecord->filearea,
        $imageRecord->itemid,
        $imageRecord->filepath,
        $imageRecord->filename,
        false

    );


    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    ?>

    <div class="text-center">
        <canvas id="tutorial" width="1024" height="768">
        </canvas>
    </div>
    <div class="text-center">
        <a class="btn btn-lg btn-info" href="<?php echo $url; ?>">Editar Imagem</a>
    </div>
    <script>
        var canvas = document.getElementById('tutorial');

        if (canvas.getContext){
            var ctx = canvas.getContext('2d');
            var img = new Image();   // Create new img element
            img.addEventListener('load', function() {
                var width = img.naturalWidth; // this will be 300
                var height = img.naturalHeight; // this will be 400
                if(width>height && width>1024){
                    heigth = (height*1024)/width;
                    width = 1024;
                }
                else if(width<height && height>768){
                    width = (width*768)/height;
                    height = 768;
                }
                ctx.drawImage(img, 0,0,width,height);
            }, false);
            img.src = '<?php echo $mageUrl; ?>'; // Set source path


        } else {
            console.log("error");
        }
    </script>
    <?php
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
