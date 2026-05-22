<?php

declare(strict_types=1);

namespace Src\Presentation\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates GET /drivers: an optional free-text search and a bounded page size.
 */
final class SearchDriversRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function searchTerm(): ?string
    {
        return $this->query('search');
    }

    public function perPage(): int
    {
        return (int) $this->query('per_page', 10);
    }
}
