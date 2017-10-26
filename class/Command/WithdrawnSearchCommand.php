<?php

namespace Homestead\Command;

use \Homestead\WithdrawnSearch;
use \Homestead\Term;

//TODO make this better

class WithdrawnSearchCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'WithdrawnSearch');
    }

    public function execute(CommandContext $context)
    {
        $term = Term::getSelectedTerm();

        $search = new WithdrawnSearch($term);
        $search->doSearch();

        $context->setContent($search->getHTMLView());
    }

}
