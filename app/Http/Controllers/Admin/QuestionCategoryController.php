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
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:question_categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:question_categories,id',
            'order' => 'integer'
        ]);

        $category = QuestionCategory::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'category' => $category
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, QuestionCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:question_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:question_categories,id',
            'is_active' => 'boolean',
            'order' => 'integer'
        ]);

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully'
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(QuestionCategory $category)
    {
        // Check if category has questions
        if ($category->questions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with questions'
            ], 422);
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with subcategories'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Get categories as tree.
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
     * Update categories order.
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
