<?php // $Id$

/*
* dotProject Installer
* @package dotProject
* @Copyright (c) 2004, The dotProject Development Team sf.net/projects/dotproject
* @ All rights reserved
* @ dotProject is Free Software, released under BSD License
* @subpackage Installer
* @ This Installer is released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @ Major Parts are based on Code from Mambo Open Source www.mamboserver.com
* @version $Revision$
*/


require_once("commonlib.php");

// propose some values
$propDbPort = ( ini_get('mysql.default_port') == null) ? $defDbPort : ini_get('mysql.default_port');


$dbmsg          = trim( dPgetParam( $_POST, 'dbmsg', '' ) );
$dbhost         = trim( dPgetParam( $_POST, 'dbhost', $defDbHost ) );
$dbname         = trim( dPgetParam( $_POST, 'dbname', $defDbName ) );
$dbuser         = trim( dPgetParam( $_POST, 'dbuser', '' ) );
$dbpass         = trim( dPgetParam( $_POST, 'dbpass', '' ) );
$dbport         = trim( dPgetParam( $_POST, 'dbport', $propDbPort ) );
$dbpersist      = trim( dPgetParam( $_POST, 'dbpersist', false ) );
$dbdrop         = trim( dPgetParam( $_POST, 'dbdrop', false ) );
$dbbackup       = trim( dPgetParam( $_POST, 'dbbackup', true ) );
$initial_company= trim( dPgetParam( $_POST, 'initial_company', '' ) );
$db_install_mode= trim( dPgetParam( $_POST, 'db_install_mode', 'install' ) );


?>
<html>
<head>
	<title>dotProject Installer</title>
	<meta name="Author" content="Gregor Erhardt: gregor at dotproject dot orangrey dot org">
	<meta name="Description" content="Automated Installer Routine for dotProject">
	<link rel="stylesheet" type="text/css" href="./install.css">
