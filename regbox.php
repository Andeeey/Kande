		<h3>Du må være registrert eller logget inn for å bidra</h3>
		<p id="message">Registrering på <strong>Kande</strong> er kjapt og enkelt. Du trenger kun et brukernavn og passord, og vise at du er menneske.</p>
		<form id="register" action="register.php<?php echo '?intent='.$_GET['intent']; ?>" onsubmit="return checkRegStatus(this, '<?php echo '?intent='.$_GET['intent']; ?>')" method="post">
			<h4>Registrer deg</h4>
			<label for="newusername">Brukernavn </label><input id="newusername" class="textbox" type="text" name="newusername" maxlength="32" tabindex="0" />
			<label for="newpassword">Passord </label><input id="newpassword" class="textbox" type="password" name="newpassword" maxlength="32" tabindex="1" />
			<div id="recaptcha">
				<noscript>
					<iframe src="http://www.google.com/recaptcha/api/noscript?k=6LfVfb4SAAAAAPFjUH67cZpQrul1Wj_gnDXJdZ2O" width="350" height="270" frameborder="0"></iframe>
					<textarea name="recaptcha_challenge_field" rows="2" cols="40"></textarea>
					<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
				</noscript>
			</div>
			<input type="submit" value="Registrer deg" />
		</form>
		<form id="login" action="login.php<?php echo '?intent='.$_GET['intent']; ?>" onsubmit="return checkLogStatus(this, '<?php echo '?intent='.$_GET['intent']; ?>')" method="post">
			<h4>...eller logg inn</h4>
			<label for="username">Brukernavn </label><input id="username" class="textbox" type="text" name="username" maxlength="32" tabindex="3" />
			<label for="password">Passord </label><input id="password" class="textbox" type="password" name="password" maxlength="32" tabindex="4" />
			<input type="submit" value="Logg inn" tabindex="5" />
		</form>
