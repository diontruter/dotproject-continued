<?php /* $Id$ */

// check permissions
$denyEdit = getDenyEdit( $m );
$user_id = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : $AppUI->user_id;

if ($denyEdit) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

if (empty( $sqlaction )) {
	$sqlaction = 0;
}
if (empty( $permission_id )) {
	$permission_id = 0;
}

//Insert, Update and delete first
if ($sqlaction == 1 && $permission_id == 0) {
	$apsql = "
	INSERT INTO permissions (permission_user, permission_grant_on, permission_item, permission_value)
	VALUES
	('$user_id', '$permission_grant_on', '$permission_item', '$permission_value')";
	db_exec( $apsql );

	$AppUI->setMsg( "Permission Created " );
} else if ($sqlaction == 1 && $permission_id <> 0) {
	$upsql ="UPDATE permissions
	SET
	permission_grant_on = '$permission_grant_on',
	permission_item = '$permission_item',
	permission_value = '$permission_value'
	WHERE permission_id = $permission_id";
	db_exec( $upsql );
	$AppUI->setMsg( "Permission Updated " );
} else if ($sqlaction == -1 && $permission_id <> 0) {
	$dpsql = "delete from permissions where permission_id =" . $permission_id;
	db_exec( $dpsql );
	$AppUI->setMsg( "Permission Deleted " );
}
$e = db_error();
if (strlen( $e ) > 0) {
	$AppUI->setMsg( $e );
}
//Pull User perms
$usql = "
SELECT u.user_id, u.user_username, p.permission_item, p.permission_id,
	p.permission_grant_on, p.permission_value,
	c.company_id, c.company_name, pj.project_id,
	pj.project_name, f.file_id, f.file_name, u2.user_id, u2.user_username
FROM users u, permissions p
LEFT JOIN companies c ON c.company_id = p.permission_item and p.permission_grant_on = 'companies'
LEFT JOIN projects pj ON pj.project_id = p.permission_item and p.permission_grant_on = 'projects'
LEFT JOIN files f ON f.file_id = p.permission_item and p.permission_grant_on = 'files'
LEFT JOIN users u2 ON u2.user_id = p.permission_item and p.permission_grant_on = 'users'
WHERE u.user_id = p.permission_user
AND u.user_id = $user_id
ORDER BY permission_grant_on, permission_item
";

$urc = db_exec( $usql );

//get username
$sql = "SELECT user_id, user_username FROM users WHERE user_id = $user_id";
db_loadHash( $sql, $uname );

//Pull all companies
$csql = "SELECT company_id, company_name FROM companies ORDER BY company_name";
$companies = db_loadHashList( $csql );

//Pull all users
$sql = "SELECT user_id, user_username FROM users ORDER BY user_username";
$users = db_loadHashList( $sql );

//Pull all projects
$sql = "SELECT project_id, project_name FROM projects ORDER BY project_name";
$projects = db_loadHashList( $sql );

$modules = array(
	"all",
	"admin",
	"calendar",
	"companies",
	"contacts",
	"files",
	"forums",
	"mcps",
	"plans",
	"projects",
	"tasks",
	"ticketsmith",
	"webmail"
);
$nmod = count( $modules );

//---------------------------------Begin Page -------------------------------//
?>

<script>
function editPerm( w, x, y, z ) {
	//w =Permission_id
	//x =permission_grant_on
	//y =permission_item
	//z =permission_value

	var form = document.perms;

	form.sqlaction2.value="edit";
	form.permission_id.value = w;
	x = x.toLowerCase();
	if (x == '<?php echo $modules[0]; ?>') {
		form.permission_grant_on.selectedIndex = 0;
	}
<?php
	for ($i=1; $i < $nmod; $i++) {
		echo "else if(x == '$modules[$i]')form.permission_grant_on.selectedIndex = $i;\n";
	}
?>
	if (z == 1) {
		form.permission_value.selectedIndex = 1;
	} else if (z == -1) {
		form.permission_value.selectedIndex = 2;
	} else {
		form.permission_value.selectedIndex = 0;
	}
	setPItem( y );
}

function setPItem( y ) {
	var form = document.perms;
	x = form.permission_grant_on[form.permission_grant_on.selectedIndex].value;
	x = x.toLowerCase();

	// Clear the select list
	var n = form.permission_item.length + 1;
	for (var i=0; i < n; i++) {
		eval( "form.permission_item.options[i]=null" )
	}

	//Set option 0 to all
	var option0 = new Option( "All", "-1" );
	form.permission_item.options[0] = option0;

	if (x == "companies") {
		<?php
		$i=1;
		$s = '';
		foreach ($companies as $id => $name) {
			$s .= "var option$i = new Option(\"$name\", \"$id\");\n";
			$s .= "form.permission_item.options[$i]=option$i;\n";
			$i++;
		}
		echo $s;
		?>
	} else if (x == "projects") {
		<?php
		$i=1;
		$s = '';
		foreach ($projects as $id => $name) {
			$s .= "var option$i = new Option(\"$name\", \"$id\");\n";
			$s .= "form.permission_item.options[$i]=option$i;\n";
			$i++;
		}
		echo $s;
		?>
	} else if (x == "users") {
		<?php
		$i=1;
		$s = '';
		foreach ($users as $id => $name) {
			$s .= "var option$i = new Option('$name', '$id');\n";
			$s .= "form.permission_item.options[$i]=option$i;\n";
			$i++;
		}
		echo $s;
		?>
	}
// select the item
	var n = form.permission_item.length;
	for (var i=0; i < n; i++) {
		//alert( i+','+form.permission_item.options[i].value+','+ form.permission_item.options[i].text );
		if ( form.permission_item.options[i].value == y ) {
			form.permission_item.selectedIndex = i;
		}
	}
}

