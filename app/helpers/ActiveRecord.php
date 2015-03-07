<?php
/**
 * Description of ActiveRecord
 *
 * @author Sync<atc58.ru>
 */
namespace app\helpers;
use app\DbConnector;
use app\helpers\QueryListHelper;
use Exception;
use mysqli_result;

class ActiveRecord {  
  protected $_table_name = "table";
  protected $_id_name = "id";
  protected $_fields = [];
  protected $_row  = [];
  protected $_is_new = true;
  /* @var $_db DbConnector */
  protected $_db;
  protected $_extend = [];
  
  public static function getAll($db){
    $class = get_called_class();
    $record = new $class($db);
    $records_ids = $record->getAllKeys();
    $answer = [];
    foreach ($records_ids as $key){
      $answer[$key] = new $class($db,$key);
    }
    return $answer;
  }

  public function getAllKeys(){
    $result = $this->_db->queryById(QueryListHelper::QUERY_GET_KEYS, [
      'tbl_name'=> $this->_table_name,
      'key_name'=> $this->_id_name],
      $this->_extend);
    if(!$result){
      throw new Exception($this->_db->getErrorList());
    }
    /* @var $result mysqli_result */
    $answer = [];
    while($row = $result->fetch_assoc()){
      $answer[] = $row['id'];
    }    
    return $answer;
  }

  public function __construct($db,$id = "") {
    /* @var $db DbConnector */
    $this->_db = $db;    
    $result = $this->_db->queryById(QueryListHelper::QUERY_GET_FIELDS, ['tbl_name'=> $this->_table_name]);
    if(!$result){
      throw new Exception($this->_db->getErrorList());
    }
    /* @var $result mysqli_result */
    while($row = $result->fetch_assoc()){
      $this->_fields[] = $row['Field'];
    }
    if($id===""){
      return;
    }
    $this->loadByKey($id);  
  }
  
  public function loadByKey($key=""){
    if($key===""){
      return;
    }
    $answer = $this->_db->queryById(
                QueryListHelper::QUERY_GET_ROW, 
                [ 'params'=> $this->_id_name."=".intval($key),
                  'tbl_name'=> $this->_table_name ],
                  $this->_extend);
    if(!$answer){
      throw new Exception($this->_db->getErrorList());
    }
    $row = $answer->fetch_assoc();
    $this->_row = $row;
    $this->_is_new = false;
  }
  
  public function __get($name){
    if(!isset($this->_row[$name])){
      return false;
    }
    return $this->_row[$name];
  }
  
  public function __set($name,$value){
    if(!in_array($name,$this->_fields)){
      throw new Exception("Field $name not exists!");
    }
    $this->_row[$name] = $value;
  }
  
  public function setExtend($extend = []){
    $this->_extend = $extend;
  }
  
  public function clearExtend(){
    $this->_extend = [];
  }

  public function save(){
    if($this->_is_new){
      return $this->insert();
    }
    $values = [];
    foreach ($this->_row as $key => $value) {
      if($key==$this->_id_name){
        continue;
      }
      $values[] = "`$key`='$value'";
    }
    $result = $this->_db->queryById(QueryListHelper::QUERY_SAVE_ROW, [
      'tbl_name' => $this->_table_name,      
      'values'   => implode(", ", $values),
      'id_name'  => $this->_id_name,
      'id'       => $this->getId()
      ]);
    if(!$result){
      throw new Exception($this->_db->getErrorList());
    }
    return true;    
  }
  
  public function insert(){
    $keys = $this->_row;
    unset($keys[$this->_id_name]);
    $rows = [];
    $values = [];
    foreach ($keys as $key=>$value){
      $rows[] = "`$key`";
      $values[] = "'$value'";
    }
    $result = $this->_db->queryById(QueryListHelper::QUERY_INSERT_ROW, [
      'tbl_name' => $this->_table_name,      
      'rows'   => implode(", ", $rows),
      'values'   => implode(", ", $values)
      ]);
    if(!$result){
      throw new Exception($this->_db->getErrorList());
    }
    return true;    
  }
  
  public function delete(){
    if($this->_is_new){
      return;
    }
    $result = $this->_db->queryById(QueryListHelper::QUERY_DELETE_ROW,[
      'tbl_name'  => $this->_table_name,
      'id_name'   => $this->_id_name,
      'id'        => $this->getId()
    ]);
    if(!$result){
      throw new Exception($this->_db->getErrorList());
    }
    return true;
  }

  public function getId(){
    return $this->_row[$this->_id_name];
  }
  
}
