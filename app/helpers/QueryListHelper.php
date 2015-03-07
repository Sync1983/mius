<?php

/**
 * Description of QueryListHelper
 *
 * @author Sync<atc58.ru>
 */
namespace app\helpers;

class QueryListHelper {  
  const QUERY_GET_FIELDS            = 1;
  const QUERY_GET_ROW               = 2;
  const QUERY_GET_KEYS              = 3;
  const QUERY_SAVE_ROW              = 4;
  const QUERY_INSERT_ROW            = 5;
  const QUERY_DELETE_ROW            = 6;
    
  const QUERY_GET_PARAMS            = 13;
  const QUERY_SET_PARAMS            = 14;
  const QUERY_NEW_PARAMS            = 15;
  


  private static $querys = [    
    self::QUERY_GET_FIELDS  => 
      "DESCRIBE [tbl_name];",
    self::QUERY_GET_ROW => 
      "SELECT * FROM [tbl_name] WHERE [params] [extend];",
    self::QUERY_GET_KEYS => 
      "SELECT [key_name] as id FROM [tbl_name] [extend];",
    self::QUERY_SAVE_ROW => 
      "UPDATE `[tbl_name]` SET [values] WHERE [id_name]=[id];",
    self::QUERY_INSERT_ROW => 
      "INSERT INTO `[tbl_name]` ([rows]) VALUES ([values]);",
    self::QUERY_DELETE_ROW => 
      "DELETE FROM `[tbl_name]` WHERE [id_name]=[id];",
    
    self::QUERY_GET_PARAMS => 
      "SELECT name,value FROM params;",
    self::QUERY_SET_PARAMS => 
      "UPDATE params SET `value`=[value] WHERE `name`=[name];",
    self::QUERY_NEW_PARAMS => 
      "INSERT INTO params (name,value) VALUES ([name],[value]);",
  ];
  
  public static function getQueryText($uid){
    if(!isset(self::$querys[$uid])){
      return false;
    }
    return self::$querys[$uid];
  }
  
}
