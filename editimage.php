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
require_login($course);
if (isguestuser()) {
    redirect($CFG->wwwroot);
}

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
    if($imageRecord){
        $mageUrl = moodle_url::make_pluginfile_url(
            $imageRecord->contextid,
            $imageRecord->component,
            $imageRecord->filearea,
            $imageRecord->itemid,
            $imageRecord->filepath,
            $imageRecord->filename,
            false

        );
    }


    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    ?>

    <div class="text-center">
        <canvas id="canvas" width="1024" height="768">
        </canvas>
    </div>
    <div class="text-center">
        <a class="btn btn-lg btn-info" href="<?php echo $url; ?>">Editar Imagem</a>
    </div>
    <?php if($mageUrl): ?>
    <form id="formAreas" method="post">
    <div class="text-center" id="containerAdd">
        <span class="containers">
            <input type="hidden" name="x[]">
            <input type="hidden" name="y[]">
            <input type="hidden" name="weigth[]">
            <input type="hidden" name="heigth[]">
            <select id="forma" name="forma">
            </select>

        </span>
    </div>
    </form>
    <button id="btnAddMore">Adicionar Mais areas</button>
    <script>
        imageSource = '<?php echo $mageUrl; ?>'; 
        var img = new Image();   // Create new img element
        img.src = imageSource;

        document.querySelector("body").onload=  function(){
            var canvas = document.getElementById("canvas");
            var ctx = canvas.getContext("2d");
            var canvasOffset = $("#canvas").offset();
            var offsetX = canvasOffset.left;
            var offsetY = canvasOffset.top;
            var startX;
            var startY;
            var isDown = false;

            function drawImage(){
                var width = img.naturalWidth; // this will be 1024 at max
                var height = img.naturalHeight; // this will be 1024 at max
                if(width>1024){
                    heigth = (height*1024)/width;
                    width = 1024;
                }
                ctx.drawImage(img, 0,0,width,height);
            }

            function drawOval(x, y) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.beginPath();
                ctx.moveTo(startX, startY + (y - startY) / 2);
                ctx.bezierCurveTo(startX, startY, x, startY, x, startY + (y - startY) / 2);
                ctx.bezierCurveTo(x, y, startX, y, startX, startY + (y - startY) / 2);
                ctx.closePath();
                ctx.stroke();
            }

            function drawCircle(x,y){
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                drawImage();
                ctx.beginPath();
                var coordx = startX+((x-startX)/2);
                var coordy = startY + ((y - startY) / 2);
                var rad = ( Math.sqrt( ((startX-x)*(startX-x)) + ((startY-y)*(startY-y)) ) )/2
                ctx.arc(coordx, coordy , rad,0, 2 * Math.PI);
                ctx.stroke();
            }

            function drawRect(x,y){
                ctx.globalAlpha = 1;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                drawImage();
                ctx.beginPath();
                var sizex = Math.abs(startX-x);
                var sizey = Math.abs(startY-y);
                var originx = startX>x?x:startX;
                var originy = startY>y?y:startY;
                ctx.rect(originx, originy, sizex, sizey);
                ctx.stroke();
                ctx.globalAlpha = 0.5;
                ctx.fill();
            }

            function handleMouseDown(e) {
                e.preventDefault();
                e.stopPropagation();
                startX = parseInt(e.clientX - offsetX);
                startY = parseInt(e.clientY - offsetY);
                isDown = true;
            }

            function handleMouseUp(e) {
                if (!isDown) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                isDown = false;
            }

            function handleMouseOut(e) {
                if (!isDown) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                isDown = false;
            }

            function handleMouseMove(e) {
                if (!isDown) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                mouseX = parseInt(e.clientX - offsetX);
                mouseY = parseInt(e.clientY - offsetY);
                drawRect(mouseX, mouseY);
            }

            $("#canvas").mousedown(function (e) {
                handleMouseDown(e);
            });
            $("#canvas").mousemove(function (e) {
                handleMouseMove(e);
            });
            $("#canvas").mouseup(function (e) {
                handleMouseUp(e);
            });
            $("#canvas").mouseout(function (e) {
                handleMouseOut(e);
            });
            $("#page").scroll(function(e){
                var BB=canvas.getBoundingClientRect();
                offsetX = BB.left;
                offsetY = BB.top;
                console.log(canvasOffset.top)
                console.log(offsetX)
                console.log(offsetY)
            })
            drawImage();
        }
    </script>
    <?php
    endif;
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();

