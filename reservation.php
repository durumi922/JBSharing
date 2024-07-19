<?php
// 세션 시작
session_start();

// 로그인 여부 확인
if (!isset($_SESSION['email'])) {
    header('Location: login_form.php');
    exit;
}

// 데이터베이스 연결 정보
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP에서 설정한 비밀번호
$dbname = "pbl2";
$port = 3306; // XAMPP에서 사용하는 MySQL 포트

$conn = new mysqli($servername, $username, $password, $dbname, $port);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 사용자 입력 값 가져오기
$id숙소 = $_POST['id숙소'];
$체크인_날짜 = $_POST['체크인_날짜'];
$체크아웃_날짜 = $_POST['체크아웃_날짜'];
$숙박인원 = $_POST['숙박인원'];

// 세션에 저장된 사용자 이메일을 사용하여 사용자 정보 가져오기
$email = $_SESSION['email'];
$sql = "SELECT * FROM 회원_게스트 WHERE 이메일='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id회원 = $row['id회원'];
} else {
    echo "사용자 정보를 불러올 수 없습니다.";
    exit;
}

// 숙소 정보 가져오기
$sql = "SELECT * FROM 숙소 WHERE id숙소='$id숙소'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $숙소 = $result->fetch_assoc();
    $가격_1박 = $숙소['가격_1박'];
    $수용가능인원 = $숙소['수용가능인원'];
} else {
    echo "숙소 정보를 불러올 수 없습니다.";
    exit;
}

// 예약 가능 여부 확인 (기존 예약 및 휴일 확인)
$sql = "SELECT COUNT(*) AS count 
        FROM 예약 
        WHERE id숙소 = '$id숙소' 
        AND (
            (체크인_날짜 <= '$체크아웃_날짜' AND 체크아웃_날짜 >= '$체크인_날짜') 
            OR 
            (체크인_날짜 <= '$체크아웃_날짜' AND 체크아웃_날짜 >= '$체크인_날짜')
        )";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
if ($row['count'] > 0) {
    echo "<script>alert('선택한 날짜에 이미 예약이 있습니다. 다른 날짜를 선택해주세요.'); window.history.back();</script>";
    exit;
}

$sql = "SELECT COUNT(*) AS count 
        FROM 숙소휴일 
        WHERE id숙소 = '$id숙소' 
        AND 시작일 <= '$체크아웃_날짜' 
        AND 종료일 >= '$체크인_날짜'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
if ($row['count'] > 0) {
    echo "<script>alert('선택한 날짜는 숙소 휴일입니다. 다른 날짜를 선택해주세요.'); window.history.back();</script>";
    exit;
}

// 인원 초과 확인
if ($숙박인원 > $수용가능인원) {
    echo "<script>alert('숙박 인원이 초과되었습니다. 다른 인원수를 선택해주세요.'); window.history.back();</script>";
    exit;
}

// 총 가격 계산
$체크인 = new DateTime($체크인_날짜);
$체크아웃 = new DateTime($체크아웃_날짜);
$interval = $체크인->diff($체크아웃);
$숙박일수 = $interval->days;
$합계가격 = $숙박일수 * $가격_1박;

// 예약 ID 생성
$sql = "SELECT id예약 FROM 예약 WHERE id숙소 = '$id숙소' ORDER BY id예약 DESC LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_id = $row['id예약'];
    $new_id_number = (int)substr($last_id, strrpos($last_id, 'B') + 1) + 1;
    $new_id = $id숙소 . 'B' . $new_id_number;
} else {
    $new_id = $id숙소 . 'B1';
}

// 예약 정보 삽입
$sql = "INSERT INTO 예약 (id예약, id회원, id숙소, 체크인_날짜, 체크아웃_날짜, 예약상태, 합계가격, 숙박인원) 
        VALUES ('$new_id', '$id회원', '$id숙소', '$체크인_날짜', '$체크아웃_날짜', '예약 완료', $합계가격, $숙박인원)";
if (!$conn->query($sql)) {
    die("Query failed: " . $conn->error);
}

// 결제 페이지로 리디렉션
header("Location: payment.php?id예약=$new_id&합계가격=$합계가격");
exit();
?>
