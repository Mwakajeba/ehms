<?php

namespace App\Services;

use App\Models\ParentNotification;
use App\Models\School\Guardian;
use App\Models\School\Student;
use Illuminate\Support\Facades\Log;

class ParentNotificationService
{
    /**
     * Send notification to all parents of a student
     */
    public function notifyStudentParents(Student $student, string $type, string $title, string $message, array $data = []): void
    {
        $parents = $student->guardians()->get();
        
        foreach ($parents as $parent) {
            $this->createNotification($parent, $student, $type, $title, $message, $data);
        }
    }

    /**
     * Send notification to a specific parent
     */
    public function notifyParent(Guardian $parent, ?Student $student, string $type, string $title, string $message, array $data = []): void
    {
        $this->createNotification($parent, $student, $type, $title, $message, $data);
    }

    /**
     * Create notification record
     */
    private function createNotification(Guardian $parent, ?Student $student, string $type, string $title, string $message, array $data = []): void
    {
        try {
            ParentNotification::create([
                'parent_id' => $parent->id,
                'student_id' => $student?->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'is_read' => false,
            ]);

            // TODO: Send push notification via FCM
            // $this->sendPushNotification($parent, $title, $message, $data);
        } catch (\Exception $e) {
            Log::error('Failed to create parent notification', [
                'parent_id' => $parent->id,
                'student_id' => $student?->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send push notification via FCM (to be implemented)
     */
    private function sendPushNotification(Guardian $parent, string $title, string $message, array $data = []): void
    {
        // TODO: Implement FCM push notification
        // This will require:
        // 1. Store FCM tokens in guardians table or separate table
        // 2. Use Firebase Cloud Messaging to send notifications
        // 3. Handle notification payload
    }
}

