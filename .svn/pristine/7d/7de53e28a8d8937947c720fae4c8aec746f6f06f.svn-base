<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GLTrial_Balance {

    function __construct() {
        $ci = get_instance();
        $this->db = $ci->db;
        $this->gl_account_model = module_model_load('gl_account');
        $this->gl_trans_model = module_model_load('trans');
    }

    function index(){
        $ci = get_instance();

//         $ci->db->select('SUM(amount) AS sum_amount',false)->from('gl_trans AS gl');
//         $ci->db->join('chart_master AS acc','acc.account_code = gl.account','INNER');
//         $ci->db->join('chart_types AS acc_type','acc_type.id = acc.account_type','INNER');

//         $ci->db->join('chart_class AS acc_class','acc_class.cid = acc_type.class_id','INNER');
//         $ci->db->where(array('acc_class.ctype >='=>CL_ASSETS,'acc_class.ctype <='=>CL_EQUITY));

//         $result = $this->db->where('gl.tran_date <=',date2sql('31-12-2015') )->get();
// bug($result->row());die;

        $data = array(
            'dimension'=>0,
            'dimension2'=>0,
            'datefrom'=>null,
            'dateto'=>null,
            'balance'=>1,
            'total'=>array(
                'previous'=>array('debit'=>0,'credit'=>0),
                'current'=>array('debit'=>0,'credit'=>0),
                'total'=>array('debit'=>0,'credit'=>0)

            ),
            'ending'=>array(
                'previous'=>array('debit'=>0,'credit'=>0),
                'current'=>array('debit'=>0,'credit'=>0),
                'total'=>array('debit'=>0,'credit'=>0)
            ),
            'uri_check'=>$ci->uri->uri_string()."/double_entry"
        );
        $data['datefrom'] = input_get('from');

        if( strlen($data['datefrom']) < 1 ){
            $data['datefrom'] = begin_month();
        }
        $data['dateto'] = input_get('to');
        if( strlen($data['dateto']) < 1 ){
            $data['dateto'] = end_month();
        }

        $begin = get_fiscalyear_begin_for_date($data['datefrom']);

        if (date1_greater_date2($begin, $data['datefrom']))
            $begin = $data['datefrom'];

        $begin = add_days($begin, -1);

        $classes = $this->gl_account_model->get_classes(false);
        if( count($classes) > 0 ) foreach ($classes AS $k=>$class){
            $types = $this->gl_account_model->get_types(false, $class->cid, false);
            if( count($types) > 0 ) foreach ($types AS $kk=>$type){
                $accounts = $this->gl_account_model->get_accounts(null, null, $type->id);
                if( count($accounts) >0 ) foreach ($accounts AS $kkk=>$ac){

                    $accounts[$kkk]->previous = $this->gl_trans_model->get_balance( $ac->account_code, $data['dimension'], $data['dimension2'], $begin, $data['datefrom'], false, false);
                    $accounts[$kkk]->current =  $this->gl_trans_model->get_balance( $ac->account_code, $data['dimension'], $data['dimension2'], $data['datefrom'], $data['dateto'], true, true);

//                     $accounts[$kkk]->total =    $this->gl_trans_model->get_balance( $ac->account_code, $data['dimension'], $data['dimension2'], $begin, $data['dateto'], false, true);
                    $accounts[$kkk]->total =    $this->gl_trans_model->get_balance( $ac->account_code, $data['dimension'], $data['dimension2'], '31-12-2015', '31-12-2015', false, true);

                    $data['total']['previous']['debit']+=$accounts[$kkk]->previous['debit'];
                    $data['total']['previous']['credit']+=$accounts[$kkk]->previous['credit'];

                    $data['total']['current']['debit']+=$accounts[$kkk]->current['debit'];
                    $data['total']['current']['credit']+=$accounts[$kkk]->current['credit'];

                    $data['total']['total']['debit']+=$accounts[$kkk]->total['debit'];
                    $data['total']['total']['credit']+=$accounts[$kkk]->total['credit'];

                }
                $types[$kk]->accounts = $accounts;
            }
            $classes[$k]->types = $types;
        }
        $data['ending']['previous']['debit'] =  $data['total']['previous']['debit'] -   $data['total']['previous']['credit'];
        $data['ending']['current']['debit'] =   $data['total']['current']['debit'] -    $data['total']['current']['credit'];
        $data['ending']['total']['debit'] =     $data['total']['total']['debit']    -   $data['total']['total']['credit'];


        $data['accounts_groups_classes'] = $classes;


        page('GL Balacne | Check');

        global $Ajax;
        if(in_ajax()) {
            $Ajax->activate('_page_body');
        }
        start_form();

        module_view('inquiry/trial_balance',$data);
        end_form();
        end_page();
    }

    function double_entry(){
        $data = array(
            'dimension'=>0,
            'dimension2'=>0,
            'datefrom'=>'28-09-2015',
            'dateto'=>'28-09-2015'
        );

        $date_range = array('gl.tran_date >='=>date2sql($data['datefrom']),'gl.tran_date <='=>date2sql($data['dateto']),'gl.amount <>'=>0 );
        $types_query =  $this->db->where($date_range)->group_by('gl.type, gl.type_no')->select('gl.type, gl.type_no')->get('gl_trans AS gl');
        if( is_object($types_query) && $types_query->num_rows > 0 ) foreach ($types_query->result() AS $tran){
            $this->db->where('account <> "" AND account IS NOT NULL');
            $this->db->where(array('type_no'=>$tran->type_no, 'type'=>$tran->type));
            $double_entry = $this->db->select('SUM(amount) AS sum')->get('gl_trans AS gl')->row();

            if( is_object($double_entry) && abs($double_entry->sum) != 0 && abs($double_entry->sum) > 0.001  ){
                bug($tran);
                bug($double_entry);

            }
        }

        bug($this->db->last_query() );

        die('call me');
    }


}
