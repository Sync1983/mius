<?php
/**
 * Description of AppHelper
 *
 * @author Sync<atc58.ru>
 */
namespace app\helpers;

class AppHelper {
  
  public static function indexRoute($action="",$params=[]){
    if($action==""){
      return "index.php";      
    }
    $get_params = "";
    if(count($params)>0){
      $get_params = "&".http_build_query($params);
    }
    return "index.php?route=".$action.$get_params;
  }

  public static function initFromArray($needle = "",$array = []){
    return isset($array[$needle])?$array[$needle]:false;
  }
}
