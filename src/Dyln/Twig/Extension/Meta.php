<?php

namespace Dyln\Twig\Extension;

use Dyln\AppEnv;

class Meta extends \Twig_Extension
{
    private $meta = [];

    public function getName()
    {
        return 'meta';
    }

    public function getFunctions()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return [
            new \Twig_SimpleFunction('meta', [$this, 'meta']),
        ];
    }

    public function meta($meta = [])
    {
        $this->meta = $meta;
        $raws = $this->meta['raws'] ?? [];
        $html = '';

        $html .= '<title>' . $this->get('title', 'Welcome') . '</title>';
        $html .= '<link rel="canonical" href="' . $this->get('canonical', '') . '">';
        $html .= '<meta name="description" content="' . $this->get('description', 'Welcome') . '" />';
        $html .= '<meta name="keywords" content="' . $this->get('keywords', '') . '" />';
        $html .= '<meta property="og:title" content="' . $this->get('og_title', $this->get('title', 'Welcome')) . '" />';
        $html .= '<meta property="og:url" content="' . $this->get('canonical', '') . '" />';
        if ($ogType = $this->get('og_type')) {
            $html .= '<meta property="og:type" content="' . $ogType . '" />';
        }
        if ($ogImage = $this->get('og_image')) {
            $html .= '<meta property="og:image" content="' . $ogImage . '" />';
        }

        $html .= '<meta name="robots" content="INDEX,FOLLOW" />';
        foreach ($raws as $raw) {
            $html .= $raw;
        }

        return $html;
    }

    private function get($keys, $default = null)
    {
        $value = \Dyln\Util\ArrayUtil::getIn($this->meta, $keys, $default);
        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        if ($keys == 'title' && AppEnv::isDebugEnabled()) {
            $value = '[' . gethostname() . '] ' . $value;
        }

        return $value;
    }
}