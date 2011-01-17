<?php
$css = dirname(__FILE__) . '/ie6.css';
$file = file($css);

foreach ($file as $row) {
	echo str_replace('css/images/', get_bloginfo('stylesheet_directory') . '/images/', $row) . "\n";
}
?>