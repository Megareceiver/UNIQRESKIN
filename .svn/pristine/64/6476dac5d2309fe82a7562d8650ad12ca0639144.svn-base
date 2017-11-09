
ajax_fun.tableHeader = function(){
    var tables = jQuery('table.table-headfixed');
    if( tables.length > 0){
	tables.find('thead');
	    tables.parent('.table-responsive').prepend('<div class="tableheadfixed" ><table class="table-striped"><thead>'+tables.find('thead').html()+'</thead></table></div>');

	    var max_top = $(".tableheadfixed").offset().top;
	    $(window).scroll(function(){
		var scrollTop = $(window).scrollTop();
		if ( scrollTop > max_top ) {
		    $(".tableheadfixed").css({"margin-top":scrollTop-max_top });
		} else {
		    $(".tableheadfixed").css({"margin-top":0 });
		}
	    });
    }


};