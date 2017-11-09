<?php

/*
 * Shippers input options
 */
function shippers_list($name, $selected_id = null, $class = NULL)
{
    $sql = "SELECT shipper_id, shipper_name, inactive FROM " . TB_PREF . "shippers";
    return combo_input($name, $selected_id, $sql, 'shipper_id', 'shipper_name', array(
        'order' => array(
            'shipper_name'
        ),
        'class' => $class
    ));
}


/*
 *
 */
function currencies_list($name, $selected_id = null, $submit_on_change = false, $class = NULL)
{
    global $ci;
    $sql = "SELECT curr_abrev, currency, inactive FROM " . TB_PREF . "currencies";

    // default to the company currency
    $disabled = false;

    if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/admin/company_preferences.php') {
        $gl_check = $ci->db->select('count(counter) AS total')
            ->get('gl_trans')
            ->row();
        if ($gl_check && $gl_check->total > 0) {
            $disabled = true;
        }
    }

    return combo_input($name, $selected_id, $sql, 'curr_abrev', 'currency', array(
        'select_submit' => $submit_on_change,
        'default' => get_company_currency(),
        'async' => false,
        'disabled' => $disabled,
        'class' => $class
    ));
}

/*
 *
 */
function _format_fiscalyears($row)
{
    return sql2date($row[1]) . "&nbsp;-&nbsp;" . sql2date($row[2]) . "&nbsp;&nbsp;" . ($row[3] ? _('Closed') : _('Active')) . "</option>\n";
}

function fiscalyears_list($name, $selected_id = null, $submit_on_change = false, $class = NULL)
{
    $bootstrap = get_instance()->bootstrap;
    if (strlen($class) < 1) {
        $class = $bootstrap->input_class;
    }
    $sql = "SELECT * FROM " . TB_PREF . "fiscal_year";

    // default to the company current fiscal year

    return combo_input($name, $selected_id, $sql, 'id', '', array(
        'order' => 'begin',
        'default' => get_company_pref('f_year'),
        'format' => '_format_fiscalyears',
        'select_submit' => $submit_on_change,
        'async' => false,
        'class' => $class
    ));
}

/*
 *
 */

// ------------------------------------------------------------------------------------------------
function security_roles_list($name, $selected_id = null, $new_item = false, $submit_on_change = false, $show_inactive = false, $class_of_input = NULL)
{
    global $all_items;

    $sql = "SELECT id, role, inactive FROM " . TB_PREF . "security_roles";

    return combo_input($name, $selected_id, $sql, 'id', 'description', array(
        'spec_option' => $new_item ? _("New role") : false,
        'spec_id' => '',
        'select_submit' => $submit_on_change,
        'show_inactive' => $show_inactive,
        'class' => $class_of_input
    ));
}

// ------------------------------------------------------------------------------------
function sales_areas_list($name, $selected_id = null, $class_of_input = NULL)
{
    $sql = "SELECT area_code, description, inactive FROM " . TB_PREF . "areas";

    if (! $class_of_input) {
        $class_of_input = get_instance()->bootstrap->input_class;
    }
    return combo_input($name, $selected_id, $sql, 'area_code', 'description', array(
        'class' => $class_of_input
    ));
}

// -------------------------------------------------------------------------------------
function sales_persons_list($name, $selected_id = null, $spec_opt = false, $class_of_input = NULL)
{
    $sql = "SELECT salesman_code, salesman_name, inactive FROM " . TB_PREF . "salesman";
    return combo_input($name, $selected_id, $sql, 'salesman_code', 'salesman_name', array(
        'order' => array(
            'salesman_name'
        ),
        'spec_option' => $spec_opt,
        'spec_id' => ALL_NUMERIC,
        'class' => $class_of_input
    ));
}

/*
 *
 */
function yesno_list($name, $selected_id = null, $name_yes = "", $name_no = "", $submit_on_change = false, $class_of_input = NULL)
{
    $items = array();
    $items['0'] = strlen($name_no) ? $name_no : _("No");
    $items['1'] = strlen($name_yes) ? $name_yes : _("Yes");

    if (! $class_of_input) {
        $class_of_input = get_instance()->bootstrap->input_class;
    }
    return array_selector($name, $selected_id, $items, array(
        'select_submit' => $submit_on_change,
        'async' => false,
        'class' => $class_of_input
    )); // FIX?
}



/*
 *
 */
