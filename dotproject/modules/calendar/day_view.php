<?php

// check permissions
$denyRead = getDenyRead( $m );

if ($denyRead) {
		echo '<script language="javascript">
		window.location="./index.php?m=help&a=access_denied";
		</script>
';
}

//define up event array
$earr = array();

// Set Day, Month, Year
if (empty( $thisMonth )) $thisMonth = date("n", time());
if (empty( $thisYear )) $thisYear = date("Y", time());
if (empty( $thisDay )) $thisDay = date("d", time());

$thisYear = intval($thisYear);
$thisMonth = intval($thisMonth);
$thisDay = intval($thisDay);

if ($thisDay < 1) $thisDay = 1;

if (empty( $todaysDay )) $todaysDay = date("d", time());
if (empty( $todaysMonth )) $todaysMonth = intval(date("m", time()));
if (empty( $todaysYear )) $todaysYear = date("Y", time());
if (empty( $field )) $field = "x";
$day=0;

// Figure out the last day of the month
$lastday[1]=31;
// Check for Leap Years
if( checkdate( $thisMonth, 29, $thisYear ) ) { $lastday[2] = 29; }else { $lastday[2]=28; }
$lastday[3]=31;
$lastday[4]=30;
$lastday[5]=31;
$lastday[6]=30;
$lastday[7]=31;
$lastday[8]=31;
$lastday[9]=30;
$lastday[10]=31;
$lastday[11]=30;
$lastday[12]=31;

//Short Day names
$dayNamesShort = array( "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" );

if($thisDay > $lastday["$thisMonth"]){$thisDay = $lastday["$thisMonth"];}

$sqldate = $thisYear . "-" . $thisMonth . "-" . $thisDay;

$prevYear = $thisYear;
$nextYear = $thisYear;
$prevMonth = $thisMonth;
$nextMonth = $thisMonth;

$yesterday = $thisDay - 1;
if ($yesterday < 1) {
	$prevMonth--;
	if ($prevMonth  < 1) {
				$prevMonth = 12;
				$prevYear--;
	}
	$yesterday = $lastday[$prevMonth];
}

$tomorrow = $thisDay + 1;
if ($tomorrow > $lastday[$thisMonth]) {
	$nextMonth++;
	if ($nextMonth > 12) {
				$nextMonth = 1;
				$nextYear++;
	}
	$tomorrow=1;
}

//Get events for today
$thismorn = mktime(0,0,0,$thisMonth, $thisDay, $thisYear);
$thiseve = $thismorn + 86399;

$sql = "
Select event_title, event_id, event_start_date, event_end_date
from events
where event_start_date < $thiseve and event_end_date >= $thismorn
order by event_start_date
";

$rc = mysql_query($sql);
if ($rc != false) {
	while ($row = mysql_fetch_array($rc)) {
				$earr[] = $row;
	}
} else {
	echo mysql_error();
}
?>

<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<TR>
	<TD><img src="./images/icons/calendar.gif" alt="Calendar" border="0" width="42" height="42"></td>
	<TD nowrap><span class="title">Day View</span></td>
	<TD align="right" width="100%">&nbsp;</td>
</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
<TR>
	<TD width="50%" nowrap>
		<a href="./index.php?m=calendar">Month View</a> :
		<a href="./index.php?m=calendar&a=week_view<?php echo "&thisYear=" . $thisYear . "&thisMonth=" . $thisMonth . "&thisDay=" . $thisDay;?>">Week View</a>
	</td>
	<TD width="50%" align="right">&nbsp;</td>
</TR>
</table>

<table border=0 cellspacing=1 cellpadding=2 width="95%" class=bordertable>
<tr>
	<td align=center>
		<a href="<?php echo("./index.php?m=calendar&a=day_view&thisYear=" . $prevYear . "&thisMonth=" . $prevMonth . "&thisDay=" . $yesterday ."&field=" . $field);?>"><img src="./images/prev.gif" width="16" height="16" alt="pre" border="0"></A>
	</td>
	<td width="100%">
		<b><?php echo $thisDay;?> <a href="<?php echo("./index.php?m=calendar&thisYear=" . $thisYear . "&thisMonth=" . $thisMonth);?>"><?php echo strftime("%B", mktime(0,0,0,$thisMonth,1,$thisYear));?> <?php echo $thisYear?></a></b>
	</td>
	<td align=center>
		<?php echo "<a href='./index.php?m=calendar&a=day_view&thisYear=" . $nextYear . "&thisMonth=" . $nextMonth . "&thisDay=" . $tomorrow ."&field=" . $field ."'>";?><img src="./images/next.gif" width="16" height="16" alt="next" border="0"></A>
  </td>
</tr>
</table>

<table width="95%" cellspacing=1 cellpadding=2 bgcolor="#efefe7">
<tr bgcolor="#cccccc">
	<td><b>Edit</b></td>
	<td><b>Event</b></td>
	<td><b>Start Date</b></td>
	<td><b>Time</b></td>
	<td><b>End Date</b></td>
	<td><b>Time</b></td>
</tr>
<?php
	foreach ($earr as $datum) {
?>
<TR>
	<TD width="5%">
		<A href="index.php?m=calendar&a=addedit&event_id=<?php echo $datum[1]; ?>">
		<img src="images/icons/pencil.gif" alt="Edit Event" border="0" width="12" height="12">
		</a>
	</td>
	<td width="50%"><?php echo $datum[0]; ?></td>
	<td width="10%"><?php echo fromDate( strftime( "%Y-%m-%d", $datum[2] ) ); ?></td>
	<td width="5%"><?php echo strftime( "%H:%M", $datum[2] ); ?></td>
	<td width="10%"><?php echo fromDate( strftime( "%Y-%m-%d", $datum[3] ) ); ?></td>
	<td width="5%"><?php echo strftime( "%H:%M", $datum[3] ); ?></td>
</tr>
<?php
	}
?>
</table>