<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RentMate Rental Statement</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #0d9488;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #0f172a;
            margin: 0 0 5px 0;
            font-size: 20px;
        }
        .header p {
            margin: 2px 0;
            color: #64748b;
            font-size: 10px;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 4px 8px;
            font-size: 11px;
        }
        .summary-table strong {
            color: #0f172a;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 9px;
            text-transform: uppercase;
            padding: 8px 6px;
            border-bottom: 1px solid #cbd5e1;
            text-align: left;
        }
        .data-table td {
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .badge-paid { background-color: #d1fae5; color: #065f46; }
        .badge-partial { background-color: #fef3c7; color: #92400e; }
        .badge-unpaid { background-color: #ffe4e6; color: #9f1239; }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1>RentMate – Rental Expense Statement</h1>
                    <p><strong>Property:</strong> {{ $settings->property_address ?? 'Kathmandu, Nepal' }}</p>
                    <p><strong>Landlord:</strong> {{ $settings->owner_name ?? 'House Owner' }} ({{ $settings->owner_phone ?? 'N/A' }})</p>
                </td>
                <td class="text-right">
                    <p><strong>Generated Date:</strong> {{ date('F d, Y') }}</p>
                    <p><strong>Tenant Move-in Date:</strong> {{ $settings->rental_start_date ? $settings->rental_start_date->format('M Y') : 'N/A' }}</p>
                    <p><strong>Currency:</strong> NPR (Nepalese Rupee)</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td>Total Rental Expenses: <strong>NPR {{ number_format($totalExpenses, 2) }}</strong></td>
                <td>Total Rent Paid: <strong>NPR {{ number_format($totalRent, 2) }}</strong></td>
                <td>Total Electricity: <strong>NPR {{ number_format($totalElectricity, 2) }}</strong></td>
            </tr>
            <tr>
                <td>Total Water (50% Share): <strong>NPR {{ number_format($totalWater, 2) }}</strong></td>
                <td>Total Paid to Date: <strong style="color: #059669;">NPR {{ number_format($totalPaid, 2) }}</strong></td>
                <td>Outstanding Balance Due: <strong style="color: {{ $totalBalance > 0 ? '#e11d48' : '#059669' }};">NPR {{ number_format($totalBalance, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Period</th>
                <th class="text-right">Rent</th>
                <th class="text-right">Units</th>
                <th class="text-right">Electricity</th>
                <th class="text-right">Water (50%)</th>
                <th class="text-right">Other</th>
                <th class="text-right">Total Expense</th>
                <th class="text-right">Total Paid</th>
                <th class="text-right">Balance Due</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $rec)
                <tr>
                    <td class="font-bold">{{ $rec->formatted_month }}</td>
                    <td class="text-right">NPR {{ number_format($rec->rent_amount, 2) }}</td>
                    <td class="text-right">{{ $rec->electricity_units }}</td>
                    <td class="text-right">NPR {{ number_format($rec->electricity_amount, 2) }}</td>
                    <td class="text-right">NPR {{ number_format($rec->water_amount, 2) }}</td>
                    <td class="text-right">NPR {{ number_format($rec->other_charges, 2) }}</td>
                    <td class="text-right font-bold">NPR {{ number_format($rec->total_expenses, 2) }}</td>
                    <td class="text-right font-bold" style="color: #059669;">NPR {{ number_format($rec->total_paid, 2) }}</td>
                    <td class="text-right font-bold" style="color: {{ $rec->balance > 0 ? '#e11d48' : '#059669' }};">
                        NPR {{ number_format($rec->balance, 2) }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $rec->status === 'paid' ? 'badge-paid' : ($rec->status === 'partially_paid' ? 'badge-partial' : 'badge-unpaid') }}">
                            {{ $rec->status_label }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #cbd5e1;">
                <td>TOTALS</td>
                <td class="text-right">NPR {{ number_format($totalRent, 2) }}</td>
                <td class="text-right">-</td>
                <td class="text-right">NPR {{ number_format($totalElectricity, 2) }}</td>
                <td class="text-right">NPR {{ number_format($totalWater, 2) }}</td>
                <td class="text-right">NPR {{ number_format($totalOther, 2) }}</td>
                <td class="text-right">NPR {{ number_format($totalExpenses, 2) }}</td>
                <td class="text-right" style="color: #059669;">NPR {{ number_format($totalPaid, 2) }}</td>
                <td class="text-right" style="color: {{ $totalBalance > 0 ? '#e11d48' : '#059669' }};">
                    NPR {{ number_format($totalBalance, 2) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated automatically by RentMate – Personal Rent & Expense Tracker on {{ date('Y-m-d H:i') }}.
    </div>

</body>
</html>
