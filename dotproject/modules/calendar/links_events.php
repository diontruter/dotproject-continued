<?php /* CALENDAR $Id$ */

/**
* Sub-function to collect events within a period
* @param Date the starting date of the period
* @param Date the ending date of the period
* @param array by-ref an array of links to append new items to
* @param int the length to truncate entries by
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
function getEventLinks( $startPeriod, $endPeriod, &$links, $strMaxLen ) {
	$events = CEvent::getEventsForPeriod( $startPeriod, $endPeriod );

	// assemble the links for the events
	foreach ($events as $row) {
		$start = new Date( $row['event_start_date'] );

	// the link
		$link['href'] = '';
		$link['alt'] = $row['event_description'];
		$link['text'] = '<table cellspacing="0" cellpadding="0" border="0"><tr>'
			. '<td>' . dPshowImage( dPfindImage( 'event'.$row['event_type'].'.png', 'calendar' ), 16, 16, '' )
			. '</td><td><a href="?m=calendar&a=view&event_id='.$row['event_id'].'"><span class="event">'.$row['event_title'].'</span></a>'
			. '</td></tr></table>';
		$links[$start->format( DATE_FORMAT_TIMESTAMP_DATE )][] = $link;
	}
}
?>