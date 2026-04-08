<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelFacilityBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise input dates to ISO (Y-m-d).
     * Accepts either *_iso from the new UI or legacy dd/mm/yyyy (and a couple of other separators).
     */
    protected function prepareForValidation(): void
    {
        // Accept ISO (preferred) or UI dd/mm/yyyy from older callers
        $fromRaw = $this->input('from_date_iso') ?? $this->input('from_date');
        $toRaw   = $this->input('to_date_iso')   ?? $this->input('to_date');

        $this->merge([
            'from_date' => $this->normaliseToIso($fromRaw),
            'to_date'   => $this->normaliseToIso($toRaw),
        ]);
    }

    private function normaliseToIso(?string $raw): ?string
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $value)->toDateString();
            } catch (\Throwable $e) {
                // try next format
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function rules(): array
    {
        return [
            // bookingId required for single-ID modes; relaxed for 'some_booking'
            'bookingId'    => [
                Rule::requiredIf(fn () => $this->input('cancel_value') !== 'some_booking'),
                'integer'
            ],

            'cancel_value' => ['required', Rule::in([
                'entire_booking',
                'some_booking',
                'recurrence_booking',
                'nonmandatory_booking',
            ])],

            // Batch for "some_booking"
            'some_booking_list'   => ['required_if:cancel_value,some_booking', 'array', 'min:1'],
            'some_booking_list.*' => ['integer'],

            // Range dates (unchanged)
            'from_date' => [
                'bail',
                'required_if:cancel_value,recurrence_booking',
                function ($attribute, $value, $fail) {
                    if ($this->input('cancel_value') !== 'recurrence_booking') {
                        return;
                    }

                    if ($value === null || trim((string) $value) === '') {
                        $fail('From date is required for recurrence cancellation.');
                        return;
                    }
                    try {
                        $from = \Carbon\Carbon::createFromFormat('Y-m-d', $this->input('from_date'));
                    } catch (\Throwable $e) {
                        $fail('Invalid from date.');
                        return;
                    }
                    $today = \Carbon\Carbon::today(config('app.timezone'));
                    if ($from->lt($today)) {
                        $fail('Past dates are not allowed.');
                    }
                },
            ],
            'to_date' => [
                'bail',
                'required_if:cancel_value,recurrence_booking',
                function ($attribute, $value, $fail) {
                    if ($this->input('cancel_value') !== 'recurrence_booking') {
                        return;
                    }

                    if ($value === null || trim((string) $value) === '') {
                        $fail('To date is required for recurrence cancellation.');
                        return;
                    }
                    try {
                        $to = \Carbon\Carbon::createFromFormat('Y-m-d', $this->input('to_date'));
                    } catch (\Throwable $e) {
                        $fail('Invalid to date.');
                        return;
                    }
                    $today = \Carbon\Carbon::today(config('app.timezone'));
                    if ($to->lt($today)) {
                        $fail('Past dates are not allowed.');
                        return;
                    }
                    try {
                        $from = \Carbon\Carbon::createFromFormat('Y-m-d', $this->input('from_date'));
                        if ($to->lt($from)) {
                            $fail('To date must be on or after From date.');
                        }
                    } catch (\Throwable $e) {
                    }
                },
            ],
        ];
    }
}
