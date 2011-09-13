<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
View::InitView ('contact');
Plugin::Trigger ('contact.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);
$resp = NULL;
View::$vars->Errors = array();
View::$vars->name = NULL;
View::$vars->email = NULL;
View::$vars->message = NULL;
View::$vars->captcha = NULL;
View::$vars->error_msg = NULL;
View::$vars->success = NULL;





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {
	
    // Validate name
    if (!empty ($_POST['name']) && !ctype_space ($_POST['name'])) {
        View::$vars->name = trim ($_POST['name']);
    } else {
        View::$vars->Errors['name'] = Language::GetText('error_name');
    }


    // Validate email
    $string = '/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i';
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ($string, $_POST['email'])) {
        View::$vars->email = trim ($_POST['email']);
    } else {
        View::$vars->Errors['email'] = Language::GetText('error_email');
    }


    // Validate feedback
    if (!empty ($_POST['message']) && !ctype_space ($_POST['message'])) {
        View::$vars->message = trim ($_POST['message']);
    } else {
        View::$vars->Errors['message'] = Language::GetText('error_message');
    }


    // Send email if no errors
    if (empty (View::$vars->Errors)) {

        $to = MAIN_EMAIL;
        $subject = 'Message received From {SITENAME}';
        $headers = 'From: Admin - TechieVideos.com <admin@techievideos.com>';
        $Msg = "Name: " . View::$vars->name . "\n";
        $Msg .= "E-mail: " . View::$vars->email . "\n\n";
        $Msg .= "Message:\n" . View::$vars->message;

        Plugin::Trigger ('contact.send');
        @mail ($to, $subject, $Msg, $headers);
        View::$vars->success = Language::GetText('success_contact_sent');

    } else {
        View::$vars->error_msg = Language::GetText('errors_below');
        View::$vars->error_msg .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->Errors);
    }
	
}


// Output Page
Plugin::Trigger ('contact.before_render');
View::Render ('contact.tpl');

?>