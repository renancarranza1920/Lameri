/*
 * Lógica de firma del lado del Cliente (Browser)
 * Requiere: jsrsasign-all-min.js
 */

// TU LLAVE PRIVADA (La que corresponde al certificado GRUPO5)
var privateKey = "-----BEGIN PRIVATE KEY-----\n" +
"MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDe2hhdVLx8QT7U\n" +
"epRot857xNn66y+CPsSmSYDanj+6TEiqyKBeFHzpxUUmHT/LlWfFZNG7KknHc0Sy\n" +
"erPluL3+EgI9gUdVi1lYZUX1ks/HsglPODR8q1muyv/5Jhm3143pPf03wPNFbpsm\n" +
"kYDWLzrOOiAngXrszcsLYttEbEDvVWaFmb/tTT42+DiGKntRGtFermU2e8MV0tOp\n" +
"m9w9YxyMh7Pvm0P2jXbHfI9dUFZt8hQdSm/SIOyjqVdCklnaf3rO27mFxlGo42RP\n" +
"Cl0ir+iN82/fke0HB00Ifu9nmA5/hWzNQr4qMh0PtWjes4eTGTaHrzoK1VWbLYs1\n" +
"+ZP4n1f7AgMBAAECggEABJ2VDJphLwDHy/+EgzsBoU4vpnCxjiKHGgACukRsp1QP\n" +
"SPLPzungt5OiBu9rJjE3MhF+ItMIUt8gFlViZpsftxtWzq6WtGxdyW35QOQtdV24\n" +
"HTZsArsCFbhXVd/EWwYMz+M8brn4HZyrDhxeqKxp4720mBFxrEEwU1FCn3nz7qEE\n" +
"ot+He5g0xQUPPUDDK+iIigE2ktuoBMU/aYfHp/XubmPs4M8Ck1TqG5WevcKD4MUM\n" +
"FDpNO1Pb9urkwtd87Zmi+Uf8UrQSMGNVr+bXiQpbJuWrlr/oS4GJROuhUHwCA6OX\n" +
"kBAp/is0pH2XCT/vFbbhopYhW/W/qre90Com4gGOHQKBgQD3ihvPxlX8jhQcekTA\n" +
"oUI2B+s3648o4Pr50fLs1+T2uzqCcxSF5omZSyVn4QpmwZquYJYh3Gs2BCg40PGH\n" +
"HZr2Euoh192VnmBiTeAMb8N1zfqPIxrjjIPeKijxT8ukCF5AWNPfFoQqyIrGe3RD\n" +
"mSNYH1UdagR6BBFvSyiwkPCyFQKBgQDmd/p0t0cnq8CXEE1hUyZtrTc4N9h1be0B\n" +
"2rjdH8LOQldr5Tiu4g1R9gNToJ/+mZAMAU99tKACbd/+CnFoA1Yw5xZZpcdfT4Iy\n" +
"5vG0xjxCBBPWTCBaRTYbuvArr6EoslBDpogNej3N6H+bnWHunp29QpvITmBjAz/z\n" +
"d2X0od01zwKBgAlh3rP9XHz6FaEOsVRryEqr+ipgb0ipXlnhr0dy12bSqeEP45M/\n" +
"ra91vbE2QmKqq+Y4GvillhqyBaRBpyXr5Ve2V9F4kRl+q0COmxNdmRb5+6k5N2ew\n" +
"h77/8hVTZPCOsKPXuhAWlmPYvgnP3BRD1acdaOh4gDrvtIl3W891buoRAoGAPmuE\n" +
"ZBQ3q2DzOnbp3lA8+juN1yYY9EUfif8JTqqJgn+pWcmtfoAvB2HQKNg6PSSuRPId\n" +
"63f+VmoX3qBJcthkgb406Xbi9sd8BflSwZlnPKIKFWJs6MYSD5Sj89EPnwwcfRYf\n" +
"hzbyglBUL3uCyLQhGN9vfeLsbCS3L2PJM2abmTUCgYEAgyyJoOm5ON05jb12snJT\n" +
"VNq++13EXV1qAw/gWd1dNCHrG8nz4ZW9OKcUBp4rJpWtB/NMiRzZiTQmUsiujbcB\n" +
"yzUNIo42cqDwzRbG7TIuJ4RB4Ukm4LxdmbSqDew8EZmOf4xRWbuyNrcIpoXNXiLc\n" +
"y5zMNWM5BTP+oYGfbQS015E=\n" +
"-----END PRIVATE KEY-----";

// Algoritmo de firma
qz.security.setSignatureAlgorithm("SHA512"); 

// Sobreescribimos la promesa de firma para que use la librería JS local
qz.security.setSignaturePromise(function(toSign) {
    return function(resolve, reject) {
        try {
            // Usamos la librería jsrsasign para firmar localmente
            var pk = KEYUTIL.getKey(privateKey);
            var sig = new KJUR.crypto.Signature({"alg": "SHA512withRSA"});
            sig.init(pk); 
            sig.updateString(toSign);
            var hex = sig.sign();
            console.log("Firma generada localmente en JS");
            resolve(stob64(hextorstr(hex)));
        } catch (err) {
            console.error(err);
            reject(err);
        }
    };
});