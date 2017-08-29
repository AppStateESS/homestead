<?php

namespace Homestead\Command;

 

class ShowRoommateProfileSearchCommand extends Command {

    private $term;

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getRequestVars()
    {
        return array('action' => 'ShowRoommateProfileSearch',
                     'term'   => $this->term);
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'RoommateProfileSearchForm.php');
        $term = $context->get('term');
        $view = new RoommateProfileSearchForm($term);

        $context->setContent($view->show());
    }
}
