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
include_once(ROOT . "/includes/banking.inc");
include_once(ROOT . "/includes/types.inc");
include_once(ROOT . "/includes/current_user.inc");

$_search_button = "<input %s type='submit' class='combo_submit' style='border:0;background:url(".site_url()."/themes/"
	."%s/images/locate.png) no-repeat;%s' aspect='fallback' name='%s' value=' ' title='"._("Set filter")."'> ";

$_select_button = "<input %s type='submit' class='combo_select' style='border:0;background:url(".site_url()."/themes/"
	."%s/images/button_ok.png) no-repeat;%s' aspect='fallback' name='%s' value=' ' title='"._("Select")."'> ";

$all_items = ALL_TEXT;

// ----------------------------------------------------------------------------
// Universal sql combo generator
// $sql must return selector values and selector texts in columns 0 & 1
// Options are merged with defaults.
function combo_input($name, $selected_id, $sql, $valfield, $namefield, $options = null)
{
    global $Ajax;

    $opts = array( // default options
        'where' => array(), // additional constraints
        'order' => $namefield, // list sort order
                               // special option parameters
        'spec_option' => false, // option text or false
        'spec_id' => 0, // option id
                        // submit on select parameters
        'default' => '', // default value when $_POST is not set
        'multi' => false, // multiple select
        'select_submit' => false, // submit on select: true/false
        'async' => true, // select update via ajax (true) vs _page_body reload
                         // search box parameters
        'sel_hint' => null,
        'search_box' => false, // name or true/false
        'type' => 0, // type of extended selector:
                     // 0 - with (optional) visible search box, search by fragment inside id
                     // 1 - with hidden search box, search by option text
                     // 2 - with (optional) visible search box, search by fragment at the start of id
                     // 3 - TODO reverse: box with hidden selector available via enter; this
                     // would be convenient for optional ad hoc adding of new item
        'search_submit' => true, // search submit button: true/false
        'size' => 8, // size and max of box tag
        'max' => 50,
        'height' => false, // number of lines in select box
        'cells' => false, // combo displayed as 2 <td></td> cells
        'search' => array(), // sql field names to search
        'format' => null, // format functions for regular options
        'disabled' => false,
        'box_hint' => null, // box/selectors hints; null = std see below
        'category' => false, // category column name or false
        'show_inactive' => false, // show inactive records.
        'editable' => false,
        'class'=> get_instance()->bootstrap->input_class,
        'data-live-search'=>false
    ) // false, or length of editable entry field
;
    // ------ merge options with defaults ----------
    if ($options != null)
        $opts = array_merge($opts, $options);

    if (! is_array($opts['where']))
        $opts['where'] = array(
            $opts['where']
        );

    $search_box = $opts['search_box'] === true ? '_' . $name . '_edit' : $opts['search_box'];
    // select content filtered by search field:
    $search_submit = $opts['search_submit'] === true ? '_' . $name . '_button' : $opts['search_submit'];
    // select set by select content field
    $search_button = $opts['editable'] ? '_' . $name . '_button' : ($search_box ? $search_submit : false);

    $select_submit = $opts['select_submit'];
    $spec_id = $opts['spec_id'];
    $spec_option = $opts['spec_option'];

    if ($opts['type'] == 0) {
        $by_id = true;
        $class = 'combo';
    } elseif ($opts['type'] == 1) {
        $by_id = false;
        $class = 'combo2';
    } else {
        $by_id = true;
        $class = 'combo3';
    }

//     $disabled = $opts['disabled'] ? "disabled" : '';
    $multi = $opts['multi'];

    if (! count($opts['search'])) {
        $opts['search'] = array(
            $by_id ? $valfield : $namefield
        );
    }
    if ($opts['sel_hint'] === null)
        $opts['sel_hint'] = $by_id || $search_box == false ? '' : _('Press Space tab for search pattern entry');

    if ($opts['box_hint'] === null)
        $opts['box_hint'] = $search_box && $search_submit != false ? ($by_id ? _('Enter code fragment to search or * for all') : _('Enter description fragment to search or * for all')) : '';

    if ($selected_id == null) {
        $selected_id = get_post($name, (string) $opts['default']);
    }
    if (! is_array($selected_id))
        $selected_id = array(
            (string) $selected_id
        ); // code is generalized for multiple selection support

    $txt = get_post($search_box);
    $rel = '';
    $limit = '';
    if (isset($_POST['_' . $name . '_update'])) { // select list or search box change
        if ($by_id)
            $txt = $_POST[$name];

        if (! $opts['async'])
            $Ajax->activate('_page_body');
        else
            $Ajax->activate($name);
    }
    if (isset($_POST[$search_button])) {
        if (! $opts['async'])
            $Ajax->activate('_page_body');
        else
            $Ajax->activate($name);
    }
    if ($search_box) {
        // search related sql modifications

        $rel = "rel='$search_box'"; // set relation to list
        if ($opts['search_submit']) {
            if (isset($_POST[$search_button])) {
                $selected_id = array(); // ignore selected_id while search
                if (! $opts['async'])
                    $Ajax->activate('_page_body');
                else
                    $Ajax->activate($name);
            }
            if ($txt == '') {
                if ($spec_option === false && $selected_id == array())
                    $limit = ' LIMIT 1';
                else {

                    // $opts['where'][] = $valfield . "=". db_escape(get_post($name, $spec_id));
                }
            } else
                if ($txt != '*') {
                    foreach ($opts['search'] as $i => $s)
                        $opts['search'][$i] = $s . " LIKE " . db_escape(($class == 'combo3' ? '' : '%') . $txt . '%');
                    // $opts['where'][] = '('. implode($opts['search'], ' OR ') . ')';
                }
        }
    }

    // sql completion
    if (count($opts['where'])) {
        $where = strpos($sql, 'WHERE') == false ? ' WHERE ' : ' AND ';
        $where .= '(' . implode($opts['where'], ' AND ') . ')';
        $group_pos = strpos($sql, 'GROUP BY');
        if ($group_pos) {
            $group = substr($sql, $group_pos);
            $sql = substr($sql, 0, $group_pos) . $where . ' ' . $group;
        } else {
            $sql .= $where;
        }
    }
    if ($opts['order'] != false) {
        if (! is_array($opts['order']))
            $opts['order'] = array(
                $opts['order']
            );
        $sql .= ' ORDER BY ' . implode(',', $opts['order']);
    }

    $sql .= $limit;
    // ------ make selector ----------
    $selector = $first_opt = '';
    $first_id = false;
    $found = false;
    $lastcat = null;
    $edit = false;
    if ($name == 'stock_id') {
        // display_notification('<pre>'.print_r($_POST, true).'</pre>');
    }
    // if($name=='curr_default') display_notification($opts['search_submit']);

    if ($result = db_query($sql)) {
        $allRows = mysql_num_rows($result);
        $i = 1;
        while ($contact_row = db_fetch($result)) {

            $value = $contact_row[0];
            $descr = $opts['format'] == null ? $contact_row[1] : call_user_func($opts['format'], $contact_row);
            $sel = '';
            if (get_post($search_button) && ($txt == $value)) {
                $selected_id[] = $value;
            }


            if ( in_array((string) $value, $selected_id, true)) {
                $sel .= ' selected';
                $found = $value;
                $edit = $opts['editable'] && $contact_row['editable'] && (@$_POST[$search_box] == $value) ? $contact_row[1] : false; // get non-formatted description
                if ($edit)
                    break; // selected field is editable - abandon list construction
            }

            /*
             * update set default _value
             */
            if( !$spec_option AND $i==1){
//                 $selected_id[] = $value;
                $sel .= ' selected';
//                 $found = true;
            } else  if ($allRows == $i && in_array('last-items', $selected_id, true)) {
                $sel .= 'selected';
            }
            // show selected option even if inactive
            if (! $opts['show_inactive'] && @$contact_row['inactive'] && $sel === '') {
                continue;
            } else
                $optclass = @$contact_row['inactive'] ? "class='inactive'" : '';

            if ($first_id === false) {
                $first_id = $value;
                $first_opt = $descr;
            }

            $cat = $contact_row[$opts['category']];
            if ($opts['category'] !== false && $cat != $lastcat) {
                $selector .= "<optgroup label='" . $cat . "'>\n";
                $lastcat = $cat;
            }
            if (isset($contact_row['parent'])) {
                $sel .= ' parent="' . $contact_row['parent'] . '" ';
            }
            $selector .= "<option $sel $optclass value='$value'>$descr</option>\n";
            $i ++;
        }
        db_free_result($result);
    }

    // Prepend special option.
    if ($spec_option !== false) { // if special option used - add it
        $first_id = $spec_id;
        $first_opt = $spec_option;
        // }
        // if($first_id !== false) {
        $sel = $found === false ? 'selected' : '';
        $optclass = @$contact_row['inactive'] ? "class='inactive'" : '';
        $selector = "<option $sel value='$first_id'>$first_opt</option>\n" . $selector;
    }

    if ($found === false) {
        $selected_id = array(
            $first_id
        );
    }

    $_POST[$name] = $multi ? $selected_id : $selected_id[0];

    $select_attributes = array(
        'autocomplete'=>'off',
        'name'=>$name,
        'title'=>$opts['sel_hint'],
        'class'=> $class.' '.$opts['class'],
        'data-size'=>6
    );

    if( $multi ){
        $select_attributes['multiple'] = true;
        $select_attributes['name'] .= '[]';
    }

    if( $opts['height'] ){
        $select_attributes['size'] = $opts['height'];
    }

    if( $opts['data-live-search']){
        $select_attributes['data-live-search'] = true;
    }
    /*
     * turn off search function
     */
    $select_attributes['data-live-search'] = false;

    $disabled = NULL;
    if( $opts['disabled'] ){
        $select_attributes['disabled'] = 'disabled';
        $disabled = 'disabled';
    }


    $selector = "\n<select "._parse_attributes($select_attributes)." $rel>" . $selector . "</select>\n";

    if ($by_id && ($search_box != false || $opts['editable'])) {
        // on first display show selector list
        if (isset($_POST[$search_box]) && $opts['editable'] && $edit) {
            $selector = "<input type='hidden' name='$name' value='" . $_POST[$name] . "'>" . "<input type='text' $disabled name='{$name}_text' id='{$name}_text' size='" . $opts['editable'] . "' maxlength='" . $opts['max'] . "' $rel value='$edit'>\n";
            set_focus($name . '_text'); // prevent lost focus
        } else
            if (get_post($search_submit ? $search_submit : "_{$name}_button"))
                set_focus($name); // prevent lost focus
        if (! $opts['editable'])
            $txt = $found;
        $Ajax->addUpdate($name, $search_box, $txt ? $txt : '');
    }

    $Ajax->addUpdate($name, "_{$name}_sel", $selector);

    // span for select list/input field update
    $selector = "<span id='_{$name}_sel' class='select w-100'>" . $selector . "<i class=\"button\"></i></span>\n";

    // if selectable or editable list is used - add select button
    if ($select_submit != false || $search_button) {
        global $_select_button;
        // button class selects form reload/ajax selector update
        $selector .= '<span style="display:none;">'.sprintf($_select_button, $disabled, user_theme(), (fallback_mode() ? '' : 'display:none;'), '_' . $name . '_update') . "</span>";
    }

    // ------ make combo ----------
    $edit_entry = '';
    if ($search_box != false) {
        $edit_entry = "<input $disabled type='text' name='$search_box' id='$search_box' size='" . $opts['size'] . "' maxlength='" . $opts['max'] . "' value='$txt' class='$class ".$opts['class']."' rel='$name' autocomplete='off' title='" . $opts['box_hint'] . "'" . (! fallback_mode() && ! $by_id ? " style=display:none;" : '') . ">";

        if ($search_submit != false || $opts['editable']) {
            global $_search_button;
            $edit_entry .= sprintf($_search_button, $disabled, user_theme(), (fallback_mode() ? '' : 'display:none;'), $search_submit ? $search_submit : "_{$name}_button") . "\n";
        }
    }
    default_focus(($search_box && $by_id) ? $search_box : $name);

    if ($search_box && $opts['cells']){
//         $str = ($edit_entry != '' ? "$edit_entry" : '') . "$selector";
        if( strlen($edit_entry) > 0 ){
            $str = "<td>".$edit_entry.'</td><td>'.$selector.'</td>';
        } else {
//             $str = $selector;
            $str = "<div class=\"row\"><div class ='col-3'>".$edit_entry.'</div><div class="col-9">'.$selector.'</div></div>';
        }
//         $str = ($edit_entry != '' ? "<div class ='col-md-2'> $edit_entry </div>" : '') . $selector;
    }

    else
        $str = "<span style=\"display:none;\">$edit_entry</span>" . $selector;

    return $str;
}



