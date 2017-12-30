<?php
/*==============================================================*\
||   MyBB-Webmail						                        ||
||   © 2007 - 2017 Home of the Sebijk.com	  					||
||   Website: http://www.sebijk.com  							||
||   Lizenz: GPL 3.0											||
\*==============================================================*/

define("IN_MYBB", 1);

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$templatelist = "webmail_start,webmail_login,webmail_listmails,webmail_menu,webmail_mailread,webmail_newmessage";
$webmail_version = "0.52 Beta";

$webmail_footer = "<br /><div id=\"copyright\"><center>Powered by MyBB-Webmail Version ".$webmail_version."<br />
Copyright &copy; 2007 - ".date("Y")." <a href=\"http://www.sebijk.com\" target=\"blank\">Home of the Sebijk.com</a></div></center>";

require_once("./global.php");

if (!function_exists('imap_open')) error("Sie k&ouml;nnen MyBB-Webmail leider nicht benutzen, da das IMAP-Modul f&uuml;r PHP nicht installiert ist.");

if($mybb->input['do'] == "logout") {
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0");
	header("Pragma: no-cache");
	header('HTTP/1.0 401 Unauthorized');
	header("Location: webmail.php?olduser=".strip_tags($_SERVER['PHP_AUTH_USER']));
}

if ((!isset($_SERVER['PHP_AUTH_USER'])) || ($mybb->input['olduser'] == $_SERVER['PHP_AUTH_USER'])) {
	if (!headers_sent()) {
		unset($_SERVER['PHP_AUTH_USER']);
		unset($_SERVER['PHP_AUTH_PW']);
		header("WWW-Authenticate: Basic realm=\"Webmail Login\"");
		header('HTTP/1.0 401 Unauthorized');
	}
}

require_once(MYBB_ROOT."inc/class_webmail.php");
$mybb_webmail=new sj_Webmail;

$mybb_webmail->username = strip_tags($_SERVER['PHP_AUTH_USER']);
$mybb_webmail->password = strip_tags($_SERVER['PHP_AUTH_PW']);
$mybb_webmail->server = "{".$mybb->settings['webmail_servers']."/".$mybb->settings['webmail_port']."/novalidate-cert}";
add_breadcrumb("Webmail-Posteingang", "webmail.php");

if(($mybb->input['do'] == "start") || (!$mybb->input['do'])) {
	// Verbindung zu dem Postfach erstellen
	$webmail_mbox = $mybb_webmail->connect();

		for($i = 1; $i <= $mybb_webmail->get_num_msg($webmail_mbox); $i++) {
		$webmail_header = $mybb_webmail->get_header($webmail_mbox, $i);
		$webmail_host = $webmail_header->from[0]->host;
		$webmail_box = $webmail_header->from[0]->mailbox;
		$webmail_date = gmdate("d.m.Y H:i:s",strtotime($webmail_header->date));
		eval("\$webmail_listmails .= \"".$templates->get("webmail_listmails")."\";");
	}
	$mybb_webmail->close($webmail_mbox);
	eval("\$webmail_menu .= \"".$templates->get("webmail_menu")."\";");
	eval("\$webmail_start .= \"".$templates->get("webmail_start")."\";");
	output_page($webmail_start);
}

if($mybb->input['do'] == "new") {
	add_breadcrumb("Neue Nachricht erfassen");
	$lang->load("private");

	// Editor initialisieren
	if($mybb->settings['bbcodeinserter'] != "off" && $forum['allowmycode'] != "no" && (!$mybb->user['uid'] || $mybb->user['showcodebuttons'] != 0))
	{
		$codebuttons = build_mycode_inserter();
		if($forum['allowsmilies'] != "no")
		{
			$smilieinserter = build_clickable_smilies();
		}
	}

	eval("\$webmail_menu .= \"".$templates->get("webmail_menu")."\";");
	eval("\$webmail_newmessage .= \"".$templates->get("webmail_newmessage")."\";");
	output_page($webmail_newmessage);
}

