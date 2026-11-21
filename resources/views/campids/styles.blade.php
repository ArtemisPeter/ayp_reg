<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Public+Sans:wght@400;600;800&display=block" rel="stylesheet">
<style>
    @page { size: A4 portrait; margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Public Sans', Arial, sans-serif; background: #eee; }
    .sheet { width: 210mm; height: 297mm; display: grid; grid-template-columns: 105mm 105mm; grid-template-rows: 148.5mm 148.5mm; background: #fff; margin: 0 auto 8mm; page-break-after: always; }
    .card { position: relative; overflow: hidden; }
    .card::after { content: ''; position: absolute; inset: 0; border: 0.2mm dashed rgba(255, 255, 255, .55); pointer-events: none; }
    .card .art { position: absolute; inset: 0; width: 100%; height: 100%; display: block; }

    /* Front: positions match the white name panel of public/images/camp-id/front.jpg (1230 x 1740 px at 300 dpi). */
    .card-front .first-name { position: absolute; left: 12mm; right: 12mm; top: 88mm; height: 16mm; display: flex; align-items: center; justify-content: center; font-family: 'Cinzel', Georgia, serif; font-weight: 700; font-size: 17mm; line-height: 1; color: #3b1650; text-transform: uppercase; white-space: nowrap; }
    .card-front .circuit { position: absolute; left: 16mm; right: 16mm; top: 105.2mm; text-align: center; font-family: 'Public Sans', Arial, sans-serif; font-weight: 800; font-size: 3.8mm; line-height: 1; letter-spacing: -.02em; text-transform: uppercase; white-space: nowrap; }

    /* Back: the QR sits in the grey box at the top left of public/images/camp-id/back.jpg. */
    .card-back .qr { position: absolute; left: 5.1mm; top: 14.8mm; width: 29.7mm; height: 26.4mm; display: flex; align-items: center; justify-content: center; background: #fff; }
    .card-back .qr svg { width: 24mm; height: 24mm; }

    .toolbar { position: sticky; top: 0; padding: 3mm 6mm; background: #333; color: #fff; display: flex; gap: 6mm; align-items: center; z-index: 5; }
    .toolbar a, .toolbar button { color: #fff; background: #566a7f; border: 0; padding: 2mm 4mm; border-radius: 1mm; text-decoration: none; cursor: pointer; font: inherit; }
    @media print { body { background: #fff; } .sheet { margin: 0; } .toolbar { display: none; } }
</style>
<script>
    /* Shrinks a long first name until it fits on one line inside the name panel. */
    document.fonts.ready.then(() => {
        document.querySelectorAll('.card-front .first-name, .card-front .circuit').forEach((line) => {
            const text = line.querySelector('span');
            let size = parseFloat(getComputedStyle(line).fontSize);
            const minimum = size * 0.4;

            while (text.offsetWidth > line.clientWidth && size > minimum) {
                size -= 1;
                line.style.fontSize = size + 'px';
            }
        });
    });
</script>
