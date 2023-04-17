<?php

namespace Elbgoods\NovaMapboxShapeField;

use Illuminate\Support\Arr;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class MapboxShapeField extends Field
{
    public $component = 'nova-mapbox-shape-field';

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->withMeta(['accessToken' => config('services.mapbox.key')]);
    }

    public function zoom(int $zoom)
    {
        return $this->withMeta(['zoom' => $zoom]);
    }

    public function latitude($latitude)
    {
        return $this->withMeta(['latitude' => $latitude]);
    }

    public function longitude($longitude)
    {
        return $this->withMeta(['longitude' => $longitude]);
    }

    public function geoJson(array $geoJson)
    {
        return $this->withMeta(compact('geoJson'));
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request->exists($requestAttribute)) {

            $geoInformation = json_decode($request[$requestAttribute], true, 512, JSON_THROW_ON_ERROR);

            if ($coordinates = Arr::get($geoInformation, 'coordinates')) {

                $points = collect($coordinates[0])->map(
                    fn(array $pair) => sprintf('%s %s', $pair[0], $pair[1])
                )->implode(', ');

                $model->{$attribute} = sprintf('POLYGON ((%s))', $points);
            }

            if (($location = Arr::get($geoInformation, 'location')) && method_exists($model, 'setLocation')) {
                $model->setLocation(
                    (float) $location['lat'],
                    (float) $location['lng'],
                );
            }
        }
    }
}
