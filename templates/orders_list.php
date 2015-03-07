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
<table>
  <thead>
    <tr>
      <td>№</td>
      <td>Добавлено</td>
      <td>Организация</td>
      <td>Формат</td>
      <td>Машин</td>
      <td>Начало</td>
      <td>Окончание</td>
      <td>Стоимость</td>
      <td>Оплата</td>
      <td></td>
    </tr>
  </thead>
  <tbody>    
    <?php foreach ($items as $item):
      $client = $clients[$item->client_id];
      ?>
    <tr>
      <td><?= $item->id           ?></td>
      <td><?= date("d-m-Y",$item->create_time)  ?></td>
      <td><?= $client->name."<br>".$client->person."<br>".$client->phone?></td>      
      <td><?= "A".$item->format   ?></td>
      <td><?= $item->order_cars   ?></td>
      <td><?= date("d-m-Y",$item->order_time)   ?></td>
      <td><?= date("d-m-Y",$item->finish_time)  ?></td>
      <td><?= $item->price        ?></td>
      <td><a href="<?= AppHelper::indexRoute('order-pay', ['id'=>$item->id])?>" class="pay <?= $item->pay?"payed":""?>"></a></td>
      <td><a href="<?= AppHelper::indexRoute('order-delete',['id'=>$item->id])?>" d>Удалить</a></td>
    </tr>
    <?php endforeach;?>
  </tbody>
</table>