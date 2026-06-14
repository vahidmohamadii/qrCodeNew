window.qrcodePrint = (imageUrl, title) => {
    if (!imageUrl) {
        return;
    }

    const printWindow = window.open('', '_blank', 'width=900,height=900');
    if (!printWindow) {
        return;
    }

    const safeTitle = title || 'QR code';
    const doc = printWindow.document;

    doc.open();
    doc.write(`<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <style>
        html, body {
            margin: 0;
            width: 100%;
            height: 100%;
            background: #fff;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        img {
            display: block;
            max-width: 100vw;
            max-height: 100vh;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <img id="qr-code-print" />
</body>
</html>`);
    doc.close();

    doc.title = safeTitle;

    const image = doc.getElementById('qr-code-print');
    if (!image) {
        printWindow.close();
        return;
    }

    image.setAttribute('alt', safeTitle);

    let printed = false;
    const doPrint = () => {
        if (printed) {
            return;
        }

        printed = true;
        printWindow.focus();
        printWindow.print();
        setTimeout(() => printWindow.close(), 250);
    };

    image.onload = () => {
        setTimeout(doPrint, 250);
    };
    image.onerror = () => {
        printWindow.close();
    };
    image.src = imageUrl;

    if (image.complete) {
        setTimeout(doPrint, 250);
    }
};

window.qrcodeScrollTo = (elementId) => {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};
