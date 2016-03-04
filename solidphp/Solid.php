<?php

/*
 * Solidphp Framework
 * 
 * @author	aurorax <i@aurorax.org>
 * @license	GNU General Public License v2.0
 * @version 2.0
 */

class Solid
{	
	private static $_config;
	private static $_module;
	private static $_debug;
	
	/**
	 * Solid::run()
	 * 
	 * Main entrance of the frame.
	 * Only can be called after calling Solid::init().
	 * @param	string	$index
	 * @param	string	$action
	 */
	public static function run($index='', $action=''){
		
		try{
			if(!defined('_INIT_') || _INIT_ !== true)
				self::exception('app must init before run.');
		
			self::_set_index($index, $action);
			
			self::_auto_load();
		
			self::_execute();
			
			self::debug();
			
		}catch(Exception $e){
			echo $e->getMessage();
			exit();
		}
	}
	
	/**
	 * Solid::init()
	 * 
	 * Initialization function of the frame.
	 * Parameter $cfg is the path of the config file, which returns an config array.
	 * Must be called before calling Solid::run() to initialize the application.
	 * @param	array	$cfg
	 */
	public static function init($cfg){
		
		try{
			if(defined('_CORE_'))
				define('_ROOT_', dirname(_CORE_));
			else
				self::exception('\'_CORE_\' undefined.');
			
			if(!defined('_DS_'))
				define('_DS_', DIRECTORY_SEPARATOR);
			else
				self::exception('\'_DS_\' already defined.');
			
			if(is_file($cfg)){
				$config = require($cfg);
				if(is_array($config)){
					self::$_config = $config;
				}else{
					self::exception('config file not set properly. must return an array.',1);
				}
			}else
				self::exception('file \''.$cfg.'\' not exists.',1);
			
			if(isset(self::$_config['APP_LIB']) && !defined('_LIB_'))
				define('_LIB_', _CORE_._DS_.self::$_config['APP_LIB']);
			
			if(isset(self::$_config['APP_FUNC']))
				self::load(_DS_.self::$_config['APP_FUNC']);
			
			self::_parse_url();
			
			define('_INIT_', true);
			
		}catch(Exception $e){
			echo $e->getMessage();
			exit();
		}
	}
	
	/**
	 * Solid::config()
	 * 
	 * Returns the config array self::$_config,
	 * or with a defined $key returns a string/array self::$_config[$key].
	 * @param	string	$key
	 * @return	string
	 * @return	array
	 * TODO: config(array(x,y)) return config[x][y]
	 */
	public static function config($key=''){
		if($key!=''){
			if(isset(self::$_config[$key])){
				return self::$_config[$key];
			}else{
				return null;
			}
		}else{
			return self::$_config;
		}
	}
	
	/**
	 * Solid::load()
	 * 
	 * The module loading function of the framework.
	 * Two ways using Solid::load():
	 *     1. Solid::load('MODULE_NAME') => loads /_LIB_/MODULE_NAME/MODULE_NAME.php
	 *     2. Solid::load('/module/path.php') => loads /_LIB_/module/path.php
	 * No matter how many times the function called, each module will only be loaded once.
	 * Returns a bool 'true' while loading succeed. Otherwise raising exceptions.
	 * @param	string	$arg
	 * @return	boolean
	 */
	public static function load($arg){
		if(defined('_LIB_')){
			if(strpos($arg,'/')===false){
				$module_path = _DS_.$arg._DS_.$arg.'.php';
			}else{
				$module_path = str_replace(array('/','\\'),_DS_,$arg);
			}
			if(isset(self::$_module[$module_path]) && self::$_module[$module_path]){
				return true;
			}else{
				$path = _LIB_.$module_path;
				if(is_file($path)){
					require $path;
					self::$_module[$module_path] = true;
					return true;
				}else{
					self::exception('file \''.$path.'\' not exists.',2);
				}
			}
		}else{
			self::exception('config[\'APP_LIB\'] not set.');
		}
	}
	
