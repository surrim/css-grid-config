<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Surrim\CssGridConfig\CssGridBuilder;

$cssGrid = (new CssGridBuilder('body'))
	->addRegion('#header', 'hh')
	->addRegion('#content', 'cc')
	->addRegion('#footer', 'ff')
	->addBreakpoint(0, [], '
		"hh"
		"cc"
	')
	->addBreakpoint(576, [], '
		"cc"
		"hh"
		"ff"
	')
	->build();
$css = $cssGrid->generate();
$serializedCss = $css->prettySerialize();

header("content-type: text/css");
echo $serializedCss;
