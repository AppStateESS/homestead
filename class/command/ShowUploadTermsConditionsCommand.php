<?php

class ShowUploadTermsConditionsCommand extends Command {

    private $term;
    private $type;

    public function getRequestVars() {
        $vars = array('action' => 'ShowUploadTermsConditions');

        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }

        if(isset($this->type)) {
            $vars['type'] = $this->type;
        }

        return $vars;
    }

    public function setTerm($term) {
        $this->term = $term;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function execute(CommandContext $context) {
        if(!isset($this->term)) {
            $this->term = $context->get('term');
        }

        $term = $this->term;

        if(!isset($this->type)) {
            $this->type = $context->get('type');
        }

        $type = $this->type;

        PHPWS_Core::initModClass('hms', 'TermsConditionsUploadView.php');
        $view = new TermsConditionsUploadView($term, $type);
        echo $view->show();
        HMS::quit();
    }
}

?>