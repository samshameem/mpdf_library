<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example Controller demonstrating mPDF library usage
 *
 * Place this in application/controllers/ for testing
 */
class Pdf_example extends CI_Controller {

	public function __construct() {
		parent::__construct();

		// Load the mPDF library
		$this->load->library('mpdf_generator');
	}

	/**
	 * Basic PDF generation example
	 */
	public function basic_pdf() {
		// Create PDF with custom config
		$config = [
			'title' => 'My Basic PDF',
			'author' => 'John Doe',
			'header_title' => 'Sample Document',
			'footer_text' => 'Confidential Document'
		];

		$this->mpdf_generator->set_config($config);

		// Add some content
		$html = '<h1>Welcome to mPDF</h1>';
		$html .= '<p>This is a basic PDF generated using the mPDF library for CodeIgniter 3.</p>';
		$html .= '<p>Current date: ' . date('Y-m-d H:i:s') . '</p>';

		$this->mpdf_generator->add_html($html);

		// Generate PDF
		$this->mpdf_generator->generate('basic_document.pdf', 'I'); // I = inline, D = download
	}

	/**
	 * PDF with table example
	 */
	public function pdf_with_table() {
		$config = [
			'title' => 'Report with Table',
			'header_title' => 'Sales Report',
			'orientation' => 'L' // Landscape for wider tables
		];

		$this->mpdf_generator->set_config($config);

		// Add title
		$this->mpdf_generator->add_html('<h1 style="text-align: center;">Monthly Sales Report</h1>');

		// Sample data
		$headers = ['Product', 'Quantity', 'Price', 'Total'];
		$data = [
			['Laptop', '10', '$1000', '$10000'],
			['Mouse', '50', '$25', '$1250'],
			['Keyboard', '30', '$50', '$1500'],
			['Monitor', '15', '$300', '$4500']
		];

		// Add table
		$this->mpdf_generator->add_table($data, $headers, [
			'header_style' => 'background-color: #4CAF50; color: white; font-weight: bold; text-align: center; padding: 10px; border: 1px solid #ddd;',
			'cell_style' => 'padding: 8px; border: 1px solid #ddd; text-align: center;'
		]);

		$this->mpdf_generator->generate('sales_report.pdf', 'I');
	}

	/**
	 * PDF from view file example
	 */
	public function pdf_from_view() {
		// Sample data to pass to view
		$data = [
			'title' => 'Invoice #12345',
			'customer' => [
				'name' => 'John Smith',
				'address' => '123 Main St, City, State 12345',
				'email' => 'john@example.com'
			],
			'items' => [
				['description' => 'Web Development', 'hours' => 40, 'rate' => 50, 'amount' => 2000],
				['description' => 'Database Design', 'hours' => 20, 'rate' => 60, 'amount' => 1200],
				['description' => 'Testing', 'hours' => 10, 'rate' => 45, 'amount' => 450]
			],
			'total' => 3650
		];

		$config = [
			'title' => 'Invoice PDF',
			'header_title' => 'INVOICE',
			'footer_text' => 'Thank you for your business!'
		];

		$this->mpdf_generator->set_config($config);

		// Load content from view file
		// Make sure you create application/views/pdf/invoice_template.php
		$this->mpdf_generator->add_view('pdf/invoice_template', $data);

		$this->mpdf_generator->generate('invoice_12345.pdf', 'D'); // D = download
	}

