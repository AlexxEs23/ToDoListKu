<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ToDo;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    public function index(Request $request){
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'tanggal_selesai');
        $sortDir = $request->input('sort_dir', 'asc');

        $query = ToDo::with('categories');

        if(!Auth::user()){
            return redirect('/login')->with('error', 'You must be logged in to view your tasks.');
        }

        $query->where('user_id', Auth::id());

        if($request->has('category_id') && !empty($request->category_id)){
            $query->where('categories', function($q) use ($request) {
                $q->where('categories_id', $request->category_id);
            }); 
        }

        if(!empty($search)){
            $query->where(function($q) use ($search) {
                $q->where('judul_tugas', 'like', '%'.$search.'%')
                  ->orWhere('deskripsi_tugas', 'like', '%'.$search.'%');
            });
        }
        $query->orderByRaw("CASE WHEN status = 'selesai' THEN 1 ELSE 0 END ASC")
              ->orderByRaw("CASE WHEN status = 'selesai' THEN tanggal_selesai END DESC")
              ->orderBy($sortBy, $sortDir);

        $todos = $query->paginate($perPage)->withQueryString();
        $categories = \App\Models\Category::all();
        return view('index', compact('todos', 'categories', 'search', 'sortBy', 'perPage', 'sortDir'));
    }

    function addTodo(Request $request){
        if(!Auth::user()){
            return redirect('/login')->with('error', 'You must be logged in to add a task.');
        }
        $request->validate([
            'judul_tugas' => 'required|max:255',
            'deskripsi_tugas' => 'required',
            'tanggal_selesai' => 'required|date',
            'status' => 'required|in:belum_dikerjakan,proses,selesai',
            // 'categories' => 'array', // opsional, jika ingin validasi array kategori
        ]);

        $todo = new ToDo();
        $todo->user_id = Auth::id();
        $todo->judul_tugas = $request->input('judul_tugas');
        $todo->deskripsi_tugas = $request->input('deskripsi_tugas');
        $todo->tanggal_selesai = $request->input('tanggal_selesai');
        $todo->status = $request->input('status');
        $todo->save();

        if($request->has('categories')){
            $todo->categories()->sync($request->categories);
        } else {
            $todo->categories()->detach();
        }

        if($todo){
            return redirect()->back()->with('success', 'Task added successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to add task.');
        }
    }

    function updateTodo(Request $request, $id){
        $todo = ToDo::find($id);
        if(!$todo){
            return redirect('/login')->back()->with('error', 'Task not found.');
        }else if($todo->user_id != Auth::id()){
            return redirect('/login')->back()->with('error', 'You do not have permission to edit this task.');
        }


        $todo->judul_tugas = $request->input('judul_tugas');
        $todo->deskripsi_tugas = $request->input('deskripsi_tugas');
        $todo->tanggal_selesai = $request->input('tanggal_selesai');
        $todo->status = $request->input('status');
        $todo->save();
        
        if($request->has('categories')){
            $todo->categories()->sync($request->categories);
        }else{
            $todo->categories()->detach();
        }if($todo){
            return redirect()->back()->with('success', 'Task updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to update task.');
        }
    }

    function deleteTodo($id){
        $todo = ToDo::find($id);
        if(!$todo){
            return redirect()->back()->with('error', 'Task not found.');
        }else if($todo->user_id != Auth::id()){
            return redirect()->back()->with('error', 'You do not have permission to delete this task.');
        }

        $todo->categories()->detach();
        $todo->delete();
        return redirect()->back()->with('success', 'Task deleted successfully.');
    }

    public function completeTodo($id) {
        $todo = ToDo::find($id);
        if(!$todo){
            return redirect()->back()->with('error', 'Task not found.');
        } else if($todo->user_id != Auth::id()){
            return redirect()->back()->with('error', 'You do not have permission to complete this task.');
        }
        $todo->status = 'selesai';
        $todo->tanggal_selesai = now();
        $todo->save();
        return redirect()->back()->with('success', 'Tugas berhasil diselesaikan!');
    }
}
