<?php

//-----------------------------------------------------------------------------
//	Retrieve exchange rate as of date $date from external source (usually inet)
//
//	Exchange rate for currency revaluation purposes is defined in FA as home_currency/curr_b ratio i.e.
//
//	amount [home] = amount [curr] * ex_rate
//
function retrieve_exrate($curr_b, $date)
{
    global $xr_providers, $dflt_xr_provider;
    $xchg_rate_provider = ((isset($xr_providers) && isset($dflt_xr_provider)) ? $xr_providers[$dflt_xr_provider] : 'ECB');

    $rate = hook_retrieve_exrate($curr_b, $date);
    if (is_numeric($rate))
        return $rate;
    return get_extern_rate($curr_b, $xchg_rate_provider, $date);
}

function get_extern_rate($curr_b, $provider = 'GOOGLE', $date)
{
    global $path_to_root;
    $provider = 'GOOGLE';
    if ($date != Today()) // no historical rates available
        return 0;

    $curr_a = get_company_pref('curr_default');
    if ($provider == 'ECB') {
        $filename = "/stats/eurofxref/eurofxref-daily.xml";
        $site = "www.ecb.int";
    } elseif ($provider == 'YAHOO') {
        $filename = "/d/quotes.csv?s={$curr_a}{$curr_b}=X&f=sl1d1t1ba&e=.csv"; // new URL's for YAHOO
        $site = "download.finance.yahoo.com";
        // $filename = "/q?s={$curr_a}{$curr_b}=X"; // Let old code be here for a while, Joe.
        // $site = "finance.yahoo.com";
    } elseif ($provider == 'GOOGLE') {
        $filename = "/finance/converter?a=1&from={$curr_a}&to={$curr_b}";
        $site = "www.google.com";
    } elseif ($provider == 'BLOOMBERG') {
        $filename = "/quote/{$curr_b}{$curr_a}:CUR";
        $site = "www.bloomberg.com";
    }

    $contents = '';
    if (function_exists('curl_init')) {
        // first check with curl as we can set short timeout;
        $retry = 1;
        do {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://' . $site . $filename);
            curl_setopt($ch, CURLOPT_COOKIEJAR, "$path_to_root/tmp/cookie.txt");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // prevent warning while save_mode/open_basedir on (redireciton doesn't occur at least on ECB page)
            if (! ini_get('save_mode') && ! ini_get('open_basedir'))
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $contents = curl_exec($ch);
            curl_close($ch);
            // due to resolver bug in some curl versions (e.g. 7.15.5)
            // try again for constant IP.
            $site = "195.128.2.97";
        } while (($contents == '') && $retry --);
    } else {
        $contents = url_get_contents("http://" . $site . $filename);
    }

    if ($provider == 'ECB') {
        $contents = str_replace("<Cube currency='USD'", " <Cube currency='EUR' rate='1'/> <Cube currency='USD'", $contents);
        $from_mask = "|<Cube\s*currency=\'" . $curr_a . "\'\s*rate=\'([\d.,]*)\'\s*/>|i";
        preg_match($from_mask, $contents, $out);
        $val_a = isset($out[1]) ? $out[1] : 0;
        $val_a = str_replace(',', '', $val_a);
        $to_mask = "|<Cube\s*currency=\'" . $curr_b . "\'\s*rate=\'([\d.,]*)\'\s*/>|i";
        preg_match($to_mask, $contents, $out);
        $val_b = isset($out[1]) ? $out[1] : 0;
        $val_b = str_replace(',', '', $val_b);
        if ($val_b) {
            $val = $val_a / $val_b;
        } else {
            $val = 0;
        }
    } elseif ($provider == 'YAHOO') {
        $val = '';
        $array = explode(',', $contents); // New operations for YAHOO. Safer.
        $val = $array[1];
        if ($val != 0)
            $val = 1 / $val;
        /*
         * Let old code be here for a while, Joe.
         * //if (preg_match('/Last\sTrade:(.*?)Trade\sTime/s', $contents, $matches)) {
         * $val = strip_tags($matches[1]);
         * $val = str_replace(',', '', $val);
         * if ($val != 0)
         * $val = 1 / $val;
         * }
         */
    } elseif ($provider == 'GOOGLE') {
        $val = '';

        $regexp = "%([\d|.]+)\s+{$curr_a}\s+=\s+<span\sclass=(.*)>([\d|.]+)\s+{$curr_b}\s*</span>%s";
        if (preg_match($regexp, $contents, $matches)) {
            $val = $matches[3];
            $val = str_replace(',', '', $val);
            if ($val != 0)
                $val = 1 / $val;
        }
    } elseif ($provider == 'BLOOMBERG') {
        $val = '';
        $stmask = '<span class=" price">';
        $val = trim(strstr($contents, $stmask));
        $stmask = chr(10);
        $val = trim(strstr($val, $stmask));
        $val = trim(strtok($val, $stmask));
    }
    return $val;
} /* end function get_extern_rate */



function get_exchange_rate_from_home_currency($currency_code, $date_ = null)
{
    if (is_company_currency($currency_code) || $currency_code == get_company_currency() || $currency_code == null)
        return 1.0000;

        // $date = date2sql($date_);

    global $ci;
    $ci->db->reset();
    $ci->db->select('rate_buy, max(date_) as date_')->where(array(
        'curr_code' => $currency_code
    ));
    if ($date_) {
        $ci->db->where('date_ <=', date('Y-m-d', strtotime($date_)));
    }
    $row = $ci->db->group_by('rate_buy')
        ->order_by('date_ Desc')
        ->get('exchange_rates')
        ->row();
    // bug( $ci->db->last_query() );

    // $sql = "SELECT FROM ".TB_PREF." WHERE = ".db_escape()."
    // AND date_ <= '$date' GROUP BY rate_buy ORDER BY date_ Desc LIMIT 1";

    // $result = db_query($sql, "could not query exchange rates");

    if (! $row || ! isset($row->rate_buy)) {
        // return get_extern_rate($currency_code);
        // no stored exchange rate, just return 1
        if (! isset($_POST['ex_rate_allow']) || $_POST['ex_rate_allow'] != 1) {

            display_error(_("Cannot retrieve exchange rate for currency $currency_code as of " . $date_ . ". Please add exchange rate manually on Exchange Rates page."));
        }

        return 1.000;
    }

    // $myrow = db_fetch_row($result);
    return $row->rate_buy;
}