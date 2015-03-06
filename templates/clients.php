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
  
<table>
  <thead>
    <td>Номер</td>
    <td style="width: 35%;">Название</td>
    <td style="width: 35%;">Контактное лицо</td>
    <td style="width: 20%;">Телефон</td>
    <td style="width: 10%;">Дата добавления</td>    
    <td style="width: 5%;"></td>    
    <td style="width: 5%;"></td>    
  </thead>
  <tbody>
    <?php foreach ($data as $row):
      /* @var $row ClientsRecord */
      ?>
    <tr>
      <td><?= $row->getId();?></td>
      <td><input type="text" name="name"    value="<?= $row->name;?>"/></td>
      <td><input type="text" name="person"  value="<?= $row->person;?>"/></td>
      <td><input type="text" name="phone"   value="<?= $row->phone;?>"/></td>
      <td><?= date("d-m-Y",$row->create_time);?></td>
      <td><a href="#" onclick="onChange(this,<?=$row->getId()?>);">Изменить</a></td>
      <td><a href="<?= AppHelper::indexRoute('client-delete', ['id'=>$row->getId()])?>">Удалить</a></td>
    </tr>      
    <?php endforeach;?>
    <tr>
      <td>+</td>
      <td><input type="text" name="name"    value="" placeholder="Введите название компании"/></td>
      <td><input type="text" name="person"  value="" placeholder="Введите контактное лицо"/></td>
      <td><input type="text" name="phone"   value="" placeholder="Введите контактный телефон"/></td>
      <td><?= date("d-m-Y");?></td>
      <td><a href="#" onclick="onCreate(this);">Добавить</a></td>
      <td></td>
    </tr>      
  </tbody>
</table>

<script type="text/javascript">
  function getParam(item){
    var params = '';
    var parent = item.parentNode.parentNode;
    var childrens_td = parent.childNodes;
    for(var i = 0; i<childrens_td.length; i++){
      var children_td = childrens_td[i];
      if(children_td.nodeName==="#text"){
        continue;
      }
      var childrens = children_td.getElementsByTagName('input');      
      if(childrens.length<=0){
        continue;
      }
      var input = childrens[0];      
      params += '&'+input.name+'='+input.value;      
    }    
    return params;
  }
  
  function onChange(item,id){
    var params = '&id='+id+ getParam(item);
    document.location = "<?= AppHelper::indexRoute("client-change")?>"+params;
  }
  
  function onCreate(item){
    var params = getParam(item);    
    document.location = "<?= AppHelper::indexRoute("client-create")?>"+params;    
  }
</script>
