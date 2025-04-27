<div class="w-full">
    @livewire('examen-drag-drop', key('examen-drag-drop-'.uniqid()))

    @push('scripts')
    <script>
document.addEventListener('livewire:initialized', () => {
    // Debug inicial
    console.log('Livewire inicializado - ExamenDragDrop');
    
    Livewire.on('examenesSeleccionadosUpdated', ({ examenes }) => {
        console.log('Evento recibido con exámenes:', examenes);
        
        if (!examenes || examenes.length === 0) {
            console.warn('Array de exámenes vacío recibido');
            return;
        }
        
        let form = document.querySelector('form[wire\\:submit="create"]');
        if (!form) {
            console.error('Formulario no encontrado');
            return;
        }
        
        let hiddenInput = form.querySelector('input[name="examenes_seleccionados"]');
        if (!hiddenInput) {
            console.log('Creando input hidden...');
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'examenes_seleccionados';
            form.appendChild(hiddenInput);
        }
        
        hiddenInput.value = JSON.stringify(examenes);
        console.log('Datos guardados en input:', hiddenInput.value);
    });
    
    // Disparar evento inicial con los exámenes actuales
    Livewire.dispatch('examenesSeleccionadosUpdated', { 
        examenes: @json($examenesSeleccionados ?? []) 
    });
});
</script>
    @endpush
</div>