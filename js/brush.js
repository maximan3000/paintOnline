// JavaScript Document

function makeLine(x, y, toX, toY) {
	ctx.beginPath();
	ctx.moveTo(x, y);
	ctx.lineTo(toX, toY);
	ctx.closePath();
	ctx.stroke();
}

function brushDown(e){
	action = true;
	brush.mPrevPos.x = e.x;
	brush.mPrevPos.y = e.y;
	makeLine(e.x, e.y, e.x-1, e.y);
};

function brushUp(){
	action = false;
};

function brushMove(e){
	if (action) {
		makeLine(brush.mPrevPos.x, brush.mPrevPos.y, e.x, e.y);
		brush.mPrevPos.x = e.x;
		brush.mPrevPos.y = e.y;
  }
};

function bDown(event){
	var e = { "x" : event.offsetX, "y" : event.offsetY };
	brushDown(e);
};

function bUp(event){
	brushUp();
};

function bMove(event){
	var e = { "x" : event.offsetX, "y" : event.offsetY };
	
	if (action){
		var data = {};
		data.x = e.x;
		data.y = e.y;
		data.toX = brush.mPrevPos.x;
		data.toY = brush.mPrevPos.y;
		data.tool = "brush";
		sendMessage(data, "pngMessage");
	}
	
	brushMove(e);
};