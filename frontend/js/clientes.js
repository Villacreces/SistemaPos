const tablaClientes = document.getElementById('tabla-clientes');
const buscarCliente = document.getElementById('buscar-cliente');

const btnNuevoCliente =
    document.getElementById('btn-nuevo-cliente');

const btnGuardarCliente =
    document.getElementById('btn-guardar-cliente');

const formularioCliente =
    document.getElementById('formulario-cliente');

const clienteId = document.getElementById('cliente-id');
const clienteCedula = document.getElementById('cliente-cedula');
const clienteNombre = document.getElementById('cliente-nombre');
const clienteCorreo = document.getElementById('cliente-correo');

const tituloModal =
    document.getElementById('titulo-modal-cliente');

const mensajeClientes =
    document.getElementById('mensaje-clientes');

const elementoModal =
    document.getElementById('modal-cliente');

const modalCliente =
    bootstrap.Modal.getOrCreateInstance(elementoModal);

let clientesCargados = [];
let temporizadorBusqueda = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarClientes();
});

buscarCliente.addEventListener('input', () => {
    clearTimeout(temporizadorBusqueda);

    temporizadorBusqueda = setTimeout(() => {
        cargarClientes(buscarCliente.value.trim());
    }, 300);
});

btnNuevoCliente.addEventListener('click', prepararNuevoCliente);
btnGuardarCliente.addEventListener('click', guardarCliente);

clienteCedula.addEventListener('input', () => {
    clienteCedula.value =
        clienteCedula.value.replace(/\D/g, '').slice(0, 10);
});

async function cargarClientes(busqueda = '') {
    tablaClientes.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted py-4">
                Cargando clientes...
            </td>
        </tr>
    `;

    try {
        const respuesta = await fetch(
            `backend/crud_clientes.php?q=${encodeURIComponent(busqueda)}`
        );

        const clientes = await leerRespuesta(respuesta);

        if (!Array.isArray(clientes)) {
            throw new Error(
                'La respuesta de clientes no tiene el formato esperado.'
            );
        }

        clientesCargados = clientes;
        mostrarClientes(clientes);

    } catch (error) {
        console.error(error);

        tablaClientes.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-4">
                    ${escaparHTML(error.message)}
                </td>
            </tr>
        `;
    }
}

