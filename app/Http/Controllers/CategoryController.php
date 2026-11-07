<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori.
     */
    public function index()
    {
        $categories = Category::withCount('documents')
            ->latest()
            ->get();

        return view('lecturer-categories', compact('categories'));
    }


    /**
     * Menyimpan kategori baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $request->name,
            'is_hidden' => false,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Kategori berhasil ditambahkan!');
    }


    /**
     * Memperbarui kategori.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Kategori berhasil diperbarui!');
    }


    /**
     * Menampilkan / menyembunyikan kategori.
     */
    public function toggleHide($id)
    {
        $category = Category::findOrFail($id);

        $category->is_hidden = !$category->is_hidden;
        $category->save();

        $status = $category->is_hidden
            ? 'disembunyikan'
            : 'ditampilkan';

        return redirect()
            ->back()
            ->with('success', "Kategori berhasil {$status}!");
    }


    /**
     * Menghapus kategori.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Tidak boleh menghapus kategori yang masih memiliki dokumen
        if ($category->documents()->count() > 0) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih berisi dokumen!'
                );
        }

        $category->delete();

        return redirect()
            ->back()
            ->with('success', 'Kategori berhasil dihapus!');
    }
}