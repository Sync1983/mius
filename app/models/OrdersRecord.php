<?php

/**
 * Description of ClientsRecord
 *
 * @author Sync<atc58.ru>
 */
namespace app\models;
use app\helpers\ActiveRecord;

class OrdersRecord extends ActiveRecord{
  protected $_table_name = "orders";
  protected $_id_name = "id";
  public $price;

  public function __construct($db, $id = "") {
    parent::__construct($db, $id);
    if(!$this->_is_new){
      return;
    }
    $this->format = 3;
    $this->pay = 0;
    $this->create_time = time();
    $this->client_id = 0;
    $this->order_cars= 1;
    $this->setStartTime(date("Y-m-d",time()));
    $this->setOrderLength(1);    
  }
  
  public function orderLength(){
    $start = getdate($this->order_time);
    $stop  = getdate($this->finish_time);
    return 12*($stop['year']-$start['year'])+($stop['mon']-$start['mon'])+1;
  }

  public function setStartTime($time){
    $now = getdate();
    if(is_string($time)){
      $time = strtotime($time)-10;
    }
    $set = getdate($time);    
    if(($now['year']<$set['year'])||
       (($now['year']==$set['year'])&&($now['mon']<=$set['mon']))
       ){
      
      $set['mon'] += 1;
    }
    $this->order_time = mktime(0, 0, 1, $set['mon'], 1, $set['year']);
  }
  
  public function setOrderLength($month_count){
    $set = getdate($this->order_time);
    
    $first_day = mktime(23, 59, 59, $set['mon']+$month_count-1, 1, $set['year']);
    $last_day = date("t",  $first_day);    
    $this->finish_time = mktime(23, 59, 59, $set['mon']+$month_count-1, $last_day, $set['year']);    
  }
  
}
