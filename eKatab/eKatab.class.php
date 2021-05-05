<?php

require_once 'REST.class.php';

/**
 * eKatab is a PHP eBook reader for the ePub format.
 * it consists of several classes to make the process of 
 * viewing/sharing your books on the local network or web 
 * as smooth as posible.
 * 
 * 
 * @version 2.0
 * @package eKatab
 * @author Abu Khadeejah Karl Holz
 *
 */
class eKatab extends RESTphulSrv {
	/**
	 * This will store values for the XML data used to describ the eBook and it's layout.
	 * The is set via the launcher script
	 * @var array $book_info
	 */
	public $book_info=array();
	/**
	 * This is a list of resources found in the epub, based on the TOC and *.opf file data
	 * the information for navagating the book is included
	 * @var array $manifest
	 */
	public $manifest=array();

	/**
	 * this is a url map list
	 * PATH_INFO to key of manifest property above
	 * 
	 * @var array $urlmap
	 */
	public $urlmap = array();
	 
	/**
	 * load the default values from the RESTful server class
	 */
	function __construct() { $this->auto_invoke(); }
	
	/** 
	 * info on Fairplay DRM from the requested resource from the epub file
	 * Only showing encryption details since I haven't figured out how to decrypt in PHP
	 * 
	 * @param array $page 
	 */
	function view_drm_info($page) {
		$this->book_info['title'] = 'Encrypted/DRM/Fairplay: '.$this->book_info['title'];
		$html = new jQuery_mobile($this->manifest);
		$html->book_info = $this->book_info;
		$html->next = $page['next'];
		$html->prev = $page['prev'];
		$html->home = $this->controller;
		$html->rest = $this->rest;
		$html->enc_info($page);
		echo $html;	
		exit();
	}


	/**
	 * load requested resource from book file 
	 * and set the values to be displayed
	 */

	function load() {
		if ($this->rest == '/' || $this->rest == '')  {
			$this->load_root();
		} elseif ($this->rest == '/manifest') {
			$html = new jQuery_mobile($this->manifest);
			$html->cache_manifest();
			exit();
		} elseif($this->rest == '/cover.jpg') {
			$filetype = mime_content_type($this->book_info['cover']);
			$this->http_header('200',$filetype, TRUE);
			$file = file_get_contents($this->book_info['cover']);
			print $file;
			exit();			
		}

		$key=$this->check_rest();
		$page = $this->manifest[$key];

		if (array_key_exists('Algorithm', $page)) {
			$this->view_drm_info($page);
		}

		switch ($page['type']) {
			case 'application/xhtml+xml':
				$this->load_html($page);
			break;
			default:
				$this->http_header('200',$page['type'], TRUE);
				$file = file_get_contents($page['zip']);
				print $file;
			exit();
		}
	}


	/** 
	 * this is for loading the cover page, some of the more advanced epub files use SVG files
	 * this will be updated as I study the format in detail
	 * 
	 */

	function load_root() {
		$rest = '/';
		$html = new jQuery_mobile($this->manifest);
		$html->book_info = $this->book_info;
		$page = array_shift($this->manifest);
		$html->next = $page['rest'];
		$html->prev = $this->controller;
		$html->home = $this->controller;
		$html->rest = $rest;
		$html->html = '<img src="' . $this->controller . '/cover.jpg" />';
		print $html;
		exit();
	}

	/**
	 * loads the requested x/HTML resource and cleans the markup for display and setup the jQuery Mobile interface
	 * @var array $page
	 */
	function load_html($page) {
		$html = new jQuery_mobile($this->manifest);
		$html->book_info = $this->book_info;
		if ($page['next'] == '') { $html->next = $this->controller; } else { $html->next = $page['next']; }
		if ($page['prev'] == '') { $html->prev = $this->controller; } else { $html->prev = $page['prev']; }

		$html->home = $this->controller;
		$html->rest = $this->rest;
		$href=array('../', './'); 
		$rest=array('', '');

		foreach ($this->manifest as $v ) {
			$href[]='"' . $v['href'] . '"';
			$rest[]='"' . $this->host . $v['rest'] . '"';
		}
		$html->html = $this->load_html_page($page, $href, $rest);
		print $html;
		exit();
	}

