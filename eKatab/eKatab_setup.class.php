<?php 
require_once 'REST.class.php';
/**
 *  The eKatab_setup was created to make the containerization of ebooks more easy and to improve performance.  
 * 
 *  This will generate php launcher scripts with embeded configuration instead of an ini configuration and a launcher script.
 *
 *  Fairplay/DRM books won't disply, so the data from the encryption.xml and others will be displayed for each requested resource
 *  if you can figure out how to display them in php or javascript, please share.
 * 

 */
class eKatab_setup extends RESTphulSrv {
	// must match in zipfile
	/**
	 * This file must exist int the epub file!!
	 *
	 * @var string $container this file is relitive to the ebook root
	 */
	private $container='META-INF/container.xml';
	
	/**
	 * File that contains digest signatures
	 *
	 * @var string $signatures this file is relitive to the ebook root
	 */
	private $signatures='META-INF/signatures.xml';

	/**
	 * File that contains encryped file references
	 *
	 * @var string $encryption this file is relitive to the ebook root
	 */
	private $encryption='META-INF/encryption.xml';
	
	/**
	 * Fairplay related xml data
	 *
	 * @var string $sinf this file is relitive to the ebook root
	 */
	private $sinf='META-INF/sinf.xml';
	
	/**
	 * This is the itunes plist file, contains personal information from the purchased book, 
	 * I have removed the reference to it, i will need to revisit it later.
	 * 
	 * iBooks uses this format, but they're encoded in a newer mac format.
	 *
	 * @var string $plist name of the plist file to match
	 */
	private $plist='iTunesMetadata.plist';
	
	/**
	 * This is the itunes art work cover, mostlikely jpeg
	 *
	 * @var string $cover jpeg cover page
	 */
	private $cover='iTunesArtwork';

	/**
	 * this is the ebooks configuration file, found in the container.xml
	 * - the file name is not consistant, like the files found in ./META-INF/
	 *  
	 * @var string $opf
	 */
	private $opf = '';

	/**
     * This is the mimetype of the ebook, epub or ibook, just a one line string
     * 
     * @var $mime_type
     */
	private $mime_type='mimetype';
	
	/**
	 * This will hold all the books meta data 
	 * @var array $book_info
	 */
	public $book_info=array();
	
	/**
	 * This is a list of all resourece found in the epub file,
	 * it contains eveything from book order, mimetypes, rest url, next/prev page, title, etc
	 * 
	 * @var array $manifest
	 */
	protected $manifest=array();
	
	/**
	 *  files found in the zip arcive
	 */
	protected $scanned_files=array();
	
	/**
	 * this is the toc file reference, found in the opf file.
	 * @var string $toc
	 */
	protected $toc;	
	
	/**
	 * list of supported mimetypes
	 * @var array $types
	 */
	private $types=array(
		'application/epub+zip',
		'application/x-ibooks+zip'
	);

	/**
	 * map PATH_INFO to manifest id
	 * @var array $urlmap
	 */
	protected $urlmap = array();

	/**
	 * print_cmdline
	 * prints a line to the STDOUT
	 * @var string $key
	 * @var string $text
	 */
	function print_cmdline($key='', $text='') { echo '> ' .$key . ': '. $text . "\n"; }

	/**
	 * This will configure the epub to be viewed and generate a launcher script
	 * @param string $epub epub file
	 * @param string $prefix prefix to epub root in zipfile
	 */
	function __construct($epub, $prefix='') {
		$this->prefix=$prefix;
		//set auto env values
		$this->auto_invoke(TRUE);
		// this is so auto invoke doesn't clober the value
		$this->pwd = $_SERVER['PWD'];
		$this->controller_root=$this->pwd;
		$p = pathinfo($epub);
		$this->controller = '/' . basename($epub, '.'.$p['extension']) . '.php';

		$file=realpath($epub);
		if (is_file($file)) {
			$book=$file;
		} elseif (is_file($epub)) {
			$book=$epub;    
		} else {
			$this->error=$epub.' |'.__METHOD__.' | '.__LINE__;
			echo $this->error;
			exit();
		}

		$this->zip='zip://'.trim($book);
		
		$this->scanned_files=$this->zip_resorces($book);
		
		$this->print_cmdline('epub fullpath', $book);

		if (in_array($this->container, $this->scanned_files)) {
			if (! class_exists ( 'DOMDocument' ))  die('DOMDocument class not found');
			$doc = new DOMDocument();
			$doc->loadXML(file_get_contents($this->zip.'#'.$this->prefix.$this->container));
			$opf=$this->DOMsearch($this->container, '//rootfile/@full-path|//c:rootfile/@full-path');
			foreach ($opf as $o)
				$this->opf = $o->nodeValue;
			
			$this->book_root= dirname($this->opf) . '/';
			if ($this->book_root == './') $this->book_root='';
			$this->read_book_info();
			$this->get_manifest();
			$this->get_toc();
			// For encrypted files
			$this->get_encryption();
			$this->get_signitures();
			$this->get_sinf();

		} else { $this->print_cmdline('ePub file is missing the META-INF/container.xml file'); die(); }
		
		$this->make_book();
	}

