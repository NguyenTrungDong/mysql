<?php 
include_once 'model.php';

function createStudentsTable($pdo)
{
    $sql = "CREATE TABLE Students
    (
        student_id INT PRIMARY KEY AUTO_INCREMENT,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE,
        join_date DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
}
function createCoursesTable($pdo)
{
    $sql = "CREATE TABLE Courses 
    (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    price INT CHECK (price >= 0)
    )";
    $pdo->exec($sql);
}

function createEnrollmentsTable($pdo)
{
    $sql = "CREATE TABLE Enrollments
    (
        enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT,
        course_id INT,
        enroll_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES Students(student_id),
        FOREIGN KEY (course_id) REFERENCES Courses(course_id)
    )";
    $pdo->exec($sql);
}

function addStatusToEnrollmentsTable($pdo)
{
    $sql = 
    "ALTER TABLE Enrollments
    ADD status ENUM('active','inactive') DEFAULT 'active'";
    $pdo->exec($sql);
}

function createStudentCourseView($pdo)
{
    $sql = "CREATE VIEW StudentCourseView AS 
    SELECT s.student_id,s.full_name,s.email,s.join_date,c.course_id, c.title as course_title, c.description, c.price as course_price
    FROM Students s
    JOIN Enrollments e ON s.student_id = e.student_id
    JOIN Courses c ON c.course_id = e.course_id";
    $pdo->exec($sql);
}

function createTitleIndexFromCoursesTable($pdo)
{
    $sql = "CREATE INDEX titleIndex ON Courses (title)";
    $pdo->exec($sql);
}