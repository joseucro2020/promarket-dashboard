<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::orderBy('id', 'desc')->get();

        return view('panel.tags.index')->with([
            'tags' => $tags,
        ]);
    }

    public function store(TagRequest $request)
    {
        $tag = Tag::create($request->all());
        $tag->status = 1;
        $tag->save();

        return response()->json([
            'tag' => $tag,
        ]);
    }

    public function update(TagRequest $request, $id)
    {
        $tag = Tag::findOrFail($id);
        $tag->name = $request->name;
        $tag->save();

        return response()->json([
            'tag' => $tag,
        ]);
    }

    public function status($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->status = $tag->status == 1 ? 0 : 1;
        $tag->save();

        return response()->json([
            'status' => $tag->status,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }
}
