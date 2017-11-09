<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class OpeningSupplier
{

    function __construct()
    {
        $this->ob = module_control_load('ob','opening');
        $this->ob->type = 'supplier';

        $this->db = get_instance()->db;

    }

    function index()
    {
        global $Ajax;
        page('Supplier Opening Balance');
        $Ajax->activate('_page_body');
        start_form();
        box_start();
        if (input_post('submit')) {
            return $this->ob->customer_submit();
        } elseif (input_get('remove')) {
            $this->ob->remove_cus_sup(input_val('remove'));
            redirect("opening/supplier");
        } else
            if (get_instance()->uri->segment(3) == 'add' || get_instance()->uri->segment(3) == 'view') {

                $this->ob->edit();
            } else {
                $this->ob->items();
            }
        box_end();
        end_form();
        end_page();
    }





    /*
     * update Actions
     */

}