<?php

function _updateSurgeryOrder($array)
{
    $db = $GLOBALS['db'];
    $a = array();
    $num = 1;
    $time = 480;
    $slength = 25;
    $date = "";
    $newarray = array();
    foreach ($array as $s) {

        $q = "Update surgeries SET surgerynum='" . $num . "',surgeryTime='" . $s->surgeryTime . "',surgeryLength='" . $s->surgeryLength . "' WHERE id='" . $s->sid . "'";
        $db->query_insert($q);
        $a[$num] = $q;


        $num++;
        //$date  = $s->dateString;

        $newarray[] = $s;
    }
    return $newarray;

}

function getsurgerybysid($sid)
{
    $db = $GLOBALS['db'];
    $clineo_db = $GLOBALS['clineo_db'];
    $q = "SELECT * FROM surgeries WHERE id='" . $sid . "' LIMIT 1";
    $rows = $db->query($q);


    if ($d_surgery = $db->fetch_array($rows)) {
        $surgery = new Surgery();
        $surgery->creator = $d_surgery['creator'];
        $surgery->creator_date = strtotime($d_surgery['creationdate']);
        $surgery->bookingCardId = $d_surgery['bookingCardId'];
        $date = convertToUnixTime($d_surgery['date']);
        $unixtime = convertToUnixTime($date);
        $surgery->date = $date; // $d_surgery['date'];
        $surgery->dateString = $d_surgery['date'];
        $surgery->pid = $d_surgery['patient_id'];
        $surgery->sid = $d_surgery['id'];
        $surgery->surgery_detail = $d_surgery['surgery_info'];
        $surgery->surgerynumber = $d_surgery['surgerynum'];
        $surgery->surgeryTime = $d_surgery['surgeryTime'];
        $surgery->surgeryLength = $d_surgery['surgeryLength'];
        $surgery->status = $d_surgery['rescheduled'];
        if ($surgery->status == "") {
            $surgery->status = "active";
        }
        $surgery->iol = $d_surgery['iol'];

        $surgery->eye = $d_surgery['eye'];

        $p = new Patient();
        $p = getpatientbypid($d_surgery['patient_id']);
        $surgery->patient = $p;
        $surgery->full_name = $p->full_name;

        $surgery->surgery_detail = getSurgeryInfo($sid);
        $q = "SELECT * FROM surgeries WHERE `patient_id`='" . $surgery->pid . "' AND `rescheduled`='' AND `id`!='" . $surgery->sid . "' ORDER BY surgerynum";
        //echo($q);
        $rows2 = $db->fetch_all_array($q);
        //print_r($rows2);
        if (count($rows2) > 0) {
            $r2 = $rows2[0];
            $firstdate = strtotime($d_surgery['date']);
            $seconddate = strtotime($r2['date']);
            if ($firstdate < $seconddate) {
                $surgery->firstSurgery = "true";
                $surgery->otherSurgery = $rows2[0]['id'];
                $surgery->otherSurgeryDate = $rows2[0]['date'];
            } else {
                $surgery->firstSurgery = "false";
                $surgery->otherSurgery = $rows2[0]['id'];
                $surgery->otherSurgeryDate = $rows2[0]['date'];
            }

            // get clineo times for other

            $o = getClineoTime($rows2[0]['dayAfterSurgeryId']);
            $surgery->otherdayAfterSurgeryTime = $o['time'];
            $surgery->otherdayAfterSurgeryDate = $o['date'];
            $o = getClineoTime($rows2[0]['weekAfterSurgeryId']);
            $surgery->otherweekAfterSurgeryTime = $o['time'];
            $surgery->otherweekAfterSurgeryDate = $o['date'];

        } else {

            $surgery->firstSurgery = "true";
            $surgery->otherSurgery = "";

        }

        $o = getClineoTime($d_surgery['dayAfterSurgeryId']);
        $surgery->dayAfterSurgeryId = $d_surgery['dayAfterSurgeryId'];
        $surgery->weekAfterSurgeryId = $d_surgery['weekAfterSurgeryId'];
        $surgery->dayAfterSurgeryTime = $o['time'];
        $surgery->dayAfterSurgeryDate = $o['date'];
        $o = getClineoTime($d_surgery['weekAfterSurgeryId']);
        $surgery->weekAfterSurgeryTime = $o['time'];
        $surgery->weekAfterSurgeryDate = $o['date'];
        return $surgery;
    }
    return false;
}


