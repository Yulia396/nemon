<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tarifas y Facturas</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
    <link rel="stylesheet" href="style.css">
</head>
<body class="container mt-4">
    
    <h1 class="text-center mb-4">Gestión de Tarifas y Facturas</h1>

    <div class="row">
        <!-- Formulario de Energía -->
        <div class="col-md-6">
            <div class="card p-3 mb-4">
                <h3>Agregar Precio de Energía</h3>
                <input type="number" id="p1_energy" class="form-control mb-2" placeholder="P1 (€)">
                <input type="number" id="p2_energy" class="form-control mb-2" placeholder="P2 (€)">
                <input type="number" id="p3_energy" class="form-control mb-2" placeholder="P3 (€)">
                <input type="date" id="start_date_energy" class="form-control mb-2">
                <input type="date" id="end_date_energy" class="form-control mb-2">
                <input type="text" id="username_energy" class="form-control mb-2" placeholder="Usuario">
                <button class="boton" onclick="insertEnergyPrice()">Guardar Precio de Energía</button>
            </div>
        </div>
        <!-- Formulario de Potencia -->
        <div class="col-md-6">
            <div class="card p-3 mb-4">
                <h3>Agregar Precio de Potencia</h3>
                <input type="number" id="p1_power" class="form-control mb-2" placeholder="P1 (€)">
                <input type="number" id="p2_power" class="form-control mb-2" placeholder="P2 (€)">
                <input type="number" id="p3_power" class="form-control mb-2" placeholder="P3 (€)">
                <input type="date" id="start_date_power" class="form-control mb-2">
                <input type="date" id="end_date_power" class="form-control mb-2">
                <input type="text" id="username_power" class="form-control mb-2" placeholder="Usuario">
                <button class="boton" onclick="insertPowerPrice()">Guardar Precio de Potencia</button>
            </div>
        </div>
    </div>
    <!-- Formulario para generar una factura -->
    <div class="card p-3 mb-4">
        <h3>Generar Factura</h3>
        <input type="number" id="c_p1" class="form-control mb-2" placeholder="Consumo P1">
        <input type="number" id="c_p2" class="form-control mb-2" placeholder="Consumo P2">
        <input type="number" id="c_p3" class="form-control mb-2" placeholder="Consumo P3">
        <input type="number" id="pot_p1" class="form-control mb-2" placeholder="Potencia P1">
        <input type="number" id="pot_p2" class="form-control mb-2" placeholder="Potencia P2">
        <input type="number" id="pot_p3" class="form-control mb-2" placeholder="Potencia P3">
        <input type="date" id="fact_start_date" class="form-control mb-2">
        <input type="date" id="fact_end_date" class="form-control mb-2">
        <input type="text" id="fact_username" class="form-control mb-2" placeholder="Usuario">
        <button class="boton" onclick="calculateBill()">Generar Factura</button>
    </div>

    <div class="row">
        <!-- Tabla de Energía -->
        <div class="col-md-6">
            <h3>Precios de Energía</h3>
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th><th>P1</th><th>P2</th><th>P3</th><th>Inicio</th><th>Fin</th><th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="energyTable"></tbody>
                </table>
            </div>
        </div>
        <!-- Tabla de Potencia -->
        <div class="col-md-6">
            <h3>Precios de Potencia</h3>
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th><th>P1</th><th>P2</th><th>P3</th><th>Inicio</th><th>Fin</th><th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="powerTable"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabla de Facturas Generadas -->
    <h3 class="mt-4">Facturas Generadas</h3>
    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th><th>Total Energía (€)</th><th>Total Potencia (€)</th><th>Total Factura (€)</th><th>Usuario</th><th>Fecha</th>
                </tr>
            </thead>
            <tbody id="invoiceTable"></tbody>
        </table>
    </div>
    <!-- Dejar un espacio al final para que la tabla no termine en el borde de la página -->
    <div style="height:50px;"></div>

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="rutasAPI.php"></script>
    <script src="scripts.js"></script>

</body>
</html>