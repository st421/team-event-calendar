<?php get_header(); ?>
<div class="container">
<div id="page" class="row">
		<?php 
		global $events_table;
		$id = get_query_var('event_id');
		if(isset($id)) {
			$event = get_item_by_id($events_table, $id);
			echo tec_display_event($event); 
		} ?>
</div>
</div>
<?php get_footer(); ?>