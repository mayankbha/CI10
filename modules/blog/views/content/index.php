    <div class="admin-box">
        <h3>Blog Posts</h3>

        <?php echo form_open(); ?>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="column-check"><input class="check-all" type="checkbox" /></th>
                        <th>Title</th>
                        <th style="width: 10em">Date</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="3">
                            With selected:
                            <input type="submit" name="submit" class="btn" value="Delete">
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                <?php if (isset($posts) && is_array($posts)) :?>
                    <?php foreach ($posts as $post) : ?>
                    <tr>
                        <td><input type="checkbox" name="checked[]" value="<?php echo $post->post_id ?>" /></td>
                        <td>
                            <a href="<?php echo site_url(SITE_AREA .'/content/blog/edit_post/'. $post->post_id) ?>">
                                <?php e($post->title); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo date('M j, Y g:ia'); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">
                            <br/>
                            <div class="alert alert-warning">
                                No Posts found.
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

        <?php echo form_close(); ?>
    </div>
