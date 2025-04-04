<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        /* Add your custom styles here */
        .invoice-header { margin-bottom: 2rem; }
        .client-info { margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem; border: 1px solid #ddd; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
<div class="invoice-header">
    <h1>Invoice #{{ $invoice->invoice_number }}</h1>
    <p>Date: {{ $invoice->invoice_date->format('d M Y') }}</p>
    <p>Due Date: {{ $invoice->due_date->format('d M Y') }}</p>
</div>

    <div class="client-info">
        <h3>Bill To:</h3>
        <p>{{ $invoice->client_name }}</p>
        <p>{!! nl2br(e($invoice->client_address)) !!}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Unit Cost</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                <td>${{ number_format($item['unit_cost'], 2) }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td>${{ number_format($item['unit_cost'] * $item['quantity'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="total">Total Amount:</td>
                <td class="total">${{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>