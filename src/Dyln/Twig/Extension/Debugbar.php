<?php

namespace Dyln\Twig\Extension;

use Dyln\AppEnv;
use Dyln\Collection\Collection;

class Debugbar extends \Twig_Extension
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
        return 'debugbar';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('debugbar', [$this, 'debugbar']),
        ];
    }

    public function debugbar()
    {
        if (AppEnv::isDebugEnabled()) {
            $payload = \Dyln\Debugbar\Debugbar::getData();
            $payload = $this->format($payload);
            $data = [];
            foreach ($payload as $sectionName => $sectionData) {
                $sectionData = Collection::create($sectionData);
                $data[$sectionName] = [
                    'count' => $sectionData->count(),
                    'time'  => $sectionData->reduce(function ($carry, $item) {
                        return $carry + \Dyln\Util\ArrayUtil::getIn($item, ['time'], 0);
                    }, 0),
                    'data'  => $sectionData->toArrayValues(),
                ];
            }

            return $this->environment->render('debugbar/debugbar.twig', ['data' => $data]);
        }
    }

    private function format($payload)
    {
        foreach ($payload as $sectionName => &$sectionData) {
            if ($sectionName == 'Mongo') {
                foreach ($sectionData as &$sd) {
                    $sd['query'] = preg_replace('/({"\$oid":"([0-9a-z]{24})"})/i', 'ObjectId("${2}")', $sd['query']);
                    $sd['query'] = str_replace(',', ', ', $sd['query']);
                }
            }
        }

        return $payload;
    }

}