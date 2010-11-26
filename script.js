var RecaptchaOptions = {theme: 'white',	tabindex: 2};

// AJAX-funksjoner bruker ajax.js!!

function checkLogin(intent) {
	func = function(data) {
		if (data == 'ok') {
			// hvis intent inneholder upvote, send intent videre til upBoat()
			if (intent.search('upvote') > -1)
				upBoat(intent);
			// hvis intent er edit.php, gå dit
			if (intent == 'edit.php')
				window.location = intent;
			// etc.
		} else  {
			// gjør skjermen mørk og vis registreringsskjerm
			grayOut(true);
			var regbox = $('regbox');
			regbox.innerHTML = mini.ajax.gets('regbox.php?intent='+intent);
			Recaptcha.create("", "recaptcha", {theme: "white", tabindex: 2, callback: Recaptcha.focus_response_field});
			regbox.style.display = 'block'; // vis registreringsskjerm
		}
	}
	mini.ajax.get('validate.php', func);
}

function logOut() {
	window.location = 'logout.php';
}

// AJAX-oppstemming av ressurs
function upBoat(intent) {
	func = function(data) {
		$('scoreID'+intent.substring(14)).innerHTML = data; // scoreID + tall blir satt for hver ressurs, intent inneholder ID på samme sted
	}
	mini.ajax.get(intent, func);
}

// skjuler/lukker registreringsskjerm
function hide() {
	regbox.style.display='none';
	grayOut(false);
}

// lukker overlay-vindu for registrering når bruker trykker ESC
document.onkeydown = function(e) {
	if (e == null)
		keycode = event.keyCode; 
	else
		keycode = e.which; 
	if (keycode == 27) { 
		hide();		
	}
};

// gjør skjermen mørk og ikke-interagerbar
function grayOut(vis) {
	var zindex = 50;
	var opacity = 40;
	var opaque = (opacity / 100);
	var bgcolor = '#000000';
	var dark = $('darkLayer');
	if (!dark) {
		var tbody = document.getElementsByTagName('body')[0];
		var tnode = document.createElement('div');
			tnode.style.position='absolute';
			tnode.style.top='0px';
			tnode.style.left='0px';
			tnode.style.overflow='hidden';
			tnode.style.display='none';
			tnode.id='darkLayer';
		tbody.appendChild(tnode);
		dark=$('darkLayer');
	}
	if (vis) {
		// Calculate the page width and height 
		if( document.body && ( document.body.scrollWidth || document.body.scrollHeight ) ) {
			var pageWidth = document.body.scrollWidth+'px';
			var pageHeight = document.body.scrollHeight+'px';
		} else if( document.body.offsetWidth ) {
			var pageWidth = document.body.offsetWidth+'px';
			var pageHeight = document.body.offsetHeight+'px';
		} else {
			var pageWidth='100%';
			var pageHeight='100%';
		}   
		//set the shader to cover the entire page and make it visible.
		dark.style.opacity = opaque;                      
		dark.style.MozOpacity = opaque;                   
		dark.style.filter = 'alpha(opacity='+opacity+')'; 
		dark.style.zIndex = zindex;        
		dark.style.backgroundColor = bgcolor;  
		dark.style.width = pageWidth;
		dark.style.height = pageHeight;
		dark.style.display = 'block';
	} else
		dark.style.display='none';
}

// AJAX-søkefunksjon
function searchResult(str, tags) {
	if (str.length == 0 && tags == 0) {
		$('results').innerHTML = '';
		return;
	}
	func = function(data) {
		$('results').innerHTML = data;
	}		
	mini.ajax.get('livesearch.php?q='+str+tags, func);
}

/*
// returnerer hele søkestrengen for tags, eks. &tags[]=PHP&tags[]=JavaScript
function toggleSearchTags() {
	tags = '';
	/*nodes = $('fronttags').childNodes;
	for (i in nodes)
		if (nodes[i].value != undefined && nodes[i].checked != 0)
			tags += '&tags[]='+nodes[i].value;
	return tags;
}

// tagfiltrering for forsiden
function toggleTag(tag) {
	nodes = $('fronttags').childNodes;	
	for (i in nodes)
		if (nodes[i].innerHTML == tag)
			if (nodes[i].style.background == 'white')
				nodes[i].style.background = 'black';
			else
				nodes[i].style.background = 'white';
}
*/

function searchDefault() {
	$('q').value = 'Søk...';
	return '';
}

// synkron uthenting av tags i form av en kommaseparert string, splitt denne i en array og returner
function getTags() {
	return mini.ajax.gets('gettags.php').split(',');
}

// endrer feilstava tags til riktig stava tags, kalles når man skriver inn tags direkte
function replaceDuplicateTags(tags) {
	for (i in tags)
		if (tags[i].length > 2) 
			if ($('tags').value.toLowerCase().lastIndexOf(tags[i].toLowerCase()) > -1) {
				reg = new RegExp(tags[i], 'gi');
				$('tags').value = $('tags').value.replace(reg, tags[i]);
			}
}

// søker etter tags i tekst, kalles når beskrivelse endres
function searchForTags(text, tags) {
	for (i in tags)
		if (tags[i].length > 2) // hvis tag er mer enn to tegn lang
			if ($('tags').value.lastIndexOf(tags[i]) == -1) // hvis tag ikke allerede finnes i tagliste
				if (text.toLowerCase().lastIndexOf(tags[i].toLowerCase()) > -1) // hvis lowercase tag er i lowercase text
					$('tags').value += tags[i]+', '; // legg til tag
					
		// else kan raffineres til å merke sånt som C og C# også, hvis de forekommer for seg selv mange ganger, f.eks.
}

// legger helt enkelt tags til i tagfeltet på inputskjermen når du klikker på dem
function addTag(tag) {
	$('tags').value += tag+', ';
}

// sjekker feltene i edit.php
function checkFields(form) {
	valid = true;
	
	if (!form.name.value) { 
		$('namespan').style.color = '#d50';
		valid = false;
	} else {
		$('namespan').style.color = '#000';
	}

	if (form.url.value && 
		form.url.value.search(/https?:\/\//i) == -1 && 
		form.url.value.search(/php/i) == -1) { // hvis vi har verdi skal den starte med http(s):// eller ha php i seg (lokal)
		$('urlspan').style.color = '#d50';
		valid = false;
	} else {
		$('urlspan').style.color = '#000';
	}

	if (!form.tags.value) {
		$('tagspan').style.color = '#d50';
		valid = false;
	} else {
		$('tagspan').style.color = '#000';
	}

	if (!form.desc.value) {
		$('descspan').style.color = '#d50';
		valid = false;
	} else {
		$('descspan').style.color = '#000';
	}
	
	return valid;
}