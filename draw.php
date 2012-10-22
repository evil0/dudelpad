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
    	 $(document).ready( function(){ 
			 //Get the canvas & context 
			 var c = $('#pad'); 
			 var ct = c.get(0).getContext('2d'); 
			 var container = $(c).parent(); 
			 //Run function when browser resizes
$(window).resize( respondCanvas ); 
			 function respondCanvas(){
				  c.attr('width', $(container).width() ); 
				  //max width 
				  c.attr('height', $(container).height() ); 
				  //max height 
				  //Call a function to redraw other content (texts, images etc) 
				  } //Initial call 
				  respondCanvas(); 
				  });
		// connection
		var socket 	= io.connect('<?php echo $_SERVER['HTTP_HOST'] ?>:3000');

		/** TODO: use of paper.js as already done in index.php **/
		var dudleDraw = function(options) {
			var color = '0, 0, 0';
			
		    // grab canvas element
		    var canvas = document.getElementById(options.id);
			canvas.height = window.innerHeight;
			canvas.width = window.innerWidth;
			
		    var ctxt = canvas.getContext("2d");
		        
			
		    ctxt.lineWidth = options.size || Math.ceil(Math.random() * 0.1);
		    ctxt.lineCap = options.lineCap || "round";
		    ctxt.pX = undefined;
		    ctxt.pY = undefined;
		
		    var lines = [,,];
			var points = [,,];
		    var offset = $(canvas).offset();
			//define a custom method to fire when shake occurs.
			function shakeEventDidOccur () {
		
				//put your own code here etc.
				if (confirm("Are you sure to clear your work?")) {
					canvas.width = canvas.width;
					socket.emit('clear canvas');
				}
			}
		
		    var self = {
		        //bind touch events
		        init: function() {
		            window.addEventListener('shake', shakeEventDidOccur, false);
		
		            canvas.addEventListener('touchstart', self.preDraw, false);
		            canvas.addEventListener('touchmove', self.draw, false);
		            canvas.addEventListener('touchend', self.ends, false);
		            
		        },

				// identify every finger for multi-touch support
		        preDraw: function(event) {
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
				
					ctxt.strokeStyle = "rgba("+color +",0.8)";
					ctxt.beginPath();

					// starting point = last x,y coordinates
					ctxt.moveTo(lines[i].x, lines[i].y);

					// ending point = current x, y coordinates
					ctxt.lineTo(points.x,points.y);
					
					ctxt.stroke();
		            ctxt.closePath();

		            var dX              =   lines[i].x - points.x; // distanza orizzontale punto-punto tra il cursore e il centro del logo
		            var dY              =   lines[i].y - points.y; // distanza verticale
		            var distance        =   Math.sqrt(Math.pow(dX, 2) + Math.pow(dY, 2)); // ipotenusa

					/* delta control */ 
		            if(distance>0) {
		            	// streaming infos
						socket.emit('clientDraw', color, lines[i].x, lines[i].y,points.x,points.y,ctxt.lineWidth,canvas.width, canvas.height);
		            }
		            return { x: lines[i].x + changeX, y: lines[i].y + changeY };
				
				
				},
				ends: function() {
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

