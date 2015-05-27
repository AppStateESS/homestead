#!/usr/bin/php
<?php
/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
//completion table don't forget
define('NUMBER_OF_STUDENTS', 20);

define('NICKNAME_CHANCE', 15); // out of 100
define('INTERNATIONAL_CHANCE', 3);
define('GRADUATE_CHANCE', 2);
define('HONORS_CHANCE', 4);
define('TEACHING_CHANCE', 3);
define('WATAUGA_CHANCE', 3);
define('GREEK_CHANCE', 2);
define('WAIVER_CHANCE', 1);

// The sum of types should be 100
define('TYPE_FRESHMEN_CHANCE', 46);
define('TYPE_TRANSFER_CHANCE', 6);
define('TYPE_CONTINUING_CHANCE', 43);
define('TYPE_RETURNING_CHANCE', 1);
define('TYPE_READMIT_CHANCE', 1);
define('TYPE_WITHDRAWN_CHANCE', 1);
define('TYPE_NONDEGREE_CHANCE', 1);
define('TYPE_GRADUATE_CHANCE', 1);

define('TABLE_NAME', 'fake_soap');

process($_SERVER['argv']);
echo "\n";

function process($arguments)
{
    $dump_data = false;
    $term = get_term_default();

    array_shift($arguments);
    if (!isset($arguments[0])) {
        $arguments[0] = '-h';
    }
    for ($i = 0; $i < count($arguments); $i++) {
        if ($arguments[$i] == '-h') {
            print_help();
            exit;
        } elseif ($arguments[$i] == '-f') {
            $i++;
            if (!isset($arguments[$i])) {
                exit("Configuration file not included.\n");
            }
            $file_directory = $arguments[$i];
        } elseif ($arguments[$i] == '-n') {
            $i++;
            if (!isset($arguments[$i])) {
                exit("Student rows not included.\n");
            }
            $number_of_students = $arguments[$i];
        } elseif ($arguments[$i] == '-x') {
            $dump_data = true;
        } elseif ($arguments[$i] == '-t') {
            $i++;
            if (!isset($arguments[$i])) {
                exit("Term is missing.\n");
            }
            $term = $arguments[$i];
        } else {
            exit("Unknown command\n");
        }
    }

    if (empty($number_of_students)) {
        $number_of_students = NUMBER_OF_STUDENTS;
    }

    include_database_file($file_directory);
    if ($dump_data) {
        $response = readline("Are you sure you want to reset ALL your student tables? (y/N):");
        if ($response == 'y') {
            reset_tables();
        } else {
            echo "Ending script.\n\n";
            exit;
        }
        echo "\nReset complete.\n------------------------------------\n\n";
    }
    build_table();
    echo TABLE_NAME . " table created.\n";
    echo "------------------------------------\n\n";
    echo "Creating $number_of_students students.\n\n";
    insert_rows($number_of_students, $term);
    echo "------------------------------------\nStudent creation complete.\n\n";

    echo "Make sure your inc/hms_defines.php file contains the following setting:
define('SOAP_OVERRIDE_FILE', 'FakeSoapTable.php');\n";
}

