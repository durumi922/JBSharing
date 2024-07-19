<?php
// 오류 보고 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 세션 시작
session_start();

// 로그아웃 처리
if (isset($_GET['logout'])) {
    session_destroy();
    echo "<script>alert('로그아웃 되었습니다!'); window.location.href='homepage.php';</script>";
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

// 기본시설 가져오기
$sql_basic_facilities = "SELECT * FROM 기본시설";
$result_basic_facilities = $conn->query($sql_basic_facilities);
if (!$result_basic_facilities) {
    die("Query failed: " . $conn->error);
}

// 편의시설 가져오기
$sql_conveniences = "SELECT * FROM 편의시설";
$result_conveniences = $conn->query($sql_conveniences);
if (!$result_conveniences) {
    die("Query failed: " . $conn->error);
}

// 검색 조건 처리
$search_location = isset($_GET['travel-location']) ? $_GET['travel-location'] : '';
$checkin_date = isset($_GET['checkin-date']) ? $_GET['checkin-date'] : '';
$checkout_date = isset($_GET['checkout-date']) ? $_GET['checkout-date'] : '';
$traveler_count = isset($_GET['traveler-count']) ? $_GET['traveler-count'] : '';
$basic_facilities = isset($_GET['basic_facilities']) ? $_GET['basic_facilities'] : [];
$conveniences = isset($_GET['conveniences']) ? $_GET['conveniences'] : [];

// 기본 SQL 쿼리
$sql = "SELECT 숙소.id숙소, 숙소.숙소이름, 숙소.숙소위치, 숙소.가격_1박, 회원_게스트.이름 AS 호스트이름, 숙소.평점, 숙소사진.이미지주소경로 AS 이미지주소경로,
        (SELECT COUNT(*) FROM 숙소리뷰 WHERE 숙소리뷰.id숙소 = 숙소.id숙소) AS 리뷰개수
        FROM 숙소 
        LEFT JOIN 호스트_숙소 ON 숙소.id숙소 = 호스트_숙소.id숙소
        LEFT JOIN 호스트 ON 호스트_숙소.id호스트 = 호스트.id호스트
        LEFT JOIN 회원_게스트 ON 호스트.id회원 = 회원_게스트.id회원
        LEFT JOIN 숙소사진 ON 숙소.id숙소 = 숙소사진.id숙소
        WHERE 1=1";

// 조건에 맞는 숙소를 찾기 위한 WHERE 절 추가
$conditions = [];
$param_types = "";
$param_values = [];

// 여행지 조건
if ($search_location) {
    $conditions[] = "숙소.숙소위치 LIKE CONCAT('%', ?, '%')";
    $param_types .= "s";
    $param_values[] = $search_location;
}

// 체크인, 체크아웃 조건
if ($checkin_date && $checkout_date) {
    $conditions[] = "숙소.id숙소 NOT IN (
                SELECT id숙소
                FROM 숙소휴일
                WHERE (시작일 <= ? AND 종료일 >= ?)
                   OR (시작일 <= ? AND 종료일 >= ?)
                   OR (시작일 >= ? AND 종료일 <= ?)
              )";
    $param_types .= "ssssss";
    $param_values[] = $checkout_date;
    $param_values[] = $checkin_date;
    $param_values[] = $checkin_date;
    $param_values[] = $checkout_date;
    $param_values[] = $checkin_date;
    $param_values[] = $checkout_date;
}

// 여행자 수 조건
if ($traveler_count) {
    $conditions[] = "숙소.수용가능인원 >= ?";
    $param_types .= "i";
    $param_values[] = $traveler_count;
}