function _getSurgeriesByPid($pid, $active)
{
    $db = $GLOBALS['db'];
    $clineo_db = $_GLOBALS['clineo_db'];
    if ($active == true) {
        $q = "SELECT * FROM surgeries WHERE patient_id='" . $pid . "' AND rescheduled != 'rescheduled' AND rescheduled!='hold'";
    } else {
        $q = "SELECT * FROM surgeries WHERE patient_id='" . $pid . "'";
    }

    $rows = $db->fetch_all_array($q);

    $ret = array();

    foreach ($rows as $record) {


        $ret[] = getsurgerybysid($record["id"]);
    }
    return $ret;
    /*
    if($record = $db->fetch_array($rows))
    {
        return $record;
    }
    return false;
    */
}

function _putSurgeryOnhold($sid)
{
    $db = $GLOBALS['db'];
    $q = "UPDATE surgeries SET rescheduled='hold' WHERE id='" . $sid . "'";
    $db->query_insert($q);
    return "DONE";
}

function _getSurgeriesByDate($date)
{

    $db = $GLOBALS['db'];
    $clineo_db = $GLOBALS['clineo_db'];
    $q = "SELECT id,rescheduled FROM surgeries WHERE date='" . $date . "' AND rescheduled = '' ORDER BY surgerynum ASC";
    //$rows = $db->query($q);
    $ret = array();
    //$ret = $q;
    $ret['query'][] = $q;
    //return $ret;

    $rows = $db->fetch_all_array($q);
    $ret['surgeries'] = array();
    foreach ($rows as $record) {

        if (($record['rescheduled'] == "rescheduled") || ($record['rescheduled'] == "hold")) {
            next;
        }
        $ret['surgeries'][] = getsurgerybysid($record['id']);


        //$ret[]= $s;
    }
    return $ret;

}

function getSurgeryByDate($year, $month, $day)
{

    //date
    $date = $year . "-" . $month . "-" . $day;
    $db = $GLOBALS['db'];
    $clineo_db = $GLOBALS['clineo_db'];
    $q = "SELECT id,rescheduled FROM surgeries WHERE date='" . $date . "' AND rescheduled = '' ORDER BY surgerynum";
    //$rows = $db->query($q);

    $ret = "";


    $rows = $db->fetch_all_array($q);

    foreach ($rows as $record) {

        if (($record['rescheduled'] == "rescheduled") || ($record['rescheduled'] == "hold")) {
            next;
        }
        $ret[] = getsurgerybysid($record['id']);


        //$ret[]= $s;
    }
    return $ret;

}

