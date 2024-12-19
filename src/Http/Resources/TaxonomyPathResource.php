<?php

namespace JobMetric\Taxonomy\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JobMetric\Taxonomy\Models\Taxonomy;

/**
 * @property string $type
 * @property int $taxonomy_id
 * @property int $path_id
 * @property int $level
 *
 * @property Taxonomy $taxonomy
 * @property Taxonomy $path
 */
class TaxonomyPathResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'taxonomy_id' => $this->taxonomy_id,
            'path_id' => $this->path_id,
            'level' => $this->level,

            'taxonomy' => $this->whenLoaded('taxonomy', function () {
                return new TaxonomyResource($this->taxonomy);
            }),

            'path' => $this->whenLoaded('path', function () {
                return new TaxonomyResource($this->taxonomy);
            }),
        ];
    }
}
