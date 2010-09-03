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
                return $this->pairingList();
            }
        }
    }

    /*
     * studentSubmitView
     *
     *   Creates the view by which a student can submit a room change or swap
     * request.
     *
     * @return html
     */
    public function studentSubmitView(){
        javascript('jquery');

        $form = new PHPWS_Form('room_change_request');

        /* Cell phone */
        $form->addText('cell_num');
        $form->setLabel('cell_num', 'Cellphone Number');
        $form->addCheck('cell_opt_out');

        /* Preferences */
        $halls = array(0=>'Choose from below...');
        $halls = $halls+HMS_Residence_Hall::get_halls_array(Term::getSelectedTerm());

        $form->addDropBox('first_choice', $halls);
        $form->setLabel('first_choice', 'First Choice');
        $form->addDropBox('second_choice', $halls);
        $form->setLabel('second_choice', 'Second Choice');

        /* Swap */
        $form->addText('swap_with');
        $form->setLabel('swap_with', 'ASU Username');

        /* Reason */
        $form->addTextArea('reason');
        $form->setLabel('reason', 'Reason');

        $form->addSubmit('Submit Request');

        /* POST location */
        $cmd = CommandFactory::getCommand('SubmitRoomChangeRequest');
        $cmd->initForm($form);

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'student/room_change_form.tpl');
    }

    /*
     * studentTrack
     *
     *   Creates a view which provides the student information about their current
     * room change request.
     *
     * @return html
     */
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

        $halls = HMS_Residence_Hall::getHallsWithVacanciesArray(Term::getSelectedTerm());
        javascript('jquery');
        javascript('/modules/hms/assign_student');

        $form = new PHPWS_Form();
        $form->addHidden('username', $student->getUsername());

        $form->addRadio('approve_deny', array('approve', 'deny'));
        $form->setLabel('approve_deny', array('Approve', 'Deny'));

        $form->addTextArea('reason');
        $form->setLabel('reason', 'Reason');

        $form->addSubmit('submit_button', 'Submit');

        $cmd = CommandFactory::getCommand('RDSubmitUpdate');
        $cmd->initForm($form);

        $tpl                   = array();
        $tpl['USERNAME']       = $student->getUsername();
        $tpl['FULLNAME']       = $student->getFullName();
        $tpl['NUMBER']         = $this->request->cell_phone;
        $tpl['STUDENT_REASON'] = $this->request->reason;

        //if we aren't switching
        if(empty($this->request->switch_with)){
            //make sure the preferences show up
            $tpl['preferences']    = array();

            //and add the room selection form elements
            $form->addDropBox('residence_hall', $halls);
            $form->setLabel('residence_hall', 'Residence hall: ');
            $form->setMatch('residence_hall', 0);
            
            $form->addDropBox('floor', array(0 => ''));
            $form->setLabel('floor', 'Floor: ');

            $form->addDropBox('room', array(0 => ''));
            $form->setLabel('room', 'Room: ');

            $form->addDropBox('bed', array(0 => ''));
            $form->setLabel('bed', 'Bed: ');

            foreach($this->request->preferences as $preference){
                $hall = new HMS_Residence_Hall();
                $hall->id = $preference['building'];
                $hall->load();
                $tpl['preferences'][] = array('PREFERENCE'=>$hall->getHallName());
            }
        } else {
            $tpl['SWAP'] = $this->request->switch_with; //TODO: pull their real name and assignment
        }
        
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

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
        //only grab requests that this rd has permission for
        foreach($this->command->_memberships as $membership){
            $pager->db->addWhere('curr_hall', $membership['instance'], '=', 'OR', 'valid_halls');
        }
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

        if($this->request->state instanceof WaitingForPairing){
            $form->addCheck('approve_deny', 'deny');
            $form->setLabel('approve_deny', 'Deny');
        } else {
            $form->addRadio('approve_deny', array('approve', 'deny'));
            $form->setLabel('approve_deny', array('Approve', 'Deny'));
        }

        $form->addTextArea('reason');
        $form->setLabel('reason', 'Reason');

        $form->addSubmit('Submit');

        $cmd = CommandFactory::getCommand('HousingSubmitUpdate');
        $cmd->username = $this->request->username;
        $cmd->initForm($form);

        $tpl = $form->getTemplate();

        $tpl['USERNAME']       = $student->getUsername();
        $tpl['FULLNAME']       = $student->getFullName();
        $tpl['NUMBER']         = $this->request->cell_phone;
        $tpl['STUDENT_REASON'] = $this->request->reason;

        $bed   = new HMS_Bed($this->request->requested_bed_id);
        $room  = $bed->get_parent();
        $floor = $room->get_parent();
        $hall  = $floor->get_parent();

        $tpl['BED'] = $hall->getHallName() . ' <b>Floor</b> ' . $floor->getFloorNumber() . ' <b>Room</b> ' . $room->room_number . ' <b>Bed -</b> '.$bed->bed_letter;

        return PHPWS_Template::process($tpl, 'hms', 'admin/housing_approve_roomchange.tpl');
    }

    public function housingList(){
        if(!Current_User::allow('admin_approve_room_change')){
            throw new Exception("I'm sorry, I can't do that Dave.");
        }

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

    public function pairingList(){
        if(!Current_User::allow('admin_approve_room_change')){
            throw new Exception("I'm sorry, I can't do that Dave.");
        }

        $db = new PHPWS_DB('hms_room_change_request');
        $db->addWhere('state', ROOM_CHANGE_PAIRED, '=', 'or');
        $db->addWhere('state', ROOM_CHANGE_PAIRING, '=', 'or');
        $db->addOrder('state desc');

        $results = $db->getObjects('RoomChangeRequest');

        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException($results->toString());
        }

        //fake the empty message
        if(empty($results)){
            return PHPWS_Template::process(array('EMPTY'=>'No room swaps outstanding.'), 'hms', 'admin/room_change_approve_pairing.tpl');
        }

        //create the commands to use to get the deny/view links
        $view    = CommandFactory::getCommand('HousingRoomChange');

        $tpl = array();
        foreach($results as $result){
            $result->load();

            if($result->state instanceof WaitingForPairing){
                $paired = $result->state->attemptToPair(); //TODO: Move this to the top if it was paired
                //if it paired, then we need to save so that we don't forget the pairing
                if($paired){
                    $result->save();
                }
                $result->load(); //have to unmap the defines to the state class
            }

            $student = StudentFactory::getStudentByUsername($result->username, Term::getSelectedTerm());

            $actions = "";
            $view->username = $student->getUsername();
            $actions .= $view->getLink('Manage');
            $tpl['requests'][] = array('NAME'     => $student->getFullName(),
                                       'USERNAME' => $student->getUsername(),
                                       'STATUS'   => $result->getStatus(),
                                       'ACTIONS'  => $actions);
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/room_change_approve_pairing.tpl');
    }
}

//?>