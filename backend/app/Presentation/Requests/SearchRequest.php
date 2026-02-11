<?php

declare(strict_types=1);

namespace App\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:people,films',
            'query' => 'required|string|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please select a search type (people or films).',
            'type.in' => 'Search type must be either "people" or "films".',
            'query.required' => 'Please enter a search term.',
            'query.min' => 'Search term must be at least 1 character.',
            'query.max' => 'Search term cannot exceed 100 characters.',
        ];
    }
}
