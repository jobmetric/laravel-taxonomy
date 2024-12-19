<?php

namespace JobMetric\Taxonomy\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use JobMetric\Language\Facades\Language;
use JobMetric\Metadata\ServiceType\Metadata as MetadataServiceType;
use JobMetric\Panelio\Facades\Breadcrumb;
use JobMetric\Panelio\Facades\Button;
use JobMetric\Panelio\Facades\Datatable;
use JobMetric\Panelio\Http\Controllers\Controller;
use JobMetric\Panelio\Http\Requests\ExportActionListRequest;
use JobMetric\Panelio\Http\Requests\ImportActionListRequest;
use JobMetric\Taxonomy\Facades\Taxonomy;
use JobMetric\Taxonomy\Facades\TaxonomyType;
use JobMetric\Taxonomy\Http\Requests\SetTranslationRequest;
use JobMetric\Taxonomy\Http\Requests\StoreTaxonomyRequest;
use JobMetric\Taxonomy\Http\Requests\UpdateTaxonomyRequest;
use JobMetric\Taxonomy\Http\Resources\TaxonomyResource;
use JobMetric\Taxonomy\Models\Taxonomy as TaxonomyModel;
use Throwable;

class TaxonomyController extends Controller
{
    private array $route;

    public function __construct()
    {
        if (request()->route()) {
            $parameters = request()->route()->parameters();

            $this->route = [
                'index' => route('taxonomy.{type}.index', $parameters),
                'create' => route('taxonomy.{type}.create', $parameters),
                'store' => route('taxonomy.{type}.store', $parameters),
                'options' => route('taxonomy.options', $parameters),
                'import' => route('taxonomy.import', $parameters),
                'export' => route('taxonomy.export', $parameters),
                'set_translation' => route('taxonomy.set-translation', $parameters),
            ];
        }
    }

    /**
     * Display a listing of the taxonomy.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return View|JsonResponse
     * @throws Throwable
     */
    public function index(string $panel, string $section, string $type): View|JsonResponse
    {
        if (request()->ajax()) {
            $query = Taxonomy::query($type, with: ['translations', 'files', 'metas', 'taxonomyRelations']);

            return Datatable::of($query, resource_class: TaxonomyResource::class);
        }

        $serviceType = TaxonomyType::type($type);

        $data['label'] = $serviceType->getLabel();
        $data['description'] = $serviceType->getDescription();
        $data['translation'] = $serviceType->getTranslation();
        $data['media'] = $serviceType->getMedia();
        $data['metadata'] = $serviceType->getMetadata();
        $data['hasUrl'] = $serviceType->hasUrl();
        $data['hasHierarchical'] = $serviceType->hasHierarchical();
        $data['hasBaseMedia'] = $serviceType->hasBaseMedia();
        $data['hasShowDescriptionInList'] = $serviceType->hasShowDescriptionInList();
        $data['hasRemoveFilterInList'] = $serviceType->hasRemoveFilterInList();
        $hasChangeStatusInList = $serviceType->hasChangeStatusInList();
        $hasImportInList = $serviceType->hasImportInList();
        $hasExportInList = $serviceType->hasExportInList();

        DomiTitle($data['label']);

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['label']);

        // add button
        Button::add($this->route['create']);
        Button::delete();

        // Check show button change status
        if ($hasChangeStatusInList) {
            Button::status();
        }

        // Check show button import
        if ($hasImportInList) {
            Button::import();
        }

        // Check show button export
        if ($hasExportInList) {
            Button::export();
        }

        DomiLocalize('taxonomy', [
            'route' => $this->route['index'],
            'has_base_media' => $data['hasBaseMedia'],
            'metadata' => $data['metadata']->map(function (MetadataServiceType $item) {
                return [
                    'label' => trans($item->customField->label),
                    'info' => trans($item->customField->info),
                ];
            }),
        ]);

        DomiPlugins('jquery.form');

        DomiAddModal('translation', '', view('translation::modals.translation-list', [
            'action' => $this->route['set_translation'],
            'items' => $data['translation']
        ]), options: [
            'size' => 'lg'
        ]);

        DomiScript('assets/vendor/taxonomy/js/list.js');

        $data['type'] = $type;

        $data['route'] = $this->route['options'];
        $data['import_action'] = $this->route['import'];
        $data['export_action'] = $this->route['export'];

