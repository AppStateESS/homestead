<?php


/**
 * Command/controller for showing the PackageDesk interface
 * @author jbooker
 *
 */
class ShowPackageDeskMenuCommand extends Command {

    /**
     * (non-PHPdoc)
     * @see Command::getRequestVars()
     */
    public function getRequestVars()
    {
        return array('action'=>'ShowPackageDeskMenu');
    }
    
    /**
     * (non-PHPdoc)
     * @see Command::execute()
     */
    public function execute(CommandContext $context)
    {
        // Check permissions
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'package_desk')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to access the Package Desk.');
        }
        
        PHPWS_Core::initModClass('hms', 'PackageDeskFactory.php');
        $desks = PackageDeskFactory::getPackageDesksAssoc();
        
        PHPWS_Core::initModClass('hms', 'PackageDeskView.php');
        $view = new PackageDeskView($desks);
        
        $context->setContent($view->show());
    }
    
}

