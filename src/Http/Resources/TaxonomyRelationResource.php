<?php

namespace JobMetric\Taxonomy\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JobMetric\Taxonomy\Models\Taxonomy;

/**
 * @property mixed $taxonomy_id
 * @property mixed $taxonomizable_id
 * @property mixed $taxonomizable_type
 * @property mixed $collection
 * @property mixed $created_at
 *
 * @property Taxonomy $taxonomy
 * @property mixed $taxonomizable
 * @property mixed $taxonomizable_resource
 */
class TaxonomyRelationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'taxonomy_id' => $this->taxonomy_id,
            'taxonomizable_id' => $this->taxonomizable_id,
            'taxonomizable_type' => $this->taxonomizable_type,
            'collection' => $this->collection,
            'created_at' => $this->created_at,

            'taxonomy' => $this->whenLoaded('taxonomy', function () {
                return new TaxonomyResource($this->taxonomy);
            }),

            'taxonomizable' => $this?->taxonomizable_resource
        ];
    }
}
