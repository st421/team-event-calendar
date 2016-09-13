<?php  

class TableField { 
  public $name; 
  public $sql; 
  
  function __construct($namey, $sqly) {
    $this->name = $namey;
    $this->sql = $sqly;
  }
}


/* 
 * Creates a table with the given name, if one doesn't exist.
 */
function create_table($table_name, $table_params) {
	global $wpdb;
	$wpdb->show_errors();
	if(!table_exists($table_name)) {
		$sql = "CREATE TABLE " . $table_name . " (" . table_sql($table_params) . ");";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

/* 
 * Drops the table with the given name, if it exists.
 */
function drop_table($table_name) {
	return do_query("DROP TABLE IF EXISTS " . $table_name . ";");
}

/*
 * Creates a SQL statement for the table columns.
 */
function table_sql($table_params) {
	$sql = "id int NOT NULL AUTO_INCREMENT,\n";
	foreach($table_params as $param) {
		$sql .= $param->name . " " . $param->sql . ",\n";
	}
	$sql .= "PRIMARY KEY  (id)";
	return $sql;
}

/* 
 * Returns true if the given table already exists in the wordpress database.
 */
function table_exists($table_name) {
	global $wpdb;
	return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
}

/* 
 * Adds to the table with the given parameters and POST data.
 */
function save_table_item($table_name, $table_params, $post_data) {
	global $wpdb;
	$result = 0;
	$insert = "";
	$vals = "";
	foreach($table_params as $param) {
		$insert .= $param->name . ",";
		if($param->name == "date") {
	    	$post_data["date"] = tec_format_date(sanitize_text_field($post_data["date"]), '/', '-');
		}
		$vals .= "'" . sanitize_text_field($post_data[$param->name]) . "',";
	}
	$vals = substr($vals, 0, -1);
	$insert = substr($insert, 0, -1);
	$query = "INSERT INTO " . $table_name . " (" . $insert . ") VALUES (" . $vals . ");";
	return do_query($query);
}

function update_table_item($table_name, $table_params, $post_data) {
	$result = 0;
	$id = sanitize_text_field($post_data['id']);
	$vals .= "";
	foreach($table_params as $param) {
		if($param->name == "date") {
	    	$post_data["date"] = tec_format_date(sanitize_text_field($post_data["date"]), '/', '-');
		}
		$vals .= $param->name . "='" . sanitize_text_field($post_data[$param->name]) . "',";
	}
	$vals = substr($vals, 0, -1);
	$query = "UPDATE " . $table_name . " SET " . $vals . " WHERE ID=" . $id . ";";
	return do_query($query);
}

function get_all_by_date($table_name) {
	global $wpdb;
	return $wpdb->get_results("SELECT * FROM " . $table_name . " ORDER BY date ASC;");
}

function get_recent_items($table_name, $num_items) {
	global $wpdb;
	$query = "SELECT * FROM " . $table_name . " WHERE date >= DATE_FORMAT(NOW(),'%Y-%m-%d') ORDER BY date ASC LIMIT " . $num_items . ";";
	return $wpdb->get_results($query);
}

function get_item_by_id($table_name, $id) {
	global $wpdb;
	$item = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE id='" . $id . "';");
	return $item[0];
}

function delete_table_item($table_name, $post_data) {
	$id = $post_data['id'];
	$query = "DELETE FROM " . $table_name . " WHERE id='" . $id . "';";
	return do_query($query);
}

function do_query($query) {
	global $wpdb;
	return $wpdb->query($query);
}

function format_date($date, $old, $new) {
	$pieces = explode($old,$date);
	if($new == '-') {
		$new_date = $pieces[2] . $new . $pieces[0] . $new . $pieces[1];
	} else {
		$new_date = $pieces[1] . $new . $pieces[2] . $new . $pieces[0];
	}
	return $new_date;
}

?>