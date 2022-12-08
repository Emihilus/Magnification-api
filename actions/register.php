<?php

require_once '../config/config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
$data = json_decode(file_get_contents("php://input", true));

if (isset($data->username)
    and isset($data->pwd)
    and isset ($data->email)) {

    $pwd = _hash($data->pwd);

    $stmt = $db->prepare("INSERT INTO users (username, password, email) values (?, ?, ?)");
    $stmt->bind_param('sss', $data->username, $pwd, $data->email);

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
