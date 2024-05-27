<?php

namespace App\Http\Controllers;

// ------------- yang di tambahkan ---------

//import Model "Post
use App\Models\Post;

//return type View
use Illuminate\View\View;


// ------------- tambahan cread dan store ---------------------

//return type redirectResponse
use Illuminate\Http\RedirectResponse;

// ------------- tambahan cread dan store ---------------------


// ------------- tambahan untuk kebutuhan edit, update dan delete -------------

// --- facades storage adalah untuk hapus gambar yang ada di storage
//import Facade "Storage"
use Illuminate\Support\Facades\Storage;

// ------------- tambahan untuk kebutuhan edit, update dan delete -------------


// ------------- yang di tambahkan ---------

use Illuminate\Http\Request;

class PostController extends Controller
{
	
	// ----------------- yang di tambahkan -------------
    
	// ----------------- tampilan method index ----------
	/**
     * index
     *
     * @return View
     */
	// method baru dengan nama index
    public function index(): View
    {
		//tips !
		// --- membuat variabel Post
		// --- beri model post yang mengambil data dari database
		// --- memanggil method letest menampilkan data dengan urutan terbaru
		// --- memanggil method paginate untuk membatasi data yang tampil sejumlah (5)
        
		//get posts 
        $posts = Post::latest()->paginate(5);

		//tips !
		// --- setelah data berhasil di tampung di dalam variabel $posts
		// --- mengirimkan variabel tersebut kedalam view menggunakan method campact
        
		//render view with posts
        return view('posts.index', compact('posts'));
    }
	// ----------------- tampilan method index -------------------------
	
	// ----------------- tampilan method cread dan store ----------------------
    /**
     * create
     *
     * @return View
     */
	// --- method create
    public function create(): View
    {
        return view('posts.create');
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
	// --- store
    public function store(Request $request): RedirectResponse
    {	
		// validasi data sesuai dengan yang diharapkan
        //validate form
        $this->validate($request, [
            'image'     => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title'     => 'required|min:5',
            'content'   => 'required|min:10'
        ]);
		
		// Tips !
		// --- buat variabel image dan melakukan request dengan file yang bernama image
		// --- request tersebut merupakan file yang dikirim dari form
		// --- upload gambar dengan method storeAs bawaan dari laravel
		// --- dan akan di simpan ke dalam folder storage/app/public/posts
		// --- file gambar akan di random dengan menggunakan method hashname
		
        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());
		
		// Tips !
		// --- memanfaatkan eloquent dan model dalam proses insert data

        //create post
        Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content
        ]);
		
		// Tips !
		// --- mengarahkan/redirect ke route yang bernama posts.index
		// --- memberikan session flash yang memiliki key success
		// --- dan isi session flash nya adalah data berhasil disimpan
		
        //redirect to index
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }
	// ----------------- tampilan method cread dan store ------------------------
	
	// ----------------- tampilan method show -----------------------------------
	 /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
	 // membuat method show dan di dalamnya diberi param id dan akan bernilai dinamis sesuai id data yang di panggil
    public function show(string $id): View
    {
        // get post by ID
		// get post berdasarkan param id
        $post = Post::findOrFail($id);

        // render view with post
		// jika data berhasil ditemukan maka akan di kirim ke view menggunakan compact
        return view('posts.show', compact('post'));
    }
	// ------------------ tampilan method show ---------------------------------
	
	// ------------------ tampilan untuk edit dan update -----------------------
	/**
     * edit
     *
	 * tujuan nya sama dengan show yaitu untuk menampilkan detail data berdasarkan id
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        //get post by ID
        $post = Post::findOrFail($id);

        //render view with post
        return view('posts.edit', compact('post'));
    }
	
    /**
     * update
     * 
	 * untuk memproses data yang akan di update
     * @param  mixed $request
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
		//sama hal nya dengan method store hanya saja kita tidak menggunakan required di image
		//karena image tidak wajib di ubah
        //validate form
        $this->validate($request, [
            'image'     => 'image|mimes:jpeg,jpg,png|max:2048',
            'title'     => 'required|min:5',
            'content'   => 'required|min:10'
        ]);

		//mencari data berdasarkan id yang diambil dari param
        //get post by ID
        $post = Post::findOrFail($id);

		//cek kondisi untuk memastikan apakah ada required file yang bernama image
        //check if image is uploaded
        if ($request->hasFile('image')) {

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/'.$post->image);
			
			//update daata ke database dengan menyertakan gambar baru
            //update post with new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content
            ]);

        } else {

            //update post without image
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        }

		//kita arahkan/redirect ke route yang bernama posts.index dengan menambahkan session flash dan dengan key success dan memiliki value data berhasil diubah 
        //redirect to index
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Diubah!']);
    }	
	// ------------------ tampilan untuk edit dan update -----------------------
	
	// ------------------ tampilan untuk delete --------------------------------
	/**
     * destroy
     *
	 * pada param kita berikan id
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        //get post by ID
        $post = Post::findOrFail($id);

		//delete data di dalam storage/app/public/posts
        //delete image
        Storage::delete('public/posts/'. $post->image);

		//delete data di dalam database post
        //delete post
        $post->delete();
		
		//redirect/arahkan ke posts.index dengan memberikan session flash dengan key success dan value data berhasil dihapus
        //redirect to index
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
	// ------------------ tampilan untuk delete --------------------------------
	
	// ----------------- yang di tambahkan --------------	
}
