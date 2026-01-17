<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Social;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function index()
    {
        $social = Social::query()->orderBy('id', 'asc')->get();

        return view('panel.contact.index', compact('social'));
    }

    public function edit($id)
    {
        $social = Social::findOrFail($id);

        return view('panel.contact.form', compact('social'));
    }

    public function update(ContactRequest $request, $id): RedirectResponse
    {
        $social = Social::findOrFail($id);
        $social->update($request->validated());

        return redirect()->route('contact.index')->with('success', __('Information updated successfully.'));
    }
}
