<?php
require '../db_connection.php';
header("Content-Type: application/json");

// Obtener todas las facturas
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $stmt = $pdo->query("SELECT * FROM Invoices ORDER BY created_at ASC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Si no hay datos
        if (!$data) {
            echo json_encode(["message" => "No hay facturas disponibles."]);
            exit;
        }
        // Enviar datos en JSON
        echo json_encode($data);
    } catch (Exception $e) {
        echo json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
        exit;
    }
}

// Generar y guardar una factura
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    // Si json_decode devuelve null
    if ($data === null) {
        echo json_encode(["error" => "JSON inválido o datos vacíos"]);
        exit;
    }

    // Verificar si están todos los datos y subdatos antes de acceder a ellos
    $required_fields = ['consumption', 'contractedpower', 'start_date', 'end_date', 'username'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode(["message" => "Falta el campo: $field"]);
            exit;
        }
    }
    foreach (['p1', 'p2', 'p3'] as $subfield) {
        if (!isset($data['consumption'][$subfield]) || empty($data['consumption'][$subfield])) {
            echo json_encode(["message" => "Falta el campo 'consumption.$subfield'"]);
            exit;
        }
    }
    foreach (['p1', 'p2', 'p3'] as $subfield) {
        if (!isset($data['contractedpower'][$subfield]) || empty($data['contractedpower'][$subfield])) {
            echo json_encode(["message" => "Falta el campo 'contractedpower.$subfield'"]);
            exit;
        }
    }

    // Validar que los datos introducidos sean correctos
    validarInputs($data);

    // Calcular factura
    $invoice = calcularInvoice($data);

    // Guardar la factura en la base de datos
    try {
        $stmt = $pdo->prepare("INSERT INTO Invoices (total_energy_cost, total_power_cost, total_invoice, username) VALUES (?, ?, ?, ?)");
        $stmt->execute([$invoice['total_energy_cost'], $invoice['total_power_cost'], $invoice['total_invoice'], $data['username']]);
        echo json_encode(["message" => "Factura generada correctamente."]);
    }catch (Exception $e) {
        echo json_encode(["error" => "Error al insertar: " . $e->getMessage()]);
    }
    exit;
}

// Calcula la factura y devuelve el total de energía, total de potencia y factura total
function calcularInvoice($data) {
    global $pdo;
    $energy_prices = getPrices($data['start_date'], $data['end_date'], 'Energy_Prices');
    $power_prices = getPrices($data['start_date'], $data['end_date'], 'Power_Prices');

    $total_energy_cost = 0;
    $total_power_cost = 0;

    foreach (['p1', 'p2', 'p3'] as $price) {
        foreach ($energy_prices as $energy_period) {
            $total_energy_cost += calcularProporcionalEnergy($data['start_date'], $data['end_date'], $energy_period['start_date'], $energy_period['end_date'], $data['consumption'][$price], $energy_period[$price]);
        }

        foreach ($power_prices as $power_period) {
            $total_power_cost += calcularProporcionalPower($data['start_date'], $data['end_date'], $power_period['start_date'], $power_period['end_date'], $data['contractedpower'][$price], $power_period[$price]);
         }
    }

    $total_invoice = $total_energy_cost + $total_power_cost;

    return [
        'total_energy_cost' => $total_energy_cost,
        'total_power_cost' => $total_power_cost,
        'total_invoice' => $total_invoice
    ];
}

// Devuelve los precios de energía o potencia de los períodos que coincidan con la fecha inicio y final introducidos
function getPrices($start_date, $end_date, $table) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE end_date >= ? AND start_date <= ? ORDER BY start_date ASC");
        $stmt->execute([$start_date, $end_date]);
        $prices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$prices) {
            echo json_encode(["error" => "No hay precios de enería o potencia en este rango de fechas"]);
            exit;
        }
    }catch (Exception $e) {
        echo json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
        exit;
    }
    return $prices;
}

// Calcula los resultados parciales cuando cae en varios períodos
function calcularProporcionalEnergy($start_date, $end_date, $period_start_date, $period_end_date, $consumption, $price) {
    $date1 = new DateTime(max($start_date, $period_start_date));
    $date2 = new DateTime(min($end_date, $period_end_date));
    $days = $date1->diff($date2)->days + 1;

    $total_days = (new DateTime($end_date))->diff(new DateTime($start_date))->days + 1;

    return ($days / $total_days) * $consumption * $price;
}
// Calcula los resultados parciales cuando cae en varios períodos
function calcularProporcionalPower($start_date, $end_date, $period_start_date, $period_end_date, $consumption, $price) {
    $date1 = new DateTime(max($start_date, $period_start_date));
    $date2 = new DateTime(min($end_date, $period_end_date));
    $days = $date1->diff($date2)->days + 1;

    $total_days = (new DateTime($end_date))->diff(new DateTime($start_date))->days + 1;

    return ($days / $total_days) * $consumption * $price * $total_days;
}

// Comprueba que los datos sean en formato correcto
function validarInputs($data) {    
    // Validar que los precios sean numéricos
    foreach (['p1', 'p2', 'p3'] as $price) {
        if (!is_numeric($data['consumption'][$price]) || !is_numeric($data['contractedpower'][$price])) {
            echo json_encode(["message" => "Los precios de enería y potencia deben ser numéricos."]);
            exit;
        }
    }

    // Validar que las fechas tengan formato correcto
    list($year_start, $month_start, $day_start) = explode('-', $data['start_date']); // Convertir la cadena en día, mes y año
    list($year_end, $month_end, $day_end) = explode('-', $data['end_date']); // Convertir la cadena en día, mes y año

    if (!checkdate($month_start, $day_start, $year_start)) {
        echo json_encode(["error" => "La fecha inicio debe tener formato YYYY-MM-DD."]);
        exit;
    } elseif (!checkdate($month_end, $day_end, $year_end)) {
        echo json_encode(["error" => "La fecha fin debe tener formato YYYY-MM-DD."]);
        exit;
    }

    // Validar que la fecha de inicio sea menor que la fecha de fin
    if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
        echo json_encode(["message" => "La fecha de inicio debe ser anterior o igual a la fecha de fin."]);
        exit;
    }
}
?>