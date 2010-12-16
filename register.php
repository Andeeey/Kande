<?php
	require_once './db.php';
	require_once 'recaptchalib.php';
	
	// jsenabled/browsercheck blir satt til 1 dersom javascript er skrudd av, fra skjult inputfelt
	$browsercheck = 0;
	if(isset($_POST['jsenabled']))
		$browsercheck = $_POST['jsenabled'];
	
	if ($browsercheck == 0) {
		include './header.php';	
		echo '<section id="error">';
	}
	
	$privatekey = "6LfVfb4SAAAAANUzWY-OqyOCxExCtyAU2-pRO3ga";
	$resp = recaptcha_check_answer ($privatekey,
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);

	if (!$resp->is_valid) {
		if ($browsercheck == 0)
			echo 'CAPTCHAen ble ikke besvart riktig.<br/>';
		else
			echo "$('message').innerHTML = 'CAPTCHAen ble ikke besvart riktig. Prøv igjen.'; $('message').style.color = '#d50'"; // evalueres av ajax-funksjonen når den returneres
	} else {
		if (isset($_POST['newusername']) && !empty($_POST['newusername']))
			$name = $_POST['newusername'];
		else {
			if ($browsercheck == 0)
				echo 'Du må fylle inn brukernavn.<br/>';
			else
				echo "$('message').innerHTML = 'Du må fylle inn brukernavn.'; $('message').style.color = '#d50'";
		}
		
		if (isset($_POST['newpassword']) && !empty($_POST['newpassword']))
			$pass = $_POST['newpassword'];
		else {
			if ($browsercheck == 0)
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
					session_start();
					session_regenerate_id();
					$_SESSION['key'] = $response['sessionKey'];
					// hvis brukeren ville gå til redigeringsskjerm, redirect dit, ellers tilbake til samme skjerm
					if ($_GET['intent'] == 'edit.php') {
						if ($browsercheck == 0)
							header('Location:edit.php');
						else
							echo "window.location = 'edit.php'";
					} else {
						if ($browsercheck == 0)
							header('Location:.');
						else
							echo "window.location = '".$_SERVER['HTTP_REFERER']."'";
					}
				}
			}
	}
	
	if (!isset($name) || !isset($pass) || !$resp->is_valid)
		echo 'Gå tilbake og prøv igjen.';
	
	if ($browsercheck == 0) {
		echo '</section>';
		include './footer.php';	
	}
?>