const cuerpoTabla = document.getElementById('cuerpo-tabla');
const inputBusqueda = document.getElementById('input-busqueda');

let modalProducto;
let modoEdicion = false;

document.addEventListener('DOMContentLoaded', () => {
    modalProducto = new bootstrap.Modal(
        document.getElementById('modalProducto')
    );

    cargarProductos();

    inputBusqueda.addEventListener('input', () => {
        cargarProductos(inputBusqueda.value.trim());
    });
});

async function cargarProductos(busqueda = '') {
    try {
        const respuesta = await fetch(
            `backend/api_productos.php?q=${encodeURIComponent(busqueda)}`
        );

        const productos = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(productos.mensaje || 'No se pudieron cargar los productos.');
        }

        cuerpoTabla.innerHTML = '';

        if (productos.length === 0) {
            cuerpoTabla.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        No se encontraron productos.
                    </td>
                </tr>
            `;
            return;
        }

        productos.forEach(producto => {
            cuerpoTabla.innerHTML += `
                <tr>
                    <td>${producto.codigo_barras}</td>
                    <td>${producto.nombre_producto}</td>
                    <td>$${Number(producto.precio_actual).toFixed(2)}</td>
                    <td>${producto.stock_disponible}</td>
                    <td>
                        <button
                            class="btn btn-sm btn-warning"
                            onclick='editarProducto(${JSON.stringify(producto)})'
                        >
                            Editar
                        </button>

                        <button
                            class="btn btn-sm btn-danger"
                            onclick="eliminarProducto(${producto.id})"
                        >
                            Eliminar
                        </button>
                    </td>
                </tr>
            `;
        });

    } catch (error) {
        console.error(error);

        cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-danger py-4">
                    Error al cargar los productos.
                </td>
            </tr>
        `;
    }
}

function abrirModal() {
    modoEdicion = false;

    document.getElementById('modalTitulo').textContent = 'Nuevo producto';
    document.getElementById('prod-id').value = '';
    document.getElementById('prod-codigo').value = '';
    document.getElementById('prod-nombre').value = '';
    document.getElementById('prod-precio').value = '';
    document.getElementById('prod-stock').value = '';

    document.getElementById('prod-codigo').disabled = false;

    modalProducto.show();
}

function editarProducto(producto) {
    modoEdicion = true;

    document.getElementById('modalTitulo').textContent = 'Editar producto';
    document.getElementById('prod-id').value = producto.id;
    document.getElementById('prod-codigo').value = producto.codigo_barras;
    document.getElementById('prod-nombre').value = producto.nombre_producto;
    document.getElementById('prod-precio').value = producto.precio_actual;
    document.getElementById('prod-stock').value = producto.stock_disponible;

    document.getElementById('prod-codigo').disabled = true;

    modalProducto.show();
}

async function guardarProducto() {
    const id = document.getElementById('prod-id').value;
    const codigo = document.getElementById('prod-codigo').value.trim();
    const nombre = document.getElementById('prod-nombre').value.trim();
    const precio = Number(document.getElementById('prod-precio').value);
    const stock = Number(document.getElementById('prod-stock').value);

    if (!nombre || precio < 0 || stock < 0 || (!modoEdicion && !codigo)) {
        alert('Complete correctamente todos los campos.');
        return;
    }

    const datos = {
        id: Number(id),
        codigo,
        nombre,
        precio,
        stock
    };

    try {
        const respuesta = await fetch('backend/api_productos.php', {
            method: modoEdicion ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok || resultado.estado !== 'success') {
            throw new Error(resultado.mensaje || 'No se pudo guardar el producto.');
        }

        modalProducto.hide();
        cargarProductos(inputBusqueda.value.trim());

        alert(resultado.mensaje);

    } catch (error) {
        alert(`Error: ${error.message}`);
    }
}

async function eliminarProducto(id) {
    if (!confirm('¿Está seguro de eliminar este producto?')) {
        return;
    }

    try {
        const respuesta = await fetch(
            `backend/api_productos.php?id=${id}`,
            { method: 'DELETE' }
        );

        const resultado = await respuesta.json();

        if (!respuesta.ok || resultado.estado !== 'success') {
            throw new Error(resultado.mensaje || 'No se pudo eliminar el producto.');
        }

        cargarProductos(inputBusqueda.value.trim());
        alert(resultado.mensaje);

    } catch (error) {
        alert(`Error: ${error.message}`);
    }
}