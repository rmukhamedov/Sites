<?php

namespace TestingCenter\Controllers;

use \TestingCenter\Http;
use TestingCenter\Models\Reservation;
use TestingCenter\Models\Token;
use TestingCenter\Utilities\DatabaseConnection;
use TestingCenter\Utilities\Cast;

class ReservationsController
{
    protected $request = array();
    protected $options = array(Http\Methods::GET, Http\Methods::POST, Http\Methods::PUT, Http\Methods::DELETE, Http\Methods::OPTIONS);
    //To-DO: Max number of desks in the testing center.  Load from config
    const TEMP_MAX = 30;

    public function get($url)
    {
        switch (count($url)) {
            case 0:
                $conditions = [];
                if (isset($_GET['studentId'])) {
                    $conditions[] = "student_id = :student_id";
                }
                if (isset($_GET['examId'])) {
                    $conditions[] = "exam_id = :exam_id";
                }
                if (isset($_GET['fromDate'])) {
                    $conditions[] = "end_date >= :from_date";
                }
                if (isset($_GET['toDate'])) {
                    $conditions[] = "start_date <= :to_date";
                }
                if (!isset($_GET['all'])) {
                    if (isset($_GET['deleted'])) {
                        $conditions[] = "deleted > 0";
                    } else {
                        $conditions[] = "deleted = 0";
                    }
                }
                // TODO: Determine what other searches to allow (e.g. course, professor, etc.)
                $reservations = [];
                $where = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";
                $connection = DatabaseConnection::getInstance();
                $statement = $connection->prepare("SELECT id, start_date, end_date, student_id, exam_id, deleted FROM Reservations $where;");
                $statement->setFetchMode(\PDO::FETCH_OBJ);
                $data = [];
                if (isset($_GET['studentId'])) {
                    $data['student_id'] = $_GET['studentId'];
                }
                if (isset($_GET['examId'])) {
                    $data['exam_id'] = $_GET['examId'];
                }
                if (isset($_GET['fromDate'])) {
                    $data['from_date'] = $_GET['fromDate'];
                }
                if (isset($_GET['toDate'])) {
                    $data['to_date'] = $_GET['toDate'];
                }
                $statement->execute($data);
                if ($statement->rowCount() > 0) {
                    while ($o = $statement->fetch()) {
                        $reservation = new Reservation($o->id);
                        $reservation->startDate = $o->start_date;
                        $reservation->endDate = $o->end_date;
                        $reservation->studentId = $o->student_id;
                        $reservation->examId = $o->exam_id;
                        $reservation->deleted = !($o->deleted == 0);
                        $reservations[] = $reservation;
                    }
                    return $reservations;
                } else {
                    http_response_code(Http\StatusCodes::NOT_FOUND);
                    exit("No matching reservations found");
                }
            case 1:
                $reservation = $this->loadReservation($url[0]);
                return $reservation;
            case 2:
                $reservation = $this->loadReservation($url[0]);
                return $reservation->$url[1];
            default:
                return null;
        }
    }

    private function loadReservation($id)
    {
        $connection = DatabaseConnection::getInstance();
        $statement = $connection->prepare("SELECT start_date, end_date, student_id, exam_id, deleted FROM Reservations WHERE id = :id;");
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $data = ["id" => $id];
        $statement->execute($data);
        if ($statement->rowCount() > 0) {
            if ($o = $statement->fetch()) {
                $reservation = new Reservation($id);
                $reservation->startDate = $o->start_date;
                $reservation->endDate = $o->end_date;
                $reservation->studentId = $o->student_id;
                $reservation->examId = $o->exam_id;
                $reservation->deleted = !($o->deleted == 0);
                return $reservation;
            }
        } else {
            http_response_code(Http\StatusCodes::NOT_FOUND);
            exit("No matching reservations found");
        }
        return null;
    }

