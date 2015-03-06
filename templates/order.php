<?php 
use app\helpers\AppHelper;
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
<form action="<?= AppHelper::indexRoute('order-add');?>" method="POST" class="order-input">
  <div class="row">
    <label for="name">Организация</label>
    <input name="name" type="text" placeholder="Введите название организации" value="<?= $name ?>"/>
  </div>
  <div class="row">
    <label for="user">Контактное лицо</label>
    <input name="user" type="text" placeholder="Заполните данные контакного лица"  value="<?= $user ?>"/>
  </div>
  <div class="row">
    <label for="phone">Контактный телефон</label>
    <input name="phone" type="text" placeholder="Введите номер телефона"  value="<?= $phone ?>"/>
  </div>
  <div class="row">
    <label for="format" style="vertical-align: middle;height: 70px;">Формат площадки</label>
    <input name="format" type="radio" value="3" <?= $format==3?"checked":""?>><img src="/img/A3.png">
    <input name="format" type="radio" value="4" <?= $format==4?"checked":""?>><img src="/img/A4.png">
    <input name="format" type="radio" value="5" <?= $format==5?"checked":""?>><img src="/img/A5.png">
  </div>
  <div class="row">
    <label for="count">Количество машин</label>
    <input name="count" type="number" placeholder="Установите необходимое количество машин"  value="<?= $count ?>"/>
  </div>
  <div class="row">
    <label for="start_time">Начало размещения</label>
    <input name="start_time" type="date" placeholder="Введите время начала публикациии"  value="<?= $start_time?>"/>
  </div>
  <div class="row">
    <label for="order_time">Длительность размещения</label>
    <input name="order_time" type="number" placeholder="Введите количество месяцев"  value="<?= $order_time?>"/>
  </div>
  <div class="row">    
    <input type="submit" value="Сохранить"/>
  </div>
</form>

