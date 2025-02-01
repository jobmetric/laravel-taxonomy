<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Taxonomy Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Taxonomy for
    | various messages that we need to display to the user.
    |
    */

    "validation" => [
        "type_required" => "The type field is required.",
        "slug_in_type" => "The slug already exists in type (:type).",
        "taxonomy_exist" => "The taxonomy does not exist in type (:type).",
        "taxonomies_exists" => "The following taxonomy IDs do not exist: :ids .",
        "taxonomy_not_found" => "The taxonomy not found.",
    ],

    "messages" => [
        "created" => "The taxonomy was created successfully.",
        "updated" => "The taxonomy was updated successfully.",
        "deleted" => "The taxonomy was deleted successfully.",
        "attached" => "The taxonomy was attached successfully.",
        "multi_attached" => "The taxonomies were attached successfully.",
        "used_in" => "The taxonomy used in ':count' places.",
        "deleted_items" => "{1} One item :taxonomy was deleted successfully.|[2,*] The :count items :taxonomy were deleted successfully.",
        "set_translation" => "The translation was saved successfully.",
        "status" => [
            "enable" => "{1} One item :taxonomy was enabled successfully.|[2,*] The :count items :taxonomy were enabled successfully.",
            "disable" => "{1} One item :taxonomy was disabled successfully.|[2,*] The :count items :taxonomy were disabled successfully.",
        ],
    ],

    "exceptions" => [
        "taxonomy_not_found" => "The taxonomy with number :number was not found.",
        "taxonomy_is_disable" => "The taxonomy with number :number is disabled.",
        "taxonomy_used" => "The taxonomy ':name' is used in other places.",
        "cannot_make_parent_subset_own_child" => "Cannot make parent subset own child.",
        "model_taxonomy_contract_not_found" => "Model ':model' not implements 'JobMetric\Taxonomy\Contracts\TaxonomyContract' interface!",
        "taxonomy_collection_not_in_taxonomy_allow_types" => "The taxonomy collection :collection not in taxonomy allow types.",
        "invalid_taxonomy_type_in_collection" => "The type of collection entered is ':collection', and the ':collection' also accepts ':collectionType' taxonomy type, but the taxonomy type you have given is ':baseType' and it is not acceptable.",
        "taxonomy_type_not_match" => "The taxonomy type ':type' does not match any taxonomy types registered in your application via listeners.",
    ],

    "list" => [
        "filters" => [
            "name" => [
                "title" => "Name",
                "placeholder" => "Enter name.",
            ],
            "status" => [
                "title" => "Status",
            ],
        ],
    ],

    "form" => [
        "create" => [
            "title" => "Create :type",
        ],
        "edit" => [
            "title" => "Edit :type with ID :number",
        ],
        "media" => [
            "base" => [
                "title" => "Original Image",
            ],
            "gallery" => [
                "title" => "More Images",
            ],
        ],
        "fields" => [
            "parent" => [
                "title" => "Parent",
            ],
            "ordering" => [
                "title" => "Ordering",
                "placeholder" => "Enter ordering.",
            ],
        ],
    ]

];
