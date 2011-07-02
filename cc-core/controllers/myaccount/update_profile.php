<?php

### Created on March 24, 2009
### Created by Miguel A. Hurtado
### This script allows the user to edit their profile


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Picture');


// Establish page variables, objects, arrays, etc
View::InitView ('update_profile');
Plugin::Trigger ('update_profile.start');
View::$vars->logged_in = User::LoginCheck (HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->Errors = array();
View::$vars->error_msg = NULL;
View::$vars->success = NULL;
$duplicate = NULL;





/**************************
 * Handle Form if submitted
 *************************/

if (isset ($_POST['submitted'])) {

    // Validate First Name
    if (!empty (View::$vars->user->first_name) && $_POST['first_name'] == '') {
        View::$vars->data['first_name'] = '';
    } elseif (!empty ($_POST['first_name']) && !ctype_space ($_POST['first_name'])) {
        View::$vars->data['first_name'] = htmlspecialchars ($_POST['first_name']);
    }


    // Validate Last Name
    if (!empty (View::$vars->user->last_name) && $_POST['last_name'] == '') {
        View::$vars->data['last_name'] = '';
    } elseif (!empty ($_POST['last_name']) && !ctype_space ($_POST['last_name'])) {
        View::$vars->data['last_name'] = htmlspecialchars ($_POST['last_name']);
    }


    // Validate Email
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i',$_POST['email'])) {
        $email = array ('email' => $_POST['email']);
        $id = User::Exist ($email);
        if (!$id || $id == View::$vars->user->user_id) {
            View::$vars->data['email'] = $_POST['email'];
        } else {
            View::$vars->Errors['email'] = Language::GetText('error_email_unavailable');
        }

    } else {
        View::$vars->Errors['email'] = Language::GetText('error_email');
    }



    // Validate Website
    if (!empty (View::$vars->user->website) && $_POST['website'] == '') {
        View::$vars->data['website'] = '';
    } elseif (!empty ($_POST['website']) && !ctype_space ($_POST['website'])) {
        View::$vars->data['website'] = htmlspecialchars ($_POST['website']);
    }



    // Validate About Me
    if (!empty (View::$vars->user->about_me) && $_POST['about_me'] == '') {
        View::$vars->data['about_me'] = '';
    } elseif (!empty ($_POST['about_me']) && !ctype_space ($_POST['about_me'])) {
        View::$vars->data['about_me'] = htmlspecialchars ($_POST['about_me']);
    }



    // Update User if no errors were found
    if (empty (View::$vars->Errors)) {
        View::$vars->success = Language::GetText('success_profile_updated');
        View::$vars->user->Update (View::$vars->data);
        Plugin::Trigger ('update_profile.update_profile');
    } else {
        View::$vars->error_msg = Language::GetText('errors_below');
        View::$vars->error_msg .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->Errors);
    }



} // END Handle Profile form





/*************************
Handle Upload picture Form
*************************/

if (isset ($_POST['submitted_picture'])) {

    $Errors = null;

    ### Validate picture
    if (!empty ($_FILES['upload']['name'])) {

        // Check for browser upload errors
        if (!empty ($_FILES['upload']['error'])) {
            if ($_FILES['upload']['error'] != 4) {
                $Errors = Language::GetText('error_picture_invalid');
            } else if ($_FILES['upload']['error'] == 2) {
                $Errors = Language::GetText('error_picture_filesize');
            } else {
                $Errors = Language::GetText('error_picture_system');
            }
        }


        // Validate mime-type sent by browser
        if (empty ($Errors) && !preg_match ('/image\/(png|gif|jpeg)/i', $_FILES['upload']['type'])) {
            $Errors = Language::GetText('error_picture_format');
        }

        // Validate file extension
        $extension = Functions::GetExtension ($_FILES['upload']['name']);
        if (empty ($Errors) && !preg_match ('/(gif|png|jpe?g)/i', $extension)) {
            $Errors = Language::GetText('error_picture_format');
        }

        // Validate filesize
        if (empty ($Errors) && (empty ($_FILES['upload']['size']) || filesize ($_FILES['upload']['tmp_name']) > 30000)) {
            $Errors = Language::GetText('error_picture_filesize');
        }

        // Validate image data
        if (empty ($Errors)) {
            $handle = fopen ($_FILES['upload']['tmp_name'],'r');
            $image_data = fread ($handle, filesize ($_FILES['upload']['tmp_name']));
            if (!@imagecreatefromstring ($image_data)) {
                $Errors = Language::GetText('error_picture_format');
            }
        }


        // Store uploaded image if no errors were found
        if (empty ($Errors)) {

            // Check for existing picture
            if (!empty (View::$vars->user->picture)) {
                Picture::Delete (View::$vars->user->picture);
            }

            // Save Picture
            $save_as = Picture::CreateFilename ($extension);
            Picture::SavePicture ($_FILES['upload']['tmp_name'], $extension, $save_as);
            View::$vars->user->Update (array ('picture' => $save_as));
            View::$vars->success = Language::GetText('success_picture_updated');
            Plugin::Trigger ('update_profile.update_picture');

        } else {
            View::$vars->error_msg = $Errors;
        }

    } else {
        View::$vars->error_msg = Language::GetText('error_picture_invalid');
    }

}





/**************************
Handle Reset picture Action
**************************/

if (!empty ($_GET['action']) && $_GET['action'] == 'reset') {
    Picture::Delete (View::$vars->user->picture);
    View::$vars->user->Update (array ('picture' => ''));
    View::$vars->success = Language::GetText('success_picture_reset');
    Plugin::Trigger ('update_profile.picture_reset');
}


// Output page
Plugin::Trigger ('update_profile.before_render');
View::Render ('myaccount/update_profile.tpl');

?>