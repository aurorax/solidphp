<?php

	class Index extends Module
	{
		public function main(){
			Display::show('/index.php');
		}
		
		public function emptyAct(){
			J();
		}
	}