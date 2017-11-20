/*syncard js*/
var allText = '';
var table_flex = '';
$("html").attr('style', 'opacity:1');

$(function(){
dropdown_menu();
getItemSelect();
autocomplete();
getAccountSelect();
// hideBranch();
getGlAcc();
noSelect();
noSelectActive();
noSelectGl();
noSelectActiveGl();
SetupJav();
dropdown_search();
// getAllLanguage();
// selLang();
//processLang(r_getCookie('uniq365_langIndex'));
	// popitup();

//---------------document printing 
print_invoice();
print_creditnote();
print_deliverynote();
print_salesorder();
print_salesquot();
print_payment();
print_purchaseorder();
print_remittance();
print_paymentvoucher();
print_depositvoucher();
print_transfervoucher();
});

function SetupJav(){
	$('body [name=domicile]').attr({
		'readonly':'true',
		'style':'background-color:#ddd'
	});
}
function popitup(url) {
	window.open(url,'name','height=600,width=850');
	// if (window.focus) {newwindow.focus();}
	return false;
}

function getItemSelect(){
	$('body').on("keyup",'tr [rel=stock_id]',function(e){
		if(e.keyCode == 13){
		var value = $(this).val();
		value = value.toUpperCase();
		var opt;
		$('table tbody tr [rel=_stock_id_edit] option:contains('+value+')').prop({selected: true});
		opt = $('table tbody tr [rel=_stock_id_edit]').find(":selected").text();
		$('table tbody tr #_stock_id_sel span.filter-option').html(opt);
		$('#d-id').remove();
		$('table tbody tr #AddItem').removeAttr('disabled');
		}
	});
}
function noSelect(){
	html = '-- Select --';
	var nah ='';
	ini  = '<option selected value=no>-- Select -- </option>';
	setTimeout(function(){
		$('table tbody tr #_stock_id_sel span.filter-option').html(html);
		// $('table tbody tr [rel=_stock_id_edit]').prepend(ini);
		nah = $('table tbody tr #_stock_id_sel span.filter-option').text();
		if( nah == '-- Select --'){
			$('table tbody tr #AddItem').attr('disabled','disabled');
		}
	},1000);
}
function noSelectActive(){
	$('body').on('click','table tbody tr #AddItem',function(){
		setTimeout(function(){
			noSelect();	

		},500);
	});

}
function noSelectGl(){
	var opt ='';
	var nah = '';
	setTimeout(function(){
		$('table tbody tr [name=_code_id_edit]').val('');
		$('table tbody tr [name=code_id] option:eq(0)').attr('selected','selected').val('no');
		nah = $('table tbody tr [name=code_id]').find(":selected").text();
		$('table tbody tr #_code_id_sel span.filter-option').html(nah);
		$('table tbody tr #AddItem').attr('disabled','disabled');
		},1000);
}
function noSelectActiveGl(){
	var id = '';
	$('body').on('change','table tbody tr [name=code_id]',function(){
		id = $('table tbody tr [name=code_id]').val();
		if(id != 'no'){
			$('table tbody tr #AddItem').removeAttr('disabled');
		}else{
			$('table tbody tr #AddItem').attr('disabled','disabled');
		}
	})
	$('body').on('click','table tbody tr #AddItem',function(){
		setTimeout(function(){
			noSelectGl();	
		},500);
	});

}

function hideBranch(){
	$('body').on("change",'[rel=_customer_id_edit]',function(e){
		var len = $('[name=branch_id] option').length;
		// if(len <= 2){
			// $('body').on('change','[name=branch_id]',function(){
				setTimeout(function(){
				$('#_branch_id_sel').parent().parent().hide();
				},2000);
			// });
			// return false;
		// }
	});
}

function getGlAcc(){
	$('body').on("keyup",'tr [name=_gl_code_edit]',function(e){
		if(e.keyCode == 13){
		var value = $(this).val();
		var opt;
		$('table tbody tr [name=gl_code] option:contains('+value+')').prop({selected: true});
		opt = $('table tbody tr [name=gl_code]').find(":selected").text();
		$('table tbody tr #_gl_code_sel span.filter-option').html(opt);
		}
	});
}

