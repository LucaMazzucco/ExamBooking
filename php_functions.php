<?php



$shuttle_capacity = 4;

/* Database Variables*/
/*
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'shuttlebookings';
$port = 3306;
*/

$host = 'localhost';
$username = 's241968';
$password = 'ionierca';
$dbname = 's241968';
$port = 3306;

$table_users = "users";

$shuttle_bookings = "shuttle";

//define("seed", "xh8jq22f");



/*
 * general functions
 */
session_start();


function check_cookie(){
    setcookie('testcookie', 'testcookie');
    /*
    if(!isset($_SESSION['refresh']))
        header("Refresh:0");
    $_SESSION['refresh'] = 'yes';
    */
    //header("Refresh:0");
    if(isset($_COOKIE['testcookie'])&& $_COOKIE['testcookie'] == 'testcookie')
        return true;
    else
        return false;
}

function check_https()
{

    $use_sts = true;
    if ($use_sts && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        header('Strict-Transport-Security: max-age=31536000');
    } elseif ($use_sts) {
        header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
        die();
    }
}

function u_Log_in(){
    return isset($_SESSION['user_email']);
}

function create_session($username){

    $_SESSION['user_email'] = $username;

}

function get_email(){
    if(!u_Log_in()){
        return null;
    }
    return $_SESSION['user_email'];
}

function Logout(){

    cancel_session();
    //foreach ( $_COOKIE as $key => $value ) {
    //    delete_cookies($key);
    //}


}

function cancel_session() {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {

        $params = session_get_cookie_params();

        setcookie(session_name(), '', time() - 3600*24,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );

    }
    session_destroy();
}



function check_time_inactivity(){

    $now = time();
    $inactivity = 0;

    if (isset($_SESSION['time'])){
        $previous = $_SESSION['time'];
        $inactivity = ($now - $previous);
    }

    if ($inactivity > 120) {
        cancel_session();
    } else {
        $_SESSION['time'] = time();
    }
}

function delete_cookies($name) {

    setcookie($name, "", time() - 3600);

}
/*
 * SANITIZE FUNCTIONS
 */

function encrypt_password($string) {


    $string = md5($string);
    return $string;

}

function check_int($string) {
    return (is_numeric($string) && $string == intval($string));
}

function sanitize_string($string, $connection = null) {

    if($connection != null) {
        $connection->real_escape_string($string);
    }
    $string = htmlentities($string, ENT_QUOTES, "utf-8");
    $string = stripslashes($string);
    return $string;
}


/*
 * DB FUNCTIONS
 */


function new_connection(){

    global $host, $username, $password, $dbname;
    $connection = new mysqli($host,$username, $password, $dbname);
    if ($connection->connect_errno) {
        echo "Failed connecting to MySQL: (" . $connection->connect_errno . ") " . $connection->connect_error;
        die;
    }

    $connection->set_charset("utf8");
    
    return $connection;

}

function db_init(){

    global $table_users, $shuttle_bookings;

    $connection = new_connection();

    $query = "CREATE TABLE IF NOT EXISTS $table_users( 
                              email varchar(255),
                              password varchar(255),
                              PRIMARY KEY(email))
                              CHARACTER SET=utf8;";

    if($connection->query($query) === false){
        printf("Error creating table users");
        delete_connection($connection);
        return false;
    }



    $query = "CREATE TABLE IF NOT EXISTS $shuttle_bookings( 
                              email varchar(255),
                              startaddr varchar(255),
                              destaddr  varchar(255),
                              quantity INTEGER,
                              PRIMARY KEY(email))
                              CHARACTER SET=utf8;";

    if($connection->query($query) === false){
        printf("Error creating table shuttle");
        delete_connection($connection);
        return false;
    }


    delete_connection($connection);
    return true;
}

function delete_connection($connection){

    $connection->close();

}

