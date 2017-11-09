<?php if ( ! defined('BASEPATH') || !class_exists('CI_Model')) exit('No direct script access allowed');
class Log_Log_Model extends CI_Model {

    function __construct(){
//         die('call me');

        if( !$this->db->table_exists('log') ){

            $this->db->query("CREATE TABLE IF NOT EXISTS `log` (
    			    `id` int(11) NOT NULL,
    			    `table` char(100) NOT NULL,
                  `table_id` char(20) NOT NULL,
                  `action` int(1) NOT NULL DEFAULT '0',
                  `data_old` text,
                  `data_new` text,
                  `uid` int(11) NOT NULL,
                  `ip_address` varchar(20) NOT NULL,
                  `user_agent` char(100) NOT NULL,
                  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

            $this->db->query("ALTER TABLE `log` ADD PRIMARY KEY (`id`), ADD KEY `id` (`id`);");
            $this->db->query("ALTER TABLE `log` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
        }


    }


    function add($data=NULL){
        if( !$data || !is_array($data) || empty($data) ) 
            return false;
        $data['uid'] =$_SESSION["wa_current_user"]->user;
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        if( isset($data['data_old']) ) 
            unset($data['data_old']);
        if( isset($data['data_new']) ) 
            unset($data['data_new']);

        $this->db->insert('log',$data);
        return $this->db->insert_id();

    }

}