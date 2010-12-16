<?php
	session_start();
	include './resource.php';
	include './db.php';

	// hvis vi har getdata med direkte identifikasjon av ressurs 
	if (isset($_GET['id']) && !empty($_GET['id']))
		$id = $_GET['id'];
	
	// har vi postdata fra edit-skjermen?
	if (isset($_POST['name']) && !empty($_POST['name']) && 
		isset($_POST['desc']) && !empty($_POST['desc']) &&
		isset($_POST['tags']) && !empty($_POST['tags'])) {
		// skriv tags, separert av komma, mellomrom til array
		$rawtags = explode(',', $_POST['tags']);
		$tags = array();
		foreach ($rawtags as $rawtag) {
			$tag = trim($rawtag);
			if ($tag != '' && array_search($tag, $tags) === false)
			// hvis ikke ikke bare mellomrom eller tom streng og ikke allerede skrevet inn
				array_push($tags, $tag);
				// aksepter tag og fjern mellomrom før og etter
			}

		if (connectToDB()) {
			//sjekk først at bruker er innlogget
			if (!verifySessionKey($_SESSION['key'])) {
				header('Location:.');
				die;				
			} else {
				// hvis vi har getdata OG har postdata kommer vi fra redigering av eksisterende ressurs
				if (isset($id)) {
					$res = getResourceByID($id);
					$res->name = $_POST['name'];
					$res->url = $_POST['url'];
					$res->description = $_POST['desc'];
					$res->tags = $tags;
					modifyResourceByID($id, $res);
				} else {
					// opprett objekt				
					$res = new ResourceClass(0, $_POST['name'], $_POST['url'], $_POST['desc'], $tags, 0, 0, substr($_SESSION['key'], 32));	
					// skriv ressurs til database, få id i retur
					$id = addResource($res);
					// stem opp for ressurseier selv
					include './upvote.php';
					// hvis ingen url er satt, sett url direkte til ressursvisning og skriv modifisert objekt til database
					if (empty($_POST['url'])) {
						$res = getResourceByID($id);
						$res->url = 'item.php?id='.$id;
						modifyResourceByID($id, $res);
					}
				}
			}
		}
		
		include_once './rss.php';
	}
	header('Location:item.php?id='.$id);
?>