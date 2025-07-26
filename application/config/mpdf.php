<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * mPDF Configuration for CodeIgniter 3
 *
 * Place this file in application/config/mpdf.php
 * Enhanced configuration to support flexible header layouts
 */

$config['mpdf'] = [
	// Page settings
	'mode' => 'utf-8',											// Document encoding
	'format' => 'A4',											// Page format (A4, A3, Letter, etc.)
	'orientation' => 'P',										// P for Portrait, L for Landscape
	'margin_left' => 15,										// Left margin in mm
	'margin_right' => 15,										// Right margin in mm
	'margin_top' => 16,											// Top margin in mm
	'margin_bottom' => 16,										// Bottom margin in mm
	'margin_header' => 9,										// Header margin in mm
	'margin_footer' => 9,										// Footer margin in mm

	// Document information
	'title' => 'PDF Document',									// Document title
	'author' => 'IonCBE',										// Document author
	'subject' => 'Generated PDF',								// Document subject
	'creator' => 'IonCBE',										// Document creator
	'keywords' => 'pdf, codeigniter',							// Document keywords

	// Font settings
	'default_font' => 'dejavusans',								// Default font family
	'default_font_size' => 12,									// Default font size

	// Header settings
	'header_enable' => false,									// Enable header

	// Header Layout Configuration
	// Available layouts:
	// - 'center_logo_center_text' (default)
	// - 'left_logo_right_text'
	// - 'right_logo_left_text'
	// - 'top_logo_bottom_text'
	// - 'top_text_bottom_logo'
	// - 'left_logo_left_text'
	// - 'right_logo_right_text'
	// - 'custom'
	'header_layout' => 'center_logo_center_text',				// Header layout type

	// Header Content
	'header_title' => 'IonCBE',									// Header title
	'header_string' => '',										// Additional header text/subtitle
	'header_logo' => FCPATH . 'assets/images/your_logo.png',	// Path to header logo
	'header_logo_width' => 30,									// Logo width in mm
	'header_logo_height' => 0,									// Logo height in mm (0 = auto)

	// Header Positioning Options
	'header_logo_position' => 'center',							// Logo position: left, center, right
	'header_text_position' => 'center',							// Text position: left, center, right
	'header_spacing' => '10px',									// Space between logo and text

	// Header Float Options (for custom layout)
	'header_logo_float' => 'none',								// Logo float: left, right, none
	'header_text_float' => 'none',								// Text float: left, right, none

	// Header Custom Styling
	'header_container_style' => '',								// Additional container styles
	'header_logo_container_style' => '',						// Logo container styles
	'header_text_container_style' => '',						// Text container styles

	// Footer settings
	'footer_enable' => false,									// Enable footer
	'footer_text' => '© 2025 IonIdea',							// Footer text
	'footer_show_page' => true,									// Show page numbers in footer

	// Protection settings
	'protection' => false,										// Enable PDF protection
	'user_password' => '',										// User password
	'owner_password' => '',										// Owner password
	'permissions' => [],										// Permissions array

	// Language and script settings
	'auto_language_detection' => true,							// Auto language detection
	'auto_script_to_lang' => true,								// Auto script to language
	'baseScript' => 1,											// Base script
	'autoVietnamese' => true,									// Auto Vietnamese
	'autoArabic' => true,										// Auto Arabic

	// Watermark settings
	'watermark' => '',											// Watermark text or image path
	'watermark_alpha' => 0.2,									// Watermark transparency
	'show_watermark_image' => false,							// Show image watermark
	'show_watermark_text' => false,								// Show text watermark

	// Temporary directory
	'tempDir' => FCPATH.'uploads/pdf_temp',						// Temporary files directory
];

/*
 * Header Layout Examples and Usage:
 *
 * 1. Logo on left, text on right:
 *    'header_layout' => 'left_logo_right_text'
 *
 * 2. Text on left, logo on right:
 *    'header_layout' => 'right_logo_left_text'
 *
 * 3. Logo on top, text below:
 *    'header_layout' => 'top_logo_bottom_text'
 *
 * 4. Text on top, logo below:
 *    'header_layout' => 'top_text_bottom_logo'
 *
 * 5. Both logo and text on left side:
 *    'header_layout' => 'left_logo_left_text'
 *
 * 6. Both logo and text on right side:
 *    'header_layout' => 'right_logo_right_text'
 *
 * 7. Custom layout using float properties:
 *    'header_layout' => 'custom',
 *    'header_logo_float' => 'left',
 *    'header_text_float' => 'right'
 *
 * 8. Center layout (default):
 *    'header_layout' => 'center_logo_center_text'
 *
 * Additional Styling Examples:
 *
 * - Custom container styling:
 *   'header_container_style' => 'border-bottom: 2px solid #000; padding-bottom: 10px;'
 *
 * - Logo container styling:
 *   'header_logo_container_style' => 'border: 1px solid #ccc; padding: 5px;'
 *
 * - Text container styling:
 *   'header_text_container_style' => 'background-color: #f5f5f5; padding: 10px;'
 *
 * - Custom spacing:
 *   'header_spacing' => '20px'  // More space between elements
 */