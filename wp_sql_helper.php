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
	global $wpdb;
	$wpdb->query("DROP TABLE IF EXISTS " . $table_name . ";");
}

/*
 * Creates a SQL statement for the table columns.
 */
function table_sql($table_params) {
	$sql = "id int NOT NULL AUTO_INCREMENT,";
	foreach($table_params as $param) {
		$sql .= $param->name . " " . $param->sql . ",";
	}
	$sql .= "PRIMARY KEY (id)";
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
 * Adds or updates the table with the given parameters and POST data.
 */
function save_table_item($table_name, $table_params, $post_data) {
	global $wpdb;
	$wpdb->show_errors();
	$result = 0;
	$id = sanitize_text_field($post_data['id']);
	$insert = "";
	$vals = "";
	if(!empty($id)) {
		$insert .= "id,";
		$vals .= "'" . $id . "',";
	}
	foreach($table_params as $param) {
		$insert .= $param->name . ",";
		if($param->name == "date") {
	    	$post_data["date"] = tec_format_date(sanitize_text_field($post_data["date"]), '/', '-');
		}
		$vals .= "'" . sanitize_text_field($post_data[$param->name]) . "',";
	}
	$vals = substr($vals, 0, -1);
	$insert = substr($insert, 0, -1);
	$query = "INSERT INTO " . $table_name . " (" . $insert . ") VALUES (" . $vals . ") ON DUPLICATE KEY UPDATE;";
	if($wpdb->query($query)) {
		$result = 1;
	}
	return $result;
}

function delete_table_item($table_name, $post_data) {
	$id = $post_data['id'];
	$query = "DELETE FROM " . $table_name . " WHERE id='" . $id . "';";
	$wpdb->query($query);
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