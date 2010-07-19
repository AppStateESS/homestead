<?php

//TODO make this better

class WithdrawnSearchCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'WithdrawnSearch');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'WithdrawnSearch.php');

        $term = Term::getSelectedTerm();

        $search = new WithdrawnSearch($term);

        $context->setContent($search->getHTMLView());
    }

}

?>