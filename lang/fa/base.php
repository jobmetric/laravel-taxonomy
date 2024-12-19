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
        "errors" => "خطای اعتبارسنجی رخ داده است.",
        "type_required" => "فیلد نوع الزامی است.",
        "slug_in_type" => "نام مستعار در نوع (:type) قبلا وجود دارد.",
        "taxonomy_exist" => "طبقه بندی در نوع (:type) وجود ندارد.",
        "taxonomies_exists" => "شناسه های طبقه بندی های زیر وجود ندارند: :ids .",
        "taxonomy_not_found" => "طبقه بندی یافت نشد.",
    ],

    "messages" => [
        "created" => "طبقه بندی با موفقیت ایجاد شد.",
        "updated" => "طبقه بندی با موفقیت به روز شد.",
        "deleted" => "طبقه بندی با موفقیت حذف شد.",
        "attached" => "طبقه بندی با موفقیت متصل شد.",
        "multi_attached" => "طبقه بندی ها با موفقیت متصل شدند.",
        "used_in" => "طبقه بندی در ':count' مکان استفاده شده است.",
        "deleted_items" => "{1} یک مورد :taxonomy با موفقیت حذف شد.|[2,*] :count مورد :taxonomy با موفقیت حذف شدند.",
        "set_translation" => "ترجمه با موفقیت ذخیره شد.",
        "status" => [
            "enable" => "{1} یک مورد :taxonomy فعال شد.|[2,*] :count مورد :taxonomy فعال شدند.",
            "disable" => "{1} یک مورد :taxonomy غیرفعال شد.|[2,*] :count مورد :taxonomy غیرفعال شدند.",
        ],
    ],

    "exceptions" => [
        "taxonomy_not_found" => "طبقه بندی با شماره :number یافت نشد.",
        "taxonomy_is_disable" => "طبقه بندی با شماره :number غیرفعال است.",
        "taxonomy_used" => "طبقه بندی ':name' در مکان های دیگر استفاده شده است.",
        "cannot_make_parent_subset_own_child" => "نمی توان پدر زیرمجموعه خود را ایجاد کرد.",
        "model_taxonomy_contract_not_found" => "مدل ':model' رابط 'JobMetric\Taxonomy\Contracts\TaxonomyContract' را پیاده سازی نمی کند!",
        "taxonomy_collection_not_in_taxonomy_allow_types" => "مجموعه طبقه بندی :collection در انواع طبقه بندی مجاز نیست.",
        "invalid_taxonomy_type_in_collection" => "نوع مجموعه وارد شده ':collection' است، و ':collection' همچنین نوع طبقه بندی ':collectionType' را قبول می کند، اما نوع طبقه بندی داده شده ':baseType' است و قابل قبول نیست.",
        "taxonomy_type_not_match" => "نوع طبقه بندی ':type' مطابقت ندارد.",
    ],

    "list" => [
        "filters" => [
            "name" => [
                "title" => "نام",
                "placeholder" => "نام را وارد کنید.",
            ],
            "status" => [
                "title" => "وضعیت",
            ],
        ],
    ],

    "form" => [
        "create" => [
            "title" => "ایجاد :type",
        ],
        "edit" => [
            "title" => "ویرایش :type :name",
        ],
        "media" => [
            "base" => [
                "title" => "عکس اصلی",
            ],
            "gallery" => [
                "title" => "تصاویر بیشتر",
            ],
        ],
        "fields" => [
            "parent" => [
                "title" => "والد"
            ],
            "ordering" => [
                "title" => "ترتیب",
                "placeholder" => "ترتیب را وارد کنید.",
            ],
        ],
    ]

];
