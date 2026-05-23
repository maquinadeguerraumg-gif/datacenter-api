<?php

header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli(
    "kodama.proxy.rlwy.net",
    "root",
    "rMGJpYmLLwhgEXBqTklSGmrZPylNfLJO",
    "datacenter_umg",
    58999
);

if ($conn->connect_error) {
    die(json_encode([
        "db_error" => $conn->connect_error
    ]));
}

$json = file_get_contents("php://input");

if (!$json) {
    die(json_encode([
        "error" => "No llegó JSON"
    ]));
}

$data = json_decode($json, true);

if (!$data) {
    die(json_encode([
        "error" => "JSON inválido"
    ]));
}

/* ===============================
   SALA SERVIDORES
=============================== */
if (isset($data["humo"])) {

    $sql = "INSERT INTO sala_servidores
    (
        temperatura,
        humedad,
        nivel_humo,
        humo_digital
    )
    VALUES
    (
        {$data['temperatura']},
        {$data['humedad']},
        {$data['humo']},
        {$data['delta']}
    )";
}

/* ===============================
   UPS
=============================== */
elseif (isset($data["zona"])) {

    $sql = "INSERT INTO sala_ups_redes
    (
        temperatura,
        humedad,
        nivel_agua,
        hay_agua,
        es_intruso,
        puerta_abierta,
        ups_activo
    )
    VALUES
    (
        {$data['t']},
        {$data['h']},
        {$data['agua_raw']},
        {$data['agua']},
        {$data['intruso']},
        {$data['puerta']},
        {$data['ahorro']}
    )";
}

/* ===============================
   JARDIN
=============================== */
elseif (isset($data["voltaje"])) {

    $vehiculo = ($data["distancia"] < 10) ? 1 : 0;

    $sql = "INSERT INTO sala_jardin
    (
        humedad_suelo,
        lluvia_detectada,
        movimiento,
        distancia_vehiculo,
        vehiculo_presente,
        bomba_riego,
        talanquera_abierta
    )
    VALUES
    (
        {$data['humedad_suelo']},
        {$data['lluvia']},
        {$data['pir']},
        {$data['distancia']},
        $vehiculo,
        {$data['bomba']},
        {$data['led']}
    )";
}

else {
    die(json_encode([
        "error" => "Tipo desconocido"
    ]));
}

if ($conn->query($sql)) {

    echo json_encode([
        "ok" => true,
        "msg" => "Insertado"
    ]);

} else {

    echo json_encode([
        "sql_error" => $conn->error,
        "query" => $sql
    ]);
}

$conn->close();

?>
