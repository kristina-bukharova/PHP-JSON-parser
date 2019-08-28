<?php

include 'JsonParser.php';

$parser = new JsonParser();

function processData($givenDataFile, $newDataFile) {
	global $parser;
	$parser->transformJson($givenDataFile, $newDataFile);
}

processData('./codeTestPage1.json', './updatedTestPage1.json');
processData('./codeTestPage2.json', './updatedTestPage2.json');
processData('./codeTestPage3.json', './updatedTestPage3.json');
processData('./codeTestPage4.json', './updatedTestPage4.json');

	
?>