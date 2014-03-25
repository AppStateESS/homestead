<?php

class LotteryDenyRoommateRequestView extends hms\View{

    private $request;
    private $term;

    public function __construct($request, $term)
    {
        $this->request = $request;
        $this->term = $term;
    }

    public function show()
    {

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initCoreClass('Captcha.php');

        $requestor = StudentFactory::getStudentByUsername($this->request['requestor'], $this->term);

        $tpl['REQUESTOR']  = $requestor->getName();
        $tpl['CAPTCHA']    = Captcha::get();

        $submitCmd = CommandFactory::getCommand('LotteryDenyRoommateRequest');
        $submitCmd->setRequestId($this->request['id']);

        $form = new PHPWS_Form();
        $submitCmd->initForm($form);

        $form->addSubmit('deny', 'Deny Roommate Request');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        $this->setTitle('Deny Roommate Request');

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_deny_roommate_request.tpl');
    }
}

?>
