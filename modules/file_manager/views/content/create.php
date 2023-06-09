<div class="admin-box">
	<h3>
            	<?php echo lang('file_manager_upload_files'); ?>
	</h3>

	<?php echo form_open_multipart('admin/content/file_manager/do_upload', 'class="form-horizontal"');?>
		<fieldset>
			<div class="control-group <?php echo form_error('file_manager_userfile') ? 'error' : ''; ?>">
				<?php echo form_label(lang('file_manager_selected_file'). lang('bf_form_label_required'), 'userfile', array('class' => "control-label") ); ?>

				<div class='controls'>
					<input id="userfile" name="userfile[]" type="file" multiple="multiple"  />
				</div>
			</div>

			<div class="form-actions">
				<br/>
				<input type="submit" class="btn btn-primary" value="<?php echo lang('file_manager_upload_file');?>" />
				<?php echo lang('file_manager_or'); ?> <?php echo anchor(SITE_AREA .'/content/file_manager', lang('file_manager_cancel'), 'class="btn btn-warning"'); ?>
			</div>
		</fieldset>
	</form>
</div>
