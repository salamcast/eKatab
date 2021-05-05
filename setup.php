<?php

include_once('eKatab/eKatab_setup.class.php');

function setup($glob=array()) {
    echo count($glob) . " Books found \n\n";
    if (count($glob) == 0) { 
        return FALSE;
    } 
    foreach ($glob as $e) {
        echo '----------------------------------------------'. "\n";
        $n=str_replace(array("'", ""), array("", ""), $e);
        if ($e != $n) {
            // rename files
            rename($e, $n);
            new eKatab_setup($n);
        } else {
            new eKatab_setup($e);
        }

    }
}


//new eKatab_setup('Books/An-Nawawis Forty Hadith.epub'); exit();


setup(glob('Books/*.epub'));

echo "\n\n##############################################\n";
echo " iBooks will not display the media proerly,\n but you can still read the text \n";
echo '----------------------------------------------'. "\n";
setup(glob('Books/*.ibooks'));
