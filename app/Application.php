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
use app\models\Placer;
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
    $clients = ClientsRecord::getAll($this->_db);
    $item = new OrdersRecord($this->_db);
    return $this->_view->render("order",[
        'clients'  => $clients,
        'item'     => $item,        
      ]);    
  }
  
  protected function actionOrderAdd() {
    $request = $_POST;
    
    $order = new OrdersRecord($this->_db);    
    $order->create_time = time();
    $order->client_id   = AppHelper::initFromArray('client_id', $request);
    $order->format      = AppHelper::initFromArray('format', $request);
    $order->order_cars  = AppHelper::initFromArray('count', $request);
    $order->setStartTime(AppHelper::initFromArray('start_time', $request));
    $order->setOrderLength(AppHelper::initFromArray('order_time', $request));    
    
    $clients = ClientsRecord::getAll($this->_db);    
    
    $result = $order->save();
    if(!$result){
      return $this->_view->render("order",[
          'clients'  => $clients,
          'item'     => $order,        
          'error'    => "Ошибка сохранения! ".$this->_db->getErrorList()
        ]);      
    }
    $pay = $this->_const->getFormatPrice($order->format);
    if(!$pay){
      return $this->_view->render("order",[
          'clients'  => $clients,
          'item'     => $order,        
          'error'    => "Ошибка рассчета стоимости!"
        ]);      
    }
    $s_time_txt = date("d-m-Y",$order->order_time);
    $f_time_txt = date('d-m-Y',$order->finish_time);
    $pay_text = $pay*$order->order_cars." руб.";
    return $this->_view->render("order",[
        'clients'  => $clients,
        'item'     => $order,        
        'info'     =>"Размещение начнется <b>$s_time_txt</b> и закончится <b>$f_time_txt</b>.<br>Стоимость заказа составляет $pay_text"
      ]);    
  }
  
  protected function actionOrderDelete(){
    $id = AppHelper::initFromArray('id', $_GET);
    $order = new OrdersRecord($this->_db,$id);
    $order->delete();
    return $this->_view->redirect(AppHelper::indexRoute("orders-list"));
  }
  
  protected function actionOrderPay(){
    $id = AppHelper::initFromArray('id', $_GET);
    $order = new OrdersRecord($this->_db,$id);
    $order->pay = 1- $order->pay;
    $order->save();
    return $this->_view->redirect(AppHelper::indexRoute("orders-list"));
  }

  protected function actionOrdersList(){
    $order = new OrdersRecord($this->_db);
    $order->setExtend(['ORDER BY'=>'`create_time` DESC']);
    $items_id = $order->getAllKeys();
    
    $items = [];
    $clients= [];
    foreach ($items_id as $id) {
      $items[$id] = new OrdersRecord($this->_db,$id);
      $items[$id]->price = $this->_const->getFormatPrice($items[$id]->format)*$items[$id]->order_cars;
      $client_id = $items[$id]->client_id;
      if(!isset($clients[$client_id])){
        $clients[$client_id] = new ClientsRecord($this->_db,$client_id);
      }
    }    
    
    return $this->_view->render("orders_list", ['items'=>$items,'clients'=>$clients]);
  }
  
  protected function actionMakeMaket(){
    $orders = OrdersRecord::getAll($this->_db);
    $clients_db = ClientsRecord::getAll($this->_db);
    $clients = [];
    $placer = new Placer($this->_const->car_count);
    foreach ($orders as $order) {
      /* @var $order OrdersRecord */
      $placer->push($order->getId(), $order->format, $order->order_cars);
      $clients[$order->getId()] = $clients_db[$order->client_id];
    }
    $result = $placer->run();
    
    return $this->_view->render("places", ['placer'=>$placer,'all_placed'=>$result,'clients'=>$clients,'error'=>  $placer->getQuene() ]);
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
