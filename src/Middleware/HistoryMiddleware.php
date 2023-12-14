<?php

namespace Mattoid\OperateLog\Middleware;

use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Flarum\Locale\Translator;
use Mattoid\OperateLog\model\UserOperateLog;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HistoryMiddleware implements MiddlewareInterface
{
    private $uriAllowed = ['/token'];

    private $settings;
    private $translator;

    public function __construct(SettingsRepositoryInterface $settings, Translator $translator)
    {
        $this->settings = $settings;
        $this->translator = $translator;
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

        $operateLog = [
            "user_id" => isset($userId) ? $userId: 0,
            "method"  => $request->getMethod(),
            "uri"     => $request->getUri(),
            "ip"      => $request->getAttribute("ipAddress"),
            "request" => in_array($request->getUri(), $this->uriAllowed) ? '' :json_encode($request->getParsedBody()),
            "response" => $response->getBody(),
            "created_at" => date('Y-m-d H:i:s')
        ];

        $saveType = $this->settings->get("mattoid-operate-log.save-type", 1);
        switch ($saveType) {
            case 1:
                app('log')->info($this->translator->trans("mattoid-operate-log.api.prefix") . " " . json_encode($operateLog));
                break;
            case 2:
                UserOperateLog::query()->insert($operateLog);
                break;
            default:
        }

        return $response;
    }
}
