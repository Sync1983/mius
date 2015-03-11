<?php
use app\helpers\AppHelper;
use app\models\OrdersRecord;
use app\models\Placer;
/* @var $item OrdersRecord */
/* @var $placer Placer*/
/* @var $placer_pay Placer*/
?>

  <a href="<?= AppHelper::indexRoute("index")?>" class = "btn">Назад</a>
  <?php if(!$result):?>  
    <div class="error">
      <span> Разместить в полном списке не удалось</span>
    </div>
  <?php else:?>
    <div class="info">
      <span> Размещается в полном списке успешно</span>
    </div>
  <?php endif;?>
  
  <span>
    <?php $free = $placer->getUnOptium();?>
    <ul>
      <li>Свободно машин для размещения [A5]: <?= AppHelper::initFromArray(1, $free) ?></li>
      <li>Свободно машин для размещения [A4]: <?= AppHelper::initFromArray(2, $free) ?></li>
      <li>Свободно машин для размещения [1A4+1A5]: <?= AppHelper::initFromArray(3, $free) ?></li>
      <li>Свободно машин для размещения [A3]: <?= AppHelper::initFromArray(4, $free) ?></li>
    </ul>  
  </span>


  
  <?php if(!$result_pay):?>  
    <div class="error">
      <span> Разместить в оплаченном списке не удалось</span>
    </div>
  <?php else:?>
    <div class="info">
      <span> Размещается в оплаченном списке успешно</span>
    </div>
  <?php endif;?>
  <span>
    <?php $free_pay = $placer_pay->getUnOptium();?>
    <ul>
      <li>Свободно машин для размещения [A5]: <?= AppHelper::initFromArray(1, $free_pay) ?></li>
      <li>Свободно машин для размещения [A4]: <?= AppHelper::initFromArray(2, $free_pay) ?></li>
      <li>Свободно машин для размещения [1A4+1A5]: <?= AppHelper::initFromArray(3, $free_pay) ?></li>
      <li>Свободно машин для размещения [A3]: <?= AppHelper::initFromArray(4, $free_pay) ?></li>
    </ul>  
  </span>
