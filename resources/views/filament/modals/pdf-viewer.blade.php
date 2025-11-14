<div style="height: 80vh; min-height: 500px;">
<iframe
src="data:application/pdf;base64,{{ $pdfContent }}"
style="width: 100%; height: 100%; border: none;"
title="Visor de Reporte PDF"
>
<p class="text-center text-gray-500 p-8">Tu navegador no soporta iframes. <a href="data:application/pdf;base64,{{ $pdfContent }}" download="reporte.pdf" class="text-primary-600 font-medium">Puedes descargar el PDF aquí.</a></p>
</iframe>
</div>