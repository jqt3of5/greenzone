<?php   
if (!session_start())
  {
    echo "Fail!";
    exit;
  }

if (!isset($_SESSION['email']) || !isset($_SESSION['userid'])){
	Header("Location: /login/login.php?error=not logged in");
	exit;
}
?>
