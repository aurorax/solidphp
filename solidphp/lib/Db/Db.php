<?php 

/*
 * TODO: PDO & MongoDB
 */
class Db
{
	private static $db='';
	private static $pre;
	
	public static function get(){
		if(self::$db=='')
			self::init();
		return self::$db;
	}
	
	public static function pre(){
		return self::$pre;
	}
	
	public static function init(){
		$data = Solid::config('DB');
		$type = $data['DB_TYPE'];
		Solid::load('/Db/'.$type.'.lib.php');
		self::$pre = $data['DB_PREF'];
		self::$db = new $type(
			$data['DB_HOST'],
			$data['DB_USER'],
			$data['DB_PASS'],
			$data['DB_NAME'],
			$data['DB_CODE'],
			$data['DB_PCON']);
	}
}