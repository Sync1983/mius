<?php

/**
 * Description of ClientsRecord
 *
 * @author Sync<atc58.ru>
 */
namespace app\models;
use app\helpers\ActiveRecord;

class ClientsRecord extends ActiveRecord{
  protected $_table_name = "clients";
  protected $_id_name = "id";
}
