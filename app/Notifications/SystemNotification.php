<?php

namespace App\Notifications;

class SystemNotification extends BaseNotification
{
    public function __construct($title, $message, $url = null, $type = 'info')
    {
        parent::__construct([
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'icon' => $this->getIconForType($type),
            'type' => $type
        ]);
    }

    private function getIconForType($type)
    {
        return match($type) {
            'success' => 'bi-check-circle-fill text-success',
            'warning' => 'bi-exclamation-triangle-fill text-warning',
            'danger' => 'bi-x-circle-fill text-danger',
            default => 'bi-info-circle-fill text-info'
        };
    }
}