<?php

namespace CodeZero\Translator\Controllers;

class KeepAliveController extends Controller
{
    /**
     * This route can be "pinged" by the Vue UI app
     * to prevent the CSRF token from expiring.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return response('Beep, beep.', 200);
    }
}
