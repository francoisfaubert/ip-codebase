<?php
namespace IP\Code\Strata\Implementation\Savable\Controller;

use Strata\Model\CustomPostType\ModelEntity;
use Strata\Model\CustomPostType\CustomPostType;
use Strata\Model\CustomPostType\LabelParser;
use Strata\View\Template;

use IP\Code\Strata\Implementation\Savable\Model\SavableQuery;

use WP_Post;

trait SavableControllerTrait {

    public function viewSavableEntries()
    {
        $this->view->set("editUrlBase", "edit.php?post_type=".$this->request->get('post_type')."&page=".$this->request->get('page'));

        if ($this->request->hasGet("viewEntry")) {
            $this->viewSavableResultDetails();
        } elseif ($this->request->hasGet("postID")) {
            $this->viewSavableSummary();
        } else {

            if ($this->request->hasGet("deleteSubmission")) {
                $this->deleteSubmission();
            }

            $this->viewSavableEntriesList();
        }
    }

    protected function viewSavableResultDetails()
    {
        $postId = (int)$this->request->get("postID");
        $entryId = (int)$this->request->get("viewEntry");

        $entity = $this->getSavableEntity(get_post($postId));
        $this->view->set("entity", $entity);
        $this->view->set("datasourceEntity", $entity->getSavableDatasourceEntity());

        $submission = $entity->getSavableSubmission($entryId);
        $this->view->set("submission", $submission);

        $templateFile = $this->getSavableDetailsTemplatePath();

        if (is_null($templateFile)) {
            $this->view->set("entityLabel", $this->getSavableTitle());
            $content = Template::parseFile($this->getDefaultSavableDetailsTemplatePath(), $this->view->getVariables());
        } else {
            $content = $this->view->loadTemplate($templateFile);
        }

        $this->view->render(array(
            "content" => $content
        ));
    }

    protected function getSavableDefaultPostType()
    {
        return $this->request->get("post_type");
    }

    protected function viewSavableEntriesList()
    {
        $querier = new SavableQuery();
        $this->view->set("recentEntities", $querier->getPage(0, 20, $this->getSavableDefaultPostType()));

        $templateFile = $this->getSavableListTemplatePath();

        if (is_null($templateFile)) {
            $this->view->set("entityLabel", $this->getSavableTitle());
            $content = Template::parseFile($this->getDefaultSavableListTemplatePath(), $this->view->getVariables());
        } else {
            $content = $this->view->loadTemplate($templateFile);
        }

        $this->view->render(array(
            "content" => $content
        ));
    }

    protected function viewSavableSummary()
    {
        $postId = (int)$this->request->get("postID");
        $entity = $this->getSavableEntity(get_post($postId));

        $this->view->set("entity", $entity);
        $this->view->set("datasourceEntity", $entity->getSavableDatasourceEntity());

        $templateFile = $this->getSavableSummaryTemplatePath();

        if (is_null($templateFile)) {
            $this->view->set("entityLabel", $this->getSavableTitle());
            $content = Template::parseFile($this->getDefaultSavableSummaryTemplatePath(), $this->view->getVariables());
        } else {
            $content = $this->view->loadTemplate($templateFile);
        }

        $this->view->render(array(
            "content" => $content
        ));
    }

    protected function getSavableEntity(WP_Post $post = null)
    {
        $name = $this->getRelatedEntityName();

        $entity = ModelEntity::factory($name);
        $entity->bindToObject($post);

        return $entity;
    }

    protected function getSavableTitle()
    {
        $name = $this->getRelatedEntityName();

        $model = CustomPostType::factory($name);
        $labelParser = new LabelParser($model);
        $labelParser->parse();

        return $labelParser->plural();
    }

    protected function getSavableSummaryTemplatePath()
    {
        return null;
    }

    protected function getSavableListTemplatePath()
    {
        return null;
    }

    protected function getSavableDetailsTemplatePath()
    {
        return null;
    }

    private function deleteSubmission()
    {
        $querier = new SavableQuery();
        $this->view->set("deletedRecord", $querier->delete($this->request->get("deleteSubmission")));
    }

    private function getDefaultSavableSummaryTemplatePath()
    {
        return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "Template" . DIRECTORY_SEPARATOR . "savableEntitySummary.php";
    }

    private function getDefaultSavableListTemplatePath()
    {
        return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "Template" . DIRECTORY_SEPARATOR . "savableList.php";
    }

    private function getDefaultSavableDetailsTemplatePath()
    {
        return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "Template" . DIRECTORY_SEPARATOR . "savableDetails.php";
    }

    private function getRelatedEntityName()
    {
        // Guess the model entity name form the controller name.
        $controllerName = $this->getShortName();
        return str_replace("Controller", "", $controllerName);
    }

}
