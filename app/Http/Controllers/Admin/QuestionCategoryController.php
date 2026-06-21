<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;

class QuestionCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = QuestionCategory::with('parent')
                                      ->withCount('questions')
                                      ->orderBy('order')
                                      ->get();

        return view('admin.question-categories.index', compact('categories'));
    }

    /**
     * Show form for creating a new category.
     */
    public function create()
    {
        $categories = QuestionCategory::where('is_active', true)->get();
        return view('admin.question-categories.create', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:question_categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:question_categories,id',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer'
        ]);

        $category = QuestionCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'is_active' => $request->has('is_active'),
            'order' => $request->order ?? 0
        ]);

        return redirect()->route('admin.question-categories.index')
                         ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(QuestionCategory $question_category)
    {
        $category = $question_category->load(['parent', 'children', 'questions']);
        return view('admin.question-categories.show', compact('category'));
    }

    /**
     * Show form for editing the specified category.
     */
    public function edit(QuestionCategory $question_category)
    {
        $category = $question_category;
        $categories = QuestionCategory::where('id', '!=', $category->id)
                                      ->where('is_active', true)
                                      ->get();
        return view('admin.question-categories.edit', compact('category', 'categories'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, QuestionCategory $question_category)
    {
        $category = $question_category;
        
        $request->validate([
            'name' => 'required|string|max:255|unique:question_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:question_categories,id',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer'
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'is_active' => $request->has('is_active'),
            'order' => $request->order ?? $category->order
        ]);

        return redirect()->route('admin.question-categories.index')
                         ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(QuestionCategory $question_category)
    {
        $category = $question_category;
        
        // Check if category has questions
        if ($category->questions()->count() > 0) {
            return redirect()->route('admin.question-categories.index')
                             ->with('error', 'Cannot delete category with questions.');
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            return redirect()->route('admin.question-categories.index')
                             ->with('error', 'Cannot delete category with subcategories.');
        }

        $category->delete();

        return redirect()->route('admin.question-categories.index')
                         ->with('success', 'Category deleted successfully.');
    }

    /**
     * Toggle category status.
     */
    public function toggleStatus(QuestionCategory $question_category)
    {
        $category = $question_category;
        
        $category->update([
            'is_active' => !$category->is_active
        ]);

        return redirect()->route('admin.question-categories.index')
                         ->with('success', 'Category status updated successfully.');
    }

    /**
     * Get categories as tree (API).
     */
    public function getTree()
    {
        $categories = QuestionCategory::with('children')
                                      ->whereNull('parent_id')
                                      ->orderBy('order')
                                      ->get();

        return response()->json($categories);
    }

    /**
     * Update categories order (API).
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:question_categories,id',
            'categories.*.order' => 'required|integer'
        ]);

        foreach ($request->categories as $categoryData) {
            QuestionCategory::where('id', $categoryData['id'])
                            ->update(['order' => $categoryData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category order updated'
        ]);
    }
}