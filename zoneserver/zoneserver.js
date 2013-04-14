var tls = require('tls');
var fs = require('fs');
var mysql = require('mysql');
var crypto = require('crypto');
var util = require('util');
var child_proc = require('child_process');
var options = {
    key: fs.readFileSync("./ssl/zonekey.pem"),
    cert: fs.readFileSync("./ssl/zonecert.pem"),
    ca: [fs.readFileSync("./ssl/zonecert.pem"),fs.readFileSync("/home/jqt3of5/demoCA/cacert.pem") ],
    passphrase: "diogee",
    requestCert: true
};

var readOnlyOptions = {
    key: fs.readFileSync("./ssl/zonekey.pem"),
    cert: fs.readFileSync("./ssl/zonecert.pem"),
    passphrase: "diogee",
    requestCert: false
}

var host = 'localhost';
var user = 'users';
var pwd = 'diogee';
var db = 'accounts';
var table = 'users';


var zoneFile = fs.readFileSync("/var/lib/bind/db.jqt3of5.com","utf8");
var zoneArray = {};

//match and insert into a dictionary. Use the domain as the key
var domainRegex = /^((\w|@)+)\s+IN\s+(CNAME|A|NS)\s+((\w|\.)+)$/gm
var result = domainRegex.exec(zoneFile);
while (result != null)
{ 

    if (result[1] == '@')
    {
	if (zoneArray[result[1]] == undefined)
	{
	    zoneArray[result[1]] = new Array();
	}
	zoneArray[result[1]].push({'domain':result[1], 'type':result[3], 'value':result[4]});
    }else {
	zoneArray[result[1]] = {'domain':result[1], 'type':result[3], 'value':result[4]};
    }
    result = domainRegex.exec(zoneFile);
}

//this whole bit of code should become obselete. Will be using DDNS for updating the server. 
// var tempout = fs.openSync("test.out", "w");
// setInterval(function(){
//     var toWrite = "";
//     for (var dom in zoneArray)
//     {
// 	var zone = zoneArray[dom];
// 	if (dom == '@')
// 	{
// 	    for (var origin in zone)
// 	    {
// 		toWrite += util.format("%s\tIN\t%s\t%s\n",zone[origin].domain, zone[origin].type, zone[origin].value);
// 	    }  
// 	} else {
// 	    toWrite += util.format("%s\tIN\t%s\t%s\n",zone.domain, zone.type, zone.value);
// 	}
//     }
//     fs.write(tempout,toWrite,0, toWrite.length, 0);
// }, 20000);


var readOnlyServer = tls.createServer(readOnlyOptions, function(cltStream) {
    var zoneState = JSON.stringify(zoneArray);
    
    cltStream.on('data', function(data){
	//the data should be the username we are looking up. 
	//respond with only the domains associate with him. 
	cltStream.write(zoneState.length+"\n");
	cltStream.write(zoneState);
	cltStream.end();

    });
    
});

