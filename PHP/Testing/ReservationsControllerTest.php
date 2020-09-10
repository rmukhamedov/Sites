<?php

namespace TestingCenter\Testing;

use TestingCenter\Http\Methods;
use TestingCenter\Models\Reservation;
use TestingCenter\Utilities\Testing;
use TestingCenter\Controllers\TokensController;
use TestingCenter\Utilities\DatabaseConnection;

use TestingCenter\Controllers\ReservationsController;

class ReservationsControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        // Get controller reference
        $controller = new ReservationsController();
        $this->getAll($controller);
        $this->getDeleted($controller);
        $this->getReservation($controller);
        $this->getAttribute($controller);
    }

    private function getAll(ReservationsController $controller)
    {
        // Test /reservations/
        $data = [];
        $reservations = $controller->get($data);
        $this->assertGreaterThan(0, count($reservations));
        foreach ($reservations as $reservation) {
            $this->assertReservationActive($reservation);
        }
    }

    private function getDeleted(ReservationsController $controller)
    {
        // Test /reservations/?deleted
        $_GET["deleted"] = true;
        $data = [];
        $reservations = $controller->get($data);
        $this->assertGreaterThan(0, count($reservations));
        foreach ($reservations as $reservation) {
            $this->assertReservationDeleted($reservation);
        }
        unset($_GET["deleted"]);
    }

    private function getReservation(ReservationsController $controller)
    {
        // Test /reservations/#/
        $data = [400];
        $reservation = $controller->get($data);
        $this->assertGoodReservation($reservation);
    }

    private function getAttribute(ReservationsController $controller)
    {
        // Test /reservations/#/studentId/
        $data = [400, "examId"];
        $examId = $controller->get($data);
        $this->assertGreaterThanOrEqual(0, $examId);
    }

    private function assertGoodReservation(Reservation $reservation)
    {
        $this->assertGreaterThanOrEqual(0, $reservation->id);
        $this->assertNotEmpty($reservation->startDate);
        $this->assertNotEmpty($reservation->endDate);
        $this->assertNotEmpty($reservation->studentId);
        $this->assertGreaterThanOrEqual(0, $reservation->examId);
    }

    private function assertReservationDeleted(Reservation $reservation)
    {
        $this->assertGoodReservation($reservation);
        $this->assertTrue($reservation->deleted);
    }

    private function assertReservationActive(Reservation $reservation)
    {
        $this->assertGoodReservation($reservation);
        $this->assertFalse($reservation->deleted);
    }

    public function testPut()
    {

        //$token = "";
        //$body = "username=notarealuser&password=secretpass123";
        //$url = "http://icarus.cs.weber.edu/~ll02508/api/v1/tokens";

        //$output = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::FORM);

        //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.
        //$this->assertNotFalse($output);
        //print_r($output);

        //$this->assertJsonStringEqualsJsonString("");
        //Compare against expected JSON object. You  could also do other tests.
    }

    public function testPutSuccess()
    {
        $jsonObj = new Reservation("1");
        $jsonObj->startDate = "2012-04-23T18:25:43.511Z";
        $jsonObj->endDate = "2012-04-23T19:25:43.511Z";
        $jsonObj->studentId = "4";
        $jsonObj->examId = "4";
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);

        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/";
        $token = $this->generateTokenStudent();

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);
        $this->assertEquals('Reservation was updated successfully', $output);
    }

    public function testPutReservationNotExists()
    {
        //find the max id and add 1
        $pdo = DatabaseConnection::getInstance();
        $statement = $pdo->prepare("SELECT MAX(id) + 1 AS maxId FROM Reservations");
        $statement->execute();
        $maxId = "0";
        while ($row = $statement->fetch()) {
            $maxId = $row['maxId'];
        }

        $jsonObj = new Reservation($maxId);
        $jsonObj->startDate = "2012-04-23T18:25:43.511Z";
        $jsonObj->endDate = "2012-04-23T19:25:43.511Z";
        $jsonObj->studentId = "4";
        $jsonObj->examId = "4";
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);

        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/";
        $token = $this->generateTokenStudent();

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);
        $this->assertEquals('304', Testing::getLastStatusCode());
    }

    public function testPutFailNoDataSent()
    {
        //this is not working, its not triggering the input = null case
        $body = '{"id": "5"}';
        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/";
        $token = $this->generateTokenFaculty();

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $body, $token, Testing::JSON);

        $this->assertEquals('Invalid Data', $output);
    }

    public function testPutBadIdSent()
    {
        $jsonObj = new Reservation("-1");
        $jsonObj->startDate = "2012-04-23T18:25:43.511Z";
        $jsonObj->endDate = "2012-04-23T19:25:43.511Z";
        $jsonObj->studentId = "4";
        $jsonObj->examId = "4";
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);

        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/";
        $token = $this->generateTokenStudent();

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);
        $this->assertEquals('Invalid Data', $output);
    }

    public function testPutBadStartDate()
    {
        $jsonObj = new Reservation("1");
        $jsonObj->startDate = "I Like Pizza";
        $jsonObj->endDate = "2012-04-23T19:25:43.511Z";
        $jsonObj->studentId = "4";
        $jsonObj->examId = "4";
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);

        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/";
        $token = $this->generateTokenStudent();

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);
        $this->assertEquals('Invalid Data', $output);
    }

    public function testPutBadEndDate()
    {
        $jsonObj = new Reservation("1");
        $jsonObj->startDate = "2012-04-23T18:25:43.511Z";
        $jsonObj->endDate = "3";
        $jsonObj->studentId = "4";
        $jsonObj->examId = "4";
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);

        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/";
        $token = $this->generateTokenStudent();

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);
        $this->assertEquals('Invalid Data', $output);
    }

    public function testPutBadStudentId()
    {
        $jsonObj = new Reservation("1");
        $jsonObj->startDate = "2012-04-23T18:25:43.511Z";
        $jsonObj->endDate = "2012-04-23T19:25:43.511Z";
        $jsonObj->studentId = "4";
        $jsonObj->examId = "4";
        $jsonObj->deleted = "Josh Rocks!";
        $jsonObj = json_encode($jsonObj);

        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/";
        $token = $this->generateTokenStudent();

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);
        $this->assertEquals('Invalid Data', $output);
    }

    public function testPutBadExamId()
    {
        $jsonObj = new Reservation("1");
        $jsonObj->startDate = "2012-04-23T18:25:43.511Z";
        $jsonObj->endDate = "2012-04-23T19:25:43.511Z";
        $jsonObj->studentId = "4";
        $jsonObj->examId = true;
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);

        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/";
        $token = $this->generateTokenStudent();

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);
        $this->assertEquals('Invalid Data', $output);
    }

    public function testPutJsonMissingField()
    {
        $jsonObj = new Reservation("1");
        $jsonObj->startDate = "2012-04-23T18:25:43.511Z";
        $jsonObj->endDate = "2012-04-23T19:25:43.511Z";
        $jsonObj->studentId = "4";
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);

        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/";
        $token = $this->generateTokenStudent();

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);
        $this->assertEquals('Invalid Data', $output);
    }

    public function testPutFailIdInURL()
    {
        $jsonObj = new Reservation("1");
        $jsonObj->startDate = "2012-04-23T18:25:43.511Z";
        $jsonObj->endDate = "2012-04-23T19:25:43.511Z";
        $jsonObj->studentId = "4";
        $jsonObj->examId = "4";
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);

        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/1/";
        $token = $this->generateTokenFaculty();
        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);
        $this->assertEquals('Id is passed through the json object', $output);
    }

    public function testPutFailTooManySameExam()
    {
        //Too many of one exam
        $jsonObj = new Reservation("2");
        $jsonObj->startDate = "1901-10-25T03:00:00.000Z";
        $jsonObj->endDate = "1901-10-25T04:00:00.000Z";
        $jsonObj->studentId = "4";
        $jsonObj->examId = "3";
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);

        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/";
        $token = $this->generateTokenAide();

        //insert 15 rows and make sure the 16th fails
        for ($i = 0; $i < 15; $i++) {
            Testing::callAPIOverHTTP($url, Methods::POST, $jsonObj, $token, Testing::JSON);
        }

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);
        $this->assertEquals('Too many with the same exam', $output);

        //reset examid 2
        $jsonObj = new Reservation("2");
        $jsonObj->startDate = "2000-04-23T18:25:43.511Z";
        $jsonObj->endDate = "2000-04-23T19:25:43.511Z";
        $jsonObj->studentId = "4";
        $jsonObj->examId = "4";
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);
        Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);

        //remove rows
        $pdo = DatabaseConnection::getInstance();
        $statement = $pdo->prepare("DELETE FROM Reservations WHERE year(start_date) = 1901");
        $statement->execute();
    }

    public function testPutFailTooManyTotal()
    {
        //Too many of any exams
        $url = "http://icarus.cs.weber.edu/~ll02508/api/v1/Reservations/";
        $token = $this->generateTokenAide();
        //insert 30 rows with different exam ids, and make sure updating an exam in that slot fails
        for ($i = 0; $i < 30; $i++) {
            $jsonObj = new Reservation("-1");
            $jsonObj->startDate = "1902-10-25T03:00:00.000Z";
            $jsonObj->endDate = "1902-10-25T04:00:00.000Z";
            $jsonObj->studentId = "4";
            $jsonObj->examId = $i;
            $jsonObj->deleted = "0";
            $jsonObj = json_encode($jsonObj);
            Testing::callAPIOverHTTP($url, Methods::POST, $jsonObj, $token, Testing::JSON);
        }

        $jsonObj = new Reservation("3");
        $jsonObj->startDate = "1902-10-25T03:00:00.000Z";
        $jsonObj->endDate = "1902-10-25T04:00:00.000Z";
        $jsonObj->studentId = "4";
        $jsonObj->examId = "3";
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);
        $this->assertEquals('No Desks Available', $output);

        //reset exam id 3
        $jsonObj = new Reservation("3");
        $jsonObj->startDate = "2000-04-23T18:25:43.511Z";
        $jsonObj->endDate = "2000-04-23T19:25:43.511Z";
        $jsonObj->studentId = "4";
        $jsonObj->examId = "4";
        $jsonObj->deleted = "0";
        $jsonObj = json_encode($jsonObj);
        Testing::callAPIOverHTTP($url, Methods::PUT, $jsonObj, $token, Testing::JSON);

        //remove rows
        $pdo = DatabaseConnection::getInstance();
        $statement = $pdo->prepare("DELETE FROM Reservations WHERE year(start_date) = 1902");
        $statement->execute();
    }


    public function testPost()
    {
    }

    //Test new reservation with valid data
    public function testPostSuccess()
    {
        $body = new Reservation("1");
        $body->studentId = "generic";
        $body->examId = "71";
        $body->startDate = "2015-10-21 07:00:00";
        $body->endDate = "2015-10-21 09:00:00";
        $body = json_encode($body);

        $url = "http://icarus.cs.weber.edu/~jm93817/api/v1/Reservations/";
        $token = $this->generateTokenStudent();

        $result = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::JSON);
        $this->assertEquals('Reservation was created successfully', $result);

    }
    // Test with invalid data in all parameters
    public function testPostInvalidData()
    {
        $body = new Reservation("1");
        $body->studentId = "////";
        $body->examId = "exam!";
        $body->startDate = "whenever";
        $body->endDate = "never";
        $body = json_encode($body);

        $url = "http://icarus.cs.weber.edu/~jm93817/api/v1/Reservations/";
        $token = $this->generateTokenStudent();

        $result = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::JSON);
        $this->assertEquals('Invalid Data', $result);
    }

    //Tests for an upcoming reservation with the same student_id && exam_id entered by the student
    public function testPostDuplicateEntryStudent()
    {
        $this->createDBEntryForStudentSuccess(71);
        $body = new Reservation("1");
        $body->studentId = "generic";
        $body->examId = "71";
        $body->startDate = "2012-10-21 00:00:00";
        $body->endDate = "2012-10-21 02:00:00";
        $body = json_encode($body);

        $token = $this->generateTokenStudent();
        $url = "http://icarus.cs.weber.edu/~jm93817/api/v1/Reservations/";

        $results = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::JSON);
        $this->assertEquals('A reservation for this student and exam already exists', $results);

        $catch = $this->cleanUpDBEntryForStudentSuccess($body[0]);
        $this->assertTrue($catch);

    }
    //Tests for an upcoming reservation with the same student_id && exam_id entered by a Faculty member with access
    public function testPostDuplicateEntryFaculty()
    {
        $this->createDBEntryForStudentSuccess(71);
        $body = new Reservation("1");
        $body->studentId = "generic";
        $body->examId = "71";
        $body->startDate = "2012-10-21 00:00:00";
        $body->endDate = "2012-10-21 02:00:00";
        $body = json_encode($body);

        $token = $this->generateTokenAide();
        $url = "http://icarus.cs.weber.edu/~jm93817/api/v1/Reservations/";

        $results = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::JSON);
        $this->assertEquals('A reservation for this student and exam already exists', $results);

        $catch = $this->cleanUpDBEntryForStudentSuccess($body[0]);
        $this->assertTrue($catch);

    }
    //Tests for an upcoming reservation with the same student_id && exam_id entered by a Testing Center Aide
    public function testPostDuplicateEntryTestAid()
    {
        $this->createDBEntryForStudentSuccess(71);
        $body = new Reservation("1");
        $body->studentId = "generic";
        $body->examId = "71";
        $body->startDate = "2012-10-21 00:00:00";
        $body->endDate = "2012-10-21 02:00:00";
        $body = json_encode($body);

        $token = $this->generateTokenAide();
        $url = "http://icarus.cs.weber.edu/~jm93817/api/v1/Reservations/";

        $results = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::JSON);
        $this->assertEquals('A reservation for this student and exam already exists', $results);

        $catch = $this->cleanUpDBEntryForStudentSuccess($body[0]);
        $this->assertTrue($catch);


    }
    public function testStudentSuccessfulDelete()
    {
        $catch = $this->createDBEntryForStudentSuccess(99);

        $token = $this->generateTokenStudent();
        $url = "http://icarus.cs.weber.edu/~dg88067/api/v1/Reservations/";
        $jsonObj = new Reservation($catch[0]);
        $jsonObj = json_encode($jsonObj);
        $results = Testing::callAPIOverHTTP($url, Methods::DELETE, $jsonObj, $token, Testing::JSON);

        $this->assertEquals('Reservation Deleted', $results);

        $catch = $this->cleanUpDBEntryForStudentSuccess($catch[0]);

        $this->assertTrue($catch);

    }

    public function testFacultySuccessfulDelete()
    {
        $catch = $this->createExamForTest();

        $token = $this->generateTokenFaculty();
        $url = "http://icarus.cs.weber.edu/~dg88067/api/v1/Reservations/";
        $catchResID = $this->createDBEntryForStudentSuccess($catch[0]);
        $jsonObj = new Reservation($catchResID[0]);
        $jsonObj = json_encode($jsonObj);
        $results = Testing::callAPIOverHTTP($url, Methods::DELETE, $jsonObj, $token, Testing::JSON);

        $this->assertEquals('Reservation Deleted', $results);

        $catch = $this->deleteExamForTest($catch[0]);
        $this->assertTrue($catch);
        $catch = $this->cleanUpDBEntryForStudentSuccess($catchResID[0]);

        $this->assertTrue($catch);
    }

    public function testAideSuccessfulDelete()
    {
        $catch = $this->createDBEntryForStudentSuccess(99);

        $token = $this->generateTokenAide();
        $url = "http://icarus.cs.weber.edu/~dg88067/api/v1/Reservations/";
        $jsonObj = new Reservation($catch[0]);
        $jsonObj = json_encode($jsonObj);
        $results = Testing::callAPIOverHTTP($url, Methods::DELETE, $jsonObj, $token, Testing::JSON);

        $this->assertEquals('Reservation Deleted', $results);

        $catch = $this->cleanUpDBEntryForStudentSuccess($catch[0]);

        $this->assertTrue($catch);
    }

    private function deleteExamForTest($exam_id)
    {
        $pdo = DatabaseConnection::getInstance();
        $statement = $pdo->prepare("DELETE FROM Exam where exam_id = :exam_id");
        $data = array("exam_id" => $exam_id);
        $found = $statement->execute($data);

        return $found;
    }

    private function createExamForTest()
    {
        $pdo = DatabaseConnection::getInstance();

        $token = $this->generateTokenFaculty();

        $exam_name = 'DavesExam';
        $num_attempts = 10;
        $faculty_username = 'genericfac';
        $time_limit = 60;
        $scratch_paper = 0;
        $calculator = 'graphing';
        $text_book = 0;
        $e_book = 'none';
        $notes = 'two pages front and back';
        $open_date = '2015-11-10';
        $close_date = '2015-11-20';

        $insertStatement = $pdo->prepare("INSERT INTO Exam " .
            "(exam_name, num_attempts_allowed, faculty_username, time_limit, scratch_paper, calculator, text_book, e_book, notes, open_date, close_date)" .
            " VALUES (:exam_name, :num_attempts_allowed, :faculty_username, :time_limit, :scratch_paper, :calculator, :text_book, :e_book, :notes, :open_date, :close_date)");


        $data = array("exam_name" => $exam_name, "num_attempts_allowed" => $num_attempts, "faculty_username" => $faculty_username,
            "time_limit" => $time_limit, "scratch_paper" => $scratch_paper, "calculator" => $calculator,
            "text_book" => $text_book, "e_book" => $e_book, "notes" => $notes, "open_date" => $open_date, "close_date" => $close_date);

        $insertStatement->execute($data);
        $statement = $pdo->prepare("SELECT exam_id FROM Exam WHERE faculty_username = :username &&  exam_name = :exam_name");
        $data = array("username" => $faculty_username, "exam_name" => $exam_name);
        $found = $statement->execute($data);

        if ($found == true) {
            $results = $statement->fetch();
            $results[1] = $token;
            return $results;
        } else
            exit("bad test, shame on you");
    }

    private function createDBEntryForStudentSuccess($exam_id)
    {
        $pdo = DatabaseConnection::getInstance();

        $token = $this->generateTokenStudent();
        $start_date = '2012-04-23 18:25:43';
        $end_date = '2012-04-23 19:25:43';

        $username = 'generic';


        $insertStatement = $pdo->prepare("INSERT INTO Reservations " .
            "(start_date, end_date, student_id, exam_id, deleted, created_by, created_on, updated_by, updated_on)" .
            " VALUES (:start_date, :end_date, :student_id, :exam_id, 0," .
            " :created_by, CURRENT_TIMESTAMP,  :updated_by, CURRENT_TIMESTAMP)");

        $data = array("start_date" => $start_date, "end_date" => $end_date,
            "student_id" => $username, "exam_id" => $exam_id,
            "created_by" => $username, "updated_by" => $username);

        $insertStatement->execute($data);

        $statement = $pdo->prepare("SELECT id FROM Reservations WHERE student_id = :username &&  exam_id = :exam_id");
        $data = array("username" => $username, "exam_id" => $exam_id);
        $found = $statement->execute($data);

        if ($found == true) {
            $results = $statement->fetch();
            $results[1] = $token;
            return $results;
        } else
            exit("bad test, shame on you");
    }

    private function cleanUpDBEntryForStudentSuccess($resID)
    {
        $pdo = DatabaseConnection::getInstance();
        $statement = $pdo->prepare("DELETE FROM Reservations where id = :resID");
        $data = array("resID" => $resID);
        $found = $statement->execute($data);

        return $found;
    }

    private function generateTokenFaculty()
    {
        $_POST['username'] = "genericfac";
        $_POST['password'] = "Hello896";
        $tokenController = new TokensController();
        return $tokenController->post();
    }

    private function generateTokenStudent()
    {
        $_POST['username'] = "generic";
        $_POST['password'] = "Hello357";
        $tokenController = new TokensController();
        return $tokenController->post();
    }

    private function generateTokenAide()
    {
        $_POST['username'] = "generictech";
        $_POST['password'] = "Hello361";
        $tokenController = new TokensController();
        return $tokenController->post();
    }
}
