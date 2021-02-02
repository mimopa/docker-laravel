let app = require('express')();
fs = require('fs');
const server = require('https').createServer({
    key: fs.readFileSync('/home/videoapp/public_html/yourdomain/node/ssl/leuke.key'),
    cert: fs.readFileSync('/home/videoapp/public_html/yourdomain.com/node/ssl/leuke.crt'),
    ca: fs.readFileSync('/home/videoapp/public_html/yourdomain.com/node/ssl/leuke.ca')
},app);
let io = require('socket.io')(server);
let socket_id;
user=[];
io.on('connection', (socket) => {
  io.sockets.on('connect', function(socket) {
     console.log('connected user');
  });
  
  socket.on('disconnect', function(){
    console.log('disconnect user');
  });
 
  socket.on('user-id', (id) => {
      console.log("User-id:" + id);
    user[id]=socket.id;
    console.log("socket id: ",socket.id);
  });
  
  socket.on('typing', (data) => {
      console.log("typing...");
      console.log(data);
    io.to(user[data.to_id]).emit('typing',{
        from_id:data.from_id
    });
  });
  
  socket.on('send_msg', (data) => {
      console.log(data);
    io.to(user[data.to_id]).emit('send_msg',{
        from_id:data.from_id,
        to_id:data.to_id,
        msg:data.msg,
        sent_on:data.sent_on,
        
    });
  });
 
});
 
const hostname = 'yourdomain.com';
const port = 3001;

server.listen(port, hostname, () => {
  console.log("Server running at https://${"+hostname+"}:${"+port+"}/");
});