<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// タイムゾーンをAsia/Tokyoに設定
date_default_timezone_set('Asia/Tokyo');

$user_id = $_SESSION['user_id']; // Make sure to set this when the user logs in

// Handle reservation deletion
if (isset($_POST['delete_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $delete_sql = "DELETE FROM reservations WHERE id = '$reservation_id' AND user_id = '$user_id'";
    if ($conn->query($delete_sql) === TRUE) {
        $_SESSION['message'] = "Reservation deleted successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error deleting reservation: " . $conn->error;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle reservation update
if (isset($_POST['update_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $new_date = $_POST['date'];
    $new_hour = $_POST['hour'];
    $new_minute = $_POST['minute'];
    $new_time = "$new_date $new_hour:$new_minute:00";
    $end_time = date('Y-m-d H:i:s', strtotime($new_time . ' +30 minutes'));

    // 現在の日時を取得し、30分単位に切り上げ
    $now = date('Y-m-d H:i:s');
    $rounded_now = date('Y-m-d H:i:s', ceil(strtotime($now) / (30 * 60)) * 30 * 60);

    // 過去の時間を予約しようとした場合のチェック
    if ($new_time < $rounded_now) {
        $_SESSION['message'] = "Request denied. Requesting time is past.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Check for existing reservation in the same time slot for any teacher
        $check_sql = "SELECT * FROM reservations WHERE reservation_time < '$end_time' AND DATE_ADD(reservation_time, INTERVAL 30 MINUTE) > '$new_time'";
        $result = $conn->query($check_sql);

        if ($result->num_rows == 0) {
            $update_sql = "UPDATE reservations SET reservation_time = '$new_time' WHERE id = '$reservation_id' AND user_id = '$user_id'";
            if ($conn->query($update_sql) === TRUE) {
                $_SESSION['message'] = "Reservation updated successfully!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['message'] = "Error updating reservation: " . $conn->error;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $_SESSION['message'] = "The selected time slot is already booked.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reserve'])) {
    // フォームからのデータを取得
    $date = $_POST['date'];
    $hour = $_POST['hour'];
    $minute = $_POST['minute'];
    $teacher = $_POST['teacher'];
    
    // 日時を組み立てる
    $reservation_time = "$date $hour:$minute:00";
    $end_time = date('Y-m-d H:i:s', strtotime($reservation_time . ' +30 minutes'));

    // 現在の日時を取得し、30分単位に切り上げ
    $now = date('Y-m-d H:i:s');
    $rounded_now = date('Y-m-d H:i:s', ceil(strtotime($now) / (30 * 60)) * 30 * 60);

    // 過去の時間を予約しようとした場合のチェック
    if ($reservation_time < $rounded_now) {
        $_SESSION['message'] = "Request denied. Requesting time is past.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Check for existing reservation with the same teacher within one hour
        $check_sql = "SELECT * FROM reservations 
                      WHERE teacher = '$teacher' 
                      AND reservation_time BETWEEN DATE_SUB('$reservation_time', INTERVAL 1 HOUR) 
                      AND DATE_ADD('$end_time', INTERVAL 1 HOUR)
                      AND user_id = '$user_id'";
        $result = $conn->query($check_sql);

        if ($result->num_rows == 0) {
            $sql = "INSERT INTO reservations (user_id, teacher, reservation_time) VALUES ('$user_id', '$teacher', '$reservation_time')";

            if ($conn->query($sql) === TRUE) {
                $_SESSION['message'] = "Reservation successful!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['message'] = "Error: " . $sql . "<br>" . $conn->error;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $_SESSION['message'] = "You cannot book consecutive sessions with the same teacher.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}


// 現在の日時を取得
$now = date('Y-m-d H:i:s');

// データベースから予約を取得し、現在の予約と過去の予約に分類
$current_reservations = [];
$previous_reservations = [];
$sql = "SELECT * FROM reservations WHERE user_id = '$user_id' ORDER BY reservation_time DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row['reservation_time'] >= $now) {
            $current_reservations[] = $row;
        } else {
            $previous_reservations[] = $row;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Lesson Reservation</title>
    <style>
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: white;
            border: 1px solid black;
            z-index: 1000;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        body {
            background-color: #cccccc; /* ページの背景をグレーに */
            color: #000000; /* 文字を黒に */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            background-color: #ffffff; /* コンテンツ部分の背景を白に */
            margin: 0px auto; /* 上下のマージンを50px、左右は自動で中央に配置 */
            padding: 20px;
            max-width: 800px; /* コンテンツ部分の最大幅を設定 */
            border-radius: 0px; /* コンテンツ部分の角を丸くする */
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); /* コンテンツ部分に影をつける */
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Reserve a Lesson</h2>
    <?php if (isset($_SESSION['message'])): ?>
        <p><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <input type="hidden" name="reserve" value="1">
        
        <label for="date">Date:</label>
        <input type="date" name="date" required><br>
        
        <label for="hour">Time:</label>
        <input type="number" name="hour" min="0" max="23" required>
        
        <label for="minute">:</label>
        <select name="minute" required>
            <option value="00">00</option>
            <option value="30">30</option>
        </select><br>
        
        Teacher: <select name="teacher" required>
            <option value="">--Please choose a teacher--</option>
            <option value="Smith">Smith</option>
            <option value="Tom">Tom</option>
            <option value="David">David</option>
            <option value="Mat">Mat</option>
            <option value="Oscar">Oscar</option>
            <option value="Bob">Bob</option>
        </select><br><br>
        <input type="submit" value="Reserve">
    </form>

    <h2>Your Reservations</h2>
    <table border="1">
        <tr>
            <th>Teacher</th>
            <th>Time</th>
            <th>Action</th>
        </tr>
        <?php foreach ($current_reservations as $reservation): ?>
            <tr>
                <td><?php echo $reservation['teacher']; ?></td>
                <td><?php echo $reservation['reservation_time']; ?></td>
                <td><a href="#" onclick="editReservation(<?php echo $reservation['id']; ?>, '<?php echo $reservation['reservation_time']; ?>')">Edit</a></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Your Previous Reservations</h2>
    <table border="1">
        <tr>
            <th>Teacher</th>
            <th>Time</th>
            <th>Report</th>
        </tr>
        <?php foreach ($previous_reservations as $reservation): ?>
            <tr>
                <td><?php echo $reservation['teacher']; ?></td>
                <td><?php echo $reservation['reservation_time']; ?></td>
                <td></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="overlay" id="overlay"></div>
<div class="popup" id="popup">
    <h2>Edit Reservation</h2>
    <form method="post" action="">
        <input type="hidden" name="reservation_id" id="edit_reservation_id">
        <label for="edit_date">Date:</label>
        <input type="date" name="date" id="edit_date" required><br>
        <label for="edit_hour">Hour:</label>
        <input type="number" name="hour" id="edit_hour" min="0" max="23" required><br>
        <label for="edit_minute">Minute:</label>
        <select name="minute" id="edit_minute" required>
            <option value="00">00</option>
            <option value="30">30</option>
        </select><br><br>
        <input type="submit" name="update_reservation" value="Update">
    </form>
    <!-- Form for deleting the reservation -->
    <form method="post" action="">
        <input type="hidden" name="reservation_id" id="delete_reservation_id">
        <input type="submit" name="delete_reservation" value="Delete">
    </form>
    <button onclick="closePopup()">Close</button>
</div>

<script>
    function editReservation(id, time) {
        var date = time.split(' ')[0];
        var timeParts = time.split(' ')[1].split(':');
        var hour = timeParts[0];
        var minute = timeParts[1];

        document.getElementById('edit_reservation_id').value = id;
        document.getElementById('edit_date').value = date;
        document.getElementById('edit_hour').value = hour;
        document.getElementById('edit_minute').value = minute;
        document.getElementById('delete_reservation_id').value = id; // Set reservation_id for delete

        document.getElementById('popup').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    function closePopup() {
        document.getElementById('popup').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }
</script>
</div>
</body>
</html>
