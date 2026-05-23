<?php

header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ===============================
   CONEXION PDO RAILWAY
=============================== */

try {

    $conn = new PDO(
        "mysql:host=kodama.proxy.rlwy.net;port=58999;dbname=datacenter_umg;charset=utf8mb4",
        "root",
        "rMGJpYmLLwhgEXBqTklSGmrZPylNfLJO"
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e){

    die(json_encode([
        "db_error"=>$e->getMessage()
    ]));
}

$json = file_get_contents("php://input");

if(!$json){
    die(json_encode([
        "error"=>"No llegó JSON"
    ]));
}

$data = json_decode($json,true);

if(!$data){
    die(json_encode([
        "error"=>"JSON inválido"
    ]));
}

/* ===============================
   ESCLAVO 1
=============================== */
if(isset($data["humo"])){

    $sql = "INSERT INTO sala_servidores
    (temperatura,humedad,nivel_humo,humo_digital)
    VALUES (?,?,?,?)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        $data["temperatura"],
        $data["humedad"],
        $data["humo"],
        $data["delta"]
    ]);

    echo json_encode([
        "ok"=>"Servidor guardado"
    ]);
}

/* ===============================
   ESCLAVO 2
=============================== */
elseif(isset($data["zona"])){

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
    VALUES (?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        $data["t"],
        $data["h"],
        $data["agua_raw"],
        $data["agua"],
        $data["intruso"],
        $data["puerta"],
        $data["ahorro"]
    ]);

    echo json_encode([
        "ok"=>"UPS guardado"
    ]);
}

/* ===============================
   ESCLAVO 3
=============================== */
elseif(isset($data["voltaje"])){

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
    VALUES (?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        $data["humedad_suelo"],
        $data["lluvia"],
        $data["pir"],
        $data["distancia"],
        $vehiculo,
        $data["bomba"],
        $data["led"]
    ]);

    echo json_encode([
        "ok"=>"Jardin guardado"
    ]);
}

else{

    echo json_encode([
        "error"=>"Tipo desconocido"
    ]);
}

?>
