<?php

namespace Dyln\Twig\Extension;

class Meta extends \Twig_Extension
{
    private $meta = [];

    public function getName()
    {
        return 'meta';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('meta', [$this, 'meta']),
        ];
    }

    public function meta($meta = [])
    {
        $this->meta = $meta;
        $raws = $this->meta['raws']??[];
        $html = '';

        if (!$canonical = $this->get('canonical')) {
            throw new \Exception('Canonical is mandatory');
        }
        $html .= '<title>' . $this->get('title', 'Welcome to Twizy.com') . '</title>';
        $html .= '<link rel="canonical" href="' . $canonical . '">';
        $html .= '<meta name="description" content="' . $this->get('description', 'Welcome to Twizy.com') . '" />';
        $html .= '<meta name="keywords" content="' . $this->get('keywords', 'e cig, e cig uk, best e cig, e cigarette, electronic cigarette, e cig kits, e liquid, e cig liquid, innokin, vision, provape, aqwa') . '" />';
        $html .= '<meta property="og:title" content="' . $this->get('og_title', $this->get('title', 'Welcome to Twizy.com')) . '" />';
        $html .= '<meta property="og:url" content="' . $canonical . '" />';
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
        $value = ArrayUtil::getIn($this->meta, $keys, $default);
        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return $value;
    }
}