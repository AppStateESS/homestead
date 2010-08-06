<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

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
        javascript('jquery');
        $form = new PHPWS_Form('room_change_request');
        $form->addText('cell_num');
        $form->setLabel('cell_num', 'Cellphone Number');
        $form->addCheck('cell_opt_out');

        $halls = array(0=>'Choose from below...');
        $halls = $halls+HMS_Residence_Hall::get_halls_array(Term::getSelectedTerm());

        $form->addDropBox('first_choice', $halls);
        $form->setLabel('first_choice', 'First Choice');
        $form->addDropBox('second_choice', $halls);
        $form->setLabel('second_choice', 'Second Choice');

        $form->addTextArea('reason');
        $form->setLabel('reason', 'Reason');

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

        $halls = HMS_Residence_Hall::getHallsDropDownValues(Term::getSelectedTerm());
        javascript('jquery');
        javascript('/modules/hms/assign_student');

        $form = new PHPWS_Form();
        $form->addHidden('username', $student->getUsername());

        $form->addDropBox('residence_hall', $halls);
        $form->setLabel('residence_hall', 'Residence hall: ');
        $form->setMatch('residence_hall', 0);

        $form->addDropBox('floor', array(0 => ''));
        $form->setLabel('floor', 'Floor: ');

        $form->addDropBox('room', array(0 => ''));
        $form->setLabel('room', 'Room: ');

        $form->addDropBox('bed', array(0 => ''));
        $form->setLabel('bed', 'Bed: ');

        $form->addRadio('approve_deny', array('approve', 'deny'));
        $form->setLabel('approve_deny', array('Approve', 'Deny'));

        $form->addTextArea('reason');
        $form->setLabel('reason', 'Reason');

        $form->addSubmit('submit_button', 'Submit');

        $cmd = CommandFactory::getCommand('RDSubmitUpdate');
        $cmd->initForm($form);

        $tpl = $form->getTemplate();

        $tpl['USERNAME']       = $student->getUsername();
        $tpl['FULLNAME']       = $student->getFullName();
        $tpl['NUMBER']         = $this->request->cell_number;
        $tpl['STUDENT_REASON'] = $this->request->reason;
        $tpl['preferences']    = array();

        foreach($this->request->preferences as $preference){
            $hall = new HMS_Residence_Hall();
            $hall->id = $preference['building'];
            $hall->load();
            $tpl['preferences'][] = array('PREFERENCE'=>$hall->getHallName());
        }

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

        $bed   = new HMS_Bed($this->request->bed_id);
        $room  = $bed->get_parent();
        $floor = $room->get_parent();
        $hall  = $floor->get_parent();

        $tpl['BED'] = $hall->getHallName() . ' <b>Floor</b> ' . $floor->getFloorNumber() . ' <b>Room</b> ' . $room->room_number . ' <b>Bed -</b> '.$bed->bed_letter;

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