<?php

if(!defined('IN_MYBB'))
	die('This file cannot be accessed directly.');


// the following is a bit of a compatibility fix at the cost of some performance; enabled by default
// set to 0 to disable
define('PHPTPL_TEMPLATE_CACHE_CHECK', 1);

$plugins->add_hook('global_start', 'phptpl_run');
$plugins->add_hook('xmlhttp', 'phptpl_run');

/*
 *  Known issue: in PHP evaluation, "?>" may not match properly if used in strings
 */

function phptpl_info()
{
	return array(
		'name'			=> 'PHP and Template Conditionals',
		'description'	=> 'Allows you to use conditionals and PHP code in templates.',
		'website'		=> 'http://mybbhacks.zingaburga.com/',
		'author'		=> 'ZiNgA BuRgA',
		'authorsite'	=> 'http://zingaburga.com/',
		'version'		=> '2.0',
		'compatibility'	=> '1*',
		'guid'			=> ''
	);
}

function phptpl_run() {
	global $templates;
	if(!defined('IN_ADMINCP') && is_object($templates))
	{
		if(PHPTPL_TEMPLATE_CACHE_CHECK) {
			$code = '
				$r = parent::get($title, $eslashes, $htmlcomments);
				if(!isset($this->parsed_cache[$title]) || $this->parsed_cache[$title][0] != $r)
				{
					$this->parsed_cache[$title] = array($r, $r);
					phptpl_parsetpl($this->parsed_cache[$title][1]);
				}
				return $this->parsed_cache[$title][1];
			';
		} else {
			$code = '
				if(!isset($this->parsed_cache[$title]))
				{
					$this->parsed_cache[$title] = parent::get($title, $eslashes, $htmlcomments);
					phptpl_parsetpl($this->parsed_cache[$title]);
				}
				return $this->parsed_cache[$title];
			';
		}
		// gain control of $templates object
		eval('
			class phptpl_templates extends '.get_class($templates).'
			{
				function phptpl_templates(&$oldtpl)
				{
					foreach(get_object_vars($oldtpl) as $var => $val)
						$this->$var = $val;
					
					$this->parsed_cache = array();
				}
				function get($title, $eslashes=1, $htmlcomments=1)
				{
					// $htmlcomments unnecessary - we\'ll now simply ignore it
					if($eslashes) {'.$code.'}
					else
						return parent::get($title, $eslashes, $htmlcomments);
				}
			}
		');
		$templates = new phptpl_templates($templates);
	}
}

function phptpl_parsetpl(&$ourtpl)
{
	$GLOBALS['__phptpl_if'] = array();
	$ourtpl = preg_replace(array(
		'#\<((?:else)?if\s+(.*?)\s+then|else\s*/?|/if)\>#sie', // note that this relies on preg_replace working in a forward order
		'#\<func (htmlspecialchars|htmlspecialchars_uni|intval|file_get_contents|floatval|urlencode|rawurlencode|addslashes|stripslashes|trim|crc32|ltrim|rtrim|chop|md5|nl2br|sha1|strrev|strtoupper|strtolower|my_strtoupper|my_strtolower|alt_trow|get_friendly_size|filesize|strlen|my_strlen|my_wordwrap|random_str|unicode_chr|bin2hex|str_rot13|str_shuffle|strip_tags|ucfirst|ucwords|basename|dirname|unhtmlentities)\>#i',
		'#\</func\>#i',
		'#\<template\s+([a-z0-9_ \-+!(),.]+)(\s*/)?\>#ie',
		'#\<\?=(.*?)\?\>#se',
		'#\<setvar\s+([a-z0-9_\-+!(),.]+)\>(.*?)\</setvar\>#ie',
		'#\<\?(?:php|\s).+?(\?\>)#se', // '#\<\?.*?(\?\>|$)#se',
	), array(
		'phptpl_if(\'$1\', \'$2\')',
		'".$1("',
		'")."',
		'$GLOBALS[\'templates\']->get(\'$1\')',
		'\'".strval(\'.phptpl_unescape_string(\'$1\').\')."\'',
		'\'".(($GLOBALS["tplvars"]["$1"] = ($2))?"":"")."\'',
		'phptpl_evalphp(\'$0\', \'$1\')',
	), $ourtpl);
}

function phptpl_if($s, $e)
{
	if($s[0] == '/') {
		// end if tag
		$last = array_pop($GLOBALS['__phptpl_if']);
		$suf = str_repeat(')', (int)substr($last, 1));
		if($last[0] == 'i')
			$suf = ':""'.$suf;
		return '"'.$suf.')."';
	} else {
		$s = strtolower(substr($s, 0, strpos($s, ' ')));
		if($s == 'if') {
			$GLOBALS['__phptpl_if'][] = 'i0';
			return '".(('.phptpl_unescape_string($e).')?"';
		} elseif($s == 'elseif') {
			$last = array_pop($GLOBALS['__phptpl_if']);
			$last = 'i'.((int)substr($last, 1) + 1);
			$GLOBALS['__phptpl_if'][] = $last;
			return '":(('.phptpl_unescape_string($e).')?"';
		} else {
			$last = array_pop($GLOBALS['__phptpl_if']);
			$last[0] = 'e';
			$GLOBALS['__phptpl_if'][] = $last;
			return '":"';
		}
	}
}


// unescapes the slashes added by $templates->get(), plus addslashes() during preg_replace()
function phptpl_unescape_string($str)
{
	return strtr($str, array('\\\\"' => '"', '\\\\' => '\\'));
}

function phptpl_evalphp($str, $end)
{
	return '".eval(\'ob_start(); ?>'
		.strtr(phptpl_unescape_string($str), array('\'' => '\\\'', '\\' => '\\\\'))
		.($end?'':'?>').'<?php return ob_get_clean();\')."';
}

// compatibility functions with Template Conditionals plugin
function phptpl_eval_expr($__s)
{
	return eval('return ('.$__s.');');
}

function phptpl_eval_text($__s)
{
	// simulate $templates->get()
	$__s = strtr($__s, array('\\' => '\\\\', '"' => '\\"', "\0" => ''));
	phptpl_parsetpl($__s);
	return eval('return "'.$__s.'";');
}

?>