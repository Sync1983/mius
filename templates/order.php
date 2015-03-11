<?php 
use app\helpers\AppHelper;
use app\models\ClientsRecord;

/* @var $client ClientsRecord */
/* @var $order \app\models\OrdersRecord */    

if(!isset($change)){
  $change = false;
}
if(!isset($orders)){
  $orders = [];
}
if(!isset($deprecated)){
  $deprecated = [];
}
?>

  <?php if(isset($error)&&$error):?>  
    <div class="error">
      <span> <?= $error ?></span>
    </div>
  <?php endif;?>
  <?php if(isset($info)):?>  
    <div class="info">
      <span> <?= $info?></span>
    </div>
  <?php endif;?>

<a href="<?= AppHelper::indexRoute("index")?>" class = "btn">Назад</a>
<form action="<?= ($change)?AppHelper::indexRoute('order-add'):AppHelper::indexRoute('order-change-save');?>" method="POST" class="order-input">
  <input type="hidden" name = "id" value="<?= $item->id?>" >
  <div class="row">
    <label for="client_id">Организация</label>        
    <select name="client_id">
      <?php foreach ($clients as $key=>$client): ?>
      <option value="<?=$client->getId()?>" <?= ($client->id==$item->client_id)?"selected":"" ?>>
        <?= $client->name." [".$client->person.", ".$client->phone."]"?>
      </option>        
      <?php endforeach;?>
    </select>    
  </div>  
  <div class="row">
    <label for="format" style="vertical-align: middle;height: 70px;">Формат площадки</label>
    <input name="format" type="radio" value="3" <?= $item->format==3?"checked":""?>><img src="/img/A3.png">
    <input name="format" type="radio" value="4" <?= $item->format==4?"checked":""?>><img src="/img/A4.png">
    <input name="format" type="radio" value="5" <?= $item->format==5?"checked":""?>><img src="/img/A5.png">
  </div>
  <div class="row">
    <label for="count">Количество машин</label>
    <input name="count" type="number" placeholder="Установите необходимое количество машин"  value="<?= $item->order_cars?>"/>
  </div>
  <div class="row">
    <label for="start_time">Начало размещения</label>
    <input name="start_time" type="date" placeholder="Введите время начала публикациии"  value="<?= strftime("%Y-%m-%d", $item->order_time)?>"/>
  </div>
  <div class="row">
    <label for="order_time">Длительность размещения</label>    
    <input name="order_time" type="number" placeholder="Введите количество месяцев"  value="<?= $item->orderLength()?>"/>
  </div>
  <?php      foreach ($orders as $key=>$order): ?>
    <input 
      type="checkbox" 
      name="deprecate[]" 
      value="<?= $order->getId() ?>" 
      <?= in_array($order->getId(), $deprecated)?"checked":""?> />
      <span>Номер заказа:<?= $order->getId() ?>
        Клиент: <?= $clients[$order->client_id]->name?>
        [Машин: <?= $order->order_cars ?>
        Размер: A<?= $order->format?>]
      </span><br />        
    
  <?php endforeach;?>
  <?php if(!$change):?>
  <div class="row">    
    <input type="submit" value="Сохранить"/>
  </div>        
  <?php endif;?>
</form>

