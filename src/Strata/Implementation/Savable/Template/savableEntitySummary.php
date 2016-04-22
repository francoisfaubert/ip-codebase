<div class="wrap">

    <h1><?php echo $entityLabel; ?></h1>

    <?php if (get_post_type($datasourceEntity->ID) === $datasourceEntity->post_type) : ?>
        <h3><a href="<?php echo admin_url('post.php?post='.$datasourceEntity->ID.'&action=edit'); ?>"><?php echo $datasourceEntity->post_title; ?></a></h3>
    <?php endif; ?>

    <?php
        $attributes = $entity->getSavableDisplayedAttributesSummaryView();
        $resultCount = $entity->getSavableEntriesCount();
        $ignoredEntityCount = $entity->getSavableEntriesIgnoredCount();
        $labels = $entity->extractSavableAttributeLabels($attributes);

        $itemsPerPage = 20;

        $currentPage = array_key_exists('savablePage', $_GET) ? (int)$_GET['savablePage'] : 1;
        if ($currentPage === 0) {
            $currentPage = 1;
        }

        $previousPage = $currentPage - 1;
        if ($previousPage < 1) {
            $previousPage = 1;
        }

        $lastPage =  ceil($resultCount / $itemsPerPage);
        $nextPage = $currentPage + 1;
        if ($nextPage > $lastPage) {
            $nextPage = $lastPage;
        }

    ?>

    <?php if ($resultCount > 0) : ?>
        <?php $nbPages = ceil($resultCount / $itemsPerPage); ?>
        <div class="tablenav top">
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo $resultCount; ?> items</span>
                <span class="pagination-links">
                    <a class="first-page" href="<?php echo $editUrlBase; ?>&amp;postID=<?php echo $datasourceEntity->ID; ?>">
                        <span class="screen-reader-text">First page</span>
                        <span aria-hidden="true">«</span>
                    </a>
                    <a class="previous-page" href="<?php echo $editUrlBase; ?>&amp;postID=<?php echo $datasourceEntity->ID; ?>&amp;savablePage=<?php echo $previousPage; ?>">
                        <span class="screen-reader-text">Previous page</span>
                        <span aria-hidden="true">‹</span>
                    </a>
                    <span class="paging-input">
                        <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                        <?php echo $currentPage; ?> of <span class="total-pages"><?php echo $nbPages; ?></span>
                    </span>
                    <a class="next-page" href="<?php echo $editUrlBase; ?>&amp;postID=<?php echo $datasourceEntity->ID; ?>&amp;savablePage=<?php echo $nextPage; ?>">
                        <span class="screen-reader-text">Next page</span>
                        <span aria-hidden="true">›</span>
                    </a>
                    <a class="first-page" href="<?php echo $editUrlBase; ?>&amp;postID=<?php echo $datasourceEntity->ID; ?>&amp;savablePage=<?php echo $lastPage; ?>">
                        <span class="screen-reader-text">Last page</span>
                        <span aria-hidden="true">»</span>
                    </a>
                </span>
            </div>
        </div>
    <?php endif; ?>


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

        <?php
            $start = ($currentPage-1) * $itemsPerPage;
            $end = $start + $itemsPerPage;
        ?>

        <?php foreach ($entity->getSavableSubmissions($start, $end  ) as $submission) : ?>
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
