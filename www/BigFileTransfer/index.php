<?php
require_once "../login/accesscontrol.php";
require_once "../login/dbinfo.php";
?>

<html>
  <head>
  </head>

  <body>
    <h1>Welcome to the Big File Transfer page!</h1>
    <form action="upload.php" method="post" enctype="multipart/form-data">
      <input type="file"><br>
      <input type="submit" value="Submit">
    </form>
  </body>
</html>
