<h3 class="mb-2 px-4 py-2 flex items-center justify-between font-semibold text-md text-gray-900 dark:text-gray-100 bg-primary-200 dark:bg-gray-900 rounded-lg shadow-lg dark:shadow-[0_4px_6px_rgba(255,255,255,0.1)] dark:border dark:border-gray-600">
    <div class="flex items-center">
        <!-- Ícono decorativo -->
        <span class="text-primary-600 dark:text-primary-400 mr-2">❖­­­­­</span> 
        <span>ㅤ</span>
        <span>{{ $status['title'] }}</span>
        <span>ㅤ</span>
        <!-- Contador de registros -->
        <span class="text-xs font-medium ml-3 bg-primary-100 dark:bg-primary-800 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-full shadow-sm dark:shadow-[0_2px_4px_rgba(255,255,255,0.1)]">
            {{ count($status['records'] ?? []) }}
        </span>
    </div>

    <!-- Icono de impresión -->
    <a  
        href="{{ route('grupo.zpl', ['status' => $status['id'], 'ordenId' => $this->ordenId]) }}"
        target="_blank"
        rel="noopener"
        class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
        title="Imprimir todas las etiquetas de este grupo"
    >
        <x-heroicon-o-printer class="w-5 h-5" />
    </a>
</h3>