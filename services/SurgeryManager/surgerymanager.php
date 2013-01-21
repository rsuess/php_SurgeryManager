<?php

/// Include the structs
require_once("../vo/SurgeryManager/Day.php");
require_once("../vo/SurgeryManager/DropDownFormInfo.php");
require_once("../vo/SurgeryManager/VoFormData.php");
require_once("../vo/SurgeryManager/Month.php");
require_once("../vo/SurgeryManager/Note.php");
require_once("../vo/SurgeryManager/Patient.php");
require_once("../vo/SurgeryManager/Pdf.php");
require_once("../vo/SurgeryManager/Surgery.php");
require_once("../SurgeryManager/includes/functions.generic.php");

// I'm using a separate config file. so pull in those values 
require_once("../SurgeryManager/includes/config.inc.php");

require_once("../SurgeryManager/includes/functions.admin.php");
require_once("../SurgeryManager/includes/functions.clineo.php");
require_once("../SurgeryManager/includes/functions.fax.php");
require_once("../SurgeryManager/includes/functions.generic.php");
require_once("../SurgeryManager/includes/functions.note.php");
require_once("../SurgeryManager/includes/functions.patient.php");
require_once("../SurgeryManager/includes/functions.pdf.php");
require_once("../SurgeryManager/includes/functions.print.php");
require_once("../SurgeryManager/includes/functions.surgery.php");
require_once("../SurgeryManager/includes/functions.clineo.php");
//$json = new Services_JSON();


class surgerymanager
{


    function surgerymanager()
    {

        $this->methodTable = array
        (
            "newSurgery" => array
            (
                "access" => "remote",
                "description" => "Inserts new surgery into database",
                "arguments" => array(
                    "creator" => array(
                        "type" => "b")
                )
            ),
            "getSurgeriesByMonth" => array
            (
                "access" => "remote",
                "description" => "Returns construct of surgeries within month"
            ),
            "getSurgeryBySid" => array
            (
                "access" => "remote",
                "description" => "Returns a surgery located by surgery ID"
            ),

        );

    }

    /**
     * This is the main Surgery Manager Data processing unit
     * @returns returns NOTHING
     */
    public function __construct()
    {

    }

    /**
     * Returns a construct of a single surgery by Surgery ID number
     * @returns returns a surgery class
     */
    function getSurgeryBySid($sid)
    {
        $surgery = getsurgerybysid($sid);

        return $surgery;
    }

    /**
     * Adds new surgery to database
     * @returns surgery ID
     */

    function newSurgeryCreation(Surgery $surgery)
    {
        if ($surgery->date)
            return newSurgery($surgery);
        else
            return "Error with structure";
    }


    /**
     *    Searches database for patient by partial name
     * @returns array of Patient CLASSes
     */
    function searchPatientByPartialName($fname, $lname)
    {

        if ($patients = getPatientByName($fname, $lname)) {
            return $patients;
        }
        return array(); // return blank array because of generic error

    }


    /**
     * Returns construct of surgeries within month
     * @returns An array of day Classes that contain surgery classes
     */
    function getSurgeriesByMonth($month, $year)
    {
        if ($month == "" || $year == "") {
            return " MONTH OR YEAR EMPTY";
        }
        $data = getSurgeriesByMonth($year, $month);
        return $data;

    }

    /**
     * Returns construct of surgeries within a day
     * @returns An array of day Classes that contain surgery classes
     */

    function getSurgeriesByDay($year, $month, $day)
    {
        //return $date;
        $data = getSurgeryByDate($year, $month, $day);
        return $data;
    }

    function getActiveSurgeriesByPid($pid)
    {
        return _getSurgeriesByPid($pid, true);
    }

    function getSurgeriesByPid($pid)
    {
        return _getSurgeriesByPid($pid, false);

    }


    /**
     * Gets PATIENT INFORMATION BY PID
     * @returns A Patient Class filled with goodies
     */
    function getPatientByPid($pid)
    {
        if ($p = getpatientbypid($pid)) {
            return $p;
        }
        return "";

    }

