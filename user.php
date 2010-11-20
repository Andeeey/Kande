<?php 
	include './header.php';
?>
	<section>
		<?php
			session_start();
			include './resource.php';
			include './db.php';
	
			// hvis vi har getdata med direkte identifikasjon av bruker 
			if (isset($_GET['uid']) && !empty($_GET['uid']))
				if (connectToDB()) {
					echo '<div class="user">';
					echo '<h3>'.countResourceByUID($_GET['uid']).' innlegg av '.$_GET['uid'].'</h3>';
					$resources = getResourcesByUID($_GET['uid'], 0, 100, 'timecreated', true, array());
					foreach ($resources as $res)
						$res->display();
					echo '</div><div class="user">';
					$r = new ResourceClass(0,'','',''); // for � f� tilgang p� time_since
					$comments = getCommentsByUID($_GET['uid'], 0, 100, false);
					$commentList = '';
					foreach ($comments as $com) {
						$commentList .= '<div class="comment"><div class="commentdata"><p><a href="user.php?uid='.urlencode($com['uid']).'">'.$com['uid'].'</a> for '.$r->time_since($com['timecreated']).' siden:';
						$s = verifyUser($_SESSION['name'], $_SESSION['pass'], false);
						if (($s['user'] == $com['uid']) || ($s['auth'] == 3))
							$commentList .= ' (<a href="edit.php?cid='.urlencode($com['cid']).'">rediger</a> | <a href="delete.php?cid='.urlencode($com['cid']).'">slett</a>)';
						$commentList .= '</p></div><div class="commentcontent">'.$r->textReplace($com['comment']).'</div></div>';
					}
					echo '<h3 class="commentheader">'.countCommentsByUID($_GET['uid']).' kommentarer av '.$_GET['uid'].'</h3>';
					echo $commentList;
					echo '</div>';
				}
		?>
	</section>
	<aside>
		<div id="contribute"><script>document.write('<a href="javascript:checkLogin(\'edit.php\')">Legg til en ressurs</a>');</script><noscript><a href="edit.php">Legg til en ressurs</a></noscript></div>
		<?php include './usermeta.php'; ?>
	</aside>
<?php 
	include './footer.php';
?>