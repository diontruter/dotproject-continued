<?php /* SYSTEM $Id$ */

// only user_type of Administrator (1) can access this page
if ($denyEdit || $AppUI->user_type != 1) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

$module = isset( $_REQUEST['module'] ) ? $_REQUEST['module'] : 'admin';
$lang = isset( $_REQUEST['lang'] ) ? $_REQUEST['lang'] : 'en';

$AppUI->savePlace( "m=system&a=translate&module=$module&lang=$lang" );

// read the installed modules
$modules = arrayMerge( array( 'common', 'styles' ), $AppUI->readDirs( 'modules' ));

// read the installed languages
$locales = $AppUI->readDirs( 'locales' );

ob_start();
	@readfile( "{$AppUI->cfg['root_dir']}/locales/en/$modules[$module].inc" );
	eval( "\$english=array(".ob_get_contents()."\n'0');" );
ob_end_clean();

$trans = array();
foreach( $english as $k => $v ) {
	if ($v != "0") {
		$trans[ (is_int($k) ? $v : $k) ] = array(
			'english' => $v
		);
	}
}

//echo "<pre>";print_r($trans);echo "</pre>";die;

if ($lang != 'en') {
	ob_start();
		@readfile( "{$AppUI->cfg['root_dir']}/locales/$lang/$modules[$module].inc" );
		eval( "\$locale=array(".ob_get_contents()."\n'0');" );
	ob_end_clean();

	foreach( $locale as $k => $v ) {
		if ($v != "0") {
			$trans[$k]['lang'] = $v;
		}
	}
}
ksort($trans);

$crumbs = array();
$crumbs["?m=system"] = "System Admin";
?>

<img src="images/shim.gif" width="1" height="5" alt="" border="0" /><br />
<table width="98%" border="0" cellpadding="0" cellspacing="1">
<form action="?m=system&a=translate" method="post" name="modlang">
<tr>
	<td><img src="./images/icons/world.gif" alt="" border="0" /></td>
	<td nowrap valign="top"><h1><?php echo $AppUI->_( 'Translation Management' );?></h1></td>
	<td align="right" width="100%" nowrap><?php echo $AppUI->_( 'Module' );?>:</span></td>
	<td><?php
	echo arraySelect( $modules, 'module', 'size="1" class="text" onchange="document.modlang.submit();"', $module );
	?></td>
	<td align="right" width="100%" nowrap>&nbsp;<?php echo $AppUI->_( 'Language' );?>:</span></td>
	<td><?php
	$AppUI->setWarning( false );
	echo arraySelect( $locales, 'lang', 'size="1" class="text" onchange="document.modlang.submit();"', $lang, true );
	$AppUI->setWarning( false );
	?></td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'" />', 'ID_HELP_SYS_TRANS' );?></td>
</tr>
</form>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
</tr>
</table>

<table width="98%" border="0" cellpadding="1" cellspacing="1" class="tbl">
<tr>
	<th width="15%" nowrap><?php echo $AppUI->_( 'Abbreviation' );?></th>
	<th width="40%" nowrap>English <?php echo $AppUI->_( 'String' );?></th>
	<th width="40%" nowrap><?php echo $AppUI->_( $locales[$lang] ).' '.$AppUI->_( 'String' );?></th>
	<th width="5%" nowrap><?php echo $AppUI->_( 'delete' );?></th>
</tr>
<form action="?m=system&a=translate_save" method="post" name="editlang">
<input type="hidden" name="module" value="<?php echo $modules[$module];?>" />
<input type="hidden" name="lang" value="<?php echo $lang;?>" />
<?php
$index = 0;
if ($lang == 'en') {
	echo "<tr>\n";
	echo "<td><input type=\"text\" name=\"trans[$index][abbrev]\" value=\"\" size=\"20\" class=\"text\" /></td>\n";
	echo "<td><input type=\"text\" name=\"trans[$index][english]\" value=\"\" size=\"40\" class=\"text\" /></td>\n";
	echo "<td colspan=\"2\">New Entry</td>\n";
	echo "</tr>\n";
}

$index++;
foreach ($trans as $k => $langs){
?>
<tr>
	<td><?php
		if ($k != @$langs['english']) {
			$k = htmlspecialchars( $k, ENT_QUOTES );
			if ($lang == 'en') {
				echo "<input type=\"text\" name=\"trans[$index][abbrev]\" value=\"$k\" size=\"20\" class=\"text\" />";
			} else {
				echo $k;
			}
		} else {
			echo '&nbsp;';
		}
	?></td>
	<td><?php
		$langs['english'] = htmlspecialchars( @$langs['english'], ENT_QUOTES );
		if ($lang == 'en') {
			if (strlen($langs['english']) < 40) {
				echo "<input type=\"text\" name=\"trans[$index][english]\" value=\"{$langs['english']}\" size=\"40\" class=\"text\" />";
			} else {
			  $rows = round(strlen($langs['english']/35)) +1 ;
			  echo "<textarea name=\"trans[$index][english]\"  cols=\"40\" class=\"small\" rows=\"$rows\">".$langs['english']."</textarea>";
			}
		} else {
			echo $langs['english'];
			echo "<input type=\"hidden\" name=\"trans[$index][english]\" value=\""
				.($k ? $k : $langs['english'])
				."\" size=\"20\" class=\"text\" />";
		}
	?></td>
	<td><?php
		if ($lang != 'en') {
			$langs['lang'] = htmlspecialchars( @$langs['lang'], ENT_QUOTES );
			if (strlen($langs['lang']) < 40) {
				echo "<input type=\"text\" name=\"trans[$index][lang]\" value=\"{$langs['lang']}\" size=\"40\" class=\"text\" />";
			} else {
			  $rows = round(strlen($langs['lang']/35)) +1 ;
			  echo "<textarea name=\"trans[$index][lang]\"  cols=\"40\" class=\"small\" rows=\"$rows\">".$langs['lang']."</textarea>";
			}
		}
	?></td>
	<td align="center"><?php echo "<input type=\"checkbox\" name=\"trans[$index][del]\" />";?></td>
</tr>
<?php
	$index++;
}
?>
<tr>
	<td colspan="4" align="right">
		<input type="submit" value="<?php echo $AppUI->_( 'submit' );?>" class="button" />
	</td>
</tr>
</form>
</table>
