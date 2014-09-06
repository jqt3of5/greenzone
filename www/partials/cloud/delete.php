<?php require_once "../../login/accesscontrol.php";

$socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"));
while (!socket_connect($socket, "127.0.0.1", "1337"))
  {
    $err = socket_last_error($socket);
    if ($err == 115 || $err == 114)
      {
        if ((time() - $time) >= $timeout)
	  {
	    socket_close($socket);
	    die("Connection timed out.\n");
	  }
        sleep(1);
        continue;
      }
    die(socket_strerror($err) . "\n");
  }
$username = $_SESSION['username'];
$path = $_GET['path'];
$buffer = "{\"command\":\"delete\", \"username\" : \"$username\", \"filepath\" : \"$path\"}";

$sentBytes = socket_write($socket, $buffer);
if ($sentBytes != strlen($buffer))
  {
    echo "failed! $sentBytes " . strlen($buffer);
    exit;
  }
$buffer = "";
$temp = "";
while ($temp = socket_read($socket, 1000))
  {
    $buffer = $buffer . $temp;
  }
echo $buffer;

?>