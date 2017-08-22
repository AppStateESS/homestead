<?php

namespace Homestead\command;

use \Homestead\Command;

class AjaxGetRoomDamageTypesCommand extends Command {


    public function getRequestVars(){
        return array('action'=>'AjaxGetRoomDamageTypes');
    }

    public function execute(CommandContext $context)
    {
        // Get the list of damage types from the database
        $damageTypes = DamageTypeFactory::getDamageTypeAssoc();

        // Group the damage types by their categories
        foreach ($damageTypes as $dmgType){
            $categories[$dmgType['category']][] = $dmgType;
        }

        // Put each category into an format that the front-end expects
        foreach($categories as $categoryName => $categoryDamages){
            $result[] = array('category' => $categoryName,
                              'DamageTypes' => $categoryDamages);
        }

        echo json_encode($result);
        exit;
    }
}
