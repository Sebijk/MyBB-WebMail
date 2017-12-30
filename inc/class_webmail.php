<?php
/*==============================================================*\
||   MyBB-Webmail						                        						||
||   © 2007 - 2017 Home of the Sebijk.com	  										||
||   Website: http://www.sebijk.com  							              ||
||   Lizenz: GPL 3.0														        				||
\*==============================================================*/

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

class sj_Webmail
{
 var $username = "user";
 var $password = "password";
 var $server = "";

	function close($webmail_mbox)
	{
		return imap_close($webmail_mbox);
	}

	function connect()
	{
		$this->open_connect=@imap_open($this->server, $this->username, $this->password) or error("Ihre Anmeldung konnte nicht erfolgreich ausgef&uuml;hrt werden.
		<br />MyBB-Webmail meldet: ".$this->error()."
		<br />Wenn Sie versehentlich das Kennwort falsch eingegeben haben, so k&ouml;nnen Sie sich <a href=\"webmail.php?do=logout\">hier</a> wieder neu anmelden.");
		return $this->open_connect;
	}

	function delete($webmail_mbox, $num)
	{
		return imap_delete($webmail_mbox, $num);
	}

	function error()
	{
		return imap_last_error();
	}

	function expunge($webmail_mbox)
	{
		return imap_expunge($webmail_mbox);
	}

	function get_body($webmail_mbox, $num)
	{
		return imap_body($webmail_mbox, $num);
	}

	function get_header($webmail_mbox, $num)
	{
		return imap_header($webmail_mbox, $num);
	}

	function get_num_msg($webmail_mbox)
	{
		return imap_num_msg($webmail_mbox);
	}


}
?>
