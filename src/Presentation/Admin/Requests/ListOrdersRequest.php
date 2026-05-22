<?php

declare(strict_types=1);

namespace Src\Presentation\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Domain\Orders\Enums\OrderStatus;

/**
 * Validates GET /orders: a filter ("active" | "all" | a status value) and
 * a bounded page size.
 */
final class ListOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowed = array_merge(
            ['active', 'all'],
            array_column(OrderStatus::cases(), 'value'),
        );

        return [
            'filter' => ['sometimes', Rule::in($allowed)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function filterValue(): string
    {
        return (string) $this->query('filter', 'active');
    }

    public function perPage(): int
    {
        return (int) $this->query('per_page', 10);
    }
}
