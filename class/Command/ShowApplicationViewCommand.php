<?php

namespace Homestead\Command;

use \Homestead\HousingApplicationView;
use \Homestead\NotificationView;

class ShowApplicationViewCommand extends Command {
    protected $appId;
    protected $username;

    public function setAppId($id){
        $this->appId = $id;
    }

    public function getAppId(){
        return $this->appId;
    }

    public function setUsername($username){
        $this->username = $username;
    }

    public function getUsername(){
        return $this->username;
    }

    public function getRequestVars(){
        return array('action'   => 'ShowApplicationView',
                     'username' => $this->username,
                     'appId'    => $this->appId);
    }

    public function execute(CommandContext $context){
        try{
            $view = new HousingApplicationView($context->get('appId'));
            $context->setContent($view->show());
        } catch(\Exception $e){
            \NQ::simple('hms', NotificationView::ERROR, $e);
        }
    }
}
