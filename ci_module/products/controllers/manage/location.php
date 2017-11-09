<?php

class ProductsManageLocation
{

    var $id, $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        start_form();
        box_start("");
        $this->listview();

        box_start("");
        $this->detail();

        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'both');
        box_footer_end();

        box_end();
        end_form();
    }

    private function listview()
    {
        $result = get_item_locations(check_value('show_inactive'));
        start_table(TABLESTYLE, 'class="table table-striped table-bordered table-hover tablestyle"');
        $th = array(
            _("Location Code"),
            _("Location Name"),
            _("Address"),
            _("Phone"),
            _("Secondary Phone"),
            "edit" => array(
                'label' => NULL,
                'width' => '5%'
            ),
            "delete" => array(
                'label' => NULL,
                'width' => '5%'
            )
        );
        inactive_control_column($th);
        table_header($th);
        $k = 0; // row colour counter
        while ($myrow = db_fetch($result)) {

            alt_table_row_color($k);

            label_cell($myrow["loc_code"]);
            label_cell($myrow["location_name"]);
            label_cell($myrow["delivery_address"]);
            label_cell($myrow["phone"]);
            label_cell($myrow["phone2"]);
            inactive_control_cell($myrow["loc_code"], $myrow["inactive"], 'locations', 'loc_code');
            edit_button_cell("Edit" . $myrow["loc_code"], _("Edit"));
            delete_button_cell("Delete" . $myrow["loc_code"], _("Delete"));
            end_row();
        }
        // END WHILE LIST LOOP
        inactive_control_row($th);
        end_table();
    }

    private function detail()
    {
        row_start();
        col_start(12);
        bootstrap_set_label_column(2);

        $_POST['email'] = "";
        if ($this->selected_id != - 1) {
            // editing an existing Location

            if ($this->mode == 'Edit') {
                $myrow = get_item_location($this->selected_id);

                $_POST['loc_code'] = $myrow["loc_code"];
                $_POST['location_name'] = $myrow["location_name"];
                $_POST['delivery_address'] = $myrow["delivery_address"];
                $_POST['contact'] = $myrow["contact"];
                $_POST['phone'] = $myrow["phone"];
                $_POST['phone2'] = $myrow["phone2"];
                $_POST['fax'] = $myrow["fax"];
                $_POST['email'] = $myrow["email"];
            }
            hidden("selected_id", $this->selected_id);
            hidden("loc_code");
            label_row(_("Location Code:"), $_POST['loc_code']);
        } else { // end of if $selected_id only do the else when a new record is being entered
            input_text_bootstrap(_("Location Code:"), 'loc_code');
        }

        input_text_bootstrap(_("Location Name:"), 'location_name');
        input_text_bootstrap(_("Contact for deliveries:"), 'contact');

        input_textarea_bootstrap(_("Address:"), 'delivery_address', null, 35, 5);

        input_text_bootstrap(_("Telephone No:"), 'phone');
        input_text_bootstrap(_("Secondary Phone Number:"), 'phone2');
        input_text_bootstrap(_("Facsimile No:"), 'fax');
        input_text_bootstrap(_("E-mail:"), 'email');

        col_end();
        row_end();
    }
}