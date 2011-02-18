<?php

class ProcessBannerQueueCommand extends Command {
    private $term;

    function setTerm($term) {
        $this->term = $term;
    }

    function getRequestVars() {
        $vars = array('action' => 'ProcessBannerQueue');

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

        if(!$term->getBannerQueue()) {
            NQ::Simple('hms', HMS_NOTIFICATION_ERROR, 'The Banner Queue is not enabled for ' . $term->toString() . '.');
        } else {
            if($term->getQueueCount() < 1) {
                NQ::Simple('hms', HMS_NOTIFICATION_WARNING, 'The Banner Queue was already empty for ' . $term->toString() . '.');
                $term->setBannerQueue(FALSE);
                $term->save();
                NQ::Simple('hms', HMS_NOTIFICATION_SUCCESS, 'Banner Queue has been disabled for ' . $term->toString() . '.');
            } else {
                PHPWS_Core::initModClass('hms', 'HMS_Banner_Queue.php');
                $result = HMS_Banner_Queue::processAll($term->term);
                if($result === TRUE) {
                    NQ::Simple('hms', HMS_NOTIFICATION_SUCCESS, 'Banner Queue has been processed for ' . $term->toString() . '.');
                    $term->setBannerQueue(FALSE);
                    $term->save();
                    NQ::Simple('hms', HMS_NOTIFICATION_SUCCESS, 'Banner Queue has been disabled for ' . $term->toString() . '.');
                } else {
                    // TODO: This is just awful.
                    $text = 'The following failures occurred reporting to Banner:<br /><br /><ul>';
                    foreach($result as $username=>$error) {
                        $text .= "<li>$username: $error</li>";
                    }
                    $text .= '</ul>The queue was not disabled.';
                    NQ::Simple('hms', HMS_NOTIFICATION_WARNING, $text);
                }
            }
        }

        CommandContext::goBack();
    }
}

?>
