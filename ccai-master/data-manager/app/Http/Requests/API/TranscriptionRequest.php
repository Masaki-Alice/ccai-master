<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class TranscriptionRequest extends FormRequest
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
            'gs_link' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'gs_link.required' => 'Please enter the GCS link to the audio e.g gs://path/to/file.mp3',
        ];
    }
}
