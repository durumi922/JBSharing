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
$name = isset($_POST['name']) ? $_POST['name'] : '';
$birth_date = isset($_POST['birth_date']) ? $_POST['birth_date'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$user_password = isset($_POST['password']) ? $_POST['password'] : '';

// 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 생년월일 변환
if (preg_match("/^\d{6}$/", $birth_date)) {
    $birth_year = substr($birth_date, 0, 2);
    $birth_month = substr($birth_date, 2, 2);
    $birth_day = substr($birth_date, 4, 2);
    $birthdate = "20$birth_year-$birth_month-$birth_day"; // 2000년대 이후 출생자로 가정
} else {
    echo "생년월일 형식이 잘못되었습니다.";
    exit;
}

// 마지막 id회원 값을 가져와서 다음 id회원 값을 생성
$last_id_query = "SELECT id회원 FROM 회원_게스트 ORDER BY id회원 DESC LIMIT 1";
$result = $conn->query($last_id_query);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_id = $row['id회원'];
    $numeric_id = intval(substr($last_id, 1)) + 1;
    $new_id = 'G' . strval($numeric_id);
} else {
    $new_id = 'G121';
}

// 이메일 중복 검사
$check_query = "SELECT * FROM 회원_게스트 WHERE 이메일='$email'";
$result = $conn->query($check_query);
if ($result->num_rows > 0) {
    echo "<p>해당 이메일로 가입된 사용자가 있습니다.</p>";
    echo '<a href="login_form.php"><button>로그인하기</button></a> ';
    echo '<a href="forgot_password.php"><button>비밀번호 찾기</button></a>';
    exit;
}

// 사용자 입력 값을 이용하여 회원가입 처리
$sql = "INSERT INTO 회원_게스트 (id회원, 이름, 생년월일, 전화번호, 이메일, 비밀번호, 가입날짜) VALUES ('$new_id', '$name', '$birthdate', '$phone', '$email', '$user_password', NOW())";

if ($conn->query($sql) === TRUE) {
    echo "<p>회원가입이 완료되었습니다!</p>";
    echo "<script>alert('회원가입이 완료되었습니다!'); window.location.href='homepage.php';</script>";
    echo '<a href="homepage.php"><button>숙소 보러가기</button></a>';
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// 연결 종료
$conn->close();
?>
