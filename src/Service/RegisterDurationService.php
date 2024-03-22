<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class RegisterDurationService
{
    private const SESSION_KEY = 'register_request_time';

    public function startTimer(Request $request): void
    {
        $request->getSession()->set(self::SESSION_KEY, time());
        $request->getSession()->save();
    }

    public function getDuration(Request $request): int
    {
        $time = time();
        $start = $request->getSession()->get(self::SESSION_KEY) ?? $time;

        return $time - $start;
    }
}
