<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Subsubcategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_english' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'paypal' => 'required|in:0,1',
            'stripe' => 'required|in:0,1',
            'status' => 'required|in:0,1',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'icon' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'icon2' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'subcategories' => 'array',
            'subcategories.*.id' => 'nullable|integer',
            'subcategories.*.name' => 'nullable|string|max:255',
            'subcategories.*.name_english' => 'nullable|string|max:255',
            'subcategories.*.slug' => 'nullable|string|max:255',
            'subcategories.*.delete' => 'nullable|in:0,1',
            'subcategories.*.icon' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'subcategories.*.sub_subcategories' => 'array',
            'subcategories.*.sub_subcategories.*.id' => 'nullable|integer',
            'subcategories.*.sub_subcategories.*.name' => 'nullable|string|max:255',
            'subcategories.*.sub_subcategories.*.name_english' => 'nullable|string|max:255',
            'subcategories.*.sub_subcategories.*.slug' => 'nullable|string|max:255',
            'subcategories.*.sub_subcategories.*.delete' => 'nullable|in:0,1',
            'subcategories.*.sub_subcategories.*.icon' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
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

        $data['image'] = $this->storeImage($request, 'image', 'img/categories');
        $data['icon'] = $this->storeImage($request, 'icon', 'img/categories/icons');
        $data['icon2'] = $this->storeImage($request, 'icon2', 'img/categories/sliders');

        $category = Category::create($data);
        $slugSource = $request->filled('slug') ? $request->input('slug') : $category->name;
        $category->slug = Str::slug($slugSource);
        $category->save();

        $this->syncSubcategories($category, $request);

        return redirect()->route('categories.index')->with('success', __('Category saved successfully.'));
    }

    public function show($id)
    {
        $category = Category::with(['subcategories.sub_subcategories'])->findOrFail($id);

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

        $data['image'] = $this->storeImage($request, 'image', 'img/categories', $category->image ?? null);
        $data['icon'] = $this->storeImage($request, 'icon', 'img/categories/icons', $category->icon ?? null);
        $data['icon2'] = $this->storeImage($request, 'icon2', 'img/categories/sliders', $category->icon2 ?? null);

        $category->update($data);
        $slugSource = $request->filled('slug') ? $request->input('slug') : $category->name;
        $category->slug = Str::slug($slugSource);
        $category->save();

        $this->syncSubcategories($category, $request);

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

    private function storeImage(Request $request, string $field, string $dir, ?string $currentPath = null): ?string
    {
        if (!$request->hasFile($field)) {
            return $currentPath;
        }

        $file = $request->file($field);
        $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $targetDir = public_path($dir);

        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $file->move($targetDir, $filename);

        if ($currentPath) {
            $currentFullPath = public_path($currentPath);
            if (File::exists($currentFullPath)) {
                File::delete($currentFullPath);
            }
        }

        return $dir . '/' . $filename;
    }

    private function syncSubcategories(Category $category, Request $request): void
    {
        $subcategories = $request->input('subcategories', []);

        foreach ($subcategories as $index => $subData) {
            $subcategoryId = $subData['id'] ?? null;
            $isDelete = !empty($subData['delete']);
            $hasName = !empty($subData['name']);
            $hasAnyData = $hasName || !empty($subData['name_english']) || !empty($subData['slug']) || $request->hasFile("subcategories.$index.icon");

            if (!$subcategoryId && !$hasAnyData) {
                continue;
            }

            $subcategory = $subcategoryId
                ? Subcategory::where('category_id', $category->id)->find($subcategoryId)
                : new Subcategory(['category_id' => $category->id]);

            if ($isDelete) {
                if ($subcategory && $subcategory->products()->count() === 0) {
                    $subcategory->status = '2';
                    $subcategory->save();
                }
                continue;
            }

            if (!$subcategory) {
                $subcategory = new Subcategory(['category_id' => $category->id]);
            }

            $subcategory->name = $subData['name'] ?? $subcategory->name;
            $subcategory->name_english = $subData['name_english'] ?? $subcategory->name_english;
            $slugSource = !empty($subData['slug']) ? $subData['slug'] : ($subData['name'] ?? $subcategory->name);
            $subcategory->slug = $slugSource ? Str::slug($slugSource) : $subcategory->slug;
            $subcategory->status = $subcategory->status ?? '1';
            $subcategory->category_id = $category->id;

            $iconPath = $this->storeImage($request, "subcategories.$index.icon", 'img/categories/subcategories', $subcategory->icon ?? null);
            if ($iconPath) {
                $subcategory->icon = $iconPath;
            }

            $subcategory->save();

            $this->syncSubSubcategories($subcategory, $subData['sub_subcategories'] ?? [], $request, $index);
        }
    }

    private function syncSubSubcategories(Subcategory $subcategory, array $subSubcategories, Request $request, int $subIndex): void
    {
        foreach ($subSubcategories as $subSubIndex => $subSubData) {
            $subSubId = $subSubData['id'] ?? null;
            $isDelete = !empty($subSubData['delete']);
            $hasName = !empty($subSubData['name']);
            $hasAnyData = $hasName || !empty($subSubData['name_english']) || !empty($subSubData['slug']) || $request->hasFile("subcategories.$subIndex.sub_subcategories.$subSubIndex.icon");

            if (!$subSubId && !$hasAnyData) {
                continue;
            }

            $subSub = $subSubId
                ? Subsubcategories::where('subcategory_id', $subcategory->id)->find($subSubId)
                : new Subsubcategories(['subcategory_id' => $subcategory->id]);

            if ($isDelete) {
                if ($subSub && $subSub->products()->count() === 0) {
                    $subSub->status = Subsubcategories::STATUS_DELETED;
                    $subSub->save();
                }
                continue;
            }

            if (!$subSub) {
                $subSub = new Subsubcategories(['subcategory_id' => $subcategory->id]);
            }

            $subSub->name = $subSubData['name'] ?? $subSub->name;
            $subSub->name_english = $subSubData['name_english'] ?? $subSub->name_english;
            $slugSource = !empty($subSubData['slug']) ? $subSubData['slug'] : ($subSubData['name'] ?? $subSub->name);
            $subSub->slug = $slugSource ? Str::slug($slugSource) : $subSub->slug;
            $subSub->status = $subSub->status ?? Subsubcategories::STATUS_ACTIVE;
            $subSub->subcategory_id = $subcategory->id;

            $iconPath = $this->storeImage(
                $request,
                "subcategories.$subIndex.sub_subcategories.$subSubIndex.icon",
                'img/categories/sub-subcategories',
                $subSub->icon ?? null
            );
            if ($iconPath) {
                $subSub->icon = $iconPath;
            }

            $subSub->save();
        }
    }
}
