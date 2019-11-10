<?php


namespace Antiques\LaravelSendgridEvents\Http\Middleware;


use Closure;
use function Symfony\Component\Console\Tests\Command\createClosure;

class SendgridEventMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $this->isKeyValid($request)) {
            return response()->json(['message' => 'Unauthorized request!'], 401);
        }

        return $next($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function isKeyValid($request) {

        if (! config('sendgridevents.url_secret_key')) {
            return true;
        }
        if ($request->input('key') == config('sendgridevents.url_secret_key')) {
            return true;
        }

        return false;
    }
}