if($mybb->input['do'] == "reply") {
	$lang->load("private");

	$num = intval($mybb->input['num']);
	// Verbindung zu dem Postfach erstellen
	$webmail_mbox = $mybb_webmail->connect();
	$webmail_header = $mybb_webmail->get_header($webmail_mbox, $num);
	if($webmail_header->reply_toaddress) $compose_to = strip_tags($webmail_header->reply_toaddress);
	else $compose_to = strip_tags($webmail_header->fromaddress);
	if (!preg_match("/^Re: /i", $webmail_header->subject) OR !preg_match("/^AW: /i", $webmail_header->subject)) $subject = "AW: ".$webmail_header->subject;
	add_breadcrumb($webmail_header->subject, "webmail.php?do=read&num=".$num);
	add_breadcrumb("Antworten");
	$webmail_mtext = quoted_printable_decode($mybb_webmail->get_body($webmail_mbox, $num));
	$message = "[quote=$compose_to]\n$webmail_mtext\n[/quote]";
	$mybb_webmail->close($webmail_mbox);

	// Editor initialisieren
	if($mybb->settings['bbcodeinserter'] != "off" && $forum['allowmycode'] != "no" && (!$mybb->user['uid'] || $mybb->user['showcodebuttons'] != 0))
	{
		$codebuttons = build_mycode_inserter();
		if($forum['allowsmilies'] != "no")
		{
			$smilieinserter = build_clickable_smilies();
		}
	}

	eval("\$webmail_menu .= \"".$templates->get("webmail_menu")."\";");
	eval("\$webmail_newmessage .= \"".$templates->get("webmail_newmessage")."\";");
	output_page($webmail_newmessage);
}

if($mybb->input['do'] == "forward") {
	$lang->load("private");

	$num = intval($mybb->input['num']);

	// Verbindung zu dem Postfach erstellen
	$webmail_mbox = $mybb_webmail->connect();
	$webmail_header = $mybb_webmail->get_header($webmail_mbox, $num);
	if($webmail_header->reply_toaddress) $compose_to = strip_tags($webmail_header->reply_toaddress);
	else $compose_to = strip_tags($webmail_header->fromaddress);
	if (!preg_match("/^Fwd: /i", $webmail_header->subject) OR !preg_match("/^Fw: /i", $webmail_header->subject)) $subject = "Fwd: ".$webmail_header->subject;
	add_breadcrumb($webmail_header->subject, "webmail.php?do=read&amp;num=".$num);
	add_breadcrumb("Weiterleiten");
	$webmail_mtext = quoted_printable_decode($mybb_webmail->get_body($webmail_mbox, $num));
	$message = "[quote=".$compose_to."]\n".$webmail_mtext."\n[/quote]";
	$mybb_webmail->close($webmail_mbox);

	// Editor initialisieren
	if($mybb->settings['bbcodeinserter'] != "off" && $forum['allowmycode'] != "no" && (!$mybb->user['uid'] || $mybb->user['showcodebuttons'] != 0))
	{
		$codebuttons = build_mycode_inserter();
		if($forum['allowsmilies'] != "no")
		{
			$smilieinserter = build_clickable_smilies();
		}
	}

	eval("\$webmail_menu .= \"".$templates->get("webmail_menu")."\";");
	eval("\$webmail_newmessage .= \"".$templates->get("webmail_newmessage")."\";");
	output_page($webmail_newmessage);
}


if($mybb->input['do'] == "delete") {

	$num = intval($mybb->input['num']);

	// Verbindung zu dem Postfach erstellen
	$webmail_mbox = $mybb_webmail->connect();
	$mybb_webmail->delete($webmail_mbox, $num);
	$mybb_webmail->expunge($webmail_mbox);
	$mybb_webmail->close($webmail_mbox);
	redirect("webmail.php", "Die E-Mail wurde gel&ouml;scht!");
}


