<?php
namespace IP\Code\Strata\Implementation\Savable\Model;

use IP\Code\Strata\Implementation\Savable\Model\Entity\SavableSubmissionEntity;
use IP\Code\Strata\Implementation\Savable\Model\Entity\SavableAnswerEntity;

class SavableQuery {

    const DB_VERSION = "0.1.2";

    protected $logger;
    protected $inputHash;


    protected $associatedEntity;

    function __construct($associatedEntity = null)
    {
        $this->logger = new Logger();
        $this->checkForTableUpdates();

        if (!is_null($associatedEntity)) {
            $this->associatedEntity = $associatedEntity;

            // This will ensure results are always valid for the current
            // state
            $this->inputHash = md5(json_encode(array_keys($associatedEntity->getAttributes())));
        }
    }

    private function checkForTableUpdates()
    {
        if ($this->getCurrentVersion() !== self::DB_VERSION) {
            $this->createTable();
        }
    }

    private function getCurrentVersion()
    {
        return get_option('ip_savable_db_version');
    }

    public function createTable()
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE {$wpdb->prefix}ip_savable (
            ID mediumint(9) NOT NULL AUTO_INCREMENT,
            post_ID mediumint(9) NOT NULL,
            post_type tinytext NOT NULL,
            input_hash tinytext NOT NULL,
            user_ip tinytext,
            user_ID mediumint(9),
            created DATETIME,
            UNIQUE KEY ID (ID)
        ) $charset;";
        dbDelta($sql);