function clearIt(){
	var form = document.perms;
	form.sqlaction2.value = "add";
	form.permission_id.value = 0;
	form.permission_grant_on.selectedIndex = 3;
	setPItem();
}

function delIt( user_id, perm_id ){
	if (confirm( 'Are you sure you want to delete this permission?' )) {
		var form = document.topform;
		window.location = './index.php?a=permissions&m=admin&sqlaction=-1&user_id='+user_id+'&permission_id='+perm_id;
	}
}

function changeUser(){
	var form = document.topform;
	window.location = "./index.php?m=admin&a=permissions&user_id=" + form.change_user[form.change_user.selectedIndex].value;
}
</script>


<table border="0" cellpadding="0" cellspacing="1">
<tr>
	<td><img src="./images/icons/admin.gif" alt="" border="0" width=42 height=42></td>
	<td nowrap><h1>*</h1></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
<tr>
	<td nowrap width="100%">
	<a href="./index.php?m=admin">user list</a>
<?php if (!$denyEdit) { ?>
	<strong>:</strong> <a href="./index.php?m=admin&a=addedituser&user_id=<?php echo $user_id;?>">edit this User</a>
<?php } ?>
	</td>
<form name="topform">
	<td align="right" nowrap>
		Select user:
<?php
	echo arraySelect( $users, 'change_user', 'onchange="changeUser()" class="text" size="1" style="width:100px;"', $user_id );
?>
	</td>
</form>
	<td align="right">
<?php if (!$denyEdit) { ?>
		<input type="button" class=button value="new user" onClick="javascript:window.location='./index.php?m=admin&a=addedituser';">
<?php } ?>
	</td>
</tr>
</table>

<table width="50%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<td width="60"> &nbsp;</td>
	<th>Module</th>
	<th>Item</th>
	<th>Permission Type</th>
</tr>
<?php
if(mysql_num_rows($urc) == 0) {
	echo '<tr><td colspan=4 align=center><strong>No permissions for this User</strong></td></tr>';
};

$i = 0;
while ($row = mysql_fetch_array( $urc )) {
	echo "<tr>";
	echo "<td>"
		."<a href=# onClick=\"editPerm({$row['permission_id']},'{$row['permission_grant_on']}',{$row['permission_item']},{$row['permission_value']});\">edit</a> | "
		."<a href=# onClick=\"delIt({$user_id},{$row['permission_id']})\">del</A></td>";

	if($row['permission_grant_on'] == "all" && $row['permission_item'] == -1 && $row['permission_value'] == -1) {
		echo '<td style="background-color:#ffc235">';
	} else if($row['permission_item'] == -1 && $row['permission_value'] == -1) {
		echo '<td style="background-color:#ffff99">';
	} else {
		echo "<td>";
	}

	echo $row['permission_grant_on'] . "</td>";

	if($row['permission_grant_on'] =="files" && $row['permission_item'] >0) {
		$item = $row['file_name'];
	} else if($row['permission_grant_on'] =="users" && $row['permission_item'] >0) {
		$item = $row['user_username'];
	} else if($row['permission_grant_on'] =="projects" && $row['permission_item'] >0) {
		$item = $row['project_name'];
	} else if($row['permission_grant_on'] =="companies" && $row['permission_item'] >0) {
		$item = $row['company_name'];
	} else {
		$item = $row['permission_item'];
	}

	if($item == "-1") {
		$item = "all";
	}

	if($row['permission_value'] ==-1) {
		$value = "read-write";
	} else if($row['permission_value'] ==1) {
		$value = "read-only";
	} else {
		$value = "deny";
	}

	echo "<td>" . $item . "</td>";
	echo "<td>" . $value . "</td>";
	echo "</tr>";
}
?>
</table>

<table>
<tr>
	<td>Key:</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffc235">&nbsp; &nbsp;</td>
	<td>=SuperUser</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffff99">&nbsp; &nbsp;</td>
	<td>=full access to module</td>
</tr>
</table>

<br />&nbsp;<br />
<table width="95%" cellpadding="2" border="0" cellspacing="1" class="std">
<tr>
	<th colspan="4">Add or modify permissions</th>
</tr>
<form method="post" name="perms">

<input type="hidden" name="user_id" value="<?php echo $user_id;?>">
<input type="hidden" name="permission_id" value="0">
<input type="hidden" name="action" value="permissions">
<input type="hidden" name="module" value="admin">
<input type="hidden" name="sqlaction" value="1">
<tr>
	<td>Module</td>
	<td>Item</td>
	<td>Level</td>
</tr>
<tr>
	<td width="15%" nowrap>
		<select name="permission_grant_on" onChange="setPItem()" style="text">
		<option value="<?php echo $modules[0]; ?>" selected><?php echo $modules[0]; ?>
	<?php
		for ($i=1; $i < $nmod; $i++) {
			echo '<option value="' . $modules[$i] . '">' . $modules[$i];
		}
	?>
		</select>
	</td>
	<td width="25%" nowrap>
		<select name="permission_item" style="text">
		<option value="-1" selected>All

		</select>
	</td>
	<td width="25%" nowrap>
		<select name="permission_value" style="text">
		<option value="0">deny
		<option value="1">read-only
		<option value="-1" selected>read-write
		</select>
	</td>
	<td width="100%">&nbsp;</td>
</tr>
<tr>
	<td colspan=2>
		<input type="reset" value="clear" style="font-size:9px;width:100px;" name="sqlaction" onClick="clearIt();">
	</td>
	<td colspan=2 align="right">
		<input type="submit" value="add" style="font-size:9px;width:100px;" name="sqlaction2">
	</td>
</tr>
</form>
</table>
