export const init = (imageSource) => {
    require(['jquery'], function($) { 
        var img = new Image();   // Create new img element
        img.addEventListener(
            "load",
            () => {
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
                $(".rdSelect").change(newSelect);
                $(".rdSelect").click();
                $(".cmbForma").change(()=>forma=this.value);
                $(".cmbTipo").change(changeLink);
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
                
                //Desenha imagem
                function drawImage(){
                    ctx.drawImage(img, 0,0,imageWidth,imageHeight);
                    $(".rdSelect").each(function(){
                        if($(this)==currentSelect){
                            return true;
                        }
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
        });
        img.src = imageSource;
    });
};