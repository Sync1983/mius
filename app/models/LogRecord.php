<?php

/**
 * Description of ClientsRecord
 *
 * @author Sync<atc58.ru>
 */
namespace app\models;
use app\helpers\ActiveRecord;

class LogRecord extends ActiveRecord{
  const COUNT_CHANGE = 1;
  const CHECK        = 2;
  const ADD_ORDER    = 3;
  const EDIT_ORDER   = 4;
  const DEL_ORDER    = 5;
  
  public static $actions_code = [
    self::COUNT_CHANGE =>
      "Изменение параметра",
    self::CHECK =>
      "Проверка заказа",
    self::ADD_ORDER =>
      "Заказ добавлен",
    self::EDIT_ORDER =>
      "Заказ изменен",
    self::DEL_ORDER =>
      "Заказ удален",    
  ];
  
  protected static $templates = [
    self::COUNT_CHANGE =>
      "Параметр [name] изменился с [old] на [new]",
    self::CHECK =>
      "Проверка возможности размещения заказа формата А[fmt] для [cnt] машин на срок [finish] начиная с даты [start] от [client] результат [result]",
    self::ADD_ORDER =>
      "Добавлен заказ формата А[fmt] для [cnt] машин на срок [finish] начиная с даты [start] от [client]",
    self::EDIT_ORDER =>
      "Изменен заказ №[id] формата А[fmt] для [cnt] машин на срок [finish] начиная с даты [start] от [client] на формата А[fmt1] для [cnt1] машин на срок [finish1] начиная с даты [start1] от [client1]",
    self::DEL_ORDER =>
      "Удален заказ №[id] формата А[fmt] для [cnt] машин на срок [finish] начиная с даты [start] от [client]",
  ];

  protected $_table_name = "logs";
  protected $_id_name = "id";
  
  public function fill($action_id,$params=[]){
    if(!isset(self::$templates[$action_id])){
      return false;
    }
    $text = self::$templates[$action_id];
    foreach ($params as $key=>$value){
      $text = str_replace("[$key]", $value, $text);
    }
    $this->time = time();
    $this->code = $action_id;
    $this->message =  mb_convert_encoding($text, 'utf-8', mb_detect_encoding($text));    
  }
  
}
