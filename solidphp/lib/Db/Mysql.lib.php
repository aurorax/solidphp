<?php 

/*
 * Class Mysql
 * 
 * Actually it's a Mysqli class.
 * Implements basic funcions of Mysql database.
 */
class Mysql
{

	private $conn;
	private $server;
	private $user;
	private $pass;
	private $dbname;
	private $encode;
	private $prefix;
	private $per;

	private $sql;
	private $result;
	
	private $debug = false;

	function __construct($server,$user,$pass,$dbname,$encode='',$prefix='',$per=false){
		$this->server = $server;
		$this->user = $user;
		$this->pass = $pass;
		$this->dbname = $dbname;
		$this->encode = $encode;
		$this->prefix = $prefix;
		$this->per = $per;
		$this->connect();
	}

	public function connect(){
		$this->conn = mysqli_connect($this->server,$this->user,$this->pass,$this->dbname);
		if (mysqli_connect_errno($this->conn)){
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_query($this->conn,'SET NAMES '.$this->encode);
	}

	function __destruct(){
		if(!$this->per){
			mysqli_close($this->conn);
		}
	}

	public function link(){
		return $this->conn;
	}
	
	public function addPrefix(& $table){
		if(isset($this->prefix) && $this->prefix!=''){
			$table = $this->prefix.$table;
		}
	}

	/* SQL语句执行
	 * 若$sql不空则执行$sql中的语句,否则执行$this->sql中的语句
	 */
	public function query($sql=''){
		if(!empty($sql)){
			$this->sql = $sql;
		}
		echo $this->sql.'<br>';
		#$this->sql = mysqli_real_escape_string($this->conn,$this->sql);
		$this->result = mysqli_query($this->conn, $this->sql);
		if (mysqli_errno($this->conn)){
			echo mysqli_error($this->conn);
			echo '<br>';
		}
		return $this->result;
	}

	/* 
	 * function createTable
	 * 
	 * $force=true	If $table existes, create after delete.
	 * $force=false If $table existes, $table cannot be re-create.
	 * @return	boolean
	 * 
	 * $this->createTable('test','id int(4), word text');  =>  CREATE TABLE sp_test(id int(4), word text) DEFAULT CHARSET utf8
	 */
	public function createTable($table,$columns,$force=false){
		$this->addPrefix($table);
		if($force)
			$this->deleteTable($table);
		$this->sql = 'CREATE TABLE '.$table.'('.$columns.') DEFAULT CHARSET '.$this->encode;
		return $this->query();
	}
	
	/*
	 * fucntion deleteTable
	 * 
	 * $d->deleteTable('test');  =>  DROP TABLE IF EXISTS sp_test
	 */
	public function deleteTable($table){
		$this->addPrefix($table);
		$this->sql = 'DROP TABLE IF EXISTS '.$table;
		return $this->query();
	}
	
	/*
	 * function select
	 * 
	 * $d->select('*','test','id=1');  =>  SELECT * FROM sp_test WHERE id=1
	 */
	public function select($column,$table,$condition=''){
		$this->addPrefix($table);
		$this->sql = 'SELECT '.$column.' FROM '.$table;
		if(!empty($condition)){
			$this->sql .= ' WHERE '.$condition;
		}
		return mysqli_fetch_all($this->query(),MYSQLI_ASSOC);
	}
	
	/*
	 * function insert
	 * 
	 * $this->insert('test','7','number7');  =>  INSERT INTO sp_test VALUES('7','number7')
	 */
	public function insert(){
		$args = func_get_args();
		$this->addPrefix($args[0]);
		$this->sql = 'INSERT INTO '.$args[0].' VALUES(\''.$args[1].'\'';
		for($i=2;$i<sizeof($args);$i++)
			$this->sql .= ',\''.$args[$i].'\'';
		$this->sql .= ')';
		return $this->query();
	}
	
	/*
	 * function update
	 * 
	 * $this->update('test','word','another','id=4');  =>  UPDATE sp_test SET word = 'another' WHERE id=4
	 */
	public function update(){
		$args = func_get_args();
		$this->addPrefix($args[0]);
		$size = sizeof($args);
		$last = $size - 1;
		$this->sql = 'UPDATE '.$args[0].' SET '.$args[1].' = \''.$args[2].'\'';
		if($last > 4){
			for($i=4;$i<$last;$i=$i+2)
				$this->sql .= ', '.$args[$i-1].' = \''.$args[$i].'\'';
		}
		$this->sql .= ' WHERE '.$args[$last];

		return $this->query();
	}

	/*
	 * function delete
	 * 
	 * $this->delete('test','id=3');  =>  DELETE FROM sp_test WHERE id=3
	 */
	public function delete($table,$condition=''){
		$this->addPrefix($table);
		$this->sql = 'DELETE FROM '.$table;
		if(!empty($condition))
			$this->sql .= ' WHERE '.$condition;
			return $this->query();
	}
}