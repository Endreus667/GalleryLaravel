<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Services\ImageService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use function Termwind\parse;

class GalleryController extends Controller
{

    protected $imageService;

    public function __construct(ImageService $imageService) {
       $this->imageService = $imageService;
    }

    public function index() {
        $images = Image::all();
        return view("index", ['images' => $images]);
    }


   public function upload(Request $request) {

    $this->validateRequest($request);

        $title = $request->only('title');
        $image = $request->file('image');

        try{

        //     $url = $this->imageService->storeImageInDisk($image);
        //   $databaseImage =  $this->imageService->storeImageInDataBase($title['title'],$url);

         $this->imageService->storeNewImage($image, $title['title']);
        //throw new Exception('....');
        } catch(Exception $error) {
            // if (isset($databaseImage)) {
            //     $this->imageService->deleteDataBaseImage($databaseImage);
            //     $this->imageService->deleteImageFromDisk($databaseImage->url);
            // }

            $this->imageService->rollback();

            return redirect()->back()->withErrors(['error'=>'Erro ao salvar a imagem. Tente novamente!']);
        }

    return redirect()->route('index');

   }

   public function delete($id) {
        $image = Image::findOrFail($id);
        $url = parse_url($image->url);
        $path = ltrim($url['path'], '/storage\/');


        if(Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            $image->delete();
        }

        return redirect()->route('index');
   }

   private function validateRequest(Request $request) {
    $request->validate([
        'title' => 'required|string|max:255|min:6',
        'image'=> [
            'required',
            'image',
            'mimes:jpeg,png,jpg,gif,webp',
            'max:2048', //2mb
            Rule::dimensions()->maxWidth(2000)->maxHeight(2000),
        ]
    ]);
   }







}
