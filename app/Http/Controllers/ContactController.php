<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    //

    /**
     * Show contact form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showForm()
    {
        // TODO: Implements.
        return view('contact.form');
    }
}
