<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class GstManageTaxes
{

    var $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {

        $this->model = module_model_load('tax', 'gst');
    }

    function index()
    {

        if( !in_ajax() ){
            display_notification(_("To avoid problems with manual journal entry all tax types should have unique Sales/Purchasing GL accounts."));
        }

        start_form();

        box_start("");
        $this->listview();
        box_footer_show_active();

        if ($this->mode == 'Edit') {
            box_start("Tax Detail",'fa-share-square-o');
            $this->detail();
        }


        box_footer_start();

        if ($this->mode == 'Edit') {
            submit_add_or_update_center($this->selected_id == - 1, '', 'both');
        }

        box_footer_end();

        box_end();
        end_form();
    }

    private function listview()
    {
        start_table(TABLESTYLE);

        $th = array(
            _("Description"),
            _("Default Rate (%)"),
            _("Sales GL Account"),
            _("Purchasing GL Account"),
            _('Type'),
            "edit" => array(
                'label' => "Edit",
                'width' => '5%'
            ),
        );
        inactive_control_column($th);
        table_header($th);

        $k = 0;
        $taxcode_options = get_instance()->api->get_data("tax_code", true);
        $taxcode_options = $taxcode_options['options'];

        $taxes = get_instance()->api->get_data('taxdetail');
        foreach ($taxes as $tax) {
            $tax_gl = $this->model->get_row($tax->id);
            if (! is_object($tax_gl)) {
                get_instance()->db->insert('tax_types', array(
                    'id' => $tax->id,
                    'name' => $tax->name,
                    'rate' => $tax->rate,
                    'inactive' => 1,
                    'sales_gl_code' => 2150,
                    'purchasing_gl_code' => 1300
                ));
                $tax_gl = $this->model->get_row($tax->id);
            }
            alt_table_row_color($k);
            label_cell($tax->name);
            label_cell(percent_format($tax->rate), "align=right");
            label_cell($tax_gl->sales_gl_code . "&nbsp;" . $tax_gl->SalesAccountName);
            label_cell($tax_gl->purchasing_gl_code . "&nbsp;" . $tax_gl->PurchasingAccountName);

            label_cell($tax->no);

            inactive_control_cell($tax->id, 1, 'tax_types', 'id');
            edit_button_cell("Edit" . $tax->id, _("Edit"));
            // delete_button_cell("Delete".$myrow["id"], _("Delete"));

            end_row();
        }

        end_table(1);
    }

    private function detail()
    {
        row_start('justify-content-md-center');
        col_start(8);


            $tax_type = NULL;
            $tax_rate = 0;

            if ($this->selected_id != - 1) {

                // editing an existing status code
                $tax_row = get_gst($this->selected_id);
                // bug($tax_row);
                $myrow = get_tax_type($this->selected_id);
                // bug($myrow);
                $_POST['name'] = $tax_row->name;
                $_POST['rate'] = percent_format($tax_row->rate);
                $_POST['sales_gl_code'] = $myrow["sales_gl_code"];
                $_POST['purchasing_gl_code'] = $myrow["purchasing_gl_code"];
                //

                $_POST['active'] = (isset($myrow['active'])) ? $myrow['active'] : 0;
                $_POST['gst_03_type'] = $myrow['gst_03_type'];
                // $_POST['f3_box']=$myrow['f3_box'];
                $_POST['use_for'] = $tax_row->use_for;

                hidden('selected_id', $this->selected_id);
//                 $tax_api = get_instance()->api_membership->get_data('taxdetail/' . $this->selected_id);
                $tax_type = $tax_row->no;
                $tax_rate = $tax_row->rate;

                // $tax_type =
            }

            input_label_bootstrap(_("Description :"), null, $tax_row->name);
            hidden('name', $tax_row->name);

            input_label_bootstrap(_("Default Rate :"), null, number_format2($tax_rate, 2) . ' %');
            hidden('rate', $tax_rate);

            gl_accounts_bootstrap(_("Sales GL Account:"), 'sales_gl_code', null);
            gl_accounts_bootstrap(_("Purchasing GL Account:"), 'purchasing_gl_code', null);

            // check_row(_("Active"),'inactive',1,0);

            // text_row_ex(_("GST 03 Box :"),'f3_box',50);
            // tax_types_list_row(_("Type :"),'gst_03_type',null);

            input_label_bootstrap( "Type :",null, $tax_type);

            radios_bootstrap('Use In', 'use_for',null,false,array(2=>'Sales', 3=>'Purchase', 1=>'Both'));

        col_end();
        row_end();
    }
}