function reset_tables()
{
    $pdo = get_connection();
    echo 'Dropping ' . TABLE_NAME . " table.\n";
    $pdo->exec('drop table if exists ' . TABLE_NAME);
    echo "Flushing autocompletion.\n";
    $pdo->exec('truncate hms_student_autocomplete');

    echo "Flushing student cache.\n";
    $pdo->exec('truncate hms_student_cache');
    echo "Flushing student phone cache.\n";
    $pdo->exec('truncate hms_student_phone_cache');
    echo "Flushing student address cache.\n";
    $pdo->exec('truncate hms_student_address_cache');

    echo "Flushing student profiles.\n";
    $pdo->exec('truncate hms_student_profiles');

    echo "Flushing activity log.\n";
    $pdo->exec('truncate hms_activity_log');

    echo "Flushing assignments.\n";
    $pdo->exec('truncate hms_assignment');
    echo "Flushing temporary assignments.\n";
    $pdo->exec('truncate hms_temp_assignment');
    echo "Flushing assignment history.\n";
    $pdo->exec('truncate hms_assignment_history');
    echo "Flushing assignment queue.\n";
    $pdo->exec('truncate hms_assignment_queue');
    echo "Flushing learning community assignments and applications.\n";
    $pdo->exec('truncate hms_learning_community_applications, hms_learning_community_assignment');

    echo "Flushing lottery reservations.\n";
    $pdo->exec('truncate hms_lottery_reservation');

    echo "Flushing applications.\n";
    $pdo->exec('truncate hms_new_application, hms_fall_application, hms_spring_application, hms_summer_application, hms_lottery_application, hms_waitlist_application');

    echo "Flushing packages.\n";
    $pdo->exec('truncate hms_package');

    echo "Flushing room change requests.\n";
    $pdo->exec('truncate hms_room_change_participant,hms_room_change_participant_state,hms_room_change_request, hms_room_change_request_state');

    echo "Flushing roommate requests.\n";
    $pdo->exec('truncate hms_roommate');
    echo "Flushing special assignments.\n";
    $pdo->exec('truncate hms_special_assignment');
}

function build_table()
{
    $pdo = get_connection();
    try {
        $result = $pdo->query('select * from ' . TABLE_NAME);
    } catch (\PDOException $e) {
        // postgresql and mysql "missing table" error codes
        if ($e->getCode() == '42P01' || $e->getCode() == '42S02') {
            echo TABLE_NAME . " table not found. Creating new.\n";
            create_new_table($pdo);
        } else {
            echo $e->getMessage() . "\n";
            exit;
        }
    }
}

function create_new_table($pdo)
{
    $table_name = TABLE_NAME;
    $query = <<<EOF
CREATE TABLE $table_name (
    banner_id varchar(20), 
    username varchar(20),
    first_name varchar(30),
    middle_name varchar(30),
    last_name varchar(30),
    pref_name varchar(30),
    dob varchar(15),
    gender char(1),
    international smallint,
    student_level char(1),
    honors smallint,
    teaching_fellow smallint,
    watauga_member smallint,
    greek char(1),
    housing_waiver smallint,
    student_type char(1),
    application_term int,
    projected_class char(2),
    credhrs_completed smallint,
    credhrs_for_term smallint,
    address text,
    phone text
);
EOF;
    $pdo->exec($query);
}

