<?php
namespace IP\Code\Strata\Implementation\Savable\Model\Entity;

use IP\Code\Strata\Implementation\Savable\Model\SavableQuery;

class SavableSubmissionEntity extends \Strata\Model\CustomPostType\ModelEntity {

    private $answers;
    private $associatedEntity;

    public function bindToEntity($entity)
    {
        $this->associatedEntity = $entity;
    }

    public function getAnswers()
    {
        if (is_null($this->answers)) {
            $query = new SavableQuery($this->associatedEntity);
            $this->answers = $query->getSubmissionAnswers($this->ID);
        }

        return $this->answers;
    }

    public function getAnswerFor($attributeKey)
    {
        foreach ($this->getAnswers() as $answer) {
            if ($answer->field_key === $attributeKey) {
                return $answer;
            }
        }
    }

}
