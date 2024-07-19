<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 찾기</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0e0ff;
            text-align: center;
            padding: 50px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px #000;
            display: inline-block;
        }
        .logo {
            width: 100px;
            height: 100px;
        }
        h2 {
            color: #333;
        }
        .button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="main_icon.png" alt="Logo" class="logo">
        <h2>비밀번호 찾기</h2>
        <form action="retrieve_password.php" method="post">
            가입한 이메일 주소: <input type="email" name="email" required><br><br>
            성명: <input type="text" name="name" required><br><br>
            생년월일: 
            <select name="birth_year" required>
                <?php
                    for ($year = 1900; $year <= 2005; $year++) {
                        echo "<option value=\"$year\">$year</option>";
                    }
                ?>
            </select>년
            <select name="birth_month" required>
                <?php
                    for ($month = 1; $month <= 12; $month++) {
                        echo "<option value=\"$month\">$month</option>";
                    }
                ?>
            </select>월
            <select name="birth_day" required>
                <?php
                    for ($day = 1; $day <= 31; $day++) {
                        echo "<option value=\"$day\">$day</option>";
                    }
                ?>
            </select>일
            <br><br>
            전화번호: <input type="text" name="phone" required><br><br>
            <input type="submit" value="비밀번호 찾기" class="button">
        </form>
    </div>
</body>
</html>
