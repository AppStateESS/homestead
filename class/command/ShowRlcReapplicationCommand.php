<?php

class ShowRlcReapplicationCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars(){
        return array('action'=>'ShowRlcReapplication');
    }

    public function execute(CommandContext $context){

        echo 'ohh hai';
    }
}

?>