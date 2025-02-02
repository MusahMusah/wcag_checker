# WCAG Checker
Ensuring web accessibility is essential for creating inclusive
digital experiences. With this tool, you can upload an HTML file
to analyze its compliance with WCAG (Web Content Accessibility
Guidelines). The system will scan your file for common
accessibility issues, such as missing alternative text, poor
keyboard navigation, improper heading structures, and ARIA
implementation. After the analysis, you&#39;ll receive a detailed
report with actionable recommendations to improve accessibility,
helping you build a more inclusive and user-friendly website.

## Prerequisites

Ensure you have the following installed before proceeding:
- [PHP](https://www.php.net/) (version 8.2 or later recommended)
- [Composer](https://getcomposer.org/)
- [Nginx](https://www.nginx.com/) or [Apache](https://httpd.apache.org/) (for serving the application)
- [Docker](https://www.docker.com/) (optional for containerized development)

## Installation

Clone the repository and install dependencies:

```bash
cd wcag_checker

# Install PHP dependencies
composer install
```

## Environment Configuration

Copy the example environment file and set up the required configurations:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

## Running the Application

Start the development server:

```bash
php artisan serve
```

Alternatively, if using Docker:

```bash
./vendor/bin/sail up 
```

The application should now be accessible at [http://localhost:8000](http://localhost:8000).


## Running Tests

Run the test suite:

```bash
php artisan test
```

## Deployment

### Deploying to Production

For production deployment, set up your web server:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ensure the web server points to the `public/` directory.

---

## WCAGAnalyzer Architecture and Scoring Logic

### Architecture

The `WCAGAnalyzer` service is designed to analyze HTML documents for Web Content Accessibility Guidelines (WCAG) compliance. It follows a modular architecture:

1. **Parser Factory**: The `HtmlParserFactory` allows switching between different HTML parsers (`CustomHtmlParser` or `SymfonyHtmlParser`) for flexibility.
2. **Rules Pipeline**: Uses Laravel's `Pipeline` to pass the HTML content through various WCAG rules, such as `MissingAltRule` and `HeadingHierarchyRule`.
3. **Rules Implementation**: Each rule extends `BaseWCAGRule` and checks specific accessibility issues, storing detected issues.
4. **Scoring System**: Generates an accessibility score based on the ratio of detected issues to total HTML elements.

### Scoring Logic

1. The parser extracts all HTML elements from the document.
2. Each rule checks for accessibility violations and logs them.
3. The total number of violations is compared against the total number of HTML elements.
4. The accessibility score is calculated using the formula:

   ```php
   $score = $totalElements ? round((1 - $issueCount / $totalElements) * 100, 2) : 100;
   ```

This ensures that documents with fewer issues receive a higher accessibility score.

### Author
Developed by [Musah Musah](https://github.com/musahmusah).