function combo_input_from_array($name, $selected_id, $items, $valfield, $namefield, $options=null){
	global $Ajax;

	$opts = array(		// default options
			'where'=> array(),		// additional constraints
			'order' => $namefield,	// list sort order
			// special option parameters
			'spec_option'=>false, 	// option text or false
			'spec_id' => 0,		// option id
			// submit on select parameters
			'default' => '', // default value when $_POST is not set
			'multi' => false,	// multiple select
			'select_submit' => false, //submit on select: true/false
			'async' => true,	// select update via ajax (true) vs _page_body reload
			// search box parameters
			'sel_hint' => null,
			'search_box' => false, 	// name or true/false
			'type' => 0,	// type of extended selector:
			// 0 - with (optional) visible search box, search by fragment inside id
			// 1 - with hidden search box, search by option text
			// 2 - with (optional) visible search box, search by fragment at the start of id
			// 3 - TODO reverse: box with hidden selector available via enter; this
			// would be convenient for optional ad hoc adding of new item
			'search_submit' => true, //search submit button: true/false
			'size' => 8,	// size and max of box tag
			'max' => 50,
			'height' => false,	// number of lines in select box
			'cells' => false,	// combo displayed as 2 <td></td> cells
			'search' => array(), // sql field names to search
			'format' => null, 	 // format functions for regular options
			'disabled' => false,
			'box_hint' => null, // box/selectors hints; null = std see below
			'category' => false, // category column name or false
			'show_inactive' => false, // show inactive records.
			'editable' => false // false, or length of editable entry field
	);
	// ------ merge options with defaults ----------
	if($options != null)
		$opts = array_merge($opts, $options);



	$spec_id = $opts['spec_id'];
	$spec_option = $opts['spec_option'];

	if ($opts['type'] == 0) {
		$by_id = true;
		$class = 'combo';
	} elseif($opts['type'] == 1) {
		$by_id = false;
		$class = 'combo2';
	} else {
		$by_id = true;
		$class = 'combo3';
	}

	$disabled = $opts['disabled'] ? "disabled" : '';
	$multi = $opts['multi'];

	if(!count($opts['search'])) {
		$opts['search'] = array($by_id ? $valfield : $namefield);
	}
	if ($opts['sel_hint'] === null)
		$opts['sel_hint'] = $by_id || $search_box==false ?
		'' : _('Press Space tab for search pattern entry');

	if ($opts['box_hint'] === null)
		$opts['box_hint'] = $search_box && $search_submit != false ?
		($by_id ? _('Enter code fragment to search or * for all')
				: _('Enter description fragment to search or * for all')) :'';

	if ($selected_id == null) {
		$selected_id = get_post($name, (string)$opts['default']);
	}
	if(!is_array($selected_id))
		$selected_id = array((string)$selected_id); // code is generalized for multiple selection support

	$txt = get_post($search_box);
	$rel = '';
	$limit = '';
	if (isset($_POST['_'.$name.'_update'])) { // select list or search box change
		if ($by_id) $txt = $_POST[$name];

		if (!$opts['async'])
			$Ajax->activate('_page_body');
		else
			$Ajax->activate($name);
	}
	if (isset($_POST[$search_button])) {
		if (!$opts['async'])
			$Ajax->activate('_page_body');
		else
			$Ajax->activate($name);
	}
	if ($search_box) {
		// search related sql modifications

		$rel = "rel='$search_box'"; // set relation to list
		if ($opts['search_submit']) {
			if (isset($_POST[$search_button])) {
				$selected_id = array(); // ignore selected_id while search
				if (!$opts['async'])
					$Ajax->activate('_page_body');
				else
					$Ajax->activate($name);
			}
			if ($txt == '') {
				if ($spec_option === false && $selected_id == array())
					$limit = ' LIMIT 1';
				else
					$opts['where'][] = $valfield . "=". db_escape(get_post($name, $spec_id));
			}
			else
				if ($txt != '*') {

				foreach($opts['search'] as $i=> $s)
					$opts['search'][$i] = $s . " LIKE "
					.db_escape(($class=='combo3' ? '' : '%').$txt.'%');
				$opts['where'][] = '('. implode($opts['search'], ' OR ') . ')';
			}
		}
	}



	// ------ make selector ----------
	$selector = $first_opt = '';
	$first_id = false;
	$found = false;
	$lastcat = null;
	$edit = false;
	//if($name=='stock_id')	display_notification('<pre>'.print_r($_POST, true).'</pre>');
	//if($name=='curr_default') display_notification($opts['search_submit']);

	foreach ($items AS $opt){
		$sel = NULL;

		if( $opt->id == $selected_id)
			$sel = 'selected';

		//if( isset($op->parent) ){
			$parent = ' parent="'.$opt->parent.'" ';
		//}
		$selector .= "<option $sel value='".$opt->id."' ".$parent." >".$opt->value."</option>\n";
	}

	// Prepend special option.
	if ($spec_option !== false) { // if special option used - add it
		$first_id = $spec_id;
		$first_opt = $spec_option;
		//	}
		//	if($first_id !== false) {
		$sel = $found===false ? 'selected' : '';
		$optclass = @$contact_row['inactive'] ? "class='inactive'" : '';
		$selector = "<option $sel value='$first_id'>$first_opt</option>\n"
		. $selector;
	}

	if ($found===false) {
		$selected_id = array($first_id);
	}

	$_POST[$name] = $multi ? $selected_id : $selected_id[0];

	$selector = "<select autocomplete='off' ".($multi ? "multiple" : '')
	. ($opts['height']!==false ? ' size="'.$opts['height'].'"' : '')
	. "$disabled name='$name".($multi ? '[]':'')."' class='$class' title='"
			. $opts['sel_hint']."' $rel>".$selector."</select>\n";

	if ($by_id && ($search_box != false || $opts['editable']) ) {
		// on first display show selector list
		if (isset($_POST[$search_box]) && $opts['editable'] && $edit) {
			$selector = "<input type='hidden' name='$name' value='".$_POST[$name]."'>"
			."<input type='text' $disabled name='{$name}_text' id='{$name}_text' size='".
			$opts['editable']."' maxlength='".$opts['max']."' $rel value='$edit'>\n";
			set_focus($name.'_text'); // prevent lost focus
		} else if (get_post($search_submit ? $search_submit : "_{$name}_button"))
			set_focus($name); // prevent lost focus
		if (!$opts['editable'])
			$txt = $found;
		$Ajax->addUpdate($name, $search_box, $txt ? $txt : '');
	}

	$Ajax->addUpdate($name, "_{$name}_sel", $selector);

	// span for select list/input field update
	$selector = "<span id='_{$name}_sel' class='select'>".$selector."</span>\n";

	// if selectable or editable list is used - add select button
	if ($select_submit != false || $search_button) {
		global $_select_button;
		// button class selects form reload/ajax selector update
		$selector .= sprintf($_select_button, $disabled, user_theme(),
				(fallback_mode() ? '' : 'display:none;'),
			 '_'.$name.'_update')."\n";
	}
	// ------ make combo ----------
	$edit_entry = '';
	if ($search_box != false) {
		$edit_entry = "<input $disabled type='text' name='$search_box' id='$search_box' size='".
				$opts['size']."' maxlength='".$opts['max'].
				"' value='$txt' class='$class' rel='$name' autocomplete='off' title='"
				.$opts['box_hint']."'"
				.(!fallback_mode() && !$by_id ? " style=display:none;":'')
				.">\n";
		if ($search_submit != false || $opts['editable']) {
			global $_search_button;
			$edit_entry .= sprintf($_search_button, $disabled, user_theme(),
					(fallback_mode() ? '' : 'display:none;'),
					$search_submit ? $search_submit : "_{$name}_button")."\n";
		}
	}
	default_focus(($search_box && $by_id) ? $search_box : $name);

	if ($search_box && $opts['cells'])
		$str = ($edit_entry!='' ? "<td>$edit_entry</td>" : '')."<td>$selector</td>";
	else
		$str = $edit_entry.$selector;
	return $str;
}