       $sql = "CREATE TABLE {$wpdb->prefix}ip_savable_field (
            ID mediumint(9) NOT NULL AUTO_INCREMENT,
            savable_ID mediumint(9) NOT NULL,
            field_key tinytext NOT NULL,
            field_value text NOT NULL,
            UNIQUE KEY ID (ID)
        ) $charset;";
        dbDelta($sql);

        update_option('ip_savable_db_version', self::DB_VERSION);
        $this->logger->log("Created or updated the Savable table.");
    }

    public function getCount()
    {
        $this->logger->logQueryStart();

        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT count(*)
            FROM {$wpdb->prefix}ip_savable
            WHERE post_ID = %d
            AND post_type = %s
            AND input_hash = %s
        ", $this->associatedEntity->ID, $this->associatedEntity->post_type, $this->inputHash));

        $this->logger->logQueryCompletion($wpdb->last_query);

        return (int)$count;
    }

    public function getIgnoredCount()
    {
        $this->logger->logQueryStart();

        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT count(*)
            FROM {$wpdb->prefix}ip_savable
            WHERE post_ID = %d
            AND post_type = %s
            AND input_hash != %s
        ", $this->associatedEntity->ID, $this->associatedEntity->post_type, $this->inputHash));

        $this->logger->logQueryCompletion($wpdb->last_query);

        return (int)$count;
    }

    public function userHasParticipated($userId)
    {
        $this->logger->logQueryStart();

        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT count(*)
            FROM {$wpdb->prefix}ip_savable
            WHERE post_ID = %d
            AND post_type = %s
            AND input_hash = %s
            AND user_ID = %d
        ", $this->associatedEntity->ID, $this->associatedEntity->post_type, $this->inputHash, $userId));

        $this->logger->logQueryCompletion($wpdb->last_query);

        return (int)$count > 0;
    }

    public function getParticipantIds()
    {
         $this->logger->logQueryStart();

        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT user_ID
            FROM {$wpdb->prefix}ip_savable
            WHERE post_ID = %d
            AND post_type = %s
            AND input_hash = %s
        ", $this->associatedEntity->ID, $this->associatedEntity->post_type, $this->inputHash));

        $this->logger->logQueryCompletion($wpdb->last_query);

        return $results;
    }

    public function insert($userId, $data)
    {
        global $wpdb;
        // Save the submission
        $this->logger->logQueryStart();
        $wpdb->insert("{$wpdb->prefix}ip_savable", array(
            "post_ID" => $this->associatedEntity->ID,
            "post_type" => $this->associatedEntity->post_type,
            "input_hash" => $this->getQuestionsHash(),
            "user_ip" => $_SERVER['REMOTE_ADDR'],
            "user_ID" => $userId,
            "created" => current_time('mysql')
        ));
        $this->logger->logQueryCompletion($wpdb->last_query);
        $savableId = (int)$wpdb->insert_id;

        if ($savableId > 0) {
            // Save the associated values
            foreach ($data as $key => $submission) {
                $this->logger->logQueryStart();
                $wpdb->insert("{$wpdb->prefix}ip_savable_field", array(
                    "savable_ID" => $savableId,
                    "field_key" => $key,
                    "field_value" => $submission
                ));
                $this->logger->logQueryCompletion($wpdb->last_query);
            }

            return $savableId;
        }

        throw new Exception(__("We could not save the data.", "ip"));
    }

    public function delete($submissionId)
    {
        global $wpdb;
        $cleanId = (int)$submissionId;
        if ($submissionId > 0) {
            $status = true;

            $this->logger->logQueryStart();
            $status = $status && $wpdb->delete("{$wpdb->prefix}ip_savable", array('ID' => (int)$submissionId), array('%d')) &&
            $this->logger->logQueryCompletion($wpdb->last_query);

            $this->logger->logQueryStart();
            $status = $status && $wpdb->delete("{$wpdb->prefix}ip_savable_field", array('savable_ID' => (int)$submissionId), array('%d'));
            $this->logger->logQueryCompletion($wpdb->last_query);

            return $status;
        }

        return false;
    }

    public function getQuestionsHash()
    {
        return $this->inputHash;
    }

    public function getPage($start = 0, $length = 20, $postType)
    {
        $this->logger->logQueryStart();

        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$wpdb->prefix}ip_savable
            WHERE post_type = %s
            ORDER BY {$wpdb->prefix}ip_savable.created DESC
            LIMIT %d, %d
        ", $postType, $start, $length));

        $this->logger->logQueryCompletion($wpdb->last_query);

        return $this->resultsToSubmissionEntities($results);
    }

    public function getSubmissions($start = 0, $length = 20)
    {
        $this->logger->logQueryStart();

        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$wpdb->prefix}ip_savable
            WHERE post_ID = %d
            AND post_type = %s
            AND input_hash = %s
            LIMIT %d, %d
        ", $this->associatedEntity->ID, $this->associatedEntity->post_type, $this->inputHash, $start, $length));

        $this->logger->logQueryCompletion($wpdb->last_query);

        return $this->resultsToSubmissionEntities($results);
    }

    public function getSubmission($submissionId)
    {
        $this->logger->logQueryStart();

        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("
            SELECT *
            FROM {$wpdb->prefix}ip_savable
            WHERE post_ID = %d
            AND post_type = %s
            AND input_hash = %s
            AND ID = %d
        ", $this->associatedEntity->ID, $this->associatedEntity->post_type, $this->inputHash, $submissionId));

        $this->logger->logQueryCompletion($wpdb->last_query);

        return $this->submissionToEntity($row);
    }

    public function getSubmissionAnswers($entryId)
    {
        $this->logger->logQueryStart();

        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$wpdb->prefix}ip_savable_field
            LEFT JOIN {$wpdb->prefix}ip_savable
                ON {$wpdb->prefix}ip_savable.ID = {$wpdb->prefix}ip_savable_field.savable_ID
            WHERE post_ID = %d
            AND post_type = %s
            AND input_hash = %s
            AND {$wpdb->prefix}ip_savable.ID = %d
        ", $this->associatedEntity->ID, $this->associatedEntity->post_type, $this->inputHash, $entryId));

        $this->logger->logQueryCompletion($wpdb->last_query);

        return $results;
    }

    public function getEntries()
    {
        $this->logger->logQueryStart();

        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$wpdb->prefix}ip_savable_field
            LEFT JOIN {$wpdb->prefix}ip_savable
                ON {$wpdb->prefix}ip_savable.ID = {$wpdb->prefix}ip_savable_field.savable_ID
            WHERE post_ID = %d
            AND post_type = %s
            AND input_hash = %s
        ", $this->associatedEntity->ID, $this->associatedEntity->post_type, $this->inputHash));

        $this->logger->logQueryCompletion($wpdb->last_query);

        return $this->resultsToAnswerEntities($results);
    }

    private function resultsToSubmissionEntities($results)
    {
        $entities = array();
        foreach ($results as $row) {
            $entities[] = $this->submissionToEntity($row);
        }

        return $entities;
    }

    private function submissionToEntity($row)
    {
        $submission = new SavableSubmissionEntity($row);

        if (!is_null($this->associatedEntity)) {
            $submission->bindToEntity($this->associatedEntity);
        }

        return $submission;
    }

    private function resultsToAnswerEntities($results)
    {
        $entities = array();

        foreach ($results as $row) {
            $entities[] = new SavableAnswerEntity($row);
        }

        return $entities;
    }

}
