<?php
if (!defined('DP_BASE_DIR')){
  die('You should not access this file directly.');
}

require(DP_BASE_DIR.'/modules/ticketsmith/config.inc.php');

##
##	Ticketsmith sql handler
##

$name = dPgetParam($_POST, 'name', '');
$email = dPgetParam($_POST, 'email', '');
$subject = dPgetParam($_POST, 'subject', '');
$priority = dPgetParam($_POST, 'priority', '');
$description = dPgetParam($_POST, 'description', '');
$ticket_company = dPgetParam($_POST, 'ticket_company', 0);
$ticket_project = dPgetParam($_POST, 'ticket_project', 0);

$author = $name . " <" . $email . ">";
$tsql =
"INSERT INTO tickets (author,subject,priority,body,timestamp,type, ticket_company, ticket_project) ".
"VALUES('$author','$subject','$priority','$description',UNIX_TIMESTAMP(),'Open', $ticket_company, $ticket_project)";

$rc = mysql_query($tsql);


if (mysql_errno()) {
	$AppUI->setMsg( mysql_error() );
} else {
	$AppUI->setMsg( "Ticket added" );
	
	$ticket = mysql_insert_id();
	//Emailing notifications.
	$boundary = "_lkqwkASDHASK89271893712893";
	$message = "--$boundary\n";
	$message .= "Content-disposition: inline\n";
	$message .= "Content-type: text/plain\n\n";
	$message .= $AppUI->_('New Ticket') . ".\n\n";
	$message .= "Ticket ID: $ticket\n";
	$message .= "Author   : $author\n";
	$message .= "Subject  : $subject\n";
	$message .= "View     : ".DP_BASE_URL."/index.php?m=ticketsmith&a=view&ticket=$ticket\n";
	$message .= "\n--$boundary\n";
	$message .= "Content-disposition: inline\n";
	$message .= "Content-type: text/html\n\n";
	$message .= "<html>\n";
	$message .= "<head>\n";
	$message .= "<style>\n";
	$message .= ".title {\n";
	$message .= "	FONT-SIZE: 18pt; SIZE: 18pt;\n";
	$message .= "}\n";
	$message .= "</style>\n";
	$message .= "<title>".$AppUI->_('New Ticket')."</title>\n";
	$message .= "</head>\n";
	$message .= "<body>\n";
	$message .= "\n";
	$message .= "<TABLE border=0 cellpadding=4 cellspacing=1>\n";
	$message .= "	<TR>\n";
	$message .= "	<TD valign=top><img src=".DP_BASE_URL."/images/icons/ticketsmith.gif alt= border=0 width=42 height=42></td>\n";
	$message .= "		<TD nowrap><span class=title>".$AppUI->_('Trouble Ticket Management - New Ticket')."</span></td>\n";
	$message .= "		<TD valign=top align=right width=100%>&nbsp;</td>\n";
	$message .= "	</tr>\n";
	$message .= "</TABLE>\n";
	$message .= "<TABLE width=600 border=0 cellpadding=4 cellspacing=1 bgcolor=#878676>\n";
	$message .= "	<TR>\n";
	$message .= "		<TD bgcolor=white nowrap><font face=arial,san-serif size=2>".$AppUI->_('Ticket ID').":</font></TD>\n";
	$message .= "		<TD bgcolor=white nowrap><font face=arial,san-serif size=2>$ticket</font></TD>\n";
	$message .= "	</tr>\n";
	$message .= "	<TR>\n";
	$message .= "		<TD bgcolor=white><font face=arial,san-serif size=2>".$AppUI->_('Author').":</font></TD>\n";
	$message .= "		<TD bgcolor=white><font face=arial,san-serif size=2>" . str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace('"', '', $author))) . "</font></TD>\n";
	$message .= "	</tr>\n";
	$message .= "	<TR>\n";
	$message .= "		<TD bgcolor=white><font face=arial,san-serif size=2>".$AppUI->_('Subject').":</font></TD>\n";
	$message .= "		<TD bgcolor=white><font face=arial,san-serif size=2>$subject</font></TD>\n";
	$message .= "	</tr>\n";
	$message .= "	<TR>\n";
	$message .= "		<TD bgcolor=white nowrap><font face=arial,san-serif size=2>".$AppUI->_('View').":</font></TD>\n";
	$message .= "		<TD bgcolor=white nowrap><a href=\"".DP_BASE_URL."/index.php?m=ticketsmith&a=view&ticket=$ticket\"><font face=arial,sans-serif size=2>".DP_BASE_URL."/index.php?m=ticketsmith&a=view&ticket=$ticket</font></a></TD>\n";
	$message .= "	</tr>\n";
	$message .= "</TABLE>\n";
	$message .= "</body>\n";
	$message .= "</html>\n";
	$message .= "\n--$boundary--\n";

	$ticketNotification = dPgetSysVal( 'TicketNotify' );
	if (count($ticketNotification) > 0) {
		mail($ticketNotification[$priority], $AppUI->_('Trouble ticket')." #$ticket ", $message, "From: " . $CONFIG['reply_to'] . "\nContent-type: multipart/alternative; boundary=\"$boundary\"\nMime-Version: 1.0");
	}
}
$AppUI->redirect( "m=ticketsmith" );
?>