<?php
class at_references
{
    var $db;
    function __construct(){
    }
    //
    //	Get reference from refs table for given transaction.
    //	Used for transactions which do not hold references (journal and bank).
    //
    function get($type, $id)
    {
        $ref = $this->db->where(array('id'=>$id,'type'=>$type))->select('reference')->get('refs')->row();
        return ( is_object($ref) && $ref->reference ) ? $ref->reference : NULL;
    }
    //
    // Check if reference is used for any non voided transaction (used for ST_JOURNALENTRY type)
    //
    function exists($type, $reference)
    {
        return (find_reference($type, $reference) != null);
    }
    //
    // Get default reference on new transaction creation.
    //
    function get_next($type)
    {

        $ref = $this->db->select('next_reference')->where('type_id',$type)->get('sys_types');
        //$sql = "SELECT next_reference FROM ".TB_PREF."sys_types WHERE type_id = ".db_escape($type);
        $str = NULL;
        if( $ref->num_rows > 0 ){
            $ref_row = $ref->row();
            $str = $ref_row->next_reference;
        } else {
            //The last transaction ref for $type could not be retreived
        }
        return $str;
    }
    //
    // Check reference is valid before add/update transaction.
    //
    function is_valid($reference)
    {
        return strlen(trim($reference)) > 0;
    }
    //
    //	Save reference (and prepare next) on write transaction.
    //
    function save($type, $id, $reference) {
        update_reference($type, $id, $reference); // store in refs table

        if ($reference == $this->get_next($type)) { // if reference was bigger or not changed from default
            $next = $this->_increment($reference);	// increment default
            $this->save_next_reference($type, $next);
        } else {
            $next = $this->_increment($reference);
            $this->save_next_reference($type, $next);
        }
        // 		die('save refecence');
    }

    private function save_next_reference($type, $reference)
    {
        $this->db->where('type_id',$type)->update('sys_types',array('next_reference'=>trim($reference)));
    }
    //
    // Restore previous reference (if possible) after voiding transaction.
    //
    function restore_last($type, $id)
    {
        $reference = get_reference($type, $id);
        $prev = $this->_increment($this->get_next($type), true); //decrement
        if ($reference== $prev) {
            save_next_reference($type, $prev);
        }
    }
    //-----------------------------------------------------------------------
    //
    //	Increments (or decrements if $back==true) reference template
    //
    function _increment($reference, $back=false)
    {
        // New method done by Pete. So f.i. WA036 will increment to WA037 and so on.
       	// If $reference contains at least one group of digits,
        // extract first didgits group and add 1, then put all together.
        // NB. preg_match returns 1 if the regex matches completely
        // also $result[0] holds entire string, 1 the first captured, 2 the 2nd etc.
        //
        if (preg_match('/^(\D*?)(\d+)(.*)/', $reference, $result) == 1)
        {
            list($all, $prefix, $number, $postfix) = $result;
            $dig_count = strlen($number); // How many digits? eg. 0003 = 4
            $fmt = '%0' . $dig_count . 'd'; // Make a format string - leading zeroes
            $val = intval($number + ($back ? ($number<1 ? 0 : -1) : 1));
            $nextval =  sprintf($fmt, $val); // Add one on, and put prefix back on

            return $prefix.$nextval.$postfix;
        }
        else
            return $reference;
    }
}