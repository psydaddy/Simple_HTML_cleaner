<?php
/*
 +-----------------------------------------------------------------------+
 | Copyright (C) 2015, Pedro Ferreira                                    |
 |                                                                       |
 | Free to use, sell and change                                          |
 +-----------------------------------------------------------------------+
 | Author: Pedro Miguel Dias Ferreira                                    |
 +-----------------------------------------------------------------------+
*/

//allowed html tags
$whitelistedTags = array('title', 'table', 'tbody', 'thead', 'tfoot', 'tr', 'th', 'td', 'colgroup', 'col', 'p', 'br', 'hr', 'blockquote', 'b', 'i', 'u', 'sub', 'sup', 'strong', 'em', 'tt', 'var', 'q', 's', 'strike', 'center', 'code', 'xmp', 'cite', 'pre', 'abbr', 'acronym', 'address', 'samp', 'fieldset', 'legend', 'a', 'img', 'div', 'span', 'b', 'font', 'big', 'small', 'h1', 'h2', 'h3', 'h4', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'dl', 'dt', 'style');

//works on each tag and atributes to find unsecure code
function TagsWorker($match){
	global $whitelistedTags;
	
	//elements from matches
	$tag = trim($match[1]);
	$atributes = trim($match[2]);
	
	
	//accept <!--[if ...]> comments
	if($tag == '!--[if' || $tag == '![endif]--'){
		if($atributes != '')
			$atributes = ' '.$atributes;
				
		return '<'.$tag.$atributes.'>'; //return as it is!
	}
	
	
	//is an open or close tag
	$is_close_tag = (strpos($tag, '/') === 0);
	
	//exclude "/" from tags
	$tag = str_replace('/', '', $tag);
	
	//clean atributes
	$clean_atributes = '';
	
	if(in_array($tag, $whitelistedTags)){
		if($is_close_tag)
			return '</'.$tag.'>';
		else{
			//fix all tag atributes (making sure that attribute=value
			$dirty = "<element $atributes />";
			$dom = new DOMDocument;
			libxml_use_internal_errors(true);
			$dom->loadHTML($dirty);
			libxml_clear_errors();
			$cleanXML = $dom->saveXML();
			
			//go throw tag elements
			$p = $dom->getElementsByTagName('element')->item(0);
			if ($p->hasAttributes()) {
				foreach ($p->attributes as $attr) {
					$name = $attr->nodeName;
					$value = $attr->nodeValue;
					
					$allowed = true;
					
					//onclick, onerror, ...
					if(stripos(trim($name), 'on') === 0)
						$allowed &= false;
					
					//javascript: as a value atribute
					if(preg_match('/^javascript[\s]*:/i', trim($value)) )
						$allowed &= false;
					
					if($allowed)
						$clean_atributes .= $name.'="'.htmlspecialchars($value).'" ';
				}
			}
			
			//add a space between atributes if there are any
			$clean_atributes = trim($clean_atributes);
			if($clean_atributes != '')
				$clean_atributes = ' '.$clean_atributes;
			
			return '<'.$tag.$clean_atributes.'>';
		}
	}else
		//return '<'.($is_close_tag?'/':'').'not_allowed>';
		return ''; //if not allowed, return empty
}

//main xss clean funciont
function cleanHtml($html){
	$html = preg_replace_callback('/<([^\s|>]*)(.*?)>/Smi', 'TagsWorker', $html);
	return $html;
}
?>
