// JavaScript Document


    function skatchDown(e){
      	action = true;
		skatch.points[0] = [e.x, e.y];
		skatch.pointer = 0;
    };

    function skatchUp(){
      	skatch.points = new Array(10);
      	action = false;
    };

    function skatchMove(e, random1, random2){
      if (action) {
        var nextpoint = skatch.pointer + 1;
        if (nextpoint > 9) nextpoint = 0;
        ctx.beginPath();
        ctx.moveTo(skatch.points[skatch.pointer][0],skatch.points[skatch.pointer][1]);
        ctx.lineTo(e.x, e.y);
        if (skatch.points[nextpoint]) {
          ctx.moveTo(skatch.points[nextpoint][0] + random1, skatch.points[nextpoint][1] + random2);
          ctx.lineTo(e.x, e.y);
        }
        ctx.stroke();
        skatch.pointer = nextpoint;
        skatch.points[skatch.pointer] = [e.x, e.y];
      }
    };

	function updateSkatchMove(data){
		var e = data.e;
		var pts = data.points;
		var ptr = data.pointer;
		var nextpoint = ptr + 1;

		if (nextpoint > 9) nextpoint = 0;
		ctx.beginPath();
		ctx.moveTo(pts[ptr][0],pts[ptr][1]);
		ctx.lineTo(e.x, e.y);
		if (pts[nextpoint]) {
			ctx.moveTo( pts[nextpoint][0] + data.rnd.rnd1, pts[nextpoint][1] + data.rnd.rnd2 );
			ctx.lineTo(e.x, e.y);
		}
		ctx.stroke();
    };

    function mDown(event){
		var e = { "x" : event.offsetX, "y" : event.offsetY };
		skatchDown(e);
    };

    function mUp(event){
		skatchUp();
		
    };

    function mMove(event){
		if (action){
			var e = { "x" : event.offsetX, "y" : event.offsetY};
			var rnd = { "rnd1": Math.round(Math.random()*10-5), "rnd2": Math.round(Math.random()*10-5) }
			
			//json
			var data = {};
			data.points = skatch.points;
			data.pointer = skatch.pointer;
			data.e = e;
			data.tool = "skatch";
			data.rnd = rnd;
			sendMessage(data, "pngMessage");
			
			skatchMove(e, rnd.rnd1, rnd.rnd2);
		}
		
		
    };