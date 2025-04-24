<template>
	<div class="container mt-5">
		<h1>Lista de Empleados</h1>
		<button 
			class="btn btn-primary mt-3"
			@click="descargarPlanilla"
			:disabled="!userIsAdmin"
		>
			<i class="bi bi-download"></i> Descargar Plantilla
		</button>
		<p v-if="!userIsAdmin" class="text-danger mt-2">
			Solo los administradores pueden descargar la planilla.
		</p>
	</div>
</template>

<script setup>
const userIsAdmin = true; // Cambia a false para probar como usuario normal

function descargarPlanilla() {
	
	if (!userIsAdmin) {
		alert("No tienes permiso para descargar la planilla.");
		console.error("Intento no autorizado de descarga.");
		return;
	}

	console.log("Descargando planilla...");
	window.location.href = "/empleados/planilla";
}
</script>