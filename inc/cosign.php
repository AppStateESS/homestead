<?php

/**
 * Cosign authorization
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class cosign_authorization extends User_Authorization {
    public $create_new_user = false;
    public $show_login_form = false;

    // Enter the url to the cosign login page
    public $login_url       = 'https://weblogin.appstate.edu/cosign-bin/cosign.cgi';
    public $login_label     = 'Cosign log in';
    public $force_redirect  =  false;
    public $always_verify   = true;

    // Link to the cosign logout
    public $logout_link     = '';

    public function authenticate()
    {
        return $_SERVER['REMOTE_USER'] == $this->user->username;
    }

    public function forceLogin()
    {
        if (!isset($_SERVER['REMOTE_USER'])) {
            return;
        }

        Current_User::loginUser($_SERVER['REMOTE_USER']);
    }
   
    public function verify()
    {

        if (!isset($_SERVER['REMOTE_USER'])) {
            return false;
        }
        $this->user->_logged = 1;
        //        $result = $this->user->username == $_SERVER['REMOTE_USER'];
        $result = isset($_SERVER['REMOTE_USER']);
        if ($result) {
            if (!$this->user->id) {
                $db = new PHPWS_DB('users');
                $db->addWhere('username', $_SERVER['REMOTE_USER']);

                $user_result = $db->select('row');
                if (!$user_result) {
                    $db = new PHPWS_DB('users');
                    $db->addWhere('username', HMS_STUDENT_USER);
                    $db->loadObject($this->user);
                    HMS_Login::student_login($_SERVER['REMOTE_USER']);
                } else {
                    PHPWS_Core::plugObject($this->user, $user_result);
                    $this->user->loadUserGroups();
                    $this->user->loadPermissions();
                }
            }
        }

        return $result;
    }

    // Run before a new user is created.
    public function createUser(){}

    public function logout(){
        setcookie('cosign-hms.ess.appstate.edu','');
        PHPWS_Core::killAllSessions();
        $this->user->_logged = 0;
        PHPWS_Core::reroute('https://weblogin.appstate.edu/cosign-bin/logout');
        exit;
    }
}
?>
