<?php

/**
 * View class responsible for showing the 'Edit Room' interface
 *
 * @author jbooker
 * @package HMS
 */
class RoomView extends hms\View {

    private $hall;
    private $floor;
    private $room;

    private $damageTypes;

    /**
     * Constructor
     *
     * @param HMS_Residence_Hall    $hall
     * @param HMS_Floor             $floor
     * @param HMS_Room              $room
     * @param Array                 $damageTypes
     */
    public function __construct(HMS_Residence_Hall $hall, HMS_Floor $floor, HMS_Room $room, Array $damageTypes){
        $this->hall		= $hall;
        $this->floor	= $floor;
        $this->room		= $room;

        $this->damageTypes = $damageTypes;
    }

    /**
     * @see View::show()
     */
    public function show()
    {
        /*** Header Info ***/
        $tpl = array();
        $tpl['ROOM_PERSISTENT_ID'] = $this->room->getPersistentId();
        $tpl['TERM'] = $this->room->getTerm();
        $tpl['TERM'] = Term::getPrintableSelectedTerm();
        $tpl['HALL_NAME']           = $this->hall->getLink();
        $tpl['FLOOR_NUMBER']        = $this->floor->getLink('Floor');

        /*** Page Title ***/
        $tpl['ROOM'] = $this->room->getRoomNumber();

        /*** Room Attributes Labels ***/
        if($this->room->isOffline()){
            $tpl['OFFLINE_ATTRIB'] = 'Offline';
        }

        if($this->room->isReserved()){
            $tpl['RESERVED_ATTRIB'] = 'Reserved';
        }

        if($this->room->isRa()){
            $tpl['RA_ATTRIB'] = 'RA';
        }

        if($this->room->isPrivate()){
            $tpl['PRIVATE_ATTRIB'] = 'Private';
        }

        if($this->room->isOverflow()){
            $tpl['OVERFLOW_ATTRIB'] = 'Overflow';
        }

        if($this->room->isParlor()){
            $tpl['PARLOR_ATTRIB'] = 'Parlor';
        }

        if($this->room->isADA()){
            $tpl['ADA_ATTRIB'] = 'ADA';
        }

        if($this->room->isHearingImpaired()){
            $tpl['HEARING_ATTRIB'] = 'Hearing Impaired';
        }

        if($this->room->bathEnSuite()){
            $tpl['BATHENSUITE_ATTRIB'] = 'Bath en Suite';
        }

        $number_of_assignees    = $this->room->get_number_of_assignees();

        $tpl['NUMBER_OF_BEDS']      = $this->room->get_number_of_beds();
        $tpl['NUMBER_OF_ASSIGNEES'] = $number_of_assignees;

        $form = new PHPWS_Form;

        $submitCmd = CommandFactory::getCommand('EditRoom');
        $submitCmd->setRoomId($this->room->id);
        $submitCmd->initForm($form);

        $form->addText('room_number', $this->room->getRoomNumber());
        $form->setLabel('room_number', 'Room Number');
        $form->addCssClass('room_number', 'form-control');

        /*** Room Gender ***/
        if($number_of_assignees == 0){
            // Room is empty, show the drop down so the user can change the gender
            $roomGenders = array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, AUTO=>AUTO_DESC);

            // Check if the user is allowed to set rooms to co-ed, if so add Co-ed to the drop down
            if(Current_User::allow('hms', 'coed_rooms')){
                $roomGenders[COED] = COED_DESC;
            }

            $form->addDropBox('gender_type', $roomGenders);
            $form->setMatch('gender_type', $this->room->gender_type);
            $form->addCssClass('gender_type', 'form-control');
        }else{
            // Room is not empty so just show the gender (no drop down)
            $tpl['GENDER_MESSAGE'] = HMS_Util::formatGender($this->room->getGender());

            // Add a hidden variable for 'gender_type' so it will be defined upon submission
            $form->addHidden('gender_type', $this->room->gender_type);

            // Show the reason the gender could not be changed.
            if($number_of_assignees != 0){
                $tpl['GENDER_REASON'] = 'Remove occupants to change room gender.';
            }
        }

