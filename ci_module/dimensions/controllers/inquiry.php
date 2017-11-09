<?php

class DimensionsInquiry
{

    var $dim, $outstanding_only = 0;

    function __construct ()
    {
        $this->dim = get_company_pref('use_dimension');
        if (isset($_GET['outstanding_only']) && $_GET['outstanding_only'])
        {
            $this->outstanding_only = 1;
        }
        $this->page_get();
        $this->page_ajaxupdate();
    }

    function index ()
    {
        
        // --------------------------------------------------------------------------------------
        start_form(false, false, $_SERVER['PHP_SELF'] . "?outstanding_only=$this->outstanding_only");
        box_start();
//         start_table(TABLESTYLE_NOBORDER);
//         start_row();
        
        $this->items_filter();
        end_row();
//         end_table();
        
        $this->items_table();
        box_footer();
        box_end();
        end_form();
    }

    private function items_filter ()
    {
        row_start('inquiry-filter justify-content-center');
        col_start(2);
        bootstrap_set_label_column(5);
        input_ref(_("Reference"), 'OrderNumber',null,null,true);
        bootstrap_set_label_column(0);
        
        col_start(2);
        numbers_list(_("Type"), 'type_', null, 1, $this->dim,_("All"));
        
        col_start(2);
        input_date_bootstrap(_("From"), 'FromDate',null,false,false,0,-5);
        col_start(2);
        input_date_bootstrap(_("To"), 'ToDate');
        
        col_start(1);
        bootstrap_set_label_column(8);
        check_bootstrap('Overdue', 'OverdueOnly');
        bootstrap_set_label_column(0);
        
        
        if (! $this->outstanding_only) {
            col_start(1);
            bootstrap_set_label_column(6);
            check_bootstrap('Open', 'OpenOnly');
            bootstrap_set_label_column(0);
            
        } else
            $_POST['OpenOnly'] = 1;
        
        col_start(1);
        
        submit_search('SearchOrders', _("Search"),'default');
        row_end();
    }

    private function items_table ()
    {
        $sql = get_sql_for_search_dimensions($this->dim);
        
        $cols = array(
                _("#") => array(
                        'fun' => 'view_link'
                ),
                _("Reference"),
                _("Name"),
                _("Type"),
                _("Date") => 'date',
                _("Due Date") => array(
                        'name' => 'due_date',
                        'type' => 'date',
                        'ord' => 'asc'
                ),
                _("Closed") => array(
                        'fun' => 'is_closed'
                ),
                _("Balance") => array(
                        'type' => 'amount',
                        'insert' => true,
                        'fun' => 'sum_dimension'
                ),
                array(
                        'insert' => true,
                        'fun' => 'edit_link'
                )
        );
        
        if ($this->outstanding_only) {
            $cols[_("Closed")] = 'skip';
        }
        
        $table = & new_db_pager('dim_tbl', $sql, $cols);
//         $table->set_marker('is_overdue', _("Marked department are overdue."));
        $table->ci_control = $this;
        
        display_db_pager($table);
    }
    function edit_link($row)
    {
    	//return $row["closed"] ?  '' :
    	//	pager_link(_("Edit"),
            	//		"/dimensions/dimension_entry.php?trans_no=" . $row["id"], ICON_EDIT);
    	return pager_link(_("Edit"),
            			"/dimensions/dimension_entry.php?trans_no=" . $row["id"], ICON_EDIT);
    }

    private function page_get ()
    {
        if (isset($_GET["stock_id"]))
            $_POST['SelectedStockItem'] = $_GET["stock_id"];
    }

    private function page_ajaxupdate ()
    {
        global $Ajax;
        if (get_post('SearchOrders')) {
            $Ajax->activate('dim_table');
        } elseif (get_post('_OrderNumber_changed')) {
            $disable = get_post('OrderNumber') !== '';
            
            $Ajax->addDisable(true, 'FromDate', $disable);
            $Ajax->addDisable(true, 'ToDate', $disable);
            $Ajax->addDisable(true, 'type_', $disable);
            $Ajax->addDisable(true, 'OverdueOnly', $disable);
            $Ajax->addDisable(true, 'OpenOnly', $disable);
            
            if ($disable) {
                // $Ajax->addFocus(true, 'OrderNumber');
                set_focus('OrderNumber');
            } else
                set_focus('type_');
            
            $Ajax->activate('dim_table');
        }
    }
}