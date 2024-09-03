<?php

namespace App\Http\Controllers\Backoffice;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display all the static pages when authenticated
     *
     * @param string $page
     * @return \Illuminate\View\View
     */
    public function index(string $page)
    {
        if (view()->exists("pages.{$page}")) {
            return view("pages.{$page}");
        }

        return abort(404);
    }

    public function vr()
    {
        return view("backoffice.pages.virtual-reality");
    }

    public function rtl()
    {
        return view("backoffice.pages.rtl");
    }

    public function profile()
    {
        return view("backoffice.pages.profile-static");
    }

    
}
