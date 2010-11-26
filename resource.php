<?php
class ResourceClass {
	
	public $id;
	public $name;
	public $url;
	public $description;
	public $tags = array();
	public $score;
	public $date;
	public $owner;
	
	// kun id, navn, url og beskrivelse er nødvendig. id kan settes til 0 eller hva som helst om dette er en ny ressurs.
	function __construct($i=0, $n, $u, $d, $t=array(), $s=1, $dt=0, $o=null) {
		$this->id = $i;
		$this->name = (string)$n;
		$this->url = (string)$u;
		$this->description = (string)$d;
		$this->tags = $t;
		$this->score = $s;
		$this->date = $dt;
		$this->owner = $o;
	}

	// Works out the time since the entry post, takes a an argument in unix time (seconds) 
	// http://www.dreamincode.net/code/snippet86.htm
	// modifisert fra engelsk
	function time_since($original) {
		// array of time period chunks
		$chunks = array(
			array(60 * 60 * 24 * 365 , 'år'),
			array(60 * 60 * 24 * 30 , 'måned'),
			array(60 * 60 * 24 * 7, 'uke'),
			array(60 * 60 * 24 , 'dag'),
			array(60 * 60 , 'time'),
			array(60 , 'minutt'),
		);
		
		$today = time(); /* Current unix time  */
		$since = $today - $original;
		
		// $j saves performing the count function each time around the loop
		for ($i = 0, $j = count($chunks); $i < $j; $i++) {
			
			$seconds = $chunks[$i][0];
			$name = $chunks[$i][1];
			
			// finding the biggest chunk (if the chunk fits, break)
			if (($count = floor($since / $seconds)) != 0) {
				break;
			}
		}
		
		if ($count == 1) 
			$print = '1 '.$name;
		else if ($name == 'år') 
			$print = "$count {$name}";
		else if ($name == 'time') 
			$print = "$count {$name}r";
		else 
			$print = "$count {$name}er";

		return $print;
	}
	
	function textReplace($str) {
		$str = preg_replace("/(\r\n|\r)/", "\n", $str); // Unix newlines
		$nbcode = preg_match_all("/@@(.+)@@/Ums", $str, $matches_code, PREG_PATTERN_ORDER); // lagre kodestrenger
		$str = preg_replace("/@@(.+)@@/Ums", "</p><pre><code>§§CODE§§</code></pre><p>", $str); // sett pre og code
		$morecode = preg_match("/@@(.*?)/Ums", $str, $match_code); // lagre kodestreng for uavslutta stykker
		$str = preg_replace("/@@(.*?)/Ums", "<pre><code>@@CODE@@</code></pre>", $str); // sett pre og code for uavslutta stykker
		$str = preg_replace("/^([^!\*#\n][^\n]+)$/Um", "<p>$1</p>", $str); // sett paragraphs
		$str = str_replace("<p><pre", "<pre", $str); // fjern p fra pre
		$str = str_replace("/pre></p>", "/pre>", $str);
		if($nbcode > 0) 
			$str = preg_replace(array_fill(0, $nbcode, "/§§CODE§§/Us"), $matches_code[1], $str, 1); // sett tilbake kode
		if($morecode > 0) 
			$str = preg_replace("/@@CODE@@/Us", $match_code[1], $str); // sett tilbake kode
		$rg_url = "[0-9a-zA-Z\.\#/~\-_%=\?\&,\+\:@;!\(\)\*\$']*"; // url'er kan bestå av disse
		$rg_link_http = "h(ttps?://" . $rg_url . ")"; // og begynner slik
		$str = preg_replace('#\[([^\]]+)\|' . $rg_link_http . '\]#U', '<a href="xx$2" class="url">$1</a>', $str); // lenke med lenketekst
		$str = preg_replace('#' . $rg_link_http . '#i', '<a href="$0" class="url">xx$1</a>', $str); // ren url
		$str = preg_replace('#xxttp#', 'http', $str); // bare fordi
		return $str;
	}
	
