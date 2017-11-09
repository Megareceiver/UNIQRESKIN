<?php

class Documentsattachment
{

    var $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        start_form(true);

        box_start("");
        systypes(_("Type:"), 'filterType', null, true);
        if (list_updated('filterType'))
            $this->selected_id = - 1;
        ;

        row_start(null, 'style="padding-top:15px"');
        $this->items_list($_POST['filterType']);
        row_end();

        box_start();
        $this->item_detail();

        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'process');
        box_footer_end();

        box_end();
        end_form();
    }

    function items_list($tran_type)
    {
        $sql = get_sql_for_attached_documents($tran_type);
        $cols = array(
            _("#") => array(
                'fun' => 'trans_view',
//                 'ord' => ''
            ),
            _("Description") => array(
                'name' => 'description'
            ),
            _("Filename") => array(
                'name' => 'filename'
            ),
            _("Size") => array(
                'name' => 'filesize'
            ),
            _("Filetype") => array(
                'name' => 'filetype'
            ),
            _("Date Uploaded") => array(
                'name' => 'tran_date',
                'type' => 'date'
            ),
            "Edit"=>array(
                'insert' => true,
                'fun' => 'edit_link',
                'align'=>'center'
            ),
            "View"=>array(
                'insert' => true,
                'fun' => 'view_link',
                'align'=>'center'
            ),
            "Down"=>array(
                'insert' => true,
                'fun' => 'download_link',
                'align'=>'center'
            ),
            "Del"=>array(
                'insert' => true,
                'fun' => 'delete_link',
                'align'=>'center'
            )
        );
        $table = & new_db_pager('trans_tbl', $sql, $cols);
        $table->ci_control = $this;

        display_db_pager($table);
    }

    function edit_link($row)
    {
        return button('Edit' . $row["id"], _("Edit"), _("Edit"), ICON_EDIT);
    }

    function view_link($row)
    {
        return button('view' . $row["id"], _("View"), _("View"), ICON_VIEW);
    }

    function download_link($row)
    {
        return button('download' . $row["id"], _("Download"), _("Download"), ICON_DOWN);
    }

    function delete_link($row)
    {
        return button('Delete' . $row["id"], _("Delete"), _("Delete"), ICON_DELETE);
    }

    function item_detail()
    {
//         row_start();
        row_start('justify-content-md-center');
        col_start(8);
        if ($this->selected_id != - 1) {
            if ( $this->mode == 'Edit') {
                $row = get_attachment($this->selected_id);
                $_POST['trans_no'] = $row["trans_no"];
                $_POST['description'] = $row["description"];
                hidden('trans_no', $row['trans_no']);
                hidden('unique_name', $row['unique_name']);
                input_label_bootstrap( _("Transaction #"), 'trans_no' );
            }
            hidden('selected_id', $this->selected_id);
        } else
            input_text( _("Transaction #") , 'trans_no');


        input_text(_("Description"), 'description');
        file_bootstrap(_("Attached File") , 'filename');
        col_end();
        row_end();
    }
}