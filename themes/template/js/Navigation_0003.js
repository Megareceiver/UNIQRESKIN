/* Author:

*/

// Create global namespace for Pastel
var Pastel = Pastel || {};

// Defines self executing navigation function
Pastel.Nav = (function () {

    var $rootEl,
		$navEl;

    $(function () {
        if ($('#nav').length) {
            Pastel.Nav.init();
        }
    });

    function _init() {
        $rootEl = $('body');
        $navEl = $('#nav');

//        $navEl
//			.delegate('#nav li', 'mouseenter', function (event) {
//			    var parentWidth = $(this).parent().width();
//			    _showSubMenu($(this), parentWidth);
//			})
//			.delegate('#nav li', 'mouseleave', function (event) {
//			    _hideSubMenu($(this));
//			});
        jQuery('#nav li').mouseenter(function() {
        		 var parentWidth = $(this).parent().width();
 			    _showSubMenu($(this), parentWidth);
        	}).mouseleave(function(){
        		_hideSubMenu($(this));
        	});

        jQuery('#nav li li').mouseenter(function() {
	   		 var parentWidth = $(this).parent().width();
	   		_showSub1Menu($(this), parentWidth);
	   	}).mouseleave(function(){
	   		_hideSub1Menu($(this));
	   	});
        jQuery('#nav li li li').mouseenter(function() {
	   		 var parentWidth = $(this).parent().width();
	   		_showSub2Menu($(this), parentWidth);
	   	}).mouseleave(function(){
	   		_hideSub2Menu($(this));
	   	});

//        $navEl
//			.delegate('#nav li li', 'mouseenter', function (event) {
//			    var parentWidth = $(this).parent().width();
//			    _showSub1Menu($(this), parentWidth);
//			})
//			.delegate('#nav li li', 'mouseleave', function (event) {
//			    _hideSub1Menu($(this));
//			});
//
//        $navEl
//          .delegate('#nav li li li', 'mouseenter', function (event) {
//              var parentWidth = $(this).parent().width();
//              _showSub2Menu($(this), parentWidth);
//          })
//          .delegate('#nav li li li', 'mouseleave', function (event) {
//              _hideSub2Menu($(this));
//          });

    }

    function _showSubMenu(obj, parentWidth) {

        var menuEl = obj.find('.sub-level');
        resizeMenuListWidth(menuEl, true);

        menuEl = obj.find('.sub-level1');
        resizeMenuListWidth(menuEl, false);

    }

    /*IE 7 fix*/
    function resizeMenuListWidth(menuEl, makeVisible) {
        var maxWidth = 0;
        var elemWidth = 0;
        menuEl.each(function (i) {
            elemWidth = parseInt($(this).css('width'));
            if (parseInt($(this).css('width')) > maxWidth) {
                maxWidth = elemWidth;
            }
        });

        menuEl.css('width', maxWidth + 'px');

        if (makeVisible)
            menuEl.addClass('visible');
    }

    function _hideSubMenu(obj) {
        obj.find('.sub-level').removeClass('visible');
    }

    function _showSub1Menu(obj, parentWidth) {

        var menuEl = obj.find('.sub-level1');
        menuEl.css('left', parentWidth + 'px');
        menuEl.addClass('visible');

        menuEl = obj.find('.sub-level2');
        resizeMenuListWidth(menuEl, false);

    }

    function _showSub2Menu(obj, parentWidth) {
        var menuEl = obj.find('.sub-level2');
        menuEl.css('left', parentWidth + 'px');
        menuEl.addClass('visible');

    }

    function _hideSub1Menu(obj) {
        obj.find('.sub-level1').removeClass('visible');
    }

    function _hideSub2Menu(obj) {
        obj.find('.sub-level2').removeClass('visible');
    }


    return {
        init: _init
    };
})();

Pastel.Login = (function () {

    var $rootEl,
		$loginEl;

    $(function () {
        if ($('#nav').length) {
            Pastel.Login.init();
        }
    });

    function _init() {
        $rootEl = $('body');
        $loginEl = $('#login, .verticalLoginControl');

        $(document).ready(function () { startTimer(); });

        //$('#txtUserName').keyup(function () { alert('usrrname changed'); }); ;

        // check on page load
        $loginEl.find('input:text').each(function () {
            _setClass($(this));
        });

        $loginEl
			.delegate('input', 'focus', function (event) {
			    _setClass($(this));
			})
             .delegate('input', 'blur', function (event) {
                 _setClass($(this));
             });

    }

    function startTimer() {
        setTimeout(checkForPassword, 100);
        setTimeout(checkForPassword_vertical, 100);
    }

    function checkForPassword() {
        var password = $('input.txtPassword');
        if (password.val() !== '')
            password.addClass('has-value');
        setTimeout(checkForPassword, 500);
    }

    function checkForPassword_vertical() {
        var password = $('input.txtPassword_vertical');
        if (password.val() !== '')
            password.addClass('has-value');
        setTimeout(checkForPassword_vertical, 500);
    }

    function _setClass(obj) {
        if (obj.val() !== '') {
            obj.addClass('has-value');
        } else {
            obj.removeClass('has-value');
        }
    }

    return {
        init: _init
    };
})();


function GenerateHelpDropDownMenu(v) {
    //$('#nav-stub').find('#liHelp').removeClass(".last");
    $('#nav-stub').find('.last').remove();

    $('#nav-stub').append('<li><div id="divHelpMenuDropdown" style="position:relative; display: block;  z-index:9999;"><dl class="helpMenuDropdown">' +
        '<dt id="HelpHintDropDown"><a class="last"><span>Help</span></a></dt><dd>' +
        '<ul><li><a id="showHelpLink" href="/Marketing/NeedHelp.aspx" target="_blank">Show Help Options</a></li>' +
        '<li><a id="showQuickTip"  style="cursor: pointer;" onclick="javascript:' + v + '();">Show Quick Tips</a></li>' +
        '</ul></dd></dl></div></li>');

    $(".helpMenuDropdown dt a").click(function () {
        $(".helpMenuDropdown dd ul").toggle();

        var isVisible = $(".helpMenuDropdown dd ul").is(":visible"); // Checks for display:[none|block], ignores visible:[true|false]

        //if(visible, redo the z-indexes)
        if (isVisible) {
            $("#divHelpMenuDropdown").parents().addClass('divHelpMenuDropdown-onTop');
        } else {
            $("#divHelpMenuDropdown").parents().removeClass('divHelpMenuDropdown-onTop');
        }
    });

    $("#showHelpLink").click(function () {
        $(".helpMenuDropdown dd ul").hide();
        $("#divHelpMenuDropdown").parents().removeClass('divHelpMenuDropdown-onTop');
    });

    $("#showQuickTip").click(function () {
        $(".helpMenuDropdown dd ul").hide();
        $("#divHelpMenuDropdown").parents().removeClass('divHelpMenuDropdown-onTop');
    });

    $(".helpMenuDropdown dd").mouseleave(function () {
       $(".helpMenuDropdown dd ul").hide();
        $("#divHelpMenuDropdown").parents().removeClass('divHelpMenuDropdown-onTop');
    });

    //$(document).bind('click', function (e) {
    //    var $clicked = $(e.target);
    //    if (!$clicked.parents().hasClass("helpMenuDropdown")) {
    //        $(".helpMenuDropdown dd ul").hide();
    //        $("#divHelpMenuDropdown").parents().removeClass('divHelpMenuDropdown-onTop');
    //    }
    //});
}
