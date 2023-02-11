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
global $DB,$PAGE;
if (isguestuser()) {
    redirect($CFG->wwwroot);
}

/* Page parameters */
$courseid = required_param('courseid', PARAM_INT);
$id = optional_param('id', null, PARAM_INT);
$image = optional_param('image', null, PARAM_INT);
$context = context_course::instance($courseid);
$course = $DB->get_record("course",array("id"=>$courseid));
$PAGE->set_pagelayout("course");
$PAGE->set_context($context);
$PAGE->set_course($course);

$url = new moodle_url('/course/format/mapedimage/editimage.php', array(
    'courseid' => $courseid,
    'image' => 1));
$PAGE->set_url($url);

require_login($course);

require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/course/format/mapedimage/editimage_form.php');
require_once($CFG->dirroot . '/course/format/mapedimage/lib.php');


if($image){


/* Not exactly sure what this stuff does, but it seems fairly straightforward */



$fileoptions = array('subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => -1,'accepted_types' => array('gif', 'jpe', 'jpeg', 'jpg', 'png'),'context' => $context);




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
$filearea = 'section';

file_prepare_standard_filemanager($data, 'bgimage', $fileoptions, $context, "format_mapedimage", $filearea, $courseid);    
$mform->set_data($data);
if ($mform->is_cancelled()) {
    // Someone has hit the 'cancel' button.
    redirect(new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $course->id));
} else if ($formdata = $mform->get_data()) { // Form has been submitted.


    $data = file_postupdate_standard_filemanager($formdata, 'bgimage', $fileoptions, $context, "format_mapedimage", $filearea, $courseid);    
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
    where contextid={$context->id} and itemid={$courseid} and
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
        <a class="btn btn-lg btn-info" href="<?php echo $url; ?>">Editar Imagem</a>
    </div>
    <br/>

    <?php if($mageUrl): ?>
        <div class="text-center">
        <canvas id="canvas" width="1024" >
        </canvas>
        </div>
        <br/>
        <hr/>
    <form id="formAreas" method="post">
    <div class="text-center" id="containerAdd">
        <div class="containers row">
            <input type="hidden" name="x[]">
            <input type="hidden" name="y[]">
            <input type="hidden" name="weigth[]">
            <input type="hidden" name="heigth[]">
            <div class="col-2">
                <input type="radio" name="selected[]" class="rdSelect" />
                Selecionar
            </div>
            <div class="col-2">
            <select class="cmbForma" name="forma">
                <option value="rect">Retangulo</option>
                <option value="circle">Circulo</option>
            </select>
            </div>
            <div class="col-2">
                <select class="cmbTipo" name="tipo[]">
                    <option value="link">Link</option>
                    <option value="section">Secao</option>
                </select>
            </div>
            <div class="col-sm">
                <select class="section hidden" name="sectuin[]">
                    <option value="section">section</option>
                </select>
                <input class="url" name="url[]" type="text" value="" />
            </div>

        </div>
    </div>
    </form>
    <button id="btnAddMore">Adicionar Mais areas</button>
    <script>
        imageSource = '<?php echo $mageUrl; ?>'; 
        var img = new Image();   // Create new img element
        img.src = imageSource;

        document.querySelector("body").onload=  function(){
            var current_x = null
            var current_y = null
            var current_weigth = null
            var current_heigth = null
            var forma = null
            var currentSelect = null

            var newSelect = function(){
                currentSelect = $(this)
                forma = $(this).parent().next().children("select").val()  
                current_heigth = $(this).parent().prev()
                current_weigth = current_heigth.prev()
                current_y = current_weigth.prev()
                current_x = current_y.prev()
            } 

            var changeLink = function(){
                    var pai = $(this).parent().next()
                    console.log(pai)
                    console.log(pai.children(".section"))
                    console.log(pai.children(".url"))
                    if(this.value=="link"){
                        pai.children(".section").addClass("hidden")
                        pai.children(".url").removeClass("hidden")
                    }
                    else{
                        pai.children(".url").addClass("hidden")
                        pai.children(".section").removeClass("hidden")
                    }
            }
            $(".rdSelect").change(newSelect)
            $(".rdSelect").click();
            $(".cmbForma").change(()=>forma=this.value)
            $(".cmbTipo").change(changeLink)
            $("#btnAddMore").click(function(){
                $("#containerAdd").append('<div class="containers row">'+
            '<input type="hidden" name="x[]">'+
            '<input type="hidden" name="y[]">'+
            '<input type="hidden" name="weigth[]">'+
            '<input type="hidden" name="heigth[]">'+
            '<div class="col-2">'+
                '<input type="radio" name="selected[]" class="rdSelect" />'+
                'Selecionar'+
            '</div>'+
            '<div class="col-2">'+
            '<select class="cmbForma" name="forma">'+
                '<option value="rect">Retangulo</option>'+
                '<option value="circle">Circulo</option>'+
            '</select>'+
            '</div>'+
            '<div class="col-2">'+
                '<select class="cmbTipo" name="tipo[]">'+
                    '<option value="link">Link</option>'+
                    '<option value="section">Secao</option>'+
                '</select>'+
            '</div>'+
            '<div class="col-sm">'+
                '<select class="section hidden" name="sectuin[]">'+
                    '<option value="section">section</option>'+
                '</select>'+
                '<input class="url" name="url[]" type="text" value="" />'+
            '</div>'+
            '<div class="col-1">'+
            '    <button class="btnRemove">Remover</button>'+
            '</div>'+
        '</div>');
                $(".containers:last .btnRemove").click(function(){
                    $(this).parent().parent().remove()
                })
                $(".rdSelect").off("change")
                $(".rdSelect").change(newSelect)
                $(".cmbForma").off("change")
                $(".cmbForma").change(()=>forma=this.value)
                $(".cmbTipo").off("change")
                $(".cmbTipo").change(changeLink)
            })

            var imageWidth = img.naturalWidth; // this will be 1024 at max
            var imageHeight = img.naturalHeight; // this will be 1024 at max
            if(imageWidth>1024){
                imageHeight = (imageHeight*1024)/imageWidth;
                imageWidth = 1024;
            }
            $("#canvas").attr("height",imageHeight);
            var canvas = document.getElementById("canvas");
            var ctx = canvas.getContext("2d");
            var canvasOffset = $("#canvas").offset();
            var offsetX = canvasOffset.left;
            var offsetY = canvasOffset.top;
            var startX;
            var startY;
            var isDown = false;
            

            function drawImage(){
                ctx.drawImage(img, 0,0,imageWidth,imageHeight);
                $(".rdSelect").each(function(){
                    if($(this)==currentSelect)
                        return true;
                    var x =$(this).parent().prev().prev().prev().prev().val() 
                    var y = $(this).parent().prev().prev().prev().val()
                    ctx.moveTo(x,y)
                    if($(this).parent().next().children("select").val() =="rect"){  
                        ctx.rect(x,y,
                        $(this).parent().prev().prev().val(), 
                        $(this).parent().prev().val());

                    }
                    else{
                        ctx.arc(x,y,
                        $(this).parent().prev().val(),0, 2 * Math.PI);
                    }
                    ctx.stroke();
                    ctx.globalAlpha = 0.5;
                    ctx.fill();
                    ctx.globalAlpha = 1;
                })
            }

            function drawCircle(x,y){
                ctx.globalAlpha = 1;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                drawImage();
                ctx.beginPath();
                ctx.moveTo(x,y)
                var coordx = startX+((x-startX)/2);
                var coordy = startY + ((y - startY) / 2);
                var rad = ( Math.sqrt( ((startX-x)*(startX-x)) + ((startY-y)*(startY-y)) ) )/2
                ctx.arc(coordx, coordy , rad,0, 2 * Math.PI);
                ctx.stroke();
                ctx.globalAlpha = 0.5;
                ctx.fill();
                current_heigth.val(rad)
                current_weigth.val(rad)
                current_y.val(coordy)
                current_x.val(coordx)
            }

            function drawRect(x,y){
                ctx.globalAlpha = 1;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                drawImage();
                ctx.moveTo(x,y);
                ctx.beginPath();
                var sizex = Math.abs(startX-x);
                var sizey = Math.abs(startY-y);
                var originx = startX>x?x:startX;
                var originy = startY>y?y:startY;
                ctx.rect(originx, originy, sizex, sizey);
                ctx.stroke();
                ctx.globalAlpha = 0.5;
                ctx.fill();
                current_heigth.val(sizey)
                current_weigth.val(sizex)
                current_y.val(originy)
                current_x.val(originx)
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
                if(forma=="rect")
                    drawRect(mouseX, mouseY);
                else
                    drawCircle(mouseX, mouseY);
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
            })
            drawImage();
        }
    </script>
    <?php
    endif;
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();

