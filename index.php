<?php
	include './header.php';
?>
	<a class="skiplink" href="#results" accesskey="2">Hopp over navigasjon</a>
	<a class="skiplink" href="#userdata" accesskey="3">Hopp til innlogging</a>
	<hr/>
<?php
	include './resource.php';
	include './db.php';
	session_start();

	// viser mest populære tags på forsiden for filtering
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
		// vi trenger bare de første 35
		$tagnames = array_slice($tagnames, 0, 35);
		echo '<ul id="fronttags" class="tags">';
		echo '<li id="poptags" title="Tags er merkelapper som klassifiserer ressursene. Klikk på tags for å finne mer innen samme tema.">Populære tags: </li>';
		// for hver (sorterte) tag, skriv tag
		foreach ($tagnames as $n => $tag) {
			echo '<li><script>document.write(\'<a class="tag" href="javascript:search(searchDefault(), \\\'&amp;tags[]='.urlencode($tag).'\\\')">'.str_replace(' ','&nbsp;',$tag).'</a>\');</script><noscript><a class="tag" href="index.php?tags[]='.urlencode($tag).'">'.str_replace(' ','&nbsp;',$tag).'</a></noscript></li>';
			if ($n < count($tagnames)-1)
				echo ' ';
		}
		echo '</ul>';
	}
?>
	<section>
		<nav>
			<ul id="navul">
				<li class="a" title="Sorterer ressurser etter en kombinasjon av alder og poeng">
					<a href="index.php?sort=hot">Heiteste</a>
				</li>
				<li class="a" title="Sorterer ressurser etter alder">
					<a href="index.php?sort=new">Nyeste</a>
				</li>
				<li class="a" title="Sorterer ressurser etter poeng">
					<a href="index.php?sort=score">Beste</a>
				</li>
				<li>
					<form name="search" action="index.php" method="get">
						<input class="textbox" type="text" name="q" id="q" value="Søk..." onkeyup="clearTimeout(stimer);stimer = setTimeout('search(document.search.q.value, \'\')',500)" onfocus="this.value = ''" onblur="if (this.value == '') this.value = 'Søk...'" title="Skriv inn søkeord for å finne ressurser eller tags" accesskey="4" />
					</form>
				</li>
			</ul>
		</nav>
		<hr/>
		<div id="results">
		<?php 
			// livesearch inneholder visning av ressurser etter sortering, tags og søkestreng
			include './livesearch.php';
		?>
		</div>
		<script>
			AJAXify();
		</script>
	</section>
	<aside>
		<div id="contribute" title="...eller still et spørsmål!"><script>document.write('<a href="javascript:checkLogin(\'edit.php\')">Legg til en ressurs</a>');</script><noscript><a href="edit.php">Legg til en ressurs</a></noscript></div>
		<?php include './usermeta.php'; ?>
		<hr/>
		<p><strong>Kande</strong> hjelper deg å finne de beste nettbaserte ressursene innen programmering, nettutvikling og beslektede fag.</p>
		<p><strong>Del</strong> dine beste <strong>kilder</strong>, eller egen <strong>kode</strong> som andre kan bruke. Still <strong>spørsmål</strong> og få <strong>svar</strong>. Kande-samfunnet stemmer fram de beste bidragene og diskuterer muligheter.</p>
	</aside>
<?php 
	include './footer.php';
?>