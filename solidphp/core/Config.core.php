<?php

	class Config
	{
		private static $config = array(
			'APP' => array(
				'APP_VERSION' => '1.0.1',
				'APP_DEBUG' => true,
				'APP_MODULE' => 'module',
				'APP_MODULE_DEFAULT' => 'Index',	//默认模块名
				'APP_MODULE_EMPTY' => 'EmptyMod',	//空模块名
				'APP_ACTION_DEFAULT' => 'main',	//默认方法名
				'APP_ACTION_EMPTY' => 'empty_act'	//空方法名
			),
			'DB' => array(
				'DB_TYPE' => 'Mysql',		//数据库类型
				'DB_PREF' => 'sp_',		//数据库前缀
				'DB_HOST' => 'localhost',	//数据库地址
				'DB_USER' => 'root',		//账户
				'DB_PASS' => '',	//密码
				'DB_PORT' => 3306,	//数据库端口
				'DB_NAME' => 'solidphp',	//数据库名称
				'DB_PCON' => false,		//false:非持续连接 true:持续连接
				'DB_CODE' => 'utf8'		//数据编码
			),
			'TPL' => array(
			
			)
		);
		
		public static function get($name=''){
			if(!empty($name)){
				$line = strpos($name,'_');
				if($line!=0){
					$pre = substr($name,0,$line);
					return self::$config[$pre][$name];
				}else
					return self::$config[$name];
			}else
				return self::$config;
		}
		
		public static function set($name,$value=''){
			if(!empty($value))
				$name = array($name=>$value);
			foreach($name as $n=>$v){
				$line = strpos($n,'_');
				$pre = substr($n,0,$line);
				self::$config[$pre][$n] = $v;
			}
		}
	}