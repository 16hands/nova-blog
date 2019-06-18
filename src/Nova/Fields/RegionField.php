<?php

namespace OptimistDigital\NovaBlog\Nova\Fields;

use Laravel\Nova\Fields\Field;
use OptimistDigital\NovaBlog\NovaBlog;
use OptimistDigital\NovaBlog\Models\Region;

class RegionField extends Field
{
    public $component = 'region-field';

    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, 'template', $resolveCallback);
    }

    public function resolve($resource, $attribute = null)
    {
        parent::resolve($resource, $attribute);

        $regions = $this->getAvailableRegions($resource);

        $this->withMeta([
            'asHtml' => true,
            'regions' => $regions,
            'existingRegions' => Region::whereNull('locale_parent_id')->get()->pluck('template', 'id'),
        ]);

        $regionsTableName = NovaBlog::getRegionsTableName();
        $locale = request()->get('locale');
        $this->rules('required', "unique:$regionsTableName,template,{{resourceId}},id,locale,$locale");
    }

    public function getAvailableRegions(Region $region = null): array
    {
        if (isset($region) && isset($region->id) && isset($region->template)) {
            return [$region->template];
        }

        return collect(NovaBlog::getRegionTemplates())
            ->filter(function ($template) {
                return !Region::where('template', $template::$name)->exists();
            })
            ->map(function ($template) {
                return $template::$name;
            })
            ->toArray();
    }
}
