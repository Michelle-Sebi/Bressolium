<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExploreActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
