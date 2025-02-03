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
The `WCAGAnalyzer` service is designed to analyze HTML documents for Web Content Accessibility Guidelines (WCAG) compliance. It features a sophisticated, modular architecture:

1. **Parser Factory**: The `HtmlParserFactory` provides flexibility by allowing dynamic selection of HTML parsers (`CustomHtmlParser` or `SymfonyHtmlParser`).

2. **Rules Pipeline**: Utilizes Laravel's `Pipeline` to systematically process the `WCAGAnalyzer` instance through multiple WCAG rules:
    - `MissingAltRule`
    - `MissingLabelRule`
    - `HeadingHierarchyRule`
    - `MetaViewportRule`
    - `KeyboardNavigation`

3. **AI-Enhanced Suggestions**: Integrates Gemini AI to provide intelligent, actionable improvements for detected accessibility issues, enhancing the analysis with context-aware recommendations.

4. **Scoring System**: Implements a comprehensive scoring mechanism that calculates accessibility compliance by considering base score, issue impact, and specific penalties.

### Scoring Logic
The scoring algorithm is designed to provide a nuanced evaluation of web accessibility:

1. **Element Analysis**:
    - Extracts and analyzes all HTML elements
    - Focuses on essential elements: `img`, `a`, `input`, `button`, headings, forms, and tables

2. **Base Score Calculation**:
    - **0 essential elements**: Base score = 40
    - **1-2 essential elements**: Base score = 50
    - **3-4 essential elements**: Base score = 60
    - **5+ essential elements**: Base score = 100

3. **Severity-Based Penalty System**:
    - **High severity issues**: -20 points per issue
    - **Medium severity issues**: -10 points per issue
    - **Low severity issues**: -5 points per issue

4. **Dynamic Impact Adjustments**:
    - For pages with less than 5 total elements and existing issues, the impact is increased by 50%
    - Minimal content with no essential accessibility features caps the final score at 40

5. **Score Calculation**:
   ```php
   $score = max(0, min(100, $baseScore - $issueImpact));
   ```

### AI-Powered Accessibility Insights
The analyzer now incorporates Gemini AI to:
- Refine issue descriptions
- Generate more actionable suggestions
- Provide context-aware improvements for accessibility challenges

### Severity Levels Based on WCAG Guidelines

Severity is determined based on WCAG 2.1 guidelines, where violations of critical accessibility requirements are assigned higher severity levels:

- **High Severity (Critical)**: Issues that severely impact accessibility and prevent users from accessing content.
    - **1.1.1 Non-text Content** (Missing alt attributes on images, inaccessible CAPTCHA, missing text alternatives)
    - **2.1.1 Keyboard** (Elements that cannot be accessed via keyboard)
    - **2.2.2 Pause, Stop, Hide** (Content that auto-updates without user control)

- **Medium Severity (Serious)**: Issues that significantly affect usability but do not completely block access.
    - **1.3.1 Info and Relationships** (Incorrect heading hierarchy, missing landmarks)
    - **1.4.3 Contrast (Minimum)** (Insufficient text contrast)
    - **2.4.7 Focus Visible** (No visible focus indicators)

- **Low Severity (Minor)**: Issues that cause inconvenience but do not significantly hinder accessibility.
    - **3.1.1 Language of Page** (Missing `lang` attribute)
    - **4.1.2 Name, Role, Value** (Missing ARIA attributes for better assistive technology support)
    - **2.5.3 Label in Name** (Inconsistent labeling of interactive elements)

By classifying violations according to these WCAG principles, the scoring system ensures an accurate reflection of the accessibility impact.

### Key Features
- Modular rule-based analysis
- Flexible HTML parser support
- AI-enhanced issue recommendations
- Comprehensive accessibility scoring
- Granular severity classification

### Requirements
- Gemini AI API KEY

### Future Enhancements
- Expanded rule set
- More detailed AI suggestions
- Enhanced scoring granularity


### Author
Developed by [Musah Musah](https://github.com/musahmusah).
