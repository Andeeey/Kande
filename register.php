<?php
	require_once './db.php';
	require_once 'recaptchalib.php';
	
	// js_disabled/browser_check blir satt til 1 dersom javascript er skrudd av, fra skjult inputfelt
	$browser_check = 0;
	if(isset($_POST['js_disabled']))
		$browser_check = $_POST['js_disabled'];
	
	if ($browser_check == 1) {
		include './header.php';	
		echo '<section id="error">';
	}
	
	$privatekey = "6LfVfb4SAAAAANUzWY-OqyOCxExCtyAU2-pRO3ga";
	$resp = recaptcha_check_answer ($privatekey,
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);

	if (!$resp->is_valid) {
		if ($browser_check == 1)
			echo 'CAPTCHAen ble ikke besvart riktig.<br/>';
		else
			echo "$('message').innerHTML = 'CAPTCHAen ble ikke besvart riktig. Prøv igjen.'; $('message').style.color = '#d50'"; // evalueres av ajax-funksjonen når den returneres
	} else {
		if (isset($_POST['newusername']) && !empty($_POST['newusername']))
			$name = $_POST['newusername'];
		else {
			if ($browser_check == 1)
				echo 'Du må fylle inn brukernavn.<br/>';
			else
				echo "$('message').innerHTML = 'Du må fylle inn brukernavn.'; $('message').style.color = '#d50'";
		}
		
		if (isset($_POST['newpassword']) && !empty($_POST['newpassword']))
			$pass = $_POST['newpassword'];
		else {
			if ($browser_check == 1)
				echo 'Du må fylle inn passord.<br/>';
			else
				echo "$('message').innerHTML = 'Du må fylle inn passord.'; $('message').style.color = '#d50'";
		}
		
		if (isset($name) && isset($pass))
			if (connectToDB()) {
				$ok = addUser($name, $pass, '', false);
				if (!$ok)
					echo "$('message').innerHTML = 'Feil ved registrering av bruker. Kanskje du vil prøve igjen?'; $('message').style.color = '#d50'";
				else {
					$response = verifyUser($name, $pass, false);
					session_id($response['sessionKey']);
					session_start();
					$_SESSION['name'] = $name;
					$_SESSION['pass'] = $pass;
					// hvis brukeren ville gå til redigeringsskjerm, redirect dit, ellers tilbake til samme skjerm
					if ($_GET['intent'] == 'edit.php') {
						if ($browser_check == 1)
							header('Location:edit.php');
						else
							echo "window.location = 'edit.php'";
					} else {
						if ($browser_check == 1)
							header('Location:'.$_SERVER['HTTP_REFERER']);
						else
							echo "window.location = '".$_SERVER['HTTP_REFERER']."'";
					}
				}
			}
	}
	
	if (!isset($name) || !isset($pass) || !$resp->is_valid)
		echo 'Gå tilbake og prøv igjen.';
	
	if ($browser_check == 1) {
		echo '</section>';
		include './footer.php';	
	}
?>