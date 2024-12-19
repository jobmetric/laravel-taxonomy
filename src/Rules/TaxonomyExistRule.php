<?php

namespace JobMetric\Taxonomy\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use JobMetric\Taxonomy\Models\Taxonomy;

class TaxonomyExistRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string|null $type = null
    )
    {
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value == 0 || empty($value)) {
            return;
        }
        $value = collect($value);
        $taxonomies = Taxonomy::query()->whereIn('id', $value);

        // it's just used when we want store the taxonomy
        $this->type && $taxonomies->where('type', $this->type);
        $diff = $value->diff(
            $taxonomies->pluck('id')
        );

        //means that we have $values (ids) that aren't exist in taxonomies table
        if ($diff->isNotEmpty()) {
            if ($this->type) {
                $fail(__('taxonomy::base.validation.taxonomy_exist', ['type' => $this->type]));
            } else {
                $fail(__(
                    'taxonomy::base.validation.taxonomies_exists',
                    [
                        'ids' => $diff->implode(", ")
                    ]
                ));
            }
        }
    }
}
