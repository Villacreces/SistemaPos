let carrito = [];
const IVA = 0.15;
let clienteSeleccionado = null;

const buscador = document.getElementById('busqueda-producto');
const resultados = document.getElementById('resultados-productos');
const cuerpoCarrito = document.getElementById('cuerpo-carrito');
const montoPagado = document.getElementById('monto-pagado');
const buscadorCliente = document.getElementById('busqueda-cliente');
const resultadosClientes = document.getElementById('resultados-clientes');
const clienteSeleccionadoTexto =
    document.getElementById('cliente-seleccionado');

buscador.addEventListener('input', buscarProductos);
buscador.addEventListener('keydown', agregarConEnter);
montoPagado.addEventListener('input', calcularCambio);
buscadorCliente.addEventListener('input', buscarClientes);

async function buscarProductos() {
    const texto = buscador.value.trim();

    if (texto === '') {
        resultados.innerHTML = '';
        return;
    }

    try {
        const respuesta = await fetch(
            `backend/api_productos.php?q=${encodeURIComponent(texto)}`
        );

        const productos = await respuesta.json();

        resultados.innerHTML = '';

        productos.forEach(producto => {
            const boton = document.createElement('button');

            boton.type = 'button';
            boton.className = 'list-group-item list-group-item-action';

            boton.innerHTML = `
                <strong>${producto.nombre_producto}</strong><br>
                <small>
                    Código: ${producto.codigo_barras} |
                    Precio: $${Number(producto.precio_actual).toFixed(2)} |
                    Stock: ${producto.stock_disponible}
                </small>
            `;

            boton.onclick = () => agregarProducto(producto);
            resultados.appendChild(boton);
        });

    } catch (error) {
        console.error(error);
    }
}

async function agregarConEnter(evento) {
    if (evento.key !== 'Enter') return;

    evento.preventDefault();

    const codigo = buscador.value.trim();

    if (codigo === '') return;

    try {
        const respuesta = await fetch(
            `backend/api_productos.php?q=${encodeURIComponent(codigo)}`
        );

        const productos = await respuesta.json();

        const producto = productos.find(
            item => item.codigo_barras === codigo
        );

        if (producto) {
            agregarProducto(producto);
        } else {
            alert('No existe un producto con ese código.');
        }

    } catch (error) {
        console.error(error);
    }
}

function agregarProducto(producto) {
    const existente = carrito.find(
        item => item.id === Number(producto.id)
    );

    if (existente) {
        if (existente.cantidad >= existente.stock) {
            alert('No hay suficiente stock.');
            return;
        }

        existente.cantidad++;
    } else {
        if (Number(producto.stock_disponible) <= 0) {
            alert('El producto no tiene stock.');
            return;
        }

        carrito.push({
            id: Number(producto.id),
            codigo: producto.codigo_barras,
            nombre: producto.nombre_producto,
            precio: Number(producto.precio_actual),
            stock: Number(producto.stock_disponible),
            cantidad: 1
        });
    }

    buscador.value = '';
    resultados.innerHTML = '';
    buscador.focus();

    mostrarCarrito();
}

