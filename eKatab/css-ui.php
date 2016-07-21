<?php
/**
 * @authour karl holz
 * @package css_one
 * @date June 27, 2012

Copyright (c) 2012 Karl Holz <newaeon|(a)|mac|d|com>

CSS_One has been created by Karl Holz, any borrowed functions have been 
noted in the code comments with a link to the origonal page.


Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

  
 * 
 */

class css_one {
    // match css
    private $match='/[A-Za-z0-9%.,_-]+?\.css/';
    // encoded image holder
    private $img=array();
    // image encoded types
    private $type=array('jpg', 'gif', 'png');
    // styles list
    public  $style=array();
    // javascript list
    public  $js=array();
    // atom feed list
    public  $atom=array();
    // rss feed list
    public  $rss=array();
    /**
     * __set img array
     * @param type $name
     * @param type $value 
     */
    function __set($name, $value) {
        $this->img[$name]=$value;
    }
    
    /**
     * __get item if the key exists
     * @param type $name
     * @return type 
     */
    function __get($name) {
        if (array_key_exists($name, $this->img)) {
            return $this->img[$name];
        } else {
            return;
        }
    }
    
    /**
     * __construct boot strap the class
     * @param type $feed
     * @return boolean 
     */
    function __construct() {
        $this->dir=dirname(__FILE__);
        $this->id=__CLASS__;
        $this->HTML5=TRUE;
        $this->css='';
        
        $this->title="CSS One - jQuery UI demo tester";
        $this->description="This class will look for images in the css directory and replace them in the CSS file";
        $this->keywords="HTML5, css, base64 images, phpclasses";
        // default for stopping robots
        $this->robots='noindex,nofollow';
        
    }

    /**
     * set jQuery js script or http link
     * @param type $j 
     */
    function set_jquery($j='') {
        if ($j != '') { 
            $this->js[]=$j; 
        } else { 
            $this->js[]='https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'; 
        }        
    }
    
    /**
     * set jQuery-ui js script or http link
     * @param type $j 
     */
    function set_jquery_ui($ui='') {
        if ($ui != '') { 
            $this->js[]=$ui; 
        } else { 
            $this->js[]='https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js'; 
        }   
    }

  /**
   * add style sheet
   * @param type $css 
   */
  function add_style($css) {
      $this->style[]=$css;
  }
  
  /**
   * add javascript
   * @param type $js 
   */
  function add_js($js) {
      $this->js[]=$js;
  }
  
  /**
   * add ATOM link
   * @param type $t
   * @param type $l 
   */
  function add_atom($t, $l){
        $this->atom[$t]=$l;
  }
  
  /**
   * add RSS link
   * @param type $t
   * @param type $l 
   */
  function add_rss($t,$l){
        $this->rss[$t]=$l;
    }

  /**
   * load html widget template
   * @param type $h 
   */
  function load_body($h) {
      if (is_file($h)) {
        $this->body=file_get_contents($h, true);
      } else {
        $this->body=file_get_contents($h);
      }
      
  }
    
    /**
     * generate a glob string for all img types
     * @param string $d css file
     */ 
    function get_img_glob($d) {
        $c='{';        
        foreach ($this->type as $t) {
            $c.=$this->get_css_dir($d).'*.'.$t.','.$this->get_css_dir($d).'*/*.'.$t.',';
        }
        $c=rtrim($c, ',');
        $c.='}';
        return $c;
    }
    
    /**
     * get the CSS directory for image searching
     * @return string 
     */
    function get_css_dir($d) {
        return $this->dir.preg_replace($this->match, '', trim($d, '.'));
    }
    /**
     * encode image list for faster stlye loading
     * 
     * borrowed from jeff [dot] jbrowns [at] com 28-Feb-2012 06:36
     * http://www.php.net/manual/en/function.base64-encode.php#107705
     * 
     * @param type $imagefile
     * @return type 
     */
    function base64_encode_image ($imagefile) {
        $filetype = strtolower(pathinfo($imagefile, PATHINFO_EXTENSION));
        if (in_array($filetype, $this->type)){
            $imgbinary = fread(fopen($imagefile, "r"), filesize($imagefile));
        } else {
            return $imagefile;
        }
        return 'data:image/'.$filetype.';base64,'.base64_encode($imgbinary);
    }
    
