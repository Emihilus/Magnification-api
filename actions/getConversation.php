<?php
require_once '../config/config.php';
require_once '../utils/jwt.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
$data = json_decode(file_get_contents("php://input", true));

if (isset($data->userToken) and is_jwt_valid($data->userToken) and isset($data->receiver) and isset ($data->myUsername)) {
    $stmt = $db->prepare("SELECT u.username sender_username, m.content, m.creation_date FROM messages m 
    left join users u on (u.id = m.sender) 
    WHERE (m.sender = (select id from users where username = ?) and m.receiver = (select id from users where username = ?)) OR (m.receiver = (select id from users where username = ?)
 and m.sender = (select id from users where username = ?)) order by m.creation_date desc");
    $stmt->bind_param('ssss', $data->receiver, $data->myUsername, $data->receiver, $data->myUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    try {
        $resultArray = [];
        while ($row = $result->fetch_assoc()) {
            $resultArray[] = $row;
        }

$stmt->close();

$stmt2 = $db->prepare("UPDATE messages m set m.status = 0 WHERE m.sender = (select id from users where username = ?) and m.receiver = (select id from users where username = ?)");
$stmt2->bind_param('ss', $data->receiver, $data->myUsername);
$stmt2->execute();
$stmt2->close();


$res = json_encode($resultArray);
echo $res; 
    } catch (Exception $e) {
        echo json_encode([
            'status' => 0
        ]);
    }
} else {
    echo json_encode([
        'status' => -1
    ]);
}
