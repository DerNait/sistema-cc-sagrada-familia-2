<template>
    <div class="container py-5">
        <h1 class="text-center mb-4 text-success fw-bold">
        📦 Historial Completo de Movimientos de Inventario
        </h1>

        <div class="table-responsive shadow rounded-4 bg-white p-3">
        <table class="table table-striped align-middle text-center">
            <thead class="table-success">
            <tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Tipo Movimiento</th>
                <th>Cantidad</th>
                <th>Stock Antes</th>
                <th>Stock Después</th>
                <th>Usuario</th>
                <th>Descripción</th>
                <th>Fecha</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="mov in movimientos" :key="mov.id">
                <td>{{ mov.id }}</td>
                <td>{{ mov.producto?.nombre ?? '—' }}</td>
                <td>{{ mov.tipo_movimiento?.tipo ?? '—' }}</td>
                <td>{{ mov.cantidad }}</td>
                <td>{{ mov.stock_pre }}</td>
                <td>{{ mov.stock_post }}</td>
                <td>{{ mov.usuario?.name ?? '—' }}</td>
                <td>{{ mov.descripcion }}</td>
                <td>{{ new Date(mov.fecha).toLocaleString() }}</td>
            </tr>
            </tbody>
        </table>
        </div>
    </div>
</template>

<script setup>
    const props = defineProps({
    movimientos: {
        type: Array,
        default: () => []
    }
    })
    </script>

    <style scoped>
    .container {
    max-width: 1200px;
    }
    .table {
    border-radius: 1rem;
    overflow: hidden;
    }
    thead {
    font-weight: bold;
    text-transform: uppercase;
    }
    tbody tr:hover {
    background-color: #f8fff5;
    transition: 0.3s;
    }
</style>
