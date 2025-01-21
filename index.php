<?php
// 데이터베이스 연결 설정
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'password');
define('DB_NAME', 'database_name');

// 유틸리티 함수들
class Utilities {
    // 데이터베이스 연결
    public static function connectDB() {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
    
    // XSS 방지를 위한 문자열 정화
    public static function sanitizeString($str) {
        return htmlspecialchars(strip_tags($str));
    }
}

// 사용자 클래스
class User {
    private $id;
    private $name;
    private $email;
    
    public function __construct($name, $email) {
        $this->name = $name;
        $this->email = $email;
    }
    
    // 사용자 저장
    public function save() {
        $conn = Utilities::connectDB();
        $name = Utilities::sanitizeString($this->name);
        $email = Utilities::sanitizeString($this->email);
        
        $sql = "INSERT INTO users (name, email) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $email);
        
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        
        return $result;
    }
}

// POST 요청 처리 예시
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if (!empty($name) && !empty($email)) {
        $user = new User($name, $email);
        if ($user->save()) {
            echo "사용자가 성공적으로 저장되었습니다.";
        } else {
            echo "저장 중 오류가 발생했습니다.";
        }
    }
}

// 간단한 폼 출력
?>
<!DOCTYPE html>
<html>
<head>
    <title>사용자 등록</title>
</head>
<body>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <div>
            <label for="name">이름:</label>
            <input type="text" name="name" id="name" required>
        </div>
        <div>
            <label for="email">이메일:</label>
            <input type="email" name="email" id="email" required>
        </div>
        <button type="submit">등록</button>
    </form>
</body>
</html>
