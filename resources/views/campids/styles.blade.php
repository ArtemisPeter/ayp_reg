<style>
    @page { size: A4 portrait; margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Public Sans', Arial, sans-serif; background: #eee; }
    .sheet { width: 210mm; height: 297mm; display: grid; grid-template-columns: 105mm 105mm; grid-template-rows: 148.5mm 148.5mm; background: #fff; margin: 0 auto 8mm; page-break-after: always; }
    .card { position: relative; overflow: hidden; border: 0.2mm dashed #999; }
    .card-front .band { height: 45%; }
    .card-front .first-name { margin: 12mm 6mm 0; text-align: center; font-size: 14mm; font-weight: 700; line-height: 1.1; word-break: break-word; }
    .card-front .circuit { position: absolute; left: 0; right: 0; bottom: 10mm; text-align: center; font-size: 6mm; font-weight: 600; }
    .card-back { display: flex; align-items: center; justify-content: center; }
    .card-back .qr { width: 70mm; height: 70mm; }
    .card-back .qr svg { width: 100%; height: 100%; }
    .toolbar { position: sticky; top: 0; padding: 3mm 6mm; background: #333; color: #fff; display: flex; gap: 6mm; align-items: center; z-index: 5; }
    .toolbar a, .toolbar button { color: #fff; background: #566a7f; border: 0; padding: 2mm 4mm; border-radius: 1mm; text-decoration: none; cursor: pointer; font: inherit; }
    @media print { body { background: #fff; } .sheet { margin: 0; } .toolbar { display: none; } }
</style>