	/**
	 * check if the rest string needs to be re-encoded as raw url
	 * it will check if it's a valid resource and return the value from the $urlmap 
	 * @return string
	 */
	function check_rest() {
		$list=$this->urlmap;
		if ($this->rest == '') return '/';
		//test one
		if (array_key_exists($this->rest, $list)) return $list[$this->rest];
		//test two decode all first
		$rest=urldecode($this->rest);
		if (array_key_exists($rest, $list)) return $list[$rest];
		//test 3 re-rawurlencode  
		$r=explode('/', $this->rest);
		$r2=array('');
		array_shift($r);
		foreach ($r as $v) $r2[]=rawurlencode($v);
		$rest=join('/', $r2);
		if (array_key_exists($rest, $list)) return $list[$rest];

		$this->error=$rest.' |'.__METHOD__.' | '.__LINE__.'<br />';
		$this->error('not_found');
	}

	
	/**
	 * load and fix html pages in the ebook before sending it to the browser
	 * @param string $file can be any file referance that is supported by file_get_contents($x), i'm using mostly zip://
	 * @param array $href a list of urls (or words, names, emails, etc.  just note that it needs to be supported by str_replace) to search for within the html documnent
	 * @param array $rest a list of new url or words, names, emails, etc. to replace the pervious list
	 */
	function load_html_page($file, $href=array(), $rest=array()) {
		$f=file_get_contents($file['zip']);
		$this->error=print_r($file,TRUE).' |'.__METHOD__.' | '.__LINE__;
		if (! $f) $f=file_get_contents(rawurldecode($file['zip']));
		if (! $f) $this->error('no_rest_zip_match');		
		$h=$this->xsl_out($this->webroot. '/xsl/epub-html.xsl', $f);
		return str_replace($href, $rest, $h);	

	}
}

/**
 * This is the jQuery Mobile view class for generating the Markup for displaying eBooks on the web with ease
 */

class jQuery_mobile {

	public $var = array();

	public $book_info = array();

	public $next = '';
	public $prev = '';
	public $rest = '';
	public $home = '';

	public $html = '';

	public $style = <<<s
blockquote {
		background: #f9f9f9;
		border-left: 10px solid #ccc;
		margin: 1.5em 10px;
		padding: 0.5em 10px;

}

blockquote:before {
		font-size: 4em;
		line-height: 0.1em;
		margin-right: 0.25em;
		vertical-align: -0.4em;
}

blockquote > p {
		color: #000;
		display: inline;
}

img {
		background: #f9f9f9;
		min-width: 40%;
		max-width:90%;
		margin: 1.5em 10px;
		padding: 0.5em 10px;
		border-left: 10px solid #ccc;
}

s
	;

	public $script = <<<j
jQuery(function (\$) {
	//HTML
	var html = {
	   list_box: function(id, title) {
		   var l ='<div id="'+id+'" data-role="collapsible" data-collapsed="false" data-theme="a" data-content-theme="b">';
		   l+='<h3>'+title+'</h3>';
		   l+='<ul data-role="listview">';
		   l+='</ul>';
		   l+='</div>';
		   return l;
	   },
	   listviewItem: function(uri, name){
		   return '<li><a data-ajax="false" class="debug_log" href="'+uri+'" data-transition="flip">'+name+'</a></li>';
	   }
	};
});	
j
	;

	public $jqcss = '/css/jquery.mobile-1.4.5.min.css';

	public $jqjs = '/js/jquery.min.js';

	public $jqui = '/js/jquery.mobile-1.4.5.min.js';

	public $debug = FALSE;

	function enc_info($page) {
		//remove relative path so the urls can be replaced
		$h = '<ul data-role="listview" data-inset="true" class="ui-listview ui-listview-inset ui-corner-all ui-shadow">';
		$h .= $this->add_enc_list("Resource: " . $page['rest'], "<p>This books is using DRM/Fairplay, this app can't read it.  Please use your iOS or reader you purchased this book on to read. I have dumped the details per each resource if you want to try it yourself</p>");

		$h .= $this->add_enc_list("Canonicalization Method", "<a id='CanonicalizationMethod' href='" . $this->book_info['CanonicalizationMethod'] . "' target='_blank' >" . $this->book_info['CanonicalizationMethod'] . "</a>");
		$h .= $this->add_enc_list("Signature Method", "<a id='SignatureMethod' href='" . $this->book_info['SignatureMethod'] . "' target='_blank' >" . $this->book_info['SignatureMethod'] . "</a>");
		$h .= $this->add_enc_list("Signature Value", $this->book_info['SignatureValue']);	
		$h .= $this->add_enc_list("Fairplay Data", '<pre style="overflow-x: scroll;">' .$this->book_info['fairplay_data']. '</pre>');

		$h .= $this->add_enc_list("Algorithm", "<a id='Algorithm' href='" . $page['Algorithm'] . "' target='_blank' >" . $page['Algorithm'] . "</a>");
		$h .= $this->add_enc_list("Cipher: ".$page['Cipher'], '<pre style="overflow-x: scroll;">' . file_get_contents($page['zip']) . '</pre>');
		$h .= $this->add_enc_list("Digest Method", "<a id='DigestMethod' href='" . $page['DigestMethod'] . "' target='_blank' >" . $page['DigestMethod'] . "</a>");
		$h .= $this->add_enc_list("Digest Value", $page['DigestValue'] );

		$h .= '</ul>';
		$this->html = $h;
		return TRUE;
	}

