<?php

namespace Elbgoods\NovaMapboxShapeField;

use Illuminate\Support\Arr;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use MStaack\LaravelPostgis\Geometries\LineString;
use MStaack\LaravelPostgis\Geometries\MultiPolygon;
use MStaack\LaravelPostgis\Geometries\Point;
use MStaack\LaravelPostgis\Geometries\Polygon;

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

            // I am a mess, please clean me up :)
            $geoInformation = json_decode($request[$requestAttribute], true, 512, JSON_THROW_ON_ERROR);

            if ($coordinates = Arr::get($geoInformation, 'coordinates')) {
                $collection = new LineString(collect($coordinates[0])->map(
                    fn (array $pair) => new Point($pair[1], $pair[0])
                )->toArray());

                $multiPolygon = new MultiPolygon([
                    new Polygon([$collection]),
                ]);

                $model->{$attribute} = $multiPolygon->toWKT();
            }

            if ($location = Arr::get($geoInformation, 'location')) {
                $model->setLocation(
                    (float) $location['lat'],
                    (float) $location['lng'],
                );
            }
        }
    }
}
