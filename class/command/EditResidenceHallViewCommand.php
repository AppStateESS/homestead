<?php

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class EditResidenceHallViewCommand extends Command {

    private $hallId;

    function setHallId($id){
        $this->hallId = $id;
    }

    function getRequestVars()
    {
        $vars = array('action'=>'EditResidenceHallView');
         
        if(isset($this->hallId)){
            $vars['hallId'] = $this->hallId;
        }
         
        return $vars;
    }

    public function getSubLink($text, $parentVars){
        return PHPWS_Text::moduleLink(dgettext('hms', $text), 'hms', $parentVars);
    }

    function execute(CommandContext $context)
    {
         
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'hall_view') ){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit halls.');
        }

        // Check for a  hall ID
        $hallId = $context->get('hallId');
        if(!isset($hallId)){
            throw new InvalidArgumentException('Missing hall ID.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'ResidenceHallView.php');
         
        $hall = new HMS_Residence_Hall($hallId);

        // Check for a hall/term mismatch, since halls are indexed by ID and not by name & term
        if($hall->term != Term::getSelectedTerm()){
            $residenceHallCmd = CommandFactory::getCommand('SelectResidenceHall');
            $residenceHallCmd->setTitle('Edit a Residence Hall');
            $residenceHallCmd->setOnSelectCmd(CommandFactory::getCommand('EditResidenceHallView'));
            $residenceHallCmd->redirect();
        }

        $hallView = new ResidenceHallView($hall);
         
        $context->setContent($hallView->show());
    }
}

?>
