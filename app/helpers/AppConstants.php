<?php

/**
 * Description of AppConstants
 *
 * @author Sync<atc58.ru>
 */
namespace app\helpers;
use app\DbConnector;
use app\helpers\QueryListHelper;
use app\models\LogRecord;
use mysqli_result;

class AppConstants {
  /** @var $_db DbConnector **/
  protected $_db;
  protected $_params;
  private $_was_error;

  /*
   * @params $db DbConnector
   */
  public function __construct($db) {
    /* @var $db DbConnector */
    $this->_db = $db;
    /* @var $answer mysqli_result*/
    $answer = $db->queryById(QueryListHelper::QUERY_GET_PARAMS);
    if(!$answer){
      throw new Exception($db->getErrorList());      
    }
    $this->_params = [];    
    while($result = $answer->fetch_assoc()){      
      $key = $result['name'];
      $value = $result['value'];
      $this->_params[$key] = $value;
    }
  }
  
  public function __get($name) {    
    if(!isset($this->_params[$name])){
      return false;
    }
    return $this->_params[$name];
  }
  
  public function __set($name, $value) {    
    if(!array_key_exists($name, $this->_params)){
      $this->_params[$name] = $value;
      $this->_was_error = $this->_newParameter($name, $value);
      return;
    }    
    $old_value = $this->_params[$name];
    
    if($old_value==$value){
      return $value;
    }    
    $this->_params[$name] = $value;
    $log = new LogRecord($this->_db);
    $log->fill(LogRecord::COUNT_CHANGE, ['name'=>$name,'old'=>$old_value,'new'=>$value]);
    $log->save();
    $this->_was_error = $this->_db->queryById(QueryListHelper::QUERY_SET_PARAMS, ['name'=>"'$name'",'value'=>"'$value'"]);
    
    return;
  }
  
  public function getFormatPrice($fmt){
    $name = "pay".intval($fmt);
    return $this->$name;
  }

  public function wasError(){
    return $this->_was_error;
  }

  protected function _newParameter($name,$value){
    $this->_db->queryById(QueryListHelper::QUERY_NEW_PARAMS, ['name'=>"'$name'",'value'=>$value]);    
    return $value;
  }
  
}
