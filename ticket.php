<?php
$mysqli = new mysqli("localhost", "root", "", "ticket");
if ($mysqli->connect_error) die("DB 연결 실패: " . $mysqli->connect_error);
$mysqli->set_charset("utf8mb4");
date_default_timezone_set("Asia/Seoul");

$items = [
    ['name' => '입장권', 'child' => 7000, 'adult' => 10000, 'note' => '입장'],
    ['name' => 'BIG3', 'child' => 12000, 'adult' => 16000, 'note' => '입장+놀이3종'],
    ['name' => '자유이용권', 'child' => 21000, 'adult' => 26000, 'note' => '입장+놀이자유'],
    ['name' => '연간이용권', 'child' => 70000, 'adult' => 90000, 'note' => '입장+놀이자유']
];

function h($s) { return htmlspecialchars($s, ENT_QUOTES); }

$name = $resultText = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['qty'])) {
    $name = $_POST['username'];
    $now = date("Y년 m월 d일 A h:i분");
    $details = "";
    $total = 0;

    foreach ($_POST['qty'] as $index => $row) {
        $childQty = (int)$row['child'];
        $adultQty = (int)$row['adult'];
        if ($childQty > 0 || $adultQty > 0) {
            $item = $items[$index];
            $childTotal = $childQty * $item['child'];
            $adultTotal = $adultQty * $item['adult'];
            if ($childQty > 0) {
                $details .= "어린이 {$item['name']} {$childQty}매\n";
            }
            if ($adultQty > 0) {
                $details .= "어른 {$item['name']} {$adultQty}매\n";
            }
            $total += $childTotal + $adultTotal;
        }
    }

    $resultText = "{$now}<br>"
        . "{$name} 고객님 감사합니다.<br>"
        . h($details)
        . "합계" . number_format($total) . "원입니다.";

    $stmt = $mysqli->prepare("INSERT INTO orders (name, order_time, details, total) VALUES (?, NOW(), ?, ?)");
    $stmt->bind_param("ssi", $name, $details, $total);
    $stmt->execute();
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>티켓 예약 시스템</title>
    <style>
        body {
            font-family: "굴림체", Gulim, sans-serif;
        }
        table {
            border-collapse: collapse;
            width: 600px;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid black;
            text-align: center;
            padding: 4px;
        }
        .container {
            width: 620px;
            margin: 20px auto;
        }
        .total {
            margin-top: 20px;
            line-height: 1.6;
            white-space: pre-line;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="post">
            고객성명 <input type="text" name="username" value="<?= h($name) ?>">
            <input type="submit" value="합계">
            <table>
                <tr>
                    <th>No</th>
                    <th>구분</th>
                    <th>어린이</th>
                    <th>어른</th>
                    <th>비고</th>
                </tr>
                <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= $item['name'] ?></td>
                        <td><?= number_format($item['child']) ?>
                            <select name="qty[<?= $i ?>][child]">
                                <?php for ($j = 0; $j <= 5; $j++): ?>
                                    <option value="<?= $j ?>"><?= $j ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                        <td><?= number_format($item['adult']) ?>
                            <select name="qty[<?= $i ?>][adult]">
                                <?php for ($j = 0; $j <= 5; $j++): ?>
                                    <option value="<?= $j ?>"><?= $j ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                        <td><?= $item['note'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </form>
        <div class="total">
            <?= $resultText ?>
        </div>
    </div>
</body>
</html>
