<?php

namespace Dyln\Debugbar\Formatter;

use function Dyln\getin;

class MongoFormatter extends \Twig_Extension
{
    /** @var  \Twig_Environment */
    protected $environment;

    /**
     * Debugbar constructor.
     * @param \Twig_Environment $environment
     */
    public function __construct(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getName()
    {
        return __CLASS__;
    }

    public function getFunctions()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return [
            new \Twig_SimpleFunction('mongo_format', [$this, 'format']),
        ];
    }

    public function format($row)
    {
        $params = [];
        if (!empty($row['fieldName'])) {
            $params[] = is_array($row['fieldName']) ? json_encode($row['fieldName']) : $row['fieldName'];
        }
        if (!empty($row['key'])) {
            $params[] = is_array($row['key']) ? json_encode($row['key']) : $row['key'];
        }
        if (!empty($row['indexName'])) {
            $params[] = is_array($row['indexName']) ? json_encode($row['indexName']) : $row['indexName'];
        }
        if (!empty($row['indexes'])) {
            $params[] = json_encode($row['indexes']);
        }
        if (!empty($row['document'])) {
            $params[] = json_encode($row['document']);
        }
        if (!empty($row['documents'])) {
            $params[] = json_encode($row['documents']);
        }
        if (!empty($row['filter'])) {
            $params[] = json_encode($row['filter']);
        }
        if (!empty($row['update'])) {
            $params[] = json_encode($row['update']);
        }
        if (!empty($row['replacement'])) {
            $params[] = json_encode($row['replacement']);
        }
        if (!empty($row['operation'])) {
            $params[] = json_encode($row['operation']);
        }
        if (!empty($row['options'])) {
            $params[] = json_encode($row['options']);
        }
        $params = array_map(function ($el) {
            return '<span class="_params">' . $this->stringToObjectId($el) . '</span>';
        }, $params);

        return $this->environment->render('_mongo.twig', [
            'command'   => $this->stringToObjectId($row['command']),
            'params'    => implode(',', $params),
            'backtrace' => getin($row, 'bt', []),
        ]);
    }

    private function stringToObjectId($string)
    {
        $re = '/{"\$oid":"([a-f\d]{24})"}/';
        $subst = 'ObjectId("$1")';
        $res = preg_replace($re, $subst, $string);

        return $res;
    }
}
