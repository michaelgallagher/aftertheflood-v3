<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$active_group = 'expressionengine';
$active_record = TRUE;

$db['expressionengine']['hostname'] = 'localhost';
$db['expressionengine']['username'] = 'root';
$db['expressionengine']['password'] = 'ch1ckpea';
$db['expressionengine']['database'] = 'aftertheflood';
$db['expressionengine']['dbdriver'] = 'mysql';
$db['expressionengine']['pconnect'] = FALSE;
$db['expressionengine']['dbprefix'] = 'exp_';
$db['expressionengine']['swap_pre'] = 'exp_';
$db['expressionengine']['db_debug'] = TRUE;
$db['expressionengine']['cache_on'] = FALSE;
$db['expressionengine']['autoinit'] = FALSE;
$db['expressionengine']['char_set'] = 'utf8';
$db['expressionengine']['dbcollat'] = 'utf8_general_ci';
$db['expressionengine']['cachedir'] = '/Users/frankharrison/Sites/aftertheflood.dev/admin/expressionengine/cache/db_cache/';

/* End of file database.php */
/* Location: ./system/expressionengine/config/database.php */

if ($_SERVER['HTTP_HOST'] == 'aftertheflood.co' || $_SERVER['HTTP_HOST'] == 'www.aftertheflood.co') {
	// settings for LIVE server
	$db['expressionengine']['hostname'] = "localhost";
	$db['expressionengine']['username'] = "atf3";
	$db['expressionengine']['password'] = "h4A78bm+";
	$db['expressionengine']['database'] = "atf3";
	$db['expressionengine']['cachedir'] = "/var/www/vhosts/aftertheflood.co/httpdocs/admin/expressionengine/cache/db_cache/";
} else if ($_SERVER['HTTP_HOST'] == 'dev.aftertheflood.co' || $_SERVER['HTTP_HOST'] == 'www.dev.aftertheflood.co') {
	// settings for LIVE server
	$db['expressionengine']['hostname'] = "localhost";
	$db['expressionengine']['username'] = "atf3";
	$db['expressionengine']['password'] = "h4A78bm+";
	$db['expressionengine']['database'] = "atf3";
	$db['expressionengine']['cachedir'] = "/var/www/vhosts/aftertheflood.co/subdomains/dev/httpdocs/admin/expressionengine/cache/db_cache/";
} else {
	// settings for LOCAL server
	$db['expressionengine']['hostname'] = "localhost";
	$db['expressionengine']['username'] = "root";
	$db['expressionengine']['password'] = "9a11a9h3r";
	$db['expressionengine']['database'] = "atf3";
	$db['expressionengine']['cachedir'] = "/Users/atf-noah/Sites/aftertheflood.dev/admin/expressionengine/cache/db_cache/";
}
