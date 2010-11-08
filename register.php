<?php
	require_once('recaptchalib.php');
	$privatekey = "";
	$resp = recaptcha_check_answer ($privatekey,
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);

	if (!$resp->is_valid) {
		// What happens when the CAPTCHA was entered incorrectly
		die ("reCAPTCHAen ble ikke skrevet inn riktig. G� tilbake og pr�v igjen. " .
			"(reCAPTCHA sier: " . $resp->error . ")");
	} else {
		// Your code here to handle a successful verification
	}
?>