<?php
	include './db.php';
	session_start();
	if (connectToDB())
		if (verifySessionKey($_SESSION['key'])) {
			mail('post.kande@gmail.com', 
				'[Rapportert ressurs] rid='.$_GET['id'], 
				'<a href="http://kande.dyndns.org/item.php?id='.$_GET['id'].'">Denne ressursen</a> er rapportert av <a href="'.substr($_SESSION['key'], 32).'">'.substr($_SESSION['key'], 32).'</a>.', 
				'From: post.kande@gmail.com');
	}
	//header('Location:'.$_SERVER['HTTP_REFERER']);
?>