<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL,
	as published by the Free Software Foundation, either version 3
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
include_once($path_to_root . "/includes/remote_url.inc");
//---------------------------------------------------------------------------------------------
function get_exchange_rate($rate_id)
{
	$sql = "SELECT * FROM ".TB_PREF."exchange_rates WHERE id=".db_escape($rate_id);
	$result = db_query($sql, "could not get exchange rate for $rate_id");

	return db_fetch($result);
}

// Retrieves buy exchange rate for given currency/date, zero if no result
function get_date_exchange_rate($curr_code, $date_)
{
	$date = date2sql($date_);
	$sql = "SELECT rate_buy FROM ".TB_PREF."exchange_rates WHERE curr_code=".db_escape($curr_code)
	." AND date_='$date'";
	$result = db_query($sql, "could not get exchange rate for $curr_code - $date_");

	if(db_num_rows($result) == 0)
		return 0;
	$row = db_fetch($result);
		return $row[0];
}

//---------------------------------------------------------------------------------------------

function update_exchange_rate($curr_code, $date_, $buy_rate, $sell_rate)
{
	if (is_company_currency($curr_code))
		display_db_error("Exchange rates cannot be set for company currency", "", true);

	$date = date2sql($date_);

	$sql = "UPDATE ".TB_PREF."exchange_rates SET rate_buy=$buy_rate, rate_sell=".db_escape($sell_rate)
	." WHERE curr_code=".db_escape($curr_code)." AND date_='$date'";

	db_query($sql, "could not add exchange rate for $curr_code");
}

//---------------------------------------------------------------------------------------------

function add_exchange_rate($curr_code, $date_, $buy_rate, $sell_rate)
{
	if (is_company_currency($curr_code))
		display_db_error("Exchange rates cannot be set for company currency", "", true);

	$date = date2sql($date_);

	$sql = "INSERT INTO ".TB_PREF."exchange_rates (curr_code, date_, rate_buy, rate_sell)
		VALUES (".db_escape($curr_code).", '$date', ".db_escape($buy_rate)
		.", ".db_escape($sell_rate).")";

	db_query($sql, "could not add exchange rate for $curr_code");
}

//---------------------------------------------------------------------------------------------

function add_new_exchange_rate($curr_code, $date_, $ex_rate){
	if (is_company_currency($curr_code) || !$ex_rate || $ex_rate==1 )
		return;

	if (!get_date_exchange_rate($curr_code, $date_))
		add_exchange_rate($curr_code, $date_, $ex_rate, $ex_rate);
}

//---------------------------------------------------------------------------------------------

function delete_exchange_rate($rate_id)
{
	$sql = "DELETE FROM ".TB_PREF."exchange_rates WHERE id=".db_escape($rate_id);
	db_query($sql, "could not delete exchange rate $rate_id");
}


//-----------------------------------------------------------------------------


//-----------------------------------------------------------------------------

function get_sql_for_exchange_rates()
{
	$sql = "SELECT date_, rate_buy, id FROM "
		.TB_PREF."exchange_rates "
		."WHERE curr_code=".db_escape($_POST['curr_abrev'])."
		 ORDER BY date_ DESC";
	return $sql;
}
?>