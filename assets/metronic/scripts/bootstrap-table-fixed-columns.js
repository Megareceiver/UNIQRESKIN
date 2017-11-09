(function ($) {

    $.fn.tableFixer = function (param) {

        return this.each(function () {
            table.call(this);
        });

        function table() {
            {
                var defaults = {
                    right: 0
                };

                var settings = $.extend({}, defaults, param);

                settings.table  = this;
                settings.parent = $(settings.table).parent();
                setParent();

                if (settings.right > 0)
                    fixRight();

                $(settings.parent).trigger("scroll");

                $(window).resize(function () {
                    $(settings.parent).trigger("scroll");
                });

                // Set style of table parent
                function setParent() {
                    var parent = $(settings.parent);
                    var table  = $(settings.table);

                    parent.append(table);
                    parent.css({
                            'overflow-x': 'auto',
                            'overflow-y': 'auto'
                        });

                    parent.scroll(function () {
                        var scrollWidth  = parent[0].scrollWidth;
                        var clientWidth  = parent[0].clientWidth;
                        var left         = parent.scrollLeft();

                        if (settings.right > 0)
                            settings.rightColumns.css("right", scrollWidth - clientWidth - left);
                    }.bind(table));
                }

                // Set table right column fixed
                function fixRight() {
                    var table = $(settings.table);

                    var fixColumn = settings.right;

                    settings.rightColumns = $();

                    var tr = table.find("tr");
                    tr.each(function (k, row) {
                        solveRightColspan(row, function (cell) {
                            settings.rightColumns = settings.rightColumns.add(cell);
                        });
                    });

                    var column = settings.rightColumns;

                    column.each(function (k, cell) {
                        var cell = $(cell);

                        setBackground(cell);
                        cell.css({
                            'position': 'relative'
                        });
                    });

                }

                // Set fixed cells backgrounds
                function setBackground(elements) {
                    elements.each(function (k, element) {
                        var element = $(element);
                        var parent  = $(element).parent();

                        var elementBackground = element.css("background-color");
                        elementBackground     = (elementBackground == "transparent" || elementBackground == "rgba(0, 0, 0, 0)") ? null : elementBackground;

                        var parentBackground = parent.css("background-color");
                        parentBackground     = (parentBackground == "transparent" || parentBackground == "rgba(0, 0, 0, 0)") ? null : parentBackground;

                        var background = parentBackground ? parentBackground : "white";
                        background     = elementBackground ? elementBackground : background;

                        element.css("background-color", background);
                        // element.css("border", '5px solid black');
                    });
                }

                function solveRightColspan(row, action) {
                    var fixColumn = settings.right;
                    var inc       = 1;

                    for (var i = 1; i <= fixColumn; i = i + inc) {
                        var nth = inc > 1 ? i - 1 : i;

                        var cell    = $(row).find("> *:nth-last-child(" + nth + ")");
                        var colspan = cell.prop("colspan");

                        action(cell);

                        inc = colspan;
                    }
                }
            }
        }
    };

})(jQuery);


function app_handle_listing_horisontal_scroll(listing_obj)
{
  //get table object
  table_obj = $('.table',listing_obj);

  //get count fixed collumns params
  count_fixed_collumns = table_obj.attr('data-count-fixed-columns')

  if(count_fixed_collumns>0)
  {
    //get wrapper object
    wrapper_obj = $('.table-scrollable',listing_obj);

    wrapper_left_margin = 0;

    table_collumns_width = new Array();
    table_collumns_margin = new Array();

    //calculate wrapper margin and fixed column width
    $('th',table_obj).each(function(index){
       if(index<count_fixed_collumns)
       {
         wrapper_left_margin += $(this).outerWidth();
         table_collumns_width[index] = $(this).outerWidth();
       }
    })

    //calcualte margin for each column
    $.each( table_collumns_width, function( key, value ) {
      if(key==0)
      {
        table_collumns_margin[key] = wrapper_left_margin;
      }
      else
      {
        next_margin = 0;
        $.each( table_collumns_width, function( key_next, value_next ) {
          if(key_next<key)
          {
            next_margin += value_next;
          }
        });

        table_collumns_margin[key] = wrapper_left_margin-next_margin;
      }
    });

    //set wrapper margin
    if(wrapper_left_margin>0)
    {
      wrapper_obj.css('cssText','margin-left:'+wrapper_left_margin+'px !important; width: auto')
    }

    //set position for fixed columns
    $('tr',table_obj).each(function(){

      //get current row height
      current_row_height = $(this).outerHeight();

      $('th,td',$(this)).each(function(index){

         //set row height for all cells
         $(this).css('height',current_row_height)

         //set position
         if(index<count_fixed_collumns)
         {
           $(this).css('position','absolute')
                  .css('margin-left','-'+table_collumns_margin[index]+'px')
                  .css('width',table_collumns_width[index])

           $(this).addClass('table-fixed-cell')
         }
      })
    })
  }
}
$( document ).ready(function() {

    app_handle_listing_horisontal_scroll($('table.table-striped'));
})