<?php
require_once '../config/config.php';
require_once 'jwt.php';


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
$data = json_decode(file_get_contents("php://input", true));

$user = $data->username;
$pwd = _hash($data->pwd);

$stmt = $db->prepare("SELECT * FROM users where username = ? and password = ?");
$stmt->bind_param('ss', $user, $pwd);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();
    $username = $row['username'];

    $headers = [
        'alg' => 'HS256',
        'typ' => 'JWT'
    ];
    $payload = [
        'username' => $username,
        'exp' => (time() + JWT_EXPIRETIME)
    ];

    $jwt = generate_jwt($headers, $payload);

    $stmt = $db->prepare("UPDATE users u set u.last_login = current_timestamp() where username = ?");
    $stmt->bind_param('s', $user);
    $stmt->execute();

    echo json_encode([
        'status' => 1,
        'token' => $jwt
    ]);
} else {
    echo json_encode([
        'status' => 0
    ]);
}
$db->close();