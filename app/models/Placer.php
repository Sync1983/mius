<?php
/**
 * Description of Placer
 *
 * @author Sync<atc58.ru>
 */
namespace app\models;
use app\models\Place;
use Iterator;

class Placer implements Iterator {
  protected $_max_cars = 0;
  protected $_quene = [];
  protected $_places = [];
  protected $_min_places = [];
  protected $_pos;

  public function __construct($max_cars) {
    $this->_max_cars = $max_cars;
    for($i=0; $i<$this->_max_cars; $i++){
      $this->_places[] = new Place();
    }
  }
  
  public function push($item){    
    $this->_quene[] = [
      'id'=>intval($item->id),
      'format'=>intval($item->format),
      'count'=>intval($item->order_cars),
      'dpkt'  => explode(",", $item->deprecate)];
  }
  
  public function run(){
    $all_placed = true;
    usort($this->_quene, [__CLASS__,'sort']);
    while(count($this->_quene)>0){
      $paste_item = &$this->_quene[0];
      //echo "Inserting ".$paste_item['id']." format [A".$paste_item['format']."] count ".$paste_item['count']."<br>";
      if(!$this->tryPaste($paste_item)){
        $all_placed = false;
        break;
      }
      if($paste_item['count']==0){
        unset($this->_quene[0]);
      }
      //echo "==> After insert ".$paste_item['id']." format [A".$paste_item['format']."] count ".$paste_item['count']."<br>";
      usort($this->_quene, [__CLASS__,'sort']);
    }  
    $this->minimaze();
    return $all_placed;
  }
  
  public function getQuene(){
    $txt = "<br>";
    foreach ($this->_quene as $value){
      $txt .= "Заказ: ".$value['id']." Формат: [A".$value['format']."] Количество неразмещенного: ".$value['count']."<br>";
    }
    return $txt;
  }
  
  public function getUnOptium(){
    $free = [];
    foreach ($this->_places as $place) {
      /* @var $place Place */
      $free_place = $place->getFreePlaces();
      if(!isset($free[$free_place])){
        $free[$free_place] = 1;
      } else {
        $free[$free_place] += 1;
      }
    }
    return $free;
  }

  //========================== Protected================================
  protected function minimaze(){
    $this->_min_places = [];
    $helper = [];
    foreach ($this->_places as $place){
      if($place->getFreePlaces()==4){
        continue;
      }
      $helper[] = ['place'=>$place,'count'=>1];
    }
    $count = count($helper);
    for($i = 0; $i< $count;$i++){
      for($j = 0; $j< $count;$j++){
        if(!isset($helper[$i])||!isset($helper[$j])||($i==$j)){
          continue;
        }
        
        $place1 = $helper[$i]['place'];
        $place2 = $helper[$j]['place'];
        
        if($place1->compare($place2)){
          $helper[$i]['count'] +=$helper[$j]['count'];
          unset($helper[$j]);
        }
      }
    }
    $this->_min_places = array_values($helper);
  }
  
  protected function tryPaste(&$item){
    foreach ($this->_places as $place){
      if( $place->place($item['format'], $item['id'],$item['dpkt']) ){
          $item['count']--;
          return true;     
      }
    }
    return false;
  }
  
  protected function sort($a,$b){
    if($a['count']==$b['count']){
      return 0;
    }elseif($a['count']<$b['count']){
      return 1;
    }
    return -1;
  }
  
  /*
   * 
   * @return Place
   */
  public function current() {
    return $this->_min_places[$this->_pos];
  }

  /*
   * 
   * @return integer
   */
  public function key() {
    return $this->_pos;
  }

  public function next() {
    $this->_pos ++;    
  }

  public function rewind() {
    $this->_pos = 0;
  }

  public function valid() {
    $count = count($this->_min_places);    
    if(($this->_pos >= $count)||($this->_pos<0)){
            return FALSE;
    }
    return true;
  }

}
