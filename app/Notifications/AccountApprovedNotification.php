<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Fit Flow Account Has Been Approved!')
            ->greeting("Welcome to Fit Flow, {$notifiable->name}!")
            ->line('Great news — your account has been reviewed and approved.')
            ->line('You can now sign in and access all programs, track your health metrics, and connect with your trainer.')
            ->action('Sign In to Fit Flow', url('/login'))
            ->line('We are excited to have you on board!')
            ->salutation('— The Fit Flow Team');
    }
}
