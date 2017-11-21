<?php

class SetupCompanySecurity
{

    function __construct()
    {
        $this->bootstrap = get_instance()->bootstrap;
    }

    function role_form()
    {
        global $Ajax;

        if (get_post('_show_inactive_update')) {
            $Ajax->activate('role');
            set_focus('role');
        }
        if (find_submit('_Section')) {
            $Ajax->activate('details');
        }

        box_start("");
        start_form();
        row_start('justify-content-md-center');

        col_start(8,"col-md-8 col-md-offset-2");
        $this->bootstrap->label_column = 2;
        security_roles_bootstrap(_("Role"), 'role', null, true, true, check_value('show_inactive'));

        $new_role = get_post('role')=='';
        col_start(2);
        $this->bootstrap->label_column = 7;
        check_bootstrap('Show Inactive', 'show_inactive', null, true);
        col_end();
        row_end();

        $this->bootstrap->label_column = 3;

        div_start('details',$trigger = null, $non_ajax = false, 'class="clearfix "');

            fieldset_start("Role Information");
            row_start('justify-content-md-center');
            col_start(8,"col-md-8 col-md-offset-2");
                input_text_bootstrap(_("Role name"), 'name');
                input_text_bootstrap(_("Role description"), 'description');
                input_text_bootstrap(_("Role name"), 'name');
                //         record_status_list_row(_("Current status:"), 'inactive');
                $this->bootstrap->col_end();
            $this->bootstrap->fieldset_end();
            col_end();
            echo "<hr>";
            row_start('justify-content-md-center');
            col_start(8,"col-md-8 col-md-offset-2");

                $this->bootstrap->label_column = 9;
                $this->access_role();
//                 $this->bootstrap->fieldset_end();
            col_end();
//             row_end();
        div_end();

        box_footer_start();
        div_start('controls');
            if ($new_role)
            {
                submit_center_first('Update', _("Update view"), '', null);
                submit_center_last('addupdate', _("Insert New Role"), '', 'default');
            }
            else
            {
                submit_center_first('addupdate', _("Save Role"), '', 'default');
                submit('Update', _("Update view"), true, '', null);
                submit('clone', _("Clone This Role"), true, '', true);
                submit('delete', _("Delete This Role"), true, '', true);
                submit_center_last('cancel', _("Cancel"), _("Cancel Edition"), 'cancel');
            }
        div_end();
        box_footer_end();


        end_form();
        $this->bootstrap->box_end();
    }

    var $security_areas , $security_sections = NULL;
    private function access_role(){
        //         start_table(TABLESTYLE, "width=40%");

        $k = $j = 0; //row colour counter
        $ext = $sec = $m = -1;

        foreach(sort_areas($this->security_areas) as $area =>$parms ) {


            // system setup areas are accessable only for site admins i.e.
            // admins of first registered company
            if (user_company() && (($parms[0]&0xff00) == SS_SADMIN)) continue;

            $newsec = ($parms[0]>>8)&0xff;
            $newext  = $parms[0]>>16;
            if ($newsec != $sec || (($newext != $ext) && ($newsec>99)))
            { // features set selection
                $ext = $newext;
                $sec = $newsec;
                $m = $parms[0] & ~0xff;

                if( in_array($m, array(SS_MANUF_C,SS_MANUF,SS_MANUF_A)) ){
                    /*
                     * QuanNH
                     * 2017-02-17 remove group not use
                     */
                    continue;
                }


                $checkBox = checkbox_material('Section'.$m,null,true,false);
                $this->bootstrap->fieldset_start($this->security_sections[$m].$checkBox ,' class="row"');
            }
            if (check_value('Section'.$m)) {
                $this->bootstrap->col_start(6,null,false);
                check_bootstrap($parms[1],'Area'.$parms[0], null, false, '');
                $this->bootstrap->col_end();
            } else {
                hidden('Area'.$parms[0]);
            }
        }
        //         end_table(1);
    }
}