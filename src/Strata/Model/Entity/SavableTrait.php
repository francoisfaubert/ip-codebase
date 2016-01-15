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

    /**
     * Looks for common patterns in $this->request->data() where
     * the entity could find a posted user entity id.
     * This is mainly for legacy purposes and the entity should supply the id
     * using getSavableUserId() when possible.
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function getSavableUserIdInData($data = array())
    {
        if (array_key_exists("userentity", $data)) {
            return (int)$data["userentity"]["ID"];
        }

        return null;
    }

    /**
     * Returns a user id on which the saved data will be associated.
     * The entity is expect to override this function and return null
     * when entity data does not require to be linked to a user.
     * @return int
     */
    public function getSavableUserId()
    {
        return get_current_user_id();
    }

    /**
     * Throws an exception when the userId has already
     * participated and the configuration only requires single
     * participations.
     * @throws Exception
     * @param  int $userId
     */
    private function throwIfSavebleUserIsUnique($userId)
    {
        if ($this->savableConfiguration['unique_entries']) {
            if ($this->userIdHasParticipated($userId)) {
                throw new Exception(__("User has already participated.", "ip"));
            }
        }
    }

    /**
     * Saves an associative array supplied by Controller->request->data()
     * containing a key matching the entities POST key.
     * @param  array  $data  Controller->request->data()
     * @return int id
     */
    public function save(array $data)
    {
        $userId = $this->getSavableUserIdInData($data);
        $this->throwIfSavebleUserIsUnique($userId);

        // We expect this to come for a $this->request->data() call
        // and there could be garbage input in there.
        $ourData = $data[$this->getSavableEntityPostKey()];
        $attributeData = array();

        foreach ($this->getSavableAttributes() as $key => $attributeConfig) {
            $attributeData[$key] = $ourData[$key];
        }

        return $this->getDump()->insert($userId, $attributeData);
    }


    /**
     * Saves the current values of each of the attributes
     * declared at the level of the savable entity.
     * @return int id
     */
    public function saveEntity()
    {
        $userId = $this->getSavableUserId();
        $this->throwIfSavebleUserIsUnique($userId);

        $attributeData = array();

        foreach ($this->getSavableAttributes() as $key => $attributeConfig) {
            $attributeData[$key] = $this->{$key};
        }

        return $this->getDump()->insert($userId, $attributeData);
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
