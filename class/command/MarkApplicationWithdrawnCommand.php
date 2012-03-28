<?php

/**
 * MarkApplicationWithdrawnCommand -- Marks a housing application as withdrawn. Depricated. Use 'CancelHousingAppCommand' instead.
 * 
 * @deprecated
 * @author jbooker
 */

class MarkApplicationWithdrawnCommand extends Command {
    
    private $appId;
    
    public function setAppId($id){
        $this->appId = $id;
    }
    
    public function getRequestVars(){
        return array('action'=>'MarkApplicationWithdrawn', 'appId'=>$this->appId);
    }
    
    public function execute(CommandContext $context)
    {
        
        if(!Current_User::allow('withdrawn_search')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to makr applications withdrawn.');
        }

        $id = $context->get('appId');
        
        if(!isset($id) || is_null($id)){
            throw new InvalidArugumentException('Missing application id.');
        }
        
        PHPWS_Core::initModclass('hms', 'HousingApplicationFactory.php');
        
        $app = HousingApplicationFactory::getApplicationById($context->get('appId'));
        $app->setWithdrawn(1);
        
        $app->save();
        
        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Application successfully marked as withdrawn.');
        $context->goBack();
    }
}

?>