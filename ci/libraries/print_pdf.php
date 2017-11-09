<?php
class print_pdf {
    function __construct(){
        $ci = get_instance();
        $this->company = $this->pdf->company;
    }
}