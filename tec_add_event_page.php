<?php wp_enqueue_script('jquery-ui-datepicker'); ?>
<?php wp_enqueue_style('jquery-ui'); ?>
<?php wp_enqueue_style('bootstrap'); ?>
<?php $nonce = wp_create_nonce("tec_nonce_save"); ?>
<script>  
jQuery(document).ready(function(){
	jQuery('#date').datepicker();  
});
jQuery(document).ready(function(){
  jQuery('#submit_event_form').click(function(){ 
		var data = {
			'action':'tec_save_event', 
			'title': jQuery('#title').val(), 
			'date': jQuery('#date').val(), 
			'time': jQuery('#time').val(), 
			'location': jQuery('#location').val(), 
			'description': jQuery('#description').val(),
			'security': '<?php echo $nonce; ?>'
		};
		jQuery.post(ajaxurl, data, function(response) {
			jQuery("#add_event_form").fadeOut("fast");
			jQuery("#event_submitted").html(response);
			jQuery("#event_submitted").fadeIn("fast");
		});
		return false;
	});
});
</script> 
<h1>Add a new event</h1>
<?php echo tec_add_event_form(); ?>
<div id='event_submitted'></div>