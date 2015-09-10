<?php
class EventElement {
  public static $name;
  public static $type;
  public static $size;
  public $value;

  
  function __construct($nam, $typ = "VARCHAR", $siz = 0, $val = 0) { 
    $name = $nam;
    $type = $typ;
    $size = $siz;
    $value = $val;
  } 

  public function header() {
    return "<th>" . $name . "</th>";
  }
  
  
  
  public function table_entry() {
    return name_for_table() . " " . $type . "(" . $size . ")";
  }
  
  public function name_for_table() {
    return strtolower($name);
  }
}

class Title extends EventElement {
    function __construct($val = 0) { 
      parent::__construct("Title", "VARCHAR", 255, $val);
    }
}

class Date extends EventElement {
    function __construct($val = 0) { 
      parent::__construct("Date", "DATE", 0, $val);
    }
    
    function table_entry() {
      return name_for_table() . " " . $type;
    }
}

class Time extends EventElement {
    function __construct($val = 0) { 
      parent::__construct("Time", "VARCHAR", 20, $val);
    }
}
  
class Location extends EventElement {
    function __construct($val = 0) { 
      parent::__construct("Location", "VARCHAR", 255, $val);
    }
}

class Brief extends EventElement {
    function __construct($val = 0) { 
      parent::__construct("Brief", "VARCHAR", 255, $val);
    }
}

class Description extends EventElement {
    function __construct($val = 0) { 
      parent::__construct("Description", "VARCHAR", 600, $val);
    }
}
class Event {
  public $title = new Title();
  public $date = new Date();
  public $time = new Time();
  public $location = new Location();
  public $brief = new Brief();
  public $description = new Description();
  
  function set_fields($tit, $dat, $tim, $loc, $bri, $des) { 
    $title->value = $tit;
    $date->value = $dat;
    $time->value = $tim;
    $location->value = $loc;
    $brief->value = $bri;
    $description->value = $des;
  }
  
  function fields_as_array() {
    return array($title, $date, $time, $location, $brief, $description);
  }
  
  function header() {
    $header = "<thead>";
    foreach (fields_as_array() as $event_element) {
      $header .= $event_element->header();
    }
    $header .= "</thead>";
    return $header;
  }
  
  function table_row_query($chopped = 0) {
    foreach ($event->fields_as_array() as $event_element) {
      $sql_query .= $event_element->name_for_table() . ",";
    }
    return do_chop($chopped, $sql_query);
  }
  
  function table_row_values($chopped = 0) {
    foreach ($event->fields_as_array() as $event_element) {
      $sql_query .= "'" . $event_element->value . "',";
    }
    return do_chop($chopped, $sql_query);
  }
  
    function do_chop($chopped, $str) {
    if($chopped) {
      return chop($str,",");
    }
    return $str;
  }
}
?>
