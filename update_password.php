<?php
// 오류 보고 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 데이터베이스 연결 정보
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP에서 설정한 비밀번호
$dbname = "pbl2";
$port = 3306; // XAMPP에서 사용하는 MySQL 포트

// 사용자 입력 값 가져오기
$email = isset($_POST['email']) ? $_POST['email'] : '';
$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 비밀번호 업데이트
$sql = "UPDATE 회원_게스트 SET 비밀번호='$hashed_password' WHERE 이메일='$email'";

if ($conn->query($sql) === TRUE) {
    echo "<p>비밀번호가 재설정되었습니다.</p>";
    echo '<a href="login_form.php"><button>로그인하기</button></a>';
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// 연결 종료
$conn->close();
?>
