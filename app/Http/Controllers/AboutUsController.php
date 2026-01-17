<?php

namespace App\Http\Controllers;

use App\Http\Requests\AboutUsRequest;
use App\Models\Nosotros;
use Illuminate\Http\RedirectResponse;

class AboutUsController extends Controller
{
    public function index()
    {
        $us = Nosotros::query()->first();

        return view('panel.about-us.index', compact('us'));
    }

    public function store(AboutUsRequest $request): RedirectResponse
    {
        Nosotros::create($request->validated());

        return redirect()->route('about-us.index')->with('success', __('Information saved successfully.'));
    }

    public function update(AboutUsRequest $request, $id): RedirectResponse
    {
        $us = Nosotros::findOrFail($id);
        $us->update($request->validated());

        return redirect()->route('about-us.index')->with('success', __('Information updated successfully.'));
    }
}
