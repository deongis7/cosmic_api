<?php

namespace App\Http\Requests;

use Pearl\RequestValidate\RequestAbstract;

class todoRequest extends RequestAbstract
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
			'name' => 'required|max:30',
			'activity' => 'required|max:30',
			'description' => 'required'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus Diisi!',
            'activity.required' => 'Aktifitas harus Diisi!',
            'description.required' => 'Deskripsi harus Diisi!',
			 'name.max' => 'Nama harus Maximal 30 Karakter!',
            'activity.max' => 'Aktifitas harus Maximal 30 Karakter!',
        ];
    }
}