function register_user($username, $pw) {

    global $table_users;

    if($username === "" || $pw === ""){
        return false;
    }
    try{
    $connection = new_connection();
    $user = sanitize_string($username, $connection);

    if($user === false){
        delete_connection($connection);
        return false;
    }

    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z0-9]).*$/', $pw)) {
        // I know for sure that there will be no password that does not respect this format, so i save one useless DB query
        return false;
    }


        $pw = encrypt_password($pw);

        $connection->autocommit(false);
        $transaction_started = true;
    $query = "INSERT INTO $table_users(email,password) VALUES ('$user','$pw')";

    if($connection->query($query) == false){
        delete_connection($connection);
        return false;
    }
        $connection->commit();
        $connection->autocommit(true);

        delete_connection($connection);
    return true;
    } catch (Exception $e) {
    $register_error = $e->getMessage();
    if ($transaction_started) {
        $connection->rollback();
        $connection->autocommit(true);
    }

}

}
function check_email_password($user_email, $pw){

    global $table_users;

    if($user_email === "" || $pw === ""){
        return false;
    }

    $connection = new_connection();

    $user = sanitize_string($user_email, $connection);

    if($user === false){
        delete_connection($connection);
        return false;
    }

    $pw = encrypt_password($pw);

    $query = "SELECT COUNT(*) FROM $table_users WHERE email = '$user' AND password = '$pw'";
    $result = $connection->query($query);
    if($result == false){
        delete_connection($connection);
        return false;
    }
    $count = $result->fetch_array(MYSQLI_NUM);
    $result->free();
    if($count[0] == 0){
        delete_connection($connection);
        return false;
    }else {
        delete_connection($connection);
        return true;
    }


}

function search_user($user_email) {

    global $table_users;

    if($user_email === ""){
        return false;
    }

    $connection = new_connection();
    
    $user = sanitize_string($user_email, $connection);

    if($user === false){
        delete_connection($connection);
        return false;
    }

    $query = "SELECT COUNT(*) FROM $table_users WHERE email = '$user'";
    $result = $connection->query($query);
    if($result === false){
        delete_connection($connection);
        return false;
    }
    $count = $result->fetch_array(MYSQLI_NUM);
    $result->free();
    if($count[0] == 0){
        delete_connection($connection);
        return false;
    }else {
        delete_connection($connection);
        return true;
    }
}




function get_book(){


    global $shuttle_bookings;
    $email = get_email();
    $connection = new_connection();
    $query = "SELECT email, startaddr, destaddr, quantity
              FROM $shuttle_bookings
              WHERE email = '$email'
             
             ";

    $result = $connection->query($query);
    if($result === false){
        printf("Error fetching users");
        delete_connection($connection);
        return -1;
    }else {
        $row = $result->fetch_array();
    }
    return $row;

}



function get_booktable(){


    global $shuttle_bookings;

    $connection = new_connection();
    $query = "SELECT email, startaddr, destaddr, quantity
              FROM $shuttle_bookings
             
             ";

    $result = $connection->query($query);
    if($result === false){
        printf("Error fetching users");
        delete_connection($connection);
        return -1;
    }else {
        $rows = array();
        while ($row = $result->fetch_array()) {
            $rows[] = $row;
        }
    }

    return $rows;

}

function get_location_list(){

    $rows = get_booktable();
    $location = array();

    foreach ($rows as $row) {

        array_push($location, $row[1], $row[2]);
    }

    $location = array_unique($location);
    sort($location);

    return $location;
}

function calc_on_board(){

    $booklist = get_booktable();
    $location = get_location_list();

    $onboard = array();
    $size = count($location);
    $i = 0;
    for($i = 0; $i<$size; $i++){
        array_push($onboard, 0);
    }
    $i = 0;
    foreach ($location as $loc) {

        foreach ($booklist as $book) {
            if($book[1] == $loc)
                $onboard[$i] += $book[3];
            elseif ($book[2] == $loc)
                $onboard[$i] -= $book[3];
        }
        $temp = $onboard[$i];
        $i++;
        $onboard[$i] = $temp;

    }
    return $onboard;



}


