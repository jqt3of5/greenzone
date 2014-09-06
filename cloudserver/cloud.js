var fs = require('fs');
var path = require('path');
var net = require('net');
var util = require('util');
var mongodb = require('mongodb');

var config = "";
fs.readFile("cloud.config", function(err, data)
{
    config = JSON.parse(data);
    var server = net.createServer(function(client) {

	//improve the user validation! if we need to...
	//This only supports ip authentication
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
		//request format....
		//command: Command currently being executed
		//         Available Commands:
		//         delete (username, filename) : bool
		//         add (username, filename, buffer) : bool
		//         update (username, filename, buffer) : bool
		//         get (username, filename) : {metadata, buffer: [byte]}
		//         list (username) : [filename]
		//username: Username of the file owner
		//Optional - 
		//metadata: {filename, creationDate, size, revision, username, isEncrypted, isCompressed}
		//filename: Filename being effected
		//filedata: File data

		var request = JSON.parse(data);
		switch(request.command)
		{
		case 'delete':
		    deleteFileForUser(request.username, request.filename, function(success) {
			if (result != "")
			{
			    client.write(result);
			}
//			client.end();
		    });
		    break;
		case 'list':
		    listFilesForUser(request.username, function(files) {
			if (files != "")
			{
			    client.write(JSON.stringify(files));
			}
//			client.end();
		    });
		    break;
		case 'add':
		    addFile(request.metadata, request.filedata, function(err){
			client.write(err);
		    });
		    break;
		case 'update':
		    updateFile(request.metadata, request.filedata, function(err) {
			client.write(err);
//			client.end();
		    });
		    break;
		case 'get':
		    getFileForUser(request.username, request.filename, function(metadata, filedata) {
			if (filedata != "")
			{
			    client.write(filedata);
			}
//			client.end();
		    });
		    break;
		}
	    } catch(exception) {
		console.log(exception);
		client.end();
	    }
	});
    });
    server.listen(config.port, function(){
    });
});

function deleteFileForUser(username, filename, onComplete)
{
    if (username == undefined || filename == undefined)
    {
	console.log("Something was undefined, stopping");
	finished("Something was undefined, stopping")
	return;
    }
    connectToDb(function(err, db) {

	if (err) 
	{
	    console.log(err); 		    
	    finished(err);
	    return; 
	}
	
	mongodb.GridStore.unlink(db, filename, {root:username}, function(err, gridStore) {
	    finished("");
	    db.close();
	});
    });
}

function listFilesForUser(username,finished)
{
    if (username == undefined)
    {
	console.log("Something was undefined, stopping");
	finished("")
	return;
    }
    connectToDb(function(err, db) {

	if (err) 
	{
	    console.log(err); 		    
	    finished("");
	    return; 
	}

	mongodb.GridStore.list(db, username, function(err, files) {
	    finished(files);
	    db.close();
	});
    });
}

function addFile(metadata, filedata, finished)
{
    if (metadata == undefined || filedata == undefined)
    {
	console.log("Something was undefined, stopping");
	finished("Something was undefiend, stopping")
	return;
    }
    connectToDb(function(err, db) {

	if (err) 
	{
	    console.log(err); 		    
	    finished(err);
	    return; 
	}

	mongodb.GridStore.exist(db, metadata.filename, metadata.username, function(err, result) {
	    if (!result)
	    {
		var gridStore = new mongodb.GridStore(db, new mongodb.ObjectID(), metadata.filename, "w", {metadata:metadata, root:metadata.username});
		gridStore.open(function(err, gridStore){
		    var buffer = new Buffer(filedata);
		    gridStore.write(buffer, function(err, gridStore) {
			gridStore.close(function(err, fileData){
			    finished("");
			    db.close();
			});
		    });
		});
	    } else {
		console.log("File already exists");
		finished("File already exists");
	    }
	});
    });
}

function updateFile(metadata, filedata, finished)
{
    if (metadata == undefined || filedata == undefined)
    {
	console.log("Something was undefined, stopping");
	finished("Something was undefiend, stopping")
	return;
    }
    connectToDb(function(err, db) {

	if (err) 
	{
	    console.log(err); 		    
	    finished(err);
	    return; 
	}

	mongodb.GridStore.exist(db, metadata.filename, metadata.username, function(err, result) {
	    if (result)
	    {
		var gridStore = new mongodb.GridStore(db, metadata.filename, "w", {metadata:metadata, root:metadata.username});
		gridStore.open(function(err, gridStore){
		    var buffer = new Buffer(filedata);

		    //write out file data
		    gridStore.write(buffer, function(err, gridStore) {
			gridStore.close(function(err, fileData){
			    finished("");
			    db.close();
			});
		    });
		    //TODO: save revisions!!
		});
	    } else {
		finished("file does not exist");
	    }
	});
    });
}

function getFileForUser(username, filename, finished)
{

}

function connectToDb(onConnect)
{
    mongodb.MongoClient.connect("mongodb://" + config.filedb.host + ":" + config.filedb.port + "/" + config.filedb.name, onConnect);
}