if($mybb->input['do'] == "send") {
	require_once(MYBB_ROOT."inc/class_parser.php");
	$parser = new postParser;

	$mail_from = strip_tags($_SERVER['PHP_AUTH_USER']);
	$webmail_to = htmlspecialchars_uni($_POST['to']);
	$webmail_subject = htmlspecialchars_uni($_POST['subject']);
	$webmail_text = $_POST['text'];
	$webmail_bcc = htmlspecialchars_uni($_POST['bcc']);
	$webmail_mailtype = htmlspecialchars_uni($_POST['mailtype']);

	$valid_mailaddress = "/^[\.0-9a-z-]+@([0-9-a-z][0-9-a-z-]+\.)+[a-z]{2,4}$/i";

	if(!$webmail_subject) $webmail_subject = "(Kein Betreff)";
	if(!$webmail_to) error("Sie m&uuml;ssen einen Empf&auml;nger angeben!");
	if(!preg_match($valid_mailaddress,$webmail_to)) error("Die E-Mail-Adresse sieht komisch aus!");

	if($webmail_mailtype == "html") {
		$parser_options = array(
			"allow_html" => 1,
			"allow_mycode" => 1,
			"allow_smilies" => 1,
			"allow_imgcode" => 1,
			"allow_videocode" => 1,
			"filter_badwords" => 0
		);
		$webmail_htmlheader = "Content-Type: text/html\n";
		$webmail_htmlheader .= "Content-Transfer-Encoding: 8bit\n";
		$webmail_text = $parser->parse_message($webmail_text, $parser_options);
		$webmail_htmltext = "<html>\n<head>\n<title>".$webmail_subject."</title>\n";
		$webmail_htmltext .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset={$charset}\" />\n";
		$webmail_htmltext .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$theme['css_url']}\" /></head>\n";
		$webmail_htmltext .= "<body bgcolor=\"#ffffff\"><div align=\"left\">".$webmail_text."\n</div>\n</body>\n</html>";
		$webmail_text = $webmail_htmltext;
	}

	if(!$webmail_text) error("Bitte geben Sie einen Nachrichtentext an!");

	if($webmail_bcc) {
		if(!preg_match($valid_mailaddress,$webmail_bcc)) error("Die E-Mail-Adresse sieht komisch aus!");
		$webmail_bcc_header = "Bcc: ".$webmail_bcc."\n";
	}

	$webmail_contentheader = "From: ".$mail_from."\n";
	if(isset($webmail_bcc_header)) $webmail_contentheader .= $webmail_bcc_header;
	if(isset($webmail_htmlheader)) $webmail_contentheader .= $webmail_htmlheader;
	$webmail_contentheader .= "X-Mailer: Mail via MyBB-Webmail";


	$email_senden = mail($webmail_to, $webmail_subject, $webmail_text, $webmail_contentheader);

	if(!$email_senden) error("Ein Fehler ist aufgetreten!");
	else redirect("webmail.php", "Ihre Nachricht wurde weitergeleitet!");
}

if($mybb->input['do'] == "read") {
	require_once(MYBB_ROOT."inc/class_parser.php");
	$lang->load("private");

	$parser = new postParser;
	$num = intval($mybb->input['num']);

	// Verbindung zu dem Postfach erstellen
	$webmail_mbox = $mybb_webmail->connect();
	$webmail_header = $mybb_webmail->get_header($webmail_mbox, $num);
	add_breadcrumb($webmail_header->subject);
	$webmail_date = gmdate("d.m.Y H:i:s",strtotime($webmail_header->date));
	$webmail_host = $webmail_header->from[0]->host;
	$webmail_box = $webmail_header->from[0]->mailbox;
	$webmail_from = $webmail_box."@".$webmail_host;
	$webmail_date_sent = gmdate("d.m.Y H:i:s",strtotime($webmail_header->MailDate));
	$webmail_mtext = quoted_printable_decode($mybb_webmail->get_body($webmail_mbox, $num));
	$mybb_webmail->close($webmail_mbox);

	if($webmail_header->Content-Type=="text/html" AND !$mybb->input['type'] == "frame") {
		$parser_options = array(
			"allow_html" => 1,
			"allow_mycode" => 1,
			"allow_smilies" => 1,
			"allow_imgcode" => 1,
			"allow_videocode" => 1,
			"filter_badwords" => 0
		);
		$webmail_mtext = "<iframe border=\"0\" frameborder=\"0\" src=\"webmail.php?do=read&amp;type=frame&amp;num=".$num."\" width=\"100%\" height=\"100%\"></iframe>";
	}
	elseif($mybb->input['type'] == "frame") {
			$parser_options = array(
			"allow_html" => 1,
			"allow_mycode" => 1,
			"allow_smilies" => 1,
			"allow_imgcode" => 1,
			"allow_videocode" => 1,
			"filter_badwords" => 0
		);
		$webmail_mtext = $parser->parse_message($webmail_mtext, $parser_options);
		echo $webmail_mtext;
		exit;
	}
	else {
		$parser_options = array(
			"allow_html" => 0,
			"allow_mycode" => 1,
			"allow_smilies" => 1,
			"allow_imgcode" => 1,
			"allow_videocode" => 1,
			"filter_badwords" => 0
		);
		$webmail_mtext = $parser->parse_message($webmail_mtext, $parser_options);
	}

	eval("\$webmail_menu .= \"".$templates->get("webmail_menu")."\";");
	eval("\$webmail_mailread .= \"".$templates->get("webmail_mailread")."\";");
	output_page($webmail_mailread);
}
?>