    public function put($url)
    {
        $input = (object) json_decode(file_get_contents('php://input'));

        if (is_null($input) ||
                !isset($input->id) || !is_numeric($input->id) || $input->id <= 0 ||
                !isset($input->startDate) || !strtotime($input->startDate) ||
                !isset($input->endDate) || !strtotime($input->endDate) ||
                !isset($input->studentId) || !is_numeric($input->studentId) ||
                !isset($input->examId) || !is_numeric($input->examId) ||
                !isset($input->deleted) || !is_numeric($input->deleted)
            ) {
            http_response_code(Http\StatusCodes::BAD_REQUEST);
            exit("Invalid Data");
        }

        if(isset($url[0])){
            http_response_code(Http\StatusCodes::BAD_REQUEST);
            exit("Id is passed through the json object");
        }

        //This part checks if there is too many people to fit the block, or too many of the same exam for
        //every other seat to be the same exam  Exits internally.
        $this->checkSeatSpace($input->startDate, $input->endDate, $input->examId);

        $auth = false;
        $role = Token::getRoleFromToken();
        $user = Token::getUsernameFromToken();
        if (
            ($role == Token::ROLE_AIDE)  ||
            ($role == Token::ROLE_STUDENT && true) || //To-Do second arg is student owner.
            ($role == Token::ROLE_FACULTY && true) //To-Do second arg is faculty owner.
        ) { $auth = true; }

        if ($auth) {
            $pdo = DatabaseConnection::getInstance();
            $statement = $pdo->prepare("UPDATE Reservations SET" .
                " start_date = :start_date, end_date = :end_date, student_id = :student_id, exam_id = :exam_id," .
                " updated_by = :username, updated_on = CURRENT_TIMESTAMP WHERE id = :id");

            $data = array("start_date"=>$input->startDate, "end_date"=>$input->endDate,
                "student_id"=>$input->studentId, "exam_id"=>$input->examId,
                "username"=>$user, "id"=>$input->id);

            $statement->execute($data);
            if($statement->rowCount() == 0){
                http_response_code(Http\StatusCodes::NOT_MODIFIED);
                exit("Reservation does not exist");
            } else {
                http_response_code(Http\StatusCodes::OK);
                exit("Reservation was updated successfully");
            }
        } else {
            http_response_code(Http\StatusCodes::UNAUTHORIZED);
            exit("You are not allowed create a new reservation");
        }
    }

    public function post($url)
    {
        $input = (object) json_decode(file_get_contents('php://input'));

        if (is_null($input) ||
            !isset($input->id) || !is_numeric($input->id) || $input->id <= 0 ||
            !isset($input->startDate) || !strtotime($input->startDate) ||
            !isset($input->endDate) || !strtotime($input->endDate) ||
            !isset($input->studentId) || !is_numeric($input->studentId) ||
            !isset($input->examId) || !is_numeric($input->examId) ||
            !isset($input->deleted) || !is_numeric($input->deleted)
        ) {
            http_response_code(Http\StatusCodes::BAD_REQUEST);
            exit("Invalid Data");
        }

        $role = Token::getRoleFromToken();
        $validRequestor = false;

        if( $role == Token::ROLE_AIDE ||
            ($role == Token::ROLE_STUDENT && true) || //To-Do: determine if the student is in the class for the exam?
            ($role == Token::ROLE_FACULTY && true) //To-Do: determine if professor owns exam
        ) {
            $validRequestor = true;
        }
        else {
            http_response_code(Http\StatusCodes::UNAUTHORIZED);
            die("User does not have access to this exam");
        }

        //exit if there is not room for the reservation
        $this->checkSeatSpace($input->startDate, $input->endDate, $input->examId);

        if ($validRequestor) {
            $pdo = DatabaseConnection::getInstance();

            $start_date = $input->startDate;
            $end_date = $input->endDate;
            $student_id = $input->studentId;
            $exam_id = $input->examId;
            $username = Token::getUsernameFromToken();

            //Verify that the record does not already exist before inserting.
            //Ideally this data integrity check will be handled in the database.
            $checkDuplicateStatement = $pdo->prepare("SELECT * FROM Reservations ".
                "WHERE student_id = :student_id AND exam_id = :exam_id AND deleted = 0");
            $checkEntryData = array("student_id"=>$student_id, "exam_id"=>$exam_id);

            $isOk = $checkDuplicateStatement->execute($checkEntryData);
            if($isOk) {
                $count = $checkDuplicateStatement->fetchColumn();
            }
            if($count != null && $count > 0) {
                http_response_code(Http\StatusCodes::CONFLICT);
                exit("A reservation for this student and exam already exists");
            }

            $insertStatement = $pdo->prepare("INSERT INTO Reservations ".
                "(start_date, end_date, student_id, exam_id, deleted, created_by, created_on, updated_by, updated_on)".
                " VALUES (:start_date, :end_date, :student_id, :exam_id, 0,".
                " :created_by, CURRENT_TIMESTAMP,  :updated_by, CURRENT_TIMESTAMP)");

            $data = array("start_date"=>$start_date, "end_date"=>$end_date,
                "student_id"=>$student_id, "exam_id"=>$exam_id,
                "created_by"=>$username, "updated_by"=>$username);

            $insertStatement->execute($data);
            http_response_code(Http\StatusCodes::OK);
            exit("Reservation was created successfully");

        } else {
            http_response_code(Http\StatusCodes::UNAUTHORIZED);
            exit("You are not allowed create a new reservation");
        }

    }

