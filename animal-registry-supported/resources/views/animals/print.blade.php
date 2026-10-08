<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $animal->name }} Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 24px;
            background: #f8fafc;
        }
        .page {
            background: white;
            border: 1px solid #e5e7eb;
            padding: 28px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        h1 {
            margin: 0;
            font-size: 30px;
            color: #111827;
        }
        .status {
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            background: #dcfce7;
            color: #166534;
        }
        .status.missing {
            background: #fef3c7;
            color: #92400e;
        }
        .status.deceased {
            background: #e5e7eb;
            color: #374151;
        }
        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 20px;
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px;
            background: #f9fafb;
        }
        .label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .value {
            font-size: 15px;
            font-weight: 600;
        }
        .section-title {
            font-size: 18px;
            margin: 0 0 12px;
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            text-align: left;
            font-size: 12px;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
        }
        .qr-box {
            width: 150px;
            height: 150px;
            border: 1px solid #e5e7eb;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }
        .small {
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div>
                <div class="small">Animal Registry Record</div>
                <h1>{{ $animal->name }}</h1>
            </div>
            <div class="status {{ $animal->status }}">{{ ucfirst($animal->status) }}</div>
        </div>

        <div class="meta">
            <div class="card">
                <span class="label">Species</span>
                <div class="value">{{ $animal->species?->name ?? 'Unknown' }}</div>
            </div>
            <div class="card">
                <span class="label">Category</span>
                <div class="value">{{ config('animal_categories.'.$animal->species?->category.'.label', ucfirst($animal->species?->category ?? 'Unknown')) }}</div>
            </div>
            <div class="card">
                <span class="label">Breed</span>
                <div class="value">{{ $animal->breed ?? 'Not provided' }}</div>
            </div>
            <div class="card">
                <span class="label">Sex</span>
                <div class="value">{{ ucfirst($animal->sex) }}</div>
            </div>
            <div class="card">
                <span class="label">Birthdate</span>
                <div class="value">{{ $animal->birthdate ? $animal->birthdate->format('M d, Y') : 'Not provided' }}</div>
            </div>
            <div class="card">
                <span class="label">Owner</span>
                <div class="value">{{ $animal->owner_name }}</div>
            </div>
            <div class="card">
                <span class="label">Contact</span>
                <div class="value">{{ $animal->owner_phone }}</div>
            </div>
            <div class="card">
                <span class="label">Group / quantity</span>
                <div class="value">{{ $animal->group_name ?? '—' }} · {{ $animal->quantity }}</div>
            </div>
            @foreach ($animal->attributes ?? [] as $key => $value)
                @if (filled($value))
                    @php
                        $attributeDefinition = collect(config('animal_categories.'.$animal->species?->category.'.fields', []))->firstWhere('key', $key);
                    @endphp
                    <div class="card">
                        <span class="label">{{ $attributeDefinition['label'] ?? \Illuminate\Support\Str::headline($key) }}</span>
                        <div class="value">{{ is_scalar($value) ? $value : json_encode($value) }}</div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="card" style="margin-bottom: 20px;">
            <span class="label">Address</span>
            <div class="value">{{ $animal->owner_address }}</div>
        </div>

        <div class="meta">
            <div class="card">
                <span class="label">QR Tag</span>
                <div class="qr-box">
                    {!! QrCode::size(120)->generate(route('animal.public', $animal->tag->identifier)) !!}
                </div>
                <div class="small" style="margin-top: 8px;">{{ $animal->tag?->identifier ?? 'No tag assigned' }}</div>
            </div>
            <div class="card">
                <span class="label">Notes</span>
                <div class="value" style="font-size: 13px; font-weight: 400;">{{ $animal->notes ?? 'No notes recorded.' }}</div>
            </div>
        </div>

        <div style="margin-top: 26px;">
            <h2 class="section-title">Vaccination History</h2>
            @if ($animal->vaccinations->isEmpty())
                <div class="small">No vaccination records found.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Vaccine</th>
                            <th>Given</th>
                            <th>Next Due</th>
                            <th>Batch</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($animal->vaccinations as $vaccination)
                            <tr>
                                <td>{{ $vaccination->vaccine_name }}</td>
                                <td>{{ $vaccination->given_on?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $vaccination->next_due_on ? $vaccination->next_due_on->format('M d, Y') : '—' }}</td>
                                <td>{{ $vaccination->batch_no ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div style="margin-top: 26px;">
            <h2 class="section-title">Health Records</h2>
            @if ($animal->healthRecords->isEmpty())
                <div class="small">No health records found.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Recorded</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($animal->healthRecords as $record)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $record->type)) }}</td>
                                <td>{{ $record->value_numeric }}{{ $record->unit ? ' ' . $record->unit : '' }}</td>
                                <td>{{ $record->recorded_at->format('M d, Y H:i') }}</td>
                                <td>{{ $record->details ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</body>
</html>
