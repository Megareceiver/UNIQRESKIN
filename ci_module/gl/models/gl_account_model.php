<?php

class Gl_GL_Account_Model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        // $this->void_model = module_model_load('tran','void');
        // $this->allocation_model = module_model_load('allocation','gl');
    }

    public function get_classes( $all = false, $balance = -1)
    {
        if (!$all) {
            $this->db->where("inactive != 1");
        }

        if ($balance == 0) {
            $this->db->where("(ctype > ".CL_EQUITY ." OR ctype=0)");
            $this->db->or_where('ctype', 0);
        } elseif ($balance == 1) {
            $this->db->where('ctype <', CL_INCOME);
            $this->db->where('ctype >', 0);
        }

        $result = $this->db->order_by('ctype, cid')->get('chart_class');
        if (! is_object($result)) {
            check_db_error('could not get account classes', $this->db->last_query());
            return NULL;
        }
        $class_items = $result->result();

        return $class_items;
    }

    public function get_accounts_classes($all = false, $balance = -1)
    {
        $classes = $this->get_classes($all, $balance);
        if (count($classes) > 0)
            foreach ($classes as $k => $class) {
                $types = $this->get_types(false, $class->cid, false);
                $classes[$k]->types = $types;
            }
        return $classes;
    }

    function get_types($all = false, $class_id = false, $parent = false)
    {
        if (! $all) {
            $this->db->where('inactive <>', 1);
        }

        if ($class_id != false) {
            $this->db->where('class_id', $class_id);
        }

        if ($parent == - 1) {
            $this->db->where("(parent = '' OR parent = '-1')");
        } elseif ($parent !== false) {
            $this->db->where('parent', $parent);
        }

        $result = $this->db->order_by('class_id, id, parent')->get('chart_types');

        if (! is_object($result)) {
            check_db_error('could not get account types', $this->db->last_query());
            return NULL;
        }

        $type_items = $result->result();

        return $type_items;
    }

    function get_accounts($from = null, $to = null, $type = null)
    {
        $this->db->select("chart_master.*, chart_types.name AS AccountTypeName");
        $this->db->from('chart_master');
        $this->db->left_join('chart_types', 'chart_types.id=chart_master.account_type');
        if ($type != null) {
            $this->db->where('account_type', $type);
        }

        if ($from != null) {
            $this->db->where('chart_master.account_code >=', $from);
        }

        if ($to != null) {
            $this->db->where('chart_master.account_code <=', $from);
        }

        $result = $this->db->order_by('account_code')->get();
        if (! is_object($result)) {
            check_db_error('could not get gl accounts', $this->db->last_query());
            return NULL;
        }
        return $result->result();
    }

    function get_balance($account, $dimension, $dimension2, $from, $to, $from_incl = true, $to_incl = true)
    {
        $this->db->select("SUM(IF(gl.amount >= 0, amount, 0)) as debit", false);
        $this->db->select("SUM(IF(gl.amount < 0, -amount, 0)) as credit", false);
        $this->db->select("SUM(gl.amount) as balance", false);

        $this->db->from('gl_trans AS gl');
        $this->db->left_join('chart_master AS acc', 'acc.account_code = gl.account');
        $this->db->left_join('chart_types AS accType', 'accType.id = acc.account_type');
        $this->db->left_join('chart_class AS accClass', 'accClass.cid = accType.class_id');

        // $sql = "SELECT SUM(IF(amount >= 0, amount, 0)) as debit,
        // SUM(IF(amount < 0, -amount, 0)) as credit, SUM(amount) as balance
        // FROM ".TB_PREF."gl_trans,".TB_PREF."chart_master,"
        // .TB_PREF."chart_types, ".TB_PREF."chart_class
        // WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code AND "
        // .TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id
        // AND ".TB_PREF."chart_types.class_id=".TB_PREF."chart_class.cid AND";

        if ($account != null) {
            // $sql .= " account=".db_escape($account)." AND";
            $this->db->where('gl.account', $account);
        }

        if ($dimension != 0) {
            // $sql .= " dimension_id = ".($dimension<0?0:db_escape($dimension))." AND";
            $this->db->where('gl.dimension_id', $dimension < 0 ? 0 : dimension);
        }

        if ($dimension2 != 0) {
            // $sql .= " dimension2_id = ".($dimension2<0?0:db_escape($dimension2))." AND";
            $this->db->where('gl.dimension2_id', $dimension2 < 0 ? 0 : $dimension2);
        }

        // $from_date = date2sql($from);
        if ($from_incl) {
            // $sql .= " tran_date >= '$from_date' AND";
            $this->db->where('gl.tran_date >=', date2sql($from));
        } else {
            // $sql .= " tran_date > IF(ctype>0 AND ctype<".CL_INCOME.", '0000-00-00', '$from_date') AND";
            $this->db->where("gl.tran_date > IF(accClass.ctype >0 AND accClass.ctype<" . CL_INCOME . ", '0000-00-00', '" . date2sql($from) . "') ");
        }

        if ($to_incl) {
            // $sql .= " tran_date >= '$from_date' AND";
            $this->db->where('gl.tran_date <=', date2sql($to));
        } else {
            $this->db->where('gl.tran_date <', date2sql($to));
        }

        // $to_date = date2sql($to);
        // if ($to_incl)
        // $sql .= " tran_date <= '$to_date' ";
        // else
        // $sql .= " tran_date < '$to_date' ";
        // $this->db->where('gl.type NOT IN (95,96,0)');
        $this->db->where('gl.type IN (0)');
        $result = $this->db->get();
        if (! is_object($result)) {
            check_db_error('No general ledger accounts were returned', $this->db->last_query());
            bug($this->db->last_query());
            return NULL;
        }
        // if( $account==5900 ){
        // bug($from);
        // bug($this->db->last_query());die;
        // }
        return $result->row();
    }

    function account_name($code)
    {
        $data = $this->db->where('account_code', $code)->get('chart_master');

        if (is_object($data) && $data->num_rows > 0) {
            return $data->row();
        } else {
            display_db_error("could not retreive the account name for $code", $this->db->last_query(), true);
        }
    }


}