	/**
	 * Solid::exception()
	 * 
	 * Raising an exception with a message.
	 * Several error levels shown below.
	 * @param	string	$message
	 * @param	integer	$error
	 * TODO: interface for external class.
	 */
	public static function exception($message, $error=0){
		switch($error){
			case 0: $type = 'fatal'; break;
			case 1: $type = 'config'; break;
			case 2: $type = 'loading'; break;
			default: $type = 'unexpected';
		}
		throw new Exception('<br><br>'.$type.' error: '.$message.'<br><br>');
	}
	
	/**
	 * Solid::debug()
	 * 
	 * Showing all the variables.
	 * TODO: interface for external class.
	 * @param	string	$key
	 * @param	string	$value
	 */
	public static function debug($key='', $value=''){
		if(isset(self::$_config['APP_DEBUG']) && self::$_config['APP_DEBUG']===true){
			self::$_debug = $GLOBALS;
			self::$_debug['APP_CONFIG'] = self::$_config;
			self::$_debug['APP_MODULE'] = self::$_module;

			echo '<pre>';
			# TODO costom print_r function
			print_r(self::$_debug);
			echo '</pre>';
		}
	}
	
	/*
	 * self::_set_index()
	 * 
	 * Set APP_INDEX & APP_ACTION.
	 * @param	string	$index
	 * @param	string	$action
	 */
	
	private static function _set_index($index, $action){
		if(!empty($index)){
			$GLOBALS['APP_INDEX']=$index;
		}else if(isset(self::$_config['APP_INDEX'])){
			$GLOBALS['APP_INDEX']=self::$_config['APP_INDEX'];
		}
			
		if(!empty($action)){
			$GLOBALS['APP_ACTION']=$action;
		}else if(isset(self::$_config['APP_ACTION'])){
			$GLOBALS['APP_ACTION']=self::$_config['APP_ACTION'];
		}
	}
	
	/**
	 * self::_parse_url()
	 * 
	 * Parse url to global variables for further use.
	 */
	private static function _parse_url(){
		if(!empty($_SERVER['PATH_INFO'])){
			$info = $_SERVER['PATH_INFO'];
		}else if(!empty($_SERVER['REQUEST_URI'])){
			$info = $_SERVER['REQUEST_URI'];
			$mark = strpos($info,'?');
			$get = substr($info,$mark+1);
			$info = substr($info,0,$mark);
		}
		
		if(!empty($info)){
			$GLOBALS['APP_INFO'] = explode('/',trim($info,'/'));
		}
		
		if(!empty($get)){
			$get = explode('&',ltrim($get,'?'));
			foreach($get as $g){
				$g = explode('=',$g);
				if(sizeof($g)>1){
					$_GET[$g[0]] = $g[1];
					/* 
					if(sizeof($g)>2){
						for($i=2;$i<sizeof($g);$i++)
							$_GET[$g[0]] .= '='.$g[$i];
					}
					*/
				}
			}
		}
		
 	}
	
	/**
	 * self::_execute()
	 * 
	 * After all the preparation _execute function runs the hole thing.
	 */
	private static function _execute(){
		$index = isset($GLOBALS['APP_INDEX'])?$GLOBALS['APP_INDEX']:'';
		$action = isset($GLOBALS['APP_ACTION'])?$GLOBALS['APP_ACTION']:'';
		
		$class = $index.'_View';
		$indexPath = _LIB_._DS_.$index._DS_.$class.'.php';
		if(is_file($indexPath)){
			require($indexPath);
		}
		
		if(empty($index) || empty($action)){
			echo 'Welcome to Solidphp.';
		}else if(class_exists($class) && method_exists($class,$action)){
			$class::$action();
		}else{
			self::exception('class or function not exists.');
		}
	}
	
	/**
	 * self::_auto_load()
	 * 
	 * Auto loading basic modules which defined in the config array.
	 */
	private static function _auto_load(){
		if(isset(self::$_config['APP_AUTOLOAD'])){
			$load = self::$_config['APP_AUTOLOAD'];
			if(!empty($load)){
				foreach ($load as $name){
					self::load($name);
				}
			}
		}
	}
	
}
