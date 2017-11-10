<?php

namespace Dyln\Debugbar;

use Dyln\AppEnv;
use Dyln\Debugbar\Formatter\MongoFormatter;
use Dyln\Twig\Extension\Functions;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use function Dyln\getin;

class DebugbarDumpMiddleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        $next($request, $response);
        if (AppEnv::isDebugEnabled()) {
            if (AppEnv::isUrlKeyMatch('dump', 1)) {
                $timeline = $this->extractTimeline(Debugbar::getData());
                $data = Debugbar::getData();
                $data['Timeline'] = $timeline;
                echo $this->render($data);
                exit;
            }
        }

        return $response;
    }

    private function extractTimeline($data)
    {
        $timeline = [];
        foreach ($data as $section => $rows) {
            foreach ($rows as $index => $row) {
                if (isset($row['start'])) {
                    $timeline[] = [
                        $section . '_' . $index,
                        (int) ($row['start'] * 1000),
                        (int) ($row['end'] * 1000),
                    ];
                }
            }
        }
        usort($timeline, function ($a, $b) {
            return $a[1] <=> $b[1];
        });
        $start = $timeline[0][1];
        foreach ($timeline as &$row) {
            $row[1] = $row[1] - $start;
            $row[2] = $row[2] - $start;
        }

        return $timeline;
    }

    private function render($data = [])
    {
        $apiResponse = getin($data, 'ApiResponse', []);
        foreach ($apiResponse as &$response) {
            $body = json_decode($response['body'], true);
            if (isset($body['debug'])) {
                unset($body['debug']);
                $response['body'] = json_encode($body);
            }
        }
        $data['ApiResponse'] = $apiResponse;

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
