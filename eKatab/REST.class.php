<?php
/**
 *   RESTphulSrv - RESTful API made easy with PHP5
 * 
 * Copyright 2012 Karl Holz,  http://www.salamcast.com<br />
 * 
 *  Licensed under the Apache License, Version 2.0 (the "License"); <br />
 *  you may not use this file except in compliance with the License.<br />
 *  You may obtain a copy of the License at<br />
 * 
 *       http://www.apache.org/licenses/LICENSE-2.0<br />
 * 
 *  Unless required by applicable law or agreed to in writing, software<br />
 *  distributed under the License is distributed on an "AS IS" BASIS,<br />
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.<br />
 *  See the License for the specific language governing permissions and<br />
 *  limitations under the License.<br />
 * 
 * @author Karl Holz
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2 Licence
 * ---
 */

/**
 * 
 * Basic PHP enviroment parseing for a RESTful PHP based web service or group of resources controlled by one script
 * - Since this is an Abstract class you'll need to extend it with your new one
 *
 * 
 * @package RESTfulSrv
 * 
 * 
 */
abstract class RESTphulSrv {

 /** @var $data array  _GET/_POST processed variable */
	public $data=array();

 /** @var $keys array  keys used in reqest, check againt acl config */
	public $keys=array();   

 /** @var $accept_list */
	public $accept_list=array();
 
/** 
 * 
 * @var $api array a list of found configuration files
 */
	public $api=array();
	
 /**
  * HTTP ERROR Codes
  * 
  * 1xx: Meta, only used for negotiations
  * 2xx: Success
  * 3xx: Redirection
  * 4xx: Client-Side Error
  * 5xx: Server-Side Error
  *
  * @var $ecode array HTTP Error codes with title
  */
 public $ecode=array(										// Importance |
 		'100' => "Continue", 								// Medium 
 		'101' => "Switching Protocols",						// Very Low
 		'200' => "OK", 										// Very High
 		'201' => "Created", 								// High
 		'202' => "Accepted", 								// Medium
 		'203' => "Non-Authoritative Information",			// Very Low
 		'204' => "No Content", 								// High
 		'205' => "Reset Content", 							// Low
 		'206' => "Partial Content", 						// Low
 		'207' => "Multi-Status",							// Low to Medium
 		'300' => "Multiple Choices", 						// Low
 		'301' => "Moved Permanently", 						// Medium
 		'302' => "Found", 									//
 		'303' => "See Other",								// High
 		'304' => "Not Modified", 							// High
 		'305' => "Use Proxy", 								// Low
 		'306' => "Unused", 									// None
 		'307' => "Temporary Redirect",						// High
 		'400' => "Bad Request", 							// High
 		'401'=> "Unauthorized", 							// High
 		'402' => "Payment Required", 						// None
 		'403' => "Forbidden",								// Medium
 		'404'=>"Not Found", 								// High
 		'405'=>"Method Not Allowed", 						// Medium
 		'406'=>"Not Acceptable", 							// Medium
 		'407'=>"Proxy Authentication Required",				// Low
 		'408'=>"Request Timeout", 							// Low
 		'409' => "Conflict", 								// High
 		'410'=>"Gone", 										// Medium
 		'411'=>"Length Required",							// Low to Medium
 		'412'=>"Precondition Failed",						// Medium
 		'413'=>"Request Entity Too Large", 					// Low to Medium
 		'414'=>"Request-URI Too Long", 						// Low
 		'415'=> "Unsupported Media Type",					// Medium
 		'416'=> "Requested Range Not Satisfiable", 			// Low
 		'417'=>"Expectation Failed",						// Medium 
 		'500' => "Internal Server Error", 					// High
 		'501' => "Not Implemented", 						// Low
 		'502' => "Bad Gateway", 							// Low
 		'503'=>"Service Unavailable",						// Medium to High
 		'504' => "Gateway Timeout", 						// Low
 		'505' => "HTTP Version Not Supported"				// Very Low
 );
 
