<?php
/**
 * Description of Error
 *
 * @author Sync<atc58.ru>
 */
namespace app\actions;
use app\View;

class Error extends View{
  private $_text = '<h3 style="padding:0">Ошибка!</h3><span style = "background-color:#DDAAAA">[message]</span>';
  private $_error_message = "error";

  public function __construct($message = "") {
    parent::__construct();
    if($message!==""){
      $this->_error_message = $message;
    }
  }
  public function out(){
    $text = $this->_text;
    $text = str_replace("[message]", $this->_error_message, $text);
    return $this->renderText($text);
  }
}
