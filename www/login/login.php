<html>
    <head>
		<link rel="stylesheet" href="../css/login.css">
    </head>
<body>
    <div id="header">
		<h1> TODD @ HOME </h1>
   <p> A Place for Web Development</p>
	</div>
	<div id="login">
		<form action='login-verify.php' method = 'post'>
			User Name: <input type='text' name='username'><br>
			Password: <input type='password' name='password'><br>
			<input type='submit' value='Submit'>
		</form>
		<?php if (isset($_GET['error'])){ echo $_GET['error']; } ?>
	</div>
    <br><br>
    <div id="footer">Dont have an account? <a href="signup.php" >Click Here</a> </div>
			    
</body>
</html>