	/**
	 * This is the launcher script that is generated
	 */
	function make_book() {
		$book_info= base64_encode(serialize($this->book_info));
		$manifest = base64_encode(serialize($this->manifest));
		$urlmap   = base64_encode(serialize($this->urlmap));	
		$p=<<<b
<?php
// launcher script for {$this->zip}
require_once 'eKatab/eKatab.class.php';
\$book = new eKatab();
\$book->urlmap = unserialize(base64_decode('$urlmap'));
\$book->book_info = unserialize(base64_decode('$book_info'));
\$book->manifest = unserialize(base64_decode('$manifest'));
\$book->load();
?>
b
;
		if(file_put_contents($this->controller_root . $this->controller, $p)) {
			$this->print_cmdline('epub launcher',  $this->controller_root . $this->controller);
		} else {
			$this->print_cmdline('FAILED to MAKE',  $this->controller_root . $this->controller);
		}

	}
	
	/**
	 * parses the TOC file and adds the relivent data to the manifest
	 */
	function get_toc() {
		$doc = new DOMDocument();
		$doc->loadXML(file_get_contents($this->toc));
		$toc = $doc->getElementsByTagName('navMap');
		$akeys = array_keys($this->manifest);
		$order=1;
		foreach($toc as $tt) {

			foreach($tt->childNodes as $t) {
				$list=array();
				if ($t->nodeName == "#text") continue;
				$list['order'] = $order;
				$list['title'] = trim($t->getElementsByTagName('navLabel')->item(0)->nodeValue);
				$list['href'] = $t->getElementsByTagName('content')->item(0)->getAttribute('src');
				$links = $t->getElementsByTagName('navPoint');
				$href = explode('#', $list['href']);
				if (count($href) == 2) {
					$list['href'] = $href[0];
				}

				$key = array_search($list['href'], array_column($this->manifest, 'href'));
				foreach ($list as $k => $v)
					$this->manifest[$akeys[$key]][$k] = $v;
  				if ($links) {
					$x=0;
					foreach($links as $l) {
						if ($l->nodeName == "#text") continue;
						$list['links'][$x]['title'] = $l->getElementsByTagName('text')->item(0)->nodeValue;
						$list['links'][$x]['href']  = $this->controller . '/'. $this->book_root . $l->getElementsByTagName('content')->item(0)->getAttribute('src');
						
						$key = array_search($list['links'][$x]['href'], array_column($this->manifest, 'rest'));
						if (!array_key_exists('title', $this->manifest[$akeys[$key]])){
							$this->manifest[$akeys[$key]]['title'] = $list['links'][$x]['title'];
						} 
						$x++;
					}
				} 
				$order++;
			}
		}
	}

