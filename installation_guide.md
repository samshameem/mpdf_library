# mPDF Library for CodeIgniter 3

A flexible and configurable PDF generator using mPDF for CodeIgniter 3, similar to the TCPDF implementation but with modern mPDF features.

## Installation

### 1. Install mPDF via Composer

First, make sure you have Composer installed in your CodeIgniter project. Then run:

```bash
composer require mpdf/mpdf
```

### 2. Install the Library Files

1. **Library File**: Place `Mpdf_generator.php` in `application/libraries/`
2. **Config File**: Place `mpdf.php` in `application/config/`
3. **Example Controller**: Place the example controller in `application/controllers/` (optional)
4. **View Template**: Create `application/views/pdf/` directory and place `invoice_template.php` there (optional)

### 3. Update Autoload (Optional)

Add to `application/config/autoload.php`:

```php
$autoload['libraries'] = array('mpdf_generator');
```

## Basic Usage

### Simple PDF Generation

```php
// Load the library
$this->load->library('mpdf_generator');

// Add content
$this->mpdf_generator->add_html('<h1>Hello World</h1><p>This is my first PDF!</p>');

// Generate PDF
$this->mpdf_generator->generate('my_document.pdf', 'I'); // I = inline, D = download
```

### PDF with Configuration

```php
$config = [
    'title' => 'My Custom PDF',
    'author' => 'John Doe',
    'orientation' => 'L', // Landscape
    'header_title' => 'Company Report',
    'footer_text' => 'Confidential'
];

$this->load->library('mpdf_generator', $config);
// or
$this->mpdf_generator->set_config($config);
```

## Key Features

### 1. HTML Content
```php
$html = '<h1>Title</h1><p>Content with <strong>formatting</strong></p>';
$this->mpdf_generator->add_html($html);
```

### 2. View Integration
```php
$data = ['name' => 'John', 'date' => date('Y-m-d')];
$this->mpdf_generator->add_view('pdf/my_template', $data);
```

### 3. Tables
```php
$headers = ['Name', 'Email', 'Phone'];
$data = [
    ['John Doe', 'john@example.com', '123-456-7890'],
    ['Jane Smith', 'jane@example.com', '098-765-4321']
];
$this->mpdf_generator->add_table($data, $headers);
```

### 4. Custom CSS
```php
$css = '.highlight { background-color: yellow; padding: 10px; }';
$this->mpdf_generator->add_css($css);
```

### 5. Multiple Pages
```php
$this->mpdf_generator->add_html('<h1>Page 1</h1>');
$this->mpdf_generator->add_page(); // New page
$this->mpdf_generator->add_html('<h1>Page 2</h1>');
```

### 6. Images
```php
// Inline image in HTML
$this->mpdf_generator->add_html('<img src="path/to/image.jpg" width="200" />');

// Programmatic image insertion
$this->mpdf_generator->add_image('path/to/image.jpg', 0, 0, 50, 30);
```

### 7. Watermarks
```php
// Text watermark
$this->mpdf_generator->set_watermark_text('CONFIDENTIAL', 0.2);

// Image watermark
$this->mpdf_generator->set_watermark_image('path/to/watermark.png', 0.1);
```

### 8. Password Protection
```php
$this->mpdf_generator->set_protection(['print', 'copy'], 'user_pass', 'owner_pass');
```

## Output Methods

### 1. Display in Browser (Inline)
```php
$this->mpdf_generator->generate('document.pdf', 'I');
```

### 2. Force Download
```php
$this->mpdf_generator->generate('document.pdf', 'D');
```

### 3. Save to File
```php
$success = $this->mpdf_generator->save('/path/to/save/document.pdf');
```

### 4. Get as String (for email attachments)
```php
$pdf_string = $this->mpdf_generator->get_pdf_string();
```

## Configuration Options

The library supports extensive configuration options:

### Page Settings
- `format`: Page format (A4, A3, Letter, etc.)
- `orientation`: P (Portrait) or L (Landscape)
- `margin_left`, `margin_right`, `margin_top`, `margin_bottom`: Page margins in mm

### Document Properties
- `title`, `author`, `subject`, `creator`, `keywords`: PDF metadata

### Header/Footer
- `header_enable`, `footer_enable`: Enable/disable headers and footers
- `header_title`, `header_string`: Header content
- `footer_text`, `footer_show_page`: Footer content and page numbers

### Security
- `protection`: Enable password protection
- `user_password`, `owner_password`: Access passwords
- `permissions`: Array of allowed actions

## Advanced Features

### Custom Headers and Footers
```php
$header_html = '<div style="text-align: center; font-weight: bold;">Custom Header</div>';
$footer_html = '<div style="text-align: center;">Page {PAGENO} of {nbpg}</div>';

$this->mpdf_generator->set_html_header($header_html);
$this->mpdf_generator->set_html_footer($footer_html);
```

### Bookmarks
```php
$this->mpdf_generator->add_bookmark('Chapter 1', 0);
$this->mpdf_generator->add_html('<h1>Chapter 1</h1>');
```

### Page Breaks
```php
$this->mpdf_generator->add_page_break();
```

## Error Handling

```php
try {
    $this->mpdf_generator->add_html($html);
    $this->mpdf_generator->generate('document.pdf', 'I');
} catch (Exception $e) {
    log_message('error', 'PDF Generation Error: ' . $e->getMessage());
    show_error('PDF could not be generated');
}
```

## Comparison with TCPDF Version

### Advantages of mPDF:
- Better HTML/CSS support
- Faster processing
- More modern codebase
- Better Unicode support
- Smaller file sizes
- Easier watermark implementation

### Migration Notes:
- Similar API design for easy migration
- Configuration options mapped appropriately  
- All major features preserved
- Enhanced HTML rendering capabilities

## Troubleshooting

### Common Issues:

1. **Composer autoload not found**
   - Ensure the path to `vendor/autoload.php` is correct
   - Install mPDF via Composer in your project root

2. **Memory issues with large PDFs**
   - Increase PHP memory limit
   - Process large datasets in chunks

3. **Font issues**
   - mPDF includes comprehensive font support by default
   - Custom fonts can be added to the mPDF font directory

4. **Image not displaying**
   - Ensure image paths are absolute or relative to the script
   - Check image file permissions
   - Verify image formats are supported (JPG, PNG, GIF)

## Requirements

- PHP 7.1 or higher
- CodeIgniter 3.x
- mPDF 8.x (installed via Composer)
- GD extension (for image processing)
- Multibyte string extension (mbstring)

## License

This library wrapper is provided as-is. mPDF itself is licensed under GPL v2+.