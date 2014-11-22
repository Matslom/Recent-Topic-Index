<?php
/*
 * Recent Topics Index Page PLUS by Matslom [matslom.pl] & GiboneKPL
 * License: GNU GENERAL PUBLIC LICENSE -> http://www.gnu.org/licenses/gpl-3.0.txt
 * Github: https://github.com/Matslom/Recent-Topic-Index
 */

if(!defined("IN_MYBB"))
{
	die("This file cannot be accessed directly.");
}

$plugins->add_hook("index_end", "recenttopicsindex_show");

function recenttopicsindex_info()
{
	global $db, $mybb, $lang;
	$lang->load("recenttopics");
	
	return array(
		"name"			=> $db->escape_string($lang->name),
		"description"	=> $db->escape_string($lang->desc),
		"website"		=> "http://www.mybboard.pl",
		"author"		=> "Matslom & GiboneKPL",
		"authorsite"	=> "matslom.pl",
		"version"		=> "1.1.6",
		"guid" 			=> "*",
		"compatibility"	=> "18*"
		);
}


function recenttopicsindex_install()
{
	global $mybb, $db, $lang;
	$lang->load("recenttopics");

	$settingGroupId = $db->insert_query('settinggroups', [
        'name'        => 'recenttopics',
        'title'       => $db->escape_string($lang->name),
        'description' => $db->escape_string($lang->settgroup_desc),
    ]);
    
	$gid = $db->insert_query("settinggroups", $settinggroups);
	$disporder = 0;

	$settings = [
		[
			'name'        => 'recenttopics_limit',
            'title'       => $lang->sett_1,
            'description' => $lang->sett_1_desc,
            'optionscode' => 'text',
            'value'       => '5',
		],
		[
			'name'        => 'recenttopics_forums',
            'title'       => $lang->sett_2,
            'description' => $lang->sett_2_desc,
            'optionscode' => 'text',
            'value'       => '',
		],
		[
			'name'        => 'recenttopics_awatar',
            'title'       => $lang->sett_3,
            'description' => $lang->sett_3_desc,
            'optionscode' => 'yesno',
            'value'       => '1',
		],
		[
			'name'        => 'recenttopics_default_a',
            'title'       => $lang->sett_4,
            'description' => $lang->sett_4_desc,
            'optionscode' => 'text',
            'value'       => './images/default_avatar.png',
		],
		[
			'name'        => 'recenttopics_skr',
            'title'       => $lang->sett_5,
            'description' => $lang->sett_5_desc,
            'optionscode' => 'yesno',
            'value'       => '1',
		],
		[
			'name'        => 'recenttopics_skr2',
            'title'       => $lang->sett_6,
            'description' => $lang->sett_6_desc,
            'optionscode' => 'text',
            'value'       => '35',
		],
		[
			'name'        => 'recenttopics_prefix',
            'title'       => $lang->sett_7,
            'description' => $lang->sett_7_desc,
            'optionscode' => 'yesno',
            'value'       => '1',
		],
		[
			'name'        => 'recenttopics_forums2',
            'title'       => $lang->sett_8,
            'description' => $lang->sett_8_desc,
            'optionscode' => 'yesno',
            'value'       => '1',
		],
	];
		
	$i = 1;
	foreach ($settings as &$row) {
        $row['gid']         = $settingGroupId;
        $row['title']       = $db->escape_string($row['title']);
        $row['description'] = $db->escape_string($row['description']);
        $row['disporder']   = $i++;
    }

    $db->insert_query_multiple('settings', $settings);
	rebuild_settings();

	$template = array(
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
		"sid" 			=> -1, 
		);
	$db->insert_query("templates", $template);
		
	$template = array(
		"title" 		=> "recenttopics_row",
		"template"		=> $db->escape_string('<tr> 
<td class="trow1 smalltext">
{$topicIndex[\'avatar\']}
{$topicIndex[\'prefix\']} <a href="{$topicIndex[\'subject_link\']}">{$topicIndex[\'subject\']}</a> {$topicIndex[\'in_forums\']} <a href="{$topicIndex[\'forum_link\']}">{$topicIndex[\'forum_name\']}</a><br />
{$topicIndex[\'username\']} {$topicIndex[\'post_date\']}
</td>
</tr>'),
		"sid" 			=> -1, 
		);
	$db->insert_query("templates", $template);

}

function recenttopicsindex_is_installed()
{
	global $mybb, $db;
	
	$q = $db->simple_select('settinggroups', '*', 'name=\'recenttopics\'');
	$group = $db->fetch_array($q);
	if($group === null || empty($group))
	return false;
	return true;
}

function recenttopicsindex_uninstall()
{
	global $mybb, $db;
	
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
			$topicIndex['prefix'] = $threadRow['displaystyle'];
		}
		else
		{
			$topicIndex['prefix'] = '';
		}
		$username_link = get_profile_link($threadRow['uid']);
		if($mybb->settings['recenttopics_awatar'] == '1')
		{	
		$topicIndex['avatar'] = "<a href=\"".$username_link."\"><img src=\"".$threadRow['avatar']."\" alt=\"avatar\" style=\"float: left;margin-right: 5px\" class=\"favimg\" width=\"35px\" height=\"35px\"/></a>"; 
		}
		else
		{
			$topicIndex['avatar'] = '';	
		}
		if($mybb->settings['recenttopics_forums2'] == '1')
		{
			$topicIndex['in_forums'] = $db->escape_string($lang->forums);
			$topicIndex['forum_name'] = $threadRow['name'];
			$topicIndex['forum_link'] = get_forum_link($threadRow['fid']);
		}
		$username = format_name($threadRow['username'], $threadRow['usergroup'], $threadRow['displaygroup']);
        $topicIndex['username'] = build_profile_link($username, $threadRow['uid']);
	    if(strlen($threadRow['subject']) > $mybb->settings['recenttopics_skr2'])
        {
			$subject = substr($threadRow['subject'], 0, $mybb->settings['recenttopics_skr2'])."...";
		}
		else
		{
			$subject = $threadRow['subject'];
		}
		$topicIndex['subject'] = htmlspecialchars_uni($subject);
		$topicIndex['subject_link'] = get_thread_link($threadRow['tid']).'&action=lastpost';
		$postdate = my_date($mybb->settings['dateformat'], $threadRow['lastpost']);
		$posttime = my_date($mybb->settings['timeformat'], $threadRow['lastpost']);
		$topicIndex['post_date'] = $postdate."-".$posttime;
		
		eval('$recenttopics_row .= "'.$templates->get('recenttopics_row').'";');
	}
    eval('$recenttopics = "'.$templates->get('recenttopics').'";');

}


?>
