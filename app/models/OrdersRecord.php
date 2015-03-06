<?php

/**
 * Description of ClientsRecord
 *
 * @author Sync<atc58.ru>
 */
namespace app\models;
use app\helpers\ActiveRecord;

class OrdersRecord extends ActiveRecord{
  protected $_table_name = "orders";
  protected $_id_name = "id";
}