function autocomplete(){
	$('body').on('keyup','[rel=stock_id]',function(e){
		if(e.keyCode != 13){
			var value = $(this).val();
			if(value !=''){
			var res;
			value = value.toUpperCase();
			var options = $('table tbody tr [rel=_stock_id_edit] option');
			var values = $.map(options ,function(option) {
			    return option.value+'%'+option.text ;
			});
			var sel;
			var relevantSelects = [];
			// var relevantSelects2 = [];
			for(var z=0; z<values.length; z++){
			     sel = values[z];
			     if(sel.indexOf(value) === 0){
			         relevantSelects.push(sel);
			     }
			}			
			var html =  '<div class=syncard-dropdown id=d-id>'+
							'<ul>';
								for(var a = 0; a < relevantSelects.length; a++){
									var nah = relevantSelects[a].split('%');
									html = html + '<li syncard-id='+nah[0]+'>'+nah[1]+'</li>';
								}
							html = html + '</ul></div>';
			$('table tbody tr [rel=stock_id]').parent().attr('style','position:relative');
			$('#d-id').remove();
			$('table tbody tr [rel=stock_id]').parent().append(html);
			}else{
			$('#d-id').remove();
			}
		}
	});

	$('body').on('click','#d-id li',function(){
		var value = $(this).attr('syncard-id');
		$('table tbody tr [rel=stock_id]').val(value);
		var opt;
		$('table tbody tr [rel=_stock_id_edit] option:contains('+value+')').prop({selected: true});
		opt = $('table tbody tr [rel=_stock_id_edit]').find(":selected").text();
		$('table tbody tr #_stock_id_sel span.filter-option').html(opt);
		$('#d-id').remove();
		$('table tbody tr #AddItem').removeAttr('disabled');
	});
}

function getAccountSelect(){
	/*show account after select*/
	$('body').on('change','table tbody tr [name=code_id]',function(){
		var opt;
		opt = $('table tbody tr [name=code_id]').find(":selected").text();
		var nah = opt.split(/(\s+\s+\s+\s+)/);
		$('table tbody tr [name=_code_id_edit]').val(nah[0]);
	});
	$('body').on("keyup",'tr [name=_code_id_edit]',function(e){
		if(e.keyCode == 13){
		var value = $(this).val();
		value = value.toUpperCase();
		var opt;
		$('table tbody tr [name=code_id] option:contains('+value+')').prop({selected: true});
		opt = $('table tbody tr [name=code_id]').find(":selected").text();
		$('table tbody tr #_code_id_sel span.filter-option').html(opt);
		$('#d-id').remove();
		$('table tbody tr #AddItem').removeAttr('disabled');
		}
	});

	/*autocomplete*/

	$('body').on('keyup','[name=_code_id_edit]',function(e){
		if(e.keyCode != 13){
			var value = $(this).val();
			if(value !=''){
			var res;
			value = value.toUpperCase();
			// var opt;
			var options = $('table tbody tr [name=code_id] option');
			var values = $.map(options ,function(option) {
			    return option.value+'%'+option.text ;
			});
			var sel;
			var relevantSelects = [];
			for(var z=0; z<values.length; z++){
			     sel = values[z];
			     if(sel.indexOf(value) === 0){
			         relevantSelects.push(sel);
			     }
			}
			var html =  '<div class=syncard-dropdown id=d-id>'+
							'<ul>';
								for(var a = 0; a < relevantSelects.length; a++){
									var nah = relevantSelects[a].split('%');
									html = html + '<li syncard-id='+nah[0]+'>'+nah[1]+'</li>';
								}
							html = html + '</ul></div>';
			$('table tbody tr [name=_code_id_edit]').parent().attr('style','position:relative');
			$('#d-id').remove();
			$('table tbody tr [name=_code_id_edit]').parent().append(html);
			}else{
			$('#d-id').remove();
			}
		}
	});

	$('body').on('click','#d-id li',function(){
		var value = $(this).attr('syncard-id');
		$('table tbody tr [name=_code_id_edit]').val(value);
		var opt;
		$('table tbody tr [name=code_id] option:contains('+value+')').prop({selected: true});
		opt = $('table tbody tr [name=code_id]').find(":selected").text();
		$('table tbody tr #_code_id_sel span.filter-option').html(opt);
		$('#d-id').remove();
		$('table tbody tr #AddItem').removeAttr('disabled');
	});
}

