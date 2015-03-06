<?php
/**
 * Description of DbConnector
 *
 * @author Sync<atc58.ru>
 */
namespace app;
use app\helpers\AppHelper;
use app\helpers\QueryListHelper;
use Exception;
use mysqli;

class DbConnector {
  public $_error_list = [];
  private $_host;
  private $_db_name;
  private $_user;
  private $_pass;
  /* @var $_db \mysqli*/
  private $_db;


  /**
   * Конструктор связи с БД
   * Принимает на вход массив
   * @param mixed $params
   * обязательные значения host, db_name, user, pass
   */
  public function __construct($params) {
    $this->_host    =AppHelper::initFromArray("host",$params);
    $this->_db_name =AppHelper::initFromArray("db_name",$params);
    $this->_user    =AppHelper::initFromArray("user",$params);
    $this->_pass    =AppHelper::initFromArray("pass",$params);
    
    $this->_db = new mysqli();
    $db = $this->_db;    
    
    $db->connect($this->_host, $this->_user, $this->_pass, $this->_db_name);
    if($db->connect_errno!=0){
      throw new Exception("Cann`t connect to database with parameters: ".  json_encode($params)." [".$db->connect_error."]");
    }
    $db->set_charset("UTF-8");
  }
  
  public function query($query,$params = []){
    $query_prepend = $query;
    foreach ($params as $key=>$value){
      $query_prepend = str_replace("[".$key."]", $value, $query_prepend);
    }
    $query_text = $query_prepend;    
    //echo $query_text;
    $result = $this->_db->query($query_text);
    if($this->_db->errno!=0){
      $this->_error_list[] = $this->_db->error;
      return false;
    }
    return $result;
  }
  
  public function queryById($id,$params=[]){
    return $this->query(QueryListHelper::getQueryText($id),$params);
  }

  public function getErrorList(){
    return implode(",", $this->_error_list);
  }
  
}
