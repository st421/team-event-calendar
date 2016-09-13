<?php  
/*
    Plugin Name: Team Event Calendar
    Plugin URI: http://susanltyler.com/team-event-calendar-plugin
    Description: An event calendar for easy display of upcoming events.
    Author: S. Tyler 
    Version: 1.0 
    Author URI: http://susanltyler.com
*/

include('wp_sql_helper.php');

global $events_table, $event_params, $wpdb;
$events_table = $wpdb->prefix . "tec_events";
$event_params = array(
  new TableField("title","VARCHAR(255)"),
  new TableField("date","DATE"),
  new TableField("time","VARCHAR(20)"),
  new TableField("location","VARCHAR(255)"),
  new TableField("description","VARCHAR(500)")
);

register_activation_hook(__FILE__,'tec_install');
register_deactivation_hook(__FILE__,'tec_uninstall'); 

add_shortcode('calendar','tec_user_calendar');
add_shortcode('upcoming_events','tec_display_upcoming_events');

add_action('admin_menu','tec_admin_setup');  
add_action('wp_ajax_tec_save_event','tec_save_event');
add_action('wp_ajax_tec_delete_event','tec_delete_event');

add_filter('query_vars', 'tec_add_event_vars');

/*
 * Calls functions necessary for plugin install.
 * -Creates table in database for events.
 */
function tec_install() {
	global $events_table, $event_params;
	create_table($events_table,$event_params);
}

/*
 * Calls functions necessary for plugin uninstall. 
 * -Drops events table in database.
 */
function tec_uninstall() {
	global $events_table;
	drop_table($events_table);
}

/*
 * Registers style sheets and menu pages.
 */
function tec_admin_setup() {  
	wp_register_style('bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
	wp_register_style('jquery-ui', '//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css');
	wp_register_style('tec-style', plugins_url('css/team_event_calendar_style.css', __FILE__));
	add_menu_page('Team Events', 'Team Events', 'administrator', 'team_events_calendar', 'tec_admin');  
	add_submenu_page('team_events_calendar', 'Add New Event', 'Add New Event', 'administrator', 'tec-event', 'tec_event_page');
	add_submenu_page(NULL, 'Edit Event', 'Edit Event', 'administrator', 'tec-event', 'tec_event_page');
} 

function tec_admin() {  
	include('tec_admin_page.php');  
}

function tec_event_page() {
	include('tec_event_page.php');
}

function tec_user_calendar($atts) {
	echo tec_display_calendar();
}

function tec_admin_calendar() {
	echo tec_display_calendar(true);
}

function tec_edit_event_form($id) {
	global $events_table;
	return tec_event_form(true, get_item_by_id($events_table, $id));
}

function tec_add_event_form() {
	return tec_event_form();
}

function tec_save_event() {
	check_ajax_referer('tec_nonce_save','security');
	global $events_table, $event_params;
	if(save_table_item($events_table,$event_params,$_POST)) {
		echo "Event successfully saved";
	} else {
		echo "ERROR; event not saved. Did you provide all parameters?";
	}
	die();
}

function tec_delete_event() {
	check_ajax_referer('tec_nonce_del','security');
	global $events_table;
	delete_table_item($events_table, $_POST);
	die();
}

function tec_event_form($edit=false, $event=NULL) {
	global $event_params;
	$form = '<form id="event_form">';
	foreach($event_params as $param) {
		$form .= '<div class="form-group">';
		$form .= '<label for="' . $param->name . '">' . $param->name . '</label>';
		if($param->name == 'description') {
			$form .= '<textarea type="text" class="form-control" id="' . $param->name . '" COLS=100 ROWS=5>';
			if($edit) {
				$form .= get_object_vars($event)[$param->name];
			}
			$form .= '</textarea>';
		} else {
			$form .= '<input type="text" class="form-control" id="' . $param->name . '"';
			if($edit) {
				$form .= ' value="' . get_object_vars($event)[$param->name] . '"';
			}
			$form .= '>';	
		}
		$form .= '</div>';
	}
	$form .= '<button type="submit" name="action" id="submit_event_form" class="btn">Submit</button></form>';
	return $form;
}

function tec_display_calendar($admin=false) {
	global $events_table, $event_params;
	$calendar_events = get_all_by_date($events_table);
	$table = '<table id="tec_calendar" class="table table-responsive table-hover"><thead>';
	foreach($event_params as $param) {
		$table .= '<th>' . $param->name . '</th>';
	}
	if($admin) {
		$table .= '<th>Delete?</th>';
	}
	$table .= '</thead><tbody>';
	foreach($calendar_events as $event) {
		$table .= '<tr>';
		foreach($event_params as $param) {
			$table .= '<td>';
			if($param->name == 'date') {
				$table .= tec_format_date($event->date,'-','/');
			} else if($param->name == 'title' && $admin) {
				$path = 'admin.php?page=tec-event&id=' . $event->id;
				$url = admin_url($path);
				$table .= '<a href="' . $url . '">' . $event->title . '</a>';
			} else {
				$table .= get_object_vars($event)[$param->name];
			}
			$table .= '</td>';
		}
		if($admin) {
			$table .= '<td id="' . $event->id . '" class="delete"></td>';
		}
		$table .= '</tr>';
	}
	$table .= '</tbody></table>';
	return $table;
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
?>