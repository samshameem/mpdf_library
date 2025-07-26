<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * mPDF Library for CodeIgniter 3
 *
 * A flexible and configurable PDF generator using mPDF
 *
 * @author Generated for CodeIgniter 3
 * @version 1.0
 * @requires mPDF 8.x (install via composer: composer require mpdf/mpdf)
 */

// Make sure to include Composer autoload or mPDF manually
// require_once FCPATH . 'vendor/autoload.php'; // Adjust path as needed

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

// Uncomment if you have PhpSpreadsheet installed
// use PhpOffice\PhpSpreadsheet\IOFactory;
// use PhpOffice\PhpWord\IOFactory as WordIOFactory;

class Mpdf_generator {
	private $CI;
	private $mpdf;
	private $config;
	private $default_config = [];
	private $supported_image_types = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
	private $temp_files = []; // Track temporary files for cleanup

	// Header layout constants
	const HEADER_LAYOUT_LEFT_LOGO_RIGHT_TEXT = 'left_logo_right_text';
	const HEADER_LAYOUT_RIGHT_LOGO_LEFT_TEXT = 'right_logo_left_text';
	const HEADER_LAYOUT_TOP_LOGO_BOTTOM_TEXT = 'top_logo_bottom_text';
	const HEADER_LAYOUT_TOP_TEXT_BOTTOM_LOGO = 'top_text_bottom_logo';
	const HEADER_LAYOUT_CENTER_LOGO_CENTER_TEXT = 'center_logo_center_text';
	const HEADER_LAYOUT_LEFT_LOGO_LEFT_TEXT = 'left_logo_left_text';
	const HEADER_LAYOUT_LEFT_LOGO_CENTER_TEXT = 'left_logo_center_text';
	const HEADER_LAYOUT_RIGHT_LOGO_RIGHT_TEXT = 'right_logo_right_text';
	const HEADER_LAYOUT_CUSTOM = 'custom';

	public function __construct($config = []) {
		$this->CI =& get_instance();

		// Enhanced default config with header layout options
		// $this->default_config = [
		// 	'header_layout' => self::HEADER_LAYOUT_CENTER_LOGO_CENTER_TEXT,
		// 	'header_logo_position' => 'center', // left, center, right
		// 	'header_text_position' => 'center', // left, center, right
		// 	'header_logo_float' => 'none', // left, right, none
		// 	'header_text_float' => 'none', // left, right, none
		// 	'header_container_style' => '', // Additional container styles
		// 	'header_logo_container_style' => '', // Logo container styles
		// 	'header_text_container_style' => '', // Text container styles
		// 	'header_spacing' => '10px', // Space between logo and text
		// ];

		// Load config file if exists
		if (file_exists(APPPATH . 'config/mpdf.php')) {
			$this->CI->load->config('mpdf');
			$file_config = $this->CI->config->item('mpdf');
			if ($file_config) {
				$this->default_config = array_merge($this->default_config, $file_config);
			}
		}

		// Merge with user config
		$this->config = array_merge($this->default_config, $config);

		// Initialize temp directory first
		$this->initialize_temp_directory();
		$this->initialize();
	}

		/**
	 * Initialize temp directory using IO_util library
	 */
	private function initialize_temp_directory() {
		try {
			// Load IO_util library if available
			if (!isset($this->CI->io_util)) {
				$this->CI->load->library('IO_util');
			}

			// Create temp directory if specified and not exists
			if (!empty($this->config['tempDir'])) {
				if (!is_dir($this->config['tempDir'])) {
					$this->CI->io_util->create_multi_dir($this->config['tempDir']);

					// Log success
					if (is_dir($this->config['tempDir'])) {
						log_message('info', 'mPDF: Temp directory created successfully: ' . $this->config['tempDir']);
					} else {
						log_message('error', 'mPDF: Failed to create temp directory: ' . $this->config['tempDir']);
					}
				}
			}
		} catch (Exception $e) {
			// Fallback to native PHP directory creation if IO_util fails
			log_message('error', 'mPDF: IO_util library failed, attempting native directory creation: ' . $e->getMessage());
			// $this->create_directory_fallback($this->config['tempDir']);
		}
	}

	/**
	 * Fallback method to create directories using native PHP
	 */
	private function create_directory_fallback($directory) {
		if (!empty($directory) && !is_dir($directory)) {
			try {
				if (mkdir($directory, 0755, true)) {
					log_message('info', 'mPDF: Temp directory created using fallback method: ' . $directory);
				} else {
					log_message('error', 'mPDF: Failed to create temp directory using fallback method: ' . $directory);
				}
			} catch (Exception $e) {
				log_message('error', 'mPDF: Directory creation failed completely: ' . $e->getMessage());
			}
		}
	}
	/**
	 * Initialize mPDF with configuration
	 */
	private function initialize() {
		$mpdf_config = [
			'mode' => $this->config['mode'] ?? 'utf-8',
			'format' => $this->config['format'] ?? 'A4',
			'default_font_size' => $this->config['default_font_size'] ?? 12,
			'default_font' => $this->config['default_font'] ?? 'Arial',
			'margin_left' => $this->config['margin_left'] ?? 10,
			'margin_right' => $this->config['margin_right'] ?? 10,
			'margin_top' => $this->config['margin_top'] ?? 16,
			'margin_bottom' => $this->config['margin_bottom'] ?? 16,
			'margin_header' => $this->config['margin_header'] ?? 9,
			'margin_footer' => $this->config['margin_footer'] ?? 9,
			'orientation' => $this->config['orientation'] ?? 'P',
			'autoLangToFont' => $this->config['auto_language_detection'] ?? false,
			'autoScriptToLang' => $this->config['auto_script_to_lang'] ?? true,
			'baseScript' => $this->config['baseScript'] ?? 1,
			'autoVietnamese' => $this->config['autoVietnamese'] ?? true,
			'autoArabic' => $this->config['autoArabic'] ?? true,
		];

		// Add temp directory to mPDF config if it exists and is writable
		if (!empty($this->config['tempDir']) && is_dir($this->config['tempDir']) && is_writable($this->config['tempDir'])) {
			$mpdf_config['tempDir'] = $this->config['tempDir'];
			log_message('info', 'mPDF: Using temp directory: ' . $this->config['tempDir']);
		} else {
			log_message('warning', 'mPDF: Temp directory not available or not writable, using system default');
		}

		$this->mpdf = new Mpdf($mpdf_config);

		// Set document properties
		$this->mpdf->SetTitle($this->config['title'] ?? '');
		$this->mpdf->SetAuthor($this->config['author'] ?? '');
		$this->mpdf->SetSubject($this->config['subject'] ?? '');
		$this->mpdf->SetCreator($this->config['creator'] ?? '');
		$this->mpdf->SetKeywords($this->config['keywords'] ?? '');

		// Set protection if enabled
		if (!empty($this->config['protection'])) {
			$this->mpdf->SetProtection(
				$this->config['permissions'] ?? [],
				$this->config['user_password'] ?? '',
				$this->config['owner_password'] ?? ''
			);
		}

		// Set watermark
		if (!empty($this->config['watermark'])) {
			if (!empty($this->config['show_watermark_text'])) {
				$this->mpdf->SetWatermarkText($this->config['watermark'], $this->config['watermark_alpha'] ?? 0.2);
				$this->mpdf->showWatermarkText = true;
			} else if (!empty($this->config['show_watermark_image'])) {
				$this->mpdf->SetWatermarkImage(
					$this->config['watermark'],
					$this->config['watermark_alpha'] ?? 0.2
				);
				$this->mpdf->showWatermarkImage = true;
			}
		}

		$this->setup_header_footer();
	}

