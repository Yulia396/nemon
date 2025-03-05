<?php
require '../../db_connection.php';
header("Content-Type: application/json");

// Obtener precios de energía
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $stmt = $pdo->query("SELECT * FROM Energy_Prices ORDER BY start_date ASC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Si no hay datos
        if (!$data) {
            echo json_encode(["message" => "No hay precios de energía disponibles."]);
            exit;
        }    
        // Enviar datos en JSON
        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        echo json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
        exit;
    }
}

// Insertar precios de energía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    // Si json_decode devuelve null
    if ($data === null) {
        echo json_encode(["error" => "JSON inválido o datos vacíos"]);
        exit;
    }

    // Verificar si están todos los datos y subdatos antes de acceder a ellos
    $required_fields = ['energy', 'start_date', 'end_date', 'username'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode(["error" => "Falta el campo: $field"]);
            exit;
        }
    }
    foreach (['p1', 'p2', 'p3'] as $subfield) {
        if (!isset($data['energy'][$subfield]) || empty($data['energy'][$subfield])) {
            echo json_encode(["message" => "Falta el campo 'energy.$subfield'"]);
            exit;
        }
    }

    // Validar que los precios son numéricos
    foreach (['p1', 'p2', 'p3'] as $price) {
        if (!is_numeric($data['energy'][$price])) {
            echo json_encode(["message" => "Los precios de enería deben ser numéricos."]);
            exit;
        }
    }

    // Validar que las fechas tengan formato correcto
    list($year_start, $month_start, $day_start) = explode('-', $data['start_date']); // Convertir la cadena en día, mes y año
    list($year_end, $month_end, $day_end) = explode('-', $data['end_date']); // Convertir la cadena en día, mes y año

    if (!checkdate($month_start, $day_start, $year_start)) {
        echo json_encode(["error" => "La fecha inicio debe tener formato YYYY-MM-DD correcto."]);
        exit;
    } elseif (!checkdate($month_end, $day_end, $year_end)) {
        echo json_encode(["error" => "La fecha fin debe tener formato YYYY-MM-DD correcto."]);
        exit;
    }

    // Validar que la fecha de inicio sea menor que la fecha de fin
    if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
        echo json_encode(["message" => "La fecha de inicio debe ser anterior o igual a la fecha de fin."]);
        exit;
    }
    
    // Introducir la información en la base de datos
    try {
        // Comprobar si ya existen precios en el rango de fechas (solapamiento)
        $stmt = $pdo->prepare("SELECT * FROM Energy_Prices WHERE start_date <= ? AND end_date >= ? ORDER BY start_date ASC");
        $stmt->execute([$data['end_date'], $data['start_date']]);
        $existing_prices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($existing_prices) {
            foreach ($existing_prices as $existing) {
                // Caso 1: Si el nuevo intervalo solapa completamente el registro existente
                if ($data['start_date'] <= $existing['start_date'] && $data['end_date'] >= $existing['end_date']) {
                    // Delete: elimina el registro solapado
                    $stmt = $pdo->prepare("DELETE FROM Energy_Prices WHERE id = ?");
                    $stmt->execute([$existing['id']]);

                // Caso 2: Si el nuevo intervalo solapa con el inicio del registro existente
                } elseif ($data['start_date'] <= $existing['start_date'] && $data['end_date'] < $existing['end_date']) {
                    $end_date_mas_uno = new DateTime($data['end_date']);
                    $end_date_mas_uno = $end_date_mas_uno->modify('+1 day');
                    // Uptade: registro existente empieza al día siguiente después del nuevo intervalo y conserva fecha final
                    $stmt = $pdo->prepare("UPDATE Energy_Prices SET start_date = ? WHERE id = ?");
                    $stmt->execute([$end_date_mas_uno->format('Y-m-d'), $existing['id']]);

                // Caso 3: Si el nuevo intervalo solapa con el final del registro existente
                } elseif ($data['start_date'] > $existing['start_date'] && $data['end_date'] >= $existing['end_date']) {
                    $start_date_menos_uno = new DateTime($data['start_date']);
                    $start_date_menos_uno = $start_date_menos_uno->modify('-1 day');                    
                    // Uptade: registo existente conserva fecha inicio y termina el día antes del nuevo intervalo
                    $stmt = $pdo->prepare("UPDATE Energy_Prices SET end_date = ? WHERE id = ?");
                    $stmt->execute([$start_date_menos_uno->format('Y-m-d'), $existing['id']]);

                // Caso 4: Si el nuevo intervalo solapa en medio de un registro existente - separamos en dos fragmentos
                }elseif ($data['start_date'] > $existing['start_date'] && $data['end_date'] < $existing['end_date']) {
                    $start_date_menos_uno = new DateTime($data['start_date']);
                    $start_date_menos_uno = $start_date_menos_uno->modify('-1 day');
                    // Uptade: registro existente conserva fecha inicio y termina el día antes del nuevo intervalo
                    $stmt = $pdo->prepare("UPDATE Energy_Prices SET end_date = ? WHERE id = ?");
                    $stmt->execute([$start_date_menos_uno->format('Y-m-d'), $existing['id']]);

                    $end_date_mas_uno = new DateTime($data['end_date']);
                    $end_date_mas_uno = $end_date_mas_uno->modify('+1 day');                    
                    // Insert: nuevo registro para conservar segundo fragmento que empieza el día sigiente del nuevo intervalo y conserva fecha final existente
                    $stmt = $pdo->prepare("INSERT INTO Energy_Prices (p1, p2, p3, start_date, end_date, username) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$existing['p1'], $existing['p2'], $existing['p3'], $end_date_mas_uno->format('Y-m-d'), $existing['end_date'], $existing['username']]);
                } 
            }
            // Insertar el nuevo intervalo
            $stmt = $pdo->prepare("INSERT INTO Energy_Prices (p1, p2, p3, start_date, end_date, username) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['energy']['p1'], $data['energy']['p2'], $data['energy']['p3'], $data['start_date'], $data['end_date'], $data['username']]);

            echo json_encode(["message" => "Precios de energía actualizados correctamente."]);
        } else {
            // Insertar los nuevos valores en la base de datos    
            $stmt = $pdo->prepare("INSERT INTO Energy_Prices (p1, p2, p3, start_date, end_date, username) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['energy']['p1'], $data['energy']['p2'], $data['energy']['p3'], $data['start_date'], $data['end_date'], $data['username']]);
            echo json_encode(["message" => "Precios de energía insertados correctamente."]);
        } 
    } catch (Exception $e) {
        echo json_encode(["error" => "Error al insertar: " . $e->getMessage()]);
    }
    exit;
}
?>