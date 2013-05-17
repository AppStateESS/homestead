<?php

PHPWS_Core::initModClass('hms', 'DamageTypeFactory.php');

class AddRoomDamageView extends View {
    
    private $room;
    
    public function __construct(HMS_Room $room)
    {
        $this->room = $room;
    }
    
    public function show()
    {
        $submitCmd = CommandFactory::getCommand('AddRoomDamage');
        $submitCmd->setRoom($this->room);
        
        $form = new PHPWS_Form('addDamageForm');
        
        $submitCmd->initForm($form);

        $damageTypes = DamageTypeFactory::getDamageTypeAssoc();
        
        $types = array();
        
        foreach ($damageTypes as $key => $t) {
            $types[$key] = $t['category'] . ' - ' . $t['description'];
        }
        
        $form->addDropBox('damage_type', $types);
        
        $form->addDropBox('side', array('Both'=>'Both', 'Left'=>'Left', 'Right'=>'Right'));
        
        $tags = $form->getTemplate();
        
        return PHPWS_Template::process($tags, 'hms', 'admin/addRoomDamage.tpl');
    }
}

?>