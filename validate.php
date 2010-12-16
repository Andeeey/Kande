<?php
	include './db.php';
	session_start();
	if (connectToDB())
		if (verifySessionKey($_SESSION['key']))
			echo 'ok';
?>