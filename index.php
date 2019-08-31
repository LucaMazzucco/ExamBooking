<?php

require_once('php_functions.php');

check_time_inactivity();
setcookie('testcookie', 'testcookie', time()+3600);

if(!isset($_SESSION['refresh']))
    header("Refresh:0");
$_SESSION['refresh'] = 'yes';

$message = '';
?>





<?php
    global $color2;
if(isset($_GET['action']) && $_GET['action'] == 'release' && !empty($release)){

    if(!u_Log_in()) {
        $_SESSION['message'] = "<p class='message error'>Session expired. Log in again to perform the operation.</p>";
    }
    header("Location: index.php");
    exit;
}

if(isset($_GET['book']) ){

    if(!u_Log_in()) {
        $_SESSION['message'] = "<p class='message error'>Session expired. Log in again to perform the operation.</p>";
    }
    else{

        $start = $_GET['starta'];
        $dest = $_GET['desta'];
        $quant = $_GET['quant'];
        $message = get_email().", Path choosed from: ".$start." to ".$dest." for ".$quant." people"."\n";
        $result = enter_book(get_email(), $start, $dest, $quant);
        if($result ==0)
            $message = $message."\nTrip succesfully booked!\n";


        if( $result == -1)
            $message = $message."Error while booking!\n";
        if( $result == -2)
            $message = "You have already a book!\n";
        if( $result == -3)
            $message = "Shuttle capacity overpassed!\n";
        if( $result == -4)
            $message = "Wrong addresses!\n";
        if( $result == -5)
            $message = "Wrong quantity!\n";

    }
}

if(isset($_GET['book_delete']) ) {

    if (!u_Log_in()) {
        $_SESSION['message'] = "<p class='message error'>Session expired. Log in again to perform the operation.</p>";
    } else {
        delete_book(get_email());
    }


}

require_once('header.php');
?>

<div id="container">
    <div id="bid">
        <table cellspacing="20">
            <?php if(u_Log_in())
                echo "<tr><td><p>$message</p></td>  </tr>"?>
            <tr><td><h2>Shuttle path planning<h2></h2></td></tr>




            <?php if(!u_Log_in() && isset($_GET['booking'])){
                echo "<p class='message error' style='color: #cc0000'>Session expired. Log in again to perform the operation.</p>";
            }?>
            <?php if(!u_Log_in() && isset($_GET['bookdelete'])){
                echo "<p class='message error' style='color: #cc0000'>Session expired. Log in again to perform the operation.</p>";
            }?>
        </table>
        <table cellspacing="20">

            <?php
            global $color2;
            echo "  <tr><td>FROM</td><td>TO</td><td>ON BOARD</td><td>"; if(u_Log_in()) echo "BOOKERS</td></tr>";

            $locations = get_location_list();
            $onboard = calc_on_board();
            $i=0;
            $size = count($locations)-1;
            for($i=0; $i<$size; $i++){
                $c = $i+1;
                $bookers = geet_bookers($locations[$i]);
                $bsize = count($bookers);
                $b = get_book();
                $color = '#cc0000';
                if($locations[$i] == $b[1])
                    $colorS = '#cc0000';
                else
                    $colorS = '#000000';

                if($locations[$c] == $b[2])
                    $colorD = '#cc0000';
                else
                    $colorD = '#000000';
                echo "  <tr><td><p class='message error' style='color: $colorS'>" . $locations[$i] . "</td><td><p class='message error' style='color: $colorD'>" . $locations[$c] . "</td><td>" .$onboard[$i] . "</td><td>"; if(u_Log_in()) echo  $bookers . "</td></tr>";
            }




           // echo "  <tr><td>TOTAL BOOKED</td><td></td><td></td><td></td><td>" . "3" . "</td></tr>";

            ?>
            <?php
                if(!is_booked(get_email()) && u_Log_in()) {
                    echo"";?>
                    <tr ><td colspan='5' >
            <form id = 'book' method = 'get' action = 'index.php' >
                <table border = '1' >
                    <tr ><td >From <input type = 'text' id = 'starta' name = 'starta'/></td ><td >To <input type = 'text' id = 'desta' name = 'desta'/></td><td>Quantity  <input type='number' min = "1" step='1' id='quant' name='quant'/></td>
                    <td ><input type = 'submit' id = 'book' name = 'book' value = 'Book now' /></td ></tr >

                    <tr><td> <select id = "prevstart" name="prevstart" onchange="setstartaddr();">
                    <?php
                    $locs = get_location_list();
                    echo "<option value=\"dest\">-</option>";
                    foreach($locs as $loc){
                              echo "<option value=\"start\">$loc</option>";
                     }
                              echo "</select> </td>";?>


                    <td> <select id = "prevdest" name="prevdest" onchange="setdestaddr();">
                    <?php
                    $locs = get_location_list();
                    echo "<option value=\"dest\">-</option>";
                    foreach($locs as $loc){
                              echo "<option value=\"dest\">$loc</option>";
                     }
                                echo "</select> </td>



                </tr>
                </table >
            </form >
                </td ></tr >
            ";}
            else {if(u_Log_in())
                    echo"<tr ><td >
                <form id = 'book_delete' method = 'get' action = 'index.php' >
                <input type = 'submit' id = 'book_delete' name = 'book_delete' value = 'Delete book' /></form></td></tr>
                ";
                }
            ?>
            
        </table><br><br><br><br>
    </div>


</div>


<?php
include('footer.php');
?>
<script type="text/javascript">
    function setstartaddr(){
            var e = document.getElementById("prevstart");
            var strUser = e.options[e.selectedIndex].text;

            document.getElementById("starta").value = strUser;

    }
    function setdestaddr(){

        var e = document.getElementById("prevdest");
        var strUser = e.options[e.selectedIndex].text;

        document.getElementById("desta").value = strUser;
    }
</script>
