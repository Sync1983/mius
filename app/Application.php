<?php
/**
 * Description of Application
 *
 * @author Sync<atc58.ru>
 */
namespace app;
use app\helpers\AppHelper;
use app\actions\Error;
use app\DbConnector;
use app\helpers\QueryListHelper;
use app\helpers\AppConstants;
use app\models\ClientsRecord;
use app\models\OrdersRecord;
use mysqli_result;

class Application {
  private $_params;
  private $_route;
  /* @var $_view View */
  private $_view;
  /* @var $_db DbConnector */
  private $_db;
  /* @var $_const AppConstants */
  private $_const;

  public function run(){
    if(!$this->_route){
      $this->_route = "index";
    }
    $this->_route = preg_replace("/-/", "", $this->_route);
    $out = "";
    if(!$this->hasAction($this->_route)){
      $out = $this->outError("Action $this->_route not found!");      
    } else {
      $out .= $this->callAction($this->_route);
    }    
    echo $this->_view->render("main", ['context'=>$out]);
    exit();
  }

  public function __construct($params) {    
    $this->_params = $params;    
    $this->_route = AppHelper::initFromArray("route", $_GET);
    $this->_view = new View();
    $this->_db  = new DbConnector($params['database']);
    $this->_const = new AppConstants($this->_db);
  }
  
  //==================== protected ======================================
  
  protected function actionIndex(){
    return $this->_view->render("index");
  }
  
  protected function actionCarCount(){    
    $count = intval($this->_const->car_count);
    return $this->_view->render("count",["count"=>$count,"error"=>false]);
  }
  
  protected function actionCarCountSave(){
    $request = $_POST;    
    $count = AppHelper::initFromArray('count', $request);    
    
    if(!$count){
      return $this->_view->render("count",["count"=>$count,"error"=>"Неверные данные"]);      
    }
    
    $this->_const->car_count = $count;
    
    if($this->_const->wasError()){
      return $this->_view->render("count",["count"=>$count,'error'=>  $this->_db->getErrorList()]);      
    }    
    
    return $this->_view->render("count",["count"=>$count,"info"=>'Запись произведена']);
  }
  
  protected function actionClients(){
    $clients = new ClientsRecord($this->_db);
    $ids = $clients->getAllKeys();
    $data = [];
    foreach ($ids as $id) {
      $data[$id] = new ClientsRecord($this->_db,$id);
    }
    return $this->_view->render('clients', ['data'=>$data]);
  }
  
  protected function actionClientChange(){
    $params = $_GET;
    $id = AppHelper::initFromArray("id", $params);
    $name = AppHelper::initFromArray("name", $params);
    $person = AppHelper::initFromArray("person", $params);
    $phone = AppHelper::initFromArray("phone", $params);
    $client = new ClientsRecord($this->_db,$id);
    $client->name = $name;
    $client->person = $person;
    $client->phone = $phone;
    $result = $client->save();
    
    $ids = $client->getAllKeys();
    $data = [];
    foreach ($ids as $id) {
      $data[$id] = new ClientsRecord($this->_db,$id);
    }
    if(!$result){
      return $this->_view->render('clients', ['data'=>$data,'error'=>"Ошибка сохранения!".$this->_db->getErrorList()]);    
    }
    return $this->_view->render('clients', ['data'=>$data,'info'=>"Запись изменена"]);    
  }
  
  protected function actionClientCreate(){
    $params = $_GET;    
    $name = AppHelper::initFromArray("name", $params);
    $person = AppHelper::initFromArray("person", $params);
    $phone = AppHelper::initFromArray("phone", $params);
    $client = new ClientsRecord($this->_db);
    $client->name = $name;
    $client->person = $person;
    $client->phone = $phone;
    $client->create_time = time();
    $result = $client->save();
    
    $ids = $client->getAllKeys();
    $data = [];
    foreach ($ids as $id) {
      $data[$id] = new ClientsRecord($this->_db,$id);
    }
    if(!$result){
      return $this->_view->render('clients', ['data'=>$data,'error'=>"Ошибка сохранения!".$this->_db->getErrorList()]);    
    }
    return $this->_view->render('clients', ['data'=>$data,'info'=>"Запись изменена"]);    
  }
  
  protected function actionClientDelete(){
    $params = $_GET;
    $id = AppHelper::initFromArray("id", $params);
    $client = new ClientsRecord($this->_db,$id);
    $client->delete();
    return $this->actionClients();
  }

