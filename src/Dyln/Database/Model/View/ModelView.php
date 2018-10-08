<?php

namespace Dyln\Database\Model\View;

interface ModelView
{
    public function export($model, $options = []);
}
