<?php

namespace JobMetric\Taxonomy\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Media\Http\Requests\MediaTypeObjectRequest;
use JobMetric\Media\ServiceType\Media;
use JobMetric\Metadata\Http\Requests\MetadataTypeObjectRequest;
use JobMetric\Taxonomy\Facades\TaxonomyType;
use JobMetric\Taxonomy\Models\Taxonomy;
use JobMetric\Taxonomy\Rules\TaxonomyExistRule;
use JobMetric\Translation\Http\Requests\TranslationTypeObjectRequest;
use JobMetric\Url\Http\Requests\UrlTypeObjectRequest;
use Throwable;

class StoreTaxonomyRequest extends FormRequest
{
    use TranslationTypeObjectRequest, MetadataTypeObjectRequest, MediaTypeObjectRequest, UrlTypeObjectRequest;

    public array $data = [];

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
     * @throws Throwable
     */
    public function rules(): array
    {
        if (!empty(request()->all())) {
            $this->data = request()->all();
        }

        $type = $this->data['type'] ?? null;
        $parent_id = $this->data['parent_id'] ?? -1;

        $rules = [
            'type' => 'required|string|in:' . implode(',', TaxonomyType::getTypes()),
            'parent_id' => [
                'nullable',
                'integer',
                new TaxonomyExistRule($type ?? null)
            ],
            'ordering' => 'numeric|sometimes',
            'status' => 'boolean|sometimes',
        ];

        // check type
        TaxonomyType::checkType($type);

        $taxonomyType = TaxonomyType::type($type);

        $this->renderTranslationFiled($rules, $this->data, $taxonomyType->getTranslation(), Taxonomy::class, parent_id: $parent_id, parent_where: ['type' => $type]);
        $this->renderMetadataFiled($rules, $taxonomyType->getMetadata());
        $this->renderMediaFiled($rules, $taxonomyType->hasBaseMedia(), $taxonomyType->getMedia());
        $this->renderUrlFiled($rules, $taxonomyType->hasUrl(), Taxonomy::class, $type);

        if (!$taxonomyType->hasHierarchical()) {
            unset($rules['parent_id']);
        }

        return $rules;
    }

    /**
     * Set data for validation
     *
     * @param array $data
     * @return static
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if (!empty(request()->all())) {
            $this->data = request()->all();
        }

        $type = $this->data['type'] ?? null;

        // check type
        TaxonomyType::checkType($type);

        $taxonomyType = TaxonomyType::type($type);

        if (!$taxonomyType->hasHierarchical()) {
            $this->merge([
                'parent_id' => null,
            ]);
        }

        $this->merge([
            'ordering' => $this->ordering ?? 0,
            'status' => $this->status ?? true,
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        $type = $this->data['type'] ?? null;

        // check type
        TaxonomyType::checkType($type);

        $taxonomyType = TaxonomyType::type($type);

        $params = [
            'parent_id' => trans('taxonomy::base.form.fields.parent.title'),
            'ordering' => trans('taxonomy::base.form.fields.ordering.title'),
            'status' => trans('package-core::base.components.boolean_status.label'),
        ];

        $this->renderTranslationAttribute($params, $this->data, $taxonomyType->getTranslation());
        $this->renderMetadataAttribute($params, $taxonomyType->getMetadata());
        $this->renderUrlAttribute($params, $taxonomyType->hasUrl());

        if ($taxonomyType->hasBaseMedia()) {
            $params['media.base'] = trans('taxonomy::base.form.media.base.title');
        }

        foreach ($taxonomyType->getMedia() as $item) {
            /**
             * @var Media $item
             */
            $collection = $item->getCollection();

            $params["media.$collection"] = trans('taxonomy::base.form.media.' . $collection . '.title');
        }

        return $params;
    }
}
