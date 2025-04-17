<!DOCTYPE html>
<html>
<head>
    <title>Your Flight Ticket</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f8f8; padding: 10px; text-align: center; }
        .content { margin-top: 20px; }
        .footer { margin-top: 20px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Your Flight Ticket</h2>
        </div>
        <div class="content">
            <p>Dear {{ $ticket['user_name'] }},</p>
            <p>Thank you for booking with us. Please find your flight ticket details below and the attached PDF for your records.</p>
            <p><strong>PNR:</strong> {{ $ticket['pnr'] }}</p>
            <p><strong>Flight:</strong> {{ $ticket['flight_name'] }} ({{ $ticket['flight_number'] }})</p>
            <p><strong>Departure:</strong> {{ $ticket['departure_from'] }} at {{ $ticket['flight_date'] }}</p>
            <p><strong>Arrival:</strong> {{ $ticket['arrival_to'] }} at {{ $ticket['return_date'] ?? 'N/A' }}</p>
            <p><strong>Total Fare:</strong> INR {{ number_format($ticket['total_fare'], 2) }}</p>
            <p>Please check-in online or arrive at the airport early to complete the necessary procedures.</p>
        </div>
        <div class="footer">
            <p>This is an automated email. Please do not reply directly to this email.</p>
            <p>Contact us at support@travelagency.com or call +91-9876543210 for assistance.</p>
        </div>
    </div>
</body>
</html>