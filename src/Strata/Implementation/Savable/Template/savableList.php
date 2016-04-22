<div class="wrap">
    <h1><?php echo $entityLabel; ?></h1>

    <?php if (isset($deletedRecord)) : ?>
        <?php if ($deletedRecord) : ?>
            <p><?php _e("Submission deleted successfully", "ip"); ?></p>
        <?php else : ?>
            <p><?php _e("We could not delete this submission", "ip"); ?></p>
        <?php endif; ?>
    <?php endif; ?>


    <h2><?php _e("Recent submissions", "ip"); ?></h2>

    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e("Submission", 'ip'); ?></th>
                <th><?php _e('User', 'ip'); ?></th>
                <th><?php _e('Created', 'ip'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($recentEntities)) : ?>
            <?php foreach ($recentEntities as $idx => $row) : ?>
            <tr class="<?php echo (($idx%2) === 0) ? 'even' : 'odd'; ?>">
                <td>
                    <a href="<?php echo $editUrlBase . "&postID=" . $row->post_ID; ?>">
                        <?php if (get_post_type($row->post_ID) === $row->post_type) : ?>
                            <?php echo get_the_title($row->post_ID); ?>
                        <?php else : ?>
                            <?php echo $entityLabel; ?>
                        <?php endif; ?>
                    </a>
                </td>
                <td>
                     <?php if ((int)$row->user_ID > 0) : ?>
                        <?php $user = get_userdata($row->user_ID); ?>
                        <?php if ($user) : ?>
                            <a href="<?php echo get_edit_user_link($row->user_ID); ?>"><?php echo $user->display_name; ?></a>
                        <?php else : ?>
                            [DELETED]
                        <?php endif; ?>
                     <?php else : ?>
                        <?php echo sprintf(__("Unregistered (%s)", "ip"), $row->user_ip); ?>
                     <?php endif; ?>
                </td>
                <td>
                    <?php echo $row->created; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr><td colspan="3"><?php _e("There have been no entries.", "ip"); ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
