<?php

function _getNotes($sid = "", $month = "", $year = "", $day = "")
{
    $data = "";
    if ($sid != "") {
        $data = getNotesBySid($sid);
    } elseif ($day != "") {
        $data = getNotesByDate($year, $month, $day);
    } elseif ($month != "" && $year != "") {
        $data = getNotesByMonth($year, $month);
    }
    $ret = array();
    if (!$data) {
        return "";
    }
    foreach ($data as $d) {
        $dd = split("-", $d['date']);

        $n = new Note();
        $n->creator = $d['creator'];
        $n->type = $d['type'];
        //$n->date =$d['date'];
        $n->day = $dd[2];
        $n->month = $dd[1];
        $n->date = $d['date'];
        $n->year = $dd[0];
        $n->sid = $d['sid'];
        $n->note = $d['note'];
        $n->id = $d['id'];
        $ret[] = $n;
    }
    return $ret;
}


function _addNote($n)
{
    $db = $GLOBALS['db'];
    $q = "INSERT INTO notes (date,note) VALUES ('" . $n->date . "','" . $n->note . "')";
    $id = $db->query_insert($q);
    return $id;

}

function _getnotesByDate($date)
{
    $db = $GLOBALS['db'];
    $q = "SELECT * FROM notes WHERE date='" . $date . "'";
    $rows = $db->fetch_all_array($q);
    $total = count($rows);
    if ($total < 1)
        return array();


    $a = array();
    foreach ($rows as $r) {
        $n = new Note();
        $n->date = $r['date'];
        $n->note = $r['note'];
        $n->id = $r['id'];
        $a[] = $n;
    }
    return $a;
}

function _deleteNote($i)
{
    $db = $GLOBALS['db'];
    $q = "DELETE FROM notes WHERE id='" . $i . "'";
    $db->query($q);
}

function getNotesBySid($sid)
{
    $db = $GLOBALS['db'];
    $q = "SELECT * FROM `notes` WHERE `sid`='" . $sid . "' ORDER BY creation_date";
    $rows = $db->fetch_all_array($q);
    $total = count($rows);
    if ($total < 1) {
        return array();
    }
    return $rows;
}


function getNotesByDate($year, $month, $day)
{
    $date = $year . "-" . $month . "-" . $day;
    $db = $GLOBALS['db'];
    $q = "SELECT * FROM notes WHERE date='" . $date . "' ORDER BY creation_date";
    $rows = $db->fetch_all_array($q);
    $total = count($rows);
    if ($total < 1) {
        return false;
    }
    return $rows;
}

function getNotesByMonth($year, $month)
{
    $db = $GLOBALS['db'];
    $clineo_db = $_GLOBALS['clineo_db'];
    $date_start = $year . "-" . $month . "-00";
    $date_end = $year . "-" . $month . "-" . date("t", mktime(0, 0, 0, $month, 1, $year));
    $q = "SELECT * FROM notes WHERE date BETWEEN '" . $date_start . "' AND date '" . $date_end . "' ORDER BY creation_date";
//	echo($q);

    $rows = $db->fetch_all_array($q);
    //print_r($rows);
    $total = count($rows);
    if ($total < 1) {
        return array();
    }
    return $rows;
}

function newNote(Note $note)
{
    /*
        public $type = "";
    public $date ="";
    public $sid ="";
    public $creator ="";
    public $note ="";
    public $id = "";
        [seconds] => 40
    [minutes] => 58
    [hours]   => 21
    [mday]    => 17
    [wday]    => 2
    [mon]     => 6
    [year]    => 2003
    [yday]    => 167
    [weekday] => Tuesday
    [month]   => June
    [0]       => 1055901520

    */
    $clineo_db = $GLOBALS['clineo_db'];
    $note->date = $note->year . "-" . $note->month . "-" . $note->day;
    //id 	creator 	type 	date 	note 	sid 	creation_date
    $q = "INSERT INTO notes (creator,date,note,sid) VALUES ('" . $note->creator . "','" . $note->date . "','" . $note->note . "','" . $note->sid . "')";
//	return $q;
    //echo($q);
    $db = $GLOBALS['db'];
    $id = $db->query_insert($q);
//	return "after query with $id";
    if ($id) {
        $note->id = $id;
        return $note;
    }
    return "";

}

function removeNote($id)
{
    $db = $GLOBALS['db'];
    $q = "DELETE FROM notes WHERE id='" . $id . "'";
    if ($db->query($q)) {
        return true;
    }
    return false;
}


?>