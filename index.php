<!-- 
 * dudelPad - Realtime multi-touch and multi-user sketchpad using node.js and socket.io (proof-of-concept)
 *
 *   howTo: 
 *		1) start drawserver.js by typing 'node drawserver.js'
 *		2) connect through a browser to the streaming page index.php
 *		3) let other users (and yourself too) connect to draw.php with a touchscreen device and start drawing!
 *		4) shake your touch device to blank the board!
 *
 *   coded by: Marco Costantino
 *   github: evil0.github.com
 *   mail: marco.costantino@ymail.com
 * 
 -->

<!DOCTYPE html>

<html lang="en">
	<head>
		<title>dudlePad - evil0.github.com</title>
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script type="text/javascript" src="scripts/jquery.min.js"></script>

		<script src="http://<?php echo $_SERVER['HTTP_HOST'] ?>:3000/socket.io/socket.io.js"></script> 
		    
		<script type="text/javascript">          
		
			var socket 	= io.connect('<?php echo $_SERVER['HTTP_HOST'] ?>:3000');
			
				socket.on('connect', function () {
						socket.emit('savemyid', '<?php echo $_SERVER['REMOTE_ADDR'] ?>');
				});
			           
			var dudleDraw = function(options) {
			
			    var canvas = document.getElementById(options.id),
			        ctxt = canvas.getContext("2d");
			
			    ctxt.lineWidth = options.size || Math.ceil(Math.random() * 35);
			    ctxt.lineCap = options.lineCap || "round";
			
			 	socket.on('clear', function () {
					canvas.width = canvas.width;
				});
			 
			    socket.on('serverDraw', function (color,startX, startY,changeX,changeY, size,w,h) { // trasforma ste var in un array "params"
			
						ctxt.lineWidth = size;
			
						ctxt.strokeStyle = "rgba("+color+", 0.8)";
			            ctxt.beginPath();
						ctxt.moveTo(startX*(canvas.width/w), startY*(canvas.height/h));
						
						ctxt.lineTo(changeX*(canvas.width/w),changeY*(canvas.height/h));
						
			            ctxt.stroke();
			            ctxt.closePath();
				});
			
			
			};
			
			
			$(function(){
			  var dudelPadListener = new dudleDraw({id:"pad", size: 1 }); 
			});
		</script>    

		</head>
	<body>
		<div id="content">
		<canvas id="pad" height=650 width=1200></canvas> 
		
		<br/>
		<b>Code by</b> Marco Costantino - <a href="http://evil0.github.com">http://evil0.github.com</a>
		<br/><i>Start drawing on your touch device here: http://<?php echo $_SERVER['HTTP_HOST']."/draw.php" ?> </i>
		Viewers: <span id="wievers"></span>
		
		</div>
	</body>
</html>


