<?php

class ManageShipper
{

    var $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        start_form();
        box_start("");
        $this->listview();
        box_footer_show_active();

        box_start("Shipping Company Detail",'fa-truck');
        $this->detail();

        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'both');
        box_footer_end();

        box_end();
        end_form();
    }

    private function listview()
    {
        $result = get_shippers(check_value('show_inactive'));
        start_table(TABLESTYLE);
        $th = array(
            _("Name"),
            _("Contact Person"),
            _("Phone Number"),
            _("Secondary Phone"),
            _("Address"),
            "edit" => array(
                'label' => "Edit",
                'width' => '5%',
                'class'=>'text-center',
            ),
            "delete" => array(
                'label' => 'Del',
                'width' => '5%',
                'class'=>'text-center',
            )
        );
        inactive_control_column($th);
        table_header($th);

        $k = 0; // row colour counter

        while ($myrow = db_fetch($result)) {
            alt_table_row_color($k);
            label_cell($myrow["shipper_name"]);
            label_cell($myrow["contact"]);
            label_cell($myrow["phone"]);
            label_cell($myrow["phone2"]);
            label_cell($myrow["address"]);
            inactive_control_cell($myrow["shipper_id"], $myrow["inactive"], 'shippers', 'shipper_id');
            edit_button_cell("Edit" . $myrow["shipper_id"], _("Edit"));
            delete_button_cell("Delete" . $myrow["shipper_id"], _("Delete"));
            end_row();
        }

        end_table(1);
    }

    private function detail()
    {
        row_start('justify-content-md-center');
        col_start(8);

        if ($this->selected_id != - 1) {
            if ($this->mode == 'Edit') {
                // editing an existing Shipper

                $myrow = get_shipper($this->selected_id);

                $_POST['shipper_name'] = $myrow["shipper_name"];
                $_POST['contact'] = $myrow["contact"];
                $_POST['phone'] = $myrow["phone"];
                $_POST['phone2'] = $myrow["phone2"];
                $_POST['address'] = $myrow["address"];
            }
            hidden('selected_id', $this->selected_id);
        }

        input_text(_("Name"), 'shipper_name');
        input_text(_("Contact Person"), 'contact');
        input_text(_("Phone Number"), 'phone');
        input_text(_("Secondary Phone Number"), 'phone2');
        input_text(_("Address"), 'address');

        col_end();
        row_end();
    }
}