    /**
     * encode all images found in the css directory 
     */
    function encode_img($c) {
        $imgs=array(); 
        $dir=glob($this->get_img_glob($c), GLOB_BRACE );
        foreach ($dir as $d) {
            $imgs['file'][]=str_replace($this->get_css_dir($c), '', $d);
            $imgs['encode'][]=$this->base64_encode_image($d)  ;
        }
        $this->imgs=$imgs;
        return $imgs;
    }

    /**
     * Print CSS output with images encoded into the style 
     */
    function printCSS() {
        header("Content-type: text/css");
        $this->makeOneCSS();
        echo $this->css;
        exit();
    }
    
    /**
     * make One CSS, combines all css files and embed images
     * @return type 
     */
    function makeOneCSS () {
        if (count($this->style) < 1 ) return ;
        foreach ($this->style as $s) {
         $c=@file_get_contents($s);  
         $this->encode_img($s);
         $css=$this->compress($c);
         if (array_key_exists('file', $this->imgs) && array_key_exists('encode', $this->imgs)) {
            $this->css.=str_replace($this->imgs['file'], $this->imgs['encode'], $css);
         } else { $this->css.=$css; } 
        }

        return ;
    }
    
  /**
   * I found this online, looked cool so I borrowed it, check the link bellow
   * The Reinhold Weber method
   * -http://www.catswhocode.com/blog/3-ways-to-compress-css-files-using-php
   * 
   * this will strip out all comments and newlines,tabs,plus uother un-needed space wasters.
   * 
   * @param type $buffer
   * @return type 
   */
  function compress($buffer) {
    /* remove comments */
    $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
    /* remove tabs, spaces, newlines, etc. */
    $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
    return $buffer;
  }

 /**
  * xHTML/HTML5 __toString output
  * set to false for xHTML/css/jquery 
  */
 function __toString() {
//  $css=$this->makeOneCSS();  
  if (stristr($_SERVER['HTTP_ACCEPT'], "application/xhtml+xml") && $this->HTML5 !== TRUE) {
   $this->mime="application/xhtml+xml";
   header("Content-Type: ".  $this->mime);
   print '<?xml version="1.0" encoding="utf-8"?>';
   $ns='xmlns="http://www.w3.org/1999/xhtml"';
  } else {
   $this->mime="text/html";
   header("Content-Type: ".  $this->mime);
   print '<!DOCTYPE HTML>';
   $ns='';
  }
  $js='';

  if (count($this->style) > 0) {
   foreach ($this->style as $j) { //append css style
  $js.='<link class="css" type="text/css" href="'.$j.'" rel="stylesheet" />'."\n";   }   
  }
  if (count($this->js) > 0) {
   foreach ($this->js as $j) { //append JavaScript
    $js.='<script type="text/javascript" src="'.$j.'"></script>'."\n";
   }   
  }
  if (count($this->atom) > 0) {
   foreach ($this->atom as $k => $j) { //append atom feeds
    $js.='<link class="atom" type="application/atom+xml" href="'.$j.'" rel="alternate" title="'.$k.'" />'."\n";
   }   
  }
  if (count($this->rss) > 0) {
   foreach ($this->rss as $k => $j) { //append rss feeds
    $js.='<link class="rss" type="application/rss+xml" href="'.$j.'" rel="alternate" title="'.$k.'" />'."\n";
   }   
  }
  return <<<H
<html $ns >
 <head>
  <title>$this->title</title>
  <meta http-equiv="Content-Type" content="$this->mime; charset=utf-8" />
  <meta http-equiv="Content-Language" content="en-us" />
  <!-- Load Javascript and CSS files to this basic HTML skel layout. -->
  <!-- let you're JavaScript and CSS build and manage the UI -->
  $js
 </head>
 <body>
  $this->body
 </body>
</html>
H
;

 }

}

?>
