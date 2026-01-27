<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SItuba | Export Kader</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px; font-size: 12px; }
        th { background: #f2f2f2; text-align: left; }
        h3 { margin: 0; }
        .meta { font-size: 12px; color: #555; margin-top: 4px; }
    </style>
</head>
<body>
    <h3>{{ $title ?? 'Export Kader' }}</h3>
    <p class="meta">Puskesmas: {{ auth()->user()->name ?? '-' }} | Tanggal: {{ now()->format('d M Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Nama</th>
                <th>Nomor HP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($kaders as $index => $kader)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $kader->name }}</td>
                    <td>{{ $kader->phone }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
