<?php
/**
 * @author Ryan Suess
 * @copyright 2009
 */
//require_once("includes/config.inc.php"); 

// pull in the file with the database class 
//require_once("includes/Database.class.php"); 
//require_once("includes/JSON.class.php");


$times['08:15']['start'] = '8:15';
$times['08:15']['end'] = '8:25';
$times['08:25']['start'] = '8:25';
$times['08:25']['end'] = '8:35';
$times['08:35']['start'] = '8:35';
$times['08:35']['end'] = '8:45';
$times['08:45']['start'] = '8:45';
$times['08:45']['end'] = '8:55';
$times['08:55']['start'] = '8:55';
$times['08:55']['end'] = '9:05';
$times['09:05']['start'] = '9:05';
$times['09:05']['end'] = '9:15';
$times['09:15']['start'] = '9:15';
$times['09:15']['end'] = '9:25';

$times['14:30']['start'] = '14:30';
$times['14:30']['end'] = '14:40';
$times['14:40']['start'] = '14:40';
$times['14:40']['end'] = '14:50';
$times['14:50']['start'] = '14:50';
$times['14:50']['end'] = '3:00';
$times['15:00']['start'] = '15:00';
$times['15:00']['end'] = '15:10';
$times['15:10']['start'] = '15:10';
$times['15:10']['end'] = '15:20';
$times['15:20']['start'] = '15:20';
$times['15:20']['end'] = '15:30';
$times['15:30']['start'] = '15:30';
$times['15:30']['end'] = '15:40';


require_once("../vo/SurgeryManager/Day.php");
require_once("../vo/SurgeryManager/DropDownFormInfo.php");
require_once("../vo/SurgeryManager/VoFormData.php");
require_once("../vo/SurgeryManager/Month.php");
require_once("../vo/SurgeryManager/Note.php");
require_once("../vo/SurgeryManager/Patient.php");
require_once("../vo/SurgeryManager/Pdf.php");
require_once("../vo/SurgeryManager/Surgery.php");
require_once("../vo/SurgeryManager/Form_Info.php");
//C:\inetpub\SurgeryManager\flexservices\services\vo\SurgeryManager


/* Database Functions */

/// take clineo patient info and make it more usable


function _getDayDetails($date)
{
    $ret = array();
    $ret['followups'] = _getDaysFollowupTimes($date);
    //$ret['surgeryStart'] = _getSurgeryStartTime($date);


    return $ret;
}


function _setDayDetails($data)
{
    $_date = $data['date'];
    $ret[] = _setDaysFollowupTimes($_date, $data['followups']);
    return $ret;
}

function _setDaysFollowupTimes($_date, $fu)
{
    global $db;
    $q = "DELETE FROM surgeryday WHERE _date='" . $_date . "'";
    $ret['query'][] = $q;
    $db->query($q);
    foreach ($fu as $k => $v) {
        $q = "INSERT INTO surgeryday (`_date`,`key`,`value`) VALUES('" . $_date . "','" . $k . "','" . $v . "')";
        $ret['query'][] = $q;
        $db->query($q);
    }

    return $ret;
}


function _getDaysFollowupTimes($date)
{
    global $db;
    $ret = array();
    $q = "SELECT * FROM surgeryday WHERE _date='" . $date . "'";
    $ret['query'][] = $q;
    //return $q;
    //////////////////////////////////
    //  SET DEFAULT DATE AND TIMES ///
    //////////////////////////////////
    $firstFollowupStartTime = 480; //8:00

    $dayafter = strtotime($date . " +1 day");
    $dayafter = date("Y-m-d", $dayafter);

    $firstFollowupDate = $dayafter;
    $secondFollowupStartTime = 870; //2:30 or 14:30

    $weekafter = strtotime($date . " +1 week");
    $weekafter = date("Y-m-d", $weekafter);
    $secondFollowupDate = $weekafter;

    $surgeryStartTime = 480;

    $rows = $db->fetch_all_array($q);
    $ret['rows'] = $rows;
    foreach ($rows as $row) {
        if ($row['key'] == "firstFollowupStartTime") {
            $firstFollowupStartTime = $row['value'];
        }
        if ($row['key'] == "firstFollowupDate") {
            $firstFollowupDate = $row['value'];
        }
        if ($row['key'] == "secondFollowupStartTime") {
            $secondFollowupStartTime = $row['value'];
        }
        if ($row['key'] == "secondFollowupDate") {
            $secondFollowupDate = $row['value'];
        }
        if ($row['key'] == "surgeryStartTime") {
            $surgeryStartTime = $row['value'];
        }
    }


    $ret['firstFollowupStartTime'] = $firstFollowupStartTime;
    $ret['secondFollowupStartTime'] = $secondFollowupStartTime;
    $ret['firstFollowupDate'] = $firstFollowupDate;
    $ret['secondFollowupDate'] = $secondFollowupDate;
    $ret['surgeryStartTime'] = $surgeryStartTime;
    return $ret;
}


function changeTime($d)
{
    if (strpos($d, ':')) {
        $a = split($d, ':');
        $ret = $a[0] * 60;
        $ret += $a[1];
        return $ret;

    } else {

        $hours = floor($d / 60);
        $minutes = $d % 60;
        if ($minutes == 0) {
            $minutes = "00";
        } elseif ($minutes < 10) {
            $minutes = "0" . $minutes;
        }
        if ($hours > 12) {
            //$hours = $hours - 12;
        }
        return $hours . ":" . $minutes;
    }


}

