<?php
	/**
	 * SOLIDPHP控制类
	 * @package		core_Display
	 */
	class Display
	{
		/* 模板文件夹 */
		private $tpl;
		
		/* 模板引用路径 */
		private $url;
		
		private $varPool;
		
		private static $display;
		
		
		/* 构造方法 */
		public function __construct(){
				$this->tpl = _ROOT_.'/public';
				$this->url = _SITE_.'/public';
		}
		
		private static function _static(){
			if(!is_object(self::$display))
				self::$display = new self;
		}
		
		//模板文件加载方法
		public function load($file){
			$file = $this->tpl.$file;
			$file = str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$file);
			if(file_exists($file)){
				require $file;
			}else{
				exit('Cannot load '.$file.': file missing!');
			}
		}
		
		public static function show($file){
			self::_static();
			self::$display->load($file);
		}
		
		public static function assign($name,$value){
			self::_static();
			self::$display->varPool[$name] = $value;
		}
		
		/* 模型和变量加载方法 */
		public function __get($name){
			return isset($this->varPool[$name])?$this->varPool[$name]:NULL;
		}
	}