function mostrarClientes(clientes) {
    tablaClientes.innerHTML = '';

    if (clientes.length === 0) {
        tablaClientes.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    No se encontraron clientes.
                </td>
            </tr>
        `;

        return;
    }

    clientes.forEach(cliente => {
        const fila = document.createElement('tr');

        fila.innerHTML = `
            <td>${Number(cliente.id)}</td>

            <td>${escaparHTML(cliente.cedula)}</td>

            <td>${escaparHTML(cliente.nombre_completo)}</td>

            <td>
                ${cliente.correo
                    ? escaparHTML(cliente.correo)
                    : '<span class="text-muted">Sin correo</span>'}
            </td>

            <td>${formatearFecha(cliente.fecha_registro)}</td>

            <td class="text-center">
                <button
                    type="button"
                    class="btn btn-sm btn-warning btn-editar">
                    Editar
                </button>

                <button
                    type="button"
                    class="btn btn-sm btn-danger btn-eliminar">
                    Eliminar
                </button>
            </td>
        `;

        fila
            .querySelector('.btn-editar')
            .addEventListener('click', () => {
                prepararEdicion(cliente);
            });

        fila
            .querySelector('.btn-eliminar')
            .addEventListener('click', () => {
                eliminarCliente(cliente);
            });

        tablaClientes.appendChild(fila);
    });
}

function prepararNuevoCliente() {
    formularioCliente.reset();

    clienteId.value = '';
    tituloModal.textContent = 'Registrar cliente';
    btnGuardarCliente.textContent = 'Guardar cliente';

    setTimeout(() => {
        clienteCedula.focus();
    }, 300);
}

function prepararEdicion(cliente) {
    clienteId.value = cliente.id;
    clienteCedula.value = cliente.cedula;
    clienteNombre.value = cliente.nombre_completo;
    clienteCorreo.value = cliente.correo ?? '';

    tituloModal.textContent = 'Modificar cliente';
    btnGuardarCliente.textContent = 'Guardar cambios';

    modalCliente.show();

    setTimeout(() => {
        clienteCedula.focus();
    }, 300);
}

async function guardarCliente() {
    const id = Number(clienteId.value) || null;
    const cedula = clienteCedula.value.trim();
    const nombre = clienteNombre.value.trim();
    const correo = clienteCorreo.value.trim();

    if (!/^[0-9]{10}$/.test(cedula)) {
        mostrarMensaje(
            'La cédula debe contener exactamente 10 números.',
            'danger'
        );

        clienteCedula.focus();
        return;
    }

    if (nombre.length < 3) {
        mostrarMensaje(
            'Ingrese el nombre completo del cliente.',
            'danger'
        );

        clienteNombre.focus();
        return;
    }

    if (correo !== '' && !clienteCorreo.checkValidity()) {
        mostrarMensaje(
            'Ingrese un correo electrónico válido.',
            'danger'
        );

        clienteCorreo.focus();
        return;
    }

    const esEdicion = id !== null;

    const datos = {
        cedula: cedula,
        nombre_completo: nombre,
        correo: correo
    };

    if (esEdicion) {
        datos.id = id;
    }

    btnGuardarCliente.disabled = true;
    btnGuardarCliente.textContent = 'Guardando...';

    try {
        const respuesta = await fetch(
            'backend/crud_clientes.php',
            {
                method: esEdicion ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            }
        );

        const resultado = await leerRespuesta(respuesta);

        modalCliente.hide();

        mostrarMensaje(
            resultado.mensaje,
            'success'
        );

        formularioCliente.reset();
        clienteId.value = '';

        await cargarClientes(buscarCliente.value.trim());

    } catch (error) {
        console.error(error);

        mostrarMensaje(
            error.message,
            'danger'
        );
    } finally {
        btnGuardarCliente.disabled = false;

        btnGuardarCliente.textContent =
            esEdicion
                ? 'Guardar cambios'
                : 'Guardar cliente';
    }
}

async function eliminarCliente(cliente) {
    const confirmado = confirm(
        `¿Está seguro de eliminar al cliente "${cliente.nombre_completo}"?`
    );

    if (!confirmado) return;

    try {
        const respuesta = await fetch(
            'backend/crud_clientes.php',
            {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: Number(cliente.id)
                })
            }
        );

        const resultado = await leerRespuesta(respuesta);

        mostrarMensaje(
            resultado.mensaje,
            'success'
        );

        await cargarClientes(buscarCliente.value.trim());

    } catch (error) {
        console.error(error);

        mostrarMensaje(
            error.message,
            'danger'
        );
    }
}

async function leerRespuesta(respuesta) {
    const texto = await respuesta.text();

    let datos;

    try {
        datos = JSON.parse(texto);
    } catch {
        throw new Error(
            `Respuesta inválida del servidor: ${texto}`
        );
    }

    if (!respuesta.ok) {
        throw new Error(
            datos.mensaje ||
            datos.detalle ||
            'Ocurrió un error.'
        );
    }

    return datos;
}

function mostrarMensaje(texto, tipo) {
    mensajeClientes.className = `alert alert-${tipo}`;
    mensajeClientes.textContent = texto;

    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });

    setTimeout(() => {
        mensajeClientes.classList.add('d-none');
    }, 4000);
}

function formatearFecha(fecha) {
    if (!fecha) {
        return 'Sin fecha';
    }

    const fechaConvertida = new Date(
        String(fecha).replace(' ', 'T')
    );

    if (Number.isNaN(fechaConvertida.getTime())) {
        return escaparHTML(fecha);
    }

    return fechaConvertida.toLocaleString('es-EC');
}

function escaparHTML(valor) {
    const elemento = document.createElement('div');
    elemento.textContent = valor ?? '';
    return elemento.innerHTML;
}