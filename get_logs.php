<?php
require_once 'config.php';

$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 50;

$where = [];
if (!empty($_GET['rut'])) {
    $rut = mysqli_real_escape_string($conn, $_GET['rut']);
    $where[] = "rut LIKE '%$rut%'";
}
if (!empty($_GET['estado'])) {
    $estado = mysqli_real_escape_string($conn, $_GET['estado']);
    $where[] = "estado_transaccion = '$estado'";
}
if (!empty($_GET['fecha_desde']) && !empty($_GET['fecha_hasta'])) {
    $desde = mysqli_real_escape_string($conn, $_GET['fecha_desde']);
    $hasta = mysqli_real_escape_string($conn, $_GET['fecha_hasta']);
    $where[] = "fecha_viaje BETWEEN '$desde' AND '$hasta'";
}
if (!empty($_GET['estado_boleto'])) {
    $estado_boleto = mysqli_real_escape_string($conn, $_GET['estado_boleto']);
    if($estado_boleto === 'confirmacionFallida'){
        $where[] = "estado_boleto LIKE 'Confirmación fallida%'";
    } else {
        $where[] = "estado_boleto = '$estado_boleto'";
    }
}
if (!empty($_GET['codigo_autorizacion'])) {
    $codigo = mysqli_real_escape_string($conn, $_GET['codigo_autorizacion']); 
    $where[] = "codigo_autorizacion LIKE '%$codigo%'";
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Total filtrado
$resultTotal = mysqli_query($conn, "SELECT COUNT(*) as total FROM totem_logs $where_sql");
$rowTotal = mysqli_fetch_assoc($resultTotal);
$totalFiltered = $rowTotal['total'];

// Consulta principal
$sql = "SELECT * FROM totem_logs $where_sql ORDER BY id DESC LIMIT $start, $length";
$result = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

$response = [
    "draw" => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
    "recordsTotal" => $totalFiltered,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
];

header('Content-Type: application/json');
echo json_encode($response);