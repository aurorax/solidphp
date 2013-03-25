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
			
			try{
				if(defined('_PATH_')) define('_ROOT_',dirname(_PATH_));
				if(!defined('_SITE_')) define('_SITE_','http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']));
				
				if(!is_dir('public')) mkdir('public');
	
				if(function_exists('spl_autoload_register'))
					spl_autoload_register(array('Solid','autoload'));
				
				self::_config();
				
				self::_parse_url();
				
				foreach($_GET as & $g)
					$g = str_replace(array('\'','"'),'',$g);
					
				if(self::_module_exists($_GET['m']))
					$module = $_GET['m'];
				else if(self::_module_exists(self::$config['APP_MODULE_EMPTY']))
					$module = self::$config['APP_MODULE_EMPTY'];
				else
					throw new Exception('module '.$_GET['m'].' not found');
					
				self::__require('/lib/function.lib.php');
				self::__require('/ext/function.ext.php');
				
				//TODO cache?
				self::_execute($module);

				if(self::$config['APP_DEBUG']){
					Debug::stop();
					//TODO Debug::collect()
					Debug::v('_GET',$_GET);
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
		}
		
		private static function _config(){
			require '/config.php';
			Config::set($config);
			self::$config = Config::get('APP');
			if(self::$config['APP_DEBUG'])
				error_reporting(E_ALL ^ E_NOTICE);	//显示除notice外所有错误报告
			else
				error_reporting(0);					//屏蔽全部错误报告
			
			session_start();
			
		}
		
		private static function _execute($module){
			if(class_exists($module)){
				$object = new $module();
				if(method_exists($object,$_GET['a'])){
					$action = $_GET['a'];
				}else if(method_exists($object,self::$config['APP_ACTION_EMPTY'])){
					$a = self::$config['APP_ACTION_EMPTY'];
				}else{
					throw new Exception('method not exists in class '.$module);
				}
				if(strtoupper($module)!=strtoupper($_GET['a']))
					$object->$action();
			}else{
				throw new Exception('class not exists');
			}
		}
		
		private static function _module_exists($module){
			if(!is_dir(self::$config['APP_MODULE'])) mkdir(self::$config['APP_MODULE']);
			$modulePath = self::$config['APP_MODULE'].'/'.$module.'.php';
			if(self::__require($modulePath))
				return true;
			else
				return false;
		}
		
		private static function _parse_url(){
			$pathinfo = explode('/',trim($_SERVER['PATH_INFO'],'/'));
			$_GET['m'] = empty($pathinfo[0])?'Index':$pathinfo[0];
			$_GET['a'] = empty($pathinfo[1])?'index':$pathinfo[1];
			for($i=2;$i<sizeof($pathinfo);$i=$i+2)
				$_GET[$pathinfo[$i]] = $pathinfo[$i+1];
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

