<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\NotificationDetail;
use App\Services\Notification\MailService;
use App\Services\NotificationService;

class TestController extends Controller
{
    public function send()
    {
        MailService::sendMail(
            'kevini9797@mustaer.com',
            'Bienvenue dans le projet',
            'emails.notification',
            [
                'title' => 'Bienvenue',
                'content' => 'Merci de rejoindre le projet Laravel Fondation Zakoura.',
                'subject' => 'Onboarding'
            ]
        );

        return "Email envoyé";
    }
    public function testNotification(Request $request)
    {
        $user = $request->user();
    
        if (!$user->collaborator) {
            return response()->json([
                'message' => 'user is not a collaborator',
            ], 400);
        }
        $positionName = "Admin SI";
      $collaborators = Collaborator::whereHas('position', function($query) use ($positionName) {
    $query->where('title', $positionName);
})->get();

$recipientIds = $collaborators->pluck('id')->toArray();

$notification = NotificationService::save(
    "You have been added to a new project",
    "{$user->collaborator->first_name} {$user->collaborator->last_name} added you to a new project",
    $user->collaborator->id, 
    [
        'type' => 'open_modal',
        'name' => 'view_project',
        'id' => 1
    ],
    $recipientIds 
);
        return response()->json($notification);
    }
 }
