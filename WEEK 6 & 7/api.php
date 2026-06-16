<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
require_once 'includes/config.php';
ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
    exit();
}
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['id'])) {
            getStudent($conn, (int)$_GET['id']);
        } else {
            getAllStudents($conn);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        addStudent($conn, $data);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        updateStudent($conn, $data);
        break;

    case 'DELETE':
        if (!isSuperAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Only Superadmin can delete students.']);
            exit();
        }
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        deleteStudent($conn, $id);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

function getAllStudents($conn) {
    $result   = $conn->query("SELECT * FROM students ORDER BY created_at DESC");
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $students]);
}

function getStudent($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    if ($student) {
        echo json_encode(['success' => true, 'data' => $student]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Student not found.']);
    }
}

function addStudent($conn, $data) {
    if (empty($data['name']) || empty($data['email']) || empty($data['course']) || empty($data['year'])) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
        return;
    }
    $name   = $conn->real_escape_string(trim($data['name']));
    $email  = $conn->real_escape_string(trim($data['email']));
    $course = $conn->real_escape_string(trim($data['course']));
    $year   = (int)$data['year'];
    $gpa    = isset($data['gpa']) && $data['gpa'] !== '' ? (float)$data['gpa'] : null;

    $stmt = $conn->prepare("INSERT INTO students (name, email, course, year, gpa) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssid', $name, $email, $course, $year, $gpa);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Student added!', 'id' => $conn->insert_id]);
    } else {
        if ($conn->errno === 1062) {
            echo json_encode(['success' => false, 'message' => 'Email already exists.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add student.']);
        }
    }
}

function updateStudent($conn, $data) {
    if (empty($data['id']) || empty($data['name']) || empty($data['email']) || empty($data['course']) || empty($data['year'])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        return;
    }
    $id     = (int)$data['id'];
    $name   = $conn->real_escape_string(trim($data['name']));
    $email  = $conn->real_escape_string(trim($data['email']));
    $course = $conn->real_escape_string(trim($data['course']));
    $year   = (int)$data['year'];
    $gpa    = isset($data['gpa']) && $data['gpa'] !== '' ? (float)$data['gpa'] : null;

    $stmt = $conn->prepare("UPDATE students SET name=?, email=?, course=?, year=?, gpa=? WHERE id=?");
    $stmt->bind_param('sssdii', $name, $email, $course, $gpa, $year, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Student updated!']);
    } else {
        if ($conn->errno === 1062) {
            echo json_encode(['success' => false, 'message' => 'Email already exists.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update student.']);
        }
    }
}

function deleteStudent($conn, $id) {
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
        return;
    }
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Student deleted!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Student not found.']);
    }
}

$conn->close();
?>