<?php

use App\Exceptions\GeminiAISuggestionException;
use App\Services\WCAG\WCAGAnalyzer;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Illuminate\Http\UploadedFile;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->withHeaders([
        'Accept' => 'application/json'
    ]);
});

// API Tests
it('successfully analyzes a valid HTML file via API', function () {
    // Mock Gemini API response
    Gemini::fake([
        GenerateContentResponse::fake([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    [
                                        'element' => 'img',
                                        'issue' => 'Enhanced: Missing alt attribute description',
                                        'suggestion' => 'Add descriptive alt text to improve accessibility',
                                        'severity' => 'high'
                                    ]
                                ])
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $htmlContent = getValidHtmlContent();
    $file = UploadedFile::fake()->createWithContent('test.html', $htmlContent);

    $response = postJson('/api/check', [
        'html_file' => $file
    ]);

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

    $data = $response->json('data');
    expect($data['accessibility_score'])->toBeNumeric();
    expect($data['issues'])->toBeArray();
});

it('detects missing alt tags and enhances with AI suggestions', function () {
    Gemini::fake([
        GenerateContentResponse::fake([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    [
                                        'element' => 'img',
                                        'issue' => 'Enhanced: Missing alt attribute description',
                                        'suggestion' => 'Add descriptive alt text to improve accessibility',
                                        'severity' => 'high'
                                    ]
                                ])
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $htmlContent = '<!DOCTYPE html><html><body><img src="test.jpg"></body></html>';
    $analyzer = new WCAGAnalyzer($htmlContent);
    $results = $analyzer->analyze();

    expect($results)
        ->toHaveKey('accessibility_score')
        ->toHaveKey('issues');

    expect($results['issues'])
        ->toBeArray()
        ->not->toBeEmpty();

    // Check if there's an enhanced issue about missing alt
    $hasEnhancedAltIssue = collect($results['issues'])->contains(function ($issue) {
        return $issue['issue'] === 'Enhanced: Missing alt attribute description';
    });

    expect($hasEnhancedAltIssue)->toBeTrue();
});

it('handles Gemini API failure gracefully', function () {
    Gemini::fake([
        new GeminiAISuggestionException('API Error'),
    ]);

    $htmlContent = '<!DOCTYPE html><html><body><img src="test.jpg"></body></html>';
    $analyzer = new WCAGAnalyzer($htmlContent);

    expect(fn() => $analyzer->analyze())
        ->toThrow(GeminiAISuggestionException::class);
});

it('validates meta viewport tag', function () {
    Gemini::fake([
        GenerateContentResponse::fake([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    'element' => 'meta',
                                    'issue' => 'Missing viewport meta tag',
                                    'suggestion' => 'Ensure the document has a viewport meta tag for responsive design.',
                                    'severity' => 'medium'
                                ])
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $htmlWithoutViewport = '<!DOCTYPE html><html><head></head><body></body></html>';
    $analyzer = new WCAGAnalyzer($htmlWithoutViewport);
    $result = $analyzer->analyze();

    $hasViewportIssue = collect($result['issues'])->contains(function ($issue) {
        return $issue === 'meta';
    });

    expect($hasViewportIssue)->toBeTrue();
});

it('calculates correct accessibility score for perfect HTML', function () {
    Gemini::fake([
        GenerateContentResponse::fake([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([])
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $perfectHtml = getValidHtmlContent();
    $analyzer = new WCAGAnalyzer($perfectHtml);
    $results = $analyzer->analyze();

    expect($results['accessibility_score'])->toBeFloat()->toEqual(20.0);
});

it('detects missing form labels', function () {
    Gemini::fake([
        GenerateContentResponse::fake([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    [
                                        'element' => 'input',
                                        'issue' => 'Enhanced: Missing form label',
                                        'suggestion' => 'Add a descriptive label for better accessibility',
                                        'severity' => 'high'
                                    ]
                                ])
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $htmlContent = '<!DOCTYPE html><html><body><form><input type="text" name="test"></form></body></html>';
    $analyzer = new WCAGAnalyzer($htmlContent);
    $results = $analyzer->analyze();

    $hasMissingFormLabels = collect($results['issues'])->contains(function ($issue) {
        return str_contains(strtolower($issue['element']), 'input');
    });

    expect($hasMissingFormLabels)->toBeTrue();
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