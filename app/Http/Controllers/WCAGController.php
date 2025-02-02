<?php

namespace App\Http\Controllers;

use App\Http\Requests\WCAGCheckRequest;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Services\WCAG\WCAGAnalyzer;

class WCAGController extends Controller
{
    public function __invoke(WCAGCheckRequest $request): ApiErrorResponse|ApiSuccessResponse
    {
        try {
            $file = $request->file('html_file');
            $htmlContent = file_get_contents($file->getPathname());

            $analyzer = new WCAGAnalyzer($htmlContent);
            $results = $analyzer->analyze();

            return new ApiSuccessResponse(
                data: $results,
                message: "File has been analyzed successfully."
            );
        } catch (\Exception $e) {
            return new ApiErrorResponse(message: $e->getMessage());
        }
    }
}
