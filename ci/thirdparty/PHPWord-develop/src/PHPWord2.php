<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$file = (BASEPATH.'thirdparty/PHPWord-develop/src/PHPWord.php');
require_once  $file;
// require_once APPPATH."/third_party/PHPWord.php";
use PhpOffice\PhpWord;

require_once BASEPATH.'thirdparty/PHPWord-develop/src/PhpWord/Autoloader.php';
PHPWord_Autoloader::register();

include_once BASEPATH.'thirdparty/Zend/stdlib/StringUtils.php';
include_once BASEPATH.'thirdparty/PHPWord-develop/src/Common/XMLWriter.php';

class Word extends PhpOffice\PHPWord\PHPWord {
    public function __construct() {
        parent::__construct();
    }
}