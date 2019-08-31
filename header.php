<?php
require_once('php_functions.php');
setcookie('testcookie', 'testcookie', time()+3600);

check_time_inactivity();
//check_https();

$use_sts = true;

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <link rel="icon" href="AllAuctionsIcon.jpg" />
    <title>Exam Bookings</title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <script type="text/javascript" src="js/jquery-3.0.0.min.js"></script>
    <link href="theme.css" rel="stylesheet" type="text/css">

</head>

<body>
    <noscript>
        <div id = "nojava">
            <p style="color: #cc0000">You don't have javascript enabled.  Good luck with that.</p>
        </div>


    </noscript>
    <div id="header">
        <div id="title_header"><p></p></div>
    </div>
    <?php

    if(check_cookie()){?>
    <div id="menu">
        <?php
            $url = explode('/', $_SERVER['PHP_SELF']);
                switch ($url[count($url) - 1]){
                    case 'home.php':
                        $page_active = 1;
                        break;
                    case 'login.php':
                        $page_active = 2;
                        
                        break;
                    case 'logout.php':
                        $page_active = 3;
                        break;
                    default:
                        $page_active = 1;
                        break;
                }
        ?>
        <div><a class="menu_el <?php if($page_active == 1) echo 'active'; ?>" href="index.php">Home</a></div>
            <?php if (!u_Log_in())
            { ?>
                <div>
                    <a class="menu_el <?php if($page_active == 2) echo 'active'; ?>" href="login.php">Login / Register</a>
                </div>
            <?php
            }else { ?>
                  <div>
                      <a class="menu_el <?php if($page_active == 3) echo 'active'; ?>" href="logout.php">Log Out</a>
                  </div>
            <?php } ?>
    </div>
<?php }
    else{
        ?>
        <div id = "menu">
            <p style="color: #cc0000">Cookie are not enabled! Please enable to navigate!</p>
        </div>
    <?php
    }
?>