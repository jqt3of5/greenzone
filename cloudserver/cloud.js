var fs = require('fs');
var path = require('path');
var net = require('net');
var util = require('util');

var config = "";
fs.readFile("cloud.config", function(err, data){
    config = JSON.parse(data);
    var server = net.createServer(function(client){
	if (config.remotehost.indexOf(client.remoteAddress) < 0)
	{
	    client.end('Not Authorized');
	    console.log(client.remoteAddress + " - Not Authorized");
	    return;
	}
	console.log(client.remoteAddress + " - Authorized");

	client.on('data', function(data) {
	    try {
		console.log("recv data: " + data);
		var request = JSON.parse(data);
		var requestPath = path.join(config.userdir, request.username, request.subpath);
		
		fs.readdir(requestPath, function(err, files) {
		    var response = Array();
		    if (files == undefined)
		    {
			client.end();
			return;
		    }
		    files.forEach(function(file){
			var stats = fs.statSync(path.join(requestPath, file));
			var resp = {"filename":file, "type" : stats.isDirectory() ? "folder" : "text", "size" : util.inspect(stats).size};
			
			response.push(resp);
		    });
		    
		    console.log("sent data: " + JSON.stringify(response));
		    client.write(JSON.stringify(response));
		    client.end();
		});

	    } catch(exception) {
		client.end('exception occured');
	    }
	});
    });
    server.listen(config.port, function(){
	
    });
});
