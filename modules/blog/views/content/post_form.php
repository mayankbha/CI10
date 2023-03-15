<div class="admin-box">
    <h3>New Post</h3>

    <?php echo form_open(current_url(), 'class="form-horizontal"'); ?>

        <div class="control-group <?php if (form_error('title')) echo 'error'; ?>">
            <label for="title">Title</label>
            <div class="controls">
                <input type="text" name="title" class="input-xxlarge" value="<?php echo isset($post) ? $post->title : set_value('title'); ?>" />
                <?php if (form_error('title')) echo '<span class="help-inline">'. form_error('title') .'</span>'; ?>
            </div>
        </div>

        <div class="control-group <?php if (form_error('slug')) echo 'error'; ?>">
            <label for="slug">Slug</label>
            <div class="controls">
                <div class="input-prepend">
                    <span class="add-on"><?php echo site_url() .'blog/' ?></span>
                    <input type="text" name="slug" class="input-xlarge" value="<?php echo isset($post) ? $post->slug : set_value('slug'); ?>" />
                </div>
                <?php if (form_error('slug')) echo '<span class="help-inline">'. form_error('slug') .'</span>'; ?>
                <p class="help-block">The unique URL that this post can be viewed at.</p>
            </div>
        </div>

        <div class="control-group <?php if (form_error('body')) echo 'error'; ?>">
            <label for="body">Content</label>
            <div class="controls">
                <?php if (form_error('body')) echo '<span class="help-inline">'. form_error('body') .'</span>'; ?>
                <textarea name="body" class="input-xxlarge" rows="15"><?php echo isset($post) ? $post->body : set_value('body') ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" name="submit" class="btn btn-primary" value="Save Post" />
            or <a href="<?php echo site_url(SITE_AREA .'/content/blog') ?>">Cancel</a>
        </div>

    <?php echo form_close(); ?>
</div>
