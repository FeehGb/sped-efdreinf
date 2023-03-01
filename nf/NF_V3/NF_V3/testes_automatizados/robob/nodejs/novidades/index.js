var app = require('express')();
var http = require('http').Server(app);
const exec = require('child_process').exec
const execSync = require('child_process').execSync;


// var io = require('socket.io')(http);




var  getPythonPages =  function(){
  comand = "python /var/www/html/nf/NF_V3/NF_V3/testes_automatizados/robob/novidades.py";
  return execSync(comand).toString();
  // return  exec(comand)
};





app.get('/', function(req, res){
  res.send(getPythonPages())
  // res.sendFile(__dirname + '/index.html');
});
/*
io.on('connection', function(socket)
{
  console.log('a user connected');
  
  socket.on('disconnect', function(){
    console.log('user disconnected');
  });
  
  socket.on('chat message', function(msg){
    io.emit('chat message', msg);
  });
});
*/
http.listen(3333, function(){
  console.log('listening on *:3333');
});
