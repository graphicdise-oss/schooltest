<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Library\LibraryBook;
use App\Models\Library\LibraryCategory;
use App\Models\Library\LibraryLoan;
use Illuminate\Http\Request;

class LibraryBookController extends Controller
{
    public function index(Request $request)
    {
        $search     = $request->get('search', '');
        $categoryId = $request->get('category_id', '');

        $books = LibraryBook::with('category')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%")
                       ->orWhere('author', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        $categories = LibraryCategory::orderBy('name')->get();

        $activeLoans = LibraryLoan::with(['book'])
            ->where('status', 'ยืมอยู่')
            ->orderBy('due_at')
            ->get();

        return view('library.books_index', compact('books', 'categories', 'search', 'categoryId', 'activeLoans'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['available_copies'] = $data['total_copies'];
        LibraryBook::create($data);
        return back()->with('success', 'เพิ่มหนังสือสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $book = LibraryBook::findOrFail($id);
        $data = $this->validated($request, $id);

        // ถ้าปรับจำนวนรวม ให้ปรับจำนวนที่ยืมได้ตามส่วนต่าง (ไม่ให้ต่ำกว่า 0)
        $onLoan = $book->total_copies - $book->available_copies;
        $data['available_copies'] = max(0, $data['total_copies'] - $onLoan);

        $book->update($data);
        return back()->with('success', 'แก้ไขข้อมูลหนังสือสำเร็จ');
    }

    public function destroy($id)
    {
        $book = LibraryBook::findOrFail($id);
        if ($book->loans()->where('status', 'ยืมอยู่')->exists()) {
            return back()->with('error', 'ไม่สามารถลบได้ เนื่องจากหนังสือเล่มนี้มีคนยืมอยู่');
        }
        $book->delete();
        return back()->with('success', 'ลบหนังสือสำเร็จ');
    }

    private function validated(Request $request, $id = null): array
    {
        $data = $request->validate([
            'category_id'    => 'nullable|exists:library_categories,id',
            'code'           => 'nullable|unique:library_books,code' . ($id ? ',' . $id : ''),
            'title'          => 'required|string|max:255',
            'author'         => 'nullable|string|max:255',
            'publisher'      => 'nullable|string|max:255',
            'isbn'           => 'nullable|string|max:50',
            'total_copies'   => 'required|integer|min:1',
            'shelf_location' => 'nullable|string|max:100',
            'price'          => 'nullable|numeric|min:0',
        ], [
            'title.required'        => 'กรุณาระบุชื่อหนังสือ',
            'code.unique'            => 'รหัสหนังสือนี้มีอยู่แล้ว',
            'total_copies.required' => 'กรุณาระบุจำนวนหนังสือ',
            'total_copies.min'      => 'จำนวนหนังสือต้องมากกว่า 0',
        ]);
        $data['category_id'] = $data['category_id'] ?: null;
        return $data;
    }
}