function _getNextFollowupTimes($date)
{
    global $db;
    $ret = array();
    $q = "SELECT * FROM surgeries WHERE date='" . $date . "' AND `rescheduled`=''";
    $rows = $db->fetch_all_array($q);
    $usedSlots = count($rows);
    $ret['query'][] = $q;
    $ret['usedSlots'] = $usedSlots;
    /// figure out first and second followup days

    $q = "SELECT * FROM surgeryday WHERE _date='" . $date . "'";

    $ret['query'][] = $q;

    //////////////////////////////////
    //  SET DEFAULT DATE AND TIMES ///
    //////////////////////////////////
    $firstFollowupStartTime = 480; //8:00

    $dayafter = strtotime($date . " +1 day");
    $dayafter = date("Y-m-d", $dayafter);

    $firstFollowupDate = $dayafter;
    $secondFollowupStartTime = 870; //2:30 or 14:30

    $weekafter = strtotime($date . " +1 week");
    $weekafter = date("Y-m-d", $weekafter);
    $secondFollowupDate = $weekafter;


    $rows = $db->fetch_all_array($q);

    $ret['rows'] = $rows;

    foreach ($rows as $row) {
        if ($row['key'] == "firstFollowupStartTime") {
            $firstFollowupStartTime = $row['value'];
        }
        if ($row['key'] == "firstFollowupDate") {
            $firstFollowupDate = $row['value'];
        }
        if ($row['key'] == "secondFollowupStartTime") {
            $secondFollowupStartTime = $row['value'];
        }
        if ($row['key'] == "secondFollowupDate") {
            $secondFollowupDate = $row['value'];
        }
    }


    /// calculate next times from start time

    // 10 minute slots
    $timecrunch = $usedSlots * 10;

    $ret['timecrunch'] = $timecrunch;

    $ret['firstFollowupStartTime'] = $firstFollowupStartTime + $timecrunch;
    $ret['secondFollowupStartTime'] = $secondFollowupStartTime + $timecrunch;
    $ret['firstFollowupDate'] = $firstFollowupDate;
    $ret['secondFollowupDate'] = $secondFollowupDate;

    return $ret;

}


function _setSurgeryStart($day, $time)
{
    $db = $GLOBALS['db'];
    $q = "SELECT * FROM surgeryDay WHERE `_date`='" . $day . "' AND `key`='startTime'";
    $rows = $db->fetch_all_array($q);


    if (count($rows) == 0) {
        $q = "INSERT INTO surgeryDay (`_date`,`key`,`value`) VALUES ('" . $day . "','startTime','" . $time . "')";
    } else {
        //$q = "INSERT INTO surgeryDay (`_date`,`key`,`value`) VALUES ('".$day."','startTime','".$time."')";
        $q = "UPDATE surgeryDay SET `value`='" . $time . "' WHERE  `_date`='" . $day . "' AND `key`='startTime'";
    }

    //$q = "INSERT INTO surgeryDay (_date,key,value) VALUES ('".$day."','startTime','".$time."')";

    $id = $db->query_insert($q);
    $o['q1'] = $q;
    return $o;

}

function _getSurgeryStart($day, $time)
{
    $db = $GLOBALS['db'];
    $q = "SELECT * FROM surgeryDay WHERE `_date`='" . $day . "' AND `key`='startTime'";

    $rows = $db->fetch_all_array($q);
    return $rows[0]['value'];

}

function convertToUnixTime($date)
{
    $a = split('-', $date);
    $year = $a[0];
    $month = $a[1];
    $day = $a[2];
    //int mktime  ([ int $hour = date("H")  [, int $minute = date("i")  [, int $second = date("s")  [, int $month = date("n")  [, int $day = date("j")  [, int $year = date("Y")  [, int $is_dst = -1  ]]]]]]] )
    return mktime(0, 0, 0, $month, $day, $year);


}


function addFormData(VoFormData $p)
{
    //formsData
    $db = $GLOBALS['db'];
    $q = "INSERT INTO formsData (name,value,catagory) VALUES ('" . $p->value . "','" . $p->value . "','" . $p->key . "')";

    $id = $db->query_insert($q);

}

function removeFormData(VoFormData $p)
{
    $db = $GLOBALS['db'];
    $q = "DELETE FROM formsData WHERE name='" . $p->value . "' AND catagory='" . $p->key . "'";
    //(name,value,catagory) VALUES ('".$value."','".$value."','".$key."')";

    $id = $db->query($q);
}


function getFormInfo($catagory)
{
    $db = $GLOBALS['db'];
    if ($catagory == "") {
        $q = "SELECT * FROM `formsData` ORDER BY value";

    } else {
        $q = "SELECT * FROM `formsData` WHERE `catagory`='" . $catagory . " ORDER BY value'";
    }
    $rows = $db->fetch_all_array($q);
    $total = count($rows);
    if ($total < 1) {
        return false;
    }
    // return $rows;


    $dd = "";
    foreach ($rows as $s) {
        $form_info = new Form_Info();
        $form_info->eLabel = $s['name'];
        $form_info->value = $s['value'];
        $dd[$s['catagory']][] = $form_info;
    }

    /* Hacked way of returning forms, need to figure out a way to do it dynamically instead of hard coding */

    $forms = new DropDownFormInfo();
    $forms->IOL = $dd['IOL'];
    $forms->anaesthesia = $dd['anaesthesia'];
    $forms->medications = $dd['medications'];
    $forms->procedure = $dd['procedure'];
    $forms->specialneeds = $dd['specialneeds'];
    $forms->specialorders = $dd['specialorders'];
    $forms->comorbidoculardisorders = $dd['comorbidoculardisorders'];
    $forms->Doctors = $dd['Doctors'];
    $forms->gaspassers = $dd['gaspassers'];

    return $forms;


}


?>