	/**
	 * Sets the book_info meta data
	 */
	function read_book_info() {
		$c=FALSE;
		if (in_array($this->cover, $this->scanned_files)) {
			$this->book_info['cover'] = $this->zip.'#'.$this->prefix.$this->cover;
		} else {
			foreach($this->scanned_files as $f) {
				preg_match('/cover.*\.[jpg][pin][gef][g]?$/i', $f, $cover);
				if ($cover) {
					$c = $f;
					break;
				}
			}
			if ($c) $this->book_info['cover'] = $this->zip.'#'.$this->prefix.$c;
		}
		if (in_array($this->mime_type, $this->scanned_files)) 
			$this->book_info['mime_type'] = file_get_contents($this->zip.'#'.$this->prefix.$this->mime_type);
		
		$two = $this->DOMsearch($this->opf, "/package/metadata|/opf:package/opf:metadata");
		foreach($two as $t) {
			foreach ($t->childNodes as $c) {
				switch ($c->nodeName) {
					case "dc:creator":
						$this->book_info['creator'] = $c->nodeValue;
						break;
					case "dc:title":
						$this->book_info['title'] = $c->nodeValue;
						break;
					case "dc:format":
						$this->book_info['format'] = $c->nodeValue;
						break;
					case "dc:identifier":
						$this->book_info['id'][] = $c->nodeValue;
						break;
					case "dc:rights":
						$this->book_info['rights'] = $c->nodeValue;
						break;
					case "dc:coverage":
						$this->book_info['coverage'] = $c->nodeValue;
						break;
					case "dc:type":
						$this->book_info['type'] = $c->nodeValue;
					break;
					case "dc:description":
						$this->book_info['description'] = $c->nodeValue;
					break;
					case "dc:publisher":
						$this->book_info['publisher'] = $c->nodeValue;
					break; 
					case "dc:language":
						$this->book_info['language'] = $c->nodeValue;
					break;
					case "dc:subject":
						$this->book_info['subject'][] = $c->nodeValue;
					break;
					case "dc:date": // Sat Mar 10 2001 format
						$this->book_info['date'] = date("D M j Y", strtotime($c->nodeValue));
					break;
					case "dc:language":
						$this->book_info['language'] = $c->nodeValue;
					break;
				}
			}
		}
	}

	/**
	 * gets the fairplay encryption information and add it to the manifest
	 */
	function get_encryption() {
		if (in_array($this->encryption, $this->scanned_files)) {
			if (! class_exists ( 'DOMDocument' ))  die('DOMDocument class not found');
			$doc = new DOMDocument();
			$doc->loadXML(file_get_contents($this->zip.'#'.$this->prefix.$this->encryption));
			$enc=$doc->getElementsByTagName('EncryptedData');
			$akeys=array_keys($this->manifest);
			foreach ($enc as $e) {
				$crypt=array();
				$crypt['Algorithm'] = $e->getElementsByTagName('EncryptionMethod')->item(0)->getAttribute('Algorithm');
				$crypt['KeyInfo'] = $e->getElementsByTagName('KeyName')->item(0)->nodeValue;
				$crypt['Cipher'] = $e->getElementsByTagName('CipherReference')->item(0)->getAttribute('URI');
				$key = array_search($crypt['Cipher'], array_column($this->manifest, 'url'));				
				foreach ($crypt as $k => $v)
					$this->manifest[$akeys[$key]][$k] = $v;
			}
		}
	}

	/**
	 * get the signature data and add it to the manifest and book_info
	 */
	function get_signitures() {
		if (in_array($this->signatures, $this->scanned_files)) {
			if (! class_exists ( 'DOMDocument' ))  die('DOMDocument class not found');
			$doc = new DOMDocument();
			$doc->loadXML(file_get_contents($this->zip.'#'.$this->prefix.$this->signatures));
			$this->book_info['CanonicalizationMethod'] = $doc->getElementsByTagName("CanonicalizationMethod")->item(0)->attributes->item(0)->nodeValue;
			$this->book_info['SignatureMethod'] = $doc->getElementsByTagName("SignatureMethod")->item(0)->attributes->item(0)->nodeValue;
			$this->book_info['SignatureValue'] = $doc->getElementsByTagName("SignatureValue")->item(0)->nodeValue;
			$this->book_info['KeyInfo'] = $doc->getElementsByTagName("KeyName")->item(0)->nodeValue;
			$sig=$doc->getElementsByTagName("Reference");
			$ref=array();
			$akeys=array_keys($this->manifest);
			foreach($sig as $s) {
				$URI = $s->attributes->item(0)->nodeValue;
				$key = array_search($URI, array_column($this->manifest, 'Cipher'));
				$this->manifest[$akeys[$key]]['DigestMethod'] = $s->getElementsByTagName('DigestMethod')->item(0)->attributes->item(0)->nodeValue;
				$this->manifest[$akeys[$key]]['DigestValue'] = $s->getElementsByTagName('DigestValue')->item(0)->nodeValue;
			}
		}
	}

