<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ToDo;
use App\Models\User;
use Illuminate\Http\Request;

class TodosController extends Controller
{   
    public function categories(){
        $categories = Category::all();
        if($categories->isNotEmpty()){
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendapatkan data',
                'data' => $categories
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Gagal mendapatkan data',
                'data' => null
            ], 400);
        }
    }
    public function toDos(Request $request){
        $query = ToDo::with(['categories', 'user'])->where('user_id', $request->user()->id);

        if($query->exists()){
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendapatkan data',
                'data' => $query->get()
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Gagal mendapatkan data',
                'data' => null
            ], 400);
        }
    }

    function postTodo(Request $request){
        try {
            $request->validate([
                'judul_tugas' => 'required|string|max:255',
                'deskripsi_tugas' => 'required|string',
                'tanggal_selesai' => 'required|date',
                'status' => 'required|in:belum_dikerjakan,proses,selesai',
                'categories.*' => 'exists:categories,id'
            ]);

            $todo = new ToDo();
            $todo->user_id = $request->user()->id; // Menggunakan ID user yang login
            $todo->judul_tugas = $request->judul_tugas;
            $todo->deskripsi_tugas = $request->deskripsi_tugas;
            $todo->tanggal_selesai = $request->tanggal_selesai;
            $todo->status = $request->status;
            $todo->save();

            if ($request->has('categories')) {
                $todo->categories()->sync($request->categories);
            }

            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan data',
                'data' => $todo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan data: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    function postCategory(Request $request){
        $request->validate([
            'name' => 'required',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->save();

        return response()->json([
            'status' => 200,
            'message' => 'Berhasil menambahkan kategori',
            'data' => $category
        ], 200);
    }

    public function updateToDo(Request $request, $id){
        try {
            $request->validate([
                'judul_tugas' => 'required|string|max:255',
                'deskripsi_tugas' => 'required|string',
                'tanggal_selesai' => 'required|date',
                'status' => 'required|in:belum_dikerjakan,proses,selesai',
                'categories.*' => 'exists:categories,id'
            ]);
            $todo = ToDo::find($id);
            if (!$todo) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Data tidak ditemukan',
                    'data' => null
                ], 404);
            }

            if ($todo->user_id !== $request->user()->id) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Unauthorized',
                    'data' => null
                ], 401);
            }

            $todo->judul_tugas = $request->judul_tugas;
            $todo->deskripsi_tugas = $request->deskripsi_tugas;
            $todo->tanggal_selesai = $request->tanggal_selesai;
            $todo->status = $request->status;       
            $todo->save();

            if ($request->has('categories')) {
                $todo->categories()->sync($request->categories);
            }
            return response()->json([
                'status' => true,
                'message' => 'Berhasil memperbarui data',
                'data' => $todo
            ], 200);

        }catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }

    }
     public function deleteToDo(Request $request, $id){
        try {
            $todo = ToDo::find($id);
            if (!$todo) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Data tidak ditemukan',
                    'data' => null
                ], 404);
            }

            if ($todo->user_id !== $request->user()->id) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Unauthorized',
                    'data' => null
                ], 401);
            }

            $todo->delete();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menghapus data',
                'data' => null
            ], 200);

        }catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
     }
    
}

