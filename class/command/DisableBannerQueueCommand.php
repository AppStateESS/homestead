<?php

class DisableBannerQueueCommand extends Command {
    private $term;

    function setTerm($term) {
        $this->term = $term;
    }

    function getRequestVars() {
        $vars = array('action' => 'DisableBannerQueue');

        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }

        return $vars;
    }

    function execute(CommandContext $context) {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'banner_queue')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to enable/disable the Banner queue.');
        }

        if(is_null($this->term)) {
            $this->term = $context->get('term');
        }

        $term = $this->term;

        if(is_null($term)) {
            throw new InvalidArgumentException('No term was specified to DisableBannerQueue');
        }

        $term = new Term($term);

        if($term->getQueueCount() > 0) {
            NQ::Simple('hms', HMS_NOTIFICATION_ERROR, 'You must process the Banner Queue before it can be disabled.');
        } else {
            $term->setBannerQueue(FALSE);
            $term->save();
            NQ::Simple('hms', HMS_NOTIFICATION_SUCCESS, 'Banner Queue has been disabled for ' . Term::toString($term->term) . '.');
        }

        CommandContext::goBack();
    }
}

?>