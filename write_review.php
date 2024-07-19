<?php
// 오류 보고 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 세션 시작
session_start();

// 게스트 로그인 여부 확인
if (!isset($_SESSION['id회원'])) {
    echo "<script>alert('로그인 후 리뷰를 작성할 수 있습니다.'); window.location.href='login_form.php';</script>";
    exit();
}

// 데이터베이스 연결
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP에서 설정한 비밀번호
$dbname = "pbl2";
$port = 3306; // XAMPP에서 사용하는 MySQL 포트

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id회원 = $_SESSION['id회원'];
$id숙소 = isset($_GET['id숙소']) ? $_GET['id숙소'] : '';

// 예약 상태 확인
$sql = "SELECT * FROM 예약 WHERE id회원 = ? AND id숙소 = ? AND 예약상태 = '체크아웃 완료'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $id회원, $id숙소);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "<script>alert('리뷰를 작성할 권한이 없습니다.'); window.location.href='homepage.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $별점 = $_POST['별점'];
    $리뷰내용 = $_POST['리뷰내용'];
    $작성일자 = date('Y-m-d');

    // 리뷰 작성
    $sql = "INSERT INTO 숙소리뷰 (id숙소리뷰, id예약, id숙소, id회원, 별점, 숙소리뷰_내용, 작성일자) VALUES (UUID(), ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $id예약, $id숙소, $id회원, $별점, $리뷰내용, $작성일자);
    if ($stmt->execute()) {
        echo "<script>alert('리뷰가 작성되었습니다.'); window.location.href='homepage.php';</script>";
    } else {
        echo "<script>alert('리뷰 작성에 실패했습니다.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>리뷰 작성</title>
</head>
<body>
    <h1>리뷰 작성</h1>
    <form method="POST" action="">
        <label for="별점">별점:</label>
        <select id="별점" name="별점" required>
            <option value="5">★★★★★</option>
            <option value="4">★★★★☆</option>
            <option value="3">★★★☆☆</option>
            <option value="2">★★☆☆☆</option>
            <option value="1">★☆☆☆☆</option>
        </select>
        <br>
        <label for="리뷰내용">리뷰 내용:</label>
        <textarea id="리뷰내용" name="리뷰내용" required></textarea>
        <br>
        <button type="submit">리뷰 작성</button>
    </form>
</body>
</html>
<?php
$conn->close();
?>
