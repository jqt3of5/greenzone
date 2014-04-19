<?php
    require_once "login/accesscontrol.php";
?>
<html lang="en" >
<head>
  <meta charset="utf-8">
  <title>My HTML File</title>
  <link rel="stylesheet" href="../bower_components/bootstrap/dist/css/bootstrap.css">
  <link rel="stylesheet" href="css/app.css">
  
  <script src="bower_components/angular/angular.js"></script>
  <script src="bower_components/angular-route/angular-route.js"></script>
  <script src="js/controllers.js"></script>
</head>
<body ng-app="toddAtHomeApp">
	<table id="header">
		<tr>
			<td id="leftHeader">
				<h1>TODD @ HOME</h1>
				<p> A place for web development</p>
			</td>
			<td id="rightHeader">
      <?php 
	  echo "<a href='login/login.php'>$_SESSION[username]</a> | <a href='login/logout.php'>Sign Out</a>";
      ?>
	
			</td>
		</tr>
	</table>
	<table id="main">
		<tr>
			<td id="menu" ng-controller="MenuController">
				<div class="menuItem" ng-click="navigateToSubTab(item.routeArg)" ng-repeat="item in menuItems">{{item.header}}</div>
			</td>
			
			<td ng-view id="content">
			</td>
			<td id="rightMargin">
			</td>
		</tr>
	</table>

</body>
</html>
