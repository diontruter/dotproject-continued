<?php /* CLASSDEFS $Id$ */
/*
	Date class
	provides an object oriented way to manipulate date and time
@BUGS
	As Date class uses Unix timetamp underlyingly, Date is only functionning on period :
	01 Jan 1970 00:00:00 +0000 to 19 Jan 2038 03:14:07 +0000
@VERSION 0.5
*/

define( 'FMT_DATEISO', '%Y%m%dT%H%M%S' );
define( 'FMT_DATELDAP', '%Y%m%d%H%M%SZ' );
define( 'FMT_DATEMYSQL', '%Y-%m-%d %H:%M:%S' );
define( 'FMT_DATERFC822', '%a, %d %b %Y %H:%M:%S' );
define( 'FMT_TIME', '%H:%M' );
define( 'WDAY_SUNDAY',    0 );
define( 'WDAY_MONDAY',    1 );
define( 'WDAY_TUESDAY',   2 );
define( 'WDAY_WENESDAY',  3 );
define( 'WDAY_THURSDAY',  4 );
define( 'WDAY_FRIDAY',    5 );
define( 'WDAY_SATURDAY',  6 );
define( 'SEC_MINUTE',    60 );
define( 'SEC_HOUR',    3600 );
define( 'SEC_DAY',    86400 );

isset($DATE_BANKHOLIDAYS) or $DATE_BANKHOLIDAYS = array();


class CDate
{
	/* unix timestamp */
	var $ts=0;
	var $Y=0;
	var $M=0;
	var $D=0;
	var $h=0;
	var $m=0;
	var $s=0;
	/* @scope protected */
	var $change = 0; // 1 if date needs recalculation
	var $format;

	function CDate( $ts=null, $format='' ) {
		if( $ts !== null ) {
			$this->setTimestamp($ts);
		} else {
			$this->setTimestamp(time());
		}
		if ($format) {
			$this->format = $format;
		}
	}

	/****
	 *	Build a date from an ISO datetime string
	 * @params $datetime string the iso-X datetime with both date and time components
	 * @static @factory
	 * @return a Date object if ok, NULL otherwise
	 *
	 * tolerant: accepts format variants with or without separators: "-" for date and ":" for time
	 *	20010801123059 => OK
	 *	20010801T123059Z => OK
	 *	20010801 123059Z => OK
	 *	2001-08-01T12:30:59 => OK
	 *	20010801T12:30:59Z => OK
	 * 2001-08-01 => error
	 * 2001-08-01T01:30 => error
	 * 2001-08-01T1:30:59 => error
	 *	timezone code is yet ignored ( not handled )
	 */
	function fromDatetime( $datetime, $format='' )
	{
		if( ! preg_match( "/^(\d{4})-?(\d{2})-?(\d{2}).?(\d{2}):?(\d{2}):?(\d{2})(.?)$/", $datetime, $a ) ) {
			return NULL;
		}
		$obj = new CDate();
		$obj->setDate( $a[1], $a[2], $a[3] );
		$obj->setTime( $a[4],$a[5], $a[6] );
		if ($format) {
			$obj->setFormat( $format );
		}
		return $obj;
	}

	function toString( $format=null )
	{
		return strftime( $format ? $format : $this->format, $this->getTimestamp() );
	}

	// since = reference timestamp
	function toAgo() {
		GLOBAL $AppUI;

		$since = abs( $this->compareTo( new CDate() ) );
		if ($since < 3600) {
			$str = sprintf( "%d ".$AppUI->_( 'minutes' ), $since/60 );
		} else if ($since < 48*3600) {
			$str = sprintf( "%d ".$AppUI->_( 'hours' ), $since/3600 );
		} else {
			$str = sprintf( "%d ".$AppUI->_( 'days' ), $since/(24*3600) );
		}
		return sprintf($AppUI->_('%s ago'), $str);
	}

	function toSQL()
	{
		return strftime( FMT_DATEMYSQL, $this->getTimestamp() );
	}

	/*
	 * can use as static form eg: Date::format( "%Y", $ts )
	 * @static
	 */
	function format( $format, $timestamp )
	{
		return strftime( $format, $timestamp );
	}

/**************************************************** GETTERS ****/
	function getStartSpaces() {
		if( $this->change ) $this->_calc();
		$sp = ($this->weekday - $this->D % 7) + 1;
		if ($sp < 0) {
			$sp += 7;
		}
		return ($sp - LOCALE_FIRST_DAY) % 7;
	}

	function getYear() {
		if( $this->change ) $this->_calc();
		return $this->Y;
	}

	function getMonth() {
		if( $this->change ) $this->_calc();
		return $this->M;
	}

	function getDay() {
		if( $this->change ) $this->_calc();
		return $this->D;
	}

	function getWeekday() {
		if( $this->change ) $this->_calc();
		return $this->weekday;
	}

	function getYearDay()
	{
		if( $this->change )
			$this->_calc();
		return date( "z", $this->ts );
	}

	function getHours() {
		if( $this->change ) $this->_calc();
		return $this->h;
	}

	function getMinutes() {
		if( $this->change ) $this->_calc();
		return $this->m;
	}

	function getSeconds() {
		if( $this->change ) $this->_calc();
		return $this->s;
	}

	function getSecondsInDay() {
		if( $this->change ) $this->_calc();
		$ts1 = mktime( 0,0,0, $this->M, $this->D, $this->Y );
		return $this->ts - $ts1;
	}

