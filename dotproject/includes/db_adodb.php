<?php /* $Id$ */
/*
	Based on Leo West's (west_leo@yahooREMOVEME.com):
	lib.DB
	Database abstract layer
	-----------------------
	ADODB VERSION
	-----------------------
	A generic database layer providing a set of low to middle level functions
	originally written for WEBO project, see webo source for "real life" usages
*/
require_once( "{$dPconfig['root_dir']}/lib/adodb/adodb.inc.php" );

$db = NewADOConnection($dPconfig['dbtype']);

function db_connect( $host='localhost', $dbname, $user='root', $passwd='', $port='3306', $persist=false ) {
        global $db, $dPrunLevel, $ADODB_FETCH_MODE;

	if ($persist) {
		if ($db->PConnect($host, $user, $passwd, $dbname)) {
			$dPrunLevel = 2;
		} else {
			($_GET['m']=='install') ? TRUE : die( 'FATAL ERROR: Connection to database server failed');
		}

	} else {
		if ($db->Connect($host, $user, $passwd, $dbname)) {
			$dPrunLevel = 2;
		} else {
			($_GET['m']=='install') ? TRUE : die( 'FATAL ERROR: Connection to database server failed');
		}
	}

        $ADODB_FETCH_MODE=ADODB_FETCH_BOTH;
}

function db_error() {
        global $db;
	if (! is_object($db))
	  dprint(__FILE__,__LINE__, 0, "Database object does not exist");
	return $db->ErrorMsg();
}

function db_errno() {
        global $db;
	if (! is_object($db))
	  dprint(__FILE__,__LINE__, 0, "Database object does not exist");
	return $db->ErrorNo();
}

function db_insert_id() {
        global $db;
	if (! is_object($db))
	  dprint(__FILE__,__LINE__, 0, "Database object does not exist");
	return $db->Insert_ID();
}

function db_exec( $sql ) {
        global $db;

	if (! is_object($db))
	  dprint(__FILE__,__LINE__, 0, "Database object does not exist");
	$qid = $db->Execute( $sql );
	dprint(__FILE__, __LINE__, 10, $sql);
	if ($msg = db_error())
        {
                global $AppUI;
                dprint(__FILE__, __LINE__, 7, "Error executing: <pre>$sql</pre>");
                /*include_once(dPgetConfig('root_dir') . '/db/create_db_fields.php');
                // Useless statement, but it is being executed only on error, 
                // and it stops infinite loop.
                $db->Execute( $sql );
                if (!db_error())
                        echo '<script language="JavaScript"> location.reload(); </script>';*/
        }
        if ( ! $qid && preg_match('/^\<select\>/i', $sql) )
	  dprint(__FILE__, __LINE__, 0, $sql);
	return $qid;
}

function db_free_result($cur ) {
        // TODO
        //	mysql_free_result( $cur );
        // Maybe it's done my Adodb
	if (! is_object($cur))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_free_result");
        $cur->Close();
}

function db_num_rows( $qid ) {
	if (! is_object($qid))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_num_rows");
	return $qid->RecordCount();
        //return $db->Affected_Rows();
}

function db_fetch_row( &$qid ) {
	if (! is_object($qid))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_row");
	return $qid->FetchRow();
}

function db_fetch_assoc( &$qid ) {
	if (! is_object($qid))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_assoc");
        return $qid->FetchRow();
}

function db_fetch_array( &$qid  ) {
	if (! is_object($qid))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_array");
        return $qid->FetchRow();
}

function db_fetch_object( $qid  ) {
	if (! is_object($qid))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_object");
	return $qid->FetchNextObject(false);
}

function db_escape( $str ) {
        global $db;
	return substr($db->qstr( $str ), 1, -1);
}

function db_version() {
        return "ADODB";
}

function db_unix2dateTime( $time ) {
        global $db;
        return $db->DBDate($time);
}

function db_dateTime2unix( $time ) {
        global $db;

        return $db->UnixDate($time);

        // TODO - check if it's used anywhere...
//	if ($time == '0000-00-00 00:00:00') {
//		return -1;
//	}
}
?>