function calc_prew_on_board($startaddr, $destaddr, $quantity){

    global $shuttle_capacity;
    $flag=0;
    $on = 0;
    $onboard = array();
    $location = get_location_list();
    $booklist = get_booktable();
    $i=0;
    printf("prova");

    array_push($location, $startaddr, $destaddr);
    $location = array_unique($location);
    sort($location);

    foreach ($location as $loc) {

        foreach ($booklist as $book) {
            if($book[1] == $loc) {
                $onboard[$i] += $book[3];
                if($onboard[$i]>$shuttle_capacity)
                    $flag = 1;
            }
            elseif ($book[2] == $loc)
                $onboard[$i] -= $book[3];
        }
        if($startaddr == $loc) {
            $onboard[$i] += $quantity;
            if($onboard[$i]>$shuttle_capacity)
                $flag = 1;
        }
        elseif ($destaddr == $loc)
            $onboard[$i] -= $quantity;
        $temp = $onboard[$i];
        $i++;
        $onboard[$i] = $temp;

    }



    if($flag === 1)
        return false;
    else
        return true;


}


function geet_bookers($stop){

    $booklist = get_booktable();
    $location = get_location_list();

    $bookers = array();
    $empty = 0;
    $size = count($location);
    $i = 0;

    foreach ($location as $loc) {

        foreach ($booklist as $book) {
            if($book[1] == $loc) {
                $empty++;
                array_push($bookers, $book[0] );
                $temp =  " (" . $book[3] . ")";
                array_push($bookers, $temp);
            }
            elseif ($book[2] == $loc) {

                $index = array_search($book[0], $bookers);
                $empty--;
                if ($index !== FALSE) {
                    unset($bookers[$index]);
                    $in = $index+1;
                    unset($bookers[$in]);

                }
            }
        }
        if($stop == $loc) break;

    }
    if($empty == 0)
        array_push($bookers, "empty");
    $string = implode(', ', $bookers);
    return $string;

}


function is_booked($email) {

    global $shuttle_bookings;

    $connection = new_connection();


    $query = "SELECT COUNT(*) FROM $shuttle_bookings WHERE email = '$email'";

    $result = $connection->query($query);
    if($result === false){
        printf("Error calculating booked_total");
        delete_connection($connection);
        return -3;
    }
    $row = $result->fetch_row();
    if($row[0]>0) {
        return 1;
    }
    else {
        return 0;
    }
}


function enter_book($email, $startaddr, $destaddr, $quantity){


    global $shuttle_bookings;
    try{
    $connection = new_connection();
    $booked= is_booked($email);

        $connection->autocommit(false);
        $transaction_started = true;

    if($booked)
        return -2;

    $startaddr = sanitize_string($startaddr, $connection);
    if($startaddr == false)
        return -4;

    $destaddr = sanitize_string($destaddr, $connection);
    if($destaddr == false)
        return -4;

    if($startaddr>= $destaddr)
        return -4;
    if(!is_numeric($quantity))
        return -5;
    if($quantity <=0)
        return -5;
    if(!calc_prew_on_board($startaddr, $destaddr, $quantity))
        return -3;

    $query = "INSERT INTO $shuttle_bookings(email, startaddr, destaddr, quantity)
              VALUES ('$email', '$startaddr', '$destaddr', '$quantity')";

    if ($connection->query($query) === false) {
        printf("Error creating table shuttle_booking");
        delete_connection($connection);
        return -1;
    }
        $connection->commit();
        $connection->autocommit(true);


        return 0;
    } catch (Exception $e) {
        $register_error = $e->getMessage();
        if ($transaction_started)
            $connection->rollback();
            $connection->autocommit(true);
    }

}


function delete_book($email){

    global $shuttle_bookings;
    $connection = new_connection();

            $query = "DELETE FROM $shuttle_bookings
                WHERE email = '$email'
                ";

            $result = $connection->query($query);
            if ($result === false) {
                printf("Error booking email!");
                delete_connection($connection);
                return -1;
            }




    return 1;
}