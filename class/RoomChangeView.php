<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'Command.php');

class RoomChangeView extends View {

    public $command;
    public $request;

    public function __construct(Command $command, RoomChangeRequest $request=NULL){
        $this->command = $command;
        $this->request = $request;
    }

    public function show(){
        if($this->command instanceof StudentRoomChangeCommand){
            if(is_null($this->request)){
                return $this->studentSubmitView();
            } else {
                return $this->studentTrack();
            }
        } elseif($this->command instanceof RDRoomChangeCommand){
            if(!is_null($this->request)){
                return $this->rdManage();
            } else {
                return $this->rdList();
            }
        } elseif($this->command instanceof HousingRoomChangeCommand){
            if(!is_null($this->request) && !($this->request->state instanceof DeniedChangeRequest)){
                return $this->housingManage();
            } elseif(!is_null($this->request)){
                return $this->housingHistory();
            } else {
                return $this->housingList();
            }
        }
    }

    public function studentSubmitView(){
        $form = new PHPWS_Form('room_change_request');
        $form->addText('cell_num');
        $form->setLabel('cell_num', 'Cellphone Number');
        $form->addCheck('cell_opt_out');
        
        $form->addTextArea('reason');
        $form->setLabel('reason', 'Reason');

        //TODO: building preference here

        $form->addSubmit('Submit Request');

        $cmd = CommandFactory::getCommand('SubmitRoomChangeRequest');
        $cmd->initForm($form);

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'student/room_change_form.tpl');
    }

    public function studentTrack(){
        $tpl['STATUS'] = $this->request->getStatus();

        return PHPWS_Template::process($tpl, 'hms', 'student/room_change_status.tpl');
    }

    public function rdManage(){
        $student = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());
        if($this->request->state instanceof RDApprovedChangeRequest){
            $tpl['STUDENT'] = $student->getFullNameProfileLink();
            $tpl['STATUS']  = $this->request->getStatus();
            return PHPWS_Template::process($tpl, 'hms', 'admin/room_change_status.tpl');
        }

        $form = new PHPWS_Form('room_change_approval');
        //todo: choose bed
        $form->addRadio('approve_deny', array('approve', 'deny'));
        $form->setLabel('approve_deny', array('Approve', 'Deny'));

        $form->addTextArea('reason');
        $form->setLabel('reason', 'Reason');

        $form->addSubmit('Submit');

        $cmd = CommandFactory::getCommand('RDSubmitUpdate');
        $cmd->initForm($form);

        $tpl = $form->getTemplate();

        $tpl['USERNAME']       = $student->getUsername();
        $tpl['FULLNAME']       = $student->getFullName();
        $tpl['NUMBER']         = $this->request->cell_number;
        $tpl['STUDENT_REASON'] = $this->request->reason;

        return PHPWS_Template::process($tpl, 'hms', 'admin/rd_approve_roomchange.tpl');
    }

    public function rdList(){
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('hms_room_change_request', 'RoomChangeRequest');
        $pager->setModule('hms');
        $pager->setTemplate('admin/rd_manage_roomchange_pager.tpl');
        $pager->addRowTags('rdRowFunction');
        $pager->setEmptyMessage('No pending room change requests.');
        $pager->addWhere('state', ROOM_CHANGE_DENIED, '<>');
        $pager->addWhere('state', ROOM_CHANGE_COMPLETED, '<>');
        $pager->addWhere('term', Term::getSelectedTerm());
        $pager->setOrder('state', 'asc');
        
        return $pager->get();
    }

    public function housingManage(){
        $student = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());
        if($this->request->state instanceof HousingApprovedChangeRequest){
            $tpl['STUDENT'] = $student->getFullNameProfileLink();
            $tpl['STATUS']  = $this->request->getStatus();
            return PHPWS_Template::process($tpl, 'hms', 'admin/room_change_status.tpl');
        }

        $form = new PHPWS_Form('room_change_approval');
        //todo: choose bed
        $form->addRadio('approve_deny', array('approve', 'deny'));
        $form->setLabel('approve_deny', array('Approve', 'Deny'));

        $form->addTextArea('reason');
        $form->setLabel('reason', 'Reason');

        $form->addSubmit('Submit');

        $cmd = CommandFactory::getCommand('HousingSubmitUpdate');
        $cmd->initForm($form);

        $tpl = $form->getTemplate();

        $tpl['USERNAME']       = $student->getUsername();
        $tpl['FULLNAME']       = $student->getFullName();
        $tpl['NUMBER']         = $this->request->cell_number;
        $tpl['STUDENT_REASON'] = $this->request->reason;

        return PHPWS_Template::process($tpl, 'hms', 'admin/housing_approve_roomchange.tpl');
    }

    public function housingList(){
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        Layout::addStyle('controlpanel');
        $panel = new PHPWS_Panel('room_change_panel');
        $tabs = array();
        $tabs['approve'] = array('title'=>'Pending Approval', 'link'=>'index.php?module=hms&action=HousingRoomChange&tab=approve', 'link_title'=>'View Students Awaiting Approval');
        $tabs['complete'] = array('title'=>'Pending Completion', 'link'=>'index.php?module=hms&action=HousingRoomChange&tab=complete', 'link_title'=>'View Requests awaiting Completion');        

        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('hms_room_change_request', 'RoomChangeRequest');
        $pager->setModule('hms');
        $pager->setTemplate('admin/housing_manage_roomchange_pager.tpl');
        $pager->addRowTags('housingRowFunction');
        $pager->setEmptyMessage('No pending room change requests.');
        $pager->addWhere('state', ROOM_CHANGE_DENIED, '<>');

        if(!isset($_GET['tab']) || $_GET['tab'] == 'approve'){
            $pager->addWhere('state', ROOM_CHANGE_RD_APPROVED);
        } elseif(isset($_GET['tab']) && $_GET['tab'] == 'complete') {
            $pager->addWhere('state', ROOM_CHANGE_HOUSING_APPROVED);
        }
        $pager->addWhere('term', Term::getSelectedTerm());

        $panel->quickSetTabs($tabs);
        return $panel->display($pager->get(), 'Manage Room Change Requests', '');
    }
}

?>