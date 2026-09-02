<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class JobApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:applied,screening,interview,offer,rejected,accepted'],
            'applied_date' => ['required', 'date'],
            'source' => ['nullable', 'string', 'max:100'],
            'job_url' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'salary_range_min' => ['nullable', 'numeric', 'min:0'],
            'salary_range_max' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Custom validation messages with clear, actionable human phrasing.
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'position.required' => 'Posisi pekerjaan belum diisi.',
            'status.required' => 'Status lamaran wajib dipilih.',
            'status.in' => 'Status lamaran tidak valid.',
            'applied_date.required' => 'Tanggal lamar belum diisi, lengkapi dulu sebelum disimpan.',
            'applied_date.date' => 'Format tanggal lamaran tidak valid.',
            'job_url.max' => 'Tautan lowongan terlalu panjang.',
            'salary_range_min.numeric' => 'Estimasi gaji minimum harus berupa angka.',
            'salary_range_max.numeric' => 'Estimasi gaji maksimum harus berupa angka.',
        ];
    }

    /**
     * Return JSON on validation failure for API requests.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }
}