/*
	Helper function.
	Returns true if selector $name is subject to update.
*/
function list_updated($name)
{
	return isset($_POST['_'.$name.'_update']) || isset($_POST['_'.$name.'_button']);
}

//----------------------------------------------------------------------------------------------

function _format_add_curr($row)
{
	static $company_currency;

	if ($company_currency == null)
	{
		$company_currency = get_company_currency();
	}
	return $row[1] . ($row[2] == $company_currency ?
		'' : ("&nbsp;-&nbsp;" . $row[2]));
}

function add_edit_combo($type)
{
	global $path_to_root, $popup_editors, $use_icon_for_editkey;

	if (!isset($use_icon_for_editkey) || $use_icon_for_editkey==0)
		return "";
	// Derive theme path
	$theme_path = $path_to_root . '/themes/' . user_theme();

	$key = $popup_editors[$type][1];
	$onclick = "onclick=\"javascript:callEditor($key); return false;\"";
	$img = "<img width='12' height='12' border='0' alt='Add/Edit' title='Add/Edit' src='$theme_path/images/".ICON_EDIT."'>";
	return "<a target = '_blank' href='#' $onclick tabindex='-1'>$img</a>";
}



//------------------------------------------------------------------------------------------------


//------------------------------------------------------------------------------------------------




