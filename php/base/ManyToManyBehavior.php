<?php

namespace app\base;

use yii\base\Behavior;
use yii\db\BaseActiveRecord;

class ManyToManyBehavior extends Behavior
{
    public array $relations = [];

    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_FIND => 'loadRelations',
        ];
    }

    public function loadRelations()
    {
        foreach ($this->relations as $name => $config) {
            $this->owner->populateRelation($name, $this->getRelation($config));
        }
    }

    private function getRelation($config)
    {
        $relation = $this->owner->getRelation($config['relation']);

        $query = $relation->select($config['select'])->viaTable($config['via'], $config['viaLink']);

        if (isset($config['orderBy'])) {
            $query->orderBy($config['orderBy']);
        }

        return $query;
    }
}