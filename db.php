<?php

///////////////////////// OPPSETT /////////////////////////

// Databaseoppsett:

define("DB_NAME","kande");

define("DB_USER","");

define("DB_PASS","");

define("DB_HOST","localhost");

///////////////////////////////////////////////////////////

// Kobler til database med verdier satt �verst i db.php.
// Hvis en database ikke eksisterer vil man f� sp�rsm�l om � opprette en.
// Hvis den ikke oppn�r kontakt med MySQL-serveren vil funksjonen kaste en RuntimeException.
function connectToDB()
{
	$result = connectDirectToDB(DB_NAME, DB_USER, DB_PASS, DB_HOST);
	
	if(!$result)
		throw new RuntimeException("Kunne ikke koble til database-server");
	return $result;
}

// Putter en ressurs inn i databasen.
// ID og Time blir satt automatisk. Dataen fra ResourceClass-objektet vil derfor ignoreres.
// Funksjonen returnerer ressursens nye ID fra databasen.
function addResource($resObject)
{
	$sql = "INSERT INTO `".DB_NAME."`.`resources` (
		`rid` ,
		`title` ,
		`link` ,
		`author` ,
		`timecreated` ,
		`rating` ,
		`description` ,
		`tags`
		)
		VALUES (
		NULL , '".$resObject->name."', '".$resObject->url."', '".$resObject->owner."',
		".time()." , '".$resObject->score."', '".$resObject->description."', '".dcTagArrayToString($resObject->tags)."'
		)";

	$result = mysql_query($sql);

	if($result)
		$result = mysql_insert_id();
		
	return $result;
}

// Returnerer en array med "ResourceClass"-objekter.
// TO INT-er definerer startpunkt og antall som skal leses inn.
// En STRING brukes for � bestemme hva man sorterer p�. Kan velge mellom "rating", "timecreated" og flere. Bruker navn fra databasen.
// En BOOL som kan settes til true for � sortere i stigende rekkef�lge.
// En ARRAY med tags som begrenser s�ket. S�k med flere tags returnerer unionen (OR).
// En STRING med description.
function getResources($startFrom, $count, $sortBy, $ascending, $tags)
{
	$tagQuery = "";
	
	if(count($tags) > 0)
	{
		$tagQuery = "WHERE tags LIKE '%".str_replace(";", "<?SK$>", $tags[0])."%'";
		
		for($i = 1; $i < count($tags); $i++)
			$tagQuery .= " OR tags LIKE '%".str_replace(";", "<?SK$>", $tags[$i])."%'";
	}
	
	if($ascending)
		$sortDir = "ASC";
	else
		$sortDir = "DESC";
	
	$sql = "SELECT * FROM `".DB_NAME."`.`resources` ".$tagQuery." ORDER BY ".$sortBy." ".$sortDir." LIMIT ".$startFrom.", ".$count;

	$result = mysql_query($sql);
	
	if($result)
	{
		$resArray = array();
		
		while($resData = mysql_fetch_row($result))
			array_push($resArray, new ResourceClass($resData[0], $resData[1], $resData[2], $resData[6], dcTagStringToArray($resData[7]), $resData[5], $resData[4], $resData[3]));
		
		$result = $resArray;
	}
	
	return $result;
}

// Returnerer et "ResourceClass"-objekt, med description.
function getResourceByID($id)
{
	$sql = "SELECT * FROM `".DB_NAME."`.`resources` WHERE rid = ".$id;
	
	$result = mysql_query($sql);
	
	if($result)
	{
		$resData = mysql_fetch_row($result);
		
		$result = new ResourceClass($resData[0], $resData[1], $resData[2], $resData[6], dcTagStringToArray($resData[7]), $resData[5], $resData[4], $resData[3]);
	}
	
	return $result;
}