        return view('taxonomy::list', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return View
     */
    public function create(string $panel, string $section, string $type): View
    {
        $data['mode'] = 'create';

        $serviceType = TaxonomyType::type($type);

        $data['label'] = $serviceType->getLabel();
        $data['description'] = $serviceType->getDescription();
        $data['translation'] = $serviceType->getTranslation();
        $data['media'] = $serviceType->getMedia();
        $data['metadata'] = $serviceType->getMetadata();
        $data['hasUrl'] = $serviceType->hasUrl();
        $data['hasHierarchical'] = $serviceType->hasHierarchical();
        $data['hasBaseMedia'] = $serviceType->hasBaseMedia();

        DomiTitle(trans('taxonomy::base.form.create.title', [
            'type' => $data['label']
        ]));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['label'], $this->route['index']);
        Breadcrumb::add(trans('taxonomy::base.form.create.title', [
            'type' => $data['label']
        ]));

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        DomiScript('assets/vendor/taxonomy/js/form.js');

        $data['type'] = $type;
        $data['action'] = $this->route['store'];

        $data['taxonomies'] = Taxonomy::all($type);

        return view('taxonomy::form', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTaxonomyRequest $request
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StoreTaxonomyRequest $request, string $panel, string $section, string $type): RedirectResponse
    {
        $form_data = $request->all();

        $taxonomy = Taxonomy::store($request->validated());

        if ($taxonomy['ok']) {
            $this->alert($taxonomy['message']);

            if ($form_data['save'] == 'save.new') {
                return back();
            }

            if ($form_data['save'] == 'save.close') {
                return redirect()->to($this->route['index']);
            }

            // btn save
            return redirect()->route('taxonomy.{type}.edit', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
                'jm_taxonomy' => $taxonomy['data']->id
            ]);
        }

        $this->alert($taxonomy['message'], 'danger');

        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param TaxonomyModel $taxonomy
     *
     * @return View
     */
    public function edit(string $panel, string $section, string $type, TaxonomyModel $taxonomy): View
    {
        $taxonomy->load(['files', 'metas', 'translations']);

        $data['mode'] = 'edit';

        $serviceType = TaxonomyType::type($type);

        $data['label'] = $serviceType->getLabel();
        $data['description'] = $serviceType->getDescription();
        $data['translation'] = $serviceType->getTranslation();
        $data['media'] = $serviceType->getMedia();
        $data['metadata'] = $serviceType->getMetadata();
        $data['hasUrl'] = $serviceType->hasUrl();
        $data['hasHierarchical'] = $serviceType->hasHierarchical();
        $data['hasBaseMedia'] = $serviceType->hasBaseMedia();

        DomiTitle(trans('taxonomy::base.form.edit.title', [
            'type' => $data['label'],
            'name' => $taxonomy->id
        ]));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['label'], $this->route['index']);
        Breadcrumb::add(trans('taxonomy::base.form.edit.title', [
            'type' => $data['label'],
            'name' => $taxonomy->id
        ]));

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        DomiScript('assets/vendor/taxonomy/js/form.js');

        $data['type'] = $type;
        $data['action'] = route('taxonomy.{type}.update', [
            'panel' => $panel,
            'section' => $section,
            'type' => $type,
            'jm_taxonomy' => $taxonomy->id
        ]);

        $data['languages'] = Language::all();
        $data['taxonomies'] = Taxonomy::all($type);

        $data['taxonomy'] = $taxonomy;
        $data['slug'] = $taxonomy->urlByCollection($type, true);
        $data['translation_edit_values'] = translationResourceData($taxonomy->translations);
        $data['media_values'] = $taxonomy->getMediaDataForObject();
        $data['meta_values'] = $taxonomy->getMetaDataForObject();

        return view('taxonomy::form', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTaxonomyRequest $request
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param TaxonomyModel $taxonomy
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateTaxonomyRequest $request, string $panel, string $section, string $type, TaxonomyModel $taxonomy): RedirectResponse
    {
        $form_data = $request->all();

        $taxonomy = Taxonomy::update($taxonomy->id, $request->validated());

        if ($taxonomy['ok']) {
            $this->alert($taxonomy['message']);

            if ($form_data['save'] == 'save.new') {
                return redirect()->to($this->route['create']);
            }

            if ($form_data['save'] == 'save.close') {
                return redirect()->to($this->route['index']);
            }

            // btn save
            return redirect()->route('taxonomy.{type}.edit', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
                'jm_taxonomy' => $taxonomy['data']->id
            ]);
        }

        $this->alert($taxonomy['message'], 'danger');

        return back();
    }

    /**
     * Delete the specified resource from storage.
     *
     * @param array $ids
     * @param mixed $params
     * @param string|null $alert
     * @param string|null $danger
     *
     * @return bool
     * @throws Throwable
     */
    public function deletes(array $ids, mixed $params, string &$alert = null, string &$danger = null): bool
    {
        $type = $params[2] ?? null;

        $serviceType = TaxonomyType::type($type);

        try {
            foreach ($ids as $id) {
                Taxonomy::delete($id);
            }

            $alert = trans_choice('taxonomy::base.messages.deleted_items', count($ids), [
                'taxonomy' => $serviceType->getLabel()
            ]);

            return true;
        } catch (Throwable $e) {
            $danger = $e->getMessage();

            return false;
        }
    }

    /**
     * Change Status the specified resource from storage.
     *
     * @param array $ids
     * @param bool $value
     * @param mixed $params
     * @param string|null $alert
     * @param string|null $danger
     *
     * @return bool
     * @throws Throwable
     */
    public function changeStatus(array $ids, bool $value, mixed $params, string &$alert = null, string &$danger = null): bool
    {
        $type = $params[2] ?? null;

        $serviceType = TaxonomyType::type($type);

        try {
            foreach ($ids as $id) {
                Taxonomy::update($id, ['status' => $value]);
            }

            if ($value) {
                $alert = trans_choice('taxonomy::base.messages.status.enable', count($ids), [
                    'taxonomy' => $serviceType->getLabel()
                ]);
            } else {
                $alert = trans_choice('taxonomy::base.messages.status.disable', count($ids), [
                    'taxonomy' => $serviceType->getLabel()
                ]);
            }

            return true;
        } catch (Throwable $e) {
            $danger = $e->getMessage();

            return false;
        }
    }

    /**
     * Import data
     */
    public function import(ImportActionListRequest $request, string $panel, string $section, string $type)
    {
        //
    }

    /**
     * Export data
     */
    public function export(ExportActionListRequest $request, string $panel, string $section, string $type)
    {
        $export_type = $request->type;

        $filePath = public_path('favicon.ico');
        $fileName = 'favicon.ico';

        return response()->download($filePath, $fileName, [
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }

    /**
     * Set Translation in list
     *
     * @param SetTranslationRequest $request
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function setTranslation(SetTranslationRequest $request): JsonResponse
    {
        try {
            return $this->response(
                Taxonomy::setTranslation($request->validated())
            );
        } catch (Throwable $exception) {
            return $this->response(message: $exception->getMessage(), status: $exception->getCode());
        }
    }
}
