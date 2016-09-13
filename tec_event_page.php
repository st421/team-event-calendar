<?php wp_enqueue_script('jquery-ui-datepicker'); ?>
<?php wp_enqueue_style('jquery-ui'); ?>
<?php wp_enqueue_style('bootstrap'); ?>
<?php $nonce = wp_create_nonce("tec_nonce_save"); ?>
<script>  
jQuery(document).ready(function(){
	jQuery('#date').datepicker();  
	jQuery('#submit_event_form').click(function(){ 
		var data = {
			'action':'tec_save_event', 
			'id':jQuery(this).attr('name'),
			'title':jQuery('#title').val(), 
			'date':jQuery('#date').val(), 
			'time':jQuery('#time').val(), 
			'location':jQuery('#location').val(), 
			'description':jQuery('#description').val(),
			'security':'<?php echo $nonce; ?>'
		};
		jQuery.post(ajaxurl, data, function(response) {
			jQuery("#event_form").fadeOut("fast");
			jQuery("#event_submitted").html(response);
			jQuery("#event_submitted").fadeIn("fast");
		});
		return false;
	});
});
</script>
<?php 
$edit = false;
$id = $_GET['id'];
if(!empty($id)) {
  $edit = true;
}
?>
<?php if($edit) { ?>
<h1>Edit event</h1>
<?php echo tec_edit_event_form($id); ?>
<?php } else { ?>
<h1>Add new event</h1>
<?php echo tec_add_event_form(); } ?>
<div id='event_submitted'></div>