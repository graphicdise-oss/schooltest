<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Library\LibraryCategory;
use Illuminate\Http\Request;

class LibraryCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $categories = LibraryCategory::withCount('books')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return view('library.categories_index', compact('categories', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:library_categories,name',
        ], [
            'name.required' => 'กรุณาระบุชื่อหมวดหมู่',
            'name.unique'   => 'มีหมวดหมู่นี้อยู่แล้ว',
        ]);

        LibraryCategory::create(['name' => $request->name]);

        return back()->with('success', 'เพิ่มหมวดหมู่หนังสือสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $cat = LibraryCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:library_categories,name,' . $id,
        ], [
            'name.required' => 'กรุณาระบุชื่อหมวดหมู่',
            'name.unique'   => 'มีหมวดหมู่นี้อยู่แล้ว',
        ]);

        $cat->update(['name' => $request->name]);

        return back()->with('success', 'แก้ไขหมวดหมู่หนังสือสำเร็จ');
    }

    public function toggle($id)
    {
        $cat = LibraryCategory::findOrFail($id);
        $cat->update(['is_active' => !$cat->is_active]);
        return back()->with('success', 'อัปเดตสถานะสำเร็จ');
    }

    public function destroy($id)
    {
        $cat = LibraryCategory::withCount('books')->findOrFail($id);
        if ($cat->books_count > 0) {
            return back()->with('error', 'ไม่สามารถลบได้ เนื่องจากมีหนังสือในหมวดหมู่นี้อยู่');
        }
        $cat->delete();
        return back()->with('success', 'ลบหมวดหมู่หนังสือสำเร็จ');
    }
}
