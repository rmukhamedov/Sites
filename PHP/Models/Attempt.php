<?php
/**
 * Created by PhpStorm.
 * User: marys_000
 * Date: 10/15/2015
 * Time: 8:40 AM
 */

namespace TestingCenter\Models;



class Attempt
{
    protected $id;
    protected $attemptID = "";
    protected $attemptAllowed = "";
    protected $workstationID = "";
    protected $startTime = "";
    protected $endTime = null;
    protected $reservationID ="";
    protected $studentID ="";
    protected $examID ="";

   /*function __construct($exam_id, $student_id, $workstation_id, $reservation_id)
    {

        $this->studentID = $student_id;
        $this->reservationID = $reservation_id;
        $this->examID = $exam_id;
        $this->workstationID = $workstation_id;

        $this->endTime = null;
    }*/

    function setStudentID($student_id)
    {
        $this->studentID = $student_id;
    }

    function setExamID($exam_id)
    {
        $this->examID = $exam_id;
    }

    function setWorkstationID($workstation_id)
    {
        $this->workstationID = $workstation_id;
    }

    function setReservationID($reservation_id)
    {
        $this->reservationID = $reservation_id;
    }

    function getAttemptID()
    {
        return $this->id;
    }

    /*function getAttemptAllowed()  //This is never passed to us
    {
        return $this->attemptAllowed;
    }*/

    function getWorkstationID()
    {
        return $this->workstationID;
    }

    function getStartTime()
    {
        return $this->startTime;
    }

    function getEndTime()
    {
        return $this->endTime;
    }

    function getReservationID()
    {
        return $this->reservationID;
    }

    function getStudentID()
    {
        return $this->studentID;
    }

    function getExamID()
    {
        return $this->examID;
    }


}