function get_connection()
{
    static $pdo;
    if (empty($pdo)) {
        echo get_dsn();
        echo "\n";
        echo get_username();
        echo "\n";
        echo get_password();
        echo "\n";
        $pdo = new PDO(get_dsn(), get_username(), get_password());
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

function get_dsn()
{
    static $dsn_string;

    if (empty($dsn_string)) {
        $dsn_array = dsn_array();
        extract($dsn_array);
        $dsn_string = "$dbtype:dbname=$dbname";
        if (!empty($dbhost)) {
            $dsn_string .= ";host=$dbhost";
        }
        if (!empty($dbport)) {
            $dsn_string .= ";port=$dbport";
        }
    }
    return $dsn_string;
}

function get_username()
{
    $dsn_array = dsn_array();
    return $dsn_array['dbuser'];
}

function get_password()
{
    $dsn_array = dsn_array();
    return $dsn_array['dbpass'];
}

function dsn_array()
{
    static $dsn_array = null;

    if (!empty($dsn_array)) {
        return $dsn_array;
    }

    $dsn = PHPWS_DSN;
    $first_colon = strpos($dsn, ':');
    $second_colon = strpos($dsn, ':', $first_colon + 1);
    $third_colon = strpos($dsn, ':', $second_colon + 1);
    $at_sign = strpos($dsn, '@');
    $first_slash = strpos($dsn, '/');
    $second_slash = strpos($dsn, '/', $first_slash + 1);
    $third_slash = strpos($dsn, '/', $second_slash + 1);

    $dbtype = substr($dsn, 0, $first_colon);
    $dbuser = substr($dsn, $second_slash + 1, $second_colon - $second_slash - 1);
    $dbpass = substr($dsn, $second_colon + 1, $at_sign - $second_colon - 1);
    if ($third_colon) {
        $dbhost = substr($dsn, $at_sign + 1, $third_colon - $at_sign - 1);
    } else {
        $dbhost = substr($dsn, $at_sign + 1, $third_slash - $at_sign - 1);
    }

    $dbname = substr($dsn, $third_slash + 1);

    if ($third_colon) {
        $dbport = substr($dsn, $third_colon + 1, $third_slash - $third_colon - 1);
    } else {
        $dbport = null;
    }

    if ($dbtype == 'mysqli') {
        $dbtype = 'mysql';
    }

    $dsn_array = array('dbname' => $dbname, 'dbtype' => $dbtype, 'dbuser' => $dbuser, 'dbpass' => $dbpass, 'dbhost' => $dbhost, 'dbport' => $dbport);
    return $dsn_array;
}

function include_database_file($file_directory)
{
    if (!is_file($file_directory)) {
        exit("Configuration file not found: $file_directory\n");
    }
    require_once $file_directory;
    if (!defined('PHPWS_DSN')) {
        exit("DSN not found\n");
    }
}

function get_term_default()
{
    return strftime('%Y', time()) . '40';
}

function print_help()
{
    $student_default = NUMBER_OF_STUDENTS;
    $term_default = get_term_default();
    echo <<<EOF
Creates a table for the fake_soap script.
    
Usage: createfakestudents.php -f directory/to/phpwebsite/config/file
       createfakestudents.php -f directory/to/phpwebsite/config/file -n number-of-students
    
Commands:
-f      Path to phpWebSite installation's database configuration file.
-n      Number of student records to create. Records are cumulative per script run.
        Defaults to $student_default students.
-t      Term assigned to all students.
        Format: YYYYTT
        Terms:
            - 10 Spring
            - 20 Summer 1
            - 30 Summer 2
            - 40 Fall
        Example for Spring 2012: 201210
        Defaults to the Fall semester of the current year: $term_default
-x      Drop the SOAP table and flush the autocomplete table.
\n
EOF;
}

function insert_rows($number_of_students, $term)
{
    $db = get_connection();

    for ($i = 0; $i < $number_of_students; $i++) {
        $row = get_row($term);
        $query = create_soap_query($row);
        $db->exec($query);
        if (!empty($row['pref_name']) && $row['first_name'] != $row['pref_name']) {
            $nickname = '"' . $row['pref_name'] . '" ';
        } else {
            $nickname = null;
        }
        $first_name = $row['first_name'];
        $last_name = $row['last_name'];
        $middle_name = $row['middle_name'];
        $banner_id = $row['banner_id'];
        $username = $row['username'];
        echo <<<EOF
$first_name $nickname$middle_name $last_name - $username - $banner_id - $term\n
EOF;
        $ac_query = create_autocomplete_query($row);
        $db->exec($ac_query);
    }
}

function create_soap_query($row)
{
    $db = get_connection();

    foreach ($row as $key => $value) {
        if (is_array($value)) {
            $value = serialize($value);
        }

        $columns[] = $key;
        $values[] = $db->quote($value);
    }
    $query = 'insert into ' . TABLE_NAME . ' (' . implode(',', $columns) . ') values (' . implode(',', $values) . ');';
    return $query;
}

function create_autocomplete_query($row)
{
    extract($row);

    $lfirst = strtolower($first_name);
    $lmiddle = strtolower($middle_name);
    $llast = strtolower($last_name);
    $start_term = '201340';

    $query = <<<EOF
insert into hms_student_autocomplete (
    banner_id,
    username,
    first_name,
    middle_name,
    last_name,
    first_name_lower,
    middle_name_lower,
    last_name_lower,
    first_name_meta,
    middle_name_meta,
    last_name_meta,
    start_term,
    end_term
) values (
    '$banner_id',
    '$username',
    '$first_name',
    '$middle_name',
    '$last_name',
    '$lfirst',
    '$lmiddle',
    '$llast',
    METAPHONE('$first_name', 4),
    METAPHONE('$middle_name', 4),
    METAPHONE('$last_name', 4),
    '$start_term',
    NULL
);
EOF;
    return $query;
}

function get_row($application_term)
{
    $first_name = first_name();
    $middle_name = middle_name($first_name);
    $last_name = last_name();
    $student_level = bool_chance(GRADUATE_CHANCE, 'G', 'U');
    $student_type = student_type($student_level);
    $projected_class = projected_class($student_type);

    $row = array(
        'banner_id' => banner_id(),
        'username' => username($last_name, $first_name),
        'first_name' => $first_name,
        'middle_name' => $middle_name,
        'last_name' => $last_name,
        'pref_name' => pref_name($first_name),
        'dob' => dob(),
        'gender' => mt_rand(0, 1) ? 'M' : 'F',
        'international' => bool_chance(INTERNATIONAL_CHANCE),
        'student_level' => $student_level,
        'honors' => bool_chance(HONORS_CHANCE),
        'teaching_fellow' => bool_chance(TEACHING_CHANCE),
        'watauga_member' => bool_chance(WATAUGA_CHANCE),
        'greek' => bool_chance(GREEK_CHANCE, 'Y', 'N'),
        'housing_waiver' => bool_chance(WAIVER_CHANCE),
        'student_type' => $student_type,
        'application_term' => $application_term,
        'projected_class' => $projected_class,
        'credhrs_completed' => credit_hours($projected_class),
        'credhrs_for_term' => credit_for_term(),
        'address' => address_array(),
        'phone' => phone_array()
    );
    return $row;
}

function phone_array()
{
    $area = mt_rand(0, 1) ? '828' : '704';
    $number = mt_rand(1111111, 9999999);
    return array(array('area_code' => $area, 'number' => $number, 'ext' => ''));
}

function address()
{
    $box_number = mt_rand(100, 999);
    $road = road();
    $city = city();
    $state = 'NC';
    $zip = '28000';

    $add['line1'] = "$box_number $road";
    $add['city'] = $city;
    $add['county'] = '095'; // not figuring this yet
    $add['state'] = $state;
    $add['zip'] = $zip;

    return $add;
}

function address_array()
{
    $address[1] = address();
    $address[1]['atyp_code'] = 'PS';
    $address[2] = address();
    $address[2]['atyp_code'] = 'PR';
    $address[3]['atyp_code'] = 'AB';
    $address[3]['line1'] = 'ASU Box ' . mt_rand(1000, 40000);
    $address[3]['city'] = 'Boone';
    $address[3]['county'] = '095';
    $address[3]['state'] = 'NC';
    $address[3]['zip'] = '28608';
    return $address;
}

function city()
{
    $cities = array(
        'Boone',
        'Durham',
        'Raleigh',
        'Greensboro',
        'Charlotte',
        'Butner',
        'Fayetteville',
        'Asheville',
        'Asheboro',
        'Cary',
        'Wilmington',
        'Concord',
        'Gastonia',
        'Rocky Mount',
        'Chapel Hill',
        'Burlington',
        'Hickory',
        'Apex',
        'Carrboro'
    );
    $idx = mt_rand(0, count($cities) - 1);
    return $cities[$idx];
}

function road()
{
    $roads = array(
        'Elm',
        'Cactus',
        'Eagle',
        'Oak',
        'Grand',
        'Dale Earnhart',
        'Richard Petty',
        'Melrose',
        '1st',
        'River',
        'Mountain',
        'Chuck Norris',
        'Belview',
        'Corning',
        'Farmers',
        'Smith',
        'Canterbury',
        'Rhubarb',
        'Apple',
        'Orange Grove',
        'Trumpet',
        'Glory',
        'State',
        'Falwell',
        'Georgina',
        'Bayleaf',
        'Umstead',
        'Fairoaks',
        'Russell',
        'Bleak',
        'Redding',
        'Sharon',
        'Critcher',
        'Bamboo',
        'Hospital',
        'Greene',
        'Willowhaven'
    );
    $idx = mt_rand(0, count($roads) - 1);
    $road_name = $roads[$idx];

    $des = array(
        'Street',
        'Road',
        'Avenue',
        'Boulevard'
    );

    $idx = mt_rand(0, count($des) - 1);
    $road_suffix = $des[$idx];

    return "$road_name $road_suffix";
}

function credit_hours($projected_class)
{
    switch ($projected_class) {
        case 'FR':
            return 3 * mt_rand(0, 10);

        case 'SO':
            return 3 * mt_rand(11, 20);

        case 'JR':
            return 3 * mt_rand(21, 30);

        case 'SR':
            return 3 * mt_rand(31, 40);
    }
}

function credit_for_term()
{
    return mt_rand(3, 6) * 3;
}

function projected_class($student_type)
{
    if ($student_type == 'F') {
        return 'FR';
    }

    $pc = mt_rand(1, 100);

    if ($pc <= 60) {
        return 'SO';
    } elseif ($pc < 90) {
        return 'JR';
    } else {
        return 'SR';
    }
}

function student_type($student_level)
{
    if ($student_level == 'G') {
        return 'G';
    }

    $F = TYPE_FRESHMEN_CHANCE;
    $C = TYPE_CONTINUING_CHANCE;
    $T = TYPE_TRANSFER_CHANCE;
    $R = TYPE_RETURNING_CHANCE;
    $Z = TYPE_READMIT_CHANCE;
    $W = TYPE_WITHDRAWN_CHANCE;
    $N = TYPE_NONDEGREE_CHANCE;
    $G = TYPE_GRADUATE_CHANCE;

    $type = mt_rand(1, 100);

    if ($type <= $F) {
        return 'F';
    } elseif ($type <= ($F + $C)) {
        return 'C';
    } elseif ($type <= ($F + $C + $T)) {
        return 'T';
    } elseif ($type <= ($F + $C + $T + $R)) {
        return 'R';
    } elseif ($type <= ($F + $C + $T + $R + $Z)) {
        return 'Z';
    } elseif ($type <= ($F + $C + $T + $R + $Z + $W)) {
        return 'W';
    } elseif ($type <= ($F + $C + $T + $R + $Z + $W + $N)) {
        return 'N';
    } else {
        return 'G';
    }
}

function bool_chance($threshold, $positive = null, $negative = null)
{
    if (!isset($positive)) {
        $positive = 1;
        $negative = 0;
    }
    return mt_rand(1, 100) <= $threshold ? $positive : $negative;
}

function banner_id()
{
    static $user_ids = array();
    $id = '900' . mt_rand(100000, 999999);
    if (in_array($id, $user_ids)) {
        return banner_id();
    }
    $user_ids[] = $id;
    return $id;
}

// creates student username
function username($last_name, $first_name)
{
    static $all_usernames = array();
    $username = strtolower(substr($last_name, 0, 6) . substr($first_name, 0, 2));
    if (in_array($username, $all_usernames)) {
        return $username . mt_rand(1, 99);
    } else {
        return $username;
    }
}

function pref_name($first_name)
{
    $chance = mt_rand(1, 100);
    if ($chance <= NICKNAME_CHANCE) {
        return nickname($first_name);
    } elseif (mt_rand(1, 15) == 1) {
        // 1 out of 15 chance their first name was entered as preferred
        return $first_name;
    } else {
        return null;
    }
}

function middle_name($first_name)
{
    if (mt_rand(1, 100) == 1) {
        return 'Danger';
    } else {
        $middle_name = first_name();
        if ($middle_name == $first_name) {
            return middle_name($first_name);
        } else {
            return $middle_name;
        }
    }
}

function dob()
{
    $low_range = floor(17 * 86400 * 365.25);
    $high_range = floor(22 * 86400 * 365.25);
    $unix_dob = time() - mt_rand($low_range, $high_range);
    return strftime('%Y-%m-%d', $unix_dob);
}

/**
 * Returns one random first name. Names that are repeated are the most popular names in the US.
 * @staticvar array $first_names
 * @return string
 */
function first_name()
{
    static $first_names = array(
        'Abigail',
        'Abigail',
        'Adina',
        'Adriana',
        'Aiken',
        'Akilah',
        'Alexander',
        'Alexander',
        'Alberto',
        'Alfonzo',
        'Annalisa',
        'Annis',
        'Archer',
        'Arielle',
        'Arnold',
        'Ava',
        'Ava',
        'Ayanna',
        'Barb',
        'Barbie',
        'Bart',
        'Bort',
        'Belkis',
        'Bong',
        'Branden',
        'Brandie',
        'Bree',
        'Brian',
        'Bryon',
        'Carlotta',
        'Carolin',
        'Charlotte',
        'Charlotte',
        'Christy',
        'Cleopatra',
        'Corazon',
        'Crystal',
        'Damian',
        'Daniel',
        'Daniel',
        'Danielle',
        'Danille',
        'Davina',
        'Delmer',
        'Dominque',
        'Elina',
        'Emily',
        'Emily',
        'Emma',
        'Emma',
        'Emilio',
        'Errol',
        'Ethan',
        'Ethan',
        'Fermina',
        'Giselle',
        'Giuseppina',
        'Gregoria',
        'Herta',
        'Jose',
        'Homer',
        'Hwa',
        'Idell',
        'Irma',
        'Isabella',
        'Isabella',
        'Jacob',
        'Jacob',
        'Jame',
        'James',
        'James',
        'Jami',
        'Janean',
        'Janyce',
        'Jaqueline',
        'Jean-Claude',
        'Jen',
        'Jenni',
        'Jennifer',
        'Jeremy',
        'Jim',
        'Jodie',
        'John',
        'Jonathon',
        'Jona',
        'Kesha',
        'Kym',
        'Lacy',
        'Leigha',
        'Leonarda',
        'Liam',
        'Liam',
        'Linnie',
        'Lisa',
        'Lloyd',
        'Loreta',
        'Luella',
        'Luther',
        'Lynsey',
        'Madison',
        'Madison',
        'Marcela',
        'Marge',
        'Mariah',
        'Mariella',
        'Martine',
        'Mary',
        'Masako',
        'Mason',
        'Mason',
        'Matt',
        'Matthew',
        'Mattie',
        'Mia',
        'Mia',
        'Michael',
        'Michael',
        'Modesto',
        'Moses',
        'Natalie',
        'Nicolasa',
        'Noah',
        'Noah',
        'Olivia',
        'Olivia',
        'Otto',
        'Paulita',
        'Queenie',
        'Rachele',
        'Ralph',
        'Renaldo',
        'Richard',
        'Robert',
        'Roselia',
        'Roselyn',
        'Shaina',
        'Shamika',
        'Shanice',
        'Shirley',
        'Sophia',
        'Sophia',
        'Sierra',
        'Stephanie',
        'Summer',
        'Suzann',
        'Suzi',
        'Tarsha',
        'Ted',
        'Tena',
        'Theodore',
        'Todd',
        'Tomasa',
        'Trang',
        'Triet',
        'Trish',
        'Tyra',
        'Ulrike',
        'Ute',
        'Valdemare',
        'Wan',
        'Will',
        'William',
        'William',
        'Willodean',
        'Yi',
        'Zachariah');
    $idx = mt_rand(0, count($first_names) - 1);
    return $first_names[$idx];
}

/**
 * Returns a random last name. Repeats increase odds for most popular names to appear.
 * @staticvar array $last_names
 * @return string
 */
function last_name()
{
    static $last_names = array(
        'Acula',
        'Achenbach',
        'Ahlers',
        'Anderson',
        'Anderson',
        'Arguelles',
        'Bacon',
        'Balentine',
        'Bancroft',
        'Banks',
        'Batten',
        'Beauford',
        'Boehm',
        'Bosley',
        'Brazeal',
        'Brown',
        'Brown',
        'Brown',
        'Carr',
        'Clodfelter',
        'Clubb',
        'Colter',
        'Cowman',
        'Danz',
        'Dawdy',
        'Davis',
        'Davis',
        'Davis',
        'Denn',
        'Drozd',
        'Dumond',
        'Einstein',
        'Enderle',
        'Estep',
        'Evers',
        'Farrah',
        'Fazenbaker',
        'Feid',
        'Ferreira',
        'Flinchum',
        'Fountain',
        'Garcia',
        'Garcia',
        'Garn',
        'Gaskell',
        'Geesey',
        'Goatley',
        'Gonsalez',
        'Gooslin',
        'Graziani',
        'Greenhill',
        'Guyton',
        'Ha',
        'Hailey',
        'Heyne',
        'Holgate',
        'Holmer',
        'Hosea',
        'Hussey',
        'Jared',
        'Jefferson',
        'Johnson',
        'Johnson',
        'Johnson',
        'Johnson',
        'Jones',
        'Jones',
        'Jones',
        'Kettler',
        'Kruger',
        'Kung',
        'Lapointe',
        'Lapp',
        'Larabee',
        'Lincoln',
        'Locklear',
        'Lozoya',
        'Lucchesi',
        'Ludlow',
        'Madrigal',
        'Matter',
        'McCroskey',
        'McGarr',
        'McGuinness',
        'McMath',
        'McNair',
        'McQuire',
        'Mei',
        'Miller',
        'Miller',
        'Moore',
        'Moore',
        'Mullally',
        'Nava',
        'Neagle',
        'Nemec',
        'Nicols',
        'Niebuhr',
        'Oden',
        'Papazian',
        'Patnaude',
        'Phou',
        'Plasse',
        'Polston',
        'Pough',
        'Rodriguez',
        'Rodriguez',
        'Roland',
        'Russum',
        'Sauer',
        'Schick',
        'Sclafani',
        'Searcy',
        'Sells',
        'Shen',
        'Simpson',
        'Siers',
        'Smith',
        'Smith',
        'Smith',
        'Smith',
        'Sparano',
        'Stallone',
        'Sturtz',
        'Sugden',
        'Sumrall',
        'Schwarzenegger',
        'Swindall',
        'Taul',
        'Taylor',
        'Taylor',
        'Theroux',
        'Tiedeman',
        'Tolliver',
        'Tomes',
        'Va Damme',
        'Vento',
        'Vo',
        'Vorhese',
        'Waggoner',
        'Washington',
        'Waye',
        'Whiteford',
        'Whitton',
        'Williams',
        'Williams',
        'Williams',
        'Wilson',
        'Wilson',
        'Winford',
        'Yingling'
    );
    $idx = mt_rand(0, count($last_names) - 1);
    return $last_names[$idx];
}

function nickname($first_name)
{
    $shortened = array(
        'Abigail' => 'Abby',
        'Alexander' => 'Alex',
        'Arnold' => 'Arny',
        'James' => 'Jim',
        'Jennifer' => 'Jenni',
        'Jonathon' => 'Jonny',
        'John' => 'Johnny',
        'Madison' => 'Maddie',
        'Matthew' => 'Matt',
        'Michael' => 'Mike',
        'Richard' => 'Rick',
        'Robert' => 'Rob',
        'Theodore' => 'Ted',
        'William' => 'Billy'
    );

    if (isset($shortened[$first_name])) {
        return $shortened[$first_name];
    }
    // 30 character limit!
    $nicknames = array(
        'Stubby',
        'The Knife',
        'Corndog',
        'The Bard',
        'Dusty',
        'Rickster',
        'LL',
        'Trey',
        'Mr. Fabulous',
        'Pickles',
        'Cthulu',
        'Mork',
        'Fonzie',
        'K.I.T',
        'Animal',
        'Hawk',
        'Heisenberg',
        'Lucky',
        'Peaches',
        'Checkers',
        'Boo Boo',
        'TCB',
        'Elvis',
        'Hayzeus',
        'Achoo',
        'X',
        'Dimples',
        'Knuckles',
        'Flip',
        'Wildone',
        'Gorgeous',
        'Giggles',
        'Lambchop',
        'Chuck',
        'MauMau',
        'Pinky',
        'Yahweh',
        'Snoopy',
        'Puddin',
        'Ducky',
        'Proper Noun',
        'Dynamite',
        'Shorty',
        'Biggy',
        'Average',
        'Duke',
        'Bliss',
        'Gipper',
        'He-Man',
        'Full Auto',
        'Baby',
        'Magnus',
        'Arny',
        'Godrats',
        'Fortuna',
        'T-Bone',
        'Gunny',
        'Jellybean',
        'Peanut',
        'Sloopy',
        'The Cable Guy',
        'Sarge',
        'Salty Dog',
        'Pumpkinhead'
    );
    $idx = mt_rand(0, count($nicknames) - 1);
    return $nicknames[$idx];
}
