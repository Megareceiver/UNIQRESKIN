<?php
$path_to_root="..";
include($path_to_root . "/includes/session.inc");
global $ci;

$ci->load_library('reporting');


$data = array();
// $rep_type = ST_JOURNAL;
$rep_type = $ci->input->get('REP_ID');

$ci->reporting->get_items($rep_type);
$ci->reporting->do_report();

?>

<html>
     <head>
         <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">

         <link rel="stylesheet" href="../themes/<?php echo user_theme()?>/css/reporting.css">

     </head>
     <body>
		<?php echo $html;?>
     </body>
</html>