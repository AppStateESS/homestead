<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\Autoassigner;

class StartAutoassignCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'StartAutoassign');
    }

    public function execute(CommandContext $context)
    {
        $content = "<pre>AUTOASSIGNER\n\n\n<br /><br /><br />";

        ob_start(); // Start output buffering
        try {
            $assigner = new Autoassigner(Term::getSelectedTerm());
            $assigner->autoassign(); //TODO: Something is wrong in here
        } catch(\Exception $e) {
            echo "EXCEPTION CAUGHT: " . $e->getMessage() . "<br /><br />\n\n";
            //var_dump($e->getTrace());
        }

        // Get output buffer content and end buffering
        $content .= ob_get_contents();
        ob_end_clean();

        $content .= '</pre>';

        $context->setContent($content);
        exit(0);
    }
}