	function display() {
		$taglinks = '';
		foreach ($this->tags as $n => $tag) {
			$taglinks = $taglinks.'<a class="tag" href="javascript:searchResult(searchDefault(), \'&amp;tags[]='.urlencode($tag).'\')">'.$tag.'</a>';
			if ($n < count($this->tags)-1)
				$taglinks = $taglinks.' ';
		}
		
		include_once './db.php';
		if (connectToDB()) {
			if (countCommentsByRID($this->id) == 1)
				$commentString = '1 kommentar';
			else
				$commentString = countCommentsByRID($this->id).' kommentarer';
		}

		echo '<div class="resource">'
		.'<div class="vote">'
		.'<a href="javascript:checkLogin(\'upvote.php?id='.$this->id.'\')"><img alt="Stem opp" title="Stem opp" src="upvote.gif" style="height: 16px" /></a>' // <noscript><a href="upvote.php?id='.$this->id.'"><img alt="Stem opp" title="Stem opp" src="upvote.gif" style="height: 16px" /></a></noscript>
		.' <span class="score" id="scoreID'.$this->id.'">'.$this->score.'</span>'
		.' poeng, '
		.' <span class="userdate">skrevet av <a href="user.php?uid='.urlencode($this->owner).'">'.$this->owner.'</a> for '.$this->time_since($this->date).' siden</span>'
		.' (<a href="report.php?id='.$this->id.'">rapporter</a>)'
		.'</div>'		//vote
		.'<div class="data">'
		.'<h3><a href="'.$this->url.'">'.$this->name.'</a></h3>'
		.$this->textReplace(substr($this->description, 0, 150).' ...')
		.'<p class="full"><a href="item.php?id='.$this->id.'">Full informasjon og '.$commentString.' &raquo;</a></p>'
		.'<p class="tags"><strong title="Tags er merkelapper som klassifiserer ressursene. Klikk på tags for å finne mer innen samme tema.">Tags: </strong>'.$taglinks.'</p>'		
		.'</div>'		//data
		.'</div>'		//resource
		.'<hr/>';
	}
	
	function displayFull() {
		$taglinks = '';
		foreach ($this->tags as $n => $tag) {
			$taglinks = $taglinks.'<a class="tag" href="javascript:searchResult(searchDefault(), \'&amp;tags[]='.urlencode($tag).'\')">'.$tag.'</a>';
			if ($n < count($this->tags)-1)
				$taglinks = $taglinks.' ';
		}
		
		echo '<div class="resource">'
		.'<div class="vote">'
		.'<a href="javascript:checkLogin(\'upvote.php?id='.$this->id.'\')"><img alt="Stem opp" title="Stem opp" src="upvote.gif" style="height: 16px" /></a>' // <noscript><a href="upvote.php?id='.$this->id.'"><img alt="Stem opp" title="Stem opp" src="upvote.gif" style="height: 16px" /></a></noscript>
		.' <span class="score" id="scoreID'.$this->id.'">'.$this->score.'</span>'
		.' poeng, '
		.' <span class="userdate">skrevet av <a href="user.php?uid='.urlencode($this->owner).'">'.$this->owner.'</a> for '.$this->time_since($this->date).' siden</span>';
		include_once './db.php';
		session_start();
		if (connectToDB()) {
			$s = verifyUser($_SESSION['name'], $_SESSION['pass'], false);
			if (($s['user'] == $this->owner) || ($s['auth'] == 3))
				echo ' (<a href="edit.php?id='.$this->id.'">rediger</a> | <a href="delete.php?id='.$this->id.'">slett</a>)';
		}
		echo ' (<a href="report.php?id='.$this->id.'">rapporter</a>)'
		.'</div>'		//vote
		.'<div class="data">'
		.'<h3><a href="'.$this->url.'">'.$this->name.'</a></h3>'
		.$this->textReplace($this->description)
		.'<p class="tags"><strong title="Tags er merkelapper som klassifiserer ressursene. Klikk på tags for å finne mer innen samme tema.">Tags: </strong>'.$taglinks.'</p>'
		.'</div>'		//data
		.'</div>'		//resource
		.'<hr/>';
	}
	
}
?>