// Returnerer en array med "ResourceClass"-objekter.
function getResourcesByUID($uid, $startFrom, $count, $sortBy, $ascending, $tags)
{
	$tagQuery = "";
	
	if(count($tags) > 0)
	{
		$tagQuery = "WHERE tags LIKE '%".str_replace(";", "<?SK$>", $tags[0])."%'";
		
		for($i = 1; $i < count($tags); $i++)
			$tagQuery .= " OR tags LIKE '%".str_replace(";", "<?SK$>", $tags[$i])."%'";
		
		$tagQuery .= "AND ";
	}
	else 
		$tagQuery = "WHERE ";
	
	if($ascending)
		$sortDir = "ASC";
	else
		$sortDir = "DESC";
	
	$sql = "SELECT * FROM `".DB_NAME."`.`resources` ".$tagQuery." author = '".$uid."' ORDER BY ".$sortBy." ".$sortDir." LIMIT ".$startFrom.", ".$count;

	//echo "SQL: " . $sql . " :END";

	$result = mysql_query($sql);
	
	if($result)
	{
		$resArray = array();
		
		while($resData = mysql_fetch_row($result))
			array_push($resArray, new ResourceClass($resData[0], $resData[1], $resData[2], $resData[6], dcTagStringToArray($resData[7]), $resData[5], $resData[4], $resData[3], null));
		
		$result = $resArray;
	}
	
	return $result;
}

// Setter ny rating/score p� en bestemt ressurs.
function modifyResourceScoreByID($id, $newScore)
{
	$sql = "UPDATE `".DB_NAME."`.`resources` SET `rating` = '".$newScore."' WHERE `resources`.`rid` = ".$id;
	
	$result = mysql_query($sql);
	
	return $result;
}

// Bytter ut en eksisterende ressurs i databasen med et nytt ResourceClass-objekt.
function modifyResourceByID($id, $resObject)
{
	$sql = "UPDATE `".DB_NAME."`.`resources` SET `title` = '".$resObject->name."',
		`link` = '".$resObject->url."',
		`author` = '".$resObject->owner."',
		`rating` = '".$resObject->score."',
		`description` = '".$resObject->description."',
		`tags` = '".dcTagArrayToString($resObject->tags)."' WHERE `resources`.`rid` = ".$id;

	$result = mysql_query($sql);
	
	return $result;
}

// Fjerner ressurs fra databasen
function deleteResourceByID($id)
{
	$sql = "DELETE FROM `".DB_NAME."`.`resources` WHERE `resources`.`rid` = ".$id;
	
	$result = mysql_query($sql);
	
	return $result;
}

// Teller antall ressurser en gitt bruker har opprettet
function countResourceByUID($uid)
{
	$sql = "SELECT COUNT(*) FROM `".DB_NAME."`.`resources` WHERE author = '".$uid."'";
	
	$result = mysql_query($sql);
	
	if($result)
	{
		$resData = mysql_fetch_row($result);
		
		$result = $resData[0];
	}
	
	return $result;
}

// Returnerer en array med alle tags som er i databasen. Ingen duplikater.
function getAllTags()
{
	$sql = "SELECT tags FROM `".DB_NAME."`.`resources`";
	
	$result = mysql_query($sql);
	if($result)
	{
		$tagArray = array();
		$tagCount = array();
		
		while($resData = mysql_fetch_row($result))
			foreach(dcTagStringToArray($resData[0]) as $tag)
				if(($i = array_search($tag, $tagArray)) === false)
				{
					if($tag != "")
					{
					array_push($tagArray, $tag);
					array_push($tagCount, 1);
					}
				}
				else 
					$tagCount[$i]++;
		
		$result = array();
		
		for($i = 0; $i < count($tagArray); $i++)
			array_push($result, array($tagArray[$i], $tagCount[$i]));
	}
	
	return $result;
}

// Oppretter en ny bruker med brukernavn (UID), �nsket passord og e-post.
// Det siste boolean-parametret angir om passordet er en MD5-hash eller ikke. Hvis denne settes til TRUE, m� passordet v�re en MD5-hash skapt gjennom f.eks. Javascript. I motsatt fall skal passordet sendes som klartekst.
// Funksjonen returnerer TRUE hvis brukeren ble opprettet korrekt, eller FALSE i motsatt fall. Bortsett fra hvis brukernavnet eksisterer fra f�r. Da returneres -1.
function addUser($uid, $password, $email, $secure)
{
	$salt = dcRandomString(16, 3);
	
	if(!$secure)
			$password = md5($password);
	
	$phash = md5($salt.$password);
	
	$sql = "INSERT INTO `".DB_NAME."`.`users` (
		`uid` ,
		`auth` ,
		`passhash` ,
		`salt` ,
		`email`
		)
		VALUES (
		'".$uid."' , 0, '".$phash."', '".$salt."', '".$email."'
		)";

	$result = mysql_query($sql);
	
	if(mysql_errno() == 1062)
		$result = -1;
	
	return $result;
}

