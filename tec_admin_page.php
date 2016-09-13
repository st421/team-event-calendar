<?php wp_enqueue_style('bootstrap'); ?>
<?php wp_enqueue_style('tec-style'); ?>
<?php $nonce = wp_create_nonce("tec_nonce_del"); ?>
<script>
jQuery(document).ready(function(){
	jQuery("td.delete").click(function() {
		var $this = jQuery(this);
		var data = {
			'action':'tec_delete_event', 
			'id':$this.attr('id'),
			'security':'<?php echo $nonce; ?>'
		};
		jQuery.post(ajaxurl, data, function(response) {
			$this.parent().fadeOut('slow');
		});
		return false;
	});
});
</script>
<h1>Team Events Calendar Plugin</h1>
<h3>Events</h3>
<?php tec_display_admin_calendar(); ?>