function getAllLanguage(){
	/*see data from cookie*/
	/*get lang from csv*/
	base_url = window.location.origin;
	 $.ajax({
        url: base_url+'/assets/lang/vocab.csv',
        type: 'GET',
        dataType : 'text',
        async: false,
        success: function (data){
            allText = data;
        }
    });

	splitText = allText.split('\n');
	var flag = splitText[0].split(';');
	var def_lang = $('[name="lang_session"]').val();
	// if (def_lang == null){def_lang=''}
	for(var a = 0; a < flag.length; a++){
		if(a == def_lang){
			$('#selLang').append('<option  selected value='+ a +'>'+ flag[a] + '</option>');
		}else{
			$('#selLang').append('<option value='+ a +'>'+ flag[a] + '</option>');
		}
		
		if(a == $('[name="default_language"]').val()){
			$('[name="coy_def_language"]').append('<option selected value='+ a +'>'+ flag[a] + '</option>');	
		}else{
			$('[name="coy_def_language"]').append('<option value='+ a +'>'+ flag[a] + '</option>');			
		}
	}
	// $('[name="select_language"] option[value="2"]').prop('selected',true);
	var lang_cookie = r_getCookie('uniq365_langIndex');
	if(lang_cookie == ''){
		processLang(def_lang);
		// console.log(def_lang);
	}else{
		processLang(lang_cookie);
		// console.log(lang_cookie);
	}
}

function selLang(){
	$('body').on('change', '#selLang', function(){
		var ie = $(this).val();
		processLang(ie);
		return false;
	});
}

