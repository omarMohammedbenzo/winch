<?php

declare(strict_types=1);

namespace Src\Presentation\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Domain\Orders\Enums\OrderStatus;

/**
 * Validates the query string for GET /drivers/{driver}/orders
 */
final class ListDriverOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(OrderStatus::class)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function statusFilter(): ?OrderStatus
    {
        $status = $this->query('status');

        return $status !== null ? OrderStatus::from($status) : null;
    }

    public function perPage(): int
    {
        return (int) $this->query('per_page', 15);
    }
}
