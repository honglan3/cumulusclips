<?php

### Created on May 16, 2009
### Created by Miguel A. Hurtado
### This script displays the completion page for the upload process


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
View::InitView ('upload_complete');
Plugin::Trigger ('upload_complete.start');
View::$vars->logged_in = User::LoginCheck (HOST  . '/login/');
View::$vars->user = new User (View::$vars->logged_in);



### Verify user completed upload process
if (isset ($_SESSION['token'])) {
    unset ($_SESSION['token']);
} else {
    header ('Location: ' . HOST . '/myaccount/upload-video/');
    exit();
}


// Output page
Plugin::Trigger ('upload_complete.before_render');
View::Render ('myaccount/upload_complete.tpl');

?>