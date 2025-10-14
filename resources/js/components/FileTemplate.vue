<template>
    <div class="bg-secondary">
        <div class="d-flex gap-2">
            <img :src="icon" alt="Icono" class="file-icon" />
            <h5 class="mb-0">{{ file_title }}</h5>
        </div>
        <div class="d-flex justify-content-between">
            <div>
                <p class="mb-1">Descarga la plantilla para subir el archivo con el formato correcto.</p>
                <p class="mb-0">Luego, súbelo usando el botón de "Seleccionar archivo".</p>
            </div>
            <a :href="template_url" class="btn btn-primary" download>
                Descargar
            </a>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    template_url: { type: String, required: true },
    icon: { type: String, default: '/images/file-icon.png' },
    file_title: { type: String, default: 'Plantilla' },
});
</script>

