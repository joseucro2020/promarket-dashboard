<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_english' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ];
    }

    public function index()
    {
        $categories = Category::whereIn('status', ['0', '1'])
            ->orderBy('id', 'desc')
            ->get();

        return view('panel.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('panel.categories.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $category = Category::create($data);
        $category->slug = Str::slug($category->name);
        $category->save();

        return redirect()->route('categories.index')->with('success', __('Category saved successfully.'));
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);

        return view('panel.categories.show', compact('category'));
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);

        return view('panel.categories.form', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validate($this->rules());

        $category->update($data);
        $category->slug = Str::slug($category->name);
        $category->save();

        return redirect()->route('categories.index')->with('success', __('Category updated successfully.'));
    }

    public function destroy($id)
    {
        $category = Category::where('id', $id)
            ->withCount(['products' => function ($q) {
                $q->where('status', '1');
            }])
            ->firstOrFail();

        if ((int) $category->products_count > 0) {
            return redirect()->route('categories.index')->with('error', __('Category has active products.'));
        }

        $category->status = '2';
        $category->slug = null;
        $category->save();

        return redirect()->route('categories.index')->with('success', __('Category deleted successfully.'));
    }

    public function status($id)
    {
        $category = Category::findOrFail($id);
        $category->status = $category->status === '1' ? '0' : '1';
        $category->save();

        return redirect()->route('categories.index')->with('success', __('Category status updated.'));
    }
}
