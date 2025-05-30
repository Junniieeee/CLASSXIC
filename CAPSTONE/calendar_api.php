<?php
include "myconnector.php";
session_start();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Students see all available slots, tutors see their own
    if ($user_role === 'tutor') {
        $result = mysqli_query($conn, "SELECT * FROM schedules WHERE tutor_id = $user_id");
    } else {
        $result = mysqli_query($conn, "SELECT * FROM schedules");
    }
    $events = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = [
            'id' => $row['schedule_id'],
            'title' => ($row['is_booked'] ? 'Booked: ' : 'Available: ') . $row['day'],
            'start' => $row['session_date'] . 'T' . $row['available_from'],
            'end' => $row['session_date'] . 'T' . $row['available_to'],
            'color' => $row['is_booked'] ? '#dc3545' : '#0d6efd',
            'extendedProps' => [
                'tutor_id' => $row['tutor_id'],
                'student_id' => $row['student_id'],
                'location_mode' => $row['location_mode'],
                'status' => $row['status'],
                'is_booked' => $row['is_booked'] // <-- ADD THIS LINE
            ]
        ];
    }
    echo json_encode($events);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    // TUTOR: CREATE slot
    if ($action === 'create' && $user_role === 'tutor') {
        $day = mysqli_real_escape_string($conn, $data['day']);
        $session_date = mysqli_real_escape_string($conn, $data['session_date']);
        $available_from = mysqli_real_escape_string($conn, $data['available_from']);
        $available_to = mysqli_real_escape_string($conn, $data['available_to']);
        $location_mode = mysqli_real_escape_string($conn, $data['location_mode']);
        $sql = "INSERT INTO schedules (tutor_id, day, available_from, available_to, session_date, location_mode) 
                VALUES ($user_id, '$day', '$available_from', '$available_to', '$session_date', '$location_mode')";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    // TUTOR: UPDATE slot
    if ($action === 'update' && $user_role === 'tutor') {
        $id = intval($data['id']);
        $day = mysqli_real_escape_string($conn, $data['day']);
        $session_date = mysqli_real_escape_string($conn, $data['session_date']);
        $available_from = mysqli_real_escape_string($conn, $data['available_from']);
        $available_to = mysqli_real_escape_string($conn, $data['available_to']);
        $location_mode = mysqli_real_escape_string($conn, $data['location_mode']);
        $sql = "UPDATE schedules SET day='$day', session_date='$session_date', available_from='$available_from', available_to='$available_to', location_mode='$location_mode' WHERE schedule_id=$id AND tutor_id=$user_id";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    // TUTOR: DELETE slot
    if ($action === 'delete' && $user_role === 'tutor') {
        $id = intval($data['id']);
        $sql = "DELETE FROM schedules WHERE schedule_id=$id AND tutor_id=$user_id";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    // STUDENT: BOOK slot
    if ($action === 'book' && $user_role === 'student') {
        $id = intval($data['id']);
        $sql = "UPDATE schedules SET student_id=$user_id, is_booked=1 WHERE schedule_id=$id AND is_booked=0";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    // STUDENT: CANCEL booking
    if ($action === 'cancel' && $user_role === 'student') {
        $id = intval($data['id']);
        $sql = "UPDATE schedules SET student_id=NULL, is_booked=0 WHERE schedule_id=$id AND student_id=$user_id";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);