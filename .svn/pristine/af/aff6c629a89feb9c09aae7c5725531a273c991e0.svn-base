<?php

class ManagePrinter
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
        $result = get_all_printers();
        start_table(TABLESTYLE);
        $th = array(
            _("Name"),
            _("Description"),
            _("Host"),
            _("Printer Queue"),
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
        table_header($th);

        $k = 0; // row colour counter
        while ($myrow = db_fetch($result)) {
            alt_table_row_color($k);

            label_cell($myrow['name']);
            label_cell($myrow['description']);
            label_cell($myrow['host']);
            label_cell($myrow['queue']);
            edit_button_cell("Edit" . $myrow['id'], _("Edit"));
            delete_button_cell("Delete" . $myrow['id'], _("Delete"));
            end_row();
        } // END WHILE LIST LOOP

        end_table();
    }

    private function detail()
    {
        if ($this->selected_id != - 1) {
            if ($this->mode == 'Edit') {
                $myrow = get_printer($this->selected_id);
                $_POST['name'] = $myrow['name'];
                $_POST['descr'] = $myrow['description'];
                $_POST['queue'] = $myrow['queue'];
                $_POST['tout'] = $myrow['timeout'];
                $_POST['host'] = $myrow['host'];
                $_POST['port'] = $myrow['port'];
            }
            hidden('selected_id', $this->selected_id);
        } else {
            if (! isset($_POST['host']))
                $_POST['host'] = 'localhost';
            if (! isset($_POST['port']))
                $_POST['port'] = '515';
        }

        row_start('justify-content-md-center');
        col_start(8, 'class="col-md-offset-2"');

        input_text(_("Printer Name"), 'name');
        input_text(_("Printer Description"), 'descr');
        input_text(_("Host name or IP"), 'host');
        input_text(_("Port"), 'port');
        input_text(_("Printer Queue"), 'queue');
        input_text(_("Timeout"), 'tout');

        col_end();
        row_end();
    }
}