 /**
  * http header, send http header info
  * 
  * @param string $code http status code
  * @param string $type mime type
  * @param bool $cache is cacheing disabled
  * @todo add ETag support for caching support, maybe an ETag uuid?
  */
 function http_header($code="200", $type="application/json", $cache=FALSE) {
 	if (!headers_sent($filename, $linenum)) {
 		header("HTTP/1.0 ".$code." ".$this->ecode[$code]);
		if (!$cache) {
 			header('Cache-Control: no-cache, must-revalidate');
 			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		}
 		header('Content-type: '.$type);
 		// You would most likely trigger an error here.
 	} else {
		if ($this->debug) {
 			echo "Headers already sent in $filename on line $linenum\n" .
 			"Cannot redirect, for now please click this <a href=\"$this->base_uri\">link</a> instead\n";
    		exit();
		}
	}	
 }
 
 
 public $ini=array();
 
 /**
  * Make ini text
  */
 
 function make_ini() {
 	$ini='';
 	foreach ($this->ini as $k => $v ) {
 		$ini.='['.$k.']'."\n";
 		foreach($v as $kk => $vv) {
 			if (!is_array($vv)) {
 				$ini.=$kk.'="'.$vv.'"'."\n";
 			} else {
 				foreach ($vv as $n =>$vvv) $ini.=$kk.'[]="'.$vvv.'"'."\n";
 			}
 		}
 	}
 	return $ini;
 }
 
 /**
  * if it's true, auto invoke class
  * 
  * @param bool $auto
  */
 function __construct($auto=FALSE) {
  if ($auto) { $this->auto_invoke(); }
 }
 
 function  DebugREST($n=__CLASS__) {
 	if ($this->debug) {
 		if (! is_dir($this->debug_dir)) {
 			mkdir($this->debug_dir,0700,TRUE);
 		}
 		// this args
 		$a=print_r($this->arg, TRUE);
 		// post
 		$p=print_r($_POST,TRUE);
 		// get
 		$g=print_r($_GET,TRUE);
 		// server
 		$s=print_r($_SERVER,TRUE);
 		// this object
 		$t=print_r($this,TRUE);
 		// cookie
 		$c=print_r($_COOKIE,TRUE);
 		//session
 		$S='';
 		if (isset($_SESSION)) $S=print_r($_SESSION,TRUE);
 		// headers
 		$r=print_r(apache_request_headers(), TRUE);
 		// input doc
 		$d=$this->put;
 		$now=date('Y-m-d|H.i');
 		$debug=<<<d
$n
-------------------------
Time Stamp: $now
-------------------------
	\$this->arg
#########################
$a
-------------------------
 	_POST
#########################
$p
-------------------------
 	_GET
#########################
$g
-------------------------
 	_SERVER
#########################
$s
-------------------------
 	This Object
#########################
$t
-------------------------
 	_Cookie
#########################
$c
-------------------------
 	_Session
#########################
$S
-------------------------
 	headers
#########################
$r
-------------------------
 	input doc
#########################
$d
d;
 		if ((! $this->debug_get) && ($this->method == 'GET')) { return TRUE; }
 		$file=$this->debug_dir.'/'.$this->method.'|'.$now.'.txt';
 		file_put_contents($file, $debug);
 	}
 }
 /**
  * Keep it restful
  *  - kill sessions and cookies
  *  - use HTTP AUTH , use HTTPS if you want more security
  *  - must be called manually; most frameworks and PHP Platforms depend on Sessions and Cookies, so lets not break them!
  *  @TODO add more items to improve the restful ness of these services
  *  
  */

function keep_it_restful() {
	unset($_COOKIE);
	session_destroy();
	unset($_SESSION);
}

/**
 * invoke the default processing of this class
 */ 
 function auto_invoke($cli = false) {
 	$this->auto_rest();
 	// auto config
	
 	$this->auto_controller(); 
 	$this->auto_collection(); 
 	$this->auto_debug_dir();
 	$this->auto_uri();
 	$this->auto_file();
 	$this->auto_dir();
 	$this->auto_ini();
	 if (!$cli) {
		$this->auto_agent();

		$this->auto_host();
		$this->auto_query();;
		$this->auto_http_accept();		
		$this->auto_http_auth();
		$this->auto_input();
		$this->auto_username();
		$this->auto_password();
 	//process auth and request input
	 	$this->process_http_auth();
 		$this->process_http_request();
	}

 	$this->auto_docroot();

 	// basic preset values, can be overridden by the API config
 	$this->mime_type='application/json';
 	$this->realm="Basic RESTfulPHP Web Auth"; 

 }
 
