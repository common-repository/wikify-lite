<?php

// https://stackoverflow.com/a/3387568/7433563

function wikify_sanitize_html($string) {
	/*
	//$string = "<p>This <b>is</b> <a href='#' onclick='foo'>it.</a></p>";
	$string = html_entity_decode($string); // This is really important for dealing with content with quotation marks passed through the $_POST function.
	$string = strip_tags($string,'<h1>,<h2>,<h3>,<h4>,<h5>,<h6>,<b>,<strong>,<strike>,<hr>,<pre>,<code>,<cite>,<br>,<blockquote>,<p>,<small>,<a>,<u>,<em>,<sub>,<sup>,<img>,<caption>,<table>,<col>,<colgroup>,<figure>, <figcaption>, <thead>, <tbody>, <tfoot>,<th>,<tr>,<td>,<div>,<span>,<ul>,<ol>,<li>');
	$dom = new DOMDocument();
	$dom->loadHTML($string);
	$allowed_attributes = array('id','href', 'src', 'class', 'style', 'colspan', 'rowspan');
	foreach($dom->getElementsByTagName('*') as $node){
		for($i = $node->attributes->length -1; $i >= 0; $i--){
			$attribute = $node->attributes->item($i);
			if(!in_array($attribute->name,$allowed_attributes)) $node->removeAttributeNode($attribute);
		}
	}

	$html = $dom->saveHTML();

	
	$html = str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">','',$html);
	$html = str_replace('<html><body>','',$html);
	$html = str_replace('</body></html>','',$html);
	$html = str_replace('%5C%22','',$html); //DOMDocument inexplicably breaks HTML attributes with extraneous characters
	*/
	return $string;

}