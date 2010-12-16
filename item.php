<?php 
	include './header.php';
?>
	<a class="skiplink" href="#userdata" accesskey="3">Hopp til innlogging</a>
	<hr/>
	<section>
		<?php
			include './resource.php';
			include './db.php';
			
			// hvis vi har en id, vis ressurs
			if (isset($_GET['id']) && !empty($_GET['id'])) {
				if (connectToDB()) {
					$res = getResourceByID($_GET['id']);
					$res->displayFull();
					if (countCommentsByRID($res->id) == 1)
						$commentString = '1 kommentar';
					else
						$commentString = countCommentsByRID($res->id).' kommentarer';

					$comments = getCommentsByRID($res->id, 0, 1000, true);
					$commentList = '';
					foreach ($comments as $com) {
						$commentList .= '<div class="comment"><div class="commentdata"><p><a href="user.php?uid='.urlencode($com['uid']).'">'.$com['uid'].'</a> for '.$res->time_since($com['timecreated']).' siden:';
						session_start();
						$s = verifySessionKey($_SESSION['key']);
						if (($s['user'] == $com['uid']) || ($s['auth'] == 3))
							$commentList .= ' (<a href="item.php?id='.$res->id.'&cid='.$com['cid'].'">rediger</a> | <a href="delete.php?cid='.$com['cid'].'">slett</a>)';
						$commentList .= '</p></div><div class="commentcontent">'.$res->textReplace($com['comment']).'</div></div><hr/>';
					}
					
					// hent kommentartekst hvis vi har bedt om redigering
					$edit = '';
					$add = '';
					if (isset($_GET['cid']) && !empty($_GET['cid']) && !empty($_SESSION['key'])) {
						$s = verifySessionKey($_SESSION['key']);
						$c = getCommentByCID($_GET['cid']);
						if (($_SESSION['name'] == $c['uid']) || ($s['auth'] == 3)) {
							$edit = $c['comment'];
							$add = '&cid='.$c['cid'];
						}
					}
						
					echo '<div class="commentborder">'
					.'<div class="comments">'
					.'<h4 class="commentheader">'.$commentString.'</h4>'
					.$commentList
					// sett inn kommentartekst i kommentarboks hvis vi har bedt om redigering
					.'<form class="newcomment" action="comment.php?rid='.$res->id.$add.'" method="post">'
					.'<h4>Legg inn en kommentar</h4>'
					.'<textarea name="comment" rows="10">'.$edit.'</textarea>'
					.'<input type="submit" value="Svar" />'
					.'</form>'
					.'</div>'		//comments
					.'</div>'		//commentborder
					.'<hr/>';
				}
			} else {
				header('Location:.');
				die;
			}
		?>
	</section>
	<aside>
		<div id="contribute"><script>document.write('<a href="javascript:checkLogin(\'edit.php\')">Legg til en ressurs</a>');</script><noscript><a href="edit.php">Legg til en ressurs</a></noscript></div>
		<?php include './usermeta.php'; ?>
		<hr/>
		<p>Hvis du har et svar eller tilføyelser, legg inn en kommentar under.</p>
	</aside>
<?php 
	include './footer.php';
?>