 function find_api_config() {
 	$match=".htaccess.ini";
 	$root=$this->auto_collection();
	return glob('{ '.$root.'/*/'.$match.', '.$root.'/*/*/'.$match.', '.$root.'/*/*/*/'.$match.', '.$root.'/*/*/*/*/'.$match.', '.$root.'/*/*/*/*/*/'.$match.', '.$root.'/*/*/*/*/*/*/'.$match.' }', GLOB_BRACE);
 }
 
 /**
  * @var $arg mixed used for __get() and __set() functions
  */
  public $arg=array();
 
 /**
  * Set class value
  * 
  * @param string $name
  * @param string $value set values as is except when name is the following:
  * - username => name check min 4 characters
  * - password => check min 8 characters Alpha Numaric and some specal chars
  */
 function __set($name, $value) {
 	switch ($name) {
 		case 'username': if (strlen($value) > 3 && ctype_alnum($value)) { $this->arg[$name]=$value; } else { $this->arg[$name]=FALSE; } break;
 	 	case 'password': if (preg_match('/[a-zA-Z0-9-_!@#$%^%&*();:,.<>?|]{8,}/', $value)) { $this->arg[$name]=$value; } else { $this->arg[$name]=FALSE; } break;
 	 	default: $this->arg[$name]=$value;
 	}
 }
 
 /**
  * Get Class value
  * 
  * @param string $name  if $name is with in the switch stament case names:
  * 
  *  - full_url => Current URI, uses  uses this_host and this_uri
  *  - base_url => Base URI, uses this_host and script
  *  - method => returns the current HTTP method being used, it can't be overridden
  *  - *all the rest* search \$this->arg
  * 
  *  all these variables can be over ridden with the set function
  * @return string|boolean if the value is not set return false 
  */
 function __get($name) {

 	if ($name == 'full_url' ) { return $this->host.$this->full_uri; } 
 	elseif ($name == 'base_url' ) { return $this->host.$this->controller; } 
 	elseif ($name == 'method' && array_key_exists('REQUEST_METHOD', $_SERVER)) { return $_SERVER['REQUEST_METHOD']; } 
 	elseif (array_key_exists($name, $this->arg)) { return $this->arg[$name]; }
 	return FALSE;
 }
 
 /**
  * Browser Agent 
  * 
  * => _SERVER["HTTP_USER_AGENT"] <=
  * @todo add some parsing for matching clients like the iWork iPhone app
  * 
  * @return string
  */
 function auto_agent() {
 	$this->client_name=$_SERVER["HTTP_USER_AGENT"];

 	return $this->client_name;
 }
 /**
  * WebService Storage directory for class files
  * 
  * => _SERVER['SCRIPT_NAME'] <=
  * @return string|void
  */
 function auto_collection() {
 	if (array_key_exists('SCRIPT_FILENAME', $_SERVER))
 		$this->controller_root=dirname($_SERVER['SCRIPT_FILENAME']);
 	return $this->controller_root;
 }
 
 /**
  * WebService Controller name/file
  *  
  * => _SERVER['SCRIPT_NAME'] <=
  * @return string|void
  */
 function auto_controller() {
 	if (array_key_exists('SCRIPT_NAME', $_SERVER)) 
 		$this->controller=$_SERVER['SCRIPT_NAME'];
 	return $this->controller;
 }
 
/**
 * auto_debug_dir 
 * 
 * => folder for Debuging information for your HTTP and WEBDAV METHODS used with your services
 * @return void|string
 */
 
 function auto_debug_dir(){
 	if (array_key_exists('SCRIPT_FILENAME', $_SERVER)) {
 		$d=dirname($_SERVER['SCRIPT_FILENAME'])."/debug.".basename($_SERVER['SCRIPT_FILENAME'],'.php');
 		if ($this->debug && !is_dir($d)) mkdir($d,0700);
 		$this->debug_dir=$d;
 	}
	return $this->debug_dir;
 }
 