	/**
	 * Advanced PDF with custom styling
	 */
	public function advanced_pdf() {
		$config = [
			'title' => 'Advanced PDF Example',
			'header_title' => 'Advanced Features Demo',
			'show_watermark_text' => true,
			'watermark' => 'CONFIDENTIAL'
		];

		$this->mpdf_generator->set_config($config);

		// Add custom CSS
		$css = '
			.header { color: #2c3e50; font-size: 24px; text-align: center; margin-bottom: 20px; }
			.content { font-family: Arial, sans-serif; line-height: 1.6; }
			.highlight { background-color: #f39c12; padding: 10px; margin: 10px 0; }
			.box { border: 2px solid #3498db; padding: 15px; margin: 10px 0; }
		';
		$this->mpdf_generator->add_css($css);

		// Add content with styling
		$html = '
			<div class="header">Advanced PDF Features</div>
			<div class="content">
				<h2>Features Demonstrated:</h2>
				<ul>
					<li>Custom CSS styling</li>
					<li>Watermark text</li>
					<li>Multiple page layouts</li>
					<li>Bookmarks</li>
				</ul>

				<div class="highlight">
					<strong>Note:</strong> This is a highlighted section with custom styling.
				</div>

				<div class="box">
					<h3>Important Information</h3>
					<p>This box demonstrates border styling and padding.</p>
				</div>
			</div>
		';

		$this->mpdf_generator->add_html($html);

		// Add a bookmark
		$this->mpdf_generator->add_bookmark('Main Section', 0);

		// Add new page
		$this->mpdf_generator->add_page();

		$this->mpdf_generator->add_html('<h1>Second Page</h1><p>This content is on the second page.</p>');

		$this->mpdf_generator->generate('advanced_example.pdf', 'I');
	}

	/**
	 * PDF with image example
	 */
	public function pdf_with_image() {
		$config = [
			'title' => 'PDF with Images',
			'header_title' => 'Document with Images'
		];

		$this->mpdf_generator->set_config($config);

		$html = '<h1>Document with Images</h1>';
		$html .= '<p>Below is an embedded image:</p>';

		// Add HTML content
		$this->mpdf_generator->add_html($html);

		// Add image (make sure the image exists)
		// $this->mpdf_generator->add_image('./assets/images/logo.png', 0, 0, 50, 30);

		$html2 = '<p>Image above was inserted programmatically.</p>';
		$html2 .= '<p>You can also embed images directly in HTML:</p>';
		$html2 .= '<img src="./assets/images/sample.jpg" width="200" height="150" alt="Sample Image" />';

		$this->mpdf_generator->add_html($html2);

		$this->mpdf_generator->generate('document_with_images.pdf', 'I');
	}

	/**
	 * Save PDF to file instead of output
	 */
	public function save_pdf() {
		$this->mpdf_generator->add_html('<h1>Saved PDF</h1><p>This PDF was saved to the server.</p>');

		$file_path = FCPATH . 'uploads/saved_document.pdf'; // Make sure uploads directory exists
		$success = $this->mpdf_generator->save($file_path);

		if ($success) {
			echo "PDF saved successfully to: " . $file_path;
		} else {
			echo "Failed to save PDF";
		}
	}

	/**
	 * Get PDF as string (for email attachment, etc.)
	 */
	public function pdf_string() {
		$this->mpdf_generator->add_html('<h1>PDF String</h1><p>This PDF was generated as a string.</p>');

		$pdf_string = $this->mpdf_generator->get_pdf_string();

		// You can now use $pdf_string for email attachments, etc.
		echo "PDF generated as string. Length: " . strlen($pdf_string) . " bytes";

		// Example: Save string to file
		file_put_contents(FCPATH . 'uploads/string_pdf.pdf', $pdf_string);
	}

	/**
	 * Protected PDF example
	 */
	public function protected_pdf() {
		$config = [
			'title' => 'Protected PDF',
			'protection' => true,
			'user_password' => 'user123',
			'owner_password' => 'owner456',
			'permissions' => ['print', 'copy']
		];

		$this->mpdf_generator->set_config($config);

		$html = '<h1>Protected Document</h1>';
		$html .= '<p>This PDF is password protected.</p>';
		$html .= '<p>User password: user123</p>';
		$html .= '<p>Owner password: owner456</p>';

		$this->mpdf_generator->add_html($html);
		$this->mpdf_generator->generate('protected_document.pdf', 'I');
	}

	/**
	 * Multi-page PDF with different orientations
	 */
	public function multi_page_pdf() {
		$this->mpdf_generator->add_html('<h1>Page 1 - Portrait</h1><p>This is the first page in portrait orientation.</p>');

		// Add new page with landscape orientation
		$this->mpdf_generator->add_page('L');
		$this->mpdf_generator->add_html('<h1>Page 2 - Landscape</h1><p>This page is in landscape orientation, perfect for wide tables or charts.</p>');

		// Add another portrait page
		$this->mpdf_generator->add_page('P');
		$this->mpdf_generator->add_html('<h1>Page 3 - Back to Portrait</h1><p>This page is back to portrait orientation.</p>');

		$this->mpdf_generator->generate('multi_page_document.pdf', 'I');
	}

	/**
	 * PDF with custom header and footer
	 */
	public function custom_header_footer() {
		// Set custom header
		$header_html = '
			<table width="100%" style="border-bottom: 1px solid #000; padding-bottom: 10px;">
				<tr>
					<td style="text-align: left; font-size: 14px; font-weight: bold;">
						My Company Logo
					</td>
					<td style="text-align: right; font-size: 12px;">
						Date: ' . date('Y-m-d') . '
					</td>
				</tr>
			</table>
		';

		// Set custom footer
		$footer_html = '
			<table width="100%" style="border-top: 1px solid #000; padding-top: 5px; font-size: 10px;">
				<tr>
					<td style="text-align: left;">
						Confidential Document
					</td>
					<td style="text-align: center;">
						www.mycompany.com
					</td>
					<td style="text-align: right;">
						Page {PAGENO} of {nbpg}
					</td>
				</tr>
			</table>
		';

		$this->mpdf_generator->set_html_header($header_html);
		$this->mpdf_generator->set_html_footer($footer_html);

		$content = '<h1>Document with Custom Header/Footer</h1>';
		$content .= '<p>This document demonstrates custom HTML headers and footers.</p>';
		$content .= str_repeat('<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. </p>', 50);

		$this->mpdf_generator->add_html($content);
		$this->mpdf_generator->generate('custom_header_footer.pdf', 'I');
	}
}