<?php
/**
 * AJAX: Get Lessons by Course ID
 */
include('../dbConnection.php');

header('Content-Type: application/json');

if(!isset($_GET['course_id'])) {
    echo json_encode([]);
    exit;
}

$course_id = intval($_GET['course_id']);

$lessons = $conn->query("SELECT lesson_id, lesson_name FROM lessons WHERE course_id = $course_id ORDER BY lesson_order");

$result = [];
while($lesson = $lessons->fetch_assoc()) {
    $result[] = [
        'lesson_id' => $lesson['lesson_id'],
        'lesson_name' => $lesson['lesson_name']
    ];
}

echo json_encode($result);