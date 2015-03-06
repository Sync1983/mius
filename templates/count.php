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
  <form class="count-form" action="<?= AppHelper::indexRoute('car-count-save');?>" method="POST">
    <label for="count">Количество автомобилей</label>
    <input type="number" name="count" value="<?=$count?>"/>
    <input type="submit" value="Сохранить">
  </form>
