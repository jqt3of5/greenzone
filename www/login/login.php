<html>
    <head>
    </head>
<body>
    <center>
    <h1>You made it to the login!</h1><br>
    <?php if (isset($_GET['error'])){ echo $_GET['error'] . '<br>'; } ?>
    <form action='login-verify.php' method = 'post'>
         User Name: <input type='text' name='userid'><br>
         Password: <input type='password' name='password'><br>
         <input type='submit' value='Submit'>
    </form>
    <br><br>
    Dont have an account? <a href="signup.php" >Click Here</a>
			
    </center>
</body>
</html>
