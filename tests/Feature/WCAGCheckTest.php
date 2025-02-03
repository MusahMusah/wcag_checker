<?php

use App\Http\Controllers\WCAGController;
use App\Services\WCAG\WCAGAnalyzer;
use App\Interfaces\HtmlParserInterface;
use Illuminate\Http\UploadedFile;
use function Pest\Laravel\postJson;

beforeEach(function () {
    // Ensure we're working with JSON
    $this->withHeaders([
        'Accept' => 'application/json'
    ]);
});

// API Tests
it('successfully analyzes a valid HTML file via API', function () {
    // Create a test HTML file
    $htmlContent = getValidHtmlContent();
    $file = UploadedFile::fake()->createWithContent('test.html', $htmlContent);

    // Make the API request
    $response = postJson('/api/check', [
        'html_file' => $file
    ]);

    // Assert response structure and success
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'accessibility_score',
                'issues'
            ]
        ])
        ->assertJson([
            'success' => true,
            'message' => 'File has been analyzed successfully.'
        ]);

    // Assert data types
    $data = $response->json('data');
    expect($data['accessibility_score'])->toBeNumeric();
    expect($data['issues'])->toBeArray();
});

it('returns validation error when no file is provided', function () {
    $response = postJson('/api/check', []);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => [
                'html_file'
            ]
        ]);
});

it('returns error for invalid file type', function () {
    $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');

    $response = postJson('/api/check', [
        'html_file' => $file
    ]);

    $response->assertStatus(422);
});

// WCAGAnalyzer Tests
it('detects missing alt tags in images', function () {
    $htmlContent = '<!DOCTYPE html><html><body><img src="test.jpg"></body></html>';
    $analyzer = new WCAGAnalyzer($htmlContent);

    $results = $analyzer->analyze();

    expect($results)
        ->toHaveKey('accessibility_score')
        ->toHaveKey('issues');

    expect($results['issues'])
        ->toBeArray()
        ->not->toBeEmpty();

    // Check if there's an issue about missing alt
    $hasAltIssue = collect($results['issues'])->contains(function ($issue) {
        return str_contains(strtolower($issue['element']), 'img');
    });

    expect($hasAltIssue)->toBeTrue();
});

it('validates meta viewport tag', function () {
    $htmlWithoutViewport = '<!DOCTYPE html><html><head></head><body></body></html>';
    $analyzer = new WCAGAnalyzer($htmlWithoutViewport);

    $results = $analyzer->analyze();

    $hasViewportIssue = collect($results['issues'])->contains(function ($issue) {
        return str_contains(strtolower($issue['element']), 'meta');
    });

    expect($hasViewportIssue)->toBeTrue();
});

it('calculates correct accessibility score for perfect HTML', function () {
    $perfectHtml = getValidHtmlContent();
    $analyzer = new WCAGAnalyzer($perfectHtml);

    $results = $analyzer->analyze();

    expect($results['accessibility_score'])->toBeFloat()->toEqual(20.0);
});

it('detects missing form labels', function () {
    $htmlContent = '<!DOCTYPE html><html><body><form><input type="text" name="test"></form></body></html>';
    $analyzer = new WCAGAnalyzer($htmlContent);

    $results = $analyzer->analyze();

    $hasMissingFormLabels = collect($results['issues'])->contains(function ($issue) {
        return str_contains(strtolower($issue['element']), 'input');
    });

    expect($hasMissingFormLabels)->toBeTrue();
});

// Helper Functions
function getValidHtmlContent(): string
{
    return '<!DOCTYPE html>
        <html>
            <head>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <body>
                <h1>Test Page</h1>
                <img src="test.jpg" alt="Test image">
                <form>
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name">
                </form>
            </body>
        </html>';
}