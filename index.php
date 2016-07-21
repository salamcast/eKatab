<?php
function make_books($glob=array(), $ext='epub') {
	foreach ($glob as $b) {
		$p=<<<b
<?php
	require_once 'eKatab/eKatab.class.php';
	new eKatab("$b");
?>
b
;
		$file=basename($b, '.'.$ext).'.php';
		if (!is_file($file)) { file_put_contents($file, $p); }
		echo '<li><a data-ajax="false" href="'.rawurlencode($file).'"  data-transition="flip">'.basename($b, '.'.$ext)."</a></li>";
	}
}

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
    <title>eKatab ebook Reader</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="css/jquery.mobile-1.2.0.css" />
	<script src="js/jquery.js"></script>
	<script src="js/jquery.mobile-1.2.0.min.js"></script>
	<script type="text/javascript">
jQuery(function ($) {
 //HTML


});
	</script>
 </head>
 <body>
	<div data-role="page" data-theme="b" id="$rest">
	 <div data-role="header"  data-position="fixed"><h1 class="title">eBooks Avalible</h1></div>
	 <div data-role="content" id="html">

	  <div id="epub1" data-role="collapsible" data-collapsed="false" data-theme="a" data-content-theme="b">
		 <h3>ePub ebooks </h3>
		 <ul data-role="listview"  data-inset="true">
      <?php make_books(glob('*.epub'), 'epub'); ?>
     </ul>
    </div>

    <div id="ibooks" data-role="collapsible" data-collapsed="true" data-theme="a" data-content-theme="b">
     <h3>iBooks (ibooks) ebooks, not fully supported</h3>
     <ul data-role="listview"   data-inset="true">
      <?php make_books(glob('*.ibooks'), 'ibooks'); ?>
     </ul>
    </div>

    <div id="ipa" data-role="collapsible" data-collapsed="true" data-theme="a" data-content-theme="b">
     <h3>iPhone app (epub) ebooks</h3>
     <backquote>These books are no longer avalible in the Apps store, stopped working properly in iOS6</backquote>
     <ul data-role="listview"  data-inset="true">
      <?php make_books(glob('*.ipa'), 'ipa');  ?>
     </ul>
    </div>



	 </div>
	</div>
 </body>
</html>