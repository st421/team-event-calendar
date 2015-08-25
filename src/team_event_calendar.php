<?php  
/*
    Plugin Name: Team Event Calendar
    Plugin URI: http://susanltyler.com/team-event-calendar-plugin
    Description: An event calendar for easy display of upcoming events.
    Author: S. Tyler 
    Version: 1.0 
    Author URI: susanltyler.com
*/     

$events_table = $wpdb->prefix . "tec_events";
register_activation_hook(__FILE__,'tec_install');
register_deactivation_hook(__FILE__, 'tec_uninstall'); 
add_shortcode('calendar','tec_display_calendar');
add_shortcode('upcoming_events','tec_display_upcoming_events');
add_action('admin_menu','tec_admin_actions');  
add_action('wp_ajax_tec_add_event','tec_save_event');
add_action('wp_ajax_admin_remove_event','tec_admin_delete_event');
add_filter('query_vars', 'tec_add_event_vars');

function tec_admin_actions() {  
	wp_register_style('teamEventCalendarStyle', plugins_url('/css/team_event_calendar_style.css', __FILE__));
	wp_register_style('datePickerCSS', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css');
	wp_register_script('datePickerJS', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js');
	add_menu_page('Team Events', 'Team Events', 'administrator', 'team_events_calendar', 'tec_admin');  
	add_submenu_page('team_events_calendar', 'Add New Event', 'Add New Event', 'administrator', 'tec_add_new_event', 'tec_add_new_event');
	add_submenu_page(NULL, 'Edit Event', 'Edit Event', 'administrator', 'tec_edit_old_event', 'tec_edit_old_event');
} 

function tec_admin() {  
        include('tec_admin_page.php');  
}

function tec_add_new_event() {
	include('tec_add_new_event_page.php');
}

function tec_edit_old_event() {
	include('tec_edit_event_page.php');
}

function tec_add_event_vars($vars) {
	$vars[0] = 'title';
	$vars[1] = 'date';
	return $vars;
}

function tec_event_template() {
    if(is_page('event')) {
        $page_template = dirname( __FILE__ ) . '/event.php';
    }
    return $page_template;
}

function tec_install() {
	global $wpdb;
	$events_table = $wpdb->prefix . "tec_events";
	if($wpdb->get_var("SHOW TABLES LIKE '$events_table'") != $events_table) {
		$sql = "CREATE TABLE " . $events_table . " (
		        id int NOT NULL AUTO_INCREMENT,
			title VARCHAR(255),
			date DATE,
			time VARCHAR(20),
			location VARCHAR(255),
			brief VARCHAR(300),
			description VARCHAR(500),
			PRIMARY KEY (id)		
		);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

function tec_save_event() {
	check_ajax_referer('tec_nonce_1');
	$event_id = $_POST['id'];
	$event_title = $_POST['title'];
	$date = $_POST['date'];
	$event_time = $_POST['time'];
	$event_location = $_POST['location'];
	$event_brief = $_POST['brief'];
	$event_description = $_POST['description'];
	$event_date = tec_format_date($date, '/', '-');
	if($event_id) {
		if(tec_update_event_data($event_id,$event_title,$event_date,$event_time,$event_location,$event_brief,$event_description)) {
			echo "Event successfully updated!";
		} else {
			echo "Oops! Something went wrong!";
		}
	} else {
		if(tec_save_event_data($event_title,$event_date,$event_time,$event_location,$event_brief,$event_description)) {
			echo "Event successfully added!";
		} else {
			echo "Oops! Something went wrong!";
		}
	}	
	die();
} 

function tec_admin_delete_event() {
	$event_id = $_POST['ID_to_delete'];
	tec_delete_event_data($event_id);
}

function tec_save_event_data($title,$date,$time,$location,$brief,$description) {
	global $wpdb, $events_table;
	$query = "INSERT INTO " . $events_table . " (title,date,time,location,brief,description) VALUES ('" . $title . "','" . $date . "','" . $time . "','" . $location . "','" . $brief. "','" . $description . "');";
	if($wpdb->query($query)) return 1; else return 0;
}

function tec_update_event_data($id,$title,$date,$time,$location,$brief,$description) {
	global $wpdb, $events_table;
	$query = "UPDATE " . $events_table . " SET title='" . $title . "', date='" . $date . "', time='" . $time . "', location='" . $location . "', brief ='" . $brief . "', description='" . $description . "' WHERE id=" . $id . ";";
	if($wpdb->query($query)) return 1; else return 0;
}

function tec_delete_event_data($id) {
	global $wpdb, $events_table;
	$query = "DELETE FROM " . $events_table . " WHERE id='" . $id . "';";
	$wpdb->query($query);
}

function tec_display_calendar($atts) {
	global $wpdb, $events_table;
	$calendar_events = $wpdb->get_results("SELECT * FROM " . $events_table . " ORDER BY date ASC;");
	echo '<table><thead><th>Title</th><th>Date</th><th>Time</th><th>Location</th><th>Brief</th><th>Description</th></thead><tbody>';
	foreach($calendar_events as $event) {
		echo '<tr><td>' . $event->title . '</td><td>' . $event->date . '</td><td>' . $event->time . '</td><td>' . $event->location . '</td><td>' . $event->brief . '</td><td>' . $event->description . '</td></tr>';
	}
	echo '</tbody></table>';
}

function tec_display_upcoming_events($atts) {
	global $wpdb, $events_table;
	$query = "SELECT * FROM " . $events_table . " WHERE date >= DATE_FORMAT(NOW(),'%Y-%m-%d') ORDER BY date ASC LIMIT 3;";
	$upcoming_events = $wpdb->get_results($query);
	$event_page = get_page_by_title('Event');
	if($upcoming_events != '') {
		echo '<ul>';
		foreach($upcoming_events as $event) {
			echo '<li><a href="/?page_id=' . $event_page->ID . '&title=' . $event->title . '&date=' . $event->date . '"><h3>' . $event->title . '</h3><h3 class="date">' . tec_format_date($event->date, '-', '.') . '</h3></a>' . $event->brief . '</li></br>';
		}
		echo '</ul>';
	}		
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

/* 
Upon uninstalling the plugin, remove the tables created. 
*/
function tec_uninstall() {
	global $wpdb, $events_table;
	$wpdb->query("DROP TABLE IF EXISTS " . $events_table . ";");
}
?>