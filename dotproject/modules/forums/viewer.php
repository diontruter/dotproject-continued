<?php
//view posts
$forum_id = isset($_GET["forum_id"]) ? $_GET["forum_id"] : 0;
$message_id = isset($_GET["message_id"]) ? $_GET["message_id"] : 0;
$post_message = isset($_GET["post_message"]) ? $_GET["post_message"] : 0;
$f = isset( $_GET['f'] ) ? $_GET['f'] : 0;

// check permissions
$denyRead = getDenyRead( $m, $forum_id );
$denyEdit = getDenyEdit( $m, $forum_id );

if ($denyRead || ($post_message & $denyEdit)) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

$sql = "
SELECT forum_id, forum_project,	forum_description, forum_owner, forum_name,
	forum_create_date, forum_last_date, forum_message_count, forum_moderated,
	user_first_name, user_last_name,
	project_name, project_color_identifier
FROM forums, users, projects 
WHERE user_id = forum_owner 
	AND forum_id = $forum_id 
	AND forum_project = project_id
";
$rc = db_exec( $sql );
$row = db_fetch_assoc($rc);
$forum_name = $row["forum_name"];
echo db_error();
?>
<table width="98%" cellspacing="1" cellpadding="1" border="0">
<input type=hidden name=dosql value=searchfiles>
<tr>
	<td><img src="./images/icons/communicate.gif" alt="" border="0" width="42" height="42"></td>
	<td nowrap width="100%"><span class="title"><?php echo $AppUI->_( 'Project' ).' '.$AppUI->_( 'Forum' );?></span></td>
<form name="forum_filter" method="GET" action="./index.php">
<input type="hidden" name="m" value="forums">
<input type="hidden" name="a" value="viewer">
<input type="hidden" name="forum_id" value="<?php echo $forum_id;?>">
	<td nowrap>
<?php
	echo arraySelect( $filters, 'f', 'size="1" class="text" onchange="document.forum_filter.submit();"', $f );
?>
	</td>
</form>
	<td><img src="images/shim.gif" width=5 height=5></td>
<form name="searcher" action="./index.php?m=files&a=search" method="post">
	<td width="100%" align="right">
		<input class="button" type="text" name="s" maxlength="30" size="20" value="Not implemented" disabled>
	</td>
	<td><img src="images/shim.gif" width="5" height="5"></td>
	<td><input class="button" type="submit" value="<?php echo $AppUI->_( 'search' );?>" disabled></td>
	<td><img src="images/shim.gif" width="5" height="5"></td>
</form>
</tr>
</table>

<table width="98%" cellspacing="0" cellpadding="2" border="0" class="std">
<tr>
	<td height="20" colspan="3" bgcolor="<?php echo $row["project_color_identifier"];?>" style="border: outset #D1D1CD 1px">
		<font size="2" color=<?php echo bestColor( $row["project_color_identifier"] );?>><b><?php echo @$row["forum_name"];?></b></font>
	</td>
</tr>
<tr>
	<td align="left" nowrap><?php echo $AppUI->_( 'Related Project' );?>:</td>
	<td nowrap><b><?php echo $row["project_name"];?></b></td>
	<td valign="top" width="50%" rowspan=99><b><?php echo $AppUI->_( 'Description' );?>:</b><br><?php echo @str_replace(chr(13), "&nbsp;<BR>",$row["forum_description"]);?></td>
</tr>
<tr>
	<td align="left"><?php echo $AppUI->_( 'Forum Owner' );?>:</td>
	<td nowrap><?php
		echo $row["user_first_name"].' '.$row["user_last_name"];
		if (intval( $row["forum_id"] ) <> 0) {
			echo " (".$AppUI->_( 'moderated' ).") ";
		}?>
	</td>
</tr>
<tr>
	<td align="left"><?php echo $AppUI->_( 'Created On' );?>:</td>
	<td nowrap><?php echo fromDate(@$row["forum_create_date"]);?></td>
</tr>
</table>

<?php
if($post_message){
	include("./modules/forums/post_message.php");
} else if($message_id == 0) {
	include("./modules/forums/view_topics.php");
} else {
	include("./modules/forums/view_messages.php");
}?>
