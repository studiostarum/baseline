<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;
use Laravel\Fortify\Fortify;

class PasswordConfirmedResponse implements PasswordConfirmedResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        $intended = $request->session()->pull('url.intended', Fortify::redirects('password-confirmation'));

        if ($this->isHomeUrl($intended)) {
            $intended = route('dashboard');
        }

        $url = $intended ?? route('dashboard');
        if (str_contains($url, 'confirm_password=')) {
            $parts = parse_url($url);
            parse_str($parts['query'] ?? '', $params);
            unset($params['confirm_password']);
            $cleanQuery = http_build_query(array_filter($params));
            $path = ($parts['path'] ?? '').($cleanQuery !== '' ? '?'.$cleanQuery : '');
            $url = (isset($parts['scheme']) ? $parts['scheme'].'://' : '')
                .($parts['host'] ?? '')
                .(isset($parts['port']) ? ':'.$parts['port'] : '')
                .$path;
        }

        return redirect()->to($url);
    }

    private function isHomeUrl(?string $url): bool
    {
        if ($url === null || $url === '') {
            return true;
        }

        $home = route('home');

        return $url === $home || $url === '/';
    }
}
