<?php

namespace App\Repositories;

use App\Models\Feedback;

class FeedbackRepository{
    public function saveOrUpdate(Feedback $feedback){
        $feedback->save();
        return Feedback::with(['user'])->find($feedback->id);
    }

    public function getByById($id){
        return Feedback::where('id', $id)->first();
    }
    public function delete($id){
        Feedback::destroy($id);
    }
}