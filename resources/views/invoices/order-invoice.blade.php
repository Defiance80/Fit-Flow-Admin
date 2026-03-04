<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order_number }}</title>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <style>
        * {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            padding: 30px;
            background: #ffffff;
            color: #000000;
            font-size: 14px;
        }
    </style>
</head>

<body>
<div class="invoice-container">

    <!-- Header -->
    <div class="header invoice-header">
        @if(isset($app_logo) && $app_logo)
            <img src="{{ $app_logo }}" class="logo" alt="Logo"
                 onerror="this.style.display='none';">
        @endif

        <div class="company-name invoice-company-name">{{ $app_name ?? 'Learning Management System' }}</div>
        <div class="invoice-title">INVOICE</div>
    </div>

    <!-- Invoice + Customer Info -->
    <div class="row invoice-row">
        <div class="box invoice-box">
            <h3>Invoice Details</h3>
            <p><strong>Order Number:</strong> {{ $order_number }}</p>
            <p><strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($invoice_date)->format('d-m-Y') }}</p>
        </div>

        <div class="box invoice-box">
            <h3>Bill To</h3>
            <p><strong>Name:</strong> {{ $customer['name'] }}</p>
            <p><strong>Email:</strong> {{ $customer['email'] }}</p>
        </div>
    </div>

    <!-- Courses Table -->
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Course ID</th>
                <th>Course Title</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courses as $course)
            <tr>
                <td>#{{ $course['course_id'] }}</td>
                <td>{{ $course['title'] }}</td>
                <td>{{ $currency_symbol ?? '$' }}{{ number_format($course['price'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Promo Codes -->
    @if($applied_promo_codes->count() > 0)
    <div class="promo-section invoice-promo-section">
        <h4>Applied Promo Codes</h4>

        @foreach($applied_promo_codes as $promo)
        <div class="promo-item invoice-promo-item">
            <strong>{{ $promo['promo_code'] }}</strong> — 
            @if($promo['discount_type'] === 'percentage')
                {{ $promo['discount_value'] }}% off
            @else
                {{ $currency_symbol ?? '$' }}{{ $promo['discount_value'] }} off
            @endif
            (Saved: {{ $currency_symbol ?? '$' }}{{ number_format($promo['discounted_amount'], 2) }})
        </div>
        @endforeach
    </div>
    @endif

    <!-- Summary -->
    <div class="summary invoice-summary">
        <div class="summary-row invoice-summary-row">
            <span>Subtotal</span>
            <span>{{ $currency_symbol ?? '$' }}{{ number_format($pricing['subtotal'], 2) }}</span>
        </div>

        <div class="summary-row invoice-summary-row">
            <span>Tax Amount</span>
            <span>{{ $currency_symbol ?? '$' }}{{ number_format($pricing['tax_amount'], 2) }}</span>
        </div>

        @if($pricing['total_discount'] > 0)
        <div class="summary-row invoice-summary-row">
            <span>Discount</span>
            <span>-{{ $currency_symbol ?? '$' }}{{ number_format($pricing['total_discount'], 2) }}</span>
        </div>
        @endif

        <div class="summary-row total invoice-summary-row">
            <span>Total</span>
            <span>{{ $currency_symbol ?? '$' }}{{ number_format($pricing['final_total'], 2) }}</span>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer invoice-footer">
        Thank you for your purchase.  
        <br>
        Invoice generated on {{ date('F j, Y \a\t g:i A') }}
    </div>

</div>
</body>
</html>
