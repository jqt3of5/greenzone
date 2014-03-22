<?php 
require_once "dbinfo.php";
  
if (!session_start())
  {
    echo "Fail!";
    exit;
  }
if (isset($_POST['ticket'])
  {
      $ticket = base64_decode($_POST['ticket']);
      //decode ticket into: user, expiration, ticket hash
      sscanf($ticket, "user=%s,expires=%d,hash=%s", $user, $expires, $ticketHash);
      //verify expiration
      if (time(0) > expiration)
      {     
	Header("Location: /login/login.php?error=Ticket has expired");
	exit;
      }
      $con = mysql_connect($dbhost, $dbuser, $dbpwd);
      if (! $con)
      {
      	    die ('Could not connect: ' . mysql_error());
      }
		
	mysql_select_db($db, $con);
	
      $result = mysql_query("SELECT user, email, password FROM users WHERE user='$_POST[userid]';");
      $row = mysql_fetch_assoc($result);
      
      //concat user+expiration+passwordHash. Basically keyed hash
      ssprintf($prehash, "user=%s,expires=%d,hash=%s", $user, $expires, $row['password']);

      //hash result, compare ticket hash to result hash
      //if same, fillout session
      if ($ticketHash == hash($prehash))
      {
	$_SESSION['email'] = $row['email'];
	$_SESSION['userid'] = $user;	
      }
  }
if (!isset($_SESSION['email']) || !isset($_SESSION['userid'])){
	Header("Location: /login/login.php?error=not logged in");
	exit;
}
?>
