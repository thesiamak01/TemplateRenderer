# TemplateRenderer

![Banner](https://raw.githubusercontent.com/thesiamak01/TemplateRenderer/refs/heads/main/TemplateRenderer-theSiamak01.png)

## Overview
TemplateRenderer is a lightweight and flexible PHP-based template engine designed for processing custom tags within template strings. It supports variable replacement, block processing, conditional statements, PHP code execution, and template inclusion.

---

## Features

- **Variable Replacement**: Replace placeholders with data values.
- **Block Processing**: Handle block-level tags and inner loops.
- **PHP Execution**: Safely execute PHP code within templates.
- **Conditional Statements**: Process conditional logic directly in templates.
- **Template Inclusion**: Include other templates or files dynamically.

---

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/thesiamak01/TemplateRenderer.git
   ```

2. Include the main class in your PHP project:
   ```php
   include_once('TemplateRenderer.class.php');
   ```

---

## Usage

### Example Code

```php
// Initialize the renderer
$renderer = new TemplateRenderer();

// Assign data
$renderer->assign('siteTitle', 'Test WebSite');
$renderer->assign('auth', true);
$renderer->assign('PostList', [
    ['PostItemName' => 'Item 1', 'PostItemLink' => 'item-1'],
    ['PostItemName' => 'Item 2', 'PostItemLink' => 'item-2'],
]);

$renderer->assign('num_1', 5);
$renderer->assign('num_2', 10);

// Load template
$template = file_get_contents('template.html');

// Render template
echo $renderer->render($template);
```

---

## Template Syntax

### Variables
```html
[tag::siteTitle]
```

### Blocks
```html
[tag::PostList--Block]
    <ul>
        [tag::PostList--Loop]
            <li>[tag::PostItemName]</li>
        [/tag::PostList--Loop]
    </ul>
[/tag::PostList--Block]
```

### PHP Code
```html
[tag::php]
echo date('Y-m-d');
[/tag::php]
```

### Includes
```html
[tag::include('header.html')]
```

### Conditions
```html
[tag::if($auth === true)]
  Welcome, user!
[tag::else]
  Please log in.
[tag::endif]
```
or
```html
[tag::if($num_1 > $num_2)]
    number 1 > number 2
[tag::elseif($num_1 == $num_2)]
    number 1 = number 2
[tag::else]
    number 1 < number 2
[tag::endif]
```

---

## License

This project is licensed under the MIT License.

---

## Contact

- **Author**: Siamak Yousefi
- **Email**: syj2001ard@gmail.com
