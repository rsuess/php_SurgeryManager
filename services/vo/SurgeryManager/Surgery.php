<?
class Surgery
{

    public $sid; /// surgery id
    public $pid; //// patient id
    public $patient; /// class patient
    public $date; /// date of surgery
    public $creator; /// id of employee that created
    public $creator_date; /// day of creation
    public $surgerynumber; /// rank of surgery from 1-15
    public $surgery_detail; // array of CLASS Form_Info in class_form_info.php
    public $which_surgery; /// 1 for first surgery, 2 for second
    //public $second_surgery_booked; // true or false
    public $firstSurgery; // true if there is no surgery before this date
    public $otherSurgery;
    public $otherSurgeryDate;
    public $full_name; /// added because ello is a woose
    var $_explicitType = "SurgeryManager.Surgery";
    public $notes; // array of Note class
    public $eye;
    public $previousSurgery;

    public $iol;
    public $dayAfterSurgeryTime;
    public $weekAfterSurgeryTime;

    public $otherdayAfterSurgeryTime;
    public $otherweekAfterSurgeryTime;

    public $dayAfterSurgeryDate;
    public $weekAfterSurgeryDate;

    public $otherdayAfterSurgeryDate;
    public $otherweekAfterSurgeryDate;

    public $dateString;


    public $surgeryTime;
    public $surgeryLength;
}

?>