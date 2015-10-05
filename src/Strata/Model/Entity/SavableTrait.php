<?php
namespace IP\Code\Strata\Model\Entity;

use IP\Code\Strata\Implementation\Savable\Model\SavableQuery;
use Exception;

trait SavableTrait {

    private $savableDump = null;

    private $savableConfiguration = array(
        "unique_entries" => false,
        "admin_page_key" => "",
        "entity_key"     => null,
        "entity_post_key"     => null
    );

    public function configureSavable($config = array())
    {
        $this->savableConfiguration = $config + $this->savableConfiguration;
    }

    public function getSavableEntityKey()
    {
        if (!is_null($this->savableConfiguration["entity_key"])) {
            return $this->savableConfiguration["entity_key"];
        }

        return $this->getWordpressKey();
    }

    public function getSavableEntityPostKey()
    {
        if (!is_null($this->savableConfiguration["entity_post_key"])) {
            return $this->savableConfiguration["entity_post_key"];
        }

        return $this->getInputName();
    }

    public function getSavableValidatingEntity()
    {
        return $this;
    }

    public function getSavableDatasourceEntity()
    {
        return $this;
    }

    public function getSavableLink()
    {
        return admin_url('edit.php?post_type='.$this->getSavableEntityKey().'&page='.$this->getSavableEntityKey().'_viewSavableEntries&postID='. $this->ID);
    }

    public function getSavableAttributes()
    {
        return $this->getSavableValidatingEntity()->getAttributes();
    }

    public function extractSavableAttributeLabels($attributes)
    {
        $labels = array();

        foreach ($attributes as $key => $attributeConfig) {
            $labels[$key] = $key;
        }

        return $labels;
    }

    public function extractSavableSubmissionAnswers($submission, $attributes)
    {
        $values = array();

        foreach ($attributes as $key => $attributeConfig) {
            $values[$key] = $submission->getAnswerFor($key);
        }

        return $values;
    }

    public function getSavableDisplayedAttributesSummaryView()
    {
        return $this->getSavableAttributes();
    }

    public function getSavableDisplayedAttributesDetailedView()
    {
        return $this->getSavableAttributes();
    }

    public function getQuestionsHash()
    {
        return $this->savableDump->getQuestionsHash();
    }

    public function save(array $data)
    {
        if ($this->savableConfiguration['unique_entries']) {
            if ($this->userIdHasPacticipated($userId)) {
                throw new Exception(__("User has already participated.", "ip"));
            }
        }

        // We expect this to come for a $this->request->data() call
        // and there could be garbage input in there.
        $ourData = $data[$this->getSavableEntityPostKey()];
        $parsedData = array();

        foreach ($this->getSavableAttributes() as $key => $attributeConfig) {
            $parsedData[$key] = $ourData[$key];
        }

        $userId = null;
        if (array_key_exists("userentity", $data)) {
            $userId = (int)$data["userentity"]["ID"];
        }

        return $this->getDump()->insert($userId, $parsedData);
    }

    private function getDump()
    {
        if (is_null($this->savableDump)) {
            $this->setDump(new SavableQuery($this->getSavableDatasourceEntity()));
        }

        return $this->savableDump;
    }

    private function setDump($dump)
    {
        return $this->savableDump = $dump;
    }

    public function userHasParticipated($wordpressUser)
    {
        return $this->userIdHasParticipated($wordpressUser->ID);
    }

    public function userIdHasParticipated($userId)
    {
        return $this->getDump()->userHasParticipated($userId);
    }

    public function getSavableParticipantIds()
    {
        return $this->getDump()->getParticipantIds();
    }

    public function getSavableEntriesCount()
    {
        return $this->getDump()->getCount();
    }

    public function getSavableSubmissions($start = 0, $count = 20)
    {
        return $this->getDump()->getSubmissions($start, $count);
    }

    public function getSavableSubmission($id)
    {
        return $this->getDump()->getSubmission($id);
    }

    public function getAllSavableEntries()
    {
        return $this->getDump()->getEntries();
    }

    public function getSavableEntriesIgnoredCount()
    {
        return $this->getDump()->getIgnoredCount();
    }

    public function parseSavableAnswer($answer, $submission)
    {
        return $answer->field_value;
    }
}
