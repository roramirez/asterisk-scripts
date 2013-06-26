<?php

/* 
*
* process asterisk cdr file (Master.csv) insert usage
* values into a pgsql database which is created for use
* The script will only insert NEW records so it is safe
* to run on the same log over-and-over.
*
* autor: John Lange (john@johnlange.ca)
* Date: Version 2 Released July 8, 2008
* http://johnlange.wordpress.com/tech-tips/asterisk/asterisk-cdr-csv-mysql-import-v20/
* 
* adapted for PostgreSQL 
* rodrigo@blackhole.cl
* Date: Versión 2.1pg Realses Jun 26, 2013
*
* Here is what the script does:
*
* Parse each row from the text log and insert it into the database after testing for a
* matching "calldate, src, duration" record in the database. Note that not all fields are
* tested.
*
* If you have a large existing database it is recomended that you add an index to the calldate
* field which will greatly speed up this import.
*
*/

//Db config
$db_host = 'localhost';
$db_name = 'asterisk';
$db_login = 'user_pbx';
$db_port = 5432;
$db_pass = 'pleasechangeme';

if($argc == 2) {
    $logfile = $argv[1];
} else {
    print("Usage ".$argv[0]." file_name\n");
    print("Where filename is the path to the Asterisk csv file to import (Master.csv)\n");
    print("This script is safe to run multiple times on a growing log file as it only imports records that are newer than the database\n");
    exit(0);
}

// connect to PostgreSQL db
$conn_string = sprintf("host=%s port=%s dbname=%s user=%s password=%s",
                        $db_host, $db_port, $db_name, $db_login, $db_pass);

$linkmb = pg_connect($conn_string) or die("Could not connect : " . pg_last_error());


//** 1) Find records in the asterisk log file. **
$rows = 0;
$handle = fopen($logfile, "r");

while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    /* NOTE: the fields in Master.csv can vary. 
     * This should work by default on all installations 
      *but you may have to edit the next line to match your configuration */

    list($accountcode,
         $src, 
         $dst, 
         $dcontext, 
         $clid, 
         $channel, 
         $dstchannel, 
         $lastapp, 
         $lastdata, 
         $start, 
         $answer, 
         $end, 
         $duration, 
         $billsec, 
         $disposition, 
         $amaflags ) = $data;


    /** 2) Test to see if the entry is unique **/
    $sql = "SELECT calldate, src, duration".
           " FROM cdr".
           " WHERE calldate='$end'".
           " AND src='$src'".
           " AND duration='$duration'".
           " LIMIT 1";
           
           
           
    if(!($result = pg_query($linkmb, $sql))) {
        print("Invalid query: " . pg_last_error()."\n");
        print("SQL: $sql\n");
        die();
    }

    if(pg_num_rows($result) == 0) { // we found a new record so add it to the DB
    
        // 3) insert each row in the database
        $sql = "INSERT INTO cdr (calldate, clid, src, dst, dcontext, channel, ".
               "dstchannel, lastapp, lastdata, duration, billsec, disposition,".
               "amaflags, accountcode) ".
               "VALUES('$end', '".
                pg_escape_string($clid).
                "', '$src', '$dst', '$dcontext', '$channel', '$dstchannel', ".
                "'$lastapp', '$lastdata', '$duration', '$billsec', '$disposition', ".
                "'$amaflags', '$accountcode')";


        if(!($result2 = pg_query($linkmb, $sql))) {
            print("Invalid query: " . pg_last_error()."\n");
            print("SQL: $sql\n");
            die();
        }
        print("Inserted: $end $src $duration\n");
        $rows++;
    } else {
        print("Not unique: $end $src $duration\n");
    }
}

    fclose($handle);
    print("$rows imported\n");
?>
