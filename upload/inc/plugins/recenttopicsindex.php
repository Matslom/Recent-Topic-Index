<?php
/*
Recent Topics
by: vbgamer45
http://www.mybbhacks.com
Copyright 2011  MyBBHacks.com

############################################
License Information:

Links to http://www.mybbhacks.com must remain unless
branding free option is purchased.
#############################################
*/
if(!defined("IN_MYBB"))
{
	die("This file cannot be accessed directly.");
}

$plugins->add_hook("index_end", "recenttopicsindex_show");

if(my_strpos($_SERVER['PHP_SELF'], 'index.php'))
{
    global $templatelist;
    if(isset($templatelist))
    {
        $templatelist .= ',';
    }
	$templatelist .= 'recenttopics,recenttopics_row';
}

function recenttopicsindex_info()
{
	global $db, $mybb, $lang;
	$lang->load("recenttopics");
	
	return array(
		"name"		=> $db->escape_string($lang->name),
		"description"		=> $db->escape_string($lang->desc),
		"website"		=> "http://www.mybbhacks.com",
		"author"		=> "vbgamer45 (Edited by Matslom & GiboneKPL from mybboard.pl)",
		"authorsite"		=> "http://www.mybbhacks.com",
		"version"		=> "1.1.5",
		"guid" 			=> "*",
		"compatibility"	=> "16*,18*"
		);
}


