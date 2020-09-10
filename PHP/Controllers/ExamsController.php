<?php

/**
 * Created by PhpStorm.
 * User: iamcaptaincode
 * Date: 10/6/2015
 * Time: 9:10 AM
 */
namespace TestingCenter\Controllers;
use \TestingCenter\Http;
use TestingCenter\Models\Exam;
use TestingCenter\Models\Token;
use TestingCenter\Utilities\DatabaseConnection;
use TestingCenter\Utilities\Cast;

class ExamsController
{

    private $db_connection = null;
    protected $request = array();
    protected $options = array(Http\Methods::GET, Http\Methods::POST, Http\Methods::PUT, Http\Methods::DELETE, Http\Methods::OPTIONS);

    function __construct()
    {
        $this->db_connection = DatabaseConnection::getInstance();
        if ($this->db_connection == null) {
            exit("It died");
        }
    }

    public function get($uri)
    {
        $role = Token::getRoleFromToken();
        $username = Token::getUsernameFromToken();

        switch ($role) {
            case Token::ROLE_FACULTY:
                $sql = "SELECT * FROM Exam WHERE faculty_username = '" . $username . "'";
                if (count($uri) > 0) {
                    $sql .= " AND exam_id = " . $uri[0];
                }
                $getQuery = $this->db_connection->query($sql);
                $getQuery->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, 'TestingCenter\Models\Exam');

                if (count($uri) > 1) {
                    $exam = $getQuery->fetch();
                    return $exam->get($uri[1]);
                }

                $exams = [];
                while ($exam = $getQuery->fetch()) {
                    array_push($exams, $exam);
                }
                return $exams;

            case Token::ROLE_STUDENT:
            case Token::ROLE_AIDE:
                $date = date("Y-m-d");
                $sql = "SELECT * FROM Exam WHERE close_date >= " . $date;
                if(count($uri) > 0) {
                    $sql .= " AND exam_id = " . $uri[0];
                }
                $getQuery = $this->db_connection->query($sql);
                $getQuery->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, 'TestingCenter\Models\Exam');
                if(count($uri) > 0) { // If a specific exam
                    $exam = $getQuery->fetch();
                    if($getQuery->rowCount() > 0) {
                        if (count($uri) > 1) { // They've specified an attribute
                            return $exam->get($uri[1]);
                        } else {
                            return $exam;
                        }
                    } else { // No exam found
                        exit("No exam with that ID found");
                    }
                } else { // Otherwise, just get all of them
                    $exams = [];
                    while ($exam = $getQuery->fetch()) {
                        array_push($exams, $exam);
                    }
                    return $exams;
                }

            default:
                exit("Error ID-10T: Enter something smart, stupid! (valid role)");
                break;
        }
    }

    public function put($uri)
    {
        $role = Token::getRoleFromToken();
        if ($role != Token::ROLE_FACULTY) {
            http_response_code(Http\StatusCodes::UNAUTHORIZED);
            exit("Non-Faculty members are not allowed to edit exams.");
        }

        if (count($uri) < 1) {
            http_response_code(Http\StatusCodes::BAD_REQUEST);
            exit("An exam ID must be specified in the URI.");
        }
        $input = json_decode(file_get_contents('php://input'));
        $exam_object = Cast::cast('TestingCenter\Models\Exam', $input);

        if (is_null($input)) {
            http_response_code(Http\StatusCodes::BAD_REQUEST);
            exit("No data to post.");
        }
        if (!is_string($exam_object->exam_name)) {
            http_response_code(Http\StatusCodes::BAD_REQUEST);
            exit("Exam name needs to be a string");
        }
        if ($exam_object->open_date != null) {
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$exam_object->open_date)) {
                http_response_code(Http\StatusCodes::BAD_REQUEST);
                exit("Please match the the format, (YYYY-MM-DD)");
            }
        }
        if ($exam_object->close_date != null) {
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$exam_object->close_date)) {
                http_response_code(Http\StatusCodes::BAD_REQUEST);
                exit("Close date needs to be a date (YYYY-MM-DD)");
            }
        }

        $keys = array();

        $data = array();
        foreach ($input as $key => $value) {
            array_push($keys, $key);
            $data[$key] = $value;
        }

        $properties = "";
        foreach($keys as $key) {
            if ($key === end($keys)) {
                $properties .= $key . "=:" . $key . " ";
            } else {
                $properties .= $key . "=:" . $key . ", ";
            }
        }
        $updateString = "UPDATE Exam SET " . $properties . "WHERE exam_id=" . $uri[0];

        $sql = $this->db_connection->prepare($updateString);
        $sql->execute($data);

        http_response_code(Http\StatusCodes::OK);
    }

    public function post($uri)
    {
        $role = Token::getRoleFromToken();
        if ($role != Token::ROLE_FACULTY) {
            http_response_code(Http\StatusCodes::UNAUTHORIZED);
            exit("Non-Faculty members are not allowed to create exams.");
        }

        $input = json_decode(file_get_contents('php://input'));
        $exam_object = Cast::cast('TestingCenter\Models\Exam' ,$input);

        if (is_null($input)) {
            http_response_code(Http\StatusCodes::BAD_REQUEST);
            exit("No data to post.");
        }
        if (!is_string($exam_object->exam_name)) {
            http_response_code(Http\StatusCodes::BAD_REQUEST);
            exit("Exam name needs to be a string");
        }
        if ($exam_object->faculty_username == "") {
            http_response_code(Http\StatusCodes::BAD_REQUEST);
            exit("Please enter a faculty username");
        }
        if ($exam_object->open_date != null) {
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $exam_object->open_date)) {
                http_response_code(Http\StatusCodes::BAD_REQUEST);
                exit("Please match the the format, (YYYY-MM-DD)");
            }
        }
        if ($exam_object->open_date != null) {
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $exam_object->close_date)) {
                http_response_code(Http\StatusCodes::BAD_REQUEST);
                exit("Close date needs to be a date (YYYY-MM-DD)");
            }
        }

        $sql = $this->db_connection->prepare("INSERT INTO Exam (exam_name, num_attempts_allowed, faculty_username, time_limit, scratch_paper, calculator, text_book, e_book, notes, open_date, close_date) "
            . "VALUES (:exam_name, :num_attempts_allowed, :faculty_username, :time_limit, :scratch_paper, :calculator, :text_book, :e_book, :notes, :open_date, :close_date)");
        $data = array("exam_name" => $exam_object->exam_name, "num_attempts_allowed" => $exam_object->num_attempts_allowed, "faculty_username" => $exam_object->faculty_username, "time_limit" => $exam_object->time_limit, "scratch_paper" => $exam_object->scratch_paper, "calculator" => $exam_object->calculator, "text_book" => $exam_object->text_book, "e_book" => $exam_object->e_book, "notes" => $exam_object->notes, "open_date" => $exam_object->open_date, "close_date" => $exam_object->close_date);
        $sql->execute($data);

        http_response_code(Http\StatusCodes::CREATED);
    }

    public function delete($uri)
    {
        $role = Token::getRoleFromToken();
        if ($role != Token::ROLE_FACULTY) {
            http_response_code(Http\StatusCodes::UNAUTHORIZED);
            exit("Non-Faculty members, are not allowed to delete exams.");
        } else {
            if ($uri[0] != null) {
                $endOfUri = $uri[0];
                $sql = "DELETE FROM Exam WHERE exam_id = " . $endOfUri;
            } else {
                exit("You need to enter a valid Exam ID to delete");
            }
        }
    }

    public function options()
    {
        header("Allow: ". implode(", ", $this->options));
    }
}