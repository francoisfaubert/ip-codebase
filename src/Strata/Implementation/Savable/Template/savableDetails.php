<div class="wrap">

    <h1><?php echo $entityLabel; ?></h1>

    <h3><a href="<?php echo admin_url('post.php?post='.$datasourceEntity->ID.'&action=edit'); ?>"><?php echo $datasourceEntity->post_title; ?></a></h3>

    <?php
        $attributes = $entity->getSavableDisplayedAttributesDetailedView();
        $labels = $entity->extractSavableAttributeLabels($attributes);
        $answers = $entity->extractSavableSubmissionAnswers($submission, $attributes);
    ?>
    <?php if (count($answers)) : ?>

        <ul style="display:block; text-align:right;">
            <li><a href="<?php echo $editUrlBase; ?>&amp;postID=<?php echo $submission->post_ID; ?>" class="button"><?php _e("Back to list", "ip"); ?></a></li>
            <li><a href="<?php echo $editUrlBase; ?>&amp;deleteSubmission=<?php echo $submission->ID; ?>" class="button remove" onclick="return confirm('<?php _e("Are you sure you wish to delete this entry?", "ip"); ?>');"><?php _e("Delete", "ip"); ?></a></li>
        </ul>

        <p>
            <?php _e("Submitted on", "ip"); ?> <?php echo $submission->created; ?>
            <?php _e("by", "ip"); ?>
            <?php if ((int)$submission->user_ID > 0) : ?>
                <?php $user = get_userdata($submission->user_ID); ?>
                <a href="<?php echo get_edit_user_link($submission->user_ID); ?>"><?php echo $user->display_name; ?></a>
            <?php else : ?>
                <?php echo sprintf(__("an unregistered user using the ip %s", "ip"), $submission->user_ip); ?>
            <?php endif; ?>.
        </p>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:20%;"><?php _e("Details", "ip"); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php // looping the labels ensures we print the correct input ?>
            <?php foreach ($labels  as $attributeKey => $label) : ?>
                <tr>
                    <td style="background:#ddd; border-bottom:1px solid #ccc; border-right:1px solid #ccc;"><?php echo $label; ?></td>
                    <td><?php echo $entity->parseSavableAnswer($submission->getAnswerFor($attributeKey), $submission); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php else : ?>

        <p><?php _e("We cannot find the information.", "ip"); ?></p>

    <?php endif; ?>

</div>
