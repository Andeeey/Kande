<?php
	require_once './db.php';

	// js_enabled/browser_check blir satt til 1 dersom javascript er skrudd av, fra skjult inputfelt
	$browser_check = 0;
	if(isset($_POST['js_enabled']))
		$browser_check = $_POST['js_enabled'];
	
	if ($browser_check == 0) {
		include './header.php';
		echo '<section id="error">';
	}
	
	if (isset($_POST['username']) && !empty($_POST['username']))
		$name = $_POST['username'];
	else {
		if ($browser_check == 0)
			echo 'Du må fylle inn brukernavn.<br/>';
		else
			echo "$('message').innerHTML = 'Du må fylle inn brukernavn.'; $('message').style.color = '#d50'"; // evalueres av ajax-funksjonen når den returneres
	}
	
	if (isset($_POST['password']) && !empty($_POST['password']))
		$pass = $_POST['password'];
	else {
		if ($browser_check == 0)
			echo 'Du må fylle inn passord.<br/>';
		else	
			echo "$('message').innerHTML = 'Du må fylle inn passord.'; $('message').style.color = '#d50'";
	}
	
	if (isset($name) && isset($pass)) {
		if (connectToDB()) {
			$response = verifyUser($name, $pass, false);
			if (!$response) {
				if ($browser_check == 0)
					echo 'Feil ved pålogging. Har du fylt ut riktig?<br/>';
				else				
					echo "$('message').innerHTML = 'Feil ved pålogging. Har du fylt ut riktig?'; $('message').style.color = '#d50'"; 
			} else {
				session_start();
				session_regenerate_id();
				$_SESSION['key'] = $response['sessionKey'];
				// hvis brukeren ville gå til redigeringsskjerm, redirect dit, ellers tilbake
				if ($_GET['intent'] == 'edit.php') {
					if ($browser_check == 0)
						header('Location:edit.php');
					else
						echo "window.location = 'edit.php'";
				} else {
					if ($browser_check == 0)
						header('Location:'.$_SERVER['HTTP_REFERER']);
					else
						echo "window.location = '".$_SERVER['HTTP_REFERER']."'";
				}
			}
		}
	} else {
		echo 'Gå tilbake og prøv igjen.';
	}
		
	if ($browser_check == 0) {
		echo '</section>';
		include './footer.php';
	}
?>