	/**
	 * Enhanced setup header and footer with flexible layouts
	 */
	private function setup_header_footer() {
		$header_html = '';
		$footer_html = '';

		// Build header HTML with flexible layout
		if (!empty($this->config['header_enable'])) {
			$header_html = $this->build_header_html();
			}

		// Build footer HTML (keeping existing functionality)
		if (!empty($this->config['footer_enable'])) {
			$footer_html = '<hr style="margin: 5px 0;"><div style="font-size: 10px;">';

			if (!empty($this->config['footer_text']) && !empty($this->config['footer_show_page'])) {
				$footer_html .= '<table width="100%"><tr>';
				$footer_html .= '<td style="text-align: left;">' . $this->config['footer_text'] . '</td>';
				$footer_html .= '<td style="text-align: right;">Page {PAGENO} of {nbpg}</td>';
				$footer_html .= '</tr></table>';
			} else if (!empty($this->config['footer_show_page'])) {
				$footer_html .= '<div style="text-align: center;">Page {PAGENO} of {nbpg}</div>';
			} else if (!empty($this->config['footer_text'])) {
				$footer_html .= '<div style="text-align: center;">' . $this->config['footer_text'] . '</div>';
			}

			$footer_html .= '</div>';
		}

		// Set header and footer
		if (!empty($header_html)) {
			$this->mpdf->SetHTMLHeader($header_html);
		}
		if (!empty($footer_html)) {
			$this->mpdf->SetHTMLFooter($footer_html);
		}
	}

	/**
	 * Build header HTML based on layout configuration
	 */
	private function build_header_html() {
		$layout = $this->config['header_layout'] ?? self::HEADER_LAYOUT_CENTER_LOGO_CENTER_TEXT;
		$container_style = $this->config['header_container_style'] ?? '';
		$spacing = $this->config['header_spacing'] ?? '10px';

		// Base container style
		$base_container_style = 'width: 100%; margin-bottom: 5px;';
		if ($container_style) {
			$base_container_style .= ' ' . $container_style;
		}

		$header_html = '<div style="' . $base_container_style . '">';

		switch ($layout) {
			case self::HEADER_LAYOUT_LEFT_LOGO_RIGHT_TEXT:
				$header_html .= $this->build_horizontal_layout('left', 'right');
				break;

			case self::HEADER_LAYOUT_RIGHT_LOGO_LEFT_TEXT:
				$header_html .= $this->build_horizontal_layout('right', 'left');
				break;

			case self::HEADER_LAYOUT_LEFT_LOGO_LEFT_TEXT:
				$header_html .= $this->build_horizontal_layout('left', 'left');
				break;

			case self::HEADER_LAYOUT_LEFT_LOGO_CENTER_TEXT:
				$header_html .= $this->build_horizontal_layout('left', 'center');
				break;

			case self::HEADER_LAYOUT_TOP_LOGO_BOTTOM_TEXT:
				$header_html .= $this->build_vertical_layout('logo', 'text');
				break;

			case self::HEADER_LAYOUT_TOP_TEXT_BOTTOM_LOGO:
				$header_html .= $this->build_vertical_layout('text', 'logo');
				break;

			// case self::HEADER_LAYOUT_LEFT_LOGO_LEFT_TEXT:
			// 	$header_html .= $this->build_same_side_layout('left');
			// 	break;

			case self::HEADER_LAYOUT_RIGHT_LOGO_RIGHT_TEXT:
				$header_html .= $this->build_same_side_layout('right');
				break;

			case self::HEADER_LAYOUT_CUSTOM:
				$header_html .= $this->build_custom_layout();
				break;

			case self::HEADER_LAYOUT_CENTER_LOGO_CENTER_TEXT:
			default:
				$header_html .= $this->build_center_layout();
				break;
		}

		$header_html .= '</div><hr style="margin: 5px 0;">';
		return $header_html;
	}

	/**
	 * Build horizontal layout (logo and text side by side)
	 */
	private function build_horizontal_layout($logo_position, $text_position) {
		$html = '<table width="100%" style="border-collapse: collapse;"><tr>';

		$logo_html = $this->get_logo_html();
		$text_html = $this->get_text_html();

		if ($logo_position === 'left') {
			$html .= '<td style="text-align: left; vertical-align: middle; width: 20%;">' . $logo_html . '</td>';
			$html .= '<td style="text-align: ' . $text_position . '; vertical-align: middle; width: 80%;">' . $text_html . '</td>';
		} else {
			$html .= '<td style="text-align: ' . $text_position . '; vertical-align: middle; width: 50%;">' . $text_html . '</td>';
			$html .= '<td style="text-align: right; vertical-align: middle; width: 50%;">' . $logo_html . '</td>';
		}

		$html .= '</tr></table>';
		return $html;
	}

