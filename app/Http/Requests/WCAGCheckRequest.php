<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WCAGCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'html_file' => 'required|mimes:html,htm|max:2048',
        ];
    }
}