	// return Unix timestamp (seconds since epoch )
	function getTimestamp() {
		if( $this->change ) $this->_calc();
		return $this->ts;
	}

	function daysInMonth() {
		if( $this->change )
			$this->_calc();
		return date( "t", $this->ts );
	}

	function daysInYear() {
		if( $this->change )
			$this->_calc();
		return date( "t", $this->ts );
	}

	function isToday() {
		// bad guess fixme
		$today = getdate( time() );
		return $this->Y == $today['year'] && $this->M == $today['mon'] && $this->D == $today['mday'];
	}
// comparison functions
	function compareTo( $date ) {
		if( ! is_object($date) || get_class($date) != "cdate" )
			return false;
		return $this->getTimestamp() - $date->getTimestamp();
	}

	function inMonth( $date ) {
		return ($this->M == $date->M && $this->Y == $date->Y);
	}

	function isBetween( $start, $end ) {
		$ts = $this->getTimestamp();
		return ($start->getTimestamp() <= $ts && $ts <= $end->getTimestamp());
	}

	function daysTo( $date, $real=false ) {
		if( ! is_object($date) || get_class($date) != "cdate" ) {
			return false;
		}
		$temp = $this;
		if (!$real) {
		// zero times for comparison otherwise comparision is based on 24 hour differences
			$temp->setTime( 0,0,0 );
			$date->setTime( 0,0,0 );
		}
		$deltats = $date->getTimestamp() - $temp->getTimestamp();
		if( $deltats > 0 )
			return (int) floor( $deltats / SEC_DAY );
		else
			return (int) ceil( $deltats / SEC_DAY );
	}

// arithmetic functions
	function addDays( $numdays ) {
		$this->D += $numdays;
		$this->_calc();
	}

	function addMonths( $num ) {
	// need to trap adding month from longer month to shorter month
	// eg, adding 1 month to Jan 31 will actually push the date into March
		$temp = $this->D;
		$this->D = 1;
		$this->M += $num;
		$this->_calc();
	// fix up the day
		$this->D = min( $temp, $this->daysInMonth() );
		$this->_calc();
	}

	function addYears( $num ) {
		$this->Y += $num;
		$this->_calc();
	}

	function addHours( $num ) {
		$this->h += $num;
		$this->_calc();
	}

	function addMinutes( $num ) {
		$this->m += $num;
		$this->_calc();
	}

	function addSeconds( $num ) {
		$this->s += $num;
		$this->_calc();
	}

/**************************************************** SETTERS ****/

	function getBankHoliday()
	{
		global $DATE_BANKHOLIDAYS;

		$dat = $this->toString('%m-%d');
		if( isset( $DATE_BANKHOLIDAYS[$dat]) )
			return $DATE_BANKHOLIDAYS[$dat];
		else
			return NULL;
	}

	function setFormat( $format=null ) {
		$this->format = $format;
	}

	function setTimestamp( $ts )
	{
		// TODO : basic validation
		$this->ts = $ts;
		$a = getdate( $this->ts );
		$this->Y = $a['year'];
		$this->M = $a['mon'];
		$this->D = $a['mday'];
		$this->h = $a['hours'];
		$this->m = $a['minutes'];
		$this->s = $a['seconds'];
		$this->weekday = $a['wday'];
		$this->change = 0;
		unset($a);
	}

	function setDate( $Y, $M, $D=1 )
	{
		$this->Y = $Y;
		$this->M = $M;
		$this->D = $D;
		$this->change = 1;
	}

	function setTime( $h, $m, $s=0 )
	{
		$this->h = $h;
		$this->m = $m;
		$this->s = $s;
		$this->change = 1;
	}

	function setHours( $val )
	{
		$this->h = $val;
		$this->change = 1;
	}

	function setMinutes( $val )
	{
		$this->m = $val;
		$this->change = 1;
	}

	function setSeconds( $val )
	{
		$this->s = $val;
		$this->change = 1;
	}

	function setYear( $val )
	{
		$this->Y = $val;
		$this->change = 1;
	}

	function setMonth( $val )
	{
		$this->M = $val;
		$this->change = 1;
	}

	function setDay( $val )
	{
		$this->D = $val;
		$this->change = 1;
	}

	// setWeekday( [0-6] )
	function setWeekday( $weekday )
	{
		$this->_calc();
		$this->D += ($weekday - $this->weekday);
		$this->change = 1;
	}

	function isValid()
	{
		if( ! checkdate( $this->M, $this->D, $this->Y ) )
			return false;
		if( $this->Y < 1970 || $this->Y > 2038 )
			return false;
		if( $this->h < 0 || $this->h > 23 || $this->m < 0 || $this->m > 59 || $this->s < 0 || $this->s > 59 )
			return false;
		return true;
	}

	/**
	 *	@protected
	 */
	function _calc()
	{
		$this->ts = mktime( $this->h, $this->m, $this->s, $this->M, $this->D, $this->Y );
		$a = @getdate( $this->ts );
		$this->Y = $a['year'];
		$this->M = $a['mon'];
		$this->D = $a['mday'];
		$this->h = $a['hours'];
		$this->m = $a['minutes'];
		$this->s = $a['seconds'];
		$this->weekday = $a['wday'];
		$this->change = 0;
	}
}
?>
