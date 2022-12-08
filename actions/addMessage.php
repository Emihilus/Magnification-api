<?php

require_once '../config/config.php';
require_once '../utils/jwt.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
$data = json_decode(file_get_contents("php://input", true));

if (isset($data->userToken)
    and is_jwt_valid($data->userToken)
    and isset($data->receiver)
    and isset ($data->myUsername)
    and isset($data->content)) {

    $ma = 1;
    $stmt = $db->prepare("INSERT INTO messages (content, sender, receiver, status) values (?, (select id from users where username = ?), (select id from users where username = ?), 2)");
    $stmt->bind_param('sss', $data->content, $data->myUsername, $data->receiver);

    try {
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 1
            ]);
        } else {
            echo json_encode([
                'status' => 0
            ]);
        }
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
