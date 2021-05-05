<?php
include_once 'eKatab/eKatab.class.php';

$html = new jQuery_mobile();
$html->make_books(glob('Books/*.epub'), 'epub');
$html->make_books(glob('Books/*.ibooks'), 'ibooks');
#$html->make_books(glob('Books/*.ipa'), 'ipa');

echo $html;
