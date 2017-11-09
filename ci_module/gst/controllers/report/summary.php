<?php
class GstReportSummary {
    function __construct() {
        $this->input = get_instance()->input;
        $this->report = module_control_load('report','report');
        $this->report_model = module_model_load('report','tax');

        $this->datatable = module_control_load('datatable','html');
    }

    var $types_report = array(
        "input-tax"=>'Total Acquisition and Input Tax for Taxable ',
        "ouput-tax"=>'Total Supply and Output Tax for Taxable Period',

//         //         array('id' => "addition",'title'=>'Additional Information for Taxable Period'),
//     //         array('id' => "invoice",'title'=>'List of Invoice for Credit Note / Debit Note'),
//     //         array('id' => "baddebt-relief",'title'=>'Bad Debt Relief'),
//         array('id' => "baddebt-recovery",'title'=>'Bad Debt Recovery'),

    );

    var $report_fields = array(
        'start_date' => array('type'=>'qdate','title'=>'Start Date','value'=>'' ),
        'end_date' => array('type'=>'qdate','title'=>'End Date','value'=>'' ),
        'type_report'=>array('type'=>'options','title'=>'Type Report','value'=>""),
    );

    var $taxes = array();

    function index(){
        if( $this->input->post() ){


            switch ($type_report=input_val('type_report')){
                case 'input-tax':
                    $this->report_return(3);
                    break;
                case 'ouput-tax':
                    $this->report_return(2);
                    break;

                default:
                    break;
            }
        } else {
            $this->form();
        }



    }

    private function form(){
        $date = new DateTime('now');
        $date->modify('last day of this month');
        $date_first = new DateTime('now');
        $date_first->modify('first day of this month');

        $this->report_fields['start_date']['value'] = $date_first->format('d-m-Y');
        $this->report_fields['end_date']['value'] = $date->format('d-m-Y');
        $this->report_fields['type_report']['options'] = $this->types_report;

        $this->report->fields = $this->report_fields;
        $submit = array(
//             'UPDATE_ITEM'=>array('Display : GST Summary',"default"),
            'UPDATE_ITEM'=>array('Display : GST Summary',false),
        );
        $this->report->form('GST Summary',$submit);

    }

    private function report_return($using=1){
        $type_report = input_val('type_report');
        $taxes = (array)api_get('taxdetail');
        $data = array();
        if( !empty($taxes) && ($using ==2 OR $using ==3) ){
            foreach ($taxes AS $tid=>$tax){

                if( $tax->use_for == $using ){
                    $data[$tid] = $tax;
                }

            }
        }

        switch ($using){
            case 3:
                $datatable_view = array(
                    'name'=>array('Types of Acquisition','left',9,'trans_type'),
                    'debit'=>array('Value of Acquisition Excluding GST (RM)','center',5,'tran_detail_view'),
                    'reference'=>array('REF','center',8),
                );
                break;
            case 2:
                break;
            default;
        }

        page( "GST Summary Sheet of ". $this->types_report[$type_report] );
        module_view('gst_summary/input_tax',array('table'=>$datatable_view,'items'=>$data));
// bug($data);die;
//         $this->datatable->view($datatable_view, $data);

    }
}
?>