//-----------------------------------------------------------------------------------------------

function tax_list($name,$selected_id=null,$submit_on_change=false){
    $sql = "SELECT taxcodeno FROM ".TB_PREF."tbltaxcode";
    return combo_input($name, $selected_id, $sql, 'taxcodeno',
        array(
            'select_submit'=> $submit_on_change,
            'async' => false
        ) );

}
function tax_list_cells($label, $name, $selected_id=null, $submit_on_change=false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo tax_list($name, $selected_id, $submit_on_change);
    echo "</td>\n";
}

function tax_list_row($label, $name, $selected_id=null, $submit_on_change=false)
{
    echo "<tr><td class='label'>$label</td>";
    tax_list_cells(null, $name, $selected_id, $submit_on_change);
    echo "</tr>\n";
}




//---------------------------------------------------------------------------------------------------



/*
function stock_items_list_row($label, $name, $selected_id=null, $all_option=false, $submit_on_change=false)
{
	echo "<tr>\n";
	stock_items_list_cells($label, $name, $selected_id, $all_option, $submit_on_change);
	echo "</tr>\n";
}
*/
//---------------------------------------------------------------------------------------------------


//------------------------------------------------------------------------------------

function stock_component_items_list($name, $parent_stock_id, $selected_id=null,
	$all_option=false, $submit_on_change=false, $editkey = false)
{
	return stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array('where'=>array("stock_id != ".db_escape($parent_stock_id))), $editkey);
}

function stock_component_items_list_cells($label, $name, $parent_stock_id,
	$selected_id=null, $all_option=false, $submit_on_change=false, $editkey = false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array('where'=>array("stock_id != ".db_escape($parent_stock_id)), 'cells'=>true),
		$editkey);
}
//------------------------------------------------------------------------------------






