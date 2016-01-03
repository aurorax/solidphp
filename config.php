<?php

return array(
	'APP_DEBUG' => true,
	'APP_LIB' => 'lib',	//lib dir name
	'APP_FUNC' => 'function.php',	//global function filename
	'APP_INDEX' => 'Index',
	'APP_ACTION' => 'Action',
	#'APP_AUTOLOAD' => array( 
	#		'Db'#, 'Display'
	#),						//needs to be an array
	#'APP_EXCEPTION' => 'NewException',

	'DB' => array(
		'DB_TYPE' => 'Mysql',
		'DB_PREF' => 'sp_',		//prefix
		'DB_HOST' => 'localhost',
		'DB_USER' => 'user',
		'DB_PASS' => 'password',
		'DB_PORT' => 3306,
		'DB_NAME' => 'solidphp',
		'DB_CODE' => 'utf8',
		'DB_PCON' => false		//false:非持续连接 true:持续连接
	)
);
		