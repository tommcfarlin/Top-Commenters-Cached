<div>
	<fieldset>
		<legend>
			<?php _e('Widget Options', self::locale); ?>
		</legend>
		<label for="<?php echo $this->get_field_id('widget_title'); ?>" class="block">
			<?php _e('Title:', self::locale); ?>
		</label>
		<input type="text" name="<?php echo $this->get_field_name('widget_title'); ?>" id="<?php echo $this->get_field_id('widget_title'); ?>" value="<?php echo $instance['widget_title']; ?>" class="" />		
	</fieldset>
</div>