//------------------------------------------------------------------------------------
function stock_purchasable_items_list($name, $selected_id=null,
	$all_option=false, $submit_on_change=false, $all=false, $editkey=false)
{
	return stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array('where'=>array("mb_flag!= 'M'"),
			'show_inactive'=>$all), $editkey);
}
//
//	This helper is used in PO/GRN/PI entry and supports editable descriptions.
//
function stock_purchasable_items_list_cells($label, $name, $selected_id=null,
			$all_option=false, $submit_on_change=false, $editkey=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo stock_items_list($name, $selected_id, $all_option, $submit_on_change,
		array('where'=>array("mb_flag!= 'M'"),
			 'editable' => 30,
			 'cells'=>true), $editkey);
}
/*
function stock_purchasable_items_list_row($label, $name, $selected_id=null,
			$all_option=false, $submit_on_change=false, $editkey=false)
{
	echo "<tr><td class='label'>$label</td>";
	stock_purchasable_items_list_cells(null, $name, $selected_id=null,
		$all_option, $submit_on_change, $editkey);
	echo "</tr>\n";
}
*/


//------------------------------------------------------------------------------------


/*
 * quannh
 */

function tax_use_for_list_row($label, $name, $selected_id=null){
	echo "<tr><td class='label'>Transactions</td>";
	echo '<td>'
			.'<div class="checkboxgroup" >'.radio(null,$name,2,$selected_id).'<span>Sales</span></div>'
			.'<div class="checkboxgroup" >'.radio(null,$name,3,$selected_id).'<span>Purchase</span></div>'
			.'<div class="checkboxgroup" >'.radio(null,$name,1,$selected_id).'<span>Both</span></div>'

		.'</td>';
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------



//------------------------------------------------------------------------------------



//------------------------------------------------------------------------------------








//------------------------------------------------------------------------------------





//------------------------------------------------------------------------------------

function _format_template_items($row)
{
	return ($row[0] . "&nbsp;- &nbsp;" . _("Amount") . "&nbsp;".$row[1]);
}

function templates_list($name, $selected_id=null, $special_option=false)
{
	$sql = "SELECT sorder.order_no,	Sum(line.unit_price*line.quantity*(1-line.discount_percent)) AS OrderValue
		FROM ".TB_PREF."sales_orders as sorder, ".TB_PREF."sales_order_details as line
		WHERE sorder.order_no = line.order_no AND sorder.type = 1 GROUP BY line.order_no";
	return combo_input($name, $selected_id, $sql, 'order_no', 'OrderValue', array(
		'format' => '_format_template_items',
		'spec_option' => $special_option===true ? ' ' : $special_option,
		'order' => 'order_no', 'spec_id' => 0,
	));
}

function templates_list_cells($label, $name, $selected_id=null, $special_option=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo templates_list($name, $selected_id, $special_option);
	echo "</td>\n";
}

function templates_list_row($label, $name, $selected_id=null, $special_option=false)
{
	echo "<tr><td class='label'>$label</td>";
	templates_list_cells(null, $name, $selected_id, $special_option);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------

function workorders_list($name, $selected_id=null)
{
	$sql = "SELECT id, wo_ref FROM ".TB_PREF."workorders WHERE closed=0";
	return combo_input($name, $selected_id, $sql, 'id', 'wo_ref', array());
}

function workorders_list_cells($label, $name, $selected_id=null)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo workorders_list($name, $selected_id);
	echo "</td>\n";
}

function workorders_list_row($label, $name, $selected_id=null)
{
	echo "<tr><td class='label'>$label</td>";
	workorders_list_cells(null, $name, $selected_id);
	echo "</tr>\n";
}

//------------------------------------------------------------------------------------



//------------------------------------------------------------------------------------




//-----------------------------------------------------------------------------------------------



//-----------------------------------------------------------------------------------------------
function _format_date($row)
{
	return sql2date($row['reconciled']);
}



function bank_reconciliation_list_cells($label,$account, $name, $selected_id=null, $submit_on_change=false, $special_option=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo bank_reconciliation_list($account, $name, $selected_id, $submit_on_change, $special_option);
	echo "</td>\n";
}
/*
function bank_reconciliation_list_row($label, $account, $name, $selected_id=null, $submit_on_change=false, $special_option=false)
{
	echo "<tr>\n";
	bank_reconciliation_list_cells($label, $account, $name, $selected_id, $submit_on_change, $special_option);
	echo "</tr>\n";
}
*/
//-----------------------------------------------------------------------------------------------

function workcenter_list($name, $selected_id=null, $all_option=false)
{
	global $all_items;

	$sql = "SELECT id, name, inactive FROM ".TB_PREF."workcentres";

	return combo_input($name, $selected_id, $sql, 'id', 'name',
	array(
		'spec_option' =>$all_option===true ? _("All Suppliers") : $all_option,
		'spec_id' => $all_items,
	) );
}

function workcenter_list_cells($label, $name, $selected_id=null, $all_option=false)
{
	default_focus($name);
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo workcenter_list($name, $selected_id, $all_option);
	echo "</td>\n";
}

function workcenter_list_row($label, $name, $selected_id=null, $all_option=false)
{
	echo "<tr><td class='label'>$label</td>";
	workcenter_list_cells(null, $name, $selected_id, $all_option);
	echo "</tr>\n";
}


//-----------------------------------------------------------------------------------------------

function cash_accounts_list_row($label, $name, $selected_id=null, $submit_on_change=false)
{
	$sql = "SELECT ".TB_PREF."bank_accounts.id, bank_account_name, bank_curr_code, inactive
		FROM ".TB_PREF."bank_accounts
		WHERE ".TB_PREF."bank_accounts.account_type=".BT_CASH;

	if ($label != null)
		echo "<tr><td class='label'>$label</td>\n";
	echo "<td>";
	echo combo_input($name, $selected_id, $sql, 'id', 'bank_account_name',
	array(
		'format' => '_format_add_curr',
		'select_submit'=> $submit_on_change,
		'async' => true
	) );
	echo "</td></tr>\n";
}
//-----------------------------------------------------------------------------------------------

function pos_list_row($label, $name, $selected_id=null, $spec_option=false, $submit_on_change=false)
{
	$sql = "SELECT id, pos_name, inactive FROM ".TB_PREF."sales_pos";

	default_focus($name);
	echo '<tr>';
	if ($label != null)
		echo "<td class='label'>$label</td>\n";
	echo "<td>";

	echo combo_input($name, $selected_id, $sql, 'id', 'pos_name',
	array(
		'select_submit'=> $submit_on_change,
		'async' => true,
		'spec_option' =>$spec_option,
		'spec_id' => -1,
		'order'=> array('pos_name')
	) );
	echo "</td></tr>\n";

}





//-----------------------------------------------------------------------------------------------




function _format_account($row)
{
		return $row[0] .  "&nbsp;&nbsp;&nbsp;&nbsp;" . $row[1];
}


function gst_tax_types($name, $selected_id=null,
                            $none_option=false, $submit_on_change=false)
{
    //$sql = "SELECT * FROM  ";
    //selected

    if($selected_id == NULL)
    {
        $indus_id = "Select...";
    }else
    {
        $myrow = get_customer($selected_id);
        $indus_id = $myrow["industry_code"];
    }
    $items = array();
    $items['0'] = "industry 1";
    $items['1'] = "industry 2";
    $items['2'] = "industry 3";

    return array_selector($name, $selected_id, $items,
        array(
            'select_submit'=> $submit_on_change,
            'async' => false ) ); // FIX?

}




//------------------------------------------------------------------------------------------------


//------------------------------------------------------------------------------------------------


//------------------------------------------------------------------------------------------------

function dateformats_list_row($label, $name, $value=null)
{
	global $dateformats;

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $dateformats );
	echo "</td></tr>\n";
}

