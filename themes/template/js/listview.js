$(document).ready(function(){
	listview.int();
});

var listview = {
	int: function(){
//		$('button[name=AddItem]').click(function(){
//			var tr = $(this).parents('tr');
//			var newRow = tr.clone();
//			newRow.find('button').attr({'title':'Remove','value':'Remove','name':'remove'}).find('span').html('Remove');
////			newRow.find('select').val( tr.find('select').val() );
//			tr.before( newRow );
//			
//			tr.find('input[type=text]').val('');
//			
//			
//			listview.removeLine();
//		});
		this.removeLine();
	},
	removeLine : function(){
		$('button[name=remove]').unbind('click').click(function(){
			$(this).parents('tr').remove();
		});
	}
};