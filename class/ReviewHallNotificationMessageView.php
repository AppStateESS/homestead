<?php

namespace Homestead;

/**
 * ReviewHallNotificationMessageView
 *
 *  Creates the view for reviewing hall notifications.
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package mod
 * @subpackage hms
 */
PHPWS_Core::initModClass('hms', 'HMS_Floor.php');

class ReviewHallNotificationMessageView extends View {

    private $subject;
    private $body;
    private $anonymous;
    private $halls;

    public function __construct($subject=null, $body=null, $anonymous=false, $halls=array(), $floors=array()){
        $this->subject   = $subject;
        $this->body      = $body;
        $this->anonymous = $anonymous;
        $this->halls     = $halls;
        $this->floors    = $floors;
    }

    public function show(){
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        $tpl = array();

        $template = new PHPWS_Template('hms');
        $template->setFile('admin/review_hall_email.tpl');

        if(is_array($this->floors)){
            foreach($this->floors as $floorId){
                $floor = new HMS_Floor();
                $floor->id = $floorId;
                $floor->load();
                $floor->loadHall();
                $tpl['halls'][$floor->_hall->getHallName()][] = 'Floor '.$floor->getFloorNumber();
            }
        } else {
            $floor = new HMS_Floor();
            $floor->id = $this->floors;
            $floor->load();
            $floor->loadHall();
            $tpl['halls'][$floor->_hall->getHallName()][] = 'Floor '.$floor->getFloorNumber();
        }

        $tpl['FROM']    = ($this->anonymous && Current_User::allow('hms', 'anonymous_notifications')) ? FROM_ADDRESS : (Current_User::getUsername() . '@' . DOMAIN_NAME);
        $tpl['SUBJECT'] = $this->subject;
        $tpl['BODY']    = preg_replace('/\n/', '<br />', $this->body);

        $editCmd = CommandFactory::getCommand('ShowHallNotificationEdit');
        $tpl['EDIT_URI'] = $editCmd->getUri();

        /*
        $form->addHidden('anonymous',   isset($this->anonymous) ? $this->anonymous : '');
        $form->addHidden('subject',     $this->subject);
        $form->addHidden('body',        $this->body);
        $form->addHidden('hall',        $this->halls);
        $form->addHidden('floor',       $this->floors);
        $form->addSubmit('back',        'Edit Message');
        */


        $form2 = new PHPWS_Form('review_email');

        $sendCmd = CommandFactory::getCommand('SendNotificationEmails');
        $sendCmd->initForm($form2);

        $form2->addHidden('anonymous',  isset($this->anonymous) ? $this->anonymous : '');
        $form2->addHidden('subject',    $this->subject);
        $form2->addHidden('body',       $this->body);
        $form2->addHidden('hall',       $this->halls);
        $form2->addHidden('floor',      $this->floors);

        foreach($tpl['halls'] as $hall=>$floors){
            foreach($floors as $floor){
                $template->setCurrentBlock('floors');
                $template->setData(array("FLOOR"=>$floor));
                $template->parseCurrentBlock();
            }
            $template->setCurrentBlock('halls');
            $template->setData(array("HALL"=>$hall));
            $template->parseCurrentBlock();
        }

        $form2->mergeTemplate($tpl);
        $tpl = $form2->getTemplate();

        $template->setCurrentBlock('remainder');
        $template->setData($tpl);
        $template->parseCurrentBlock();

        return $template->get();
    }
}