function getSurgeriesByMonth($year, $month)
{
    $db = $GLOBALS['db'];
    $clineo_db = $_GLOBALS['clineo_db'];

    $date_start = $year . "-" . $month . "-01";
    //$date = date("l", mktime(0, 0, 0, $month, 1, $year));
    $date_end = $year . "-" . $month . "-" . date("t", mktime(0, 0, 0, $month, 1, $year));

    $q = "SELECT * FROM surgeries WHERE date BETWEEN '" . $date_start . "' AND date '" . $date_end . "' AND rescheduled = '' ORDER BY surgerynum";


    $ret = "";


    $rows = $db->fetch_all_array($q);

    foreach ($rows as $record) {

        if (($record['rescheduled'] == "rescheduled") || ($record['rescheduled'] == "hold")) {
            next;
        }
        $ret[$record['date']][] = getsurgerybysid($record['id']);

    }
    $arrr = array();
    if ($ret != "") {
        foreach ($ret as $f) {
            $arrr[] = $f;
        }


        $d_month = new Month();
        $d_month->month = $month;
        $d_month->year = $year;

        if ($arrr) {


            foreach ($arrr as $d_day) {
                $day = new day();
                foreach ($d_day as $d_surgery) {
                    $surgery = new surgery();
                    $surgery = $d_surgery;

                    $mynotes = getNotesBySid($d_surgery->id);

                    $notes = array();
                    if ($mynotes) {
                        foreach ($mynotes as $n) {
                            $note = new Note();
                            $note->creator = $n['creator'];
                            $note->type = $n['type'];
                            $note->date = $n['date'];
                            $note->sid = $n['sid'];
                            $note->note = $n['note'];
                            $note->id = $n['id'];

                        }
                        $surgery->notes = $notes; //($d_surgery['id']);
                    } else {
                        $surgery->notes = array();
                    }

                    $day->date = $surgery->date;
                    $day->surgeries[] = $surgery;

                }
                $d_month->days[] = $day;
            }
        }
        $d_month->noteGroup = getNotesByMonth($year, $month);


        return $d_month;


    }
    return "";

}

function getGasPasser($date)
{
    $db = $GLOBALS['db'];


    $q = "SELECT * FROM gaspasser WHERE date = '" . $date . "'";

    $row = $db->fetch_array($q);
    if ($row) {
        return $row['name'];
    }
    return "";
}

function getSurgeriesByPatient($pid)
{
    $db = $GLOBALS['db'];
    $clineo_db = $GLOBALS['clineo_db'];
    $q = "SELECT * FROM surgeries WHERE patient_id = '" . $pid . "' ORDER BY surgerynum";
    $rows = $db->fetch_all_array($q);

    $total = count($rows);
    if ($total < 1) {
        return false;
    }
    return $rows;

}

function getHoldSurgeries()
{
    //date
    $date = $year . "-" . $month . "-" . $day;
    $db = $GLOBALS['db'];
    $clineo_db = $GLOBALS['clineo_db'];
    $q = "SELECT * FROM surgeries WHERE rescheduled='hold'";
    $rows = $db->query_insert($q);

    $ret = "";


    $rows = $db->fetch_all_array($q);


    $dd = array();

    foreach ($rows as $record) {

        $s = new Surgery();

        $s = getsurgerybysid($record['id']);
        $dd[] = $s;
    }
    if (count($dd) > 0) {
        return $dd;

    }
    return "";
}

function getSurgeryInfo($sid)
{
    $db = $GLOBALS['db'];
    $q = "SELECT * FROM surgery_info WHERE surgery_id='" . $sid . "'";
    $rows = $db->fetch_all_array($q);

    $a = array();
    foreach ($rows as $r) {
        $a[$r['catagory']][] = $r['value'];
    }
    if (count($a) < 1) {
        return null;
    }
    return $a;

}