 /**
  * auto_uri 
  * 
  * => _SERVER["REQUEST_URI"] <= returns full uri with requests and all.
  * @return void|unknown
  */
 function auto_uri() { 
 	if (array_key_exists("REQUEST_URI", $_SERVER))
	 	$this->full_uri=$_SERVER["REQUEST_URI"];
 	return $this->full_uri; 
 }
 
 /**
  * auto_file 
  * 
  * => $_SERVER["PATH_TRANSLATED"] <= checks for a file along the request uri
  * @return void|unknown|string
  */
 function auto_file() {
 	if (array_key_exists("PATH_TRANSLATED", $_SERVER) && is_file($_SERVER["PATH_TRANSLATED"])){
 		$this->direct_match=TRUE;
 		$this->file=$_SERVER["PATH_TRANSLATED"];
 	} elseif (is_file($this->controller_root.$this->rest)) {
 		$this->file=$this->controller_root.$this->rest;
 	} elseif (is_file($this->controller_root.$this->store.'/'.$this->document)) {
 		$this->file=$this->controller_root.$this->store.'/'.$this->document;
 	}
 	return $this->file;
 }
 
 /**
  * auto_dir 
  * 
  * => $_SERVER["PATH_TRANSLATED"] <= checks for a directory along the request uri
  * @return string
  */
 
 function auto_dir(){
 	if (array_key_exists("PATH_TRANSLATED", $_SERVER) && is_dir($_SERVER["PATH_TRANSLATED"])){
 		$this->direct_match=TRUE;
 		$this->pwd=$_SERVER["PATH_TRANSLATED"];
 	} elseif (is_dir($this->controller_root.$this->rest)) {//controller_root
 		$this->pwd=$this->controller_root.$this->rest;
 	} elseif (is_dir($this->controller_root.$this->store) && $this->store != '/') {
 		$this->pwd=$this->controller_root.$this->store;
 	}
 	return $this->pwd;
 }
 
/**
 * auto_ini 
 * 
 * => an INI config file ref based on _SERVER['SCRIPT_FILENAME'] with a .ht. file prefix to hide by default in Apache HTTPD
 * @return string
 */
 function auto_ini() {
 	if (array_key_exists('SCRIPT_NAME', $_SERVER)) {
 		if (array_key_exists("PATH_TRANSLATED", $_SERVER) && is_file($_SERVER["PATH_TRANSLATED"]."/.ht.".basename($_SERVER['SCRIPT_FILENAME'],'.php').'.ini')) {
 			$this->inifile=$_SERVER["PATH_TRANSLATED"]."/.ht.".basename($_SERVER['SCRIPT_FILENAME'],'.php').'.ini';
 		} elseif(is_file($this->controller_root.$this->store."/.ht.".basename($_SERVER['SCRIPT_FILENAME'],'.php').'.ini')  && $this->path != '/') {
 			$this->inifile=$this->controller_root.$this->store."/.ht.".basename($_SERVER['SCRIPT_FILENAME'],'.php').'.ini';
	 	} else {
 			$this->inifile=$this->controller_root."/.ht.".basename($_SERVER['SCRIPT_FILENAME'],'.php').'.ini';
 		}
 	}
 	return $this->inifile;
 }
 
 /**
  * auto_host 
  * 
  * => _SERVER["HTTP_HOST"] <= if _SERVER['HTTPS'] is set than use https://
  * @return string
  */
 function auto_host() {
 	if (array_key_exists('HTTPS', $_SERVER) && isset($_SERVER['HTTPS'])) { 
 		$this->host="https://".$_SERVER["HTTP_HOST"]; 
 	} else { 
 		$this->host="http://".$_SERVER["HTTP_HOST"]; 
 	}
 	return $this->host;
 }
 
 function auto_docroot(){
 	if (array_key_exists("DOCUMENT_ROOT", $_SERVER)) 
 		$this->webroot=$_SERVER["DOCUMENT_ROOT"];
 	return $this->webroot;
 }
 