// 기본시설 조건 추가 (침실, 침대, 욕실)
$facility_types = ['침실', '침대', '욕실'];
foreach ($facility_types as $facility_type) {
    if (isset($basic_facilities[$facility_type]) && $basic_facilities[$facility_type] > 0) {
        $conditions[] = "숙소.id숙소 IN (
                    SELECT 숙소_기본시설.id숙소
                    FROM 숙소_기본시설
                    LEFT JOIN 기본시설 ON 숙소_기본시설.id기본시설 = 기본시설.id기본시설
                    WHERE 기본시설.기본시설명 = ? AND 숙소_기본시설.개수 >= ?
                  )";
        $param_types .= "si";
        $param_values[] = $facility_type;
        $param_values[] = $basic_facilities[$facility_type];
    }
}

// 편의시설 조건 추가 (모든 선택한 편의시설을 포함하는 숙소 검색)
if (!empty($conveniences)) {
    $placeholders = implode(',', array_fill(0, count($conveniences), '?'));
    $conditions[] = "숙소.id숙소 IN (
                SELECT 숙소_편의시설.id숙소
                FROM 숙소_편의시설
                WHERE 숙소_편의시설.id편의시설 IN ($placeholders)
                GROUP BY 숙소_편의시설.id숙소
                HAVING COUNT(DISTINCT 숙소_편의시설.id편의시설) = ?
              )";
    $param_types .= str_repeat('s', count($conveniences)) . 'i';
    $param_values = array_merge($param_values, $conveniences, [count($conveniences)]);
}

if (!empty($conditions)) {
    $sql .= " AND (" . implode(" AND ", $conditions) . ")";
}

$sql .= " GROUP BY 숙소.id숙소";

$stmt = $conn->prepare($sql);

if (!empty($param_values)) {
    $stmt->bind_param($param_types, ...$param_values);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
} else {
    die("Query failed: " . $stmt->error);
}

$all_criteria_selected = $search_location && $checkin_date && $checkout_date && $traveler_count && !empty($basic_facilities) && !empty($conveniences);