// Sjekker om en bruker kan logge inn med et gitt brukernavn og passord.
// Det siste boolean-parametret fungerer p� samme m�te som i addUser(...), og angir om passordet er en MD5-hash eller ikke.
// Funksjonen returnerer FALSE hvis brukernavnet eller passordet er feil. I motsatt fall returneres en assosiativ array med f�lgende n�kler:
// > "user": Brukernavnet (UID)
// > "auth": Et tall som angir hvilket tilgangsniv� brukeren har. Brukes for � angi moderatorer og administratorer. M� (enn s� lenge...) endres rett i databasen.
// > "sessionKey": En n�kkelstring som kan brukes sammen med verifySessionKey(...) for � holde folk innlogget. Kan plasseres i Cookie hvis de bes�kende vil bli "husket".
function verifyUser($uid, $password, $secure)
{
	$sql = "SELECT uid, auth, passhash, salt FROM `".DB_NAME."`.`users` WHERE uid = '".$uid."'";
	
	$result = mysql_query($sql);
	
	if($result)
	{
		$resData = mysql_fetch_row($result);
		
		if(!$secure)
			$password = md5($password);
		
		if($result = $resData && md5($resData[3].$password) == $resData[2])
			$result = array("user" => $resData[0], "auth" => $resData[1], "sessionKey" => md5($resData[2]).$resData[0]);
	}
	
	return $result;
}

// Gj�r samme nytte som verifyUser, men med en sessionKey i stedet for brukernavn og passord. Returnerer en assosiativ array med "user" og "auth", men ikke "sessionKey".
function verifySessionKey($sessionKey)
{
	$sessionHash = substr($sessionKey, 0, 32);
	$uid = substr($sessionKey, 32);
	
	$sql = "SELECT uid, auth, passhash FROM `".DB_NAME."`.`users` WHERE uid = '".$uid."'";
	
	$resQuery = mysql_query($sql);
	
	$resData = @mysql_fetch_row($resQuery);
	
	if($resData)
		$result = md5($resData[2]) == $sessionHash;
	
	if($result)
		$result = array("user" => $resData[0], "auth" => $resData[1]);
	
	return $result;
}

// Oppretter en ny kommentar, som tilh�rer ressursen med angitt RID, og er skrevet av brukeren med angitt UID.
// Returnerer kommentarens nye unike kommentar-id (CID).
function addComment($rid, $uid, $comment)
{
	$sql = "INSERT INTO `".DB_NAME."`.`comments` (
		`cid` ,
		`uid` ,
		`rid` ,
		`comment` ,
		`timecreated` ,
		`timemodified`
		)
		VALUES (
		NULL , '".$uid."', '".$rid."', '".$comment."', ".time().", 0
		)";
	
	$result = mysql_query($sql);
	
	if($result)
		$result = mysql_insert_id();
	
	return $result;
}

// Endrer teksten i kommentaren med angitt CID.
function modifyCommentByCID($cid, $comment)
{
	$sql = "UPDATE `".DB_NAME."`.`comments` SET `comment` = '".$comment."', `timemodified` = ".time()."
 WHERE `cid` = ".$cid;

	$result = mysql_query($sql);
	
	return $result;
}

// Sletter kommentaren med angitt CID.
function deleteCommentByCID($cid)
{
	$sql = "DELETE FROM `".DB_NAME."`.`comments` WHERE `cid` = ".$cid;
	
	$result = mysql_query($sql);
	
	return $result;
}

