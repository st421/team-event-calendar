<?php
class EventElement {
  public $name;
  public $type;
  public $size;
  public $value;

  
  function __construct($nam, $typ = "VARCHAR", $siz = 0, $val = 0) { 
    $name = $nam;
    $type = $typ;
    $size = $siz;
    $value = $val;
  } 

  function header() {
    return "<th>" . $name . "</th>";
  }
  
  function table_entry() {
    return name_for_table() . " " . $type . "(" . $size . ")";
  }
  
  function name_for_table() {
    return strtolower($name);
  }
}

class Title extends EventElement {
    function __construct($val) { 
      parent::__construct("Title", "VARCHAR", 255, $val);
    }
}

class Date extends EventElement {
    function __construct($val) { 
      parent::__construct("Date", "DATE", 0, $val);
    }
    
    function table_entry() {
      return name_for_table() . " " . $type;
    }
}

class Time extends EventElement {
    function __construct($val) { 
      parent::__construct("Time", "VARCHAR", 20, $val);
    }
}
  
class Location extends EventElement {
    function __construct($val) { 
      parent::__construct("Location", "VARCHAR", 255, $val);
    }
}

class Brief extends EventElement {
    function __construct($val) { 
      parent::__construct("Brief", "VARCHAR", 255, $val);
    }
}

class Description extends EventElement {
    function __construct($val) { 
      parent::__construct("Description", "VARCHAR", 600, $val);
    }
}
class Event {
  public $title;
  public $date;
  public $time;
  public $location;
  public $brief;
  public $description;
  
  function __construct($tit, $dat, $tim, $loc, $bri, $des) { 
    $title = new Title($tit);
    $date = new Date($dat);
    $time = new Time($tim);
    $location = new Location($loc);
    $brief = new Brief($bri);
    $description = new Description($des);
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
}
?>
