<?php

class pageperms {
  protected $_formid = 0;
  protected $_isview = 0;
  protected $_isviewextended = 0;
  protected $_ismodify = 0;
  protected $_ismodifyextended = 0;
  protected $_isdelete = 0;  
  protected $_canview = 0;  
  protected $_canmodify = 0; 
    
  
  public function __construct() {
      
    }
    
  public static function withRow( array $row ) {
    	$instance = new static();
    	$instance->fill( $row );
    	return $instance;
    }

  protected function fill( array $array ) {
    	if(isset($array)) {
            $this->_formid = $array['formid'];
            $this->_isview = $array['isview'];
            $this->_isviewextended = $array['isviewextended'];
            $this->_ismodify = $array['ismodify'];
            $this->_ismodifyextended = $array['ismodifyextended'];
            $this->_isdelete = $array['isdelete'];
            if ($array['isview'] == 1) {
	      $this->_canview = $array['isview'];
	    }
	    elseif ($array['isviewextended'] == 1) {
	      $this->_canview = $array['isviewextended'];
	    }
	    else {
	      $this->_canview = $array['isview'];
	    }
	    if ($array['ismodify'] == 1) {
	      $this->_canmodify = $array['ismodify'];
	    }
	    elseif ($array['ismodifyextended'] == 1) {
	      $this->_canmodify = $array['ismodifyextended'];
	    }
	    else {
	      $this->_canmodify = $array['ismodify'];
	    }
        }
    }
  
  
  
  function set_formid($new_formid) { 
    $this->_formid = $new_formid;  
  }
  function set_isview($new_isview) { 
    $this->_isview = $new_isview;  
  }
  function set_isviewextended($new_isviewextended) { 
    $this->_isviewextended = $new_isviewextended;  
  }
  function set_ismodify($new_ismodify) { 
    $this->_ismodify = $new_ismodify;  
  }
  function set_ismodifyextended($new_ismodifyextended) { 
    $this->_ismodifyextended = $new_ismodifyextended;  
  }
  function set_isdelete($new_isdelete) { 
    $this->_isdelete = $new_isdelete;  
  }
  function set_canview($new_canview) { 
    $this->_canview = $new_canview;  
  }
  function set_canmodify($new_canmodify) { 
    $this->_canmodify = $new_canmodify;  
  }
  
  function get_formid() {
    return $this->_formid;
  }
  function get_isview() {
    return $this->_isview;
  }
  function get_isviewextended() {
    return $this->_isviewextended;
  }
  function get_ismodify() {
    return $this->_ismodify;
  }
  function get_ismodifyextended() {
    return $this->_ismodifyextended;
  }
  function get_isdelete() {
    return $this->_isdelete;
  }
  function get_canview() {
    return $this->_canview;
  }
  function get_canmodify() {
    return $this->_canmodify;
  }

}
  
?>
