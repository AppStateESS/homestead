<?php

namespace Homestead;

class EditRlcView extends View {

    public function show(){

        $tpl = array();

        // Check permissions
        if(UserStatus::isAdmin()){
            if(\Current_User::allow('hms', 'learning_community_maintenance')){
                $addCmd = CommandFactory::getCommand('ShowAddRlc');
                $tpl['ADD_URI'] = $addCmd->getURI();

                $invitesCmd = CommandFactory::getCommand('ShowSendRlcInvites');
                $tpl['SEND_INVITES_URI'] = $invitesCmd->getURI();
            }

            if(\Current_User::allow('hms', 'view_rlc_applications')){
                $applicationsCmd = CommandFactory::getCommand('ShowAssignRlcApplicants');
                $tpl['APPLICATIONS_URI'] = $applicationsCmd->getUri();

                $deniedAppsCmd = CommandFactory::getCommand('ShowDeniedRlcApplicants');
                $tpl['DENIED_APPS_URI'] = $deniedAppsCmd->getUri();
            }

            if(\Current_User::allow('hms' ,'view_rlc_members')){
                $allMembersCmd = CommandFactory::getCommand('ViewRlcAssignments');
                $tpl['ALL_MEMBERS_URI'] = $allMembersCmd->getUri();
            }

            if(\Current_User::allow('hms', 'email_rlc_rejections')){
                // Using JSConfirm, ask user if the _really_ want to send the emails
                $onConfirmCmd = CommandFactory::getCommand('SendRlcRejectionEmails');
                $tpl['SEND_REJECTS_URI'] = $onConfirmCmd->getUri();
            }
        }

        // JS Bundles for Room Assignment List
        $tpl['vendor_bundle'] = AssetResolver::resolveJsPath('assets.json', 'vendor');
        $tpl['entry_bundle'] = AssetResolver::resolveJsPath('assets.json', 'rlcCardList');

        return \PHPWS_Template::process($tpl, 'hms', 'admin/rlcList.tpl');
    }
}