function _rescheduleSurgery($data)
{
    //return $data;
    $db = $GLOBALS['db'];
    $oldsid = $data['sid'];
    $today = date("Y-m-d H:m:s");

    $q = "SELECT * FROM surgeries WHERE  id='" . $oldsid . "'";
    $ret['query'][] = $q;
    if ($rows = $db->fetch_all_array($q)) {
        $ret['query'][] = "Error with query";
    }

    $surgery = getsurgerybysid($rows[0]['id']);
//	$surgery = $rows[0] as Surgery;
    //$dw = new DateWrapper($surgery->date);
    //$surgery->date = $dw;
    $dayafter = 0;
    $weekafter = 0;

    $ret['data'] = $data;

    $ret['workingdate1'] = $surgery->date;
    $ret['workingdate2'] = $surgery->dateString;
    $ret['dayAfterSurgeryTime'] = $surgery->dayAfterSurgeryTime;
    $ret['dayAfterSurgeryId'] = $surgery->dayAfterSurgeryId;
    $ret['weekAfterSurgeryTime'] = $surgery->weekAfterSurgeryTime;
    $ret['weekAfterSurgeryId'] = $surgery->weekAfterSurgeryId;
    $surgery->datestring = $data['dateString'];
    $surgery->date = $data['date'];

    $firstPoId = 0;
    $secondPoId = 0;
    if ($surgery->dayAfterSurgeryId == 0) {

        $d = getNextPostOpTime($data['dateString'], "true");
        $dayafter = createClineoAppointment($surgery->pid, $d['date'], $d['time']);
        $ret['dayAfter'] = $dayafter;
        $firstPoId = $dayafter['insert_id'];
    } else {

        $d = getNextPostOpTime($data['dateString'], "true");
        $dayafter = updateClineoAppointment($d['date'], $d['time'], $surgery->dayAfterSurgeryId);
        //$dayafter = $surgery->dayAfterSurgeryId;
        $ret['dayAfter'] = $dayafter;
        $firstPoId = $dayafter['insert_id'];
    }

    // book second post op which is a week after surgery date
    if ($surgery->weekAfterSurgeryId == 0) {

        $d = getNextPostOpTime($data['dateString'], "false");
        $weekafter = createClineoAppointment($surgery->pid, $d['date'], $d['time']);
        $ret['weekAfter'] = $weekafter;
        $secondPoId = $weekAfter['insert_id'];

    } else {

        $d = getNextPostOpTime($data['dateString'], "false");
        $weekafter = updateClineoAppointment($d['date'], $d['time'], $surgery->weekAfterSurgeryId);
        $ret['weekAfter'] = $weekafter;
        $secondPoId = $weekafter['insert_id'];
    }


    $q = "INSERT INTO surgeries (date,createdDateTime,creator,surgerynum,patient_id,eye,dayAfterSurgeryId,weekAfterSurgeryId) VALUES ('" . $data['dateString'] . "','" . $today . "','" . $surgery->creator . "','" . $surgery->surgerynumber . "','" . $surgery->pid . "','" . $surgery->eye . "','" . $firstPoId . "','" . $secondPoId . "')";

    $ret['query'][] = $q;

    if ($insert_id = $db->query_insert($q)) {
        $s_info = $surgery->surgery_detail;
        if (count($s_info) > 0) {
            foreach ($s_info as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $q = "INSERT INTO surgery_info (surgery_id,catagory,name,value) VALUES ('" . $insert_id . "','" . $key . "','" . $v . "','" . $v . "')";
                        $ret['query'][] = $q;
                        if (!$db->query_insert($q)) {
                            $ret['query'][] = "ERROR $q";
                        }
                    }
                } else {
                    $q = "INSERT INTO surgery_info (surgery_id,catagory,name,value) VALUES ('" . $insert_id . "','" . $key . "','" . $value . "','" . $value . "')";
                    $ret['query'][] = $q;
                    if (!$db->query_insert($q)) {
                        $ret['query'][] = "ERROR $q";
                    }
                }
            }
        }
        $surgery->sid = $insert_id;

    }


    $q = "UPDATE surgeries SET rescheduled='rescheduled' WHERE id='" . $oldsid . "'";
    $ret['query'][] = $q;
    if (!$db->query($q)) {
        $ret['query'][] = "ERROR $q";
    }

    return $ret;
}