	function add_enc_list($k, $val) {
		return <<<h

		<li data-role="list-divider" data-theme="a" data-swatch="a" data-form="ui-bar-a" role="heading" class="ui-li-divider ui-bar-a ui-first-child">
			<h3>{$k}</h3>
		</li>
		<li data-form="ui-body-a" data-swatch="a" data-theme="a" class="ui-li-static ui-body-a">
			{$val}
		</li>
h
		;
	}

	/**
	 * cache manifest document for a more webapplike experience and offline access
	 */
	function cache_manifest() {
		$list = '';
		foreach ($this->var as $k => $v) {
			$list .= $v['rest'] . "\n";
		}
		header('Content-Type: text/cache-manifest');
		$m=<<<m
CACHE MANIFEST:
{$this->jqcss}
# jQuery and jQuery Mobile
{$this->jqjs}
{$this->jqui}
# epub manifest
$list
m
		;
		echo $m;
		exit();
	}

	function __construct($var=array()) {
		if (is_array($var)) {
			$this->var=$var;
		}
		// if (preg_match('/^.*iPhone/', $this->var['client_name'])) {
		// 	$this->var['cover_height'] = "430px";
		// 	$this->var['cover_width'] = "290px";
		// }
		$this->book_info['title'] = '';
		$this->book_info['creator'] = '';
		$this->book_info['publisher'] = '';
		$this->book_info['description'] = '';
		$this->book_info['date'] = '';
		$this->book_info['language'] = '';
		$this->book_info['mime_type'] = '';

		return TRUE;
	}

	function footer() {
		$footer = '<div data-role="footer"  data-position="fixed">';
		if ($this->book_info['mime_type'] == 'application/x-ibooks+zip'){
		 	$footer.='<h4>iBooks Author.app books are not fully supported! it will not render properly</h4>';
		} elseif ($this->book_info['mime_type'] != '') {
			$footer.='<h4>eBook mime type: ' . $this->book_info['mime_type'] . '</h4>';
		}
		$footer.='</div>';
		return $footer;
	}

	function links() {
		$links='<ul data-role="listview" id="page_links" data-filter="true"  data-inset="true">';
		foreach ($this->var as $l => $o) {
			// && $o['zip'] != $this->book_info['first']
			if ($o['type'] == 'application/xhtml+xml') {
				if (array_key_exists('title', $o)) {
					$links.='<li><a  id="'.$l.'" href="'.$o['rest'].'"  >'.$o['title'].'</a></li>';
				} 
			}
		}
		$links.='</ul>';
		return $links;
	}

	function show_debug() {
		if (!$this->debug) return '';
		if (is_array($this->var))
			return '<pre>'.print_r($this->var, TRUE).'</pre>';
		return '';
	}

	function make_books($glob=array(), $ext='epub') {
		if (count($glob) < 1) return;
		switch ($ext) {
			case 'epub':
				$title='ePub ebooks';
				$sub = '';
			break;
			case 'ibooks':
				$title = 'iBooks (ibooks) ebooks, not fully supported';
				$sub = '';
			break;
			case 'ipa':
				$title = 'iPhone app (epub) ebooks ';
				$sub= '<backquote>These books are no longer avalible in the Apps store, stopped working properly in iOS6</backquote>';
			break;
		}

		$h = <<<h
		<div id="{$ext}" data-role="collapsible" data-collapsed="false" data-theme="a" data-content-theme="b">
			<h3>$title</h3>
			$sub
			<ul data-role="listview"  data-inset="true">
h
		;
		foreach ($glob as $b) {
			$file=basename($b, '.'.$ext).'.php';
			$h.='		<li><a data-ajax="false" href="'.rawurlencode($file).'"  data-transition="flip">'.basename($b, '.'.$ext)."</a></li>";
		}
		$h .= <<<h
			</ul>
		</div>
h
		;
		$this->html .= $h;
		return TRUE;

	}


