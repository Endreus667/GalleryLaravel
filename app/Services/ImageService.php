<?php

namespace App\Services;

use App\Interfaces\ImageServiceInterface;
use App\Models\Image;
use Error;
use Exception;
use Illuminate\Support\Facades\Storage;

class ImageService implements ImageServiceInterface  {

    private $rollbackStack = null;

    public function storeNewImage($image, $title) : Image {
        try {
            $url =  $this->storeImageInDisk($image);
          return  $this->storeImageInDatabase($title, $url);
        } catch (Exception $e) {
            throw new Error('Erro ao gravar a imagem, Tente novamente.');
        }


    }

    public function deleteImageFromDisk($imageUrl): bool {
        echo '<- deleteImageFromDisk <br> ';
        $imagePath = str_replace(asset('storage/'), '', $imageUrl);
        Storage::disk('public')->delete($imagePath);
        return true;
    }

    public function deleteDataBaseImage($databaseImage): bool {
        echo '<- deleteDatabaseImage <br> ';
        if ($databaseImage) {
            $databaseImage->delete();
            return true;
        }
        return false;
    }
    public function rollback() {

        while (!empty($this->rollbackStack)) {
            $rollbackAction = array_pop($this->rollbackStack);

            // Verifique se rollbackAction é um array e contém os índices esperados
            if (is_array($rollbackAction) && isset($rollbackAction['method'], $rollbackAction['params'])) {
                $method = $rollbackAction['method'];
                $params = $rollbackAction['params'];

                if (method_exists($this, $method)) {
                    call_user_func_array([$this, $method], $params);
                } else {
                    // Log ou manipule o caso em que o método não existe
                    throw new Exception("Método {$method} não existe na classe.");
                }
            } else {
                // Log ou manipule o caso em que rollbackAction não tem a estrutura correta
                throw new Exception('Rollback action deve ser um array com chaves "method" e "params".');
            }
        }



      //queue->  // if(!empty($this->rollbackQueue)) {
        //     foreach($this->rollbackQueue as $interaction) {
        //         $method = $interaction['method'];
        //         $params = $interaction['params'];

        //         if(method_exists($this, $method)) {
        //             call_user_func_array([$this, $method], $params);
        //         }
        //     }

        // }

    }

    private function storeImageInDisk($image): string {
        echo'-> storeImageInDisk <br>';
        $imageName = $image->storePublicly('uploads', 'public');
        $url = asset('storage/' . $imageName);
        $this->addToRollbackQueue('deleteImageFromDisk',[$url]);
        return $url;
    }

    private function storeImageInDatabase($title, $url): Image {
    echo'-> storeImageInDatabase <br>';
        $image = Image::create([ // Retorna a instância do modelo
            'title' => $title,
            'url'=> $url,
        ]);
        $this->addToRollbackQueue('deleteDatabaseImage',[$image]);
        return $image;
    }

    private function addToRollbackQueue($method, $params =[]) {
        $this->rollbackStack[]=[
            'method' => $method,
            'params' => $params
        ];
    }

}
