<?php

namespace App\Http\Requests;

use App\Enums\StatisticsPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StatisticsRequest extends FormRequest
{
    private const MAX_RANGE_DAYS = 366;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'period' => ['nullable', Rule::enum(StatisticsPeriod::class)],
            'from' => ['nullable', 'required_if:period,custom', 'date'],
            'to' => ['nullable', 'required_if:period,custom', 'date', 'after_or_equal:from'],
            'shop_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['from', 'to']) || ! $this->filled(['from', 'to'])) {
                    return;
                }

                $days = CarbonImmutable::parse($this->string('from')->toString())
                    ->diffInDays(CarbonImmutable::parse($this->string('to')->toString()));

                if ($days > self::MAX_RANGE_DAYS) {
                    $validator->errors()->add('to', 'Oraliq 1 yildan oshmasligi kerak.');
                }
            },
        ];
    }
}
