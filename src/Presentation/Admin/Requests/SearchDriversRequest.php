<?php

declare(strict_types=1);

namespace Src\Presentation\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Domain\Drivers\Enums\DriverStatus;

/**
 * Validates GET /drivers: optional free-text search, status filter, page size.
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
            'status' => ['sometimes', Rule::enum(DriverStatus::class)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function searchTerm(): ?string
    {
        return $this->query('search');
    }

    public function statusFilter(): ?DriverStatus
    {
        $status = $this->query('status');

        return $status !== null ? DriverStatus::from($status) : null;
    }

    public function perPage(): int
    {
        return (int) $this->query('per_page', 10);
    }
}
