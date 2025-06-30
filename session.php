<?php
@session_start();
if(isset($_SESSION['empid']))
{   
	//10 Minutes  Logout Sesssion 30 Minutes 1800
	if(time()-$_SESSION["login_time_stamp"] >600) 
    {
        session_unset();
        session_destroy();
        header("Location:index.php");
    }
    
}
 else 
{
echo '<script type="text/javascript">window.location.href="index.php";</script>"';
}
?>