	/**
	 * Build vertical layout (logo and text stacked)
	 */
	private function build_vertical_layout($first, $second) {
		$html = '';
		$spacing = $this->config['header_spacing'] ?? '10px';

		$logo_html = $this->get_logo_html();
		$text_html = $this->get_text_html();

		$first_content = ($first === 'logo') ? $logo_html : $text_html;
		$second_content = ($second === 'logo') ? $logo_html : $text_html;

		$logo_position = $this->config['header_logo_position'] ?? 'center';
		$text_position = $this->config['header_text_position'] ?? 'center';

		$first_align = ($first === 'logo') ? $logo_position : $text_position;
		$second_align = ($second === 'logo') ? $logo_position : $text_position;

		$html .= '<div style="text-align: ' . $first_align . ';">' . $first_content . '</div>';
		$html .= '<div style="margin-top: ' . $spacing . '; text-align: ' . $second_align . ';">' . $second_content . '</div>';

		return $html;
	}

	/**
	 * Build same side layout (both logo and text on same side)
	 */
	private function build_same_side_layout($position) {
		$html = '';
		$spacing = $this->config['header_spacing'] ?? '10px';

		$logo_html = $this->get_logo_html();
		$text_html = $this->get_text_html();

		$html .= '<div style="text-align: ' . $position . ';">';
		$html .= '<div style="display: inline-block; vertical-align: middle;">' . $logo_html . '</div>';
		$html .= '<div style="display: inline-block; vertical-align: middle; margin-left: ' . $spacing . ';">' . $text_html . '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Build center layout (original behavior)
	 */
	private function build_center_layout() {
		$html = '<div style="text-align: center;">';
		$html .= $this->get_logo_html();
		$html .= $this->get_text_html();
		$html .= '</div>';
		return $html;
	}

	/**
	 * Build custom layout using float properties
	 */
	private function build_custom_layout() {
		$html = '';
		$logo_float = $this->config['header_logo_float'] ?? 'none';
		$text_float = $this->config['header_text_float'] ?? 'none';

		$logo_html = $this->get_logo_html();
		$text_html = $this->get_text_html();

		// Create containers with float styles
		if ($logo_float !== 'none') {
			$html .= '<div style="float: ' . $logo_float . '; ' . ($this->config['header_logo_container_style'] ?? '') . '">' . $logo_html . '</div>';
		}

		if ($text_float !== 'none') {
			$html .= '<div style="float: ' . $text_float . '; ' . ($this->config['header_text_container_style'] ?? '') . '">' . $text_html . '</div>';
		}

		// Add elements without float if not floated
		if ($logo_float === 'none') {
			$html .= '<div style="' . ($this->config['header_logo_container_style'] ?? '') . '">' . $logo_html . '</div>';
		}

		if ($text_float === 'none') {
			$html .= '<div style="' . ($this->config['header_text_container_style'] ?? '') . '">' . $text_html . '</div>';
		}

		// Clear floats
		$html .= '<div style="clear: both;"></div>';

		return $html;
	}

	/**
	 * Get logo HTML
	 */
	private function get_logo_html() {
		$logo_html = '';

		if (!empty($this->config['header_logo']) && file_exists($this->config['header_logo'])) {
			$logo_style = '';
			if (!empty($this->config['header_logo_width']) && $this->config['header_logo_width'] > 0) {
				$logo_style .= 'width: ' . $this->config['header_logo_width'] . 'mm; ';
			}
			if (!empty($this->config['header_logo_height']) && $this->config['header_logo_height'] > 0) {
				$logo_style .= 'height: ' . $this->config['header_logo_height'] . 'mm; ';
			}
			$logo_html = '<img src="' . $this->config['header_logo'] . '" style="' . $logo_style . '">';
		}

		return $logo_html;
	}

	/**
	 * Get text HTML (title and string)
	 */
	private function get_text_html() {
		$text_html = '';

		// Add title
		if (!empty($this->config['header_title'])) {
			$text_html .= '<h1 style="margin: 5px 0;">' . $this->config['header_title'] . '</h1>';
		}

		// Add header string
		if (!empty($this->config['header_string'])) {
			$text_html .= '<p style="margin: 2px 0;">' . $this->config['header_string'] . '</p>';
		}

		return $text_html;
	}

	/**
	 * Set header layout configuration
	 */
	public function set_header_layout($layout, $options = []) {
		$this->config['header_layout'] = $layout;

		// Merge additional options
		foreach ($options as $key => $value) {
			$this->config[$key] = $value;
		}

		// Reinitialize to apply changes
		$this->setup_header_footer();
		return $this;
	}

	/**
	 * Quick method to set logo on left, text on right
	 */
	public function set_header_logo_left_text_right($logo_path = null, $title = null, $subtitle = null) {
		if ($logo_path) $this->config['header_logo'] = $logo_path;
		if ($title) $this->config['header_title'] = $title;
		if ($subtitle) $this->config['header_string'] = $subtitle;

		return $this->set_header_layout(self::HEADER_LAYOUT_LEFT_LOGO_RIGHT_TEXT);
	}

	/**
	 * Quick method to set text on left, logo on right
	 */
	public function set_header_text_left_logo_right($logo_path = null, $title = null, $subtitle = null) {
		if ($logo_path) $this->config['header_logo'] = $logo_path;
		if ($title) $this->config['header_title'] = $title;
		if ($subtitle) $this->config['header_string'] = $subtitle;

		return $this->set_header_layout(self::HEADER_LAYOUT_RIGHT_LOGO_LEFT_TEXT);
	}

	/**
	 * Quick method to set logo on top, text below
	 */
	public function set_header_logo_top_text_bottom($logo_path = null, $title = null, $subtitle = null) {
		if ($logo_path) $this->config['header_logo'] = $logo_path;
		if ($title) $this->config['header_title'] = $title;
		if ($subtitle) $this->config['header_string'] = $subtitle;

		return $this->set_header_layout(self::HEADER_LAYOUT_TOP_LOGO_BOTTOM_TEXT);
	}

	/**
	 * Quick method to set text on top, logo below
	 */
	public function set_header_text_top_logo_bottom($logo_path = null, $title = null, $subtitle = null) {
		if ($logo_path) $this->config['header_logo'] = $logo_path;
		if ($title) $this->config['header_title'] = $title;
		if ($subtitle) $this->config['header_string'] = $subtitle;

		return $this->set_header_layout(self::HEADER_LAYOUT_TOP_TEXT_BOTTOM_LOGO);
	}

	/**
	 * Manually create temp directory (public method for external use)
	 */
	public function ensure_temp_directory($directory = null) {
		$target_dir = $directory ?: $this->config['tempDir'];
		if (!empty($target_dir)) {
			$this->initialize_temp_directory();
			return is_dir($target_dir) && is_writable($target_dir);
		}
		return false;
	}

	/**
	 * Get temp directory path
	 */
	public function get_temp_directory() {
		return $this->config['tempDir'] ?? null;
	}

	/**
	 * Clean temp directory (optional cleanup method)
	 */
	public function clean_temp_directory($max_age_hours = 24) {
		$temp_dir = $this->config['tempDir'] ?? null;
		if (!empty($temp_dir) && is_dir($temp_dir)) {
			try {
				$files = glob($temp_dir . '/*');
				$now = time();
				$max_age_seconds = $max_age_hours * 3600;

				foreach ($files as $file) {
					if (is_file($file) && ($now - filemtime($file)) >= $max_age_seconds) {
						unlink($file);
					}
				}

				log_message('info', 'mPDF: Temp directory cleaned, removed files older than ' . $max_age_hours . ' hours');
				return true;
			} catch (Exception $e) {
				log_message('error', 'mPDF: Failed to clean temp directory: ' . $e->getMessage());
				return false;
			}
		}
		return false;
	}

	/**
	 * Update configuration
	 */
	public function set_config($config) {
		$this->config = array_merge($this->config, $config);

		// Reinitialize temp directory if changed
		if (isset($config['tempDir'])) {
			$this->initialize_temp_directory();
		}
		$this->initialize();
		return $this;
	}

	/**
	 * Get current configuration
	 */
	public function get_config($key = null) {
		if ($key) {
			return isset($this->config[$key]) ? $this->config[$key] : null;
		}
		return $this->config;
	}

	/**
	 * Get mPDF instance
	 */
	public function get_mpdf() {
		return $this->mpdf;
	}

	/**
	 * Add content from HTML
	 */
	public function add_html($html, $mode = 0) {
		$this->mpdf->WriteHTML($html, $mode);
		return $this;
	}

	/**
	 * Add content from view
	 */
	public function add_view($view, $data = [], $mode = 0) {
		$html = $this->CI->load->view($view, $data, true);
		$this->mpdf->WriteHTML($html, $mode);
		return $this;
	}

	/**
	 * Add text content
	 */
	public function add_text($text) {
		$this->mpdf->WriteHTML('<p>' . htmlspecialchars($text) . '</p>');
		return $this;
	}

	/**
	 * Add new page
	 */
	public function add_page($orientation = '', $resetpagenum = '', $pagenumstyle = '', $suppress = '') {
		$this->mpdf->AddPage($orientation, '', $resetpagenum, $pagenumstyle, $suppress);
		return $this;
	}

	/**
	 * Add page break
	 */
	public function add_page_break() {
		$this->mpdf->WriteHTML('<pagebreak />');
		return $this;
	}

	/**
	 * Set font
	 */
	public function set_font($family, $size = 0) {
		if ($size > 0) {
			$this->mpdf->WriteHTML('<style>body { font-family: ' . $family . '; font-size: ' . $size . 'pt; }</style>', 1);
		} else {
			$this->mpdf->WriteHTML('<style>body { font-family: ' . $family . '; }</style>', 1);
		}
		return $this;
	}

	/**
	 * Add CSS styles
	 */
	public function add_css($css) {
		// $this->mpdf->WriteHTML('<style>' . $css . '</style>', 1);
		$this->mpdf->WriteHTML($css, 1);
		return $this;
	}

	/**
	 * Add CSS from file
	 */
	public function add_css_file($css_file) {
		if (file_exists($css_file)) {
			$css = file_get_contents($css_file);
			$this->add_css($css);
		}
		return $this;
	}

	/**
	 * Generate and output PDF
	 */
	public function generate($filename = 'document.pdf', $dest = 'I') {
		$this->mpdf->Output($filename, $dest);
	}

	/**
	 * Save PDF to file
	 */
	public function save($filepath) {
		$this->mpdf->Output($filepath, 'F');
		return file_exists($filepath);
	}

	/**
	 * Get PDF as string
	 */
	public function get_pdf_string() {
		return $this->mpdf->Output('', 'S');
	}

	/**
	 * Add table from array
	 */
	public function add_table($data, $headers = [], $config = []) {
		if (empty($data)) {
			return $this;
		}

		$default_table_config = [
			'class' => 'pdf-table',
			'style' => 'border-collapse: collapse; width: 100%; margin: 10px 0;',
			'header_style' => 'background-color: #f2f2f2; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #ddd;',
			'cell_style' => 'padding: 6px; border: 1px solid #ddd; text-align: left;',
			'alternate_row' => true,
			'alternate_row_color' => '#f9f9f9'
		];

		$table_config = array_merge($default_table_config, $config);

		$html = '<table class="' . $table_config['class'] . '" style="' . $table_config['style'] . '">';

		// Add headers
		if (!empty($headers)) {
			$html .= '<thead><tr>';
			foreach ($headers as $header) {
				$html .= '<th style="' . $table_config['header_style'] . '">' . htmlspecialchars($header) . '</th>';
			}
			$html .= '</tr></thead>';
		}

		// Add data rows
		$html .= '<tbody>';
		foreach ($data as $row_index => $row) {
			$row_style = $table_config['cell_style'];
			if ($table_config['alternate_row'] && $row_index % 2 == 1) {
				$row_style .= ' background-color: ' . $table_config['alternate_row_color'] . ';';
			}

			$html .= '<tr>';
			foreach ($row as $cell) {
				$html .= '<td style="' . $row_style . '">' . htmlspecialchars($cell) . '</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';

		$this->mpdf->WriteHTML($html);
		return $this;
	}

	/**
	 * Set bookmark
	 */
	public function add_bookmark($text, $level = 0) {
		$this->mpdf->Bookmark($text, $level);
		return $this;
	}

	/**
	 * Add image
	 */
	public function add_image($file, $x = 0, $y = 0, $w = 0, $h = 0, $type = '', $link = '') {
		if ($x == 0 && $y == 0) {
			// Inline image
			$img_html = '<img src="' . $file . '"';
			if ($w > 0) $img_html .= ' width="' . $w . 'mm"';
			if ($h > 0) $img_html .= ' height="' . $h . 'mm"';
			if (!empty($link)) $img_html .= ' onclick="window.open(\'' . $link . '\')"';
			$img_html .= ' />';
			$this->mpdf->WriteHTML($img_html);
		} else {
			// Positioned image - requires custom implementation or use of annotations
			$this->mpdf->Image($file, $x, $y, $w, $h, $type, $link);
		}
		return $this;
	}

	/**
	 * NEW METHOD: Add file to PDF based on file type
	 */
	public function add_file($file_path, $options = []) {
		if (!file_exists($file_path)) {
			log_message('error', 'mPDF: File not found: ' . $file_path);
			return $this;
		}

		$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

		switch ($file_extension) {
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
			case 'bmp':
			case 'webp':
				return $this->add_image_file($file_path, $options);

			case 'pdf':
				return $this->add_pdf_file($file_path, $options);

			case 'doc':
			case 'docx':
				return $this->add_word_file($file_path, $options);

			case 'xls':
			case 'xlsx':
			case 'csv':
				return $this->add_excel_file($file_path, $options);

			case 'txt':
				return $this->add_text_file($file_path, $options);

			default:
				return $this->add_unsupported_file($file_path, $options);
		}
	}

	/**
	 * Add image file with enhanced options
	 */
	private function add_image_file($file_path, $options = []) {
		$default_options = [
			'width' => 0,
			'height' => 0,
			'max_width' => 180, // mm
			'max_height' => 250, // mm
			'align' => 'center',
			'caption' => '',
			'caption_style' => 'font-size: 10px; text-align: center; margin-top: 5px;',
			'add_filename' => true,
		];

		$options = array_merge($default_options, $options);

		// Add filename header if requested
		if ($options['add_filename']) {
			$html = '<h3 style="color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 15px;">';
			$html .= 'Image: ' . basename($file_path) . '</h3>';
			$this->mpdf->WriteHTML($html);
		}

		// Get image dimensions and calculate appropriate size
		if ($options['width'] == 0 && $options['height'] == 0) {
			$image_info = getimagesize($file_path);
			if ($image_info) {
				$original_width = $image_info[0];
				$original_height = $image_info[1];

				// Convert pixels to mm (rough conversion: 1mm ≈ 3.78px at 96 DPI)
				$width_mm = $original_width / 3.78;
				$height_mm = $original_height / 3.78;

				// Scale down if too large
				if ($width_mm > $options['max_width']) {
					$scale = $options['max_width'] / $width_mm;
					$width_mm = $options['max_width'];
					$height_mm = $height_mm * $scale;
				}

				if ($height_mm > $options['max_height']) {
					$scale = $options['max_height'] / $height_mm;
					$height_mm = $options['max_height'];
					$width_mm = $width_mm * $scale;
				}

				$options['width'] = $width_mm;
				$options['height'] = $height_mm;
			}
		}

		$html = '<div style="text-align: ' . $options['align'] . '; margin: 10px 0;">';
		$html .= '<img src="' . $file_path . '"';
		if ($options['width'] > 0) $html .= ' width="' . $options['width'] . 'mm"';
		if ($options['height'] > 0) $html .= ' height="' . $options['height'] . 'mm"';
		$html .= ' />';

		if (!empty($options['caption'])) {
			$html .= '<div style="' . $options['caption_style'] . '">' . htmlspecialchars($options['caption']) . '</div>';
		}

		$html .= '</div>';

		$this->mpdf->WriteHTML($html);
		return $this;
	}

	/**
	 * Add PDF file pages
	 */
	private function add_pdf_file($file_path, $options = []) {
		$default_options = [
			'pages' => 'all', // 'all' or array of page numbers [1,2,3]
			'scale' => 1.0,
			'add_page_break' => true,
			'add_filename' => true,
			'preserve_size' => false, // true to keep original page sizes, false to fit to current format
			'center_on_page' => true, // center the imported page if it's smaller
			'scale_to_fit' => true // scale down large pages to fit
		];
		$options = array_merge($default_options, $options);

		try {
			// Check if file exists and is readable
			if (!file_exists($file_path) || !is_readable($file_path)) {
				throw new Exception('PDF file not found or not readable: ' . $file_path);
			}

			// Add filename header if requested
			if ($options['add_filename']) {
				$html = '<h3 style="color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 15px;">';
				$html .= 'PDF: ' . basename($file_path) . '</h3>';
				$this->mpdf->WriteHTML($html);
			}

			// Get current document settings (using available properties)
			$currentMargins = [
				'left' => $this->config['margin_left'] ?? 15,
				'right' => $this->config['margin_right'] ?? 15,
				'top' => $this->config['margin_top'] ?? 16,
				'bottom' => $this->config['margin_bottom'] ?? 16
			];

			// Set source file and get page count
			$page_count = $this->mpdf->SetSourceFile($file_path);

			// Determine which pages to import
			$pages_to_import = [];
			if ($options['pages'] === 'all') {
				$pages_to_import = range(1, $page_count);
			} elseif (is_array($options['pages'])) {
				$pages_to_import = array_filter($options['pages'], function($page) use ($page_count) {
					return $page >= 1 && $page <= $page_count;
				});
			} else {
				throw new Exception('Invalid pages option. Use "all" or array of page numbers.');
			}

			if (empty($pages_to_import)) {
				throw new Exception('No valid pages to import.');
			}

			foreach ($pages_to_import as $page_num) {
				try {
					// Import the page template
					$templateId = $this->mpdf->ImportPage($page_num);

					// Get the imported page dimensions
					$template_size = $this->mpdf->GetTemplateSize($templateId);
					$template_width = $template_size['width'];
					$template_height = $template_size['height'];
					$template_orientation = ($template_width > $template_height) ? 'L' : 'P';

					if ($options['preserve_size']) {
						// Add page with the same size and orientation as the imported page
						// Create custom format array for the imported page size
						$custom_format = [$template_height, $template_width, ];
						$this->mpdf->AddPage($template_orientation,'','','','','','','','','','','','','','','','','','','',$custom_format);

						// Use template at full size
						$this->mpdf->UseTemplate($templateId);

					} else {
						// Add page with current document settings
						$this->mpdf->AddPage();

						// Get current page dimensions
						$current_width = $this->mpdf->w - $currentMargins['left'] - $currentMargins['right'];
						$current_height = $this->mpdf->h - $currentMargins['top'] - $currentMargins['bottom'];

						// Calculate scaling and positioning
						$scale_x = $current_width / $template_width;
						$scale_y = $current_height / $template_height;

						if ($options['scale_to_fit']) {
							// Scale to fit while maintaining aspect ratio
							$scale = min($scale_x, $scale_y, $options['scale']);
						} else {
							// Use specified scale
							$scale = $options['scale'];
						}

						// Calculate dimensions after scaling
						$scaled_width = $template_width * $scale;
						$scaled_height = $template_height * $scale;

						// Calculate position (center if requested and fits)
						if ($options['center_on_page']) {
							$x = $currentMargins['left'] + ($current_width - $scaled_width) / 2;
							$y = $currentMargins['top'] + ($current_height - $scaled_height) / 2;
						} else {
							$x = $currentMargins['left'];
							$y = $currentMargins['top'];
						}

						// Ensure position is not negative
						$x = max($x, $currentMargins['left']);
						$y = max($y, $currentMargins['top']);

						// Use template with calculated position and size
						$this->mpdf->UseTemplate($templateId, $x, $y, $scaled_width, $scaled_height);

						// Add debug info if scale is significantly different from 1.0
						if (abs($scale - 1.0) > 0.1) {
							log_message('info', sprintf(
								'mPDF: Scaled PDF page %d from %.1fx%.1f to %.1fx%.1f (scale: %.2f)',
								$page_num, $template_width, $template_height,
								$scaled_width, $scaled_height, $scale
							));
						}
					}

				} catch (Exception $e) {
					log_message('error', 'mPDF: Error importing page ' . $page_num . ': ' . $e->getMessage());

					// Add error page with standard format
					$this->mpdf->AddPage();
					$error_html = '<div style="border: 1px solid #ff6b6b; padding: 20px; margin: 20px; background: #ffe0e0; color: #d63031; text-align: center;">';
					$error_html .= '<h4>Error importing page ' . $page_num . '</h4>';
					$error_html .= '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
					$error_html .= '</div>';
					$this->mpdf->WriteHTML($error_html);
				}
			}
			// Note: mPDF automatically maintains document settings for subsequent content
			// No need to manually reset format and orientation

			log_message('info', 'mPDF: Successfully imported ' . count($pages_to_import) . ' pages from PDF: ' . basename($file_path));

		} catch (Exception $e) {
			log_message('error', 'mPDF: Error processing PDF file: ' . $e->getMessage());

			// Add error message to PDF
			$this->mpdf->AddPage();
			$error_html = '<div style="border: 1px solid #ff6b6b; padding: 15px; margin: 10px 0; background: #ffe0e0; color: #d63031;">';
			$error_html .= '<p><strong>Error processing PDF file:</strong> ' . basename($file_path) . '</p>';
			$error_html .= '<p><em>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</em></p>';
			$error_html .= '</div>';
			$this->mpdf->WriteHTML($error_html);
		}

		return $this;
	}

	/**
	 * Add Word document content
	 */
	private function add_word_file($file_path, $options = []) {
		$default_options = [
			'extract_text_only' => true,
			'preserve_formatting' => false,
			'add_filename' => true
		];

		$options = array_merge($default_options, $options);

		try {
			// Check if PhpWord is available
			if (!class_exists('PhpOffice\PhpWord\IOFactory')) {
				throw new Exception('PhpWord library not found. Install via composer: composer require phpoffice/phpword');
			}

			if ($options['add_filename']) {
				$html = '<h3 style="color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px;">';
				$html .= 'Document: ' . basename($file_path) . '</h3>';
				$this->mpdf->WriteHTML($html);
			}

			// This is a placeholder - actual implementation would require PhpWord
			$html = '<div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #f9f9f9;">';
			$html .= '<p><strong>Word Document Content</strong></p>';
			$html .= '<p>To fully support Word documents, install PhpOffice/PhpWord:</p>';
			$html .= '<code>composer require phpoffice/phpword</code>';
			$html .= '<p>File: ' . basename($file_path) . '</p>';
			$html .= '</div>';

			$this->mpdf->WriteHTML($html);

		} catch (Exception $e) {
			log_message('error', 'mPDF: Error processing Word file: ' . $e->getMessage());
			$this->add_error_message('Error processing Word file: ' . basename($file_path));
		}

		return $this;
	}

	/**
	 * Add Excel file content
	 */
	private function add_excel_file($file_path, $options = []) {
		$default_options = [
			'sheet_name' => null, // null for active sheet
			'max_rows' => 1000,
			'max_cols' => 50,
			'add_filename' => true,
			'table_style' => [
				'border' => '1px solid #ddd',
				'border_collapse' => 'collapse',
				'width' => '100%',
				'font_size' => '8px'
			],
			'cell_padding' => '4px',
			'header_bg' => '#f5f5f5',
			'alternate_row_bg' => '#fafafa',
			'include_empty_cells' => false
		];
		$options = array_merge($default_options, $options);

		try {
			$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
			if ($file_extension === 'csv') {
				return $this->add_csv_file($file_path, $options);
			}

			// Check if PhpSpreadsheet is available
			if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
				throw new Exception('PhpSpreadsheet library not found. Install via composer: composer require phpoffice/phpspreadsheet');
			}

			// Check if file exists and is readable
			if (!file_exists($file_path) || !is_readable($file_path)) {
				throw new Exception('Excel file not found or not readable: ' . $file_path);
			}

			// Load the spreadsheet
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);

			// Get the worksheet
			if ($options['sheet_name']) {
				$worksheet = $spreadsheet->getSheetByName($options['sheet_name']);
				if (!$worksheet) {
					throw new Exception('Sheet "' . $options['sheet_name'] . '" not found');
				}
			} else {
				$worksheet = $spreadsheet->getActiveSheet();
			}

			// Add filename header if requested
			if ($options['add_filename']) {
				$sheet_title = $worksheet->getTitle() ? ' (Sheet: ' . $worksheet->getTitle() . ')' : '';
				$html = '<h3 style="color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 15px;">';
				$html .= 'Spreadsheet: ' . basename($file_path) . $sheet_title . '</h3>';
				$this->mpdf->WriteHTML($html);
			}

			// Get the highest row and column numbers
			$highestRow = $worksheet->getHighestRow();
			$highestColumn = $worksheet->getHighestColumn();
			$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

			// Apply limits
			$maxRows = min($highestRow, $options['max_rows']);
			$maxCols = min($highestColumnIndex, $options['max_cols']);

			// Start building HTML table
			$tableStyle = '';
			foreach ($options['table_style'] as $property => $value) {
				$tableStyle .= str_replace('_', '-', $property) . ': ' . $value . '; ';
			}

			$html = '<table style="' . $tableStyle . '">';

			// Process rows
			$hasData = false;
			for ($row = 1; $row <= $maxRows; $row++) {
				$rowHtml = '';
				$rowHasData = false;

				// Determine if this is likely a header row (first row with data)
				$isHeaderRow = ($row == 1);

				// Row styling
				$rowStyle = '';
				if ($isHeaderRow) {
					$rowStyle .= 'background-color: ' . $options['header_bg'] . '; font-weight: bold; ';
				} elseif ($row % 2 == 0) {
					$rowStyle .= 'background-color: ' . $options['alternate_row_bg'] . '; ';
				}

				$rowHtml .= '<tr' . ($rowStyle ? ' style="' . $rowStyle . '"' : '') . '>';

				for ($col = 1; $col <= $maxCols; $col++) {
					$cell = $worksheet->getCellByColumnAndRow($col, $row);
					$cellValue = '';

					// Get cell value based on type
					if ($cell->getValue() !== null) {
						if ($cell->getDataType() == \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA) {
							try {
								$cellValue = $cell->getCalculatedValue();
							} catch (Exception $e) {
								$cellValue = $cell->getValue(); // Fallback to formula text
							}
						} else {
							$cellValue = $cell->getFormattedValue();
						}

						// Clean up the value
						$cellValue = trim($cellValue);
						if ($cellValue !== '') {
							$rowHasData = true;
							$hasData = true;
						}
					}

					// Skip empty cells at the end if option is set
					if (!$options['include_empty_cells'] && $cellValue === '') {
						// Check if there are more non-empty cells in this row
						$hasMoreData = false;
						for ($checkCol = $col + 1; $checkCol <= $maxCols; $checkCol++) {
							$checkCell = $worksheet->getCellByColumnAndRow($checkCol, $row);
							if ($checkCell->getValue() !== null && trim($checkCell->getFormattedValue()) !== '') {
								$hasMoreData = true;
								break;
							}
						}
						if (!$hasMoreData) {
							break; // Skip remaining empty columns in this row
						}
					}

					// Cell styling
					$cellStyle = 'padding: ' . $options['cell_padding'] . '; ';
					$cellStyle .= 'border: ' . $options['table_style']['border'] . '; ';
					$cellStyle .= 'font-size: ' . $options['table_style']['font_size'] . '; ';

					// Get cell alignment from Excel
					$alignment = $cell->getStyle()->getAlignment();
					if ($alignment->getHorizontal() !== \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_GENERAL) {
						switch ($alignment->getHorizontal()) {
							case \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT:
								$cellStyle .= 'text-align: left; ';
								break;
							case \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT:
								$cellStyle .= 'text-align: right; ';
								break;
							case \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER:
								$cellStyle .= 'text-align: center; ';
								break;
						}
					}

					// Check for merged cells
					$isMerged = false;
					$colspan = 1;
					$rowspan = 1;

					foreach ($worksheet->getMergeCells() as $mergeRange) {
						if ($cell->isInRange($mergeRange)) {
							$isMerged = true;
							$mergeRangeCoordinates = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::getRangeBoundaries($mergeRange);

							// Convert column letters to numeric indices
							$startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($mergeRangeCoordinates[0][0]);
							$endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($mergeRangeCoordinates[1][0]);
							$startRow = $mergeRangeCoordinates[0][1];
							$endRow = $mergeRangeCoordinates[1][1];

							$colspan = $endCol - $startCol + 1;
							$rowspan = $endRow - $startRow + 1;

							// Skip if this is not the top-left cell of the merge
							$topLeftCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol) . $startRow;
							if ($cell->getCoordinate() !== $topLeftCoordinate) {
								continue 2; // Skip this cell
							}
							break;
						}
					}

					// Escape HTML and preserve line breaks
					$cellValue = htmlspecialchars($cellValue, ENT_QUOTES, 'UTF-8');
					$cellValue = nl2br($cellValue);

					// Build cell HTML
					$tagName = $isHeaderRow ? 'th' : 'td';
					$cellHtml = '<' . $tagName . ' style="' . $cellStyle . '"';

					if ($colspan > 1) {
						$cellHtml .= ' colspan="' . $colspan . '"';
					}
					if ($rowspan > 1) {
						$cellHtml .= ' rowspan="' . $rowspan . '"';
					}

					$cellHtml .= '>' . ($cellValue !== '' ? $cellValue : '&nbsp;') . '</' . $tagName . '>';
					$rowHtml .= $cellHtml;
				}

				$rowHtml .= '</tr>';

				// Only add row if it has data or if we're including empty rows
				if ($rowHasData || $options['include_empty_cells']) {
					$html .= $rowHtml;
				}
			}

			$html .= '</table>';

			// Add some spacing after the table
			$html .= '<div style="margin-bottom: 10px;"></div>';

			// Only write HTML if we found data
			if ($hasData) {
				$this->mpdf->WriteHTML($html);
			} else {
				// Add a message for empty spreadsheets
				$emptyHtml = '<div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #f9f9f9;">';
				$emptyHtml .= '<p><em>The spreadsheet appears to be empty or contains no readable data.</em></p>';
				$emptyHtml .= '</div>';
				$this->mpdf->WriteHTML($emptyHtml);
			}

			// Log successful processing
			log_message('info', 'mPDF: Successfully processed Excel file: ' . basename($file_path) . ' (' . $maxRows . ' rows, ' . $maxCols . ' columns)');

		} catch (Exception $e) {
			log_message('error', 'mPDF: Error processing Excel file: ' . $e->getMessage());

			// Add error message to PDF
			$errorHtml = '<div style="border: 1px solid #ff6b6b; padding: 15px; margin: 10px 0; background: #ffe0e0; color: #d63031;">';
			$errorHtml .= '<p><strong>Error processing Excel file:</strong> ' . basename($file_path) . '</p>';
			$errorHtml .= '<p><em>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</em></p>';
			$errorHtml .= '</div>';
			$this->mpdf->WriteHTML($errorHtml);
		}

		return $this;
	}

