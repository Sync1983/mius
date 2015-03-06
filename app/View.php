<?php

/**
 * Description of View
 *
 * @author Sync<atc58.ru>
 */
namespace app;

class View {
  protected $_view_path = "";
  
  public function __construct() {
    $path = __DIR__;
    $pos = strpos($path,"app");
    $path = substr($path, 0 , $pos);
    $this->_view_path = $path;
  }


  public function renderText($text,$params=[]){
    ob_start();
    ob_implicit_flush(false);
    extract($params, EXTR_OVERWRITE);
    echo($text);

    return ob_get_clean();
  }

  public function render($view,$params=[]){    
    if(!is_file($this->_view_path."/templates/".$view.".php")){      
      return false;
    }
    ob_start();
    ob_implicit_flush(false);
    extract($params, EXTR_OVERWRITE);
    require($this->_view_path."/templates/".$view.".php");

    return ob_get_clean();
  }
}
