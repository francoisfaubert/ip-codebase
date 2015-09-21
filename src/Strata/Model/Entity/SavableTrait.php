<?php
namespace IP\Code\Model\Entity;

use Exception;

trait SavableTrait {

    protected $USERS_KEY = "user_entries";
    protected $DATA_KEY = "user_data";
    protected $SUBMISSIONS = "submissions";
    protected $DATA_DUMP_METAKEY = "savable_dump";

    private $savableDump = null;

    protected $savableConfiguration = array(
        "unique_entries" => false
    );

    public function configureSavable($config = array())
    {
        $this->savableConfiguration = $config + $this->savableConfiguration;
    }

    public function getFreshResults()
    {
        $data = get_post_meta($this->getDatasourcePostId(), $this->DATA_DUMP_METAKEY, true);

        if ($data == '') {
            $data = $this->getDefaultSavableResultset();
        }

        $maybeUnserialized = maybe_unserialize($data);

        // When a post is saved from the backend, the content gets cast as a regular string
        // and no longer the WP serialized array.
        if (is_string($maybeUnserialized)) {
            return json_decode($maybeUnserialized, true);
        }

        return $maybeUnserialized;
    }


    public function save(array $data)
    {
        $this->saveUserPostData($data);
        $this->saveFieldPostData($data);

        return $this->updateResults();
    }

    private function getDump()
    {
        if (is_null($this->savableDump)) {
            $this->savableDump = $this->getFreshResults();
        }

        return $this->savableDump;
    }

    private function setDump($dump)
    {
        return $this->savableDump = $dump;
    }

    public function userHasPacticipated($wordpressUser)
    {
        $dump = $this->getDump();

        $userIds = array_keys($dump[$this->USERS_KEY]);
        return in_array($wordpressUser->ID, $userIds);
    }

    public function getSubmissions()
    {
        $dump = $this->getDump();
        return $dump[$this->SUBMISSIONS];
    }

    public function getParticipants()
    {
        $dump = $this->getDump();
        return $dump[$this->USERS_KEY];
    }

    public function setSubmissions($value)
    {
        $dump = $this->getDump();
        $dump[$this->SUBMISSIONS] = $value;
        $this->setDump($dump);
    }

    public function setParticipants($value)
    {
        $dump = $this->getDump();
        $dump[$this->USERS_KEY] = $value;
        $this->setDump($dump);
    }

    public function getParticipantCount()
    {
        return count($this->getParticipants());
    }

    private function getDefaultSavableResultset()
    {
        return array(
            $this->USERS_KEY => array(),
            $this->SUBMISSIONS => array()
        );
    }

    protected function updateResults()
    {
        $dump = $this->getDump();
        return update_post_meta($this->getDatasourcePostId(), $this->DATA_DUMP_METAKEY, serialize($dump));
    }

    protected function getDatasourcePostId()
    {
        return $this->ID;
    }

    /**
     * Save the current user ID if it was sent as POST data. Otherwise
     * generate a unique or serial id for the user depending on the settings.
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    protected function saveUserPostData($data)
    {

        if (array_key_exists("userentity", $data)) {
            $userID = (int)$data["userentity"]["ID"];

            $participants = $this->getParticipants();
            $participants[$userID] = $data["userentity"]["display_name"];

            $this->setParticipants($participants);
        }
    }

    // Loop through posted values and add 1 to the current values if
    // they pass validation.
    protected function saveFieldPostData($data)
    {
        $workingResults = $this->getSubmissions();

        $userID = $this->generateUserId($data);
        if ($this->savableConfiguration['unique_entries']) {
            if (array_key_exists($userID, $workingResults)) {
                throw new Exception("User has already participated.");
            }
        } else {
            $userID = $userID . "@" . time();
        }

        $workingResults[$userID] = array();
        $ourData = $data[$this->getInputName()];

        foreach ($this->getAttributes() as $questionIdx => $choice) {
            $workingResults[$userID][$questionIdx] = $ourData[$questionIdx];
        }

        $this->setSubmissions($workingResults);
    }

    protected function generateUserId($data)
    {
        return (array_key_exists("userentity", $data)) ? (int)$data["userentity"]["ID"] : $_SERVER['REMOTE_ADDR'];
    }
}
