<?php

namespace App\Services;

use Exception;
use App\Models\Task;
use App\Models\Attachment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Cache;
use App\Http\Traits\ModelActionsTrait;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AttachmentService {

     /**
     * ApiResponseTrait: trait customize the methods for successful , failed , authentecation responses.
     * ModelActionsTrait: trait customize the method model to stor all life cycle of task and all Error messages.
     */
    use ApiResponseTrait,ModelActionsTrait;
    /**
     * method to view all attachments 
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
     * @param   $task_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_Attachment($data,$task_id) {
        try {
            $file = $data['attachment'];
            $originalName = $file->getClientOriginalName();
            
            // Ensure the file extension is valid and there is no path traversal in the file name
            if (preg_match('/\.[^.]+\./', $originalName)) {
                //إضافة سجل خطأ إلى جدول ErrorTask
                $this->error('create Attachment','Attachment',null, Auth::id(), null ,trans('general.notAllowedAction'));
                throw new Exception(trans('general.notAllowedAction'), 403);
            }
            
            
            // Check for path traversal attack (e.g., using ../ or ..\ or / to go up directories)
            if (strpos($originalName, '..') !== false || strpos($originalName, '/') !== false || strpos($originalName, '\\') !== false) {
                //إضافة سجل خطأ إلى جدول ErrorTask                
                $this->error('create Attachment','Attachment',null, Auth::id(), null ,trans('general.pathTraversalDetected'));
                throw new Exception(trans('general.pathTraversalDetected'), 403);
            }
            
            // Validate the MIME type to ensure it's an file
            $allowedMimeTypes = ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/pdf', 'txt/plain'];
            $mime_type = $file->getClientMimeType();
          //  dd($mime_type);
            
            if (!in_array($mime_type, $allowedMimeTypes)) {
                //إضافة سجل خطأ إلى جدول ErrorTask
                $this->error('create Attachment','Attachment',null, Auth::id(), null ,trans('general.invalidFileType'));
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
            
            Cache::forget('all_task');

            $this->model('create Attachment', 'Attachment', $task_id, Auth::id(), $file);

            return $file;
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->failed_Response('Something went wrong with creating the attachment', 400);
        }
    }

}
