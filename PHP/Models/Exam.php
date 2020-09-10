<?php
/**
 * Created by PhpStorm.
 * User: Joshua
 * Date: 10/7/2015
 * Time: 1:37 PM
 */

namespace TestingCenter\Models;


class Exam
{
    public $exam_id = '';
    public $exam_name = '';
    public $num_attempts_allowed = '';
    public $faculty_username = '';
    public $time_limit = '';
    public $calculator = '';
    public $text_book = '';
    public $scratch_paper = '';
    public $e_book = '';
    public $notes = '';
    public $open_date = '';
    public $close_date = '';

    function __construct($exam_name = "", $num_attempts_allowed = 1, $faculty_username = "", $time_limit = "", $calculator = calculator::None, $text_book = "", $e_book = "", $notes = "")
    {
        $this->exam_name = $exam_name;
        $this->num_attempts_allowed = $num_attempts_allowed;
        $this->faculty_username = $faculty_username;
        $this->time_limit = $time_limit;
        $this->calculator = $calculator;
        $this->text_book = $text_book;
        $this->e_book = $e_book;
        $this->notes = $notes;
    }

    function set($attr, $value)
    {
        $this->$attr = $value;
    }

    function get($attr)
    {
        return $this->$attr;
    }
}



class calculator
{
    const __default = self::None;

    const None = "null";
    const Graphing = "graphing";
    const Scientific = "scientific";
    const Four_Function = "4_function";
}