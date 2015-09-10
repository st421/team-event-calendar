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

  public function table_header_title_string() {
    return "<th>" . $name . "</th>";
  }
  
  public function table_row_init() {
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
  
  public function set_fields($tit, $dat, $tim, $loc, $bri, $des) { 
    $title->value = $tit;
    $date->value = $dat;
    $time->value = $tim;
    $location->value = $loc;
    $brief->value = $bri;
    $description->value = $des;
  }
  
  public function fields_as_array() {
    return array($title, $date, $time, $location, $brief, $description);
  }
  
  public function table_header_string() {
    $header = "<thead>";
    foreach (fields_as_array() as $event_element) {
      $header .= $event_element->table_header_title_string();
    }
    $header .= "</thead>";
    return $header;
  }
  
  public function table_row_string() {
    $header = "<tr>";
    foreach (fields_as_array() as $event_element) {
      $header .= "<td>" . $event_element->value . "</td>";
    }
    $header .= "</tr>";
    return $header;
  }
  
  public function get_page_link_start($page_id) {
    return '<a href="/?page_id=' . $page_id . '&title=' . $title . '&date=' . $date . '">';
  }
  
  public function list_element_string($page_id) {
    $list_str = '<li>' . get_page_link_start();
    $list_str .= '<h3>' . $title . '</h3>';
    $list_str .= '<h3 class="date">' . tec_format_date($event->date, '-', '.') . '</h3>';
    $list_str .= '</a>' . $event->brief . '</li></br>';
    return $list_str;
  }
  
  public function table_row_query($chopped = 1) {
    foreach ($event->fields_as_array() as $event_element) {
      $sql_query .= $event_element->name_for_table() . ",";
    }
    return do_chop($chopped, $sql_query);
  }
  
  public function table_row_values($chopped = 1) {
    foreach ($event->fields_as_array() as $event_element) {
      $sql_query .= "'" . $event_element->value . "',";
    }
    return do_chop($chopped, $sql_query);
  }
  
  public function table_row_update($chopped = 1) {
    foreach ($event->fields_as_array() as $event_element) {
      $sql_query .= $event_element->title . "='" . $event_element->value . "',";
    }
    return do_chop($chopped, $sql_query);
  }
  
  function do_chop($chopped, $str) {
    if($chopped) {
      return chop($str,",");
    }
    return $str;
  }
  
  function tec_format_date($date, $old, $new) {
  	$pieces = explode($old,$date);
  	if($new == '-') {
  		$new_date = $pieces[2] . $new . $pieces[0] . $new . $pieces[1];
  	} else {
  		$new_date = $pieces[1] . $new . $pieces[2] . $new . $pieces[0];
  	}
  	return $new_date;
    }
  }
?>