  protected function actionOrder(){
    return $this->_view->render("order",[
        'name'  => '',
        'user'  => '',
        'phone' => '',
        'format'=> 3,
        'count' => 1,
        'start_time' => strftime("%Y-%m-%d"),
        'order_time' => 1,
      ]);    
  }
  
  protected function actionOrderAdd() {
    $request = $_POST;    
    
    $name  = AppHelper::initFromArray('name', $request);    
    $user  = AppHelper::initFromArray('user', $request);    
    $phone = AppHelper::initFromArray('phone', $request);    
    $format= AppHelper::initFromArray('format', $request);    
    $count = AppHelper::initFromArray('count', $request);    
    $start_time = AppHelper::initFromArray('start_time', $request);    
    $order_time = AppHelper::initFromArray('order_time', $request);  
    
    
    $dates = $this->getStartFinishDates($start_time, $order_time);    
    $start_time_mon = $dates['start'];
    $finish_time = $dates['finish'];
    $s_time_txt = strftime("%Y-%m-%d",$start_time_mon);
        
    $query_params = [
        'create_time' => time(),
        'name'        => $name,
        'user'        => $user,
        'phone'       => $phone,
        'format'      => $format,
        'order_cars'  => $count,
        'order_time'  => $start_time_mon,
        'finish_time' => $finish_time,
        'price'       => 0,
        'pay'         => 0,
    ];
    
    $return_params = [
        'name'  => $name,
        'user'  => $user,
        'phone' => $phone,
        'format'=> $format,
        'count' => $count,
        'start_time' => $start_time,
        'order_time' => $order_time];
    
    $pay = $this->getPriceByFormat($format);
    if(!$pay){
      $return_params['error'] = "Ошбика! Невозможно определить стоимость формата! <br>".$this->_db->getErrorList();
      return $this->_view->render("order",$return_params);      
    }
    
    $query_params['price'] = $pay * $count;
    
    $query = QueryListHelper::getQueryText(QueryListHelper::QUERY_ORDER_ADD);
    
    if(!$this->_db->query($query,$query_params)){
      $return_params['error'] = $this->_db->getErrorList();
      return $this->_view->render("order",$return_params);
    }
    
    $f_time_txt = strftime("%Y-%m-%d",$finish_time);
    $pay_text   = $pay*$count." руб.";
    $return_params['info']  = "Размещение начнется <b>$s_time_txt</b> и закончится <b>$f_time_txt</b>.<br>"
                              . "Стоимость заказа составляет $pay_text";
    
    return $this->_view->render("order",$return_params);
    
  }
  
  protected function actionOrdersList(){
    $order = new OrdersRecord($this->_db);
    $items_id = $order->getAllKeys();
    
    $items = [];
    $clients= [];
    foreach ($items_id as $id) {
      $items[$id] = new OrdersRecord($this->_db,$id);
      $client_id = $items[$id]->client_id;
      if(!isset($clients[$client_id])){
        $clients[$client_id] = new ClientsRecord($this->_db,$client_id);
      }
    }    
    
    return $this->_view->render("orders_list", ['items'=>$items,'clients'=>$clients]);
  }

  protected function outError($error_text){
    $error = new Error($error_text);
    return $error->out();
  }
  
  protected function hasAction($name){
    return method_exists($this, "action".$name);
  }
  
  protected function callAction($name){
    $action = "action".$name;
    return $this->$action();
  }
  
  //========================= Private ======================================
  
  private function getStartFinishDates($start,$length){
    $s_time = getdate(strtotime($start));
    
    $current_mon = getdate();
    
    if(($s_time['year']<$current_mon['year'])||
       (($s_time['year']==$current_mon['year'])&&($s_time['mon']<=$current_mon['mon']))
       ){
      $s_time['mon'] += 1;
    }
    $start_time = mktime(0, 0, 1, $s_time['mon'], 1, $s_time['year']);
    $finish_time = mktime(23, 59, 59, $s_time['mon']+$length, 1, $s_time['year']);
    $mon_days = date("%t", $finish_time);
    $finish_time += 24*60*60*($mon_days-1);    
    return ['start'=>$start_time,'finish'=>$finish_time];
  }
  
  private function getPriceByFormat($format){
    $name = "pay".intval($format);
    $query = QueryListHelper::getQueryText(QueryListHelper::QUERY_GET_PRICE_BY_FORMAT);
    /* @var $result mysqli_result */
    $result = $this->_db->query($query, ['name'=>$name]);
    if(!$result){
      return 0;
    }
    $assoc = $result->fetch_assoc();    
    return intval(AppHelper::initFromArray($name, $assoc));
  }
}
