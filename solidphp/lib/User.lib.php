<?php

	class Model_User extends Model
	{
		/* 数据表名 */
		protected $table = 'user';
		
		/* 用户信息 */
		public $info;
		
		public function add($user,$pass){
			$info = md5($user.$pass.KEY);
			return $this->db->insert($this->table,'',$user,$pass,'',$info);
		}
		
		public function del($user){
			return $this->db->delete($this->table,'username='.$user);
		}
		
		/* 登录方法
		 * @args	$user,$pass
		 * @return	bool
		 */
		public function login($user,$pass){
			if($this->isLoggedin()) return true;
			$row = $this->db->select('id,password,infocheck',$this->table,'username=\''.$user.'\''); 
			if(($row!=NULL)&&($row['password'] == $pass)){
				$info = md5($user.$pass.KEY);
				if($row['infocheck'] == $info){
					$log = md5(date('YmdHis').$info.KEY);
					$this->db->update($this->table,'logcheck',$log,'id='.$row['id']);
					setcookie('id',$row['id'],0);
					setcookie('log',$log,0);
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		/* 获取登陆用户名 */
		public function getName($id){
			if($this->isLoggedin()){
				return $this->db->select('username',$this->table,'id='.$id);
			}
		}
		
		/* 登录确认方法
		 * @return	bool
		 */
		public function isLoggedin(){
			if(isset($_COOKIE['id']) && isset($_COOKIE['log'])){
				$id = $_COOKIE['id'];
				if(!preg_match('/[^\d]/',$id)){
					$row = $this->db->select('username,logcheck',$this->table,'id='.$id);
					if(($row['logcheck'] != NULL)&&($_COOKIE['log'] == $row['logcheck']))
						return true;
				}
				return false;
			}
		}
		
		/* 登出方法
		 * @return	bool
		 */
		public function logout(){
			setcookie('id','',time()-86400);
			setcookie('log','',time()-86400);
		}
	}