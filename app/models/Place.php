<?php
/**
 * Description of Place
 *
 * @author Sync<atc58.ru>
 */
namespace app\models;
use Exception;

class Place {
  protected $_free_place=4;
  protected $_placed =[];
  
  public function canPlace($format,$id){
    if( ($format!==3)&&
        ($format!==4)&&
        ($format!==5)){
      throw new Exception("Try place undefined format [ A$format ]");
    }
    $place = 1<<(5-$format);
    if(($this->_free_place-$place) < 0){
      return false;
    }
    if(isset($this->_placed[$id])){
      return false;
    }
    return true;
  }
  
  public function place($format,$id){    
    if(!$this->canPlace($format,$id)){
      return FALSE;
    }
    $this->_placed[$id] = $format;
    $place = 1<<(5-$format);
    $this->_free_place -= $place;
    if($this->_free_place < 0){
      throw new Exception("Overplaced! [ ".  implode(",", $this->_placed)." ]");
    }
    return true;
  }  
  
  public function sort(){
    asort($this->_placed);    
  }

  public function getTable(){    
    //Создаем 2 строки для <tr> отображения
    $result = [0=>"",1=>""];
    $pos = 0;                 //Для А5 указывает в какую строку запхать
    $cnt = 0;                 //Сохраняем кол-во занятых клеток, чтобы потом добить пустыми клетками до листа
    
    asort($this->_placed);    // Отсортируем по формату, чтобы A4 оказался левее A5
    //По формату добавляем в одну из строк колонку
    foreach ($this->_placed as $key => $format) {
      if($format==3){ 
        $result[0] .= "<td rowspan=\"2\"> A3[$key]</td>";     // Используя rowspan - не нужно добавлять ячейку во вторую строку
        $cnt += 4;
      }
      if($format==4){
        $result[0] .= "<td rowspan=\"2\"> A4[$key]</td>";
        $cnt += 2;
      }
      if($format==5){
        $result[$pos] .= "<td> A5[$key]</td>";              //Добавляем ячейку А5 меняя $pos на между 0 и 1
        $pos = 1 - $pos;
        $cnt += 1;
      }
    }
    //Если лист незаполнен до конца - добиваем его пустыми блоками
    while($cnt<4){
      $result[$pos] .= "<td> - </td>";
      $pos = 1 - $pos;
      $cnt += 1;      
    }    
    return $result;
  }
  
  public function getData(){
    return $this->_placed;
  }
  
  public function getFreePlaces(){
    return $this->_free_place;
  }

  public function compare($cmp){
    /* @var $cmp Place */
    $arr1 = $this->_placed;
    $arr2 = $cmp->_placed;
    $diff1 = array_diff_assoc($arr1, $arr2);
    $diff2 = array_diff_assoc($arr2, $arr1);
    if((count($diff1)>0)||(count($diff2)>0)){
      return false;
    }    
    return true;
  }
  
}
