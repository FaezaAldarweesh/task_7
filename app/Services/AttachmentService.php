<?php

namespace App\Services;

use Exception;
use App\Models\Task;
use App\Models\Attachment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\AttachmentResources;
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
            $allowedMimeTypes = ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/pdf', 'txt/plain'];
            $mime_type = $file->getClientMimeType();
          //  dd($mime_type);
            
            if (!in_array($mime_type, $allowedMimeTypes)) {
                throw new FileException(trans('general.invalidFileType'), 403);
            }
            
            // Generate a safe, random file name
            $fileName =  Str::random(32) ;//. '.' .'jpg'
    
            $extension = $file->getClientOriginalExtension(); // Safe way to get file extension
            $filePath = "files/{$fileName}.{$extension}";
    
            // Store the file securely
            $path = Storage::disk('local')->put($filePath, file_get_contents($file));
    
            // Store file metadata in the database
            $task = Task::where('id','=',$task_id)->first();

            $file = $task->attachments()->create([
                'created_by' => Auth::id(),
                'name' => $fileName
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
    public function update_attachment($data, $attachment_id) {
        try {
            $attachment = Attachment::findOrFail($attachment_id);
    
            if ($attachment->created_by == Auth::id()) {
                if (isset($data['attachment'])) {
                    $file = $data['attachment'];
                    $originalName = $file->getClientOriginalName();
    
                    // Ensure the file extension is valid and there is no path traversal in the file name
                    if (preg_match('/\.[^.]+\./', $originalName)) {
                        throw new Exception(trans('general.notAllowedAction'), 403);
                    }
    
                    // Check for path traversal attack
                    if (strpos($originalName, '..') !== false || strpos($originalName, '/') !== false || strpos($originalName, '\\') !== false) {
                        throw new Exception(trans('general.pathTraversalDetected'), 403);
                    }
    
                    // Validate the MIME type
                    $allowedMimeTypes = ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/pdf', 'txt/plain'];
                    $mime_type = $file->getClientMimeType();
    
                    if (!in_array($mime_type, $allowedMimeTypes)) {
                        throw new FileException(trans('general.invalidFileType'), 403);
                    }
    
                    // Check if the new file is the same as the old file
                    if ($originalName !== $attachment->name) {
                        // Generate a safe, random file name
                        $fileName = Str::random(32);
                        $extension = $file->getClientOriginalExtension();
                        $filePath = "files/{$fileName}.{$extension}";
    
                        // Store the new file
                        Storage::disk('local')->put($filePath, file_get_contents($file));
    
                        // Delete the old file if it exists
                        $oldFilePath = 'files/'.$attachment->name;
                        //dd($oldFilePath);
                        if (Storage::disk('local')->exists($oldFilePath)) {
                            Storage::disk('local')->delete($oldFilePath);
                        }
    
                        // Update attachment record
                        $attachment->update(['name' => $fileName]);
                    }
    
                    return $attachment;
                }
            }
    
            return $this->failed_Response('Unauthorized or no file provided', 403);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->failed_Response($th->getMessage(), 400);
        }
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
            $attachment = Attachment::findOrFail($attachment_id);
    
            // حدد المسار الصحيح للملف
            $filePath = storage_path('storage/app/files/' . $attachment->name);
    
            // تحقق مما إذا كان الملف موجودًا باستخدام Storage بدلاً من is_file
            if (Storage::disk('local')->exists('storage/app/files/' . $attachment->name)) {
                // استخدم Storage لحذف الملف من التخزين
                Storage::disk('local')->delete('storage/app/files/' . $attachment->name);
            } else {
                Log::warning("File does not exist: " . $filePath); // قم بتسجيل تحذير في حالة عدم وجود الملف
            }
    
            // احذف السجل من قاعدة البيانات
            $attachment->delete();
    
            return true;
    
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->failed_Response($e->getMessage(), 400);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->failed_Response('Something went wrong with deleting attachment', 400);
        }
    }
    
//========================================================================================================================

}
