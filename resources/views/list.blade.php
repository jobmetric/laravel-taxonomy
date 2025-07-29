@extends('panelio::layout.layout')

@section('body')
    <x-list-view name="{{ $label }}" action="{{ $route }}" export-action="{{ $export_action }}" import-action="{{ $import_action }}">
        @if($hasRemoveFilterInList)
            <x-slot name="filter">
                <div class="col-md-3">
                    <div class="mb-5">
                        <label class="form-label">{{ trans('taxonomy::base.list.filters.name.title') }}</label>
                        <input type="text" name="translation[name]" class="form-control filter-list" id="filter-name" placeholder="{{ trans('taxonomy::base.list.filters.name.placeholder') }}" autocomplete="off">
                    </div>
                </div>
                @foreach($metadata as $meta)
                    @php
                        /**
                         * @var \JobMetric\Metadata\Typeify\Metadata $meta
                         */
                    @endphp
                    @if($meta->hasFilter)
                        <div class="col-md-3">
                            {!! $meta->customField->render(showInfo: false, class: 'filter-list filter-metadata', classParent: 'mb-5') !!}
                        </div>
                    @endif
                @endforeach
            </x-slot>
        @endif

        <thead>
            <tr>
                <th width="1%"></th>
                <th width="1%">
                    <div class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="1" id="check-all"/>
                        <label class="form-check-label ms-0" for="check-all"></label>
                    </div>
                </th>
                <th width="6%" class="text-center text-gray-800">{{ trans('package-core::base.list.columns.image') }}</th>
                <th width="42%" class="text-gray-800 auto-width-content">{{ trans('package-core::base.list.columns.name') }}</th>
                <th width="10%" class="text-center text-gray-800">{{ trans('package-core::base.list.columns.status') }}</th>
                <th width="10%" class="text-center text-gray-800">{{ trans('package-core::base.list.columns.ordering') }}</th>
                <th width="15%" class="text-center text-gray-800">{{ trans('package-core::base.list.columns.translations') }}</th>
                <th width="15%" class="text-center text-gray-800">{{ trans('package-core::base.list.columns.action') }}</th>
            </tr>
        </thead>
    </x-list-view>

    @if($hasShowDescriptionInList)
        <div class="mt-10">
            <h6>{{ $description ?? '' }}</h6>
        </div>
    @endif
@endsection
