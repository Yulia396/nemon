$(document).ready(function() {
    getEnergyPrices();  // Obtener precios de energía al cargar
    getPowerPrices();   // Obtener precios de potencia al cargar
    getInvoices();      // Obtener facturas generadas al cargar
});

// Mostrar mensajes de la API en un alert
function showApiResponse(response) {
    if (!response) return;
    alert(response.message || response.error);
}

// Insertar Precio de Energía
function insertEnergyPrice() {
    let data = {
        energy: {
            p1: $("#p1_energy").val(),
            p2: $("#p2_energy").val(),
            p3: $("#p3_energy").val()
        },
        start_date: $("#start_date_energy").val(),
        end_date: $("#end_date_energy").val(),
        username: $("#username_energy").val()
    };

    $.ajax({
        url: rutaAPIenergy,
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(data),
        success: function(response) {
            showApiResponse(response);
            getEnergyPrices(); // Recargar tabla de energía
        },
        error: function(xhr) {
            showApiResponse(xhr.responseJSON);
        }
    });
}

// Insertar Precio de Potencia
function insertPowerPrice() {
    let data = {
        power: {
            p1: $("#p1_power").val(),
            p2: $("#p2_power").val(),
            p3: $("#p3_power").val()
        },
        start_date: $("#start_date_power").val(),
        end_date: $("#end_date_power").val(),
        username: $("#username_power").val()
    };

    $.ajax({
        url: rutaAPIpower,
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(data),
        success: function(response) {
            showApiResponse(response);
            getPowerPrices(); // Recargar tabla de potencia
        },
        error: function(xhr) {
            showApiResponse(xhr.responseJSON);
        }
    });
}

// Obtener Precios de Energía
function getEnergyPrices() {
    $.ajax({
        url: rutaAPIenergy,
        type: "GET",
        success: function(response) {
            let rows = "";
            if (!Array.isArray(response) || response.length === 0) {
                $("#energyTable").html("<tr><td colspan='7'>No hay precios de energía disponibles.</td></tr>");
                return;
            }

            response.forEach(price => {
                rows += `<tr>
                    <td>${price.id}</td>
                    <td>${price.p1}</td>
                    <td>${price.p2}</td>
                    <td>${price.p3}</td>
                    <td>${price.start_date}</td>
                    <td>${price.end_date}</td>
                    <td>${price.username}</td>
                </tr>`;
            });

            $("#energyTable").html(rows);
        },
        error: function(xhr) {
            showApiResponse(xhr.responseJSON);
        }
    });
}

// Obtener Precios de Potencia
function getPowerPrices() {
    $.ajax({
        url: rutaAPIpower,
        type: "GET",
        success: function(response) {
            let rows = "";
            if (!Array.isArray(response) || response.length === 0) {
                $("#powerTable").html("<tr><td colspan='7'>No hay precios de potencia disponibles.</td></tr>");
                return;
            }

            response.forEach(price => {
                rows += `<tr>
                    <td>${price.id}</td>
                    <td>${price.p1}</td>
                    <td>${price.p2}</td>
                    <td>${price.p3}</td>
                    <td>${price.start_date}</td>
                    <td>${price.end_date}</td>
                    <td>${price.username}</td>
                </tr>`;
            });

            $("#powerTable").html(rows);
        },
        error: function(xhr) {
            showApiResponse(xhr.responseJSON);
        }
    });
}

function calculateBill() {
    let data = {
        consumption: {
            p1: $("#c_p1").val(),
            p2: $("#c_p2").val(),
            p3: $("#c_p3").val()
        },
        contractedpower: {
            p1: $("#pot_p1").val(),
            p2: $("#pot_p2").val(),
            p3: $("#pot_p3").val()
        },
        start_date: $("#fact_start_date").val(),
        end_date: $("#fact_end_date").val(),
        username: $("#fact_username").val().trim() === "" ? null : $("#fact_username").val()
    };

    $.ajax({
        url: rutaAPIgeneratebill,
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(data),
        success: function(response) {
            showApiResponse(response);          
            getInvoices(); // Actualizar la lista de facturas después de generar una
            
        },
        error: function(xhr) {
            showApiResponse(xhr.responseJSON);
        }
    });
}

// Obtener y mostrar las facturas generadas
function getInvoices() {
    $.ajax({
        url: rutaAPIgeneratebill,
        type: "GET",
        success: function(response) {
            if (!Array.isArray(response) || response.length === 0) {
                $("#invoiceTable").html("<tr><td colspan='6'>No hay facturas registradas.</td></tr>");
                return;
            }

            let rows = "";
            response.forEach(invoice => {
                rows += `<tr>
                    <td>${invoice.id}</td>
                    <td>${invoice.total_energy_cost}€</td>
                    <td>${invoice.total_power_cost}€</td>
                    <td>${invoice.total_invoice}€</td>
                    <td>${invoice.username}</td>
                    <td>${invoice.created_at}</td>
                </tr>`;
            });
            $("#invoiceTable").html(rows);
        },
        error: function(xhr) {
            console.error("Error al obtener facturas:", xhr);
        }
    });
}