<?php

namespace App\Http\Controllers;

use App\Models\BusinessCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(){return view('categories.index',['categories'=>BusinessCategory::orderBy('priority')->paginate(50)]);}
    public function store(Request$r){BusinessCategory::create($this->data($r));return back()->with('success','Category added.');}
    public function update(Request$r,BusinessCategory$category){$category->update($this->data($r));return back()->with('success','Category updated.');}
    public function destroy(BusinessCategory$category){$category->delete();return back()->with('success','Category deleted.');}
    public function toggleAll(Request$r){BusinessCategory::query()->update(['enabled'=>$r->boolean('enabled')]);return back()->with('success','Categories updated.');}
    private function data(Request$r):array{return$r->validate(['name'=>'required|string|max:190','api_alias'=>'nullable|string|max:190','search_term'=>'nullable|string|max:190','enabled'=>'sometimes|boolean','priority'=>'required|integer|min:1|max:10000']);}
}
