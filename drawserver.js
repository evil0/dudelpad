/* dudelPad - Realtime multi-touch and multi-user sketchpad using node.js and socket.io (proof-of-concept)
 * 
 *   howTo: 
 *		1) start drawserver.js by typing 'node drawserver.js'
 *		2) connect through a browser to the streaming page index.php
 *		3) let other users (and yourself too) connect to draw.php with a touchscreen device and start drawing!
 * 		4) shake your touch device to blank the board!
 * 
 *   coded by: Marco Costantino
 *   github: evil0.github.com
 *   mail: marco.costantino@ymail.com
 * 
 */

var io = require('socket.io').listen(3000);
var clients = new Array();

io.sockets.on('connection', function (socket) {

	/* savemyid(idd) - pushes socket ids into array "clients"
	 * 
	 * 		@idd => socket id (received by client)
	 * 
	 */
	
	socket.on('savemyid', function (sid) {
		 
		console.log(sid+ ' connected to chat server.'); 
		clients[sid] = socket.id;
		 
	});
	
    /* private message(to, startX, startY, changeX, changeY, d, size, w ,h)
     * 
     * 		@to 	=> socket id (receiver)
     * 		@color	=> line color expressed in rrggbb
     * 		@[startX,startY] 	=> start coordinates (moveTo)
     * 		@[changeX,changeY] 	=> end coordinates (lineTo)
     * 		@size	=> line size
     * 		@w		=> device width
     * 		@h		=> device height
     * 
     */

	socket.on('clientDraw', function (color, startX, startY,changeX,changeY,size,w ,h) {
		socket.broadcast.emit('serverDraw', color,startX, startY,changeX,changeY,size,w ,h);
		//io.sockets.clients[id].emit for private talking
	});
	
	/* clear canvas(to) - clear canvas
	 * 
	 * 		@to 	=> socket id (receiver)
	 */
	
	socket.on('clear canvas', function () {
		socket.broadcast.emit('clear');
	});
	
	socket.on('mouseUp', function () {
		socket.broadcast.emit('mouseUp');
		//io.sockets.clients[id].emit for private talking
	});
	
	socket.on('mouseDown', function () {
		socket.broadcast.emit('mouseDown');
		//io.sockets.clients[id].emit for private talking
	});
	
});
