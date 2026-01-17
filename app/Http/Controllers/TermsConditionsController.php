<?php

namespace App\Http\Controllers;

use App\Http\Requests\TermsRequest;
use App\Models\Condiciones;
use App\Models\Terminos;
use Illuminate\Http\RedirectResponse;

class TermsConditionsController extends Controller
{
    public function index()
    {
        $terms = Terminos::query()->orderBy('id', 'desc')->first();
        $conditions = Condiciones::query()->orderBy('id', 'desc')->first();

        return view('panel.terms-conditions.index', compact('terms', 'conditions'));
    }

    public function store(TermsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $terms = Terminos::query()->orderBy('id', 'desc')->first() ?? new Terminos();
        $terms->fill([
            'texto' => $validated['terms_text'],
            'english' => $validated['terms_english'],
        ]);
        $terms->save();

        $conditions = Condiciones::query()->orderBy('id', 'desc')->first() ?? new Condiciones();
        $conditions->fill([
            'texto' => $validated['conditions_text'],
            'english' => $validated['conditions_english'],
        ]);
        $conditions->save();

        return redirect()->route('terms-conditions.index')->with('success', __('Information updated successfully.'));
    }
}