// 대체 숙소 쿼리
if ($result->num_rows == 0) {
    // 대체 숙소 조건
    $alternative_conditions = [];
    $alternative_param_types = "";
    $alternative_param_values = [];

    // 여행지 조건
    if ($search_location) {
        $alternative_conditions[] = "숙소.숙소위치 LIKE CONCAT('%', ?, '%')";
        $alternative_param_types .= "s";
        $alternative_param_values[] = $search_location;
    }

    // 예약 상태, 휴일 상태 조건
    if ($checkin_date && $checkout_date) {
        $alternative_conditions[] = "숙소.id숙소 NOT IN (
                    SELECT id숙소
                    FROM 숙소휴일
                    WHERE (시작일 <= ? AND 종료일 >= ?)
                       OR (시작일 <= ? AND 종료일 >= ?)
                       OR (시작일 >= ? AND 종료일 <= ?)
                  )";
        $alternative_param_types .= "ssssss";
        $alternative_param_values[] = $checkout_date;
        $alternative_param_values[] = $checkin_date;
        $alternative_param_values[] = $checkin_date;
        $alternative_param_values[] = $checkout_date;
        $alternative_param_values[] = $checkin_date;
        $alternative_param_values[] = $checkout_date;
    }

    // 기본시설 조건 (적게 가지고 있는 경우)
    foreach ($facility_types as $facility_type) {
        if (isset($basic_facilities[$facility_type]) && $basic_facilities[$facility_type] > 0) {
            $alternative_conditions[] = "숙소.id숙소 NOT IN (
                        SELECT 숙소_기본시설.id숙소
                        FROM 숙소_기본시설
                        LEFT JOIN 기본시설 ON 숙소_기본시설.id기본시설 = 기본시설.id기본시설
                        WHERE 기본시설.기본시설명 = ? AND 숙소_기본시설.개수 >= ?
                      )";
            $alternative_param_types .= "si";
            $alternative_param_values[] = $facility_type;
            $alternative_param_values[] = $basic_facilities[$facility_type];
        }
    }

    // 편의시설 조건 (하나라도 포함하지 않는 경우)
    if (!empty($conveniences)) {
        $placeholders = implode(',', array_fill(0, count($conveniences), '?'));
        $alternative_conditions[] = "숙소.id숙소 NOT IN (
                    SELECT 숙소_편의시설.id숙소
                    FROM 숙소_편의시설
                    WHERE 숙소_편의시설.id편의시설 IN ($placeholders)
                    GROUP BY 숙소_편의시설.id숙소
                    HAVING COUNT(DISTINCT 숙소_편의시설.id편의시설) = ?
                  )";
        $alternative_param_types .= str_repeat('s', count($conveniences)) . 'i';
        $alternative_param_values = array_merge($alternative_param_values, $conveniences, [count($conveniences)]);
    }

    $alternative_sql = "SELECT 숙소.id숙소, 숙소.숙소이름, 숙소.숙소위치, 숙소.가격_1박, 회원_게스트.이름 AS 호스트이름, 숙소.평점, MIN(숙소사진.이미지주소경로) AS 이미지주소경로,
                        (SELECT COUNT(*) FROM 숙소리뷰 WHERE 숙소리뷰.id숙소 = 숙소.id숙소) AS 리뷰개수
                        FROM 숙소 
                        LEFT JOIN 호스트_숙소 ON 숙소.id숙소 = 호스트_숙소.id숙소
                        LEFT JOIN 호스트 ON 호스트_숙소.id호스트 = 호스트.id호스트
                        LEFT JOIN 회원_게스트 ON 호스트.id회원 = 회원_게스트.id회원
                        LEFT JOIN 숙소사진 ON 숙소.id숙소 = 숙소사진.id숙소";

    if (!empty($alternative_conditions)) {
        $alternative_sql .= " WHERE (" . implode(" OR ", $alternative_conditions) . ")";
    }

    $alternative_sql .= " GROUP BY 숙소.id숙소";

    $alternative_stmt = $conn->prepare($alternative_sql);
    $alternative_stmt->bind_param($alternative_param_types, ...$alternative_param_values);

    if ($alternative_stmt->execute()) {
        $alternative_result = $alternative_stmt->get_result();
    } else {
        die("Query failed: " . $alternative_stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>홈페이지</title>
    <script src="https://kit.fontawesome.com/b2a6848219.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4NgSiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="homepage.css">
    <style>
        .dropdown:hover .dropdown-content,
        .traveler-select:hover .traveler-dropdown {
            display: block;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="main_icon2.png" alt="Logo">
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
    <div class="banner">
        <img src="logo2color.png" alt="Banner Image">
    </div>
    <div class="content">
        <form method="GET" action="homepage.php" class="dropdown-container">
            <div class="dropdown">
                <label for="travel-location">여행지</label>
                <input type="text" id="travel-location" name="travel-location" readonly value="<?php echo htmlspecialchars($search_location); ?>">
                <div class="dropdown-content">
                    <a href="#" class="bold">한국</a>
                    <div class="cities">
                        <a href="#" onclick="setLocation('서울')">서울</a>
                        <a href="#" onclick="setLocation('경기도')">경기도</a>
                        <a href="#" onclick="setLocation('부산')">부산</a>
                        <a href="#" onclick="setLocation('강원도')">강원도</a>
                        <a href="#" onclick="setLocation('제주')">제주</a>
                        <a href="#" onclick="setLocation('')">전국</a>
                    </div>
                </div>
            </div>
            <div class="dropdown">
                <label for="checkin-date">체크인 날짜</label>
                <input type="date" id="checkin-date" name="checkin-date" value="<?php echo htmlspecialchars($checkin_date); ?>">
            </div>
            <div class="dropdown">
                <label for="checkout-date">체크아웃 날짜</label>
                <input type="date" id="checkout-date" name="checkout-date" value="<?php echo htmlspecialchars($checkout_date); ?>">
            </div>
            <div class="dropdown traveler-select">
                <label for="traveler-select-button">여행자</label>
                <input type="text" id="traveler-select-button" name="traveler-count" class="traveler-select-button" readonly value="<?php echo htmlspecialchars($traveler_count); ?>">
                <div class="traveler-dropdown">
                    <div class="traveler-option">
                        <span>인원수</span>
                        <button type="button" onclick="decreaseTraveler('adult')">-</button>
                        <span id="adult-count"><?php echo htmlspecialchars($traveler_count); ?></span>
                        <button type="button" onclick="increaseTraveler('adult')">+</button>
                    </div>
                    <button type="button" onclick="confirmTravelers()">확인</button>
                </div>
            </div>
            <div class="dropdown">
                <label for="basic-facilities-select">기본 시설</label>
                <input type="text" id="basic-facilities-select" name="basic-facilities-select" readonly value="<?php echo implode(', ', array_keys($basic_facilities)); ?>">
                <div class="dropdown-content grid-container">
                    <div class="grid-row">
                        <?php while ($row = $result_basic_facilities->fetch_assoc()): ?>
                            <div class="grid-item">
                                <label for="basic_facility_<?php echo $row['id기본시설']; ?>"><?php echo $row['기본시설명']; ?></label>
                                <input type="number" id="basic_facility_<?php echo $row['id기본시설']; ?>" name="basic_facilities[<?php echo $row['기본시설명']; ?>]" min="0" max="9" value="<?php echo isset($basic_facilities[$row['기본시설명']]) ? htmlspecialchars($basic_facilities[$row['기본시설명']]) : 0; ?>">
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <div class="dropdown">
                <label for="conveniences-select">편의 시설</label>
                <input type="text" id="conveniences-select" name="conveniences-select" readonly value="<?php echo implode(', ', $conveniences); ?>">
                <div class="dropdown-content grid-container">
                    <div class="grid-row">
                        <?php $count = 0; ?>
                        <?php while ($row = $result_conveniences->fetch_assoc()): ?>
                            <div class="grid-item">
                                <label for="convenience_<?php echo $row['id편의시설']; ?>"><?php echo $row['편의시설명']; ?></label>
                                <input type="checkbox" id="convenience_<?php echo $row['id편의시설']; ?>" name="conveniences[]" value="<?php echo $row['id편의시설']; ?>" <?php echo in_array($row['id편의시설'], $conveniences) ? 'checked' : ''; ?>>
                            </div>
                            <?php if (++$count % 3 == 0): ?>
                                </div><div class="grid-row">
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="search-button" onclick="saveFormData()"><i class="fas fa-search"></i></button>
        </form>

        <!-- 숙소 정보 표시 섹션 -->
        <div class="accommodations">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="accommodation">
                        <a href="details.php?id=<?php echo $row['id숙소']; ?>">
                            <img src="<?php echo htmlspecialchars($row['이미지주소경로']); ?>" alt="<?php echo htmlspecialchars($row['숙소이름']); ?>">
                            <div class="info">
                                <h3><?php echo $row['숙소이름']; ?></h3>
                                <p><?php echo $row['숙소위치']; ?></p>
                                <p>₩<?php echo number_format($row['가격_1박']); ?> / 박</p>
                                <p>호스트 <?php echo $row['호스트이름']; ?>님</p>
                                <p>평점 <?php echo ($row['평점'] !== null) ? number_format($row['평점'], 1) : '평가 없음'; ?></p>
                                <p>리뷰 <?php echo $row['리뷰개수']; ?>개</p>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <?php if ($all_criteria_selected): ?>
                    <p>검색 조건에 만족하는 숙소가 없습니다. 아래는 대체 숙소 목록입니다.</p>
                    <?php if (isset($alternative_result) && $alternative_result->num_rows > 0): ?>
                        <?php while($row = $alternative_result->fetch_assoc()): ?>
                            <div class="accommodation">
                                <a href="details.php?id=<?php echo $row['id숙소']; ?>">
                                    <img src="<?php echo htmlspecialchars($row['이미지주소경로']); ?>" alt="<?php echo htmlspecialchars($row['숙소이름']); ?>">
                                    <div class="info">
                                        <h3><?php echo $row['숙소이름']; ?></h3>
                                        <p><?php echo $row['숙소위치']; ?></p>
                                        <p>₩<?php echo number_format($row['가격_1박']); ?> / 박</p>
                                        <p>호스트 <?php echo $row['호스트이름']; ?>님</p>
                                        <p>평점 <?php echo ($row['평점'] !== null) ? number_format($row['평점'], 1) : '평가 없음'; ?></p>
                                        <p>리뷰 <?php echo $row['리뷰개수']; ?>개</p>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>대체 숙소가 없습니다.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p>선택하신 여행지의 숙소 목록입니다.</p>
                <?php endif; ?>
            <?php endif; ?>
            <?php $conn->close(); ?>
        </div>
    </div>
    <script>
        function setLocation(location) {
            document.getElementById('travel-location').value = location;
        }

        function increaseTraveler(type) {
            var countElement = document.getElementById(type + '-count');
            var count = parseInt(countElement.textContent) || 0; // 추가
            count++;
            countElement.textContent = count;
            document.getElementById('traveler-select-button').value = count;
        }

        function decreaseTraveler(type) {
            var countElement = document.getElementById(type + '-count');
            var count = parseInt(countElement.textContent) || 0; // 추가
            if (count > 0) {
                count--;
                countElement.textContent = count;
                document.getElementById('traveler-select-button').value = count;
            }
        }

        function confirmTravelers() {
            var adultCount = document.getElementById('adult-count').textContent;
            var travelerText = '';
            if (adultCount > 0) travelerText += '성인 ' + adultCount + '명';
            document.getElementById('traveler-select-button').value = travelerText;
        }

        document.querySelectorAll('.dropdown-content.grid-container input[type="checkbox"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var selected = Array.from(document.querySelectorAll('.dropdown-content.grid-container input[type="checkbox"]:checked')).map(function(el) {
                    return el.nextElementSibling.textContent;
                }).join(', ');
                document.getElementById('conveniences-select').value = selected;
            });
        });

        function saveFormData() {
            const form = document.querySelector('form');
            const formData = new FormData(form);
            const searchParams = new URLSearchParams();

            for (const pair of formData) {
                searchParams.append(pair[0], pair[1]);
            }

            history.replaceState(null, '', '?' + searchParams.toString());
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchParams = new URLSearchParams(window.location.search);

            if (searchParams.has('travel-location')) {
                document.getElementById('travel-location').value = searchParams.get('travel-location');
            }
            if (searchParams.has('checkin-date')) {
                document.getElementById('checkin-date').value = searchParams.get('checkin-date');
            }
            if (searchParams.has('checkout-date')) {
                document.getElementById('checkout-date').value = searchParams.get('checkout-date');
            }
            if (searchParams.has('traveler-count')) {
                const travelerCount = searchParams.get('traveler-count');
                document.getElementById('traveler-select-button').value = travelerCount;
                document.getElementById('adult-count').textContent = travelerCount;
            }
            if (searchParams.has('basic-facilities-select')) {
                const basicFacilities = searchParams.get('basic-facilities-select').split(', ');
                basicFacilities.forEach(function(facility) {
                    const input = document.querySelector(`input[name="basic_facilities[${facility}]"]`);
                    if (input) {
                        input.value = basicFacilities[facility];
                    }
                });
            }
            if (searchParams.has('conveniences-select')) {
                const conveniences = searchParams.get('conveniences-select').split(', ');
                conveniences.forEach(function(convenience) {
                    const input = document.querySelector(`input[name="conveniences[]"][value="${convenience}"]`);
                    if (input) {
                        input.checked = true;
                    }
                });
                document.getElementById('conveniences-select').value = conveniences.join(', ');
            }
        });
    </script>
</body>
</html>