function processLang(newLang){
	if(newLang==''){newLang = 0}
	var str = '';
	var splitSplitText = [];
	var ini = [];
	var newText = [];
	var oldText = [];
	var oldTextSort = [];
	var lastLang = r_getCookie('uniq365_langIndex');
	if(lastLang == ''){lastLang = 0}
	lastLang = (newLang == lastLang) ? 0 : lastLang;

	splitText = allText.split('\n');
	for(var a = 1; a < splitText.length -1 ; a++){
		tempString = splitText[a].split(';');
		splitSplitText.push(tempString);
	}

	for(var b = 0; b < splitSplitText.length; b++){
		if(splitSplitText[b][lastLang].trim() == ''  || splitSplitText[b][newLang].trim() == '' || splitSplitText[b][newLang].trim() == null){
			temp = newLang;
			tempLast = lastLang;
			if(splitSplitText[b][lastLang].trim() == '') { lastLang = 0; }
			if(splitSplitText[b][newLang].trim() == '' || splitSplitText[b][newLang].trim() == null) { newLang = 0; }
			tempSringLang = splitSplitText[b][lastLang];
			tempNewSringLang = splitSplitText[b][newLang];
			newLang = temp;
			lastLang = tempLast;
			oldText.push(tempSringLang);
			newText.push(tempNewSringLang);
			
		}else{
			tempSringLang = splitSplitText[b][lastLang];
			tempNewSringLang = splitSplitText[b][newLang];
			oldText.push(tempSringLang);
			oldTextSort.push(tempSringLang);
			newText.push(tempNewSringLang);
			// tempSringLang = new RegExp(splitSplitText[b][lastLang],"g");
			// tempSringLang = splitSplitText[b][lastLang];
			// tempNewSringLang = splitSplitText[b][newLang];
			// document.body.innerHTML = document.body.innerHTML.replace(tempSringLang, tempNewSringLang);
			// document.body.innerHTML = document.body.innerHTML.replace(new RegExp('<span>'+tempSringLang+'</span>',"g"), '<span>'+tempNewSringLang+'<span>');
			// document.body.innerHTML = document.body.innerHTML.replace(new RegExp('<h4>'+tempSringLang+'</h4>',"g"), '<h4>'+tempNewSringLang+'</h4>');
			// document.body.innerHTML = document.body.innerHTML.replace(new RegExp('</i> '+tempSringLang+'</a>',"g"), '</i>'+tempNewSringLang+'</a>');
			// document.body.innerHTML = document.body.innerHTML.replace(new RegExp('</i> '+tempSringLang,"g"), '</i>'+tempNewSringLang);
		}
	}
	oldTextSort.sort(function (a,b){
		return b.length - a.length;
	});
	for(var a = 0; a < oldTextSort.length; a++){
		var withFind = $('body:contains("'+ oldTextSort[a] +'")');
			if(withFind.length > 0){
				var indexNa = oldText.indexOf(oldTextSort[a]);
				// document.body.innerHTML = document.body.innerHTML.replace(oldText[indexNa], newText[indexNa]);
				// document.body.innerHTML = document.body.innerHTML.replace(oldText[indexNa], newText[indexNa]);
				// document.body.innerHTML = document.body.innerHTML.replace(new RegExp(oldText[indexNa], "g"), newText[indexNa]);
				document.body.innerHTML = document.body.innerHTML.replace(new RegExp('label">'+oldText[indexNa]+'</label>',"g"), 'label">'+newText[indexNa]+'</label>');
				document.body.innerHTML = document.body.innerHTML.replace(new RegExp('<span>'+oldText[indexNa]+'</span>',"g"), '<span>'+newText[indexNa]+'</span>');
				document.body.innerHTML = document.body.innerHTML.replace(new RegExp(oldText[indexNa]+' 			</h1>',"g"),newText[indexNa]+' 			</h1>');
				document.body.innerHTML = document.body.innerHTML.replace(new RegExp('</i> '+oldText[indexNa]+'</a>',"g"), '</i> '+newText[indexNa]+'</a>');
				document.body.innerHTML = document.body.innerHTML.replace(new RegExp('<h4>'+oldText[indexNa]+'</h4>',"g"), '<h4>'+newText[indexNa]+'</h4>');
				document.body.innerHTML = document.body.innerHTML.replace(new RegExp(oldText[indexNa]+'</button>',"g"),newText[indexNa]+'</button>');
				document.body.innerHTML = document.body.innerHTML.replace(new RegExp('</i>  '+oldText[indexNa],"g"),  '</i> '+newText[indexNa]);
				document.body.innerHTML = document.body.innerHTML.replace(new RegExp('</i> '+oldText[indexNa],"g"), '</i> '+newText[indexNa]);
				document.body.innerHTML = document.body.innerHTML.replace(new RegExp(oldText[indexNa]+'</th>',"g"), newText[indexNa]+'</th>');
				document.body.innerHTML = document.body.innerHTML.replace(new RegExp(oldText[indexNa]+'</td>',"g"), newText[indexNa]+'</td>');

			}
	}
	if(newLang == 1){
		// document.body.innerHTML = document.body.innerHTML.replace('To', 'Hingga');
		document.body.innerHTML = document.body.innerHTML.replace('No records', 'Data kosong');
	}

	$("#selLang").val(newLang);
	r_setCookie('uniq365_langIndex', newLang, 1);
	$("html").attr('style', 'opacity:1');
	return false;
}

