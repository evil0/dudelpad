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
		<script type="text/javascript" src="scripts/jquery.min.js"></script>
		<script src="http://<?php echo $_SERVER['HTTP_HOST'] ?>:3000/socket.io/socket.io.js"></script> 
<link rel="stylesheet" type="text/css" href="css/mobile.css">
		<script type="text/javascript" src="scripts/shake.js"></script>
		<script type="text/javascript" src="scripts/paper.js"></script>
    	<script type="text/javascript">   
		var socket 	= io.connect('<?php echo $_SERVER['HTTP_HOST'] ?>:3000');

		var path;
		var dudleDraw = function(options) {
			var color = '0, 0, 0';
			
		    // grab canvas element
		    var canvas = document.getElementById(options.id),
		        ctxt = canvas.getContext("2d");
		   
			canvas.height = window.innerHeight;
			canvas.width = window.innerWidth;
			
			
		    paper.setup(canvas);
		    path = new paper.Path();  

		    var lines = [,,];
			var points = [,,];
		    var offset = $(canvas).offset();
		    
			/* Custom method fired when shake occurs */
			function shakeEventDidOccur () {
				if (confirm("Are you sure to clear your work?")) {
					canvas.width = canvas.width;
					socket.emit('clear canvas');
				}
			}
		
		    var self = {
		        /* bind touch events */
		        init: function() {
		            window.addEventListener('shake', shakeEventDidOccur, false);
		
		            canvas.addEventListener('touchstart', self.preDraw, false);
		            canvas.addEventListener('touchmove', self.draw, false);
		            canvas.addEventListener('touchend', self.ends, false);
		            
		        },

				// identify every finger for multi-touch support
		        preDraw: function(event) {
		        	path = new paper.Path(); 
		        	path.strokeColor = 'black';
					path.strokeStyle = "rgba("+color+", 0.8)";
					path.strokeWidth = 1;
					
		        	socket.emit('mouseDown');
					
					$.each(event.touches, function(i, touch) {
						var id      = touch.identifier;	
						lines[id] 	= {	x     : this.pageX - offset.left, 
									 	y     : this.pageY - offset.top, 
									  	color : "black" };
					});
					
		            event.preventDefault();
		        },

				// keep trace of every finger movement
		        draw: function(event) {
		            var e = event, hmm = {};
					
						$.each(event.touches, function(i, touch) {
							var id = touch.identifier,
								moveX = this.pageX - offset.left - lines[id].x,
								moveY = this.pageY - offset.top - lines[id].y;
			
							var ret = self.move(id, moveX, moveY);
							lines[id].x = ret.x;
							lines[id].y = ret.y;
						});
					
		            event.preventDefault();
		        },

				// start drawing and broadcasting
		        move: function(i, changeX, changeY) {
				
					points = {x: lines[i].x + changeX, y: lines[i].y + changeY};
					var dx  = lines[i].x + changeX - lines[i].x,
					dy = lines[i].y + changeY - lines[i].y;

					// starting point = last x,y coordinates
					path.moveTo(lines[i].x, lines[i].y);

					// ending point = current x, y coordinates
					path.lineTo(points.x,points.y);

					paper.view.draw();
					

		            var dX              =   lines[i].x - points.x; // distanza orizzontale punto-punto tra il cursore e il centro del logo
		            var dY              =   lines[i].y - points.y; // distanza verticale
		            var distance        =   Math.sqrt(Math.pow(dX, 2) + Math.pow(dY, 2)); // ipotenusa

					/* delta control */ 
		            if(distance>0) {
		            	// streaming infos
						socket.emit('clientDraw', color, lines[i].x, lines[i].y,points.x,points.y,path.strokeWidth,canvas.width, canvas.height);
		            }
		            
		            return { x: lines[i].x + changeX, y: lines[i].y + changeY };
				
				
				},
				ends: function() {
			    	path.simplify(5);
					paper.view.draw();
					socket.emit('mouseUp');
					
				}
		    };
		
		    return self.init();
		};
		
		
		$(function(){
		  var dudelPadListener = new dudleDraw({id:"pad", size: 1 }); 
		});
		
		</script>    
	</head>
	
	<body>
	
		<div id="content">
	
		   	<canvas id="pad" ></canvas> 
		  
			<br/>
			<b>Code by</b> Marco Costantino - <a href="http://evil0.github.com">http://evil0.github.com</a>
			<br/><i>Streaming to: http://<?php echo $_SERVER['HTTP_HOST']."/index.php" ?> </i>
			Viewers: <span id="wievers"></span>
		
		</div>
	</body>
</html>

