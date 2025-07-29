<?php

namespace JobMetric\Taxonomy\Http\Requests;

use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;
use JobMetric\Taxonomy\Facades\TaxonomyType;
use JobMetric\Taxonomy\Models\Taxonomy;
use JobMetric\Taxonomy\Rules\TaxonomyExistRule;
use JobMetric\Translation\Http\Requests\TranslationTypeObjectRequest;

class SetTranslationRequest extends FormRequest
{
    use TranslationTypeObjectRequest;

    public string|null $type = null;

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
     * @return array<string, ValidationRule|array|string>
     * @throws Exception
     */
    public function rules(): array
    {
        $form_data = request()->all();
        $type = $this->route()->parameters()['type'];

        $locale = $form_data['locale'] ?? null;
        $id = $form_data['translatable_id'] ?? null;

        if (is_null($locale)) {
            throw new InvalidArgumentException('Locale is required', 400);
        }

        if (is_null($id)) {
            throw new InvalidArgumentException('Translatable ID is required', 400);
        }

        /**
         * @var Taxonomy $taxonomy
         */
        $taxonomy = Taxonomy::query()->findOrFail($id);

        $rules = [
            'locale' => ['required', 'string'],
            'translatable_id' => ['required', 'integer', new TaxonomyExistRule($type)],
        ];

        TaxonomyType::ensureTypeExists($type);

        $taxonomyType = TaxonomyType::type($type);

        $this->renderTranslationFiled($rules, $form_data, $taxonomyType->getTranslation(), Taxonomy::class, object_id: $id, parent_id: $taxonomy->parent_id, parent_where: ['type' => $type]);

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        $form_data = request()->all();
        $type = $this->route()->parameters()['type'];

        $taxonomyType = TaxonomyType::type($type);

        $params = [];
        $this->renderTranslationAttribute($params, $form_data, $taxonomyType->getTranslation());

        return $params;
    }
}
