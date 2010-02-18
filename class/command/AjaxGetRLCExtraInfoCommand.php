<?php

class AjaxGetRLCExtraInfoCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'AjaxGetRLCExtraInfo');
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        
        $rlc = new HMS_Learning_Community($context->get('id'));
        
        if($rlc->extra_info != NULL){
            $tpl = array();
            $tpl['INFO'] = $rlc->extra_info;
            
            $returnData = array();
            $returnData['content']  = PHPWS_Template::process($tpl, 'hms', 'student/rlcExtraInfoDialog.tpl'); 
            $returnData['title']    = 'Extra Info for ' . $rlc->community_name; 
            
            $context->setContent(json_encode($returnData));
        }else{
            $context->setContent('');
        }
    }
}

?>