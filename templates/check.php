<?php 
use app\helpers\AppHelper;
use app\models\ClientsRecord;
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
<form action="<?= AppHelper::indexRoute('check-start')?>" method="POST" class="order-input">  
  <div class="row">
    <label for="client_id">Организация</label>        
    <select name="client_id">
      <?php foreach ($clients as $key=>$client):
        /* @var $client ClientsRecord */
        ?>
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
  <div class="row">    
    <input type="submit" value="Проверить"/>
  </div>
</form>

