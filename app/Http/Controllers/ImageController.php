<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'desc');

        $query = Image::query()->with('createdBy');

        $allowedDirs = ['asc', 'desc'];

        if (in_array($sortDir, $allowedDirs, true) && ! empty($sortBy)) {
            switch ($sortBy) {
                case 'id':
                    $query->orderBy('id', $sortDir);
                    break;
                case 'patient_code':
                    $query->orderBy('patient_code', $sortDir);
                    break;
                case 'file_name':
                    $query->orderBy('file_name', $sortDir);
                    break;
                case 'created_at':
                    $query->orderBy('created_at', $sortDir);
                    break;
                default:
                    $query->orderBy('id', 'asc');
                    break;
            }
        } else {
            // Default order when no sort is selected.
            $query->orderBy('id', 'desc');
        }

        $data = $query->paginate($perPage);
        return view('images.show', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $image = Image::findOrFail($id);
        $image->delete();

        return redirect()->route('admin.images.list')->with('success', 'Xóa hình ảnh thành công');
    }
}
