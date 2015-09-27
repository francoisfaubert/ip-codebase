<div class="wrap">

    <h1><?php echo $entityLabel; ?></h1>

    <h3><a href="<?php echo admin_url('post.php?post='.$datasourceEntity->ID.'&action=edit'); ?>"><?php echo $datasourceEntity->post_title; ?></a></h3>

    <?php
        $attributes = $entity->getSavableDisplayedAttributesSummaryView();
        $resultCount = $entity->getSavableEntriesCount();
        $ignoredEntityCount = $entity->getSavableEntriesIgnoredCount();
        $labels = $entity->extractSavableAttributeLabels($attributes);
    ?>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <?php foreach ($labels as $attributeKey => $label) : ?>
                    <th>
                        <?php echo $label; ?>
                    </th>
                <?php endforeach; ?>
                <th><?php _e('User', 'ip'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
    <?php if ($resultCount > 0) : ?>
        <?php foreach ($entity->getSavableSubmissions() as $submission) : ?>
        <tr>
            <?php foreach ($entity->extractSavableSubmissionAnswers($submission, $attributes)  as $attributeKey => $answer) : ?>
                <td>
                    <?php if ($answer) : ?>
                        <?php echo $entity->parseSavableAnswer($answer, $submission); ?>
                    <?php else : ?>
                        --
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>

            <td>
                <?php if ((int)$submission->user_ID > 0) : ?>
                    <?php $user = get_userdata($submission->user_ID); ?>
                    <a href="<?php echo get_edit_user_link($submission->user_ID); ?>"><?php echo $user->display_name; ?></a>
                <?php else : ?>
                    <?php echo sprintf(__("Unregistered (%s)", "ip"), $submission->user_ip); ?>
                <?php endif; ?>
            </td>

            <td>
                <a href="<?php echo $editUrlBase; ?>&amp;postID=<?php echo $submission->post_ID; ?>&amp;viewEntry=<?php echo $submission->ID; ?>" class="button"><?php _e("View details", "ip"); ?></a>
            </td>

        </tr>
        <?php endforeach; ?>
    <?php else : ?>
            <tr><td colspan="<?php echo count($labels)+2; ?>"><?php _e("There have been no entries.", "ip"); ?></td></tr>
    <?php endif; ?>

        </tbody>
    </table>

    <p style="font-size:0.8em; color: #bbb">
        <?php echo sprintf(__("The content is based on the questions set '%s'.", 'ip'), $entity->getQuestionsHash()); ?>
        <?php if ($ignoredEntityCount > 0) : ?>
            <?php echo sprintf(__("There are %s other entries which are being ignored because they do not match this question set.", 'ip'), $ignoredEntityCount); ?>
        <?php endif; ?>
    </p>