	/**
	 * Add CSV file content
	 */
	private function add_csv_file($file_path, $options = []) {
		try {
			if (($handle = fopen($file_path, "r")) !== FALSE) {
				$data = [];
				$headers = [];
				$row_count = 0;

				while (($row = fgetcsv($handle, 1000, ",")) !== FALSE && $row_count < ($options['max_rows'] ?? 1000)) {
					if ($row_count === 0) {
						$headers = $row;
					} else {
						$data[] = $row;
					}
					$row_count++;
				}
				fclose($handle);

				if (!empty($data)) {
					$this->add_table($data, $headers, [
						'class' => 'csv-table',
						'style' => 'border-collapse: collapse; width: 100%; margin: 10px 0; font-size: 8px;'
					]);
				}
			}
		} catch (Exception $e) {
			log_message('error', 'mPDF: Error processing CSV file: ' . $e->getMessage());
			$this->add_error_message('Error processing CSV file: ' . basename($file_path));
		}

		return $this;
	}

	/**
	 * Add text file content
	 */
	private function add_text_file($file_path, $options = []) {
		$default_options = [
			'max_length' => 10000,
			'preserve_formatting' => true,
			'add_filename' => true
		];

		$options = array_merge($default_options, $options);

		try {
			$content = file_get_contents($file_path);

			if (strlen($content) > $options['max_length']) {
				$content = substr($content, 0, $options['max_length']) . "\n\n[Content truncated...]";
			}

			if ($options['add_filename']) {
				$html = '<h3 style="color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px;">';
				$html .= 'Text File: ' . basename($file_path) . '</h3>';
				$this->mpdf->WriteHTML($html);
			}

			if ($options['preserve_formatting']) {
				$html = '<pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd; white-space: pre-wrap; font-family: monospace; font-size: 8px;">';
				$html .= htmlspecialchars($content);
				$html .= '</pre>';
			} else {
				$html = '<div style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9;">';
				$html .= '<p>' . nl2br(htmlspecialchars($content)) . '</p>';
				$html .= '</div>';
			}

			$this->mpdf->WriteHTML($html);

		} catch (Exception $e) {
			log_message('error', 'mPDF: Error processing text file: ' . $e->getMessage());
			$this->add_error_message('Error processing text file: ' . basename($file_path));
		}

		return $this;
	}

