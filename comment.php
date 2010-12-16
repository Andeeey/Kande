<?php
	require './db.php';
	session_start();
	if (!empty($_SESSION['key']))
		if (isset($_GET['rid']) && !empty($_GET['rid']) &&
			isset($_POST['comment']) && !empty($_POST['comment']))
			if (connectToDB())
				if (isset($_GET['cid']) && !empty($_GET['cid'])) {
					$s = verifySessionKey($_SESSION['key']);
					$c = getCommentByCID($_GET['cid']);
					// hvis bruker er eier eller har auth == 3
					if ((substr($_SESSION['key'], 32) == $c['uid']) || ($s['auth'] == 3))
						modifyCommentByCID($_GET['cid'], $_POST['comment']);
				} else {
					$s = verifySessionKey($_SESSION['key']);
					if ($s)
						addComment($_GET['rid'], substr($_SESSION['key'], 32), $_POST['comment']);
				}
	header('Location:item.php?id='.$_GET['rid']);
?>