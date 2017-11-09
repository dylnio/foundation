<?php

namespace Dyln\Debugbar;

use Dyln\AppEnv;
use Dyln\Debugbar\Formatter\MongoFormatter;
use Dyln\Twig\Extension\Functions;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;

class DebugbarDumpMiddleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        $next($request, $response);
        if (AppEnv::isDebugEnabled()) {
            if (AppEnv::isUrlKeyMatch('dump', 1)) {
                echo $this->render(Debugbar::getData());
                exit;
            }
        }

        return $response;
    }

    private function render($data = [])
    {
        return $this->getView()->fetch('index.twig', ['data' => $data]);
    }

    private function getView()
    {
        $view = new Twig(__DIR__ . '/template', [
            'cache'               => sys_get_temp_dir(),
            'debug'               => true,
            'auto_reload'         => true,
            'charset'             => 'UTF-8',
            'base_template_class' => 'Twig_Template',
            'strict_variables'    => true,
            'autoescape'          => 'html',
            'optimizations'       => -1,
        ]);
        $view->addExtension(new \Twig_Extension_Debug());
        $view->addExtension(new MongoFormatter($view->getEnvironment()));
        $view->addExtension(new Functions());

        return $view;
    }
}
