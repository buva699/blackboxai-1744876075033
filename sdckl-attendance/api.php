<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sdckl_attendance";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';

if ($method === 'GET') {
    if ($path === 'students') {
        $result = $conn->query("SELECT student_id, full_name, class_id, contact_number, status FROM students WHERE status = 'active'");
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        echo json_encode($students);
    } elseif ($path === 'attendance') {
        $date = $_GET['date'] ?? date('Y-m-d');
        $sql = "SELECT a.student_id, a.date, a.time_in, a.status, s.full_name, c.class_name
                FROM attendance a
                JOIN students s ON a.student_id = s.student_id
                JOIN classes c ON s.class_id = c.class_id
                WHERE a.date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $attendance = [];
        while ($row = $result->fetch_assoc()) {
            $attendance[] = $row;
        }
        echo json_encode($attendance);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Invalid API path"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}

$conn->close();
?>