function recenttopicsindex_install()
{
	global $mybb, $db, $lang;
	$lang->load("recenttopics");

	$settinggroups = array(
		"name" 			=> "recenttopics", 
		"title" 		=> $db->escape_string($lang->name),
		"description" 	=> $db->escape_string($lang->settgroup_desc),
		"disporder" 	=> 100, 
		"isdefault" 	=> 0
	);
	$gid = $db->insert_query("settinggroups", $settinggroups);
	$disporder = 0;

	$setting = array(
		"sid"			=> NULL,
		"name"			=> "recenttopics_limit",
		"title"			=> $db->escape_string($lang->sett_1),
		"description"	=> $db->escape_string($lang->sett_1_desc),
		"optionscode"	=> "text", 
		"value"			=> "5",
		"disporder"		=> $disporder++,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "recenttopics_forums",
		"title"			=> $db->escape_string($lang->sett_2),
		"description"	=> $db->escape_string($lang->sett_2_desc),
		"optionscode"	=> "text", 
		"value"			=> null,
		"disporder"		=> $disporder++,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);

	$setting = array(
		"sid"			=> NULL,
		"name"			=> "recenttopics_awatar",
		"title"			=> $db->escape_string($lang->sett_3),
		"description"	=> $db->escape_string($lang->sett_3_desc),
		"optionscode"	=> "yesno", 
		"value"			=> "1",
		"disporder"		=> $disporder++,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "recenttopics_default_a",
		"title"			=> $db->escape_string($lang->sett_4),
		"description"	=> $db->escape_string($lang->sett_4_desc),
		"optionscode"	=> "text", 
		"value"			=> "./images/default_avatar.png", 
		"disporder"		=> $disporder++,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "recenttopics_skr",
		"title"			=> $db->escape_string($lang->sett_5),
		"description"	=> $db->escape_string($lang->sett_5_desc),
		"optionscode"	=> "yesno",
		"value"			=> "1",
		"disporder"		=> $disporder++,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "recenttopics_skr2",
		"title"			=> $db->escape_string($lang->sett_6),
		"description"	=> $db->escape_string($lang->sett_6_desc),
		"optionscode"	=> "text",
		"value"			=> "35",
		"disporder"		=> $disporder++,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "recenttopics_prefix",
		"title"			=> $db->escape_string($lang->sett_7),
		"description"	=> $db->escape_string($lang->sett_7_desc),
		"optionscode"	=> "yesno",
		"value"			=> "1",
		"disporder"		=> $disporder++,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "recenttopics_forums2",
		"title"			=> $db->escape_string($lang->sett_8),
		"description"	=> $db->escape_string($lang->sett_8_desc),
		"optionscode"	=> "yesno",
		"value"			=> "1",
		"disporder"		=> $disporder++,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	rebuild_settings();

	$template = array(
		"tid" 			=> "NULL",
		"title" 		=> "recenttopics",
		"template"		=> $db->escape_string('<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="margin-top:5px;">
<thead>
<tr>
<td class="thead" colspan="2">
<div><strong>{$lang->index_name}</strong></div>
</td>
</tr>
</thead>
{$recenttopics_row}
</table>
<br />'),
		"sid" 			=> "-1", 
		);
	$db->insert_query("templates", $template);
		
	$template = array(
		"tid" 			=> "NULL",
		"title" 		=> "recenttopics_row",
		"template"		=> $db->escape_string('<tr> 
<td class="trow1 smalltext">
{$avatar}
{$prefix} <a href="{$subject_link}">{$subject}</a> {$in_forums} <a href="{$forum_link}">{$forum_name}</a><br />
{$username} {$post_date}
</td>
</tr>'),
		"sid" 			=> "-1", 
		);
	$db->insert_query("templates", $template);

}

function recenttopicsindex_is_installed()
{
	global $mybb, $db, $lang;
	$lang->load("recenttopics");
	
	$q = $db->simple_select('settinggroups', '*', 'name=\'recenttopics\'');
	$group = $db->fetch_array($q);
	if($group === null || empty($group))
	return false;
	return true;
}

function recenttopicsindex_uninstall()
{
	global $mybb, $db, $lang;
	$lang->load("recenttopics");
	
	$db->delete_query('settings', 'name LIKE \'%recenttopics_%\'');
	$db->delete_query("settinggroups", "name = 'recenttopics'");
	$db->delete_query('templates', 'title LIKE (\'%recenttopics%\')');
}


function recenttopicsindex_activate()
{

}

function recenttopicsindex_deactivate()
{

}

function recenttopicsindex_show()
{
	global $db, $mybb, $page, $recenttopics, $theme, $permissioncache, $templates, $theme, $lang;
	$lang->load("recenttopics");
	require_once MYBB_ROOT."inc/functions_search.php";

	// Run the Query
	
    // !!! FIX private forum exposure!!!
	if( !is_array($permissioncache) ||(is_array($permissioncache) && ((count($permissioncache)==1) && (isset($permissioncache['-1']) && ($permissioncache['-1'] = "1"))))) 
	{
		$permissioncache = forum_permissions();
	}

	$unsearchforums = get_unsearchable_forums();
	if($unsearchforums)
	{
		$where_sql .= " AND t.fid NOT IN ($unsearchforums)";
	}
	
	$inactiveforums = get_inactive_forums();
	if ($inactiveforums)
	{
		$where_sql .= " AND t.fid NOT IN ($inactiveforums)";
	}
	
//Jeżeli ma wyciągać tematy z każdego działu oraz prefiks jest włączony
if($mybb->settings['recenttopics_forums'] == null && $mybb->settings['recenttopics_prefix'] == '1')
{
	$query = $db->query("SELECT t.tid, t.fid, t.subject, t.lastposteruid, t.prefix, p.displaystyle, p.pid, t.lastposter, t.lastpost, f.name, u.username, u.usergroup, u.displaygroup, u.avatar, u.uid
						 FROM ".TABLE_PREFIX."threads t  
						 LEFT JOIN ".TABLE_PREFIX."forums f ON f.fid=t.fid
						 LEFT JOIN ".TABLE_PREFIX."users u ON u.username=t.lastposter
						 LEFT JOIN ".TABLE_PREFIX."threadprefixes p ON (p.pid=t.prefix)
						 WHERE t.visible = 1 $where_sql
						 ORDER BY t.lastpost 
						 DESC LIMIT " . $mybb->settings["recenttopics_limit"]);
}
//Jeżeli ma wyciągać tematy z wybranych działu oraz prefiks jest włączony
elseif($mybb->settings['recenttopics_forums'] != null && $mybb->settings['recenttopics_prefix'] == '1')
{
	$query = $db->query("SELECT t.tid, t.fid, t.subject, t.prefix, t.lastposteruid, t.lastposter, p.displaystyle, p.pid, t.lastpost, f.name, u.username, u.usergroup, u.displaygroup, u.avatar, u.uid
						 FROM ".TABLE_PREFIX."threads t  
						 LEFT JOIN ".TABLE_PREFIX."forums f ON f.fid=t.fid
						 LEFT JOIN ".TABLE_PREFIX."users u ON u.username=t.lastposter
						 LEFT JOIN ".TABLE_PREFIX."threadprefixes p ON (p.pid=t.prefix)
						 WHERE t.fid IN(".$mybb->settings["recenttopics_forums"].") 
						 AND t.visible = 1 $where_sql
						 ORDER BY t.lastpost
						 DESC LIMIT " . $mybb->settings["recenttopics_limit"]);
}
//Jeżeli ma wyciągać z każdego działu oraz prefiks jest wyłączony
elseif($mybb->settings['recenttopics_forums'] == null && $mybb->settings['recenttopics_prefix'] == '0')
{
	$query = $db->query("SELECT t.tid, t.fid, t.subject, t.lastposteruid, t.lastposter, t.lastpost, f.name, u.username, u.usergroup, u.displaygroup, u.avatar, u.uid
						 FROM ".TABLE_PREFIX."threads t  
						 LEFT JOIN ".TABLE_PREFIX."forums f ON f.fid=t.fid
						 LEFT JOIN ".TABLE_PREFIX."users u ON u.username=t.lastposter
						 WHERE t.visible = 1 $where_sql
						 ORDER BY t.lastpost 
						 DESC LIMIT " . $mybb->settings["recenttopics_limit"]);
}
//Jeżeli ma wyciągać tematy z wybranegoo działu oraz prefiks jest wyłączony
elseif($mybb->settings['recenttopics_forums'] != null && $mybb->settings['recenttopics_prefix'] == '0')
{
	$query = $db->query("SELECT t.tid, t.fid, t.subject, t.lastposteruid, t.lastposter, t.lastpost, f.name, u.username, u.usergroup, u.displaygroup, u.avatar, u.uid
						 FROM ".TABLE_PREFIX."threads t  
						 LEFT JOIN ".TABLE_PREFIX."forums f ON f.fid=t.fid
						 LEFT JOIN ".TABLE_PREFIX."users u ON u.username=t.lastposter
						 WHERE t.fid IN(".$mybb->settings["recenttopics_forums"].") 
						 AND t.visible = 1 $where_sql
						 ORDER BY t.lastpost
						 DESC LIMIT " . $mybb->settings["recenttopics_limit"]);
}
	while($threadRow = $db->fetch_array($query))
	{
	
		if($threadRow['avatar'] == null) 
		{
			$threadRow['avatar'] = $mybb->settings['recenttopics_default_a']; 
		}
		if($threadRow['prefix'] != '')
		{
			$prefix = $threadRow['displaystyle'];
		}
		else
		{
			$prefix = '';
		}
		$username_link = get_profile_link($threadRow['uid']);
		if($mybb->settings['recenttopics_awatar'] == '1')
		{	
		$avatar = "<a href=\"".$username_link."\"><img src=\"".$threadRow['avatar']."\" alt=\"avatar\" style=\"float: left;margin-right: 5px\" class=\"favimg\" width=\"35px\" height=\"35px\"/></a>"; 
		}
		else
		{
			$avatar = '';	
		}
		if($mybb->settings['recenttopics_forums2'] == '1')
		{
			$in_forums = $db->escape_string($lang->forums);
			$forum_name = $threadRow['name'];
			$forum_link = get_forum_link($threadRow['fid']);
		}
		$username = format_name($threadRow['username'], $threadRow['usergroup'], $threadRow['displaygroup']);
        $username = build_profile_link($username, $threadRow['uid']);
	    if(strlen($threadRow['subject']) > $mybb->settings['recenttopics_skr2'])
        {
			$subject = substr($threadRow['subject'], 0, $mybb->settings['recenttopics_skr2'])."...";
		}
		else
		{
			$subject = $threadRow['subject'];
		}
		$subject = htmlspecialchars_uni($subject);
		$subject_link = get_thread_link($threadRow['tid']).'&action=lastpost';
		$postdate = my_date($mybb->settings['dateformat'], $threadRow['lastpost']);
		$posttime = my_date($mybb->settings['timeformat'], $threadRow['lastpost']);
		$post_date = $postdate."-".$posttime;

      
		if($mybb->settings['recenttopics_awatar'] == '1' ) { $recenttopics .= $awatar; }
		
		eval('$recenttopics_row .= "'.$templates->get('recenttopics_row').'";');
	}
    eval('$recenttopics = "'.$templates->get('recenttopics').'";');

}


?>
