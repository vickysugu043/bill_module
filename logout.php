<?php
	session_start();
	unset($_SESSION["empid"]);
	unset($_SESSION["empname"]);
	unset($_SESSION["empwrkunit"]);
	header('location:index.php');
?>