 /**
  * auto_http_accept 
  * 
  * => _SERVER['HTTP_ACCEPT'] <= list of accecptable file types, defaults to json
  * @return string
  */
 function  auto_http_accept() {
 	if (array_key_exists('HTTP_ACCEPT', $_SERVER)) {
 		$this->accept_list=explode(',', $_SERVER['HTTP_ACCEPT']);
 		$this->accept=$_SERVER['HTTP_ACCEPT'];
 	} else {
 		$this->accept='application/json';
 		$this->accept_list=array( 'application/json', 'text/plain', 'text/xml' );
 	}
 	return $this->accept;
 }

 /**
  * auto_http_auth 
  * 
  * => _SERVER['HTTP_AUTHORIZATION'] <= or => _SERVER['REDIRECT_HTTP_AUTHORIZATION'] <=
  * @return string
  */
 function auto_http_auth() {
 	if (array_key_exists('HTTP_AUTHORIZATION', $_SERVER) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
 		$this->auth=$_SERVER['HTTP_AUTHORIZATION'];
 	} elseif (array_key_exists('REDIRECT_HTTP_AUTHORIZATION', $_SERVER) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
 		$this->auth=$_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
 	}
 	return $this->auth;
 }
 
 /**
  * auto_rest 
  * 
  * => process rest uri path
  * 
  * this will create the uuids and set the collection, store and document based on the path portion of the uri 
  * @return boolean
  */
 function auto_rest() {
 	if (array_key_exists('ORIG_PATH_INFO', $_SERVER)) { $this->rest=rawurldecode($_SERVER['ORIG_PATH_INFO']); }
 	elseif (array_key_exists('PATH_INFO', $_SERVER)) { $this->rest=rawurldecode($_SERVER['PATH_INFO']); }
 	else { $this->rest='/'; }
 	if ($this->rest != '/') {
 		$uri=explode('/',$this->rest);
 		array_shift($uri); //blank before first '/'
 		
 		$this->collection=array_shift($uri);
 		$this->document=array_pop($uri);
 		$this->store='/'.join('/',$uri);
 		$this->uuid_uri=$this->uuid($this->rest, 'uri'); 
 		$this->uuid_collection=$this->uuid($this->collection, 'collection'); 
 		$this->uuid_document=$this->uuid($this->document, 'document'); 
 		$this->uuid_store=$this->uuid($this->store, 'store');
 		
 	}
 	return TRUE;
 }
 /**
  * auto_input 
  * 
  * => Process PUT data to your PHP script
  * @return string
  */
 
 function auto_input() {
 	$putdata = fopen("php://input", "r");
 	if (! function_exists('stream_get_contents')) {
 		$this->error='stream_get_contents not found';
 		$this->error('php_fail');
 	}
 	$x=stream_get_contents($putdata);
 	fclose($putdata);/* Close the streams */
 	$this->put=$x;
 	return $this->put;
 }
 
 /**
  * auto_query 
  * 
  * => parse the string and replace '&' with ',' in _SERVER['QUERY_STRING']
  * @return void|string
  */
 function auto_query() {
 	if (array_key_exists('QUERY_STRING', $_SERVER)) 
 		$this->query=rawurldecode($_SERVER['QUERY_STRING']);
 	return $this->query;
 }
 
 /**
  * auto_username 
  * 
  * => _SERVER['PHP_AUTH_USER']
  * @return void|string
  */
 function auto_username() {
 	if (array_key_exists('PHP_AUTH_USER', $_SERVER)) 
 		$this->username=$_SERVER['PHP_AUTH_USER'];
 	return $this->username;
 }

 /**
  * auto_password 
  * 
  * => _SERVER['PHP_AUTH_PW']
  * @return void|string
  */
 function auto_password() {
 	if (array_key_exists('PHP_AUTH_PW', $_SERVER)) 
 		$this->password=$_SERVER['PHP_AUTH_PW'];
 	return $this->password;
 }
   /**
    * Genarates an UUID 
    * 
    *  - borrowed from Anis uddin Ahmad's Universal FeedGerator, modified by Karl Holz
    * 
    * @param	  string $key value to hash
    * @param      string $prefix an optional prefix to Hash, use Class name as default
    * @return     string  the formated uuid
    */
  function uuid($key = null, $prefix =__CLASS__) {
      $key = ($key == null)? $this->base : $key;
      $chars = md5($key);
      $uuid  = substr($chars,0,8) . '-';
      $uuid .= substr($chars,8,4) . '-';
      $uuid .= substr($chars,12,4) . '-';
      $uuid .= substr($chars,16,4) . '-';
      $uuid .= substr($chars,20,12);
      return $prefix .'/'. $uuid;
   }
  
