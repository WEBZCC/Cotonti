<?php
/* 
 * This script is forced to patch user_datebirth in existing databases
 */

define('SED_CODE', TRUE);

chdir('..');

require_once('datas/config.php');
require_once($cfg['system_dir'].'/functions.php');
require_once($cfg['system_dir'].'/database.'.$cfg['sqldb'].'.php');
$sed_dbc = sed_sql_connect($cfg['mysqlhost'], $cfg['mysqluser'], $cfg['mysqlpassword'], $cfg['mysqldb']);
unset($cfg['mysqlhost'], $cfg['mysqluser'], $cfg['mysqlpassword']);

// Create temporary table and copy existing values
$sql = <<<SQL
CREATE TABLE sed_tmp (u_id int(11) unsigned NOT NULL, u_birthdate int(11) NOT NULL);
INSERT INTO sed_tmp (u_id, u_birthdate)
	SELECT user_id, user_birthdate FROM sed_users
SQL;

foreach (explode(';', $sql) as $q) sed_sql_query($q);

// Drop user_birthdate and restore it from temp
$sql = <<<SQL
ALTER TABLE sed_users DROP user_birthdate;
ALTER TABLE sed_users ADD user_birthdate DATE NOT NULL DEFAULT '1970-01-01'
SQL;
foreach (explode(';', $sql) as $q) sed_sql_query($q);

$res = sed_sql_query('SELECT u_id, u_birthdate FROM sed_tmp');
while ($row = sed_sql_fetchassoc($res))
{
	$bdate = sed_stamp2date($row['u_birthdate']);
	sed_sql_query("UPDATE sed_users SET user_birthdate = '$bdate' WHERE user_id = " . $row['u_id']);
}
sed_sql_freeresult($res);

sed_sql_query('DROP TABLE sed_tmp');

echo 'Conversion completed';
?>
