var canvas = document.getElementById("canvas");
var ctx = canvas.getContext("2d");
var canvasOffset = $("#canvas").offset();
var offsetX = canvasOffset.left;
var offsetY = canvasOffset.top;
var startX;
var startY;
var isDown = false;

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
    ctx.beginPath();
    var coordx = startX+((x-startX)/2);
    var coordy = startY + ((y - startY) / 2);
    var rad = ( Math.sqrt( ((startX-x)*(startX-x)) + ((startY-y)*(startY-y)) ) )/2
    ctx.arc(coordx, coordy , rad,0, 2 * Math.PI);
    ctx.stroke();
}

function drawRect(x,y){
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.beginPath();
    var sizex = Math.abs(startX-x);
    var sizey = Math.abs(startY-y);
    var originx = startX>x?x:startX;
    var originy = startY>y?y:startY;
    ctx.rect(originx, originy, sizex, sizey);
    ctx.stroke();

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