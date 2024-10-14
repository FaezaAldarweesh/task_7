<?php

namespace App\Http\Services;

use Exception;
use App\Models\Attachment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AttachmentService {

    //trait customize the methods for successful , failed , authentecation responses.
    use ApiResponseTrait;
    /**
     * method to view all attachments 
     * @param   Request $request
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function get_all_Attachments(){
        try {
            $attachment = Attachment::where('created_by','=', Auth::id())->get();
            return $attachment;
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with fetche attachments', 400);}
    }
//========================================================================================================================
    /**
     * method to store a new attachment
     * @param   $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_Attachment($data,$task_id) {
        try {
            $file = $data['attachment'];
            $originalName = $file->getClientOriginalName();
    
            // Ensure the file extension is valid and there is no path traversal in the file name
            if (preg_match('/\.[^.]+\./', $originalName)) {
                throw new Exception(trans('general.notAllowedAction'), 403);
            }
    
    
            // Check for path traversal attack (e.g., using ../ or ..\ or / to go up directories)
            if (strpos($originalName, '..') !== false || strpos($originalName, '/') !== false || strpos($originalName, '\\') !== false) {
                throw new Exception(trans('general.pathTraversalDetected'), 403);
            }
    
            // Validate the MIME type to ensure it's an file
            $allowedMimeTypes = ['file/doc', 'file/docx', 'file/zip', 'file/pdf', 'file/text'];
            $mime_type = $file->getClientMimeType();
    
            if (!in_array($mime_type, $allowedMimeTypes)) {
                throw new FileException(trans('general.invalidFileType'), 403);
            }
    
            // Generate a safe, random file name
            $fileName = Str::random(32);
    
            $extension = $file->getClientOriginalExtension(); // Safe way to get file extension
            $filePath = "files/{$fileName}.{$extension}";
    
            // Store the file securely
            $path = Storage::disk('local')->put($filePath, $file, $fileName . '.' . $extension);
    
            // Store file metadata in the database
            $file = Attachment::create([
                'name' => $originalName,
            ]);
    
            return $file;

        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->failed_Response('Something went wrong with creating the attachment', 400);
        }
    }
//========================================================================================================================
    /**
     * method to update attachment alraedy exist
     * @param  $data
     * @param  Attachment $attachment
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function update_attachment($data,$attachment_id){
        try {  

        } catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);
        }catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with view attachment', 400);}
    }
//========================================================================================================================
    /**
     * method to delete attachment alraedy exist
     * @param  Attachment $attachment
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function delete_attachment($attachment_id)
    {
        try {  

        }catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with deleting attachment', 400);}
    }
//========================================================================================================================

}
