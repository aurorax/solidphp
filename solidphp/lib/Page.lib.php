<?php

	class Page
	{
		private $db;
		private $table = 'page';
		
		public function __construct(){
			$this->db = Db::get();
		}
		
		public function add($name,$header,$url,$title='',$content=''){
			return $this->db->insert($this->table,'',$name,$header,$url,$title,$content,'0');
		}
		
		public function edit($id,$name,$header,$url,$title='',$content=''){
			return $this->db->update($this->table,'name',$name,'header',$header,'url',$url,'title',$title,'content',$content,'id='.$id);
		}
		
		public function select($condition='',$order=''){
			if(!stripos($condition,'=') && !stripos($condition,'<') && !stripos($condition,'>')){
				$order = $condition;
				$condition = '';
			}
			$row = $this->db->select('id,name,header,url,title,content,sort',$this->table,$condition);
			$size = sizeof($row);
			if($order=='INC'){
				for($i=0;$i<$size-1;$i++){
					for($j=$i+1;$j<$size;$j++){
						if($row[$i]['sort']>$row[$j]['sort']){
							$temp = $row[$j];
							$row[$j] = $row[$i];
							$row[$i] = $temp;
						}
					}
				}
			}
			return $row;
		}
		
		public function del($n,$v){
			$this->db->delete($this->table,$n.'='.$v);
		}
	}