<?php

namespace App\Http\Requests\RestaurantAdmin;

use App\Models\Resource;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class UpdateFloorPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * This endpoint is only ever called via the floor plan editor's fetch()
     * call (which sends Accept: application/json), so validation failures
     * should always return a JSON 422 rather than a redirect.
     */
    protected function failedValidation(ValidatorContract $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }

    public function rules(): array
    {
        return [
            'positions' => ['required', 'array'],
            'positions.*.resource_id' => ['required', 'integer', 'exists:resources,id'],
            'positions.*.x' => ['required', 'numeric', 'min:0', 'max:100'],
            'positions.*.y' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $restaurant = $this->attributes->get('restaurant');
            $resourceIds = collect($this->input('positions', []))->pluck('resource_id')->filter();

            if ($resourceIds->isEmpty()) {
                return;
            }

            $ownedCount = Resource::query()
                ->where('restaurant_id', $restaurant->id)
                ->whereIn('id', $resourceIds)
                ->count();

            if ($ownedCount !== $resourceIds->unique()->count()) {
                $validator->errors()->add('positions', 'One or more resources do not belong to this restaurant.');
            }
        });
    }
}
