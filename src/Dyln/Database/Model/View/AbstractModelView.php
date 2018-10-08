<?php

namespace Dyln\Database\Model\View;

use function Dyln\getin;

abstract class AbstractModelView implements ModelView
{
    protected $key;
    protected $model;
    protected $global = [];
    protected $views = [];
    protected $selectedView = null;

    public function export($model, $options = [])
    {
        $this->model = $model;
        $data = [];
        $params = getIn($options, ['params'], []);
        $excludedFields = getIn($options, ['exclude_fields'], []);
        if (!is_array($excludedFields)) {
            $excludedFields = [$excludedFields];
        }
        $this->selectedView = getIn($options, ['view']);
        $inject = getIn($options, ['inject'], []);
        $rules = $this->getRequestedRules();
        $params['view'] = $this->selectedView;
        foreach ($rules as $field => $value) {
            if (in_array($field, $excludedFields)) {
                continue;
            }
            if (is_callable($value)) {
                $value = $value($params);
            }
            $data[$field] = $value;
        }

        return $inject ? $data + $inject : $data;
    }

    protected function getRequestedRules()
    {
        $rules = $this->getRules();
        $fields = array_keys($rules);
        if ($this->selectedView) {
            if (is_array($this->selectedView)) {
                $fields = $this->selectedView;
            } else {
                $viewFields = getin($this->views, $this->selectedView);
                if (is_null($viewFields)) {
                    throw new \InvalidArgumentException('Invalid _eview: ' . $this->selectedView);
                }
                if ($viewFields) {
                    $fields = $viewFields;
                }
            }
            $fields = array_merge($this->global, $fields);
        }

        return array_intersect_key($rules, array_flip($fields));
    }

    abstract public function getRules($fields = [], $params = []);
}
