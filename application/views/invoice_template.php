<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<style>
		body {
			font-family: Arial, sans-serif;
			font-size: 12px;
			line-height: 1.4;
			color: #333;
		}
		.invoice-header {
			text-align: center;
			margin-bottom: 30px;
			border-bottom: 2px solid #2c3e50;
			padding-bottom: 20px;
		}
		.invoice-title {
			font-size: 28px;
			font-weight: bold;
			color: #2c3e50;
			margin-bottom: 10px;
		}
		.invoice-number {
			font-size: 16px;
			color: #7f8c8d;
		}
		.customer-info {
			margin-bottom: 30px;
		}
		.customer-info h3 {
			color: #2c3e50;
			border-bottom: 1px solid #bdc3c7;
			padding-bottom: 5px;
		}
		.items-table {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 20px;
		}
		.items-table th {
			background-color: #34495e;
			color: white;
			padding: 12px;
			text-align: left;
			font-weight: bold;
		}
		.items-table td {
			padding: 10px 12px;
			border-bottom: 1px solid #ecf0f1;
		}
		.items-table tr:nth-child(even) {
			background-color: #f8f9fa;
		}
		.text-right {
			text-align: right;
		}
		.text-center {
			text-align: center;
		}
		.total-section {
			float: right;
			width: 300px;
			margin-top: 20px;
		}
		.total-row {
			display: table;
			width: 100%;
			margin-bottom: 5px;
		}
		.total-label {
			display: table-cell;
			font-weight: bold;
			padding: 5px 10px;
			text-align: right;
		}
		.total-value {
			display: table-cell;
			padding: 5px 10px;
			text-align: right;
			border-bottom: 1px solid #bdc3c7;
		}
		.grand-total {
			background-color: #2c3e50;
			color: white;
			font-size: 18px;
			font-weight: bold;
		}
		.footer-note {
			margin-top: 50px;
			padding-top: 20px;
			border-top: 1px solid #bdc3c7;
			font-style: italic;
			color: #7f8c8d;
			text-align: center;
		}
	</style>
</head>
<body>
	<!-- Invoice Header -->
	<div class="invoice-header">
		<div class="invoice-title"><?php echo $title; ?></div>
		<div class="invoice-number">Date: <?php echo date('F d, Y'); ?></div>
	</div>

	<!-- Customer Information -->
	<div class="customer-info">
		<h3>Bill To:</h3>
		<strong><?php echo $customer['name']; ?></strong><br>
		<?php echo $customer['address']; ?><br>
		Email: <?php echo $customer['email']; ?>
	</div>

	<!-- Items Table -->
	<table class="items-table">
		<thead>
			<tr>
				<th>Description</th>
				<th class="text-center">Hours</th>
				<th class="text-right">Rate</th>
				<th class="text-right">Amount</th>
			</tr>
		</thead>
		<tbody>
			<?php $subtotal = 0; ?>
			<?php foreach ($items as $item): ?>
				<tr>
					<td><?php echo $item['description']; ?></td>
					<td class="text-center"><?php echo $item['hours']; ?></td>
					<td class="text-right">$<?php echo number_format($item['rate'], 2); ?></td>
					<td class="text-right">$<?php echo number_format($item['amount'], 2); ?></td>
				</tr>
				<?php $subtotal += $item['amount']; ?>
			<?php endforeach; ?>
		</tbody>
	</table>

	<!-- Total Section -->
	<div class="total-section">
		<div class="total-row">
			<div class="total-label">Subtotal:</div>
			<div class="total-value">$<?php echo number_format($subtotal, 2); ?></div>
		</div>
		<div class="total-row">
			<div class="total-label">Tax (8%):</div>
			<div class="total-value">$<?php echo number_format($subtotal * 0.08, 2); ?></div>
		</div>
		<div class="total-row grand-total">
			<div class="total-label">Total:</div>
			<div class="total-value">$<?php echo number_format($total, 2); ?></div>
		</div>
	</div>

	<div style="clear: both;"></div>

	<!-- Footer Note -->
	<div class="footer-note">
		Thank you for your business! Payment is due within 30 days.
	</div>
</body>
</html>