</head>
<body>
<span class="error"><?php echo $dbmsg; ?></span>
<h1><img src="dp.png" align="middle" alt="dotProject Logo"/>&nbsp;Installer for dotProject <?php echo dPgetVersion();?>: Step 2</h1>
<form action="do_backup.php" method="post" name="form" id="form">
        <table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center">
        <tr>
            <td class="title" colspan="2">Database Settings</td>
        </tr>
         <tr>
            <td class="item">Database Host Name</td>
            <td align="left"><input class="button" type="text" name="dbhost" value="<?php echo $dbhost; ?>" title="The Name of the Host the Database Server is installed on" /></td>
          </tr>
           <tr>
            <td class="item">Database Name</td>
            <td align="left"><input class="button" type="text" name="dbname" value="<?php echo $dbname; ?>" title="The Name of the Database dotProject will use and/or install" /></td>
          </tr>
          <tr>
            <td class="item">Database User Name</td>
            <td align="left"><input class="button" type="text" name="dbuser" value="<?php echo "$dbuser"; ?>" title="The Database User that dotProject uses for Database Connection" /></td>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td class="item">Database User Password</td>
            <td align="left"><input class="button" type="text" name="dbpass" value="<?php echo "$dbpass"; ?>" title="The Password according to the above User." /></td>
          </tr>

          <tr>
            <td class="item">Database Port Name</td>
            <td align="left"><input class="button" type="text" name="dbport" value="<?php echo $dbport; ?>" title="The Port the Database Server is listening to. If empty a standard value of 3306 is used." /></td>
          </tr>
           <tr>
            <td class="item">Use Persistent Connection?</td>
            <td align="left"><input type="checkbox" name="dbpersist" value="true" <?php echo ($dbpersist==true) ? 'checked="checked"' : ''; ?> title="Use a persistent Connection to your Database Server." /></td>
          </tr>
          <tr>
            <td class="item">Drop Existing Database?</td>
            <td align="left"><input type="checkbox" name="dbdrop" value="true" <?php echo ($dbdrop==true) ? 'checked="checked"' : ''; ?> title="Deletes an existing Database before installing a new one. This deletes all data in the given database. Data cannot be restored." /></td>
            <td class="item">If checked, existing Data will be lost!</td>
        </tr>
        </tr>
          <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
          <tr>
            <td class="title" colspan="2">Populate Database</td>
        </tr>
        <tr>
            <td class="item" colspan="2">Fill the Database with Structure and/or Content. While filling the database with Structure will be
            necessary for Installation from Scratch (Install - Add Structure), it is recommended/handy for Upgrades avoiding it and apply
            the database upgrade scripts distributed with dotProject automatically (Upgrade) or by hand (Manual Installation - Do Nothing).
            For now,  an automatic Upgrade is only possible from one release step to another (not more than one steps in a time!), otherwise
            it is very likely that you will experience errors running dotProject. In Case of once Upgrading more than one Release Versions,
            the only way to go is a manual Application of all necessary upgrade scripts.
            Furthermore dotProject needs an initial company created for running properly.
            Fill in an appropriate name or leave empty if you do want to create one</td>
        </tr>
         <tr>
            <td class="item">Database Installation Mode</td>
            <td align="left"><select class="button" size="1" name="db_install_mode" title="Title">
            <option value="install" <?php echo ($db_install_mode == 'install') ? 'selected="selected"' : '';?>>Install - Add Structure</option>
            <option value="upgrade" <?php echo ($db_install_mode == 'upgrade') ? 'selected="selected"' : '';?>>Upgrade</option>
            <option value="manual" <?php echo ($db_install_mode == 'manual') ? 'selected="selected"' : '';?>>Manual Installation - Do Nothing</option></select></td>
        </tr>
        <tr>
            <td class="item">Create Initial Company</td>
            <td align="left"><input class="button" type="text" name="initial_company" value="<?php echo $initial_company; ?>" title="Create an Initial Company. Leave empty if you do not want to create a company." /></td>
        </tr>
          <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
          <tr>
            <td class="title" colspan="2">Backup existing Database (Recommended)</td>
        </tr>
        <tr>
            <td class="item" colspan="2">Receive a Backup SQL File containing all Tables for the database entered above
            by clicking on the Button labeled 'Backup' down below.</td>
        </tr>
        <tr>
            <td class="item">Add 'Drop Tables'-Command in SQL-Script?</td>
            <td align="left"><input type="checkbox" name="backupdrop" value="false" <?php echo ($backupdrop==true) ? 'checked="checked"' : ''; ?> title="If this command is added, existing data will be deleted by running the backup script. This can be handy not needing to manually delete existing database tables." /></td>
        </tr>
        <tr>
            <td class="item">Receive SQL File</td>
            <td align="left"><input class="button" type="submit" name="dobackup" value="Backup" title="Click here to retrieve a database backup file that can be stored on your local system. " /></td>
        </tr>
          <tr>
            <td colspan="3" align="right"><br /> <input class="button" type="submit" name="next" value="Next" title="Save Settings and try to install the database with the given information." /></td>
          </tr>
        </table>
        <?php if ($dbmsg > "") {
                echo "<input type=\"hidden\" name=\"root_dir\" value=\"$root_dir\">
                <input type=\"hidden\" name=\"host_locale\" value=\"$host_locale\">
                <input type=\"hidden\" name=\"host_style\" value=\"$host_style\">
                <input type=\"hidden\" name=\"jpLocale\" value=\"$jpLocale\">
                <input type=\"hidden\" name=\"currency_symbol\" value=\"$currency_symbol\">
                <input type=\"hidden\" name=\"base_url\" value=\"$base_url\">
                <input type=\"hidden\" name=\"site_domain\" value=\"$site_domain\">
                <input type=\"hidden\" name=\"page_title\" value=\"$page_title\">
                <input type=\"hidden\" name=\"company_name\" value=\"$company_name\">
                <input type=\"hidden\" name=\"daily_working_hours\" value=\"$daily_working_hours\">
                <input type=\"hidden\" name=\"cal_day_start\" value=\"$cal_day_start\">
                <input type=\"hidden\" name=\"cal_day_end\" value=\"$cal_day_end\">
                <input type=\"hidden\" name=\"cal_day_increment\" value=\"$cal_day_increment\">
                <input type=\"hidden\" name=\"cal_working_days\" value=\"$cal_working_days\">
                <input type=\"hidden\" name=\"check_legacy_passwords\" value=\"$check_legacy_passwords\">
                <input type=\"hidden\" name=\"show_all_tasks\" value=\"$show_all_tasks\">
                <input type=\"hidden\" name=\"show_all_task_assignees\" value=\"$show_all_task_assignees\">
                <input type=\"hidden\" name=\"enable_gantt_charts\" value=\"$enable_gantt_charts\">
                <input type=\"hidden\" name=\"log_changes\" value=\"$log_changes\">
                <input type=\"hidden\" name=\"check_tasks_dates\" value=\"$check_tasks_dates\">
                <input type=\"hidden\" name=\"locale_warn\" value=\"$locale_warn\">
                <input type=\"hidden\" name=\"locale_alert\" value=\"$locale_alert\">
                <input type=\"hidden\" name=\"debug\" value=\"$debug\">
                <input type=\"hidden\" name=\"relink_tickets_kludge\" value=\"$relink_tickets_kludge\">
                <input type=\"hidden\" name=\"restrict_task_time_editing\" value=\"$restrict_task_time_editing\">
                <input type=\"hidden\" name=\"ft_default\" value=\"$ft_default\">
                <input type=\"hidden\" name=\"ft_application_msword\" value=\"$ft_application_msword\">
                <input type=\"hidden\" name=\"ft_text_html\" value=\"$ft_text_html\">
                <input type=\"hidden\" name=\"ft_application_pdf\" value=\"$ft_application_pdf\">
                <input type=\"hidden\" name=\"cfgmsg\" value=\"$cfgmsg\">
                ";
        }?>
</form>
</body>
</html>