 /**
  * process HTTP Request methods
  * 
  *  - GET => none of these vales should be used to change any data, only query and toggle ui 
  *  - POST => should be for creating new or updating items into the database
  *  - PUT => data comes in on the php://input stream, should be used for replacing resources and creating new resources
  *  - DELETE => parses qurey string for DELETE HTTP Method, no _GET is populated
  *  - PROPFIND => used by webdav clients, like iWork publishing
  * @todo add webdav support, atleast for PROPFIND
  * @return boolean|void defaults to error page if not a proper HTTP METHOD used, returns true if no error
  */
   
 function process_http_request() {
  //process request to be used in extended class
  switch($this->method) {
   case 'OPTIONS': $this->process_uri_query_str(); break;
//   case 'TRACE':  break;
   case 'HEAD': $this->process_uri_query_str(); break;
   case 'GET':  $this->process_array($_GET); break; 
   case 'POST': $this->process_array($_POST); break; 
   case 'PUT':  break; 
   case 'DELETE': 
   	$this->process_uri_query_str(); 
   break;
   case 'PROPFIND': 
   	$this->process_uri_query_str();
   break;
   default:
	$this->error=$this->method;
	$this->error('method');
  }
  return TRUE;
 }

 /** 
  * Process HTTP BASIC AUTH
  * 
  *  - sets PHP_AUTH_USER and PHP_AUTH_PW from HTTP_AUTHORIZATION
  * @link http://ca2.php.net/manual/en/features.http-auth.php#106285
  * @return boolean returns true always
  */
 
 function process_http_auth() {
  // Parse Basic Authentication
  if(isset($this->auth) && preg_match('/Basic\s+(.*)$/i', $this->auth, $matches)) {
   list($name, $password) = explode(':', base64_decode($matches[1]));
   $_SERVER['PHP_AUTH_USER'] = strip_tags($name); 
   $_SERVER['PHP_AUTH_PW'] = strip_tags($password);  
  }
  return TRUE;
 }
 
 /**
  * process any key=>value pairs
  * 
  * useful for $_GET and $_POST arrays
  * 
  * @param array $arg
  * @return boolean fails if not an array with at least 1 item
  */
 
 function process_array($arg) {
 	if (! is_array($arg)) { return FALSE; }
    if (count($arg) < 1) { return FALSE; }
    foreach (array_keys($arg) as $k){ 
    	if (count($this->keys) < 1) { // if keys is empty then allow all
    		$this->data[$k]=$arg[$k]; 
    	} else { // if the post/get keys are not defined then don't add to data list
    		if (array_key_exists($k, $this->keys)){ $this->data[$k]=$arg[$k]; }  
    	}	
    }
    return TRUE;
 }
 
 /**
  * Process the QUERY_STRING 
  * 
  *  - ideal for when _GET is not populated by PHP 
  * 
  * rested.app on mac forces a query sring in DELETE request, there is no flexiblity with this HTTP METHOD in that tool
  * 
  * @return boolean always returns true
  */

 function process_uri_query_str() {
 	$d=explode('&', $this->query);
 	foreach ($d as $k) { 
 		$e=explode('=', $k); 
 		if (count($e) == 2) {
 			$this->data[$e[0]]=$e[1];
 		} 
 	}
 	$this->keys=array_keys($this->data);
 	return TRUE;
 }
  
 /**
  * Rest URI
  * @param string $rest rest resource
  * @return string RESTful URI 
  */
 function rest_uri($rest) { 
 	return $this->base_uri.$rest; 
 }
  
