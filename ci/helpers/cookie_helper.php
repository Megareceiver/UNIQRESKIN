<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('set_cookie'))
{
	function set_cookie($name = '', $value = '', $expire = 3600, $domain = '', $path = '/', $prefix = '', $secure = true){
		// Set the config file options
		$CI = get_instance();

		$CI->input->set_cookie($name, serialize($value), $expire, $domain, $path, $prefix, $secure);
	}
}

// --------------------------------------------------------------------

/**
 * Fetch an item from the COOKIE array
 *
 * @access	public
 * @param	string
 * @param	bool
 * @return	mixed
 */
if ( ! function_exists('get_cookie')) {
	function get_cookie($index = '', $xss_clean = FALSE){
		$CI = get_instance();

		$prefix = '';

		if ( ! isset($_COOKIE[$index]) && config_item('cookie_prefix') != ''){
			$prefix = config_item('cookie_prefix');
		}
        $value = $CI->input->cookie($prefix.$index, $xss_clean);
        $value = unserialize($value);
        return $value;
	}
}

// --------------------------------------------------------------------

/**
 * Delete a COOKIE
 *
 * @param	mixed
 * @param	string	the cookie domain.  Usually:  .yourdomain.com
 * @param	string	the cookie path
 * @param	string	the cookie prefix
 * @return	void
 */
if ( ! function_exists('delete_cookie'))
{
	function delete_cookie($name = '', $domain = '', $path = '/', $prefix = '')
	{
		set_cookie($name, '', '', $domain, $path, $prefix);
	}
}


/* End of file cookie_helper.php */
/* Location: ./system/helpers/cookie_helper.php */