function payment_services($name, $class_of_input = NULL)
{
    global $payment_services;

    $services = array_combine(array_keys($payment_services), array_keys($payment_services));

    return array_selector($name, null, $services, array(
        'spec_option' => _("No payment Link"),
        'spec_id' => '',
        'class' => $class_of_input
    ));
}

/*
 * Dimensions
 * ---------------------------------------------------------------------------------------
 */
function dimensions_list($name, $selected_id = null, $no_option = false, $showname = ' ', $submit_on_change = false, $showclosed = false, $showtype = 1, $class_of_input = NULL)
{
    $sql = "SELECT id, CONCAT(reference,'  ',name) as ref FROM " . TB_PREF . "dimensions";

    $options = array(
        'order' => 'reference',
        'spec_option' => $no_option ? $showname : false,
        'spec_id' => 0,
        'select_submit' => $submit_on_change,
        'async' => false,
        'class' => $class_of_input." ".get_instance()->bootstrap->input_class
    );

    if (! $showclosed)
        $options['where'][] = "closed=0";
    if ($showtype)
        $options['where'][] = "type_=" . db_escape($showtype);

    return combo_input($name, $selected_id, $sql, 'id', 'ref', $options);
}

/*
 *
 */



/*
 *
 */
function gl_systypes_list($name, $value = null, $spec_opt = false)
{
    global $systypes_array;

    $types = $systypes_array;
    $bootstrap = get_instance()->bootstrap;
    foreach (array(
        ST_LOCTRANSFER,
        ST_PURCHORDER,
        ST_SUPPRECEIVE,
        ST_MANUISSUE,
        ST_MANURECEIVE,
        ST_SALESORDER,
        ST_SALESQUOTE,
        ST_DIMENSION
    ) as $type)
        unset($types[$type]);

    return array_selector($name, $value, $types, array(
        'spec_option' => $spec_opt,
        'spec_id' => ALL_NUMERIC,
        'async' => false,
        'class' => $bootstrap->input_class
    ));
}

/*
 *
 */
function payment_terms_list($name, $selected_id = null)
{
    $sql = "SELECT terms_indicator, terms, inactive FROM " . TB_PREF . "payment_terms";
    return combo_input($name, $selected_id, $sql, 'terms_indicator', 'terms', array(
        'class' => get_instance()->bootstrap->input_class
    ));
}

function credit_status_list($name, $selected_id = null)
{
    $sql = "SELECT id, reason_description, inactive FROM " . TB_PREF . "credit_status";
    return combo_input($name, $selected_id, $sql, 'id', 'reason_description', array(
        'class' => get_instance()->bootstrap->input_class
    ));
}

/*
 *
 */
function tax_groups_list($name, $selected_id = null, $none_option = false, $submit_on_change = false)
{
    $sql = "SELECT id, name FROM " . TB_PREF . "tax_groups";

    return combo_input($name, $selected_id, $sql, 'id', 'name', array(
        'order' => 'id',
        'spec_option' => $none_option,
        'spec_id' => ALL_NUMERIC,
        'select_submit' => $submit_on_change,
        'async' => false,
        'class' => get_instance()->bootstrap->input_class
    ));
}

/*
 *
 */


/*
 *
 */
function crm_category_types_list($name, $selected_id = null, $filter = array(), $submit_on_change = true)
{
    $sql = "SELECT id, name, type, inactive FROM " . TB_PREF . "crm_categories";

    $multi = false;
    $groups = false;
    $where = array();
    if (@$filter['class']) {
        $where[] = 'type=' . db_escape($filter['class']);
    } else
        $groups = 'type';
    if (@$filter['subclass'])
        $where[] = 'action=' . db_escape($filter['subclass']);
    if (@$filter['entity'])
        $where[] = 'entity_id=' . db_escape($filter['entity']);
    if (@$filter['multi']) { // contact category selector for person
        $multi = true;
    }

    return combo_input($name, $selected_id, $sql, 'id', 'name', array(
        'multi' => $multi,
        'height' => $multi ? 5 : 1,
        'category' => $groups,
        'select_submit' => $submit_on_change,
        'async' => true,
        'where' => $where,
        'class' => get_instance()->bootstrap->input_class
    ));
}

/*
 *
 */
function languages_list($name, $selected_id = null, $all_option = false)
{
    global $installed_languages;

    $items = array();
    if ($all_option)
        $items[''] = $all_option;
    foreach ($installed_languages as $lang)
        $items[$lang['code']] = $lang['name'];
    return array_selector($name, $selected_id, $items);
}



