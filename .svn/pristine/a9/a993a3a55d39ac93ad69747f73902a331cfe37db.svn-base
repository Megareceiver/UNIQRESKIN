<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('force_download')){
	function force_download($filename = '', $data = ''){
		if ($filename == '' OR $data == ''){
			return FALSE;
		}

		// Try to determine if the filename includes a file extension.
		// We need it in order to set the MIME type
		if (FALSE === strpos($filename, '.')) {
			return FALSE;
		}

		// Grab the file extension
		$x = explode('.', $filename);
		$extension = end($x);

		// Load the mime types
		if (is_file(BASEPATH.'config/mimes.php'))
		{
			include(BASEPATH.'config/mimes.php');
		}

		// Set a default mime if we can't find it
		if ( ! isset($mimes[$extension]))
		{
			$mime = 'application/octet-stream';
		}
		else
		{
			$mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
		}

		// Generate the server headers
		if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE)
		{
			header('Content-Type: '.$mime);
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Content-Transfer-Encoding: binary");
			header('Pragma: public');
			header("Content-Length: ".strlen($data));
		}
		else
		{
			header('Content-Type: '.$mime);
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			header("Content-Length: ".strlen($data));
		}

		exit($data);
	}
}

if ( ! function_exists('read_file')){
	function read_file($file){
		if ( ! file_exists($file)) {
			return FALSE;
		}

		if (function_exists('file_get_contents')){
			return file_get_contents($file);
		}

		if ( ! $fp = @fopen($file, FOPEN_READ)){
			return FALSE;
		}

		flock($fp, LOCK_SH);

		$data = '';
		if (filesize($file) > 0){
			$data =& fread($fp, filesize($file));
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		return $data;
	}
}

function save_file_content($name,$content=null){
    file_put_contents(ROOT."/account/".$name,$content);

}

function check_dir( $fpath ){
    if( !is_string($fpath) ) return ;

    $forders = explode('/',$fpath);
    $dir_check =  (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '' : '/' ;

    foreach ($forders AS $k=>$dir){
        if( trim($dir) =='' )
            continue;
        $dir_check .= $dir.'/';
        if( !is_dir($dir_check) ){
            mkdir($dir_check, 0777);
        }
    }
    return realpath($dir_check);
}

function compress($buffer) {
   $buffer= preg_replace('/<!--(.|\s)*?-->/', '', $buffer);
   $search = array(
                '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
            '/[^\S ]+\</s',  // strip whitespaces before tags, except space
            '/(\s)+/s'       // shorten multiple whitespace sequences
          );

      $replace = array(
         '>',
         '<',
         '\\1'
      );

      $buffer = preg_replace($search, $replace, $buffer);
      return ($buffer);
}
