<?php


require_once('php_functions.php');



setcookie('testcookie', 'testcookie', time()+3600);

if(!isset($_SESSION['refresh']))
    header("Refresh:0");
$_SESSION['refresh'] = 'yes';

if(isset($_POST["login"])) {

	if(isset($_COOKIE['continue_operation']) && $_COOKIE['continue_operation'] = "true") {
		$url = 'index.php?action=reserve';
		delete_cookies('continue_operation');
	}else{
		$url = 'index.php';
	}

    $login = check_email_password($_POST['username_l'], $_POST['password_l']);
    if($login){
        create_session($_POST['username_l']);
        header("Location: " . $url);
        exit;
    }
}

if(isset($_POST['register'])){
    $return = register_user($_POST['username_r'], $_POST['password_r']);
    if(!$return)
    echo "<p class='error'>Wrong password or mail, try again!</p>
                    <?php unset($login)";
}

if(!u_Log_in()){
    include('header.php');
    if(check_cookie()) {
        delete_cookies('test');
        ?>
        <!-- /*remember to take off https://localhost/AllBookingsSept/ from address*/ -->
        <div id="container">
            <div id="login_box">
                <h2>Log In</h2>
                <p class="message">Login to book now!</p>

                <form id="login_form" method="post" action="login.php">
                    <input type="email" placeholder="Email" id="username_l" name="username_l"/><br>
                    <input type="password" placeholder="Password" id="password_l" name="password_l"/><br><br>
                    <input type="submit" id="login" name="login" value="Log in"/>
                </form>
                <p>Aren't you registred?</p>
                <input type="button" id="register_show" name="register_show" value="Register now!" onclick="show_register();"/>
                <?php
                if (isset($login) && !$login) {
                    ?>
                    <p class="error">Wrong password or mail, try again!</p>
                    <?php unset($login);
                }
                if (isset($return) && $return) {
                    ?>
                    <p class="success">Successfully registered! Log in to enter a bid!</p>
                    <?php
                } elseif (isset($return) && !$return) {
                    ?>
                    <p class="error">Something went wrong, please try again!</p>
                    <?php
                    unset($return);
                }
                ?>

            </div>
            <div id="register_box" style="visibility: hidden">
                <h2>Aren't you registred yet? <br></h2>Register now!<br><br>
                <form id="register_form" method="post" action="login.php">
                    <input type="email" placeholder="Email" id="username_r" name="username_r"
                           onkeydown="checkemail();" onkeyup="checkemail();" onfocus="checkemail();" onchange="checkemail()"/><br>
                    <input type="password" placeholder="Password" id="password_r" name="password_r"
                           onkeydown="checkpass();" onkeyup="checkpass();" onfocus="checkpass();" onchange="checkpass()"/></br></br>
                    <div id="checkp" style="color: #cc0000"></div>

                    <input type="submit" name="register" id="register" value="Register" align="right"/>
                </form>
                <p>Already registered?</p>
                <input type="button" id="login_show" name="login_show" value="Login!" onclick="show_login();"/>

                <span id="form_error1_r"></span>
                <span id="form_error2_r"></span>
                <?php
                if (isset($return) && $return) {
                    ?>
                    <p class="success">Successfully registered! Log in to reserve your trip!</p>
                    <?php
                } elseif (isset($return) && !$return) {
                    ?>
                    <p class="error">Something went wrong, please try again!</p>
                    <?php unset($return);
                }
                ?>
            </div>
        </div>
        <?php
    }
}
include('footer.php');
?>
<script type="text/javascript">

    function show_register(){
        document.getElementById('register_box').style.visibility = 'visible';
        document.getElementById('login_box').style.display = 'none';
    }
    function show_login(){
        document.getElementById('login_box').style.display = 'block ';
        document.getElementById('register_box').style.visibility = 'hidden';
    }




    function checkpass() {

        var pass;
        pass = document.getElementById('password_r').value;

        var re = /^(?=.*[a-z])(?=.*[A-Z0-9]).*$/;

        if( re.test(String(pass))) {
            document.getElementById('checkp').innerHTML = "";
            document.getElementById('register').disabled = false;
        }
        else {
            document.getElementById('checkp').innerHTML = "Password must contain at least one lower case letter and one number or uppercase letter!";
            document.getElementById('register').disabled = true;
        }




    }


    function checkemail() {

        var pass;
        pass = document.getElementById('username_r').value;


        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if(re.test(String(email).toLowerCase()))
            document.getElementById('checkp').innerHTML= "";
        else {
            document.getElementById('checkp').innerHTML = "Wrong email";
            // document.getElementById('register').disabled = false;
        }




    }

</script>
