<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input) {
        include 'send-email.php';
        
        $payment_data = [
            'booking_id' => $input['booking_id'],
            'transaction_id' => $input['transaction_id'],
            'amount' => $input['amount'],
            'payment_method' => 'Online Payment',
            'customer_email' => 'customer@example.com' // This should be retrieved from booking data
        ];
        
        if (sendPaymentConfirmation($payment_data)) {
            echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
