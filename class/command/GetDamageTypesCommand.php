<?php

class GetDamageTypesCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'GetDamageTypes');
    }

    public function execute(CommandContext $context)
    {
        echo json_encode(DamageTypeFactory::getDamageTypeAssoc());
        exit;
    }
}
