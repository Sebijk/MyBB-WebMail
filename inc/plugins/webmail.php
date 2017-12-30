<?php
/*==============================================================*\
||   MyBB-Webmail						                        						||
||   © 2007 - 2017 Home of the Sebijk.com	  										||
||   Website: http://www.sebijk.com  							              ||
||   Lizenz: GPL 3.0														        				||
\*==============================================================*/


if( !defined('IN_MYBB') )
{
	die("Hacking attempt!");
}

function webmail_info()
{
	return array(
		'name'			=> 'MyBB-Webmail',
		'description'	=> 'Dieses Plugin fügt ein Webbasiertes E-Mail-Programm in MyBB ein',
		'website'		=> 'http://www.sebijk.com',
		'author'		=> 'Sebijk',
		'authorsite'	=> 'http://www.sebijk.com',
		'version'		=> '0.52 Beta',
		"guid" 			=> "",
		"compatibility" => "16*"
	);
}


function webmail_activate()
{
require(MYBB_ROOT. 'inc/adminfunctions_templates.php');
global $db, $mybb;


$webmail_group = array(
		"gid"			=> "NULL",
		"name"			=> "Webmail-Einstellungen",
		"title"			=> "Webmail-Einstellungen",
		"description"	=> "Einstellungen für das MyBB-Webmail.",
		"disporder"		=> "3",
		"isdefault"		=> "no",
	);

	$db->insert_query("settinggroups", $webmail_group);
	$gid = $db->insert_id();


	$webmail_setting_1 = array(
		"sid"			=> "NULL",
		"name"			=> "webmail_servers",
		"title"			=> "Webmail Server",
		"description"	=> "Geben Sie hier den Webmail Server ein.",
		"optionscode"	=> "text",
		"value"			=> 'localhost',
		"disporder"		=> '1',
		"gid"			=> intval($gid),
	);


	$webmail_setting_2 = array(
		"sid"			=> "NULL",
		"name"			=> "webmail_port",
		"title"			=> "Webmail Server Typ",
		"description"	=> "Geben Sie hier den Webmail Server-Typ ein. Möglich sind pop3 oder imap.",
		"optionscode"	=> "text",
		"value"			=> 'pop3',
		"disporder"		=> '2',
		"gid"			=> intval($gid),
	);

	$webmail_listmails_template = array(
		"title"		=> 'webmail_listmails',
		"template"	=> '<tr>
<td align="center" class="trow1" width="10%"><span class="smalltext">{$webmail_header->Recent} {$webmail_header->Flagged} {$webmail_header->Answered} {$webmail_header->Deleted}</span></td>
<td class="trow2" width="35%"><a href="webmail.php?do=read&num=$i">{$webmail_header->subject}</td>
<td align="center" class="trow1">{$webmail_box}@{$webmail_host}</td>
<td class="trow2" align="center" style="white-space: nowrap"><span class="smalltext">{$webmail_date}</span></td>
<td class="trow1" align="center" style="white-space: nowrap"><span class="smalltext">{$webmail_header->Size}</span></td>
</tr>',
		"sid"		=> -1,
		"version"	=> 053,
		"status"	=> '',
		"dateline"	=> 1305468972,
	);

	$webmail_menu_template = array(
		"title"		=> 'webmail_menu',
		"template"	=> "<table width=\"100%\" border=\"0\" align=\"center\">
<tr>
<td valign=\"top\">
<table border=\"0\" cellspacing=\"{\$theme[''borderwidth'']}\" cellpadding=\"{\$theme[''tablespace'']}\" class=\"tborder\">
<tr>
<td class=\"trow1\">
<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">
<tr>
<td class=\"trow1\"><span class=\"smalltext\"><a href=\"webmail.php\">Posteingang</a> | <a href=\"webmail.php?do=new\">Neue Nachricht</a> | <a href=\"webmail.php?do=logout\">Abmelden</a></span></td>
</tr>
</table>
</td>
</tr>
</table>
<br />",
		"sid"		=> -1,
		"version"	=> 053,
		"status"	=> '',
		"dateline"	=> 1305468972,
	);

	$webmail_mailread_template = array(
		"title"		=> 'webmail_mailread',
		"template"	=> '<html>
<head>
<title>MyBB-Webmail: {$webmail_header->subject}</title>
{$headerinclude}
</head>
<body>
{$header}
{$webmail_menu}
'."<table border=\"0\" cellspacing=\"{\$theme[''borderwidth'']}\" cellpadding=\"{\$theme[''tablespace'']}\" class=\"tborder\">".'
<tr>
<td class="thead" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td class="thead"><strong>{$webmail_header->subject}</strong></td><td class="thead" align="right"><a href="webmail.php?do=reply&amp;num={$num}">{$lang->reply}</a> | <a href="webmail.php?do=forward&amp;num={$num}">{$lang->forward}</a> | <a href="webmail.php?do=delete&amp;num={$num}">{$lang->delete_pm}</a></td></table></td>
</tr>

<tr>
<td class="trow1" width="100%" valign="top">
<table width="100%">
<tr><td><span class="smalltext"><strong>{$webmail_header->subject}</strong></span>
<br />
<div id="pid_">
<p>
{$webmail_mtext}
</p>
</div>
</td></tr>
</table>
</td></tr>

<tr>
<td class="tfoot" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td class="tfoot"><strong>{$webmail_header->subject}</strong></td><td class="tfoot" align="right"><a href="webmail.php?do=reply&amp;num={$num}">{$lang->reply}</a> | <a href="webmail.php?do=forward&amp;num={$num}">{$lang->forward}</a> | <a href="webmail.php?do=delete&amp;num={$num}">{$lang->delete_pm}</a></td></table></td>
</tr>
</table>
{$webmail_footer}
{$footer}
</body>
</html>',
		"sid"		=> -1,
		"version"	=> 053,
		"status"	=> '',
		"dateline"	=> 1305468972,
	);

	$webmail_newmessage_template = array(
		"title"		=> 'webmail_newmessage',
		"template"	=> '<html>
<head>
<title>Neue Nachricht</title>
{$headerinclude}
<script type="text/javascript" src="jscripts/post.js?ver=16"></script>
</head>
<body>
{$header}
{$webmail_menu}
<form action="webmail.php?do=send" method="post" name="input">
'."<table border=\"0\" cellspacing=\"{\$theme[''borderwidth'']}\" cellpadding=\"{\$theme[''tablespace'']}\" class=\"tborder\">".'
<tr>
<td class="thead" colspan="2"><strong>Neue Nachricht erfassen:</strong></td>
</tr>
{$loginbox}
<tr>
<td class="trow2" width="20%"><strong>{$lang->compose_to}</strong></td>
<td class="trow2"><input type="text" class="textbox" name="to"" size="40" maxlength="85" value="{$compose_to}" tabindex="1" /></td>
</tr>
<tr>
<td class="trow2" width="20%"><strong>Bcc:</strong></td>
<td class="trow2"><input type="text" class="textbox" name="bcc"" size="40" maxlength="85" value="{$bcc}" tabindex="2" /></td>
</tr>
<td class="trow2" width="20%"><strong>{$lang->compose_subject}</strong></td>
<td class="trow2"><input type="text" class="textbox" name="subject" size="40" maxlength="85" value="{$subject}" tabindex="3" /></td>
</tr>
<tr>
<td class="trow2" valign="top"><strong>{$lang->compose_message}</strong><br /><div style="margin:auto">{$smilieinserter}</div></td>
<td class="trow2">
<textarea name="text" id="message" rows="20" cols="70" tabindex="4">{$message}</textarea>
{$codebuttons}
</td>
</tr>
<tr>
<td class="trow2" width="20%"><strong>E-Mail senden als</strong></td>
<td class="trow2"><input type="radio" name="mailtype" value="text" checked="checked" /> Text&nbsp; <input type="radio" name="mailtype" value="html" /> HTML</td>
</tr>
</table>
<br />
<div style="text-align:center"><input type="submit" class="button" name="submit" value="Senden" tabindex="4" accesskey="r" /> <input type="reset" class="button" name="reset" value="Zur&uuml;cksetzen" tabindex="6" /></div>
</form>
{$webmail_footer}
{$footer}
</body>
</html>',
		"sid"		=> -1,
		"version"	=> 053,
		"status"	=> '',
		"dateline"	=> 1305468972,
	);

	$webmail_start_template = array(
		"title"		=> 'webmail_start',
		"template"	=> '<html>
<head>
'."<title>Mein Postfach - {\$mybb->settings[''bbname'']}</title>".'
{$headerinclude}
</head>
<body>
{$header}
{$webmail_menu}
'."<table border=\"0\" cellspacing=\"{\$theme[''borderwidth'']}\" cellpadding=\"{\$theme[''tablespace'']}\" class=\"tborder\">".'
<tr>
<td class="thead" align="center" colspan="6"><strong>Mein Posteingang</strong></td>
</tr>
<tr>
<td class="tcat" align="center" width="10%" style="white-space: nowrap"><span class="smalltext"><strong>Status</strong></span></td>
<td class="tcat" align="center" width="35%"><span class="smalltext"><strong>Betreff</strong></span></td>
<td class="tcat" align="center" width="30%" style="white-space: nowrap"><span class="smalltext"><strong>Von</strong></span></td>
<td class="tcat" align="center"  width="20%" style="white-space: nowrap"><span class="smalltext"><strong>Erhalten</strong></span></td>
<td class="tcat" align="center" width="5%" style="white-space: nowrap"><span class="smalltext"><strong>Gr&ouml;&szlig;e</strong</span></td>
</tr>
{$webmail_listmails}
</table>
<br />
</td>
</tr>
</table>
</td>
</tr>
</table>
{$webmail_footer}
{$footer}
</body>
</html>',
		"sid"		=> -1,
		"version"	=> 053,
		"status"	=> '',
		"dateline"	=> 1305468972,
	);

	$db->insert_query('templates', $webmail_listmails_template);
	$db->insert_query('templates', $webmail_menu_template);
  $db->insert_query('templates', $webmail_mailread_template);
  $db->insert_query('templates', $webmail_newmessage_template);
  $db->insert_query('templates', $webmail_start_template);

	$db->insert_query("settings", $webmail_setting_1);
	$db->insert_query("settings", $webmail_setting_2);
	rebuild_settings();

}

function webmail_deactivate()
{
	global $db;
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='webmail_servers'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='webmail_port'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='Webmail-Einstellungen'");

	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title ='webmail_listmails'");
	//$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title ='webmail_login'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title ='webmail_mailread'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title ='webmail_menu'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title ='webmail_newmessage'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title ='webmail_start'");
	rebuild_settings();


}

if(!function_exists("rebuild_settings"))
{
	function rebuild_settings()
	{
		global $db;
		$query = $db->query("SELECT * FROM ".TABLE_PREFIX."settings ORDER BY title ASC");
		while($setting = $db->fetch_array($query))
		{
			$setting['value'] = addslashes($setting['value']);
			$settings .= "\$settings['".$setting['name']."'] = \"".$setting['value']."\";\n";
		}
		$settings = "<?php\n/*********************************\ \n  DO NOT EDIT THIS FILE, PLEASE USE\n  THE SETTINGS EDITOR\n\*********************************/\n\n$settings\n?>";
		$file = fopen(MYBB_ROOT. "inc/settings.php", "w");
		fwrite($file, $settings);
		fclose($file);
	}
}
?>
