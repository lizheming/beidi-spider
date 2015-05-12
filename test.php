<?php 
include "Film.php";

$id = 0;
do {
	$film = new Film($id);
	try {
		if( $film->title === "" ) throw new Exception("$id no result");
		echo $film;
		file_put_contents("film/$id.json", $film);
		$id = $film->prev;
	} catch (Exception $e) {
		$log = "$id : ".PHP_EOL;
		$log .= $e->getMessage();
		$log .= PHP_EOL."==============================".PHP_EOL;
		error_log($log, 3, "error.log");
	}
} while ( $film->prev != 0 );