function mostrarCarrito() {
    cuerpoCarrito.innerHTML = '';

    if (carrito.length === 0) {
        cuerpoCarrito.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    No hay productos agregados.
                </td>
            </tr>
        `;

        actualizarTotales();
        return;
    }

    carrito.forEach(producto => {
        const subtotal = producto.precio * producto.cantidad;

        cuerpoCarrito.innerHTML += `
            <tr>
                <td>${producto.codigo}</td>
                <td>${producto.nombre}</td>
                <td>$${producto.precio.toFixed(2)}</td>

                <td>
                    <button class="btn btn-sm btn-outline-secondary"
                            onclick="cambiarCantidad(${producto.id}, -1)">−</button>

                    <span class="mx-2">${producto.cantidad}</span>

                    <button class="btn btn-sm btn-outline-secondary"
                            onclick="cambiarCantidad(${producto.id}, 1)">+</button>
                </td>

                <td>$${subtotal.toFixed(2)}</td>

                <td>
                    <button class="btn btn-sm btn-danger"
                            onclick="eliminarProducto(${producto.id})">
                        Eliminar
                    </button>
                </td>
            </tr>
        `;
    });

    actualizarTotales();
}

function cambiarCantidad(id, cambio) {
    const producto = carrito.find(item => item.id === id);

    if (!producto) return;

    const nuevaCantidad = producto.cantidad + cambio;

    if (nuevaCantidad <= 0) {
        eliminarProducto(id);
        return;
    }

    if (nuevaCantidad > producto.stock) {
        alert('No hay suficiente stock.');
        return;
    }

    producto.cantidad = nuevaCantidad;
    mostrarCarrito();
}

function eliminarProducto(id) {
    carrito = carrito.filter(item => item.id !== id);
    mostrarCarrito();
}

function actualizarTotales() {
    const subtotal = carrito.reduce(
        (total, producto) =>
            total + producto.precio * producto.cantidad,
        0
    );

    const iva = subtotal * IVA;
    const total = subtotal + iva;

    document.getElementById('subtotal').textContent =
        `$${subtotal.toFixed(2)}`;

    document.getElementById('iva').textContent =
        `$${iva.toFixed(2)}`;

    document.getElementById('total').textContent =
        `$${total.toFixed(2)}`;

    calcularCambio();
}

function calcularCambio() {
    const subtotal = carrito.reduce(
        (total, producto) => total + producto.precio * producto.cantidad,
        0
    );

    const total = subtotal + (subtotal * IVA);
    const pagado = Number(montoPagado.value) || 0;
    const cambio = pagado - total;

    document.getElementById('cambio').value =
        `$${Math.max(cambio, 0).toFixed(2)}`;
}
async function buscarClientes() {
    const texto = buscadorCliente.value.trim();

    clienteSeleccionado = null;

    if (texto === '') {
        resultadosClientes.innerHTML = '';
        clienteSeleccionadoTexto.textContent =
            'Cliente: Consumidor final';
        return;
    }

    try {
        const respuesta = await fetch(
            `backend/api_clientes.php?q=${encodeURIComponent(texto)}`
        );

        const textoRespuesta = await respuesta.text();

        let clientes;

        try {
            clientes = JSON.parse(textoRespuesta);
        } catch {
            throw new Error(
                `Respuesta inválida del servidor: ${textoRespuesta}`
            );
        }

        if (!respuesta.ok) {
            throw new Error(
                clientes.mensaje || 'No se pudieron cargar los clientes.'
            );
        }

        resultadosClientes.innerHTML = '';

        if (!Array.isArray(clientes) || clientes.length === 0) {
            resultadosClientes.innerHTML = `
                <div class="list-group-item text-muted">
                    No se encontraron clientes.
                </div>
            `;

            return;
        }

        clientes.forEach(cliente => {
            const boton = document.createElement('button');

            boton.type = 'button';
            boton.className =
                'list-group-item list-group-item-action';

            boton.innerHTML = `
    <strong>${cliente.nombre_completo}</strong>
    <br>
    <small>
        Cédula: ${cliente.cedula}
        ${cliente.correo
            ? ` | Correo: ${cliente.correo}`
            : ''}
    </small>
`;

            boton.addEventListener('click', () => {
                seleccionarCliente(cliente);
            });

            resultadosClientes.appendChild(boton);
        });

    } catch (error) {
        console.error(error);

        resultadosClientes.innerHTML = `
            <div class="list-group-item text-danger">
                Error al buscar clientes.
            </div>
        `;
    }
}
function seleccionarCliente(cliente) {
    clienteSeleccionado = Number(cliente.id);

    buscadorCliente.value = cliente.nombre_completo;

    clienteSeleccionadoTexto.textContent =
        `Cliente: ${cliente.nombre_completo} — ${cliente.cedula}`;

    resultadosClientes.innerHTML = '';
}


function usarConsumidorFinal() {
    clienteSeleccionado = null;

    document.getElementById('busqueda-cliente').value = '';
    document.getElementById('resultados-clientes').innerHTML = '';
    document.getElementById('cliente-seleccionado').textContent =
        'Cliente: Consumidor final';
}

async function procesarVenta() {
    if (carrito.length === 0) {
        alert('Debe agregar productos al carrito.');
        return;
    }

    const subtotal = carrito.reduce(
        (total, producto) => total + producto.precio * producto.cantidad,
        0
    );

    const iva = subtotal * IVA;
    const total = subtotal + iva;
    const pagado = Number(montoPagado.value) || 0;

    if (pagado < total) {
        alert('El monto pagado es insuficiente.');
        montoPagado.focus();
        return;
    }

    try {
        const respuesta = await fetch('backend/procesar_venta.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                cliente_id: clienteSeleccionado,
                productos: carrito,
                subtotal: subtotal,
                iva: iva,
                total: total,
                pagado: pagado,
                cambio: pagado - total
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
            throw new Error(resultado.mensaje || 'No se pudo guardar la venta.');
        }

        alert(`Venta N.º ${resultado.venta_id} procesada correctamente.`);
        window.open(
    `factura.php?id=${resultado.venta_id}`,
    '_blank'
);

        carrito = [];
        montoPagado.value = '';
        usarConsumidorFinal();
        mostrarCarrito();
        buscador.focus();

    } catch (error) {
        console.error(error);
        alert(`Error: ${error.message}`);
    }
}