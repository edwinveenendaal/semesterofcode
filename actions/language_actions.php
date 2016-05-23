<?php
include('include.php');//Includes the necessary bootstrapping and the ajax functions
	/*
	 * This is how to call a single translation
	$jq.get( url('language','translate'), { word: 'your_word_to_translate' }, function(result) {
		alert(result);
	});

	* This is how to call a multiple word translation
	$jq.get( url('language','translate'), { words: ['one','two','three'] }, function(result) {
		var parsed = JSON.parse(result);
		alert(parsed[0] + " : " + parsed[1] + " : " + parsed[2] + " : ");
	});
*/
switch ($_GET['action']){
	case 'translate':
		if(isset($_GET['word'])){
			echo t($_GET['word']);
		}
		else if(isset($_GET['words'])){
			$words = $_GET['words'];
			$translatedWords = array();
			foreach($words as $word){
				array_push($translatedWords,t($word));
			}
			echo json_encode($translatedWords);
		}
		
		else{
			echo t("No word/s submitted!");
		}
		break;
	default: echo "No such action: ".$_GET['action'];
}