// Returnerer et sett med kommentarer for en angitt ressurs (RID).
// Avgrenses via "start" og "totaltantall", p� samme m�te som getResources(...).
// Settes "stigenderekkef�lge" til TRUE blir resultatet i stigende rekkef�lge.
// Resultatet sorteres etter tidspunktet kommentaren ble opprettet.
// Returnerer en array med alle kommentarene. Arrayen inneholder et sett assosiative arrays, med f�lgende n�kler:
// > "cid": Kommentar-ID
// > "rid": TIlh�rende ressurs-ID
// > "uid": Brukernavnet til den som skrev kommentaren
// > "comment": Selve kommentaren
// > "timecreated": Tidspunktet kommentaren ble opprettet via addComment(...)
// > "timemodified": Tidspunktet kommentaren sist ble modifisert via modifyCommentByCID(...). Satt til 0 hvis den aldri er modifisert.
function getCommentsByRID($rid, $startFrom, $count, $ascending)
{
	if($ascending)
		$sortDir = "ASC";
	else
		$sortDir = "DESC";
	
	$sql = "SELECT cid, uid, comment, timecreated, timemodified FROM `".DB_NAME."`.`comments` WHERE `rid` = ".$rid." ORDER BY timecreated ".$sortDir." LIMIT ".$startFrom.", ".$count;
	
	$result = mysql_query($sql);

	if($result)
	{
		$resArray = array();
		
		while($resData = mysql_fetch_row($result))
			array_push($resArray, array("cid" => $resData[0], "rid" => $rid, "uid" => $resData[1], "comment" => $resData[2], "timecreated" => $resData[3], "timemodified" => $resData[4]));
		
		$result = $resArray;
	}
	
	return $result;
}

// Identisk som getCommentsByRID(...) p� alle m�ter, bortsett fra at den returnerer alle kommentarene til en gitt bruker i stedet for til en gitt ressurs.
function getCommentsByUID($uid, $startFrom, $count, $ascending)
{
	if($ascending)
		$sortDir = "ASC";
	else
		$sortDir = "DESC";
	
	$sql = "SELECT cid, rid, comment, timecreated, timemodified FROM `".DB_NAME."`.`comments` WHERE `uid` = '".$uid."' ORDER BY timecreated ".$sortDir." LIMIT ".$startFrom.", ".$count;

	$result = mysql_query($sql);

	if($result)
	{
		$resArray = array();
		
		while($resData = mysql_fetch_row($result))
			array_push($resArray, array("cid" => $resData[0], "rid" => $resData[1], "uid" => $uid, "comment" => $resData[2], "timecreated" => $resData[3], "timemodified" => $resData[4]));
		
		$result = $resArray;
	}
	
	return $result;
}

// Returnerer en enkelt kommentar, angitt ved kommentar-ID (CID). Returnerer �n assosiativ array med samme n�kler som i getCommentsByRID(...) og getCommentsByUID(...).
function getCommentByCID($cid)
{
	$sql = "SELECT uid, rid, comment, timecreated, timemodified FROM `".DB_NAME."`.`comments` WHERE cid = ".$cid;
	
	$result = mysql_query($sql);

	if($result)
	{
		$resData = mysql_fetch_row($result);
		$result = array("cid" => $cid, "rid" => $resData[1], "uid" => $resData[0], "comment" => $resData[2], "timecreated" => $resData[3], "timemodified" => $resData[4]);
	}
	
	return $result;
}

// Teller antall kommentarer en angitt ressurs har.
// Kan benyttes sammen med "start" og "totaltantall" i getCommentsByRID(...) og getCommentsByUID(...) for � finne antall sider man trenger for � vise alle kommentarene, gitt at man vil fordele det p� flere sider.
function countCommentsByRID($rid)
{
	$sql = "SELECT COUNT(*) FROM `".DB_NAME."`.`comments` WHERE rid = ".$rid;
	
	$result = mysql_query($sql);
	
	if($result)
	{
		$resData = mysql_fetch_row($result);
		
		$result = $resData[0];
	}
	
	return $result;
}

// Samme som countCommentsByRID(...), bare at den teller antall kommentarer en gitt bruker har skrevet.
function countCommentsByUID($uid)
{
	$sql = "SELECT COUNT(*) FROM `".DB_NAME."`.`comments` WHERE uid = '".$uid."'";
	
	$result = mysql_query($sql);
	
	if($result)
	{
		$resData = mysql_fetch_row($result);
		
		$result = $resData[0];
	}
	
	return $result;
}

// -------- F�lgende metoder er ikke ment for "allment" bruk --------



function connectDirectToDB($dbName, $user, $pass, $host)
{
	if(!defined("DB_NAME"))
		define("DB_NAME",$dbName);
	
	$resultA = @mysql_connect($host, $user, $pass); // Koble til MySQL-server

	if(!($resultB = @mysql_select_db(DB_NAME)) && $resultA) // Velge standard database fra server
		$resultB = dcCheckAndBuildDB();

	return $resultA && $resultB;
}

