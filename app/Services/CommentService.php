<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Request;

class CommentService {
    //trait customize the methods for successful , failed , authentecation responses.
    use ApiResponseTrait;
    /**
     * method to view all comments 
     * @param   Request $request
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function get_all_Comments(){
        try {
            $comment = Comment::where('created_by','=', Auth::id())->get();
            return $comment;
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with fetche comments', 400);}
    }
//========================================================================================================================
    /**
     * method to store a new comment
     * @param   $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_Comment($data,$id) {
        try {
            $task = Task::where('id','=',$id)->first();

            $comment = $task->comments()->create([
                'created_by' => Auth::id(),
                'comment' => $data['comment']
            ]);

            return $comment;
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->failed_Response('Something went wrong with creating the comment', 400);
        }
    }
//========================================================================================================================
    /**
     * method to update comment alraedy exist
     * @param  $data
     * @param  Comment $comment
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function update_comment($data,$comment_id){
        try {  
            $comment = Comment::where('id','=',$comment_id)->first();

            if($comment->created_by == Auth::id()){
                $comment->update($data);
            }else{
                throw new \Exception('comment dose not belongs to you');
            }

            return $comment;
        } catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);
        }catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with view comment', 400);}
    }
//========================================================================================================================
    /**
     * method to delete comment alraedy exist
     * @param  Comment $comment
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function delete_comment($comment_id)
    {
        try {  
            $comment = Comment::find($comment_id);
            if(!$comment){
                throw new \Exception('comment not found');
            }elseif($comment->created_by != Auth::id()){
                throw new \Exception('comment dose not belongs to you');
            }

            $comment->delete();

            return true;
        }catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with deleting comment', 400);}
    }
//========================================================================================================================

}
