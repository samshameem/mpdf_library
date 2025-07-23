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
require_once FCPATH . 'vendor/autoload.php'; // Adjust path as needed

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class Mpdf_generator {
	private $CI;
	private $mpdf;
	private $config;
	private $default_config = [
		// Page settings
		'mode' => 'utf-8',
		'format' => 'A4',
		'orientation' => 'P', // P for Portrait, L for Landscape
		'margin_left' => 15,
		'margin_right' => 15,
		'margin_top' => 16,
		'margin_bottom' => 16,
		'margin_header' => 9,
		'margin_footer' => 9,

		// Document information
		'title' => 'PDF Document',
		'author' => 'CodeIgniter Application',
		'subject' => 'PDF Document',
		'creator' => 'mPDF Generator',
		'keywords' => '',

		// Font settings
		'default_font' => 'dejavusans',
		'default_font_size' => 12,

		// Header/Footer settings
		'header_enable' => true,
		'footer_enable' => true,
		'header_title' => '',
		'header_string' => '',
		'footer_text' => '',
		'footer_show_page' => true,
		'header_logo' => '',
		'header_logo_width' => 0,
		'header_logo_height' => 0,

		// Protection settings
		'protection' => false,
		'user_password' => '',
		'owner_password' => '',
		'permissions' => [],

		// Other settings
		'auto_language_detection' => true,
		'auto_script_to_lang' => true,
		'baseScript' => 1,
		'autoVietnamese' => true,
		'autoArabic' => true,
		'watermark' => '',
		'watermark_alpha' => 0.2,
		'show_watermark_image' => false,
		'show_watermark_text' => false,
	];

	public function __construct($config = []) {
		$this->CI =& get_instance();

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

		$this->initialize();
	}

	/**
	 * Initialize mPDF with configuration
	 */
	private function initialize() {
		$mpdf_config = [
			'mode' => $this->config['mode'],
			'format' => $this->config['format'],
			'default_font_size' => $this->config['default_font_size'],
			'default_font' => $this->config['default_font'],
			'margin_left' => $this->config['margin_left'],
			'margin_right' => $this->config['margin_right'],
			'margin_top' => $this->config['margin_top'],
			'margin_bottom' => $this->config['margin_bottom'],
			'margin_header' => $this->config['margin_header'],
			'margin_footer' => $this->config['margin_footer'],
			'orientation' => $this->config['orientation'],
			'autoLangToFont' => $this->config['auto_language_detection'],
			'autoScriptToLang' => $this->config['auto_script_to_lang'],
			'baseScript' => $this->config['baseScript'],
			'autoVietnamese' => $this->config['autoVietnamese'],
			'autoArabic' => $this->config['autoArabic'],
		];

		$this->mpdf = new Mpdf($mpdf_config);

		// Set document properties
		$this->mpdf->SetTitle($this->config['title']);
		$this->mpdf->SetAuthor($this->config['author']);
		$this->mpdf->SetSubject($this->config['subject']);
		$this->mpdf->SetCreator($this->config['creator']);
		$this->mpdf->SetKeywords($this->config['keywords']);

		// Set protection if enabled
		if ($this->config['protection']) {
			$this->mpdf->SetProtection(
				$this->config['permissions'],
				$this->config['user_password'],
				$this->config['owner_password']
			);
		}

		// Set watermark
		if (!empty($this->config['watermark'])) {
			if ($this->config['show_watermark_text']) {
				$this->mpdf->SetWatermarkText($this->config['watermark'], $this->config['watermark_alpha']);
				$this->mpdf->showWatermarkText = true;
			} else if ($this->config['show_watermark_image']) {
				$this->mpdf->SetWatermarkImage(
					$this->config['watermark'],
					$this->config['watermark_alpha']
				);
				$this->mpdf->showWatermarkImage = true;
			}
		}

		$this->setup_header_footer();
	}

	/**
	 * Setup header and footer
	 */
	private function setup_header_footer() {
		$header_html = '';
		$footer_html = '';

		// Build header HTML
		if ($this->config['header_enable']) {
			$header_html = '<div style="text-align: center;">';

			// Add logo if specified
			if (!empty($this->config['header_logo']) && file_exists($this->config['header_logo'])) {
				$logo_style = '';
				if ($this->config['header_logo_width'] > 0) {
					$logo_style .= 'width: ' . $this->config['header_logo_width'] . 'mm; ';
				}
				if ($this->config['header_logo_height'] > 0) {
					$logo_style .= 'height: ' . $this->config['header_logo_height'] . 'mm; ';
				}
				$header_html .= '<img src="' . $this->config['header_logo'] . '" style="' . $logo_style . '">';
			}

			// Add title
			if (!empty($this->config['header_title'])) {
				$header_html .= '<h2 style="margin: 5px 0;">' . $this->config['header_title'] . '</h2>';
			}

			// Add header string
			if (!empty($this->config['header_string'])) {
				$header_html .= '<p style="margin: 2px 0;">' . $this->config['header_string'] . '</p>';
			}

			$header_html .= '</div><hr style="margin: 5px 0;">';
		}

		// Build footer HTML
		if ($this->config['footer_enable']) {
			$footer_html = '<hr style="margin: 5px 0;"><div style="font-size: 10px;">';

			if (!empty($this->config['footer_text']) && $this->config['footer_show_page']) {
				$footer_html .= '<table width="100%"><tr>';
				$footer_html .= '<td style="text-align: left;">' . $this->config['footer_text'] . '</td>';
				$footer_html .= '<td style="text-align: right;">Page {PAGENO} of {nbpg}</td>';
				$footer_html .= '</tr></table>';
			} else if ($this->config['footer_show_page']) {
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
	 * Update configuration
	 */
	public function set_config($config) {
		$this->config = array_merge($this->config, $config);
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
		$this->mpdf->WriteHTML('<style>' . $css . '</style>', 1);
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