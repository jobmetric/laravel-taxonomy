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
use JobMetric\Translation\Http\Requests\MultiTranslationTypeObjectRequest;
use JobMetric\Url\Http\Requests\UrlTypeObjectRequest;

class UpdateTaxonomyRequest extends FormRequest
{
    use MultiTranslationTypeObjectRequest, MetadataTypeObjectRequest, MediaTypeObjectRequest, UrlTypeObjectRequest;

    public string|null $type = null;
    public int|null $taxonomy_id = null;
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
     */
    public function rules(): array
    {
        if (!empty(request()->all())) {
            $this->data = request()->all();
        }

        $type = $this->type ?? $this->data['type'] ?? null;
        $parent_id = $this->data['parent_id'] ?? -1;

        if (is_null($this->taxonomy_id)) {
            $taxonomy_id = $this->route()->parameter('jm_taxonomy')->id;
        } else {
            $taxonomy_id = $this->taxonomy_id;
        }

        $rules = [
            'parent_id' => [
                'nullable',
                'integer',
                'sometimes',
                new TaxonomyExistRule($type)
            ],
            'ordering' => 'numeric|sometimes',
            'status' => 'boolean|sometimes',
        ];

        // check type
        TaxonomyType::checkType($type);

        $taxonomyType = TaxonomyType::type($type);

        $this->renderMultiTranslationFiled($rules, $taxonomyType->getTranslation(), Taxonomy::class, object_id: $taxonomy_id, parent_id: $parent_id, parent_where: ['type' => $type]);
        $this->renderMetadataFiled($rules, $taxonomyType->getMetadata());
        $this->renderMediaFiled($rules, $taxonomyType->hasBaseMedia(), $taxonomyType->getMedia());
        $this->renderUrlFiled($rules, $taxonomyType->hasUrl(), Taxonomy::class, $type, $taxonomy_id);

        if (!$taxonomyType->hasHierarchical()) {
            unset($rules['parent_id']);
        }

        return $rules;
    }

    /**
     * Set type for validation
     *
     * @param string $type
     * @return static
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set taxonomy id for validation
     *
     * @param int $taxonomy_id
     * @return static
     */
    public function setTaxonomyId(int $taxonomy_id): static
    {
        $this->taxonomy_id = $taxonomy_id;

        return $this;
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

        $type = $this->type ?? $this->data['type'] ?? null;

        // check type
        TaxonomyType::checkType($type);

        $taxonomyType = TaxonomyType::type($type);

        if (!$taxonomyType->hasHierarchical()) {
            $this->merge([
                'parent_id' => null,
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        if (!empty(request()->all())) {
            $this->data = request()->all();
        }

        $type = $this->type ?? $this->data['type'] ?? null;

        $params = [
            'parent_id' => trans('taxonomy::base.form.fields.parent.title'),
            'ordering' => trans('taxonomy::base.form.fields.ordering.title'),
            'status' => trans('package-core::base.components.boolean_status.label'),
        ];

        // check type
        TaxonomyType::checkType($type);

        $taxonomyType = TaxonomyType::type($type);

        $this->renderMultiTranslationAttribute($params, $taxonomyType->getTranslation());
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
