<?php
/**
 * Created by PhpStorm.
 * User: marys_000
 * Date: 10/15/2015
 * Time: 8:24 AM
 */

namespace TestingCenter\Controllers;

use \TestingCenter\Http;
use TestingCenter\Models\Exam;
use TestingCenter\Models\Token;
use TestingCenter\Models\Attempt;
use TestingCenter\Utilities\Cast;
use TestingCenter\Utilities\DatabaseConnection;

class AttemptsController
{


    //protected $request = array();
    protected $options = array(Http\Methods::GET, Http\Methods::POST, Http\Methods::PUT, Http\Methods::DELETE, Http\Methods::OPTIONS);

    public function get($id)
    {

        $id = intval(current($id));
        $dbh  = DatabaseConnection::getInstance();

        if ($id == 0)
        {
            //all
            $attempts = array();
            $get_info = $dbh->prepare("SELECT * FROM Attempt");
            $get_info->execute();
            $temp = $get_info->fetchObject('TestingCenter\Models\Attempt');
            while($temp){
                array_push($attempts,$temp);
                $temp = $get_info->fetchObject('TestingCenter\Models\Attempt');
            }
            return $attempts;
            //One
        }else if($id > 0){
            $get_info = $dbh->prepare("SELECT * FROM Attempt WHERE Attempt_id = $id");
            $get_info->execute();
            $temp = $get_info->fetchObject('TestingCenter\Models\Attempt');
            if ($temp == false) {
                http_response_code(Http\StatusCodes::BAD_REQUEST);
                exit("Invalid Input");
            }
            return $temp;
        }
    }

    //$workstation = new WorkstationsController();    //call the workstation controller to get the workstation id function
    //$workstation->get(null);

    //Once we have this, what do we do with it?!

    // $exam = new ExamsController();  //call the Exams Controller to get the Exam id function
    //$exam->get(null);
 
    public function put($id)
    {
        $id = intval(current($id));
        $dbh  = DatabaseConnection::getInstance();
        $endTime = $_SERVER['REQUEST_TIME'];

        $statement = $dbh->prepare("UPDATE Attempt SET dateTimeEnd = :endTime WHERE Attempt_id = $id");
        $statement->execute(array(':endTime' => $endTime));

        if($id > 0) {
            return "Success at $endTime";
        }else{
            return "fail!";
        }


    }

    public function post($uri)// instead of $studentID, $examID, $workstationID, $reservationID)
    {
        //Database Try/Catch
        //$id = intval(current($id));
        $dbh  = DatabaseConnection::getInstance();
        $input = (object) json_decode(file_get_contents('php://input'));

        $input = Cast::cast("\\TestingCenter\\Models\\Attempt", $input);

        if (is_null($input)) {
            http_response_code(Http\StatusCodes::BAD_REQUEST);
            exit("No data to post.");
        }

        //$statement = "SELECT count(*) WHERE Exam_id = $examID AND student_id = $studentID GROUP BY Attempt_num";           //user input
        $statement = "SELECT * FROM Attempt WHERE Exam_id = $input->exam_id AND student_id = $input->student_id;";    //JSON object
        $stmt = $dbh->prepare($statement);
        $stmt->execute();

        $attemptNum = $stmt->rowCount(); //fetch the returned value
        $attemptAllowed = 3;    //Placeholder - call the exam controller to get the attempts allowed here!

        if($attemptNum < $attemptAllowed)
        {
            $endTime = null;    //New Attempt will not have an end time
            //$startTime = $_SERVER['REQUEST_TIME'];  //this gives us 0s??? Maybe we should use dual instead?
            $statement = $dbh->prepare("SELECT SYSDATE() FROM DUAL;");
            $statement->execute();




            /* INSERTING DATA*/
            $statement = $dbh->prepare("INSERT INTO `Attempt` (student_id, Exam_id, dateTimeStart, dateTimeEnd, Workstation_id, Reservation_id)" .
                " VALUES (:student_id, :Exam_id, :dateTimeStart, :dateTimeEnd, :Workstation_id, :Reservation_id);");

            //$data = array("student_id"=>$studentID, "Attempt_num"=>$attemptNum, "Exam_id"=>$examID, "dateTimeStart"=>$startTime,  //user input
            //    "dateTimeEnd"=>$endTime, "Workstation_id"=>$workstationID, "Reservation_id"=>$reservationID);
            $data = array("student_id"=>$input->student_id, "Exam_id"=>$input->exam_id, "dateTimeStart"=>$startTime,    //JSON object
                  "dateTimeEnd"=>$endTime, "Workstation_id"=>$input->workstation_id, "Reservation_id"=>$input->reservation_id);
            $statement->execute($data);

            //$temp = $statement->fetchObject('TestingCenter\Model\Attempt');

            return "success with Attempt Number: $attemptNum and Attempt Allowed: $attemptAllowed";

        }else if($attemptNum > $attemptAllowed){
            exit("Attempt Number: $attemptNum and Attempt Allowed: $attemptAllowed - No more attempts allowed!");
        }
        //return $input; DETECT THIS!@!!1!
       return "Nothing is working!";
    }

    public function delete($id)
    {
        $id = intval($id);
        $dbh  = DatabaseConnection::getInstance();
        /**
         * This is a sample of checking the user's permissions before allowing the behavior.
         */
        /*$role = Token::getRoleFromToken();
        if ($role != Token::ROLE_FACULTY) {
            http_response_code(Http\StatusCodes::UNAUTHORIZED);
            exit("Non-Faculty members, are not allowed to delete attempts.");
        } else {*/
            $stmt = "DELETE FROM Attempt WHERE Attempt_id = $id;";
            $statement = $dbh->prepare($stmt);
            $statement->execute();

            return "Successfully deleted Attempt Number $id";
        //}

    }

    public function options()
    {
        header("Allow: " . implode(", ", $this->options));
    }
}