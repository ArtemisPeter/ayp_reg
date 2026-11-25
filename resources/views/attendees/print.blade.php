<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendees {{ now()->format('Y-m-d') }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Public Sans", "Segoe UI", Arial, sans-serif; font-size: 10pt; color: #1f1830; background: #fff; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; padding: 12px 16px; background: #f4eefa; border-bottom: 1px solid #ddd0ea; }
        .toolbar button, .toolbar a { font: inherit; padding: 8px 14px; border-radius: 6px; border: 1px solid #4a1d6e; background: #4a1d6e; color: #fff; cursor: pointer; text-decoration: none; }
        .toolbar a { background: #fff; color: #4a1d6e; }
        .toolbar span { color: #5d5470; }
        main { padding: 16px; }
        header { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; border-bottom: 2px solid #4a1d6e; padding-bottom: 8px; margin-bottom: 10px; }
        h1 { margin: 0; font-size: 16pt; color: #4a1d6e; }
        .sub { margin: 2px 0 0; color: #5d5470; }
        .filters { margin: 0 0 10px; color: #5d5470; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 5px 6px; border-bottom: 1px solid #e3dcec; text-align: left; vertical-align: top; }
        th { background: #4a1d6e; color: #fff; font-weight: 600; }
        thead { display: table-header-group; }
        tr { break-inside: avoid; }
        tbody tr:nth-child(even) td { background: #faf7fd; }
        td.num { color: #8a819c; width: 3em; }
        .muted { color: #8a819c; }
        .empty { padding: 24px; text-align: center; color: #5d5470; }
        @media print {
            .toolbar { display: none; }
            main { padding: 0; }
            th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tbody tr:nth-child(even) td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Save as PDF / Print</button>
        <a href="javascript:history.length > 1 ? history.back() : window.close()">Back</a>
        <span>In the print window, choose <strong>Save as PDF</strong> as the destination.</span>
    </div>
    <main>
        <header>
            <div>
                <h1>DUYOG {{ $camp ? "'".substr((string) $camp->year, -2) : '' }} Attendees</h1>
                @if ($camp?->theme)
                    <p class="sub">{{ $camp->theme }}@if ($camp->venue) · {{ $camp->venue }}@endif</p>
                @endif
            </div>
            <div class="sub">{{ number_format($attendees->count()) }} {{ str('attendee')->plural($attendees->count()) }} · Printed {{ now()->format('F j, Y g:i A') }}</div>
        </header>

        <p class="filters">{{ $filterSummary === [] ? 'Everyone registered' : implode(' · ', $filterSummary) }}</p>

        @if ($attendees->isEmpty())
            <p class="empty">No attendees match these filters.</p>
        @else
            <table>
                <thead>
                    <tr><th>#</th><th>Last Name</th><th>First Name</th><th>Middle Name</th><th>Local Church</th><th>Circuit</th><th>Birthday</th><th>Baptismal</th><th>Type</th><th>Registration</th></tr>
                </thead>
                <tbody>
                    @foreach ($attendees as $attendee)
                        <tr>
                            <td class="num">{{ $loop->iteration }}</td>
                            <td>{{ $attendee->last_name }}</td>
                            <td>{{ $attendee->first_name }}</td>
                            <td>{{ $attendee->middle_name ?? '' }}</td>
                            <td>{{ $attendee->localChurch->name }}</td>
                            <td>{{ $attendee->localChurch->circuit->name }}</td>
                            <td>{{ $attendee->birthday->format('Y-m-d') }}</td>
                            <td>{{ $attendee->is_baptized ? 'Yes' : 'No' }}</td>
                            <td>{{ ucfirst($attendee->type->value) }}</td>
                            <td>{{ $attendee->registration_type->value === 'pre_registration' ? 'Pre-Registration' : 'On-Site' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </main>
    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
</body>
</html>
