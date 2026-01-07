<?php

namespace App\Http\Requests\Api;

use App\Enums\CourseSortField;
use App\Enums\CourseType;
use App\Enums\SortOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseListRequest extends FormRequest
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
            'type' => ['nullable', 'string', Rule::enum(CourseType::class)],
            'sort' => ['nullable', 'string', Rule::enum(CourseSortField::class)],
            'order' => ['nullable', 'string', Rule::enum(SortOrder::class)],
        ];
    }
}
