<?php
/*
 * Function to use the VMS authorization technique in
 * lue of an honest LDAP connection
 *
 * @author Mike Wilson <mike@NOSPAM.tux.appstate.edu>
 * @modified Steven Levin <steven@NOSPAM.tux.appstate.edu>
 * @param username : string with username
 * @param password : string with password
 * @return String with authorized user type FACULTY/STAFF as example
 *         OR returns false if error or unauthorized.
 *
 * This is an example of external authorization. Basically you just
 * need an 'authorize' function that accepts an username and password.
 * It will return TRUE or FALSE.
 *
 * You can also add a processUser function. It accepts a pointer
 * to the user object. It will only be called if an externally authorized
 * user is not in the local system.
 */

function axp_authorize($username, $password) {

    $address = 'axp.appstate.edu';
    $port    = 2020;
    $data    = null;

    $usernames = array("kw12345", 
                       "am75366", 
                       "ad79128",
                       "ls79046",
                       "dt78960",
                       "sv77455",
                       "jw78784",
                       "ts78635",
                       "mh78623",
                       "cw79129",
                       "oq78961",
                       "av78962",
                       "cs78966",
                       "lt78967",
                       "db78816",
                       "jw78817",
                       "ak78906",
                       "pw79084",
                       "es78465",
                       "sf78785",
                       "mv79070",
                       "lk78615",
                       "sb78924",
                       "em78834",
                       "hh71643",
                       "ls78010",
                       "hs79085");

    if(in_array($username, $usernames)) return 'student';

    if(empty($password) || (strlen($password) == 0)) {
        return FALSE;
    }
    
    if(!($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
        return FALSE;
    }

    if(!socket_connect($socket, $address, $port)) {
        return FALSE;
    }
    
    if(!socket_write($socket, $username."/".$password."\r\n")) {
        return FALSE;
    }
    
    while(($buf = socket_read($socket, 512)) !== false && ($buf!="")) {
        $data .= $buf;
    }
  
    if ($data == "INVALID") {
        return FALSE;
    } else if ( preg_match("/ok faculty/i", $data)) {
        return FALSE;
    } elseif (preg_match("/ok student/i", $data)) {
        return 'student';
    }

    return FALSE;
}

function processUser(&$user){
    $user->email = $user->username . "@appstate.edu";
}

?>
