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
    "id"=>$couseid,
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
        $imageUrl = moodle_url::make_pluginfile_url(
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

    <?php if($imageUrl):
        $coursesec = $DB->get_records("course_sections",array("course"=>$courseid));
        $sections = "";
        foreach($coursesec as $sec){
            if($sec->section)
                $sections .= "<option value='{$sec->section}'>".($sec->name?$sec->name:"Seção ".$sec->section)."</option>";
        }
        ?>
        <div class="text-center">
        <canvas id="imgmapcanvas" width="1024" >
        </canvas>
        </div>
        <br/>
        <hr/>
    <div class="text-center" id="containerAdd">
        <?php
        $regions = $DB->get_records("format_mapedimage_regions",array("courseid"=>$courseid));
        if($regions){
            foreach($regions as $region){
                ?>
        <form>
        <div class="containers row">
            <input type="hidden" value="<?php echo $region->xleft;?>" name="x">
            <input type="hidden" value="<?php echo $region->ytop;?>" name="y">
            <input type="hidden" value="<?php echo $region->weigth;?>" name="weigth">
            <input type="hidden" value="<?php echo $region->heigth;?>" name="heigth">
            <div class="col-2">
                <input type="radio" name="selected" class="rdSelect" />
                Selecionar
            </div>
            <div class="col-2">
            <select class="cmbForma" name="form">
                <option <?php echo $region->form=="rect"?"selected='true'":"";?>  value="rect">Retangulo</option>
                <option <?php echo $region->form=="circle"?"selected='true'":"";?>  value="circle">Circulo</option>
            </select>
            </div>
            <div class="col-2">
                <?php
                $secSelect = null;
                $href = str_replace("#section-","",$region->href);
                if(is_numeric($href)){
                    $selectSecao = "selected='true'";
                    $hideUrl = "hidden";
                    $hideSecao = "";
                    $selectUrl = "";
                    $urlString = "";
                }
                else{
                    $selectSecao = "";
                    $hideUrl = "";
                    $hideSecao = "hidden";
                    $selectUrl = "selected='true'";
                    $urlString = $region->href;
                }
                ?>
                <select class="cmbTipo" name="tipo">
                    <option <?php echo $selectUrl;?> value="link">Link</option>
                    <option <?php echo $selectSecao; ?> value="section">Secao</option>
                </select>
            </div>
            <div class="col-sm">
                <select class="cmbSection <?php echo $hideSecao;?>" name="section">
                    <?php 
                    foreach($coursesec as $sec){
                        if($sec->section){
                            echo "<option ".($sec->section==$href?"selected='true'":"")." value='{$sec->section}'>".($sec->name?$sec->name:"Seção ".$sec->section)."</option>";
                        }
                    }

                    
                    ?>
                </select>
                <input class="url <?php echo $hideUrl;?>" value="<?php echo $urlString;?>" name="url" type="text" />
            </div>
            <div class="col-1">
                <button disabled="true" instance="<?php echo $region->id; ?>" class="btnRemove">Remover</button>
            </div>
            
        </div>
        </form>
        <?php
            }
        }
        else{
            ?>
        <form>
        <div class="containers row">
            <input type="hidden" name="x">
            <input type="hidden" name="y">
            <input type="hidden" name="weigth">
            <input type="hidden" name="heigth">
            <div class="col-2">
                <input type="radio" name="selected" class="rdSelect" />
                Selecionar
            </div>
            <div class="col-2">
            <select class="cmbForma" name="form">
                <option value="rect">Retangulo</option>
                <option value="circle">Circulo</option>
            </select>
            </div>
            <div class="col-2">
                <select class="cmbTipo" name="tipo">
                    <option value="link">Link</option>
                    <option value="section">Secao</option>
                </select>
            </div>
            <div class="col-sm">
                <select class="cmbSection hidden" name="section">
                    <?php echo $sections; ?>
                </select>
                <input class="url" name="url" type="text" value="" />
            </div>
            <div class="col-1">
                <button disabled="true" class="btnRemove">Remover</button>
            </div>
            
        </div>
        </form>
        <?php
        }
        ?>
    </div>
    <button id="btnAddMore">Adicionar Mais areas</button>
    <?php
    endif;
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    
    if($imageUrl){
        ?>
    <script>
        imageSource = '<?php echo $imageUrl; ?>'; 
        img = new Image();   // Create new img element
        img.src = imageSource;

        startImage = function(){
            var courseid = '<?php echo $courseid; ?>'; 
            var current_x = null
            var current_y = null
            var current_weigth = null
            var current_heigth = null
            var forma = null
            var currentSelect = null

            var addId = function(data){
                jQuery("input").attr("disabled",false);
                if(data){
                    currentSelect.parent().parent().find(".btnRemove").attr("instance",data.id);
                }
                else {
                    if(jQuery(".containers .btnRemove").length==1){
                        jQuery(".containers .btnRemove").attr("disabled",true);
                        jQuery(".containers .btnRemove").unbind("click");
                    }
                    if(jQuery(".rdSelect:checked").length==0){
                        jQuery(".rdSelect:first").click();
                    }
                }
            }

            var sendData = function(removeid){
                jQuery("input").attr("disabled",true);
                if(removeid){
                    $.ajax({
                        type: "POST",
                        url: "ajaxrequest.php",
                        data: {"removeid":removeid},
                        dataType:"json",
                        success: addId,
                    });
                }
                else{
                    data = {}
                    data.id = currentSelect.parent().parent().find(".btnRemove").attr("instance");
                    data.xleft = current_x.val();
                    data.ytop = current_y.val();
                    data.weigth = current_weigth.val();
                    data.heigth = current_heigth.val();
                    data.form = forma;
                    data.courseid=courseid;
                    if(currentSelect.parent().parent().find(".cmbTipo").val()=="link"){
                        data.href = currentSelect.parent().parent().find(".url").val();
                    }
                    else{
                        data.href = "#section-"+currentSelect.parent().parent().find(".cmbSection").val();
                    }
                    $.ajax({
                        type: "POST",
                        url: "ajaxrequest.php",
                        data: data,
                        dataType:"json",
                        success: addId,
                    });
                }
                
            }

            var newSelect = function(){
                currentSelect = jQuery(this)
                if(currentSelect){
                    jQuery(".rdSelect").prop('checked', false);
                    currentSelect.prop('checked', true);
                    current_heigth = jQuery(this).parent().prev()
                    current_weigth = current_heigth.prev()
                    current_y = current_weigth.prev()
                    current_x = current_y.prev()
                    forma = current_weigth.parent().find(".cmbForma").val()  
                }
            } 

            var changeLink = function(){
                    var pai = jQuery(this).parent().next()
                    pai.parent().find(".rdSelect").click()
                    if(this.value=="link"){
                        pai.children(".cmbSection").addClass("hidden")
                        pai.children(".url").removeClass("hidden")
                    }
                    else{
                        pai.children(".url").addClass("hidden")
                        pai.children(".cmbSection").removeClass("hidden")
                    }

                    sendData()
            }

            var changeUrl = function(){
                jQuery(this).parent().parent().find(".rdSelect").click();
                sendData(); 
            }

            function removeBindings(){
                if(jQuery(".containers .btnRemove").length>1){
                    jQuery(".containers .btnRemove").attr("disabled",null);
                    jQuery(".containers .btnRemove").unbind("click");
                    jQuery(".containers .btnRemove").click(function(){
                        var id = jQuery(this).attr("instance");
                        if(id){
                            sendData(id);
                        }
                        jQuery(this).parent().parent().remove()
                        if(jQuery(".containers .btnRemove").length==1){
                            jQuery(".containers .btnRemove").attr("disabled",true);
                            jQuery(".containers .btnRemove").unbind("click");
                        }
                        if(jQuery(".rdSelect:checked").length==0){
                            jQuery(".rdSelect:first").click();
                        }
                    })
                }
            }

            var changeForma = function(){
                forma=this.value;
                jQuery(this).parent().parent().find(".rdSelect").click();
            }

            function initiateBindings(){
                jQuery(".rdSelect").off("change")
                jQuery(".rdSelect").change(newSelect)
                jQuery(".cmbForma").off("change")
                jQuery(".cmbForma").change(changeForma)
                jQuery(".cmbTipo").off("change")
                jQuery(".cmbTipo").change(changeLink);
                jQuery(".cmbSection").off("change");
                jQuery(".cmbSection").change(changeUrl);
                jQuery(".url").off("change");
                jQuery(".url").change(changeUrl);
                jQuery(".rdSelect:last").click();
            }

            jQuery("#btnAddMore").click(function(){
                jQuery("#containerAdd").append('<form><div class="containers row">'+
            '<input type="hidden" name="x">'+
            '<input type="hidden" name="y">'+
            '<input type="hidden" name="weigth">'+
            '<input type="hidden" name="heigth">'+
            '<div class="col-2">'+
                '<input type="radio" name="selected" class="rdSelect" />'+
                'Selecionar'+
            '</div>'+
            '<div class="col-2">'+
            '<select class="cmbForma" name="form">'+
                '<option value="rect">Retangulo</option>'+
                '<option value="circle">Circulo</option>'+
            '</select>'+
            '</div>'+
            '<div class="col-2">'+
                '<select class="cmbTipo" name="tipo">'+
                    '<option value="link">Link</option>'+
                    '<option value="section">Secao</option>'+
                '</select>'+
            '</div>'+
            '<div class="col-sm">'+
                '<select class="cmbSection hidden" name="section">'+
                    "<?php echo $sections; ?>"+
                '</select>'+
                '<input class="url" name="url" type="text" value="" />'+
            '</div>'+
            '<div class="col-1">'+
            '    <button class="btnRemove">Remover</button>'+
            '</div>'+
        '</div></form>');
                removeBindings();
                initiateBindings();
            })
            initiateBindings();
            removeBindings();

            var imageWidth = img.naturalWidth; // this will be 1024 at max
            var imageHeight = img.naturalHeight; // this will be 1024 at max
            if(imageWidth>1024){
                imageHeight = (imageHeight*1024)/imageWidth;
                imageWidth = 1024;
            }
            jQuery("#imgmapcanvas").attr("height",imageHeight);
            var canvas = document.getElementById("imgmapcanvas");
            var ctx = canvas.getContext("2d");
            var canvasOffset = jQuery("#imgmapcanvas").offset();
            var offsetX = canvasOffset.left;
            var offsetY = canvasOffset.top;
            var startX;
            var startY;
            var isDown = false;
            

            drawImage =function(){
                ctx.globalAlpha = 1;
                ctx.clearRect(0, 0,  canvas.width, canvas.height);
                ctx.drawImage(img, 0,0,imageWidth,imageHeight);
                jQuery(".rdSelect").each(function(){
                    if(jQuery(this)==currentSelect)
                        return true;
                    var x =jQuery(this).parent().prev().prev().prev().prev().val() 
                    var y = jQuery(this).parent().prev().prev().prev().val()
                    ctx.moveTo(x,y)
                    if(jQuery(this).parent().next().children("select").val() =="rect"){  
                        ctx.rect(x,y,
                        jQuery(this).parent().prev().prev().val(), 
                        jQuery(this).parent().prev().val());

                    }
                    else{
                        ctx.arc(x,y,
                        jQuery(this).parent().prev().val(),0, 2 * Math.PI);
                    }
                    ctx.stroke();
                    ctx.globalAlpha = 0.3;
                    ctx.fill();
                    ctx.globalAlpha = 1;
                })
            }

            function drawCircle(x,y){
                drawImage();
                ctx.moveTo(x,y)
                ctx.beginPath();
                var coordx = startX+((x-startX)/2);
                var coordy = startY + ((y - startY) / 2);
                var rad = ( Math.sqrt( ((startX-x)*(startX-x)) + ((startY-y)*(startY-y)) ) )/2
                ctx.arc(coordx, coordy , rad,0, 2 * Math.PI);
                ctx.stroke();
                ctx.globalAlpha = 0.3;
                ctx.fill();
                current_heigth.val(rad)
                current_weigth.val(rad)
                current_y.val(coordy)
                current_x.val(coordx)
            }

            function drawRect(x,y){
                drawImage();
                ctx.moveTo(x,y);
                ctx.beginPath();
                var sizex = Math.abs(startX-x);
                var sizey = Math.abs(startY-y);
                var originx = startX>x?x:startX;
                var originy = startY>y?y:startY;
                ctx.rect(originx, originy, sizex, sizey);
                ctx.stroke();
                ctx.globalAlpha = 0.3;
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
                sendData(); 
            }

            function handleMouseOut(e) {
                if (!isDown) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                isDown = false;
                sendData(); 
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

            jQuery("#imgmapcanvas").mousedown(function (e) {
                handleMouseDown(e);
            });
            jQuery("#imgmapcanvas").mousemove(function (e) {
                handleMouseMove(e);
            });
            jQuery("#imgmapcanvas").mouseup(function (e) {
                handleMouseUp(e);
            });
            jQuery("#imgmapcanvas").mouseout(function (e) {
                handleMouseOut(e);
            });
            scrolFunction = function(e){
                var BB=canvas.getBoundingClientRect();
                offsetX = BB.left;
                offsetY = BB.top;
                
            };
            
            document.addEventListener('scroll',scrolFunction);
            drawImage();

        }

        img.addEventListener('load', checkVariable, false);

        function checkVariable(){
            img.removeEventListener('load', checkVariable);
            if ( window.jQuery){
                startImage()
            }
            else{
                window.setTimeout("checkVariable();",100);
            }
        }


        //checkVariable();
    </script>
        <?php
    }