/*
function _rescheduleSurgery( $surgery)
{
	$db = $GLOBALS['db'];
	$oldsid = $surgery->sid;
	$today = date ("Y-m-d H:m:s");
	
	//$q = "SELECT * FROM surgeries WHERE  id='".$oldsid."'";	
	//$rows = $db->fetch_all_array($q);
	//$surgery2 = $rows[0];
	//$dw = new DateWrapper($surgery->date);
	//$surgery->date = $dw;
	$dayafter = 0;
	$weekafter = 0;
	$ret['workingdate1'] = $surgery->date;
	$ret['workingdate2'] = $surgery->dateString;
	$ret['dayAfterSurgeryTime'] = $surgery->dayAfterSurgeryTime ;
	$ret['dayAfterSurgeryId'] =  $surgery->dayAfterSurgeryId;
	$ret['weekAfterSurgeryTime'] =  $surgery->weekAfterSurgeryTime;
	$ret['weekAfterSurgeryId'] = $surgery->weekAfterSurgeryId;
	
	
	if($surgery->dayAfterSurgeryTime!= "")
	{
		if($surgery->dayAfterSurgeryId==0)
		{
			$dayafter = strtotime(  $surgery->dateString . " +1 day");
			$ret['dayafter1'] = $dayafter;
			
			$dayafter = date("Y-m-d",$dayafter);
			$ret['dayafter2'] = $dayafter;
		
			$dayafter= createClineoAppointment($surgery->pid,$dayafter,$surgery->dayAfterSurgeryTime);
			$ret['dayafterId'] = $dayafter;
		}else{
			$dayafter = strtotime(  $surgery->dateString . " +1 day");
			$ret['dayafter1'] = $dayafter;
			
			$dayafter = date("Y-m-d",$dayafter);
			$ret['dayafter2'] = $dayafter;
			updateClineoAppointment($dayafter,$surgery->dayAfterSurgeryTime,$surgery->dayAfterSurgeryId);
			$dayafter = $surgery->dayAfterSurgeryId;
			
		}
	}else{
		$ret['dayafter'] = "Is Empty";
		$dayafter = 0;
	
	}
	
	if($surgery->weekAfterSurgeryTime!= "")
	{
		// book second post op which is a week after surgery date
		if($surgery->weekAfterSurgeryId==0)
		{
			$weekafter = strtotime(  $surgery->dateString . " +1 week");
			$ret['weekafter1'] = $weekafter;
			
			$weekafter = date("Y-m-d",$weekafter);
			$ret['weekafter2'] = $weekafter;
		
			$weekafter= createClineoAppointment($surgery->pid,$weekafter,$surgery->weekAfterSurgeryTime);
			$ret['dayafterId'] = $weekafter;
		}else{
			$weekafter = strtotime(  $surgery->dateString . " +1 week");
			$ret['weekafter1'] = $weekafter;
			
			$weekafter = date("Y-m-d",$weekafter);
			$ret['weekafter2'] = $weekafter;
			updateClineoAppointment($weekafter,$surgery->weekAfterSurgeryTime,$surgery->weekAfterSurgeryId);
			$weekafter = $surgery->weekAfterSurgeryId;
			
		}
	}else{
		$ret['weekafter'] = "Is Empty";
		$weekafter = 0;
	}

	
	
		
		
		$q = "INSERT INTO surgeries (date,createdDateTime,creator,surgerynum,patient_id,eye,dayAfterSurgeryId,weekAfterSurgeryId) VALUES ('". $surgery->dateString."','".$today."','".$surgery->creator."','".$surgery->surgerynumber."','".$surgery->pid."','".$surgery->eye."','".$dayafter."','".$weekafter."')";
		
		//echo($q);
		$ret['queryinsert'] = $q;
		
		if($insert_id=$db->query_insert($q))
		{
			$s_info = $surgery->surgery_detail;
			if(count($s_info)>0)
			{
				foreach($s_info as $key=>$value)
				{
					if(is_array($value))
					{
						foreach($value as $v)
						{
							$q = "INSERT INTO surgery_info (surgery_id,catagory,name,value) VALUES ('".$insert_id."','".$key."','".$v."','".$v."')";						
							$db->query_insert($q);
						}
					}else{
						$q = "INSERT INTO surgery_info (surgery_id,catagory,name,value) VALUES ('".$insert_id."','".$key."','".$value."','".$value."')";
						$db->query_insert($q);
					}
				}
			}
		$surgery->sid = $insert_id;
		
		}
		
		
		$q = "UPDATE surgeries SET rescheduled='rescheduled' WHERE id='".$oldsid."'";	
	$db->query_insert($q);
		
	
		return $ret;
		//return $surgery;
			

}
*/

