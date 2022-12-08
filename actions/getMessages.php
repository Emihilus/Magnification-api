<?php

require_once '../config/config.php';
require_once '../utils/jwt.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
$data = json_decode(file_get_contents("php://input", true));

$resultArray = [];

//echo is_jwt_valid($data->userToken);
if (isset($data->userToken) && is_jwt_valid($data->userToken) && isset($data->username)) {

    $stmt = $db->prepare("SELECT u.username,
       u.id,

  (SELECT mm.content
   FROM messages mm
   WHERE (mm.receiver =
            (SELECT u.id
             FROM users u
             WHERE u.username = ?)
          AND mm.sender = u.id)
     OR (mm.sender =
           (SELECT u.id
            FROM users u
            WHERE u.username = ?)
         AND mm.receiver = u.id)
   ORDER BY mm.creation_date DESC
   LIMIT 1) content,

  (SELECT mm.creation_date
   FROM messages mm
   WHERE (mm.receiver =
            (SELECT u.id
             FROM users u
             WHERE u.username = ?)
          AND mm.sender = u.id)
     OR (mm.sender =
           (SELECT u.id
            FROM users u
            WHERE u.username = ?)
         AND mm.receiver = u.id)
   ORDER BY mm.creation_date DESC
   LIMIT 1) creation_date,
   
   (SELECT mm.status
   FROM messages mm
   WHERE (mm.receiver =
            (SELECT u.id
             FROM users u
             WHERE u.username = ?)
          AND mm.sender = u.id)
   ORDER BY mm.creation_date DESC
   LIMIT 1) msg_status
FROM messages m
LEFT JOIN users u ON (m.sender = u.id)
WHERE m.receiver =
    (SELECT u.id
     FROM users u
     WHERE u.username = ?)
  OR m.sender =
    (SELECT u.id
     FROM users u
     WHERE u.username = ?)
GROUP BY m.sender");
    $stmt->bind_param('sssssss', $data->username, $data->username, $data->username, $data->username, $data->username, $data->username, $data->username);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $stmt2 = $db->prepare("UPDATE messages m set m.status = 1 WHERE m.status = 2 and  m.receiver = (select id from users where username = ?)");
    $stmt2->bind_param('s', $data->username);
    $stmt2->execute();
    $stmt2->close();


    while ($row = $result->fetch_assoc()) {
        $resultArray[] = $row;
    }
    echo json_encode($resultArray);
} else {
    echo json_encode([
        'status' => -1
    ]);
}
