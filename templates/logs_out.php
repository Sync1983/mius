<?php
use app\helpers\AppHelper;
use app\models\LogRecord;

?>  
<a href="<?= AppHelper::indexRoute("index")?>" class = "btn">Назад</a>
  
<table>
  <thead>    
    <td style="width: 10%;">ID</td>
    <td style="width: 10%;">Дата</td>
    <td style="width: 20%;">Действие</td>
    <td style="width: 60%;">Описание</td>
  </thead>
  <tbody>
    <?php foreach ($logs as $row):
      /* @var $row LogRecord*/
      ?>
    <tr>
      <td><?= $row->id;?></td>
      <td><?= date("d-m-Y (H:i:s)",$row->time);?></td>
      <td><?= LogRecord::$actions_code[$row->code];?></td>
      <td><?= $row->message;?></td>
    </tr>      
    <?php endforeach;?>
  </tbody>
</table>
