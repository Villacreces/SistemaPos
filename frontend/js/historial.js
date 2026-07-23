const cuerpoHistorial = document.getElementById('cuerpo-historial');
const totalVendido = document.getElementById('total-vendido');
const cantidadFacturas = document.getElementById('cantidad-facturas');
const ticketPromedio = document.getElementById('ticket-promedio');
const textoResultados = document.getElementById('texto-resultados');

document.addEventListener('DOMContentLoaded', cargarHistorial);

document.getElementById('btn-buscar').addEventListener('click', cargarHistorial);
document.getElementById('btn-limpiar').addEventListener('click', limpiarFiltros);

async function cargarHistorial() {
    const parametros = new URLSearchParams();

    const fechaInicio = document.getElementById('fecha-inicio').value;
    const fechaFin = document.getElementById('fecha-fin').value;
    const cliente = document.getElementById('filtro-cliente').value.trim();
    const factura = document.getElementById('filtro-factura').value.trim();

    if (fechaInicio) parametros.append('fecha_inicio', fechaInicio);
    if (fechaFin) parametros.append('fecha_fin', fechaFin);
    if (cliente) parametros.append('cliente', cliente);
    if (factura) parametros.append('factura', factura);

    cuerpoHistorial.innerHTML = `
        <tr>
            <td colspan="7" class="text-center text-muted py-4">
                Cargando facturas...
            </td>
        </tr>
    `;

    try {
        const respuesta = await fetch(
            `backend/api_historial.php?${parametros.toString()}`
        );

        const resultado = await respuesta.json();

        if (!respuesta.ok || resultado.estado !== 'success') {
            throw new Error(resultado.mensaje || 'No se pudo cargar el historial.');
        }

        mostrarResumen(resultado.resumen);
        mostrarFacturas(resultado.facturas);

    } catch (error) {
        console.error(error);

        cuerpoHistorial.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger py-4">
                    ${error.message}
                </td>
            </tr>
        `;
    }
}

function mostrarResumen(resumen) {
    totalVendido.textContent =
        `$${Number(resumen.total_vendido).toFixed(2)}`;

    cantidadFacturas.textContent =
        resumen.cantidad_facturas;

    ticketPromedio.textContent =
        `$${Number(resumen.ticket_promedio).toFixed(2)}`;
}

function mostrarFacturas(facturas) {
    cuerpoHistorial.innerHTML = '';
    textoResultados.textContent =
        `${facturas.length} resultado${facturas.length === 1 ? '' : 's'}`;

    if (facturas.length === 0) {
        cuerpoHistorial.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    No se encontraron facturas.
                </td>
            </tr>
        `;
        return;
    }

    facturas.forEach(factura => {
        const fecha = new Date(factura.fecha_emision);

        const fechaFormateada = fecha.toLocaleString('es-EC', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });

        const claseEstado =
            factura.estado === 'Pagada'
                ? 'badge-pagada'
                : 'badge-anulada';

        cuerpoHistorial.innerHTML += `
            <tr>
                <td>#${factura.id}</td>
                <td>${fechaFormateada}</td>
                <td>
                    <strong>${factura.cliente}</strong><br>
                    <small class="text-muted">${factura.cedula}</small>
                </td>
                <td>${factura.cajero}</td>
                <td>$${Number(factura.total_factura).toFixed(2)}</td>
                <td>
                    <span class="badge ${claseEstado}">
                        ${factura.estado}
                    </span>
                </td>
                <td>
                    <div class="acciones-tabla">
                        <button
                            class="btn btn-sm btn-primary"
                            onclick="verDetalle(${factura.id})">
                            Ver detalles
                        </button>

                        <button
                            class="btn btn-sm btn-secondary"
                            onclick="reimprimirFactura(${factura.id})">
                            Reimprimir
                        </button>

                        <button
                            class="btn btn-sm btn-danger"
                            onclick="anularFactura(${factura.id})"
                            ${factura.estado === 'Anulada' ? 'disabled' : ''}>
                            Anular
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
}

function limpiarFiltros() {
    document.getElementById('fecha-inicio').value = '';
    document.getElementById('fecha-fin').value = '';
    document.getElementById('filtro-cliente').value = '';
    document.getElementById('filtro-factura').value = '';

    cargarHistorial();
}

function reimprimirFactura(id) {
    window.open(`factura.php?id=${id}`, '_blank');
}

async function verDetalle(id) {
    const cuerpoDetalle = document.getElementById('cuerpo-detalle-venta');
    const datosFactura = document.getElementById('datos-factura');
    const titulo = document.getElementById('tituloDetalleVenta');

    cuerpoDetalle.innerHTML = `
        <tr>
            <td colspan="5" class="text-center text-muted py-4">
                Cargando detalles...
            </td>
        </tr>
    `;

    datosFactura.innerHTML = '';
    titulo.textContent = `Detalles de la factura #${id}`;

    const modal = new bootstrap.Modal(
        document.getElementById('modalDetalleVenta')
    );

    modal.show();

    try {
        const respuesta = await fetch(
            `backend/api_detalle_venta.php?id=${id}`
        );

        const resultado = await respuesta.json();

        if (!respuesta.ok || resultado.estado !== 'success') {
            throw new Error(
                resultado.mensaje || 'No se pudo cargar el detalle.'
            );
        }

        const venta = resultado.venta;
        const detalles = resultado.detalles;

        datosFactura.innerHTML = `
            <div class="row g-3">
                <div class="col-md-4">
                    <strong>Cliente:</strong><br>
                    ${venta.cliente}<br>
                    <small class="text-muted">${venta.cedula}</small>
                </div>

                <div class="col-md-3">
                    <strong>Cajero:</strong><br>
                    ${venta.cajero}
                </div>

                <div class="col-md-3">
                    <strong>Total:</strong><br>
                    $${Number(venta.total_factura).toFixed(2)}
                </div>

                <div class="col-md-2">
                    <strong>Estado:</strong><br>
                    <span class="badge ${
                        venta.estado === 'Pagada'
                            ? 'bg-success'
                            : 'bg-danger'
                    }">
                        ${venta.estado}
                    </span>
                </div>
            </div>
        `;

        cuerpoDetalle.innerHTML = '';

        if (detalles.length === 0) {
            cuerpoDetalle.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        La factura no tiene productos.
                    </td>
                </tr>
            `;
            return;
        }

        detalles.forEach(detalle => {
            cuerpoDetalle.innerHTML += `
                <tr>
                    <td>${detalle.codigo_barras}</td>
                    <td>${detalle.nombre_producto}</td>
                    <td>${detalle.cantidad}</td>
                    <td>$${Number(detalle.precio_congelado).toFixed(2)}</td>
                    <td>$${Number(detalle.subtotal).toFixed(2)}</td>
                </tr>
            `;
        });

    } catch (error) {
        console.error(error);

        cuerpoDetalle.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-danger py-4">
                    ${error.message}
                </td>
            </tr>
        `;
    }
}

async function anularFactura(id) {
    const confirmar = confirm(
        `¿Está seguro de anular la factura #${id}?\n\n` +
        'Los productos serán devueltos al inventario.'
    );

    if (!confirmar) return;

    try {
        const respuesta = await fetch('backend/anular_venta.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                venta_id: id
            })
        });

        const texto = await respuesta.text();
        let resultado;

        try {
            resultado = JSON.parse(texto);
        } catch {
            throw new Error(`Respuesta inválida del servidor: ${texto}`);
        }

        if (!respuesta.ok || resultado.estado !== 'success') {
            throw new Error(
                resultado.mensaje || 'No se pudo anular la factura.'
            );
        }

        alert(resultado.mensaje);
        cargarHistorial();

    } catch (error) {
        console.error(error);
        alert(`Error: ${error.message}`);
    }
}