        //Always show the option to set the default gender
        $form->addDropBox('default_gender', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, AUTO => AUTO_DESC));
        $form->setLabel('default_gender', 'Default Gender');
        $form->setMatch('default_gender', $this->room->default_gender);
        $form->addCssClass('default_gender', 'form-control');

        $form->addDropBox('rlc_reserved', array("0"=>"Choose RLC") + RlcFactory::getRlcList($this->room->getTerm()));
        $form->setLabel('rlc_reserved', 'Reserved for RLC');
        $form->setMatch('rlc_reserved', $this->room->getReservedRlcId());
        $form->addCssClass('rlc_reserved', 'form-control');

        $form->addCheck('offline', 1);
        $form->setLabel('offline', 'Offline');
        $form->setMatch('offline', $this->room->isOffline());

        $form->addCheck('reserved', 1);
        $form->setLabel('reserved','Reserved');
        $form->setMatch('reserved', $this->room->isReserved());

        $form->addCheck('ra', 1);
        $form->setLabel('ra','Reserved for RA');
        $form->setMatch('ra', $this->room->isRa());

        $form->addCheck('private', 1);
        $form->setLabel('private','Private');
        $form->setMatch('private', $this->room->isPrivate());

        $form->addCheck('overflow', 1);
        $form->setLabel('overflow','Overflow');
        $form->setMatch('overflow', $this->room->isOverflow());

        $form->addCheck('parlor', 1);
        $form->setLabel('parlor','Parlor');
        $form->setMatch('parlor', $this->room->isParlor());

        $form->addCheck('ada', 1);
        $form->setLabel('ada', 'ADA');
        $form->setMatch('ada', $this->room->isAda());

        $form->addCheck('hearing_impaired', 1);
        $form->setLabel('hearing_impaired', 'Hearing Impaired');
        $form->setMatch('hearing_impaired', $this->room->isHearingImpaired());

        $form->addCheck('bath_en_suite', 1);
        $form->setLabel('bath_en_suite', 'Bath en Suite');
        $form->setMatch('bath_en_suite', $this->room->bathEnSuite());

        $form->addSubmit('submit', 'Submit');

        // Assignment pagers
        $tpl['BED_PAGER'] = HMS_Bed::bed_pager_by_room($this->room->id);

        // if the user has permission to view the form but not edit it then
        // disable it
        if(    Current_User::allow('hms', 'room_view')
        && !Current_User::allow('hms', 'room_attributes')
        && !Current_User::allow('hms', 'room_structure'))
        {
            $form_vars = get_object_vars($form);
            $elements = $form_vars['_elements'];

            foreach($elements as $element => $value){
                $form->setDisabled($element);
            }
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        $reasonsList = HMS_Room::listReserveReasons();

        $tpl['ATHLETICS_OPTIONS'] =  $reasonsList['Athletics'];
        $tpl['SPECIAL_NEEDS_OPTIONS'] =  $reasonsList['SpecialNeeds'];
        $tpl['SCHOLARS_OPTIONS'] =  $reasonsList['ScholarsOrganizations'];
        $tpl['MISC_OPTIONS'] =  $reasonsList['Miscellaneous'];

        if($this->room->getReservedReason() == "") {
            $tpl['CURRENT_REASON'] = 'none';
        } else {
            $tpl['CURRENT_REASON'] = $this->room->getReservedReason();
        }

        $tpl['RESERVED_NOTES'] = $this->room->getReservedNotes();

        Layout::addPageTitle("Edit Room");

        $tpl['ROOM_DAMAGE_LIST'] = $this->roomDamagePager();


        if(Current_User::allow('hms', 'add_room_dmg')){
            $dmgCmd = CommandFactory::getCommand('ShowAddRoomDamage');
            $dmgCmd->setRoom($this->room);
            $tpl['ADD_DAMAGE_URI']  = $dmgCmd->getURI();
        }

        $tpl['vendor_bundle'] = AssetResolver::resolveJsPath('assets.json', 'vendor');
        $tpl['entry_bundle'] = AssetResolver::resolveJsPath('assets.json', 'roomDamages');

        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_room.tpl');
    }

    private function roomDamagePager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('hms', 'RoomDamage.php');

        $pager = new DBPager('hms_room_damage', 'RoomDamageDb');
        $pager->db->addJoin('LEFT OUTER', 'hms_room_damage', 'hms_damage_type', 'damage_type', 'id');


        $pager->addWhere('hms_room_damage.room_persistent_id', $this->room->getPersistentId());
        $pager->addWhere('hms_room_damage.repaired', 0); // Only non-repaired damages


        $pager->setModule('hms');
        $pager->setTemplate('admin/roomDamagesPager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No damages found.");
        $pager->addRowTags('getRowTags');

        return $pager->get();
    }
}
