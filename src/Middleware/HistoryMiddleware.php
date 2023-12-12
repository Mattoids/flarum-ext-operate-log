<?php

namespace Mattoid\OperateLog\Middleware;

use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Mattoid\OperateLog\model\UserOperateLog;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HistoryMiddleware implements MiddlewareInterface
{
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $typesAllowed = [];
        $actor = RequestUtil::getActor($request);
        $userId = Arr::get($actor, 'id');

        if ($this->settings->get('mattoid-operate-log.request-type-get')) {
            $typesAllowed[] = "GET";
        }
        if ($this->settings->get('mattoid-operate-log.request-type-post')) {
            $typesAllowed[] = "POST";
        }
        if ($this->settings->get('mattoid-operate-log.request-type-put')) {
            $typesAllowed[] = "PUT";
            $typesAllowed[] = "PATCH";
        }
        if ($this->settings->get('mattoid-operate-log.request-type-delete')) {
            $typesAllowed[] = "DELETE";
        }

        $response = $handler->handle($request);

        if (!in_array($request->getMethod(), $typesAllowed)) {
            return $response;
        }

        $operateLog = new UserOperateLog();
        $operateLog->user_id = $userId;
        $operateLog->method = $request->getMethod();
        $operateLog->uri = $request->getUri();
        $operateLog->request = json_encode($request->getParsedBody());
        $operateLog->response = $response->getBody();
        $operateLog->save();

        return $response;
    }
}