	/**
	 * gets the fairplay data and adds it to the book_info
	 */
	function get_sinf() {
		if (in_array($this->sinf, $this->scanned_files)) {
			if (! class_exists ( 'DOMDocument' ))  die('DOMDocument class not found');
			$doc = new DOMDocument();
			$doc->loadXML(file_get_contents($this->zip.'#'.$this->prefix.$this->sinf));
			$this->book_info['fairplay_id'] =  $doc->getElementsByTagName("sID")->item(0)->nodeValue;
			$this->book_info['fairplay_data'] =  $doc->getElementsByTagName("sData")->item(0)->nodeValue;
		}
	}
	
	/**
	 * creates the manifest
	 */
	function get_manifest() {
		$spine=$this->DOMsearch($this->opf, '//spine/itemref|//opf:spine/opf:itemref');
		$order = array();
		foreach ($spine as $s) {
			$item = array();
			foreach($s->attributes as $a) {
				$this->manifest[$a->value]['id'] = $a->value;
				$order[] = $a->value;
				break;
			}
		}

		$toc=$this->DOMsearch($this->opf, '//manifest/item|//opf:manifest/opf:item');

		foreach ($toc as $t) {
			$item = array();
			foreach($t->attributes as $a) $item[$a->name] = $a->value;
			$id = $item['id'];
			switch ($id) {
				case 'ncx': case 'ncxtoc': case 'toc_ncx': case 'toc.ncx':
					$this->toc = $this->zip . '#' . $this->prefix. $this->book_root . $item['href'];
					break;
			}

			if ($id == 'cover-image' && ! array_key_exists('cover', $this->book_info)) {
				$this->book_info['cover'] = $this->zip . '#' .$this->prefix. $this->book_root . $item['href'];
			}
			$this->manifest[$id]['type'] = $item['media-type'];
			$this->manifest[$id]['url'] = $this->book_root . $item['href'];
			$this->manifest[$id]['zip'] = $this->zip . '#' .$this->prefix. $this->book_root . $item['href'];
			$this->manifest[$id]['href'] = $item['href'];
			$this->manifest[$id]['prev'] = '';
			$this->manifest[$id]['rest'] = $this->controller . '/'. $this->book_root . $item['href'];
			
			$this->urlmap['/'. $this->book_root . $item['href']] = $id;
			$this->manifest[$id]['next'] = ''; 
		}
		foreach($order as $k => $id) {
			$next=$k + 1;
			$prev=$k - 1;

			if ($prev >= 0 ) {
				$key=$order[$prev];
				$this->manifest[$id]['prev'] = $this->controller . '/'. $this->book_root . $this->manifest[$key]['href'];
			}
			if ($next < count($order)) {
				$key=$order[$next];
				$this->manifest[$id]['next'] = $this->controller . '/'. $this->book_root . $this->manifest[$key]['href'];
			}
		}
	}

	/**
	 * XPATH DOM search of xml documents
	 * 
	 * @var string $file XML file to read
	 * @var string $q  xpath query
	 */
	function DOMsearch($file, $q) {
        if (! class_exists ( 'DOMDocument' ))  die('DOMDocument class not found');
		if (! class_exists ( 'DOMXpath' ))  die('DOMXpath class not found');
        $doc = new DOMDocument();
		$doc->loadXML(file_get_contents($this->zip.'#'.$this->prefix.$file));
        $xpath = new DOMXpath($doc);
		$xpath->registerNamespace('c', "urn:oasis:names:tc:opendocument:xmlns:container");
		$xpath->registerNamespace('opf', "http://www.idpf.org/2007/opf");
		$xpath->registerNamespace('dc', "http://purl.org/dc/elements/1.1/"); 
		$xpath->registerNamespace('dcterms',"http://purl.org/dc/terms/"); 
		$xpath->registerNamespace('calibre', "http://calibre.kovidgoyal.net/2009/metadata");
		$xpath->registerNamespace('xsi',"http://www.w3.org/2001/XMLSchema-instance");
		return $xpath->query($q);
    }
}