function dateseps_list_row($label, $name, $value=null)
{
	global $dateseps;

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $dateseps );
	echo "</td></tr>\n";
}

function thoseps_list_row($label, $name, $value=null)
{
	global $thoseps;

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $thoseps );
	echo "</td></tr>\n";
}

function decseps_list_row($label, $name, $value=null)
{
	global $decseps;

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $decseps );
	echo "</td></tr>\n";
}

function themes_list_row($label, $name, $value=null)
{
	global $path_to_root;

	$path = $path_to_root.'/themes/';
	$themes = array();
	$themedir = opendir($path);
	while(false !== ($fname = readdir($themedir)))
	{
		if($fname!='.' && $fname!='..' && $fname!='CVS' && is_dir($path.$fname))
		{
			$themes[$fname] =  $fname;
		}
	}
	ksort($themes);

	echo "<tr style='display:none;'><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $themes );
	echo "</td></tr>\n";
}

function pagesizes_list_row($label, $name, $value=null)
{
	global $pagesizes;

	$items = array();
	foreach ($pagesizes as $pz)
		$items[$pz] = $pz;

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $items );
	echo "</td></tr>\n";
}





function print_profiles_list_row($label, $name, $selected_id=null, $spec_opt=false,
	$submit_on_change=true)
{
	$sql = "SELECT profile FROM ".TB_PREF."print_profiles"
		." GROUP BY profile";
	$result = db_query($sql, 'cannot get all profile names');
	$profiles = array();
	while($myrow=db_fetch($result)) {
		$profiles[$myrow['profile']] = $myrow['profile'];
	}

	echo "<tr>";
	if ($label != null)
		echo "<td class='label'>$label</td>\n";
	echo "<td>";

	echo array_selector($name, $selected_id, $profiles,
		array( 'select_submit'=> $submit_on_change,
			'spec_option'=>$spec_opt,
			'spec_id' => ''
		 ));

	echo "</td></tr>\n";
}

function printers_list($name, $selected_id=null, $spec_opt=false, $submit_on_change=false)
{
	static $printers; // query only once for page display

	if (!$printers) {
		$sql = "SELECT id, name, description FROM ".TB_PREF."printers";
		$result = db_query($sql, 'cannot get all printers');
		$printers = array();
		while($myrow=db_fetch($result)) {
			$printers[$myrow['id']] = $myrow['name'].'&nbsp;-&nbsp;'.$myrow['description'];
		}
	}
	return array_selector($name, $selected_id, $printers,
		array( 'select_submit'=> $submit_on_change,
			'spec_option'=>$spec_opt,
			'spec_id' => ''
		 ));
}

//------------------------------------------------------------------------------------------------




//------------------------------------------------------------------------------------------------


function tab_list_row($label, $name, $selected_id=null)
{
	global $installed_extensions;

	$tabs = array();
	foreach ($_SESSION['App']->applications as $app) {
		$tabs[$app->id] = access_string($app->name, true);
	}
	echo "<tr>\n";
	echo "<td class='label'>$label</td><td>\n";
	echo array_selector($name, $selected_id, $tabs);
	echo "</td></tr>\n";
}

//-----------------------------------------------------------------------------------------------




//---------------------------------------------------------------------------------------------
//	List of sets of active extensions
//
function extset_list($name, $value=null, $submit_on_change=false)
{
	global $db_connections;

	$items = array();
	foreach ($db_connections as $comp)
		$items[] = sprintf(_("Activated for '%s'"), $comp['name']);
	return array_selector( $name, $value, $items,
		array(
			'spec_option'=> _("Available and/or installed"),
			'spec_id' => -1,
			'select_submit'=> $submit_on_change,
			'async' => true
		));
}



function payment_type_list_row($label, $name, $selected_id=null, $submit_on_change=false)
{
	global $pterm_types;

	echo "<tr><td class='label'>$label</td><td>";
	echo array_selector($name, $selected_id, $pterm_types,
		array(
			'select_submit'=> $submit_on_change
		) );
	echo "</td></tr>\n";
}

function coa_list_row($label, $name, $value=null)
{
	global $path_to_root, $installed_extensions;

	$path = $path_to_root.'/sql/';
	$coas = array();
	$sqldir = opendir($path);
	while (false !== ($fname = readdir($sqldir)))
	{
		if (is_file($path.$fname) && substr($fname,-4)=='.sql' && @($fname[2] == '_'))
		{
			$ext = array_search_value($fname, $installed_extensions, 'sql');
			if ($ext!=null) {
				$descr = $ext['name'];
			} elseif ($fname == 'en_US-new.sql') { // two standard COAs
				$descr = _("Standard new company American COA (4 digit)");
			} elseif ($fname == 'en_US-demo.sql') {
				$descr = _("Standard American COA (4 digit) with demo data");
			} else
				$descr = $fname;

			$coas[$fname] =  $descr;
		}
	}
	ksort($coas);

	echo "<tr><td class='label'>$label</td>\n<td>";
	echo array_selector( $name, $value, $coas );
	echo "</td></tr>\n";
}