    public function delete()
    {
        $input = (object) json_decode(file_get_contents('php://input'));
        if (is_null($input)) {
            http_response_code(Http\StatusCodes::BAD_REQUEST);
            exit("No data to post.");
        }

        $pdo = DatabaseConnection::getInstance();

        $role = Token::getRoleFromToken();

        if ($role == Token::ROLE_AIDE){
            $statement = $pdo->prepare("UPDATE Reservations SET".
                " updated_by = :username, updated_on = CURRENT_TIMESTAMP, deleted = :deleted WHERE id = :id");
            $user = Token::getUsernameFromToken();
            $data = array("username"=>$user, "id"=>$input->id, "deleted"=>1);

            $catch = $statement->execute($data);
            if($catch == true) {
                http_response_code(Http\StatusCodes::OK);
                exit("Reservation Deleted");
            }
            else{
                http_response_code(Http\StatusCodes::NOT_FOUND);
                exit("Reservation ID not found");
            }
        }
        if ($role == Token::ROLE_FACULTY) {

            $searchForExamID = $pdo->prepare("SELECT exam_id FROM Reservations WHERE id = :id");
            $dataForSearch = array("id"=>$input->id);
            $foundExamID = $searchForExamID->execute($dataForSearch);
            $examIDResults = $searchForExamID->fetch();
            if($foundExamID == true)
            {
                $doesProfessorOwn = $pdo->prepare("SELECT faculty_username FROM Exam WHERE exam_id = :exam_id");
                $dataForOwnershipVerification = array("exam_id"=>$examIDResults[0]);
                $foundOwnership = $doesProfessorOwn->execute($dataForOwnershipVerification);
                $facultyOwner = $doesProfessorOwn->fetch();
                if($foundOwnership == true && $facultyOwner[0] == Token::getUsernameFromToken())
                {
                    $statement = $pdo->prepare("UPDATE Reservations SET".
                        " updated_by = :username, updated_on = CURRENT_TIMESTAMP, deleted = :deleted WHERE id = :id");
                    $user = Token::getUsernameFromToken();
                    $data = array("username"=>$user, "id"=>$input->id, "deleted"=>1);

                    $catch = $statement->execute($data);
                    if($catch == true) {
                        http_response_code(Http\StatusCodes::OK);
                        exit("Reservation Deleted");
                    }
                    else{
                        http_response_code(Http\StatusCodes::NOT_FOUND);
                        exit("Reservation ID not found");
                    }
                }
                else{
                    http_response_code(Http\StatusCodes::NOT_FOUND);
                    exit("Faculty name does not own exam");
                }

            }
            else{
                http_response_code(Http\StatusCodes::NOT_FOUND);
                exit("Exam ID not found");
            }


        }
        if ($role == Token::ROLE_STUDENT){

            $user = Token::getUsernameFromToken();
            $statement = $pdo->prepare("SELECT deleted FROM Reservations WHERE id = :id AND student_id = :username");
            $data = array("id"=>$input->id, "username"=>$user);
            $found = $statement->execute($data);
            $results = $statement->fetch();
            if($found == true) {
                if($results[0] > 0) {
                    http_response_code(Http\StatusCodes::NOT_MODIFIED);
                    exit("Already Deleted");
                }
                if($results[0] == 0){
                    $statement = $pdo->prepare("UPDATE Reservations SET".
                        " updated_by = :username, updated_on = CURRENT_TIMESTAMP, deleted = :deleted WHERE id = :id AND student_id = :username");

                    $data = array("username"=>$user, "id"=>$input->id, "deleted"=>'1');

                    $catch = $statement->execute($data);
                    if($catch == true) {
                        http_response_code(Http\StatusCodes::OK);
                        exit("Reservation Deleted");
                    }
                    else {
                        http_response_code(Http\StatusCodes::NOT_FOUND);
                        exit("Reservation ID not found");
                    }
                }

            }
            else{
                http_response_code(Http\StatusCodes::Not_Found);
                exit("Either your ID or username is not valid for the operation");
            }

        }
        http_response_code(Http\StatusCodes::OK);
        exit("What the crap is wrong?!");
    }

    public function options()
    {
        header("Allow: " . implode(", ", $this->options));
    }

    public function checkSeatSpace($startDate, $endDate, $examId)
    {
        //Tests to see if there is room for the new or updated reservation

        //This part checks if there is too many people to fit the block, or too many of the same exam for
        //every other seat to be the same exam
        $pdo = DatabaseConnection::getInstance();
        $statement = $pdo->prepare("CALL spGetAvail(:start_date, :end_date, :exam_id)");
        $data = array("start_date"=>$startDate, "end_date"=>$endDate, "exam_id"=>$examId);

        $statement->execute($data);
        $examCount = 0;
        $totalCount = 0;
        while($row = $statement->fetch()){
            $examCount = $row['ExamCount'];
            $totalCount = $row['TotalCount'];
        }

        if($totalCount >= self::TEMP_MAX){
            http_response_code(Http\StatusCodes::CONFLICT);
            exit("No Desks Available");
        }
        if($examCount >= self::TEMP_MAX/2){
            http_response_code(Http\StatusCodes::CONFLICT);
            exit("Too many with the same exam");
        }

    }

}