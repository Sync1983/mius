<?php
use app\helpers\AppHelper;
?>
<ul class="index-list">
  <li><a href="<?= AppHelper::indexRoute("index")?>">+Главная</a></li>
  <li><a href="<?= AppHelper::indexRoute("car-count")?>">+Изменить количество машин</a></li>
  <li><a href="<?= AppHelper::indexRoute("check")?>">+Проверить возможность заказа</a></li>
  <li><a href="<?= AppHelper::indexRoute("order")?>">+Добавить заказ</a></li>
  <li><a href="<?= AppHelper::indexRoute("clients")?>">+Управление клиентами</a></li>
  <li><a href="<?= AppHelper::indexRoute("orders-list")?>">+Просмотреть заказы</a></li>
  <li><a href="<?= AppHelper::indexRoute("count-free")?>">+Посчитать свободные площадки</a></li>
  <li><a href="<?= AppHelper::indexRoute("count-free-pay")?>">+Посчитать свободные площадки (<b>только по оплаченным</b>)</a></li>
  <li><a href="<?= AppHelper::indexRoute("make-maket")?>">+Сформировать макеты</a></li>
  <li><a href="<?= AppHelper::indexRoute("make-maket-pay")?>">+Сформировать макеты (<b>только по оплаченным</b>)</a></li>
  <li><a href="<?= AppHelper::indexRoute("logs")?>">+Логи</a></li>
</ul>