/*
 * quannh 150330
 */
function gst_type_list($name, $selected_id=null, $spec_opt=false, $submit_on_change=false,$use_for=1){
	$sql = "SELECT id, name, inactive FROM ".TB_PREF."tax_types";
	if( in_array($use_for, array(2,3)) ){
		$sql .=" WHERE use_for=$use_for";
	}
	return combo_input($name, $selected_id, $sql, 'id', 'name',
			array('order'=>'id',
					'spec_option' => $spec_opt,
					'spec_id' => -1,
					'select_submit'=> $submit_on_change,
					'async' => true
			));
}

//-----------------------------------------------------------------------------------------------
function stock_sales_gst_type_list($name, $selected_id=null, $spec_opt=false, $submit_on_change=false)
{
	$sql = "SELECT id, name, inactive FROM ".TB_PREF."tax_types WHERE use_for=2";
	return combo_input($name, $selected_id, $sql, 'id', 'name',
 	array('order'=>'id',
		'spec_option' => $spec_opt,
		'spec_id' => -1,
 		'select_submit'=> $submit_on_change,
 		'async' => true
 	));
}

function stock_sales_gst_type_list_cells($label, $name, $selected_id=null, $spec_opt=false, $submit_on_change=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo stock_sales_gst_type_list($name, $selected_id, $spec_opt, $submit_on_change);
	echo "</td>\n";
}