	/**
	 * Handle unsupported file types
	 */
	private function add_unsupported_file($file_path, $options = []) {
		$file_info = [
			'name' => basename($file_path),
			'size' => filesize($file_path),
			'type' => pathinfo($file_path, PATHINFO_EXTENSION),
			'modified' => date('Y-m-d H:i:s', filemtime($file_path))
		];

		$html = '<div style="border: 2px dashed #ccc; padding: 15px; margin: 10px 0; text-align: center; background: #f9f9f9;">';
		$html .= '<h4 style="color: #666;">📄 Unsupported File Type</h4>';
		$html .= '<p><strong>File:</strong> ' . htmlspecialchars($file_info['name']) . '</p>';
		$html .= '<p><strong>Type:</strong> ' . strtoupper($file_info['type']) . '</p>';
		$html .= '<p><strong>Size:</strong> ' . $this->format_file_size($file_info['size']) . '</p>';
		$html .= '<p><strong>Modified:</strong> ' . $file_info['modified'] . '</p>';
		$html .= '<p style="font-size: 10px; color: #999;">This file type cannot be embedded directly into the PDF.</p>';
		$html .= '</div>';

		$this->mpdf->WriteHTML($html);

		return $this;
	}

	/**
	 * Add error message to PDF
	 */
	private function add_error_message($message) {
		$html = '<div style="border: 1px solid #ff6b6b; background: #ffe0e0; padding: 10px; margin: 10px 0; color: #d63031;">';
		$html .= '<strong>⚠️ Error:</strong> ' . htmlspecialchars($message);
		$html .= '</div>';

		$this->mpdf->WriteHTML($html);
		return $this;
	}