 /**
  * Basic HTTP Auth
  * @return boolean
  */
 function basic_auth() {
 	if (! $this->username) {
 		header('WWW-Authenticate: Basic realm="'.$this->realm.'"');
 		header('HTTP/1.0 401 Unauthorized');
		return FALSE; 	
 	}
 	return TRUE;
 }
 
 /**
  * Clears http auth and logs user out
  * 
  * only clear HTTP auth if _GET['clear'] is set, then redirect to script
  * 
  * @return void cancel HTTP Auth and exit
  */
 function cancel_auth(){
 	if (array_key_exists('clear', $_GET) && isset($_GET['clear'])) {
  		unset($_SERVER['PHP_AUTH_USER']); unset($_SERVER['PHP_AUTH_PW']); unset($_GET);
   		header('WWW-Authenticate: Basic realm="Cancel to logout"');
   		header('HTTP/1.0 401 Unauthorized');
   		header("Location: ".$this->controller); 
   		exit();
  	}    
 }
 
 /**
  * write .htaccess file to script root, only use if you want this script to conroll the directory
  *
  * This is useful for createding applications with out the script name in the URI
  * @return bool
  */
 function make_htaccess_file() {
 	$worker=$this->controller;
	$ht=<<<HT
Options FollowSymLinks
RewriteEngine On
RewriteRule ^([^.]+)$ $1 [QSA]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ $worker [QSA,L]  
HT
	;
	$file=$this->controller_root.'/.htaccess';
	if (! function_exists('file_put_contents')) {
		$this->error='file_put_contents not found';
		$this->error('php_fail');
	}
	file_put_contents($file, $ht);
	return true;
 }
 
 
 function zip_resorces($zip){
 	if (! class_exists('ZipArchive')) {
 		$this->error='ZipArchive not found';
 		$this->error('php_fail');
 	}
 	$za = new ZipArchive();
 	$za->open($zip);
 	$list=array();
 	for ($i=0; $i<$za->numFiles;$i++) {
 		$z=$za->statIndex($i);
 		//META-INF/container.xml
 		$list[]=$z['name'];
 	}
 	$za->close();
 	return $list;
 }
 
 /**
  * Apply xslt template to xml data 
  *   - you can send params to your xslt style sheet like this array('a' => 'a value', 'b' => 2);
  *   - you can write the output to a file 
  *
  * @param string $xsltmpl XSLT stylesheet to be applied to XML
  * @param string $xml_load XML data
  * @param array $param to be passed to XSLT style sheet
  * @param string $file absolute path
  * @return string|boolean|void new document from transformed XML data or fail
  */
 function xsl_out($xsltmpl, $xml_load, $param=array(), $file='') {
 	if (! class_exists('DOMDocument')) {
 		$this->error='DOMDocument not found';
 		$this->error('php_fail');
 	}
 	$xml=new DOMDocument();
	if (!is_file($xml_load) ){ 
		if (! $xml->loadXML($xml_load)) { 
			
   			$this->error=$xml_load;
   			$this->error('xml_fail'); 
   		}
	} else { 
		if (! $xml->load(realpath($xml_load))) { 
   			$this->error=$xml_load;
   			$this->error('xml_fail'); 
   		}
  	}
  	//loads XSL template file
  	$xsl=new DOMDocument();
  	if (!is_file($xsltmpl)) { 
  		if(! $xsl->loadXML($xsltmpl)) { 
   			$this->error=$xsltmpl;
   			$this->error('xslt_fail'); 
   		}
  	} else {
   		if(! $xsl->load(realpath($xsltmpl))) { 
   			$this->error=$xsltmpl;
   			$this->error('xslt_fail'); 
   		}
  	}
  	//process XML and XSLT files and return result
  	if (! class_exists('XSLTProcessor')) {
  		$this->error='XSLTProcessor not found';
  		$this->error('php_fail');
  	}
  	$xslproc = new XSLTProcessor();
  	if (is_array($param) && count($param) > 0) { 
  		$xslproc->setParameter('', $param); 
  	}
  	$xslproc->importStylesheet($xsl);
  	if ($file != '') {
   		if ($xslproc->transformToURI($xml, 'file://'.$file)) { 
   			return true; 
   		} else { 
			$this->error=$file."<br />".$xml;
			$this->error('output_file_xslt');
   		}
  	} else {
    	return $xslproc->transformToXml($xml);
  	}
 }

