<?php

/**
 * RaMasqueradeAsSelfCommand
 * Allows RAs to login as the student version of themselves.
 *
 * @author Jeremy Booker
 * @package Hms
 */
class RaMasqueradeAsSelfCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'RaMasqueradeAsSelf');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms','ra_login_as_self')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to login as the student verison of yourself.');
        }

        UserStatus::wearMaskAsSelf();
         
        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}
?>