function stock_sales_gst_type_list_row($label, $name, $selected_id=null, $spec_opt=false, $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	stock_sales_gst_type_list_cells(null, $name, $selected_id, $spec_opt, $submit_on_change);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------------------

//-----------------------------------------------------------------------------------------------
function stock_purchase_gst_type_list($name, $selected_id=null, $spec_opt=false, $submit_on_change=false)
{
	$sql = "SELECT id, name, inactive FROM ".TB_PREF."tax_types WHERE use_for=3";
	return combo_input($name, $selected_id, $sql, 'id', 'name',
 	array('order'=>'id',
		'spec_option' => $spec_opt,
		'spec_id' => -1,
 		'select_submit'=> $submit_on_change,
 		'async' => true
 	));
}

function stock_purchase_gst_type_list_cells($label, $name, $selected_id=null, $spec_opt=false, $submit_on_change=false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo stock_purchase_gst_type_list($name, $selected_id, $spec_opt, $submit_on_change);
	echo "</td>\n";
}

function stock_purchase_gst_type_list_row($label, $name, $selected_id=null, $spec_opt=false, $submit_on_change=false)
{
	echo "<tr><td class='label'>$label</td>";
	stock_purchase_gst_type_list_cells(null, $name, $selected_id, $spec_opt, $submit_on_change);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------------------
//----------1.2	Customer and Supplier Master ------------------------
function industry_code_list($name, $selected_id=null,
	$none_option=false, $submit_on_change=false)
{
	//$sql = "SELECT * FROM  ";
	//selected
	$items = array();
	$items['0'] = "Select...";
	$items['1'] = "DS";
	$items['2'] = "ZR";
	$items['3'] = "ZR ( E )";
	$items['4'] = "ZR ( L )";
	$items['5'] = "ES43";
	$items['6'] = "Other";


	return array_selector($name, $selected_id, $items,
		array(
			'order'=>'id',
			'spec_option' => $none_option,
			'spec_id' => 0,
			'select_submit'=> $submit_on_change,
			'async' => false
			) );
}

// function msic_section_list($name, $selected_id=null, $none_option=false, $submit_on_change=false){
// 	$items = file_get_contents("http://register.accountanttoday.net/msic/section");

// 	return combo_input_from_array($name, $selected_id, json_decode($items), 'id', 'name',
//  	array('order'=>'id',
// 		'spec_option' => '--Select Section--',
// 		'spec_id' => -1,
//  		'select_submit'=> $submit_on_change,
//  		'async' => true
//  	));
// }

// function msic_division_list($name, $selected_id=null,$section=0){
// 	$items_all = file_get_contents("http://register.accountanttoday.net/msic/division");
// 	$cache = combo_input_from_array('division_cache',$selected_id, json_decode($items_all), 'id', 'name',
//  	array('order'=>'id'));

// 	$items = array();
// 	if( $section > 0){
// 		$items = file_get_contents("http://register.accountanttoday.net/msic/division?parent=$section");
// 	}

// 	return combo_input($name, $selected_id, $items, 'id', 'name',
//  	array('order'=>'id',
// 		'spec_option' => '--Select Category--',
// 		'spec_id' => -1,
// //  		'select_submit'=> $submit_on_change,
//  		'async' => true
//  	)).'<span style="display:none;" >'.$cache.'</span>';
// }

// function msic_item_list($name, $selected_id=null, $divission=0){

// 	$items_all = file_get_contents("http://register.accountanttoday.net/msic/msic-items");

// 	//$cache = combo_input('item_cache', 0, $sqlFull, 'id', 'name',array('order'=>'id'));
// 	$cache = combo_input_from_array('item_cache',$selected_id, json_decode($items_all), 'id', 'name',array('order'=>'id'));


// // 	if( $selected_id > 0){
// // 		$sql = "SELECT id, code AS description, division AS parent FROM ".TB_PREF."msic_item WHERE id=$selected_id";
// // 	}

// 	$items = array();
// 	if( $selected_id > 0){
// 		$items = file_get_contents("http://register.accountanttoday.net/msic/msic/msic-items?parent=$selected_id");
// 	}

// 	return combo_input($name, $selected_id, $items, 'id', 'name',

//  	array('order'=>'id',
// 		'spec_option' => '--Select MSIC Code--',
// 		'spec_id' => -1,
// //  		'select_submit'=> $submit_on_change,
//  		'async' => true
//  	)).'<span style="display:none;" >'.$cache.'</span>';;
// }

// function industry_code_list_cells($label, $name, $selected_msic=null, $none_option=false, $submit_on_change=false){
// 	if ($label != null)
// 		echo "<td>$label</td>\n";
// 	echo "<td>";
// 	$msic_division = 0;
// 	$msic_section = 0;
// 	if( $selected_msic && $selected_msic > 0){

// 		$sqlGETMsic="SELECT * FROM ".TB_PREF."msic_item WHERE id=".db_escape($selected_msic);
// 		$msic =  db_fetch(db_query($sqlGETMsic));
// 		if( $msic && isset($msic['division']) ){
// 			$msic_division = $msic['division'];
// 			if( $msic && isset($msic['division']) ){
// 				$sqlGETSection="SELECT * FROM ".TB_PREF."msic_division WHERE id=".db_escape($msic_division);
// 				$division =  db_fetch(db_query($sqlGETSection));
// 				if( $division && isset($division['section']) ){
// 					$msic_section = $division['section'];
// 				}
// 			}

// 		}
// 	}


// 	$selected_division = '';
// 	echo msic_section_list($name.'_section', $msic_section, $none_option, $submit_on_change).'<br>';
// 	echo msic_division_list($name.'_division', $msic_division,$msic_section).'<br>';
// 	echo msic_item_list($name.'_msic', $selected_msic, $none_option, $submit_on_change).'<br>';
// 	echo "</td>\n";
// }

function industry_code_list_row($label, $name, $selected_id=null, $none_option=false, $submit_on_change=false){
	echo "<tr><td class='label'>$label</td>";
	industry_code_list_cells(null, $name, $selected_id, $none_option, $submit_on_change);
	echo "</tr>\n";
}

// function section_code_list_row($label, $name, $selected_id=null, $none_option=false, $submit_on_change=false){
// 	echo "<tr><td class='label'>$label</td><td>";
// 	echo msic_section_list($name.'_section', $selected_id, $none_option, $submit_on_change);
// 	echo "</td></tr>";
// }

// function division_code_list_row($label, $name, $selected_id=null, $msic_section=0){
// 	echo "<tr><td class='label'>$label</td><td>";
// 	echo msic_division_list($name, $selected_id, $msic_section);
// 	echo "</td></tr>";
// }

///////////radio gst by customer//////////////
function customer_tax_list($name, $selected_id=null, $none_option=false, $submit_on_change=false) {
	$sql = "SELECT id, name, gst_03_type FROM ".TB_PREF."tax_types WHERE use_for=2 ";
	//selected

	//if($selected_id == NULL)
	//{
	//	$tax_type = "Select GST Type";
	//}else
	//{
		//$myrow = get_customer($selected_id);
		//$tax_type = $myrow["customer_tax_id"];
	//}


	$options =array(
		'order' => 'id',
		'spec_option' => $none_option,
		'spec_id' => ALL_NUMERIC,
		'select_submit'=> $submit_on_change,
		'async' => false,
	);
	$company = get_company_prefs();
	if( !isset($company['gst_no']) || trim($company['gst_no'])==''){
		$options['disabled'] = true;
	}
	return combo_input('customer_gst_03_type', $selected_id, $sql, 'id', 'customer_tax_id',$options);
}
function customer_tax_list_cells($label, $name, $selected_id=null, $none_option=false, $submit_on_change=false)
{

	echo "<td>";
	echo customer_tax_list($name, $selected_id, $none_option, $submit_on_change);
	echo "</td></tr>";
}
function customer_tax_list_row($label, $name, $selected_id=null, $none_option=false, $submit_on_change=false)
{

	customer_tax_list_cells(null, $name, $selected_id, $none_option, $submit_on_change);

}
///////////radio gst by supplier//////////////
function supplier_tax_list($name, $selected_id=null,
	$none_option=false, $submit_on_change=false)
{
	$sql = "SELECT id, name FROM ".TB_PREF."tax_types WHERE use_for=3";

	return combo_input('supplier_gst_03_type', $selected_id, $sql, 'id', 'supplier_tax_id',
	array(
		'order' => 'id',
		'spec_option' => $none_option,
		'spec_id' => ALL_NUMERIC,
		'select_submit'=> $submit_on_change,
		'async' => false,
	) );
}
function supplier_tax_list_cells($label, $name, $selected_id=null, $none_option=false, $submit_on_change=false)
{

	echo "<td>";
	echo supplier_tax_list($name, $selected_id, $none_option, $submit_on_change);
	echo "</td></tr>";
}
function supplier_tax_list_row($label, $name, $selected_id=null, $none_option=false, $submit_on_change=false)
{

	supplier_tax_list_cells(null, $name, $selected_id, $none_option, $submit_on_change);

}

//TUANVT5
function stock_invoice_list($name, $selected_id=null, $spec_opt=false, $submit_on_change=false,$use_for=''){
	$sql = "SELECT id, CONCAT(SUBSTRING(name FROM INSTR(name,'(')+1 FOR (LENGTH(name)-INSTR(name,'(')-1)),'(',rate,'%)') as name, inactive FROM ".TB_PREF."tax_types";
	if( $use_for !='' ){
		$sql.=' WHERE use_for IN ('.$use_for.')';
	}

	$options = array('order'=>'id',
		'spec_option' => $spec_opt,
		'spec_id' => -1,
 		'select_submit'=> $submit_on_change,
 		'async' => true
 	);
	$company = get_company_prefs();
	if( !isset($company['gst_no']) || trim($company['gst_no'])==''){
		$options['disabled'] = true;
	}

	return combo_input($name, $selected_id, $sql, 'id', 'name',$options);
}

function stock_invoice_list_cells($label, $name, $selected_id=null, $spec_opt=false, $submit_on_change=false,$use_for=''){
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo stock_invoice_list($name, $selected_id, $spec_opt, $submit_on_change,$use_for);
	echo "</td>\n";
}

function stock_invoice_list_row($label, $name, $selected_id=null, $spec_opt=false, $submit_on_change=false, $use_for=''){
	echo stock_invoice_list_cells(null, $name, $selected_id, $spec_opt, $submit_on_change,$use_for);
}
?>
