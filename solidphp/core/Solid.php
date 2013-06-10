<?php
/* SolidPHP程序入口类
 * @pachage		SOLIDPHP
 * @author		aurorax
 * @lastmodify	2012/12/31
 */

	class Solid
	{
		private static $config;
		
		private static $require;
		 
		/* 执行方法
		 * 用于入口程序运行
		 */
		public static function run(){
			
			//开始运行时间
			$GLOBALS['startTime'] = microtime();
			//内存初始使用
			if(function_exists('memory_get_usage'))
				$GLOBALS['startRam'] = memory_get_usage();
				
			ob_start();
			
			try{
				if(defined('_PATH_')) define('_ROOT_',dirname(_PATH_));
				if(!defined('_SITE_')) define('_SITE_',rtrim('http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']),'\\\/'));
				
				if(!is_dir('public')) mkdir('public');
	
				if(function_exists('spl_autoload_register')){
					spl_autoload_register(array('Solid','autoload'));
				}
				
				self::_config();
				
				self::_parse_url();
				
				foreach($_GET as & $g){
					$g = str_replace(array('\'','"'),'',$g);
				}
				
				self::__require('/lib/function.lib.php');
				self::__require('/ext/function.ext.php');
				//TODO cache?
				self::_execute();

				if(self::$config['APP_DEBUG']){
					Debug::stop();
					//TODO Debug::collect()
					Debug::v('DBLINK',Db::get()->link());
					Debug::v('_COOKIE',$_COOKIE);
					Debug::v('_GET',$_GET);
					Debug::v('_POST',$_POST);
					Debug::v('require',self::$require);
					Debug::v('page',$_SERVER['PHP_SELF']);
					//TODO end
					Debug::show();
				}
				
			}catch(Exception $e){
				echo $e->getMessage().'<br />';
				echo $e->getCode().'<br />';
				echo $e->getFile().'<br />';
				echo $e->getLine().'<br />';
			}
			
			ob_end_flush();
		}
		
		private static function _config(){
			require './config.php';
			Config::set($config);
			self::$config = Config::get('APP');
			if(self::$config['APP_DEBUG'])
				error_reporting(E_ALL ^ E_NOTICE);	//显示除notice外所有错误报告
			else
				error_reporting(0);					//屏蔽全部错误报告
			
			session_start();
			
		}
		
		private static function _execute(){
			$module = '';
			$action = '';
			if(self::_module_exists($_GET['m'])){
				$module = $_GET['m'];
			}else if(self::_module_exists(self::$config['APP_MODULE_EMPTY'])){
				$module = self::$config['APP_MODULE_EMPTY'];
			}else{
				throw new Exception('module '.$_GET['m'].' not found');
			}
			if(class_exists($module)){
				$object = new $module();
				if(method_exists($object,$_GET['a'])){
					$action = $_GET['a'];
				}else if(method_exists($object,self::$config['APP_ACTION_EMPTY'])){
					$action = self::$config['APP_ACTION_EMPTY'];
				}else{
					throw new Exception('method not exists in class '.$_GET['m']);
				}
				if(method_exists($object,'__construct') || (strtoupper($module)!=strtoupper($_GET['a'])))
					$object->$action();
			}else{
				throw new Exception('class not exists');
			}
		}
		
		private static function _module_exists($module){
			if(!is_dir(self::$config['APP_MODULE'])){
				mkdir(self::$config['APP_MODULE']);
			}
			$modulePath = '/'.self::$config['APP_MODULE'].'/'.$module.'.php';
			if(self::__require($modulePath))
				return true;
			else
				return false;
		}
		
		private static function _parse_url(){
			$info = '';
			if(empty($_GET)){
				if(!empty($_SERVER['PATH_INFO'])){
					$info = explode('/',trim($_SERVER['PATH_INFO'],'/'));
				}else{
					$uri = trim($_SERVER['REQUEST_URI'],'/');
					$pre = rtrim($_SERVER['PHP_SELF'],'index.php');
					$uri = ltrim($uri,$pre);
					$info = explode('/',trim($uri,'/'));
				}
			}
			if(!empty($info)){
				$_GET['m'] = empty($info[0])?$_GET['m']:$info[0];
				$_GET['a'] = empty($info[1])?$_GET['a']:$info[1];
				for($i=3;$i<sizeof($info);$i=$i+2){
					$_GET[$info[$i-1]] = $info[$i];
				}
			}
			$_GET['m'] = isset($_GET['m'])?$_GET['m']:self::$config['APP_MODULE_DEFAULT'];
			$_GET['a'] = isset($_GET['a'])?$_GET['a']:self::$config['APP_ACTION_DEFAULT'];
		}
		
		//类自动加载
		public static function autoload($class){
			$class_array = array();
			$flag = 0;
			self::__require('/core/Config.core.php');
			$class_array[] = '/core/'.$class.'.core.php';
			$class_array[] = '/core/Db/'.$class.'.db.php';
			$class_array[] = '/lib/'.$class.'.lib.php';
			$class_array[] = '/ext/'.$class.'.ext.php';
			$class_array[] = self::$config['APP_MODULE'].'/'.$class.'.php';
			
			foreach($class_array as $file){
				if(self::__require($file)){
					$flag = 1;
					break;
				}
			}
			
			if(!$flag)
				throw new Exception('class not found!');
		}
		
		/* 自动包含
		 * 利用require实现require_once
		 */
		public static function __require($class){
			if(file_exists(_PATH_.$class))
				$class = _PATH_.$class;
			else if(file_exists(_ROOT_.$class))
				$class = _ROOT_.$class;
			else
				return false;
			$class = str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$class);
			if(!isset(self::$require[$class])){
				require $class;
				self::$require[$class] = true;
			}
			return true;
		}
	}

