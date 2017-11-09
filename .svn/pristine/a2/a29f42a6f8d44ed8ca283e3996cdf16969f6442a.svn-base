<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class OpeningGl
{
    function __construct()
    {
        $this->ob_gl_model = module_model_load('gl');
    }
    
    function index()
    {
        if (isset($_POST['submit']) ){
            $begin_fiscalyear = begin_fiscalyear();
        
            $input_date = null;
            if( input_post('trans_date') ) {
                $input_date = input_post('trans_date');
            }
            if( strtotime($input_date) >= strtotime(begin_fiscalyear()) ){
                trigger_error('Date input can\'t in current fiscal year!', E_USER_ERROR);
            } else {
                $this->submit_check();
            }
        }
        
        page(_("System GL Accounts Opening Balance"));
        $this->form();
    }
    
    private function form(){
        start_form();
        box_start();
        start_table(TABLESTYLE2);
        
        
        echo '<tr><td colspan=2 >Opening Balance Date</td>';
        
        $_POST['trans_date'] = $this->check_tran_date();
        echo date_cells(null,'trans_date',$title = null, $check = null, $inc_days = 0, $inc_months = 0, $inc_years = 0, $params = array("class"=>'center'));
        
        echo '</tr>';
        
        $i = 1;
        
        $gl_account_posting = $this->ob_gl_model->accounts();

        $debit_total = $credit_total = 0;
        foreach ($gl_account_posting AS $group=>$items){
        
            echo '<tr><td colspan=3 class="tableheader" >'.$group.'</td></tr>';
            if( $i <= 1 ){
                echo '<tr><td style="width:60%;"> </td><td class="textright" >Debit</td><td class="textright" >Credit</td></tr>';
            }
        
            if( $items ){

                foreach ($items as $code=>$item) {

                    echo '<tr><td>'.$item['name'].'</td>';
                    echo '<td>';
                    input_money(NULL,"ob[$code][debit]",money_total_format($item['debit']) , curr_default() );
                    echo '</td>';
                    echo '<td>';
                    input_money(NULL,"ob[$code][credit]",money_total_format($item['credit']) , curr_default() );
                    echo '</td>';
                    echo '</tr>';

                    $debit_total+=$item['debit'];
                    $credit_total+=$item['credit'];
                }
        
            }
            $i++;
        }
        echo '<tr><td class="textright textbold" >Total</td>';
        echo '<td class="textright" >'.money_total_format($debit_total).'</td>';
        echo '<td class="textright">'.money_total_format($credit_total).'</td>';
        echo '</tr>';
        echo '<input type="hidden" name="type" value="gl">';
        end_table(1);
        
        box_footer_start();
        submit('submit', _("Submit"), true, '', 'default','save');
        //submit('submit', _("Submit"), true, '', false);
        box_footer_end();
        box_end();
        end_form(1);
    }
    
    private function check_tran_date(){
        $exist_date = get_instance()->db->select('tran_date')->where('amount !=',0)->get('opening_gl',1)->row();
        
        if( $exist_date && isset($exist_date->tran_date) ){
            $tran_date = date('d-m-Y',strtotime($exist_date->tran_date) );
        } else {
            $current_year = get_current_fiscalyear();
            $date = new DateTime(date2sql($current_year['begin']));
            $date->modify('-1 day');
            $tran_date = $date->format('d-m-Y');
        }
        return $tran_date;
    }
    
    private function submit_check(){
        global $ci;

        $tran_date = date2sql($_POST['trans_date']);
        $debits = input_post("debit");
        $credits = input_post('credit');

        $opening_value = input_post("ob");
        if( is_array($opening_value) AND !empty($opening_value)){
            foreach ($opening_value AS $account_code => $ob){
                 $amount = 0;
                 $ob_type = NULL;
                 if( isset($ob['debit']) AND floatval($ob['debit']) > 0 ){
                     $amount = strtonumber($ob['debit']);
                     $ob_type = "debit";
                 }
                 
                 if( isset($ob['credit']) AND floatval($ob['credit']) > 0 ){
                     $amount = -strtonumber($ob['credit']);
                     $ob_type = "credit";
                 }
                 
                 if( $ob_type != NULL AND floatval($amount) != 0 ){
                     $newData = array(
                             'amount' =>$amount,
                             'type'=>ST_OPENING_GL,
                             'account'=>$account_code,
                             'tran_date'=>$tran_date
                     );
                      
                     $this->ob_gl_model->update_gl_account($newData,$ob_type);
                 }
                 
            }
        }
        die();
        if( is_array($debits) ){
            foreach ($debits AS $acc_code=>$debit){
                if( $debit != '' ){
                    $newData = array(
                            'amount' =>strtonumber($debit),
                            'type'=>ST_OPENING_GL,
                            'account'=>$acc_code,
                            'tran_date'=>$tran_date
                    );
        
                    $model->update_gl_account($newData,'debit');
                }
            }
        }
        
        if( is_array($credits) ){
            foreach ($credits AS $acc_code=>$credit){ if( $credit != '' ){
                $newData = array(
                        'amount' =>-strtonumber($credit),
                        'type'=>ST_OPENING_GL,
                        'account'=>$acc_code,
                        'tran_date'=>$tran_date
                );
                bug($newData);die;
                $model->update_gl_account($newData,'credit');
            }}
        }
        bug($credits);
        bug($debits);
        die;
        display_notification(_("GL Account Opening Balance has been updated."));
        $this->reset();
    }
}