var server = tls.createServer(options, function (cltStream) {

    //extract the username form the certificate
    var peerCert = cltStream.getPeerCertificate();
    var remoteUser = "";
    var remotePwd = "";

    if (peerCert.subject == undefined)
    {
	//prompt for username and password
	console.log("Requires a user name and password");
	
	remoteUser = "jqt3of5";
	var hash = crypto.createHash('md5');
	hash.update("");
	remotePwd = hash.digest("hex");
    } else {
	remoteUser = peerCert.subject.CN;
    }

    console.log("You connected as " +  remoteUser, cltStream.authorized ? 'authorized' : 'unauthorized');
    
    if (cltStream.authorizationError != undefined){
	console.log(cltStream.authorizationError);
    }

    //verify this user is in the database
    var con = mysql.createConnection({
	host: host,
	user: user,
	password: pwd,
    });
    con.connect();
    
    con.query('use ' + db);

    con.query('SELECT userid, password FROM users WHERE userid=\''+remoteUser+'\'', 
	      function(err, result, fields){
		  if (err) throw err;
		 
		  if (result.length == 0 || (!cltStream.authorized && result[0].password != remotePwd))
		      {
			  console.log('Authentication Fail for user: ' + remoteUser);
			  cltStream.write("USER: FAIL\n");
			  cltStream.destroy();
			  return;
		      }

		  console.log('Authentication Success for user: ' + remoteUser);
		  cltStream.write("USER: OK\n"); 

		  cltStream.on('data', function(data){
		      console.log("from client: " + data);
		      
		      var request = JSON.parse(data);
		      request.userid = escape(request.userid);
		      var child =  child_proc.exec("nsupdate -l -k /etc/bind/rndc.key", function(error, stdout, stderr)
						   {
						       if(stdout.length > 0 || stderr.length > 0)
						       {
							   var response = "Unsuccessful. Error: " + stderr;
							   cltStream.write(response.length + "\n");
							   cltStream.write(response);
							   
							   console.log("nsupdate: " + stdout);
							   console.log("nsupdate: " + stderr);
						       }
						       cltStream.write("7\n");
						       cltStream.write("Success");
						   });
		      //child.stdin.write("server localhost\n");

		      for (var i in request.reqs)
		      {
			  var req = request.reqs[i];
			  handleRequest(con, child, request.userid,escape(req.command),escape(req.domain),escape(req.zone),escape(req.type),escape(req.value));
		      }
		      child.stdin.write("send\n");
		      child.stdin.write("quit\n");
		  });

		  cltStream.on('end', function(){
		  });

	      });

    function handleRequest(con, nschild,  userid, command, domain, zone, type, value)
    {
	var response = "";
	nschild.stdin.write("zone "+zone+"\n");
	//are you supposed to be messing with this domain?
	con.query('SELECT domain, zone, userid FROM domains WHERE domain=\''+domain+'\' AND zone=\''+zone+'\'', 
		  function(err, result, fields){
		      // var response = "You don't have permission to access: " + req.domain+"."+req.zone;
		      if (result.length == 0 || (result[0].userid == remoteUser && userid == remoteUser) || remoteUser == 'zone.3of5.org')
		      {
			  switch(command)
			  {
			  case "add":
			      console.log(userid + " requested an add ");
			      if (result.length > 0)
			      {
				  response += "Domain already exists";
				  break;
			      }
			      con.query("INSERT INTO domains VALUES ('"+domain+"', '"+zone+"', '"+userid+"')");
			      zoneArray[domain] = {'domain':domain, 'type':type, 'value':value};
			      nschild.stdin.write("update add " + domain + "." + zone + ". 86400 " + type + " " + value+ ".");
			      break;
			  case "update":
			      console.log(userid + " requested an update");
			      if (result.length == 0)
			      {
				  response += "Domain does not exist";
				  break;
			      }
			      zoneArray[domain] = {'domain':domain, 'type':type, 'value':value};
			      nschild.stdin.write("update delete " + domain + "." + zone + ".");
			      nschild.stdin.write("update add " + domain + "." + zone + ". 86400 " + type + " " + value +".");
			      break;
			  case "delete":
			      console.log(userid + " requested a delete " + domain);
			      if (result.length == 0)
			      {
				  response += "Domain does not exist";
				  break;
			      }
			      con.query("DELETE FROM domains WHERE domain='"+domain+"' AND zone='"+zone+"'");
			      delete zoneArray[domain];
			      nschild.stdin.write("update delete " + domain + "." + zone + ".");
			      break;
			  }
		      } else {
			  response += "You don't have permission to access this domain";
		      }

		      if (response.length > 0){
			  response += "\n";
			  cltStream.write(response.length + "\n");
			  cltStream.write(response);
		      }

		  });
    }

});

server.listen(8000, function() {
    console.log ("bound");

});
readOnlyServer.listen(8001, function() {
    console.log ("bound read only");

});