function r_setCookie(cname,cvalue,exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires=" + d.toGMTString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function r_getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

var loadFile = function (url, callback) {
        JSZipUtils.getBinaryContent(url, callback);
    }
function print_out(data, front){
	var currentdate = new Date(); 
	var datetime = " Print @"+currentdate.getDate() + "-"
	+ (currentdate.getMonth()+1)  + "-" 
	+ currentdate.getFullYear();
	loadFile("../company/words/"+data.template, function (err, content) {
        if (err) {
            throw e
        };
        doc = new Docxtemplater(content);
        doc.setData(data);
        doc.render()
        out = doc.getZip().generate({type: "blob"})
        saveAs(out, front+data.reference+", "+data.tran_date+datetime+".docx")
    });
}

function print_invoice(){
	var print_data;
	$('#btninvoice').click(function(){
		var datasend = new FormData();
        datasend.append('PARAM_0', $("[name='PARAM_0']").val());
        datasend.append('PARAM_1', $("[name='PARAM_1']").val());
        datasend.append('PARAM_2', $("[name='PARAM_2']").val());
        datasend.append('PARAM_3', $("[name='PARAM_3']").val());
        datasend.append('PARAM_4', $("[name='PARAM_4']").val());
        datasend.append('PARAM_5', $("[name='PARAM_5']").val());
        datasend.append('PARAM_6', $("[name='PARAM_6']").val());
        datasend.append('PARAM_7', $("[name='PARAM_7']").val());
        datasend.append('PARAM_8', $("[name='PARAM_8']").val());
        datasend.append('PARAM_9', $("[name='PARAM_9']").val());
        datasend.append('PARAM_10', $("[name='PARAM_10']").val());

        $.ajax({
            url: "../report/report/print_invoice",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false,
            dataType:"json",
            async : false,
            success: function(data){
            	var hitung = Object.keys(data).length;
            	console.log(hitung);
            	console.log(data);
            	
	            for (var i = 0; i < hitung; i++) {
		            var input = data[i].data;
		            var front = "Tax Invoice";
		            print_out(input, front);
	             	console.log(input);
	            }
            },
            complete: function(xhr,status) { },
            error: function(xhr,status,error) { console.log(xhr) }
        });
        
        return false;
	});
}

function print_creditnote(){
	$('#btncreditnote').click(function(){
		var datasend = new FormData();
        datasend.append('PARAM_0', $("[name='PARAM_0']").val());
        datasend.append('PARAM_1', $("[name='PARAM_1']").val());
        datasend.append('PARAM_2', $("[name='PARAM_2']").val());
        datasend.append('PARAM_3', $("[name='PARAM_3']").val());
        datasend.append('PARAM_4', $("[name='PARAM_4']").val());
        datasend.append('PARAM_5', $("[name='PARAM_5']").val());
        datasend.append('PARAM_6', $("[name='PARAM_6']").val());
        datasend.append('PARAM_7', $("[name='PARAM_7']").val());
        datasend.append('PARAM_8', $("[name='PARAM_8']").val());
        datasend.append('PARAM_9', $("[name='PARAM_9']").val());
        datasend.append('PARAM_10', $("[name='PARAM_10']").val());

        $.ajax({
            url: "../report/report/print_creditnote",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false,
            dataType:"json",
            async : false,
            success: function(data){
            	var hitung = Object.keys(data).length;
            	console.log(hitung);
            	console.log(data);
            	
	            for (var i = 0; i < hitung; i++) {
		            var input = data[i].data;
		            var front = "CreditNote, ";
		            print_out(input, front);
	             	console.log(input);
	            }
            },
            complete: function(xhr,status) { },
            error: function(xhr,status,error) { console.log(xhr) }
        });
        
        return false;
	});
}

function print_deliverynote(){
	$('#btndeliverynote').click(function(){
		var datasend = new FormData();
        datasend.append('PARAM_0', $("[name='PARAM_0']").val());
        datasend.append('PARAM_1', $("[name='PARAM_1']").val());
        datasend.append('PARAM_2', $("[name='PARAM_2']").val());
        datasend.append('PARAM_3', $("[name='PARAM_3']").val());
        datasend.append('PARAM_4', $("[name='PARAM_4']").val());
        datasend.append('PARAM_5', $("[name='PARAM_5']").val());
        datasend.append('PARAM_6', $("[name='PARAM_6']").val());
        datasend.append('PARAM_7', $("[name='PARAM_7']").val());
        datasend.append('PARAM_8', $("[name='PARAM_8']").val());
        datasend.append('PARAM_9', $("[name='PARAM_9']").val());
        datasend.append('PARAM_10', $("[name='PARAM_10']").val());

        $.ajax({
            url: "../report/report/print_deliverynote",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false,
            dataType:"json",
            async : false,
            success: function(data){
            	var hitung = Object.keys(data).length;
            	console.log(hitung);
            	console.log(data);
            	
	            for (var i = 0; i < hitung; i++) {
		            var input = data[i].data;
		            var front = "DeliveryNote, ";
		            print_out(input, front);
	             	console.log(input);
	            }
            },
            complete: function(xhr,status) { },
            error: function(xhr,status,error) { console.log(xhr) }
        });
        
        return false;
	});
}

function print_salesorder() {
	$('#btnsalesorder').click(function(){
		var datasend = new FormData();
        datasend.append('PARAM_0', $("[name='PARAM_0']").val());
        datasend.append('PARAM_1', $("[name='PARAM_1']").val());
        datasend.append('PARAM_2', $("[name='PARAM_2']").val());
        datasend.append('PARAM_3', $("[name='PARAM_3']").val());
        datasend.append('PARAM_4', $("[name='PARAM_4']").val());
        datasend.append('PARAM_5', $("[name='PARAM_5']").val());
        datasend.append('PARAM_6', $("[name='PARAM_6']").val());
        datasend.append('PARAM_7', $("[name='PARAM_7']").val());
        datasend.append('PARAM_8', $("[name='PARAM_8']").val());
        datasend.append('PARAM_9', $("[name='PARAM_9']").val());
        datasend.append('PARAM_10', $("[name='PARAM_10']").val());

        $.ajax({
            url: "../report/report/print_salesorder",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false,
            dataType:"json",
            async : false,
            success: function(data){
            	var hitung = Object.keys(data).length;
            	console.log(hitung);
            	console.log(data);
            	
	            for (var i = 0; i < hitung; i++) {
		            var input = data[i].data;
		            var front = "SalesOrder, ";
		            print_out(input, front);
	             	console.log(input);
	            }
            },
            complete: function(xhr,status) { },
            error: function(xhr,status,error) { console.log(xhr) }
        });
        
        return false;
	});
}

function print_salesquot() {
	$('#btnsalesquot').click(function(){
		var datasend = new FormData();
        datasend.append('PARAM_0', $("[name='PARAM_0']").val());
        datasend.append('PARAM_1', $("[name='PARAM_1']").val());
        datasend.append('PARAM_2', $("[name='PARAM_2']").val());
        datasend.append('PARAM_3', $("[name='PARAM_3']").val());
        datasend.append('PARAM_4', $("[name='PARAM_4']").val());
        datasend.append('PARAM_5', $("[name='PARAM_5']").val());
        datasend.append('PARAM_6', $("[name='PARAM_6']").val());
        datasend.append('PARAM_7', $("[name='PARAM_7']").val());
        datasend.append('PARAM_8', $("[name='PARAM_8']").val());
        datasend.append('PARAM_9', $("[name='PARAM_9']").val());
        datasend.append('PARAM_10', $("[name='PARAM_10']").val());

        $.ajax({
            url: "../report/report/print_salesquot",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false,
            dataType:"json",
            async : false,
            success: function(data){
            	var hitung = Object.keys(data).length;
            	console.log(hitung);
            	console.log(data);	
            	
	            for (var i = 0; i < hitung; i++) {
		            var input = data[i].data;
		            var front = "SalesQuotation, ";
		            print_out(input, front);
	             	console.log(input);
	            }
            },
            complete: function(xhr,status) { },
            error: function(xhr,status,error) { console.log(xhr) }
        });
        
        return false;
	});
}

function print_payment() {
	$('#btnpayment').click(function(){
		var datasend = new FormData();
        datasend.append('PARAM_0', $("[name='PARAM_0']").val());
        datasend.append('PARAM_1', $("[name='PARAM_1']").val());
        datasend.append('PARAM_2', $("[name='PARAM_2']").val());
        datasend.append('PARAM_3', $("[name='PARAM_3']").val());
        datasend.append('PARAM_4', $("[name='PARAM_4']").val());
        datasend.append('PARAM_5', $("[name='PARAM_5']").val());
        datasend.append('PARAM_6', $("[name='PARAM_6']").val());
        datasend.append('PARAM_7', $("[name='PARAM_7']").val());
        datasend.append('PARAM_8', $("[name='PARAM_8']").val());
        datasend.append('PARAM_9', $("[name='PARAM_9']").val());
        datasend.append('PARAM_10', $("[name='PARAM_10']").val());

        $.ajax({
            url: "../report/report/print_payment",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false,
            dataType:"json",
            async : false,
            success: function(data){
            	var hitung = Object.keys(data).length;
            	console.log(hitung);
            	console.log(data);	
            	
	            for (var i = 0; i < hitung; i++) {
		            var input = data[i].data;
		            var front = "Payment, ";
		            print_out(input, front);
	             	console.log(input);
	            }
            },
            complete: function(xhr,status) { },
            error: function(xhr,status,error) { console.log(xhr) }
        });
        
        return false;
	});
}

function print_purchaseorder() {
	$('#btnpurchaseorder').click(function(){
		var datasend = new FormData();
        datasend.append('PARAM_0', $("[name='PARAM_0']").val());
        datasend.append('PARAM_1', $("[name='PARAM_1']").val());
        datasend.append('PARAM_2', $("[name='PARAM_2']").val());
        datasend.append('PARAM_3', $("[name='PARAM_3']").val());
        datasend.append('PARAM_4', $("[name='PARAM_4']").val());
        datasend.append('PARAM_5', $("[name='PARAM_5']").val());
        datasend.append('PARAM_6', $("[name='PARAM_6']").val());
        datasend.append('PARAM_7', $("[name='PARAM_7']").val());
        datasend.append('PARAM_8', $("[name='PARAM_8']").val());
        datasend.append('PARAM_9', $("[name='PARAM_9']").val());
        datasend.append('PARAM_10', $("[name='PARAM_10']").val());

        $.ajax({
            url: "../report/report/print_purchaseorder",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false,
            dataType:"json",
            async : false,
            success: function(data){
            	var hitung = Object.keys(data).length;
            	console.log(hitung);
            	console.log(data);	
            	
	            for (var i = 0; i < hitung; i++) {
		            var input = data[i].data;
		            var front = "Payment, ";
		            print_out(input, front);
	             	console.log(input);
	            }
            },
            complete: function(xhr,status) { },
            error: function(xhr,status,error) { console.log(xhr) }
        });
        
        return false;
	});
}

function print_remittance() {
	$('#btnremittance').click(function(){
		var datasend = new FormData();
        datasend.append('PARAM_0', $("[name='PARAM_0']").val());
        datasend.append('PARAM_1', $("[name='PARAM_1']").val());
        datasend.append('PARAM_2', $("[name='PARAM_2']").val());
        datasend.append('PARAM_3', $("[name='PARAM_3']").val());
        datasend.append('PARAM_4', $("[name='PARAM_4']").val());
        datasend.append('PARAM_5', $("[name='PARAM_5']").val());
        datasend.append('PARAM_6', $("[name='PARAM_6']").val());
        datasend.append('PARAM_7', $("[name='PARAM_7']").val());
        datasend.append('PARAM_8', $("[name='PARAM_8']").val());
        datasend.append('PARAM_9', $("[name='PARAM_9']").val());
        datasend.append('PARAM_10', $("[name='PARAM_10']").val());

        $.ajax({
            url: "../report/report/print_remittance",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false,
            dataType:"json",
            async : false,
            success: function(data){
            	var hitung = Object.keys(data).length;
            	console.log(hitung);
            	console.log(data);	
            	
	            for (var i = 0; i < hitung; i++) {
		            var input = data[i].data;
		            var front = "Remittance, ";
		            print_out(input, front);
	             	console.log(input);
	            }
            },
            complete: function(xhr,status) { },
            error: function(xhr,status,error) { console.log(xhr) }
        });
        
        return false;
	});
}

function print_paymentvoucher() {
	$('#btn801').click(function(){
		var datasend = new FormData();
        datasend.append('start_date', $("[name='start_date']").val());
        datasend.append('end_date', $("[name='end_date']").val());
        datasend.append('account', $("[name='account']").val());
        datasend.append('ref', $("[name='ref']").val());
        datasend.append('comment', $("[name='comment']").val());
        datasend.append('type', $("[name='type']").val());
        datasend.append('PARAM_0', $("[name='PARAM_0']").val());
        datasend.append('trans_no', $("[name='trans_no']").val());

        $.ajax({
            url: "../report/report/print_paymentvoucher",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false,
            dataType:"json",
            async : false,
            success: function(data){
            	var hitung = Object.keys(data).length;
            	console.log(hitung);
            	console.log(data);	
            	
	            for (var i = 0; i < hitung; i++) {
		            var input = data[i].data;
		            var front = "PaymentVoucher, ";
		            print_out(input, front);
	             	console.log(input);
	            }
            },
            complete: function(xhr,status) { },
            error: function(xhr,status,error) { console.log(xhr) }
        });
        
        return false;
	});
}

function print_depositvoucher() {
	$('#btn802').click(function(){
		var datasend = new FormData();
        datasend.append('start_date', $("[name='start_date']").val());
        datasend.append('end_date', $("[name='end_date']").val());
        datasend.append('account', $("[name='account']").val());
        datasend.append('ref', $("[name='ref']").val());
        datasend.append('comment', $("[name='comment']").val());
        datasend.append('type', $("[name='type']").val());
        datasend.append('PARAM_0', $("[name='PARAM_0']").val());
        datasend.append('trans_no', $("[name='trans_no']").val());

        $.ajax({
            url: "../report/report/print_depositvoucher",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false,
            dataType:"json",
            async : false,
            success: function(data){
            	var hitung = Object.keys(data).length;
            	console.log(hitung);
            	console.log("deposit");
            	console.log(data);	
            	
	            for (var i = 0; i < hitung; i++) {
		            var input = data[i].data;
		            var front = "DepositVoucher, ";
		            print_out(input, front);
	             	console.log(input);
	            }
            },
            complete: function(xhr,status) { },
            error: function(xhr,status,error) { console.log(xhr) }
        });
        
        return false;
	});
}

function print_transfervoucher() {
	$('#btn803').click(function(){
		var datasend = new FormData();
        datasend.append('start_date', $("[name='start_date']").val());
        datasend.append('end_date', $("[name='end_date']").val());
        datasend.append('account', $("[name='account']").val());
        datasend.append('ref', $("[name='ref']").val());
        datasend.append('comment', $("[name='comment']").val());
        datasend.append('type', $("[name='type']").val());
        datasend.append('PARAM_0', $("[name='PARAM_0']").val());
        datasend.append('trans_no', $("[name='trans_no']").val());

        $.ajax({
            url: "../report/report/print_transfervoucher",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false,
            dataType:"json",
            async : false,
            success: function(data){
            	var hitung = Object.keys(data).length;
            	console.log(hitung);
            	console.log("transfer");
            	console.log(data);	
            	
	            for (var i = 0; i < hitung; i++) {
		            var input = data[i].data;
		            var front = "TransferVoucher, ";
		            print_out(input, front);
	             	console.log(input);
	            }
            },
            complete: function(xhr,status) { },
            error: function(xhr,status,error) { console.log(xhr) }
        });
        
        return false;
	});
}


function dropdown_search(){
	// $("body").on('click', '#dropdown_search', function(){
 //        $(".dropdown_search").slideToggle();
 //        // console.log('asdf');	
 //    });
 	$("body").on('click', '#dropdown_search', function(){
        $(".dropdown_search").toggle('slide');
        // console.log('asdf');
        if(table_flex == ''){
        	$('#table-flex').addClass('col-md-12');
        	$('#table-flex #dropdown_search span').html('Show search');
        	table_flex = '1';
        }else{
        	$('#table-flex').removeClass('col-md-12');
        	$('#table-flex #dropdown_search span').html('Hide search');
        	table_flex = '';
        }
        // console.log(table_flex);
    });
    // alert('asdf');	

}
function dropdown_menu(){
	$('.dropdown-menu .dropdown-submenu').on("click", function(e){
	    $('.dropdown-menu .dropdown-submenu').removeClass('open');
	    $(this).toggleClass('open');
	    e.stopPropagation();
	    // e.preventDefault();
 	});
	$('body').on("click", function(e){
	    $('.dropdown-menu .dropdown-submenu').removeClass('open');
 	});
}