    /**
     * Returns form information in a Form_Catagory class containing an array of Form_Info Classes
     * @returns An array of Form_Catagory Classes that contain Fomr_INfo classes
     */

    function getFormDropDownData($catagory = "")
    {
        return getFormInfo($catagory);
    }

///	function deleteNote($id)
//	{
//		if(removeNote($id))

//		{
//			return $id;
//		}else{
//			return "error";
//		}
//	}

//   function getNotes($sid="",$month="",$year="",$day="")
//	{
//	   return _getNotes($sid,$month,$year,$day);
//   }
    function setNote($n) //$sid="",$month="",$year="",$day="",$notestuff="",$creator="")
    {
        return _setNote($n);
    }

    /*
        function getNextAvailableAppointmentTime($month,$year,$day)
        {
            $date = $year."-".$month."-".$day;
            $time = get_next_available_time($date);
            return $time;

        }
      */

    function AddFormData($key, $value)
    {
        $p = new VoFormData();
        $p->key = $key;
        $p->value = $value;
        addFormData($p);
    }

    function RemoveFormData($key, $value)
    {
        $p = new VoFormData();
        $p->key = $key;
        $p->value = $value;
        removeFormData($p);
    }

    function GetHoldSurgeries()
    {
        return getHoldSurgeries();
    }

    function RescheduleSurgery($s)
    {
        return _rescheduleSurgery($s);
    }

    function PutSurgeryOnhold($sid)
    {
        return _putSurgeryOnhold($sid);
    }

    function UpdateSurgery(Surgery $s)
    {
        return _updateSurgery($s);
    }

    function UpdateSurgeryOrder($array)
    {
        return _updateSurgeryOrder($array);
    }


    function createClineoAppointment($pid, $date, $time)
    {
        createClineoAppointment($pid, $date, $time);
    }


    //// PRINTING

    function PrintInstructionSheet($sid)
    {
        return _pInstructions($sid);
    }

    function PrintRxForm($sid)
    {
        return _pRx($sid);
    }

    function PrintCheckSheet($sid)
    {
        _pCheckSheet($sid);
    }

    function PrintRequestForPostPOp($sid)
    {
        _pPostOp($sid);
    }


    function deleteSurgery($sid)
    {
        _deleteSurgery($sid);
    }

    function cancelSurgery($sid)
    {
        _cancelSurgery($sid);
    }


    function fixClineoAppts()
    {
        _fixClineoAppts();
    }

    function createBookingCard($surgery)
    {
        return _createBookingCard($surgery);
    }

    function createSurgeryFaxCover($date)
    {
        return _createSurgeryFaxCover($date);
    }

    function getPdfs($date)
    {
        //return "ASFDSFHGFRGTHYGFRGT";
        return _getPdfs2($date);
    }

    function getSurgeriesByDate($date)
    {
        return _getSurgeriesByDate($date);
    }

    function deletePDF($url)
    {
        return _deletePDF($url);
    }

    function addNote($n)
    {
        return _addNote($n);
    }

    function getNotes($d)
    {
        return _getnotesByDate($d);
    }

    function deleteNote($i)
    {
        _deleteNote($i);
    }

    function setSurgeryStart($date, $time)
    {
        return _setSurgeryStart($date, $time);
    }

    function getSurgeryStart($date)
    {
        return _getSurgeryStart($date, $time);
    }

    function savePatientInfo($data)
    {
        return _savePatientInfo($data);
    }

    function saveSurgeryInfo($data)
    {
        return _saveSurgeryInfo($data);
    }

    function getDayDetails($date)
    {
        return _getDayDetails($date);
    }

    function setDayDetails($o)
    {
        return _setDayDetails($o);
    }

    function saveTestingData($data)
    {
        return _saveTestingData($data);
    }

    function getTestingData($pid)
    {

        return _getTestingData($pid);
    }

    function getNextFollowupTimes($date)
    {
        return _getNextFollowupTimes($date);
    }
}


?>