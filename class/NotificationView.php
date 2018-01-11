<?php

namespace Homestead;

\PHPWS_Core::initModClass('notification', 'NQ.php');

class NotificationView
{
    // Notification type constants
    const ERROR     = 9;
    const WARNING   = 8;
    const SUCCESS   = 7;
    const INFO      = 6;

    private $notifications = array();

    public function popNotifications()
    {
        $this->notifications = \NQ::popAll('hms');
    }

    public function show()
    {
        if(empty($this->notifications)) {
            return '';
        }

        $tpl = array();
        $tpl['NOTIFICATIONS'] = array();

        foreach($this->notifications as $notification) {

            if(!$notification instanceof \Notification) {
                throw new \InvalidArgumentException('Something was pushed onto the NQ that was not a Notification.');
            }
            $type = self::resolveType($notification);
            $tpl['NOTIFICATIONS'][][$type] = $notification->toString();
        }

        return \PHPWS_Template::process($tpl, 'hms', 'NotificationView.tpl');
    }

    protected function resolveType(\Notification $notification)
    {
        switch($notification->getType()) {
            case NotificationView::ERROR:
                return 'ERROR';
            case NotificationView::WARNING:
                return 'WARNING';
            case NotificationView::SUCCESS:
                return 'SUCCESS';
            case NotificationView::INFO:
                return 'INFO';
            default:
                return 'UNKNOWN';
        }
    }
}
