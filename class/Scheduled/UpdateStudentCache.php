<?php

class UpdateStudentCache {

    public static function cliExec()
    {
        \PHPWS_Core::initModClass('users', 'Users.php');
        \PHPWS_Core::initModClass('users', 'Current_User.php');

        \PHPWS_Core::initModClass('hms', 'PdoFactory.php');
        \PHPWS_Core::initModClass('hms', 'Term.php');
        \PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $userId = \PHPWS_DB::getOne("SELECT id FROM users WHERE username = 'jbooker'");

        $user = new \PHPWS_User($userId);

        // Uncomment for production on branches
        $user->auth_script = 'local.php';
        $user->auth_name = 'local';

        //$user->login();
        $user->setLogged(true);

        \Current_User::loadAuthorization($user);
        //\Current_User::init($user->id);
        $_SESSION['User'] = $user;

        $obj = new UpdateStudentCache();
        $obj->execute();
    }

    public function execute()
    {
        $db = PdoFactory::getPdoInstance();

        // Get the list of current and future terms
        $terms = Term::getFutureTerms();
        $terms[] = Term::getCurrentTerm();

        $terms = implode(', ', $terms);

        // Delete cache entries from those terms
        // NB: Inserting vars in the query is normally dangerous, but we trust the input data here.
        // The workaround/proper solution to getting PDO to properly use a list of integers isn't worth the effort here
        $query = "DELETE FROM hms_student_cache WHERE term IN ($terms)";
        $stmt = $db->prepare($query);
        $stmt->execute();


        // Get a unique list of banner IDs and terms for all applicants/assignments in
        // current and future terms
        $query = "SELECT DISTINCT banner_id, term FROM (SELECT banner_id, term FROM hms_new_application WHERE term IN ($terms) UNION SELECT banner_id, term FROM hms_assignment WHERE term IN ($terms)) AS foo";
        $stmt = $db->prepare($query);
        $stmt->execute();

        $results = $stmt->fetchAll();

        // Fetch every student, which will re-populate the local database cache table
        foreach($results as $result){
            StudentFactory::getStudentByBannerId($result['banner_id'], $result['term']);
        }

    }
}
