<?php 
	include './header.php';
?>
	<a class="skiplink" href="#userdata" accesskey="3">Hopp til innlogging</a>
	<hr/>
	<section>
		<?php
			session_start();
			include './resource.php';
			include './db.php';
			
			// hvis vi har getdata med direkte identifikasjon av ressurs 
			if (isset($_GET['id']) && !empty($_GET['id']))
				if (connectToDB())
					$res = getResourceByID($_GET['id']);
			
		?>
			<h3>Legg inn en ny ressurs</h3>
			<form class="newresource" action="newitem.php<?php if (isset($res->id)) echo '?id='.$res->id; ?>" onsubmit="return checkFields(this)" method="post">
				<h4>Tittel</h4>
				<span id="namespan">En beskrivende tittel på ditt bidrag eller din forespørsel.</span><br/>
				<input class="textbox" type="text" id="name" name="name" value="<?php if (isset($res->name)) echo $res->name; ?>"  onkeyup="searchForTags(this.value, taglist)" maxlength="256" />
				
				<h4>URL (valgfritt)</h4>
				<span id="urlspan">Hvis dette er en ekstern ressurs, legg inn full adresse (med http) til det du vil dele.</span><br/>
				<input class="textbox" type="text" id="url" name="url" value="<?php if (isset($res->url)) echo $res->url; ?>" maxlength="256" /><br />
				
				<script>var taglist = getTags()</script>
				<h4>Beskrivelse eller innhold</h4>
				<span id="descspan">Gi en tydelig beskrivelse av ressursen, gi oss dine beste tips, eller dine vanskeligste spørsmål.</span><br/>
				<textarea name="desc" id="desc" rows="20" onkeyup="searchForTags(this.value, taglist)"><?php if (isset($res->description)) echo $res->description; ?></textarea>	
				
				<h4>Tags</h4>
				<span id="tagspan">Skriv tags separert av komma, eller legg til fra lista under. Vi gjør vårt beste med å hjelpe deg å velge.</span><br/>
				<input class="textbox" type="text" id="tags" name="tags" value="<?php if (isset($res->tags)) echo implode(', ',$res->tags); ?>" onkeyup="replaceDuplicateTags(taglist)" maxlength="256" /><br/>
				<?php
					if (connectToDB()) {
						// hent tags
						$tags = getAllTags();
						$tagnames = array();
						$tagscores = array();
						// tags ligger som 2-dimensjonal array med tags[0] = tagnavn og array[1] = hyppighet
						foreach ($tags as $tag) {
							$tagnames[] = $tag[0];
							$tagscores[] = $tag[1];
						}
						// sorterer tagnames etter synkende tagscores
						array_multisort($tagscores, SORT_DESC, $tagnames);
						// vi trenger bare de første 150
						$tagnames = array_slice($tagnames, 0, 150);
						if (isset($tagnames))
							foreach ($tagnames as $n => $tag) {
								echo '<a href="javascript:addTag(\''.$tag.'\')">'.$tag.'</a>';
								if ($n < count($tagnames)-1)
									echo ', ';
							}
					}
				?>
				<p><input type="submit" value="Ferdig!" /></p>
			</form>
			<hr/>
	</section>
	<aside>
		<?php include './usermeta.php'; ?>
		<hr/>
		<p>Ressursene du deler kan være alt fra nettsider med programmerings- eller designmateriale, til interessant kildekode, kodebiblioteker, eller digitale lærebøker.</p>
		<p>En annen mulighet er å skrive inn kodeeksempler direkte, som andre kan ha nytte av. Eller du kan stille et spørsmål, og få svar fra andre brukere.</p>
		<p>Du kan bruke visse tegn for å formattere teksten. To krøllalfa <code>@@før og etter en tekst@@</code> gir deg preformattert tekst, kjekt for å skrive inn kodeeksempler. URL'er som begynner med <code>http://</code> eller <code>https://</code> blir automatisk til lenker, eller du kan skrive <code>[Lenketekst|http://url]</code> for å lage lenker med en annen tekst.</p>
	</aside>
<?php 
	include './footer.php';
?>