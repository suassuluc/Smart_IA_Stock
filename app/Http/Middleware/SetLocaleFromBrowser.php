<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromBrowser
{
    /**
     * Mapeia códigos do Accept-Language (ex: pt-BR) para o locale do Laravel (pt_BR).
     */
    protected function mapBrowserLocaleToApp(string $browserLocale): string
    {
        $map = [
            'pt-BR' => 'pt_BR',
            'pt-br' => 'pt_BR',
            'pt_BR' => 'pt_BR',
            'pt' => 'pt_BR',
            'en-US' => 'en',
            'en-GB' => 'en',
            'en' => 'en',
        ];

        $normalized = str_replace('_', '-', $browserLocale);

        return $map[$normalized] ?? $map[$browserLocale] ?? str_replace('-', '_', $browserLocale);
    }

    /**
     * Extrai idiomas preferidos do header Accept-Language, ordenados por prioridade (q).
     */
    protected function getPreferredLocales(Request $request): array
    {
        $header = $request->header('Accept-Language', '');
        if (empty($header)) {
            return [];
        }

        $locales = [];
        foreach (explode(',', $header) as $part) {
            $part = trim($part);
            if (str_contains($part, ';')) {
                [$code, $q] = explode(';', $part, 2);
                $q = (float) trim(str_replace('q=', '', $q));
            } else {
                $code = $part;
                $q = 1.0;
            }
            $locales[trim($code)] = $q;
        }
        arsort($locales);

        return array_keys($locales);
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $available = config('app.available_locales', ['en', 'pt_BR']);

        foreach ($this->getPreferredLocales($request) as $browserLocale) {
            $locale = $this->mapBrowserLocaleToApp($browserLocale);
            if (in_array($locale, $available, true)) {
                App::setLocale($locale);
                break;
            }
        }

        return $next($request);
    }
}
