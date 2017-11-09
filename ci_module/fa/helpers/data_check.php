<?php

function check_empty_result($sql)
{
    $result = db_query($sql, "could not do check empty query");

    $myrow = db_fetch_row($result);
    return $myrow[0] > 0;
}