function getLastsurgery($date, $pid)
{
    $db = $GLOBALS['db'];
    $q = "SELECT * FROM surgeries WHERE patient_id='" . $pid . "' AND date < '" . $date . "'";
    //$rows = $db->query($q);
    $rows = $db->fetch_all_array($q);
    $lastSid = "";
    foreach ($rows as $record) {
        $lastSid = $record['id'];
    }
    return $lastSid;
}

function _deleteSurgery($sid)
{
    $db = $GLOBALS['db'];

    $clineo_db = $GLOBALS['clineo_db'];
    $q = "SELECT * FROM surgeries WHERE id='" . $sid . "' LIMIT 1";
    $rows = $db->query($q);


    $d_surgery = $db->fetch_array($rows);

    delete_clineo_appt($d_surgery['dayAfterSurgeryId']);
    delete_clineo_appt($d_surgery['weekAfterSurgeryId']);

    $q = "DELETE FROM surgeries WHERE id='" . $sid . "'";
    //echo($q);
    $rows = $db->query($q);
    $q = "DELETE FROM surgery_info WHERE surgery_id='" . $sid . "'";
    ///echo($q);
    $rows = $db->query($q);
}

function _cancelSurgery($sid)
{
    $db = $GLOBALS['db'];

    $clineo_db = $GLOBALS['clineo_db'];
    $q = "SELECT * FROM surgeries WHERE id='" . $sid . "' LIMIT 1";
    $rows = $db->query($q);


    $d_surgery = $db->fetch_array($rows);
    delete_clineo_appt($d_surgery['dayAfterSurgeryId']);
    delete_clineo_appt($d_surgery['weekAfterSurgeryId']);

    $q = "UPDATE surgeries SET rescheduled='cancelled' WHERE id='" . $sid . "'";
    $rows = $db->query($q);

}


