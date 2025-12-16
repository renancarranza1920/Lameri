<script src="{{ asset('js/qz-tray.js') }}"></script>
<script src="{{ asset('js/sha256.min.js') }}"></script>
<script src="{{ asset('js/sign-message.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsrsasign/10.9.0/jsrsasign-all-min.js"></script>
<script>
    function onStart() {
        setTimeout(() => document.body.classList.add("grabbing"))
    }

    function onEnd() {
        document.body.classList.remove("grabbing")
    }

    function setData(dataTransfer, el) {
        dataTransfer.setData('id', el.id)
    }

    function onAdd(e) {
        const recordId = e.item.id
        const status = e.to.dataset.statusId
        const fromOrderedIds = [].slice.call(e.from.children).map(child => child.id)
        const toOrderedIds = [].slice.call(e.to.children).map(child => child.id)

        Livewire.dispatch('status-changed', {recordId, status, fromOrderedIds, toOrderedIds})
    }

    function onUpdate(e) {
        const recordId = e.item.id
        const status = e.from.dataset.statusId
        const orderedIds = [].slice.call(e.from.children).map(child => child.id)

        Livewire.dispatch('sort-changed', {recordId, status, orderedIds})
    }

    document.addEventListener('livewire:navigated', () => {
        const statuses = @js($statuses->pluck('id')->values()->toArray());

        statuses.forEach(status => {
            const container = document.querySelector(`[data-status-id='${status}']`);
            if (container) {
                Sortable.create(container, {
                    group: 'filament-kanban',
                    ghostClass: 'opacity-50',
                    animation: 150,
                    onStart,
                    onEnd,
                    onUpdate,
                    setData,
                    onAdd,
                });
            }
        });
    });
</script>

<script>
// Función para esperar a que la librería QZ cargue
function waitForQZ(callback) {
    if (window.qz) {
        callback();
    } else {
        setTimeout(() => waitForQZ(callback), 100);
    }
}

waitForQZ(() => {
    console.log("🔥 Librería QZ Tray cargada. Configurando certificado...");

 qz.security.setCertificatePromise(function(resolve, reject) {
        //Preferred method - from server
//        fetch("assets/signing/digital-certificate.txt", {cache: 'no-store', headers: {'Content-Type': 'text/plain'}})
//          .then(function(data) { data.ok ? resolve(data.text()) : reject(data.text()); });

        //Alternate method 1 - anonymous
//        resolve();  // remove this line in live environment

        //Alternate method 2 - direct
        resolve("-----BEGIN CERTIFICATE-----\n" +
"MIIECzCCAvOgAwIBAgIGAZsQMIfiMA0GCSqGSIb3DQEBCwUAMIGiMQswCQYDVQQG\n" +
"EwJVUzELMAkGA1UECAwCTlkxEjAQBgNVBAcMCUNhbmFzdG90YTEbMBkGA1UECgwS\n" +
"UVogSW5kdXN0cmllcywgTExDMRswGQYDVQQLDBJRWiBJbmR1c3RyaWVzLCBMTEMx\n" +
"HDAaBgkqhkiG9w0BCQEWDXN1cHBvcnRAcXouaW8xGjAYBgNVBAMMEVFaIFRyYXkg\n" +
"RGVtbyBDZXJ0MB4XDTI1MTIxMTAxMzI1NFoXDTQ1MTIxMTAxMzI1NFowgaIxCzAJ\n" +
"BgNVBAYTAlVTMQswCQYDVQQIDAJOWTESMBAGA1UEBwwJQ2FuYXN0b3RhMRswGQYD\n" +
"VQQKDBJRWiBJbmR1c3RyaWVzLCBMTEMxGzAZBgNVBAsMElFaIEluZHVzdHJpZXMs\n" +
"IExMQzEcMBoGCSqGSIb3DQEJARYNc3VwcG9ydEBxei5pbzEaMBgGA1UEAwwRUVog\n" +
"VHJheSBEZW1vIENlcnQwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDe\n" +
"2hhdVLx8QT7UepRot857xNn66y+CPsSmSYDanj+6TEiqyKBeFHzpxUUmHT/LlWfF\n" +
"ZNG7KknHc0SyerPluL3+EgI9gUdVi1lYZUX1ks/HsglPODR8q1muyv/5Jhm3143p\n" +
"Pf03wPNFbpsmkYDWLzrOOiAngXrszcsLYttEbEDvVWaFmb/tTT42+DiGKntRGtFe\n" +
"rmU2e8MV0tOpm9w9YxyMh7Pvm0P2jXbHfI9dUFZt8hQdSm/SIOyjqVdCklnaf3rO\n" +
"27mFxlGo42RPCl0ir+iN82/fke0HB00Ifu9nmA5/hWzNQr4qMh0PtWjes4eTGTaH\n" +
"rzoK1VWbLYs1+ZP4n1f7AgMBAAGjRTBDMBIGA1UdEwEB/wQIMAYBAf8CAQEwDgYD\n" +
"VR0PAQH/BAQDAgEGMB0GA1UdDgQWBBTlc4ku4aNHW+bXw/3ehRdr5+DBdzANBgkq\n" +
"hkiG9w0BAQsFAAOCAQEAg9qqp9Z7mkuMb+1PLFuO9+4RwaP15280oWPmDRx4rNAg\n" +
"RNwzTD+9njpldlSCzEpvWl0rv2vIs++0236enti89ywtMeWcViBZmxXuHtkhSh26\n" +
"z4iLHCwtbHxa51LB3OcD17D88S67GxICyA7h/KMqyrXsdaPbI0O0GsNkxu/ZiFrf\n" +
"qLQemayyYybMSdujqnIRpyy5fcPQNF+e440yVTVipG5fskQEnLxkdnspCWozGOjI\n" +
"kqbMjfHe5hANoMzyvco2f5MFJZeeUFwpWuktzGDO70Ye4ospOMXE2sscS500ynFW\n" +
"wOj5VeKTsKspX6yzzzhHke9tHey7eCi9p3OMl8aMqw==\n" +
"-----END CERTIFICATE-----");
    });

    qz.security.setSignatureAlgorithm("SHA512"); // Since 2.1




    // 3. IMPRESIÓN (esto está OK)
    document.addEventListener('livewire:init', () => {
        Livewire.on('imprimirZplFrontend', (eventData) => {
            let zplCode = eventData.zpl || eventData[0]?.zpl;

            if (!zplCode) {
                alert("Error: No se recibió código ZPL.");
                return;
            }

            let connectionPromise = qz.websocket.isActive()
                ? Promise.resolve()
                : qz.websocket.connect();

            connectionPromise.then(() => {
                const printerName = "ZDesigner ZD230-203dpi ZPL";

                return qz.printers.find(printerName).then(() => {
                    let config = qz.configs.create(printerName, { raw: true });
                    return qz.print(config, [{
                        type: 'raw',
                        format: 'plain',
                        data: zplCode
                    }]);
                });
            })
            .catch(err => alert("Error: " + err));
        });
    });
});

</script>