	function __toString() {
		$subject='';
		if (array_key_exists('subject', $this->book_info)) 
			$subject = join(', ',$this->book_info['subject']);

		$html = preg_replace(array(
			'/<p class="(blockquote[a-zA-Z0-9]*)">(.*)<\/p>/',
			'/<img /'
			),array(
			'<blockquote ><p class="$1">$2</p></blockquote>',
			'<br /><img '
			), $this->html);
		if (count($this->var) > 0) {
			$nav = <<<n
	    	<div data-role="navbar" data-iconpos="left" >
		    	<ul>
					<li><a id="prev" data-transition="slide"  href="{$this->prev}" data-icon="arrow-l">Prev</a></li>
					<li><a data-rel="back"  href="#" data-icon="back" />Back</a></li>
					<li><a  href="#book_toc" data-transition="slidedown" id="toc_list" data-icon="grid"/>TOC</a></li>
					<li><a id="next" data-transition="slide" href="{$this->next}" data-icon="arrow-r" >Next</a></li>
		    	</ul>
			</div>
n
			;
		}
		return <<<h
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <title class="title">{$this->book_info['title']}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="{$this->jqcss}" />
	<script src="{$this->jqjs}"></script>
	<script src="{$this->jqui}"></script>
	<script type="text/javascript">
{$this->script}
	</script>
	<style type="text/css">
{$this->style}
	</style>
    </head>
    <body>
	<div data-role="page" data-theme="b" id="{$this->rest}">
	    <div data-role="header"  data-position="fixed"><h1 class="title">{$this->book_info['title']}</h1>
		$nav
	    </div>
	    <div data-role="content" id="html">
		{$html}
	    </div>
	    {$this->footer()}
	 </div>
   <div data-role="page" data-theme="b" id="book_toc">
	    <div data-role="header" data-position="fixed">
	    	<h1 id="title">{$this->book_info['title']} - TOC</h1>
	    	<div data-role="navbar" data-iconpos="left" >
		    	<ul>
					<li><a id="home" data-transition="slideup" href="{$this->home}" data-icon="arrow-l">Home</a></li>
					<li><a data-rel="back" data-transition="slideup" href="#" data-icon="back" />Back</a></li>
					<li><a id="info"  data-transition="slideup" href="#info" data-icon="arrow-r" >Info</a></li>
		    	</ul>
			</div>
		 </div>
	   <div data-role="content" >
			<div data-theme="b" data-content-theme="c">{$this->links()}</div>
	   </div>
	   {$this->footer()}
	</div>
	<div data-role="page" data-theme="b" id="info">
		<div data-role="header" data-position="fixed"><h1 id="title">{$this->book_info['title']} - Info</h1>
	    	<div data-role="navbar" data-iconpos="left" >
		    	<ul>
					<li><a id="home" data-transition="slideup" href="{$this->home}" data-icon="arrow-l">Home</a></li>
					<li><a data-rel="back" data-transition="slideup" href="#" data-icon="back" />Back</a></li>
					<li><a id="info"  data-transition="slideup" href="#{$this->rest}" data-icon="arrow-r" >Resume </a></li>
		    	</ul>
			</div>
		</div>
	  <div data-role="content" >
			<div >
				<div >
					<div ><h3>Title</h3><p>{$this->book_info['title']}</p></div>
					<div ><h3>Creator</h3><p>{$this->book_info['creator']}</p></div>
					<div ><h3>Publisher</h3><p>{$this->book_info['publisher']}</p></div>
					<div ><h3>Description</h3><p>{$this->book_info['description']}</p></div>
					<div ><h3>Subject</h3><p>{$subject}</p></div>
					<div ><h3>Date</h3><p>{$this->book_info['date']}</p></div>
					<div ><h3>Language</h3><p>{$this->book_info['language']}</p></div>
				</div>

			</div>
			<hr/>
			{$this->show_debug()}
	 </div>
	 {$this->footer()}
	</div>
 </body>
</html>
h
			;

	}

}