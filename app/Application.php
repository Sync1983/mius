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
use app\helpers\AppConstants;
use app\models\ClientsRecord;
use app\models\OrdersRecord;
use app\models\Placer;
use app\models\LogRecord;

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
    $orders = OrdersRecord::getAll($this->_db,["WHERE order_time "=>">=".time()]);
    $item = new OrdersRecord($this->_db);
    return $this->_view->render("order",[
        'clients'  => $clients,
        'item'     => $item,        
        'orders'   => $orders,
        'change'   => true,
      ]);    
  }
  
  protected function actionCheck() {    
    $order = new OrdersRecord($this->_db);    
    
    $order->create_time = time();
    $order->client_id   = 0;
    $order->format      = 3;
    $order->order_cars  = 1;
    $order->setStartTime (time());
    $order->setOrderLength(1);    
    
    $clients = ClientsRecord::getAll($this->_db);    
    
    //$result = $order->save();
    return $this->_view->render("check",[
        'clients'  => $clients,
        'item'     => $order,        
        'change'   => false
      ]);    
  }
  
  protected function actionCheckStart() {    
    $request = $_POST;
    $order = new OrdersRecord($this->_db);    
    
    $order->create_time = time();    
    $order->client_id   = AppHelper::initFromArray('client_id', $request);
    $order->format      = AppHelper::initFromArray('format', $request);
    $order->order_cars  = AppHelper::initFromArray('count', $request);
    $order->setStartTime (AppHelper::initFromArray('start_time', $request));
    $order->setOrderLength(AppHelper::initFromArray('order_time', $request));    
    
    $clients = ClientsRecord::getAll($this->_db);    
    
    $orders = OrdersRecord::getAll($this->_db,["WHERE order_time "=>"BETWEEN ".$order->order_time." AND ".$order->finish_time]);
    $orders_pay = OrdersRecord::getAll($this->_db,["WHERE pay=1 and order_time "=>"BETWEEN ".$order->order_time." AND ".$order->finish_time]);
    
    $placer = new Placer($this->_const->car_count);
    $placer_pay = new Placer($this->_const->car_count);
    $placer->push($order);
    $placer_pay->push($order);
    
    foreach ($orders as $order_t) {
      $placer->push($order_t);
    }
    foreach ($orders_pay as $order_t) {
      $placer_pay->push($order_t);
    }
    $result = $placer->run();
    $result_pay = $placer_pay->run();
    
    $log = new LogRecord($this->_db);
    $log->fill(LogRecord::CHECK,[
      'fmt'=> $order->format,
      'cnt'=> $order->order_cars,
      'finish'=> $order->orderLength(),
      'start'=> date("d-m-Y",$order->order_time),
      'client'=> $clients[$order->client_id]->name,
      'result'=>"[".($result*1).",".($result_pay*1)."]"]);
    $log->save();
    
    return $this->_view->render("check_answer",[
        'result'      => $result,
        'result_pay'  => $result_pay,
        'placer'      => $placer,
        'placer_pay'  => $placer_pay,
        'item'        => $order,
      ]);    
  }
  
  protected function actionOrderAdd() {
    $request = $_POST;
    
    $order = new OrdersRecord($this->_db);    
    $order->create_time = time();
    $order->client_id   = AppHelper::initFromArray('client_id', $request);
    $order->format      = AppHelper::initFromArray('format', $request);
    $order->order_cars  = AppHelper::initFromArray('count', $request);
    $order->setStartTime (AppHelper::initFromArray('start_time', $request));
    $order->setOrderLength(AppHelper::initFromArray('order_time', $request));    
    $deprecated = AppHelper::initFromArray('deprecate', $request);     
    $order->deprecate   = implode(",", $deprecated);
    
    $clients = ClientsRecord::getAll($this->_db);    
    $orders = OrdersRecord::getAll($this->_db,["WHERE order_time "=>"BETWEEN ".$order->order_time." AND ".$order->finish_time]);
    
    $placer = new Placer($this->_const->car_count);
    $placer->push($order);
    
    foreach ($orders as $order_t) {
      $placer->push($order_t);
    }    
    $check = $placer->run();
    if(!$check){
      return $this->_view->render("order",[
          'clients'  => $clients,
          'item'     => $order,        
          'error'    => "Ошибка добавления! Заказ невозможно разместить"
        ]);      
    }    
    
    $result = $order->save();
    
    $log = new LogRecord($this->_db);
    $log->fill(LogRecord::ADD_ORDER,[
      'id' => $order->id,
      'fmt'=> $order->format,
      'cnt'=> $order->order_cars,
      'finish'=> $order->orderLength(),
      'start'=> date("d-m-Y",$order->order_time),
      'client'=> $clients[$order->client_id]->name]);      
    $log->save();
    
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
        'change'   => false,
        'info'     =>"Размещение начнется <b>$s_time_txt</b> и закончится <b>$f_time_txt</b>.<br>Стоимость заказа составляет $pay_text"
      ]);    
  }
  
  protected function actionOrderChangeSave() { 
    $request = $_POST;
    $id = AppHelper::initFromArray('id', $request);
    $clients = ClientsRecord::getAll($this->_db);    
    
    $order = new OrdersRecord($this->_db,$id);  
    $params = [
      'id'      => $id,
      'fmt'     => $order->format,
      'cnt'     => $order->order_cars,
      'finish'  => $order->orderLength(),
      'start'   => date("d-m-Y",$order->order_time),
      'client'  => $clients[$order->client_id]->name,
    ];
    
    $order->client_id   = AppHelper::initFromArray('client_id', $request);
    $order->format      = AppHelper::initFromArray('format', $request);
    $order->order_cars  = AppHelper::initFromArray('count', $request);
    $order->setStartTime (AppHelper::initFromArray('start_time', $request));
    $order->setOrderLength(AppHelper::initFromArray('order_time', $request));    
    $deprecated = AppHelper::initFromArray('deprecate', $request);     
    $order->deprecate   = implode(",", $deprecated);
    $orders = OrdersRecord::getAll($this->_db,["WHERE order_time "=>"BETWEEN ".$order->order_time." AND ".$order->finish_time]);
    
    $placer = new Placer($this->_const->car_count);
    $placer->push($order);
    
    foreach ($orders as $order_t) {
      $placer->push($order_t);
    }    
    $check = $placer->run();
    if(!$check){
      return $this->_view->render("order",[
          'clients'  => $clients,
          'item'     => $order,
          'change'   => false,
          'error'    => "Ошибка добавления! Заказ невозможно разместить"
        ]);      
    }    
    $result = $order->save();
    $log = new LogRecord($this->_db);
    $log->fill(LogRecord::EDIT_ORDER,  array_merge($params,[ 
      'id'      => $order->getId(),
      'fmt1'    => $order->format,
      'cnt1'    => $order->order_cars,
      'finish1' => $order->orderLength(),
      'start1'  => date("d-m-Y",$order->order_time),
      'client1' => $clients[$order->client_id]->name]));      
    $log->save();
    if(!$result){
      return $this->_view->render("orders_list",[          
          'item'     => $order,        
          'error'    => "Ошибка сохранения! ".$this->_db->getErrorList()
        ]);      
    }
    
    return $this->_view->redirect("index.php?route=orders-list");
  }
  
  protected function actionOrderChange() {
    $id = AppHelper::initFromArray('id', $_GET);
    $order = new OrdersRecord($this->_db, $id);        
    
    $clients = ClientsRecord::getAll($this->_db);    
    $orders = OrdersRecord::getAll($this->_db,["WHERE order_time "=>">=".time()]);
    
    return $this->_view->render("order",[
        'clients'  => $clients,
        'item'     => $order,  
        'change'   => false,
        'orders'   => $orders,
        'deprecated' => explode(",", $order->deprecate),
        'info'     =>"Редактирование записи"
      ]);    
  }
  
  protected function actionOrderDelete(){
    $id = AppHelper::initFromArray('id', $_GET);
    $order = new OrdersRecord($this->_db,$id);
    $clients = ClientsRecord::getAll($this->_db);    
    $log = new LogRecord($this->_db);
    $log->fill(LogRecord::DEL_ORDER,[
      'id' => $order->id,
      'fmt'=> $order->format,
      'cnt'=> $order->order_cars,
      'finish'=> $order->orderLength(),
      'start'=> date("d-m-Y",$order->order_time),
      'client'=> $clients[$order->client_id]->name]);      
    $log->save();
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
    $test_order = new OrdersRecord($this->_db);
    $test_order->setStartTime(time());
    $test_order->setOrderLength(1);
    
    $orders = OrdersRecord::getAll($this->_db,["WHERE order_time "=>"BETWEEN ".$test_order->order_time." AND ".$test_order->finish_time]);
    $clients_db = ClientsRecord::getAll($this->_db);
    $clients = [];
    $placer = new Placer($this->_const->car_count);
    foreach ($orders as $order) {
      /* @var $order OrdersRecord */
      $placer->push($order);
      $clients[$order->getId()] = $clients_db[$order->client_id];
    }
    $result = $placer->run();
    
    return $this->_view->render("places", [
      'placer'      => $placer,
      'all_placed'  => $result,
      'clients'     => $clients,
      'error'       => $placer->getQuene(),
      'show_start'  => $test_order->order_time,
      'show_finish' => $test_order->finish_time ]);
  }
  
  protected function actionMakeMaketPay(){
    $test_order = new OrdersRecord($this->_db);
    $test_order->setStartTime(time());
    $test_order->setOrderLength(1);
    
    $orders = OrdersRecord::getAll($this->_db,["WHERE pay = 1 AND order_time "=>"BETWEEN ".$test_order->order_time." AND ".$test_order->finish_time]);
    $clients_db = ClientsRecord::getAll($this->_db);
    $clients = [];
    $placer = new Placer($this->_const->car_count);
    foreach ($orders as $order) {
      /* @var $order OrdersRecord */
      $placer->push($order);
      $clients[$order->getId()] = $clients_db[$order->client_id];
    }
    $result = $placer->run();
    
    return $this->_view->render("places", [
      'placer'      => $placer,
      'all_placed'  => $result,
      'clients'     => $clients,
      'error'       => $placer->getQuene(),
      'show_start'  => $test_order->order_time,
      'show_finish' => $test_order->finish_time ]);
  }
  
  protected function actionCountFree(){
    $test_order = new OrdersRecord($this->_db);
    $test_order->setStartTime(time());
    $test_order->setOrderLength(1);
    
    $orders = OrdersRecord::getAll($this->_db,["WHERE order_time "=>"BETWEEN ".$test_order->order_time." AND ".$test_order->finish_time]);$placer = new Placer($this->_const->car_count);
    foreach ($orders as $order) {
      /* @var $order OrdersRecord */
      $placer->push($order);
    }
    $result = $placer->run();
    
    return $this->_view->render("cnt_places", [
      'placer'      => $placer,
      'all_placed'  => $result,      
      'error'       => $placer->getQuene(),
      'show_start'  => $test_order->order_time,
      'show_finish' => $test_order->finish_time ]);
  }
  
  protected function actionCountFreePay(){
    $test_order = new OrdersRecord($this->_db);
    $test_order->setStartTime(time());
    $test_order->setOrderLength(1);
    
    $orders = OrdersRecord::getAll($this->_db,["WHERE pay = 1 AND order_time "=>"BETWEEN ".$test_order->order_time." AND ".$test_order->finish_time]);$placer = new Placer($this->_const->car_count);
    foreach ($orders as $order) {
      /* @var $order OrdersRecord */
      $placer->push($order);
    }
    $result = $placer->run();
    
    return $this->_view->render("cnt_places", [
      'placer'      => $placer,
      'all_placed'  => $result,      
      'error'       => $placer->getQuene(),
      'show_start'  => $test_order->order_time,
      'show_finish' => $test_order->finish_time ]);
  }
  
  public function actionLogs(){
    $logs = LogRecord::getAll($this->_db);
    return $this->_view->render("logs_out",['logs'=>$logs]);
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
  
}
