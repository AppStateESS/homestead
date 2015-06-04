<?php

PHPWS_Core::initModClass('hms', 'RlcApplicationFactory.php');

class AdminAddRlcMembersCommand extends Command {

    private $community;

    public function getRequestVars()
    {
        return array(
                'action' => 'AdminAddRlcMembers',
                'communityId' => $this->community->getId()
        );
    }

    public function setCommunity($community)
    {
        $this->community = $community;
    }

    public function execute(CommandContext $context)
    {
        if (!Current_User::allow('hms', 'add_rlc_members')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view RLC members.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
        PHPWS_Core::initModClass('hms', 'RlcApplicationFactory.php');
        PHPWS_Core::initModClass('hms', 'RlcMembershipFactory.php');

        // Get the selected term
        $term = Term::getSelectedTerm();

        // Get the request community
        $communityId = $context->get('communityId');

        if (!isset($communityId) || $communityId == '') {
            throw new InvalidArgumentException('Missing community id.');
        }

        $community = new HMS_Learning_Community($communityId);

        // Get banner ID list and make sure it has content
        $bannerIds = $context->get('banner_id_list');

        if (!isset($bannerIds) || $bannerIds == '') {
            $errorCmd = CommandFactory::getCommand('ShowAdminAddRlcMember');
            $errorCmd->setCommunity($community);
            $errorCmd->redirect();
        }

        // Break up string into an array of individual ids
        $bannerIds = explode("\n", $bannerIds);

        foreach ($bannerIds as $banner) {
            // Clean up the banner id
            $banner = trim($banner);

            // Skip blank lines
            if($banner == '') {
                continue;
            }

            // Get the student
            try {
                $student = StudentFactory::getStudentByBannerId($banner, $term);
            } catch (StudentNotFoundException $e) {
                NQ::simple('hms', hms\NotificationView::ERROR, "Couldn't find a student with ID: {$e->getRequestedId()}");
                continue;
            } catch (InvalidArgumentException $e) {
                NQ::simple('hms', hms\NotificationView::ERROR, "This doesn't look like a banner ID: $banner");
                continue;
            }

            // Check for an existing housing application
            $housingApp = HousingApplicationFactory::getAppByStudent($student, $term);

            // If no housing app, show a warning
            if (is_null($housingApp)) {
                NQ::simple('hms', hms\NotificationView::ERROR, "No housing application found for: {$student->getName()}({$student->getBannerID()})");
                continue;
            }

            // Check for an existing learning community application
            $rlcApp = RlcApplicationFactory::getApplication($student, $term);

            if($rlcApp == null){
                // Create a new learning community application
                $rlcApp = new HMS_RLC_Application();
                $rlcApp->setUsername($student->getUsername());
                $rlcApp->setDateSubmitted(time());
                $rlcApp->setFirstChoice($community->getId());
                $rlcApp->setSecondChoice(null);
                $rlcApp->setThirdChoice(null);
                $rlcApp->setWhySpecificCommunities('Application created administratively.');
                $rlcApp->setStrengthsWeaknesses('');
                $rlcApp->setRLCQuestion0(null);
                $rlcApp->setRLCQuestion1(null);
                $rlcApp->setRLCQuestion2(null);
                $rlcApp->setEntryTerm($term);
                if($student->getType() == TYPE_CONTINUING){
                    $rlcApp->setApplicationType(RLC_APP_RETURNING);
                } else {
                    $rlcApp->setApplicationType(RLC_APP_FRESHMEN);
                }

                $rlcApp->save();

            } else {
                // RLC application already exists
                NQ::simple('hms', hms\NotificationView::WARNING, "RLC application already exists for {$student->getName()}({$student->getBannerID()})");
            }

            // Check for RLC membership
            $membership = RlcMembershipFactory::getMembership($student, $term);

            if($membership !== false){
                NQ::simple('hms', hms\NotificationView::ERROR, "RLC membership already exists for {$student->getName()}({$student->getBannerID()})");
                continue;
            }

            // Create RLC Membership
            $membership = new HMS_RLC_Assignment();
            $membership->rlc_id         = $community->getId();
            $membership->gender         = $student->getGender();
            $membership->assigned_by    = UserStatus::getUsername();
            $membership->application_id = $rlcApp->id;
            $membership->state          = 'new';

            $membership->save();
        }

        $successCmd = CommandFactory::getCommand('ShowViewByRlc');
        $successCmd->setRlcId($community->getId());
        $successCmd->redirect();
    }
}
