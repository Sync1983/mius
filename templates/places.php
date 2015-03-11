<?php
use app\helpers\AppHelper;
use app\models\Place;
use app\models\Placer;
/* @var $placer Placer */
?>

  <?php if(!$all_placed):?>  
    <div class="error">
      <span> Не удалось разместить все заказы! <?= $error ?></span>
    </div>
  <?php endif;?>
  <?php if($all_placed):?>  
    <div class="info">
      <span> Все заказы размещены</span>
    </div>
  <?php endif;?>
  <a href="<?= AppHelper::indexRoute("index")?>" class = "btn">Назад</a>
  <p>Сформированы макеты для заказов в период: (<?= date("d-m-Y",$show_start) ?> - <?= date("d-m-Y",$show_finish)?>)</p>
  <?php  foreach ($placer as $key=>$value): 
    /* @var $place Place */    
    $place = $value['place'];
    $rows = $place->getTable();
    $count = $value['count'];
    $data = $place->getData();
    $info = "<ul class=\"info\">";
    foreach ($data as $id=>$format){
      $client = $clients[$id];
      $info .= "<li>[A$format] :".$client->name."[".$client->id."]"." Заказ №:".$id. "</li>";
    }
    $info .= "</ul>"
    ?>
    <div class="place-shower">
      <ul class="place-shower">
        <li class="count"><?=$count?></li>  
        <li>          
          <table class="place-shower" id="place<?= $key ?>">        
            <tr name="row1"><?=$rows[0]?></tr>
            <tr name="row2"><?=$rows[1]?></tr>
          </table>
        </li>
        <li><?= $info ?></li>  
      </ul>
    </div>
      
  <?php endforeach;?>

  <span>
    <?php $free = $placer->getUnOptium();?>
    <ul>
      <li>Свободно машин для размещения [A5]: <?= AppHelper::initFromArray(1, $free) ?></li>
      <li>Свободно машин для размещения [A4]: <?= AppHelper::initFromArray(2, $free) ?></li>
      <li>Свободно машин для размещения [1A4+1A5]: <?= AppHelper::initFromArray(3, $free) ?></li>
      <li>Свободно машин для размещения [A3]: <?= AppHelper::initFromArray(4, $free) ?></li>
    </ul>
  
  </span>