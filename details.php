<?php
// 오류 보고 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 세션 시작
session_start();

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

// id숙소가 제공되지 않은 경우 홈으로 리다이렉션
if (!isset($_GET['id'])) {
    header("Location: homepage.php");
    exit();
}

$id숙소 = $_GET['id'];

// 숙소 정보 쿼리
$sql = "SELECT 숙소.*, 호스트.id호스트, 호스트.운영날짜, 회원_게스트.이름 AS 호스트이름, 회원_게스트.이메일 AS 호스트이메일
        FROM 숙소
        LEFT JOIN 호스트_숙소 ON 숙소.id숙소 = 호스트_숙소.id숙소
        LEFT JOIN 호스트 ON 호스트_숙소.id호스트 = 호스트.id호스트
        LEFT JOIN 회원_게스트 ON 호스트.id회원 = 회원_게스트.id회원
        WHERE 숙소.id숙소 = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id숙소);
$stmt->execute();
$stmt->bind_result($숙소_id, $숙소_이름, $숙소_위치, $수용가능인원, $가격_1박, $건물유형, $숙소설명, $평점, $호스트_id, $운영날짜, $호스트이름, $호스트이메일);
$stmt->fetch();
$stmt->close();

// 경력 계산
$운영날짜_obj = new DateTime($운영날짜);
$현재날짜 = new DateTime();
$interval = $운영날짜_obj->diff($현재날짜);
$경력 = $interval->y . "년 " . $interval->m . "개월";

// 이미지 쿼리
$sql = "SELECT 이미지주소경로 FROM 숙소사진 WHERE id숙소 = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id숙소);
$stmt->execute();
$stmt->bind_result($이미지주소경로);
$이미지들 = [];
while ($stmt->fetch()) {
    $이미지들[] = $이미지주소경로;
}
$stmt->close();

// 리뷰 쿼리
$sql_reviews = "SELECT 숙소리뷰.*, 회원_게스트.이름 AS 리뷰작성자
                FROM 숙소리뷰
                LEFT JOIN 회원_게스트 ON 숙소리뷰.id회원 = 회원_게스트.id회원
                WHERE 숙소리뷰.id숙소 = ?";
$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->bind_param("s", $id숙소);
$stmt_reviews->execute();
$stmt_reviews->bind_result($리뷰_id, $리뷰_예약, $리뷰_숙소, $리뷰_회원, $별점, $리뷰_내용, $작성일자, $리뷰작성자);
$리뷰들 = [];
while ($stmt_reviews->fetch()) {
    $리뷰들[] = [
        '리뷰작성자' => $리뷰작성자,
        '별점' => $별점,
        '숙소리뷰_내용' => $리뷰_내용,
        '작성일자' => $작성일자
    ];
}
$stmt_reviews->close();

// 리뷰 평균 별점 계산
$sum_of_ratings = 0;
$num_of_ratings = count($리뷰들);

foreach ($리뷰들 as $리뷰) {
    $sum_of_ratings += $리뷰['별점'];
}

$average_rating = $num_of_ratings > 0 ? $sum_of_ratings / $num_of_ratings : 0;

// 기본시설 쿼리
$sql_basic_facilities = "SELECT 기본시설.기본시설명, 숙소_기본시설.개수
                         FROM 숙소_기본시설
                         LEFT JOIN 기본시설 ON 숙소_기본시설.id기본시설 = 기본시설.id기본시설
                         WHERE 숙소_기본시설.id숙소 = ?";
$stmt_basic_facilities = $conn->prepare($sql_basic_facilities);
$stmt_basic_facilities->bind_param("s", $id숙소);
$stmt_basic_facilities->execute();
$stmt_basic_facilities->bind_result($기본시설명, $개수);
$기본시설들 = [];
while ($stmt_basic_facilities->fetch()) {
    $기본시설들[] = ['기본시설명' => $기본시설명, '개수' => $개수];
}
$stmt_basic_facilities->close();

// 편의시설 쿼리
$sql_conveniences = "SELECT 편의시설.편의시설명
                     FROM 숙소_편의시설
                     LEFT JOIN 편의시설 ON 숙소_편의시설.id편의시설 = 편의시설.id편의시설
                     WHERE 숙소_편의시설.id숙소 = ?";
$stmt_conveniences = $conn->prepare($sql_conveniences);
$stmt_conveniences->bind_param("s", $id숙소);
$stmt_conveniences->execute();
$stmt_conveniences->bind_result($편의시설명);
$편의시설들 = [];
while ($stmt_conveniences->fetch()) {
    $편의시설들[] = $편의시설명;
}
$stmt_conveniences->close();

// 휴일 쿼리
$sql_holidays = "SELECT 시작일, 종료일 FROM 숙소휴일 WHERE id숙소 = ?";
$stmt_holidays = $conn->prepare($sql_holidays);
$stmt_holidays->bind_param("s", $id숙소);
$stmt_holidays->execute();
$stmt_holidays->bind_result($시작일, $종료일);
$휴일들 = [];
while ($stmt_holidays->fetch()) {
    $휴일들[] = ['시작일' => $시작일, '종료일' => $종료일];
}
$stmt_holidays->close();

// 예약된 날짜 쿼리
$sql_bookings = "SELECT 체크인_날짜, 체크아웃_날짜 FROM 예약 WHERE id숙소 = ?";
$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param("s", $id숙소);
$stmt_bookings->execute();
$stmt_bookings->bind_result($체크인_날짜, $체크아웃_날짜);
$예약들 = [];
while ($stmt_bookings->fetch()) {
    $예약들[] = ['체크인_날짜' => $체크인_날짜, '체크아웃_날짜' => $체크아웃_날짜];
}
$stmt_bookings->close();