function dcCheckAndBuildDB()
{
	if($_POST["pass"] != "passord")
		dcAskForNewDB();
	
	$sql = "CREATE DATABASE `".DB_NAME."`";

	$result = mysql_query($sql);

	$sql = "CREATE TABLE `".DB_NAME."`.`resources` (
		`rid` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`title` VARCHAR( 256 ) NOT NULL ,
		`link` VARCHAR( 256 ) NOT NULL ,
		`author` VARCHAR( 32 ) NOT NULL ,
		`timecreated` INT UNSIGNED NOT NULL ,
		`rating` MEDIUMINT NOT NULL ,
		`description` TEXT NOT NULL ,
		`tags` VARCHAR( 256 ) NOT NULL
		) ENGINE = MYISAM ";
	
	$result = mysql_query($sql) && $result;
	
	$sql = "CREATE TABLE `".DB_NAME."`.`users` (
		`uid` VARCHAR( 32 ) NOT NULL,
		`auth` TINYINT UNSIGNED NOT NULL ,
		`passhash` CHAR( 32 ) NOT NULL ,
		`salt` CHAR( 16 ) NOT NULL ,
		`email` VARCHAR( 64 ) NOT NULL ,
		PRIMARY KEY ( uid )
		) ENGINE = MYISAM ";
	
	$result = mysql_query($sql) && $result;
	
	$sql = "CREATE TABLE `".DB_NAME."`.`comments` (
		`cid` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`uid` VARCHAR( 32 ) NOT NULL ,
		`rid` INT UNSIGNED NOT NULL ,
		`comment` TEXT NOT NULL ,
		`timecreated` INT UNSIGNED NOT NULL ,
		`timemodified` INT UNSIGNED NOT NULL
		) ENGINE = MYISAM ";
	
	$result = mysql_query($sql) && $result;
	
	return $result;
}

function dcAskForNewDB()
{
	ob_clean();
	
	echo '<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<title>MySQL tilkbolet, men database mangler.</title>
	</head>
	<body style = "width: 500px;">
		<div style="background-color: #F8F5F3; border: solid; padding: 4px;">
			<p>MySQL tilkoblet, men databasen mangler. Vil du opprette ny database med alle n�dvendige tabeller?</p>
			<p>Databasenavn: <b>'.DB_NAME.'</b></p>
			<p>Tast inn passordet for � opprette ny database:</p>
			<form name="dbpass" method="post" action="..'.$_SERVER["SCRIPT_NAME"].'">
				<input type="password" name="pass" id="pass">
				<input type="submit" name="submit" id="submit" value="Opprett database">
			</form>';
	if(isset($_POST["pass"]))
		echo '			<p style="color: #FF0000;">Feil passord!</p>';
	echo '			<br />
		</div>
	</body>
</html>';

	die();
}

function dcTagArrayToString($tagArray)
{
	for($i = 0; $i < count($tagArray); $i++)
		$tagArray[$i] = str_replace(";", "<?SK$>", $tagArray[$i]);	
	
	return implode(";", $tagArray);
}

function dcTagStringToArray($tagString)
{
	$tagArray = explode(";", $tagString);
	
	for($i = 0; $i < count($tagArray); $i++)
		$tagArray[$i] = str_replace("<?SK$>", ";", $tagArray[$i]);	
		
	return $tagArray;
}

function dcRandomString($length, $complexity)
{
	$chars = array();	
	
	switch(min($complexity, 3))
	{
		case 3:
			array_push($chars, ",",".","-",";",":","_","|","�","!",
			"#","�","%","&","/","(",")","=","?","`","@","�","$","{",
			"[","]","}","�","�","^","~","*");
		case 2:
			array_push($chars, "A","B","C","D","E","F","G","H","I",
			"J","K","L","M","N","O","P","Q","R","S","T","U","V","W",
			"X","Y","Z"); 
		case 1:
			array_push($chars, "a","b","c","d","e","f","g","h","i",
			"j","k","l","m","n","o","p","q","r","s","t","u","v","w",
			"x","y","z"); 
		default:
			array_push($chars, "0","1","2","3","4","5","6","7","8","9"); 
	}
	
	for($i = 0; $i < $length; $i++)
		$result .= $chars[mt_rand(0, count($chars)-1)];
	
	return $result;
}

?>