 /**
  * Error Page with jQuery mobile
  *  - much better than plain text
  *
  *	@param string $e error key
  * @return void prints error page
  */
 function error($e) {
 	switch ($e) {
 		case 'php_fail':
 			$code='500';
 			$msg='PHP function or class missing';
 		break;
 		case 'xml_fail':
 			$code='500';
 			$msg='XML failed to load';
 		break;
 		case 'xslt_fail':
 			$code='500';
 			$msg='XSLT failed to load';
 		break;
 		case 'output_file_xslt':
 			$code='500';
 			$msg="Couldn't write file to directory";
 		break;
 		case 'method':
 			$code='405';
 			$msg="Something went wrong! HTTP method is unknow, could be supported later on if it's useful, Inshallah";
 		break;
 		default: $msg="Unknow Error in ".__CLASS__." | ".$e; $code='404';
 	}
// 	$this->http_header($code, 'text/html');
?>
	<div data-role="page">
	    <div data-role="header"><h1><?php echo __CLASS__; ?> Error  | <?php echo $e; ?></h1></div> 
	    <div data-role="content" >
		<div data-role="collapsible" data-collapsed="false" data-theme="a" data-content-theme="b">
		    <h3>Error:</h3>
		    <pre><?php echo HtmlSpecialChars($msg);?></pre>
		    <pre><?php if ($this->error) { echo $this->error; } ?></pre>
		</div>
		<?php if ($this->debug) {   ?>
		<div data-role="collapsible" data-collapsed="true" data-theme="a" data-content-theme="b">
		    <h1>Debug class</h1><pre class="this"><?php echo print_r($this, TRUE); ?></pre>
		</div>
		<div data-role="collapsible" data-collapsed="true" data-theme="a" data-content-theme="b">
		    <h1>_Server</h1><pre class="server"><?php echo print_r($_SERVER, TRUE); ?></pre>
		</div>
		<div data-role="collapsible" data-collapsed="true" data-theme="a" data-content-theme="b">
		    <h1>_GET</h1><pre class="get"><?php echo print_r($_GET, TRUE); ?></pre>
		</div>
		<div data-role="collapsible" data-collapsed="true" data-theme="a" data-content-theme="b">
		    <h1>_POST</h1><pre class="post"><?php echo print_r($_POST, TRUE); ?></pre>
		</div>
		<?php } ?>
	</div> 

    <?php 
	exit();
 }
 
}

/**
 * apache_request_headers if not
 * @link http://ca2.php.net/manual/en/function.getallheaders.php#99814 
 */
	if (!function_exists('apache_request_headers')) {
		function apache_request_headers() {
			foreach($_SERVER as $key=>$value) {
				if (substr($key,0,5)=="HTTP_") {
					$key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
					$out[$key]=$value;
				}else{
					$out[$key]=$value;
				}
			}
			return $out;
		}
	}
	
	/**
	 * RESTfulBugger debuging your WebServer enviroment
	 * this shold help you in buding web clients and web services
	 * The debuger by default logs all POST, PUT and DELETE requests, 
	 * so requests made with a test client can be logged, think about using JAM! Lite app for a non Joomla site
	 * @author Karl Holz
	 *
	 */

	class RESTfulBugger extends RESTphulSrv {	
		function __construct($api) {
			$this->debug=TRUE;
				
			if (is_file($api) && function_exists('parse_ini_file')) {
				$this->api=parse_ini_file($api, TRUE);
			} elseif (is_array($api) ) {
				// a simple key value list php arrays like
				// 'section' => array('item' => 'value') or
				// 'section' => array('items' => array('8', '6'), 'days' => array('24', '27'))
				$this->api=$api;
			} elseif (function_exists('parse_ini_string')) {
				  $this->api=parse_ini_string($api, TRUE);
			}
			//	if ($this->this_host.$this->full_uri != $this->base_uri) $this->debug_get=TRUE;
			$tmp='';

			parent::__construct(TRUE);
			$this->DebugREST();
		}
	
	
	}
	
?>