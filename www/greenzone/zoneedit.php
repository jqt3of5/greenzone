<?php

require_once "accesscontrol.php";
require_once "dbinfo.php";

if (empty($_GET['json']) && (empty($_GET['command']) || empty($_GET['domain']) || empty($_GET['zone'])))
  {
    exit;
  }

$zoneUser = 'admin';
$zonePwd = 'password';
$zoneHost = 'localhost';

//Connect to the zone zerver
$streamContext = stream_context_create();
stream_context_set_option($streamContext, 'ssl', 'local_cert', '/home/jqt3of5/Documents/zoneserver/ssl/zonecertkey.pem');
$tlscon = stream_socket_client('tls://' .$zoneHost . ':8000', $error, $errorString, 3, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $streamContext);
    
if (!$tlscon)
  {
    echo "Could not connect to the zone server";
    exit;
  }

$res = fgets($tlscon);
    
if (strpos($res, "USER: FAIL"))
  {
    echo "Zone server connection: " . $res . " ";
    exit;
  }
if (!empty($_GET['json']))
  {
      fwrite($tlscon, "{\"userid\":\"$_SESSION[userid]\",\"reqs\":$_GET[json]}");
  }else {
      fwrite($tlscon, "{\"userid\":\"$_SESSION[userid]\", \"reqs\":[{\"command\":\"$_GET[command]\", \"domain\":\"$_GET[domain]\", \"zone\":\"$_GET[zone]\", \"type\":\"$_GET[type]\", \"value\":\"$_GET[value]\"}]}" );
  }

$count = fgets($tlscon);
$res = fread($tlscon, $count);

fclose($tlscon);
echo $res;
exit; 

?>