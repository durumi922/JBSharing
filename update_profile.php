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

// 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// POST 데이터 가져오기
$이름 = $_POST['이름'];
$생년월일 = $_POST['생년월일'];
$전화번호 = $_POST['전화번호'];
$이메일 = $_POST['이메일'];
$가입날짜 = $_POST['가입날짜'];

// 세션에 저장된 사용자 이메일을 사용하여 사용자 정보 업데이트
$old_email = $_SESSION['email'];
$sql = "UPDATE 회원_게스트 SET 이름='$이름', 생년월일='$생년월일', 전화번호='$전화번호', 이메일='$이메일', 가입날짜='$가입날짜' WHERE 이메일='$old_email'";

if ($conn->query($sql) === TRUE) {
    // 이메일이 변경되었을 경우 세션에 반영
    if ($old_email != $이메일) {
        $_SESSION['email'] = $이메일;
    }
    echo "프로필이 성공적으로 업데이트되었습니다.";
    header('Location: profile.php');
} else {
    echo "프로필 업데이트 중 오류가 발생했습니다: " . $conn->error;
}

// 연결 종료
$conn->close();
?>
