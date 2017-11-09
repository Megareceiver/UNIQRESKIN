<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * User: QuanICT
 * Date: 6/26/2017
 * Time: 12:26 PM
 */
class AdminChangePassword
{

    function __construct()
    {
        $this->check_submit();
    }

    public function form(){

        msg_info(_("Enter your new password in the fields."));
        start_form();
        box_start();

        $myrow = get_user($_SESSION["wa_current_user"]->user);


        $_POST['cur_password'] = "";
        $_POST['password'] = "";
        $_POST['passwordConfirm'] = "";

        row_start('justify-content-md-center');
        col_start(12,"col-md-8");

        input_label(_("User login:"),null, $myrow['user_id']);
        input_password(_("Current Password:"), 'cur_password');
        input_password(_("New Password:"), 'password');
        input_password(_("Repeat New Password:"), 'passwordConfirm');

        row_end();
        box_footer_start();

        submit_icon('UPDATE_ITEM', _('Change password'),'save');
        box_footer_end();
        box_end();
        end_form();
    }

    private function check_submit(){
        if (isset($_POST['UPDATE_ITEM']) && check_csrf_token())
        {

            if ($this->can_process())
            {
                global $allow_demo_mode;
                if ($allow_demo_mode) {
                    display_warning(_("Password cannot be changed in demo mode."));
                } else {
                    update_user_password($_SESSION["wa_current_user"]->user,
                        $_SESSION["wa_current_user"]->username,
                        md5($_POST['password']));
                    display_notification(_("Your password has been updated."));
                }
                global $Ajax;
                $Ajax->activate('_page_body');
            }
        }
    }

    private function can_process()
    {

        $Auth_Result = hook_authenticate($_SESSION["wa_current_user"]->username, $_POST['cur_password']);

        if (!isset($Auth_Result))	// if not used external login: standard method
            $Auth_Result = get_user_auth($_SESSION["wa_current_user"]->username, md5($_POST['cur_password']));

        if (!$Auth_Result)
        {
            display_error( _("Invalid password entered."));
            set_focus('cur_password');
            return false;
        }

        if (strlen($_POST['password']) < 4)
        {
            display_error( _("The password entered must be at least 4 characters long."));
            set_focus('password');
            return false;
        }

        if (strstr($_POST['password'], $_SESSION["wa_current_user"]->username) != false)
        {
            display_error( _("The password cannot contain the user login."));
            set_focus('password');
            return false;
        }

        if ($_POST['password'] != $_POST['passwordConfirm'])
        {
            display_error( _("The passwords entered are not the same."));
            set_focus('password');
            return false;
        }

        return true;
    }
}