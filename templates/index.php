<?php
use app\helpers\AppHelper;
?>
<ul class="index-list">
  <li><a href="<?= AppHelper::indexRoute("index")?>">+Главная</a></li>
  <li><a href="<?= AppHelper::indexRoute("car-count")?>">+Изменить количество машин</a></li>
  <li><a href="<?= AppHelper::indexRoute("index")?>">Проверить возможность заказа</a></li>
  <li><a href="<?= AppHelper::indexRoute("order")?>">Добавить заказ</a></li>
  <li><a href="<?= AppHelper::indexRoute("clients")?>">+Управление клиентами</a></li>
  <li><a href="<?= AppHelper::indexRoute("orders-list")?>">+Просмотреть заказы</a></li>
  <li><a href="<?= AppHelper::indexRoute("index")?>">Посчитать свободные площадки</a></li>
  <li><a href="<?= AppHelper::indexRoute("index")?>">Посчитать свободные площадки (<b>только по оплаченным</b>)</a></li>
  <li><a href="<?= AppHelper::indexRoute("index")?>">Сформировать макеты</a></li>
  <li><a href="<?= AppHelper::indexRoute("index")?>">Сформировать макеты (<b>только по оплаченным</b>)</a></li>
</ul>