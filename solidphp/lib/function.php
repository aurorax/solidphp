<?php

function e($str){
	echo $str;
}

function pr($arr, $type='print_r'){
	echo '<pre>';
	switch($type){
		case 'var_dump':
			var_dump($arr);
			break;
		default:
			print_r($arr);
	}
	echo '</pre>';
}