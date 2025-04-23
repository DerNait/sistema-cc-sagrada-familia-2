<template>
	<div class="container mt-5">
		<h1>Lista de Empleados</h1>
		<button 
			class="btn btn-primary mt-3"
			@click="descargarPlanilla"
		>
			<i class="bi bi-download"></i> Descargar Plantilla
		</button>
	</div>
</template>
  
<script setup>

function descargarPlanilla() {
	console.log("Descargando planilla...");

	window.location.href = "/empleados/planilla"
}

</script>