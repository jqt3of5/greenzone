<?php
//require_once "../login/accesscontrol.php";
if (!session_start())
  {
    echo "Fail!";
    exit;
  }
require_once "../login/dbinfo.php";
?>

<html>
  <head>
    <script src="FileList.js"></script>  
    <script>
	window.onload=function(){
		InitFileList();
	};
    </script>
  </head>

  <body style="background: grey;">

    <div style="text-align: center; background: grey">
      <div id='header'  style="text-align: right;"> 
	<?php
	   if (isset($_SESSION['username']))
	   {
	       echo "<a href='/login/account.php' id='accountButton' class='button'>$_SESSION[username]</a> ";
	       echo "<a href='/login/logout.php' id='signoutButton' class='button'>Logout</a> ";
	   } else {
	       echo "<a href='/login/signup.php' id='signUpButton' class='button'>Sign Up</a> ";
	       echo "<a href='/login/login.php' id='loginButton' class='button'>Login</a> ";
	   }
	?>
      </div>
      <div style="margin-left: auto; margin-right: auto; width: 400px; background: #00aa00; overflow: hidden; border:3px solid; border-radius:20px;">
	<h1>Big File Transfer</h1><br>
	When E-Mail isn\'t enough. Use this utility to transfer large files. <br><br>
        Drag and drop files into the region below
	<div id='uploadRegion' style="background: #00ff00; height: 100px; width: 70%; margin-left: auto; margin-right: auto; border-radius: 15px;"> 
      </div>

      <p>Upload progress: <progress id="uploadprogress" min="0" max="100" value="0">0</progress></p>

      <div id="fileListDiv" style="background: darkgrey;">
      	    <?php

	       if (isset($_SESSION['userid']))
	       {
		   $con = mysql_connect($dbhost, $dbuser, $dbpwd) or die("Connection Failed");
		   mysql_select_db($db, $con);
	       
		   $result = mysql_query("SELECT guid, fileName FROM files WHERE userid='$_SESSION[userid]'");
		   $fileItemJson = "{";
		   while ($row = mysql_fetch_array($result))
		   {
			$fileItemJson = $fileItemJson . "\"$row[guid]\":{name:\"$row[fileName]\", size:0, date: \"1/1/1970\"},";
			echo "<a class='fileItem' id='$row[guid]' href='/BigFileTransfer/download.php?guid=$row[guid]'>$row[fileName]</a><br>\n";
		   }
		   $fileItemJson = $fileItemJson . "}";
		   mysql_close($con); 
		   echo "<script> fileItemInfos = $fileItemJson </script>\n";
	       }
	       ?>
	       <div id="fileInfoView" style="display: none;  position: absolute; border: solid black 1px; background: white">
	           <div id="fileInfoName"></div><br>
	           <div id="fileInfoSize"></div><br>
		   <a id="fileDownload">Download</a> <a id="fileShare">Share</a> <a id="fileDelete">Delete</a><br>
	       </div>
	</div>
      </div>
    <script>
var progress = document.getElementById('uploadprogress');
var fileDropArea = document.getElementById('uploadRegion');
function readFiles(files)
{
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'upload.php');
    xhr.onload = function(){
	progress.value = progress.innerHTML = 100;
    };
    
    xhr.upload.onprogress = function(event)
    {
					if (event.lengthComputable)
					{
	    			var complete = (event.loaded / event.total * 100 | 0);
	    			progress.value = progress.innerHTML = complete;
					}
    };

    xhr.onreadystatechange = function(event)
    {
				if (event.target.readyState === 4 && event.target.status === 200)
				{
				    document.getElementById("filesTable").innerHTML += event.target.responseText;
				}
    };
    var formData = new FormData();
    for (var i = 0; i < files.length; i++)
    {
						formData.append('files[]', files[i]);
    }
    xhr.send(formData);
}

		fileDropArea.ondragover = function(e){
		    return false;
		};
		
		fileDropArea.ondrop = function(e){
		    e.preventDefault();
		    readFiles(e.dataTransfer.files);
		};

    </script>
  </body>
</html>