	/**
	 * Format file size for display
	 */
	private function format_file_size($bytes) {
		$units = ['B', 'KB', 'MB', 'GB'];
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= pow(1024, $pow);

		return round($bytes, 2) . ' ' . $units[$pow];
	}

	/**
	 * Add multiple files at once
	 */
	public function add_files($file_paths, $options = []) {
		if (!is_array($file_paths)) {
			$file_paths = [$file_paths];
		}

		// do not add header if specified
		if (empty($options['header_enable'])) {
			$this->mpdf->SetHTMLHeader();
		}

		foreach ($file_paths as $file_path) {
			$this->add_file($file_path, $options);

			// Add page break between files if specified
			if (!empty($options['page_break_between_files'])) {
				$this->add_page_break();
			}
		}

		return $this;
	}

	/**
	 * Clean up temporary files
	 */
	public function cleanup_temp_files() {
		foreach ($this->temp_files as $temp_file) {
			if (file_exists($temp_file)) {
				unlink($temp_file);
			}
		}
		$this->temp_files = [];
		return $this;
	}

	/**
	 * Destructor to clean up temporary files
	 */
	public function __destruct() {
		$this->cleanup_temp_files();
	}
	/**
	 * Set password protection
	 */
	public function set_protection($permissions = [], $user_password = '', $owner_password = '') {
		$this->mpdf->SetProtection($permissions, $user_password, $owner_password);
		return $this;
	}

	/**
	 * Set watermark text
	 */
	public function set_watermark_text($text, $alpha = 0.2) {
		$this->mpdf->SetWatermarkText($text, $alpha);
		$this->mpdf->showWatermarkText = true;
		return $this;
	}

	/**
	 * Set watermark image
	 */
	public function set_watermark_image($image, $alpha = 0.2, $size = 'D', $position = 'P') {
		$this->mpdf->SetWatermarkImage($image, $alpha, $size, $position);
		$this->mpdf->showWatermarkImage = true;
		return $this;
	}

	/**
	 * Add HTML header
	 */
	public function set_html_header($html, $side = '') {
		if (empty($side)) {
			$this->mpdf->SetHTMLHeader($html);
		} else {
			$this->mpdf->SetHTMLHeader($html, $side);
		}
		return $this;
	}

	/**
	 * Add HTML footer
	 */
	public function set_html_footer($html, $side = '') {
		if (empty($side)) {
			$this->mpdf->SetHTMLFooter($html);
		} else {
			$this->mpdf->SetHTMLFooter($html, $side);
		}
		return $this;
	}

	/**
	 * Enable/disable debug mode
	 */
	public function debug($enable = true) {
		$this->mpdf->debug = $enable;
		return $this;
	}
}