function _saveSurgeryInfo($data)
{
    $db = $GLOBALS['db'];
    $s = $data;
    // first check to see if there is a surgery already on this day


    // check to see if there is a sid
    $isnew = true;
    $sid = 0;

    if ($s->sid == 0 || $s->sid == null) {
        $isnew = true;
    } else {
        $isnew = false;
    }
    $today = date("Y-m-d H:m:s");

    if ($isnew == true) {

        $ret['type'] = " NEW SURGERY";


        $firstFollowUpId = 0;
        $secondFollowUpId = 0;

        $ret['firstfollowup'] = createPostOpTime($s->pid, $s->dateString, "true");
        $firstfollowUpId = $ret['firstfollowup']['createClineoAppointment']['insert_id'];

        $ret['secondfollowup'] = createPostOpTime($s->pid, $s->dateString, "false");
        $secondFollowUpId = $ret['secondfollowup']['createClineoAppointment']['insert_id'];


        // create clineo surgery slot
        ////////////////////////////////////
        ////// TO DO ///////////////////////
        ////////////////////////////////////

        /// fill in surgeries database
        $creator = $s->creator;
        $surgerynumber = $s->surgerynumber;
        if ($creator == null) {
            $creator = 0;
        }
        if ($surgerynumber == null) {
            $surgerynumber = 0;
        }

        $q = "INSERT INTO surgeries (`date`,`createdDateTime`,`creator`,`surgerynum`,`patient_id`,`eye`,`dayAfterSurgeryId`,`weekAfterSurgeryId`) VALUES ('" . $s->dateString . "','" . $today . "'," . $creator . "," . $surgerynumber . ",'" . $s->pid . "','" . $s->eye . "','" . $firstfollowUpId . "','" . $secondFollowUpId . "')";

        //echo($q);
        $ret['query'][] = $q;
        $fluffyid = 0;
        if ($insert_id = $db->query_insert($q)) {
            $fluffyid = $insert_id;
            $ret['Inserted'] = "INSERTED A OK YO !";
            $s_info = $s->surgery_detail;
            if (count($s_info) > 0) {
                foreach ($s_info as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $v) {
                            if ($v != "") {
                                $q = "INSERT INTO `surgery_info` (`surgery_id`,`catagory`,`name`,`value`) VALUES ('" . $insert_id . "','" . $key . "','" . $v . "','" . $v . "')";
                                $ret['query'][] = $q;
                                $db->query_insert($q);
                            } else {
                                $ret['query'][] = "OPPS1 $key v $v is empty";
                            }
                        }
                    } else {
                        if ($v != "") {
                            $q = "INSERT INTO `surgery_info` (`surgery_id`,`catagory`,`name`,`value`) VALUES ('" . $insert_id . "','" . $key . "','" . $value . "','" . $value . "')";
                            $ret['query'][] = $q;
                            $db->query_insert($q);
                        } else {
                            $ret['query'][] = "OPPS2 $key v $v is empty";
                        }
                    }
                }
            }

            $s->sid = $insert_id;
            //	$ret['surgery'] =getsurgerybysid($insert_id);
            $ret['surgery'] = $s; //getsurgerybysid($insert_id);
            return $ret;
        } else {
            $ret['fucked'] = $db->error;
            return $ret;
        }
    } else {

        $ret['type'] = "UPDATING SURGERY";

        $q = "UPDATE `surgeries` SET  surgerynum='" . $s->surgerynumber . "',eye='" . $s->eye . "',surgeryTime='" . $s->surgeryTime . "',surgeryLength='" . $s->surgeryLength . "' WHERE id='" . $s->sid . "'";
        $ret['updatequery'] = $q;
        $db->query_insert($q);


        //$q = "DELETE FROM `surgery_info` WHERE 'surgery_id'='".$s->sid."'";
        $q = "DELETE FROM `surgery_info` WHERE surgery_id=" . $s->sid;
        $ret['query'][] = $q;
        if (!$db->query($q)) {
            $ret['delete'] = "ERROR  $q";

        } else {
            $ret['delete'] = $q;
        }
        $s_info = $s->surgery_detail;


        if (count($s_info) > 0) {
            foreach ($s_info as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        if ($v != "") {
                            $q = "INSERT INTO `surgery_info` (`surgery_id`,`catagory`,`name`,`value`) VALUES (" . $s->sid . ",'" . $key . "','" . $v . "','" . $v . "')";
                            $ret['query'][] = $q;

                            if (!($db->query_insert($q))) {
                                //return "ERROR INSERTING";
                                $ret['query'][] = "FAILED";
                            } else {
                                $ret['query'][] = $q;
                            }
                        } else {
                            $ret['query'][] = "OPPS3 $key v $v is empty";
                        }
                    }
                } else {
                    if ($value != "") {
                        $q = "INSERT INTO `surgery_info` (`surgery_id`,`catagory`,`name`,`value`) VALUES (" . $s->sid . ",'" . $key . "','" . $value . "','" . $value . "')";
                        $ret['query'][] = $q;
                        //$ret['query3'][] = $q;
                        if (!$db->query_insert($q)) {
                            $ret['query'][] = "FAILED";
                        } else {
                            $ret['query'][] = $q;
                        }
                    } else {
                        $ret['query'][] = "OPPS4 $key v $v is empty";
                    }
                }
            }
            $ret['zdone'] = "YES";
        }


        //$ret['surgery'] = $s;
        $ret['surgery'] = $s; //getsurgerybysid($insert_id);


        return $ret;
    }


}


?>
