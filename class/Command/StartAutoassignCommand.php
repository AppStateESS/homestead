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
        // TODO: PULSE!

        echo "<html><head><title>AUTOASSIGNER TEST MODE</title></head><body><pre>\n\n";
        echo "AUTOASSIGNER 1970s MODE\n\n";

        try {
            $assigner = new Autoassigner(Term::getSelectedTerm());
            $assigner->autoassign();
        } catch(\Exception $e) {
            echo "EXCEPTION CAUGHT: " . $e->getMessage() . "<br /><br />\n\n";
            var_dump($e->getTrace());
        }

        echo "</pre></body></html>\n\n";
        exit(0);
    }
}
