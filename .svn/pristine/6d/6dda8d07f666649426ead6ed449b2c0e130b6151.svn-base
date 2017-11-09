<?php
function db_has_manufacturable_items()
{
    return check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."stock_master WHERE (mb_flag='M')");
}

function check_db_has_manufacturable_items($msg)
{
    if (!db_has_manufacturable_items())
    {
        msg_danger($msg);
        return true;
    }
    return false;
}