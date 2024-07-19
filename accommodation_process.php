<?php
// 오류 보고 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 세션 시작
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login_form.php");
    exit();
}

// 데이터베이스 연결
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP에서 설정한 비밀번호
$dbname = "pbl2";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 최대 id숙소를 찾고 증가시키는 함수
function getNextId숙소($conn) {
    $sql = "SELECT MAX(CAST(SUBSTRING(id숙소, 2) AS UNSIGNED)) AS max_id FROM 숙소";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ? $row['max_id'] : 0;
    return 'L' . str_pad($max_id + 1, 3, '0', STR_PAD_LEFT);
}

// 최대 id호스트를 찾고 증가시키는 함수
function getNextId호스트($conn) {
    $sql = "SELECT MAX(CAST(SUBSTRING(id호스트, 3) AS UNSIGNED)) AS max_id FROM 호스트";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ? $row['max_id'] : 100;
    return 'HK' . str_pad($max_id + 1, 3, '0', STR_PAD_LEFT);
}

// 데이터 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $location = $_POST['location'] ?? '';
    $price = $_POST['price'] ?? '';
    $capacity = $_POST['capacity'] ?? '';
    $building_type = $_POST['building_type'] ?? '';
    $description = $_POST['description'] ?? '';

    // 새로운 숙소 ID 생성
    $id숙소 = getNextId숙소($conn);

    // 숙소 데이터 삽입
    $sql = "INSERT INTO 숙소 (id숙소, 숙소이름, 숙소위치, 가격_1박, 수용가능인원, 건물유형, 숙소설명) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssss', $id숙소, $name, $location, $price, $capacity, $building_type, $description);
    $stmt->execute();

    // 호스트 ID를 세션에서 가져옴
    $email = $_SESSION['email'];
    $sql = "SELECT id회원 FROM 회원_게스트 WHERE 이메일 = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $id회원 = $row['id회원'];

    $sql = "SELECT id호스트 FROM 호스트 WHERE id회원 = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $id회원);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id호스트 = $row['id호스트'];
    } else {
        // 새로운 호스트 ID 생성
        $id호스트 = getNextId호스트($conn);
        $운영날짜 = date('Y-m-d');

        $sql = "INSERT INTO 호스트 (id호스트, id회원, 운영날짜) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $id호스트, $id회원, $운영날짜);
        $stmt->execute();
    }

    // 호스트_숙소 테이블에 데이터 삽입
    $sql = "INSERT INTO 호스트_숙소 (id숙소, id호스트) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $id숙소, $id호스트);
    $stmt->execute();

    // 이미지 처리
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $index => $tmp_name) {
            $image_name = $_FILES['images']['name'][$index];
            $image_tmp_name = $_FILES['images']['tmp_name'][$index];
            $image_path = "uploads/" . basename($image_name);

            // 이미지 업로드
            if (move_uploaded_file($image_tmp_name, $image_path)) {
                // 이미지 경로를 데이터베이스에 삽입
                $sql = "SELECT COUNT(*) AS count FROM 숙소사진 WHERE id숙소 = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $id숙소);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $count = $row['count'];

                $id숙소사진 = $id숙소 . 'P' . ($count + 1);
                $sql = "INSERT INTO 숙소사진 (id숙소, id숙소사진, 이미지주소경로) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sss', $id숙소, $id숙소사진, $image_path);
                $stmt->execute();
            }
        }
    }

    // 기본 시설 삽입
    if (isset($_POST['basic_facilities'])) {
        foreach ($_POST['basic_facilities'] as $id기본시설 => $quantity) {
            $sql = "INSERT INTO 숙소_기본시설 (id숙소, id기본시설, 개수) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssi', $id숙소, $id기본시설, $quantity);
            $stmt->execute();
        }
    }

    // 편의 시설 삽입
    if (isset($_POST['conveniences'])) {
        foreach ($_POST['conveniences'] as $id편의시설) {
            $sql = "INSERT INTO 숙소_편의시설 (id숙소, id편의시설) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $id숙소, $id편의시설);
            $stmt->execute();
        }
    }

    // 숙소 휴일 삽입
    if (isset($_POST['holiday_start']) && isset($_POST['holiday_end'])) {
        foreach ($_POST['holiday_start'] as $index => $start_date) {
            $end_date = $_POST['holiday_end'][$index];
            if (!empty($start_date) && !empty($end_date)) {
                $sql = "SELECT COUNT(*) AS count FROM 숙소휴일 WHERE id숙소 = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $id숙소);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $count = $row['count'];
                $id숙소휴일 = $id숙소 . 'NB' . ($count + 1);

                $sql = "INSERT INTO 숙소휴일 (id숙소, id숙소휴일, 시작일, 종료일) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssss', $id숙소, $id숙소휴일, $start_date, $end_date);
                $stmt->execute();
            }
        }
    }

    // 등록 완료 후 리다이렉션 또는 메시지 표시
    echo "<script>alert('숙소가 성공적으로 등록되었습니다!'); window.location.href='host.php';</script>";
}

// 연결 종료
$conn->close();
?>