$conn->close();
?>

<?php
// JSON으로 변환
$휴일_json = json_encode($휴일들);
$예약_json = json_encode($예약들);
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var holidays = <?php echo $휴일_json; ?>;
        var bookings = <?php echo $예약_json; ?>;

        var disabledDates = [];

        holidays.forEach(function(holiday) {
            var start = new Date(holiday['시작일']);
            var end = new Date(holiday['종료일']);
            while (start <= end) {
                disabledDates.push(new Date(start).toISOString().split('T')[0]);
                start.setDate(start.getDate() + 1);
            }
        });

        bookings.forEach(function(booking) {
            var start = new Date(booking['체크인_날짜']);
            var end = new Date(booking['체크아웃_날짜']);
            while (start <= end) {
                disabledDates.push(new Date(start).toISOString().split('T')[0]);
                start.setDate(start.getDate() + 1);
            }
        });

        flatpickr("#체크인_날짜", {
            dateFormat: "Y-m-d",
            disable: disabledDates,
            onChange: function(selectedDates, dateStr, instance) {
                flatpickr("#체크아웃_날짜", {
                    dateFormat: "Y-m-d",
                    disable: disabledDates,
                    minDate: dateStr
                });
            }
        });

        flatpickr("#체크아웃_날짜", {
            dateFormat: "Y-m-d",
            disable: disabledDates
        });
    });

    // 모달 스크립트
    function openModal(imageSrc) {
        document.getElementById("modalImage").src = imageSrc;
        document.getElementById("imageModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("imageModal").style.display = "none";
    }
</script>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($숙소_이름); ?> - 상세 정보</title>
    <link rel="stylesheet" href="details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <a href="homepage.php"><img src="main_icon2.png" alt="Logo"></a>
            <i class="fa-solid fa-house"></i>
        </div>
        <ul class="nav-btn">
            <?php if (isset($_SESSION['email'])): ?>
                <li><a href="mypage.php">마이페이지</a></li>
                <li><a href="homepage.php?logout=true">로그아웃</a></li>
            <?php else: ?>
                <li><a href="login_form.php">로그인</a></li>
                <li><a href="registration_form.php">회원가입</a></li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="content">
        <h1><?php echo htmlspecialchars($숙소_이름); ?></h1>
        <p><?php echo htmlspecialchars($숙소_위치); ?></p>
        <div class="image-container">
            <?php foreach ($이미지들 as $index => $이미지): ?>
                <img src="<?php echo htmlspecialchars($이미지); ?>" alt="<?php echo htmlspecialchars($숙소_이름); ?>" class="image-<?php echo $index + 1; ?>" onclick="openModal('<?php echo htmlspecialchars($이미지); ?>')">
            <?php endforeach; ?>
        </div>
        <div class="details">
            <h2>숙소 정보</h2>
            <p>수용 가능 인원: <?php echo htmlspecialchars($수용가능인원); ?>명</p>
            <p>가격(1박): <?php echo number_format($가격_1박); ?>원</p>
            <p>건물 유형: <?php echo htmlspecialchars($건물유형); ?></p>
            <p>숙소 설명: <?php echo htmlspecialchars($숙소설명); ?></p>
            <p>평점: <?php echo number_format($average_rating, 1); ?></p>
        </div>
        <div class="facilities">
            <div class="facility-list">
                <h3>기본 시설</h3>
                <ul>
                    <?php foreach ($기본시설들 as $기본시설): ?>
                        <li><?php echo htmlspecialchars($기본시설['기본시설명']); ?>: <?php echo htmlspecialchars($기본시설['개수']); ?>개</li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="facility-list">
                <h3>편의시설</h3>
                <ul>
                    <?php foreach ($편의시설들 as $편의시설): ?>
                        <li><?php echo htmlspecialchars($편의시설); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="host-info">
            <h2>호스트 정보</h2>
            <p>이름: <?php echo htmlspecialchars($호스트이름); ?></p>
            <p>이메일: <?php echo htmlspecialchars($호스트이메일); ?></p>
            <p>경력: <?php echo $경력; ?></p>
        </div>
        <div class="reservation">
            <form action="reservation.php" method="post">
                <input type="hidden" name="id숙소" value="<?php echo htmlspecialchars($id숙소); ?>">
                <label for="체크인_날짜">체크인 날짜:</label>
                <input type="text" id="체크인_날짜" name="체크인_날짜" required>
                <label for="체크아웃_날짜">체크아웃 날짜:</label>
                <input type="text" id="체크아웃_날짜" name="체크아웃_날짜" required>
                <label for="숙박인원">숙박 인원:</label>
                <input type="number" id="숙박인원" name="숙박인원" min="1" max="<?php echo htmlspecialchars($수용가능인원); ?>" required>
                <button type="submit">예약하기</button>
            </form>
        </div>
        <div class="reviews">
            <h2>리뷰</h2>
            <?php if (count($리뷰들) > 0): ?>
                <?php foreach ($리뷰들 as $리뷰): ?>
                    <div class="review">
                        <p><strong><?php echo htmlspecialchars($리뷰['리뷰작성자']); ?></strong> (별점: <?php echo htmlspecialchars($리뷰['별점']); ?>)</p>
                        <p><?php echo htmlspecialchars($리뷰['숙소리뷰_내용']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>작성된 리뷰가 없습니다.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 모달 -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 60px;
            left: 0;
            top: 0px;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.9);
        }

        .modal-content {
            margin: auto;
            margin-top: 70px;
            display: block;
            width: 80%;
            max-width: 700px;
        }

        .close {
            position: absolute;
            top: 70px;
            left: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
        }

        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</body>
</html>
