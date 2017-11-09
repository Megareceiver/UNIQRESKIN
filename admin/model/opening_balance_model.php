<?php
class opening_balance_model{

	function chart_master(){
		$data = array();

		$sql = "SELECT chart.account_code, chart.account_name, type.name, chart.inactive, type.id
		FROM (".TB_PREF."chart_master chart,".TB_PREF."chart_types type) "
		."LEFT JOIN ".TB_PREF."bank_accounts acc "
		."ON chart.account_code=acc.account_code
		WHERE chart.account_type=type.id AND chart.inactive=0";

		/* acc.account_code  IS NULL AND  */

		if($result = db_query($sql)) {
			while ($row = db_fetch($result)) {
				if( !isset($data[ $row['name'] ]) ){
					$data[ $row['name'] ] = array();
				}
				$data[ $row['name'] ][ $row['account_code'] ] = $row['account_name'];
			}

		}

		return $data;
	}
}