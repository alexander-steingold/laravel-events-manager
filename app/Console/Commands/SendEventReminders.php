<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends notifications to all event attendees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::with('attendees.user')
            ->whereBetween('start_time', [now(), now()->addDay()])
            ->get();
        $eventCount = $events->count();
        $eventLabel = Str::plural('event', $eventCount);

        $this->info("Found {$eventCount} {$eventLabel}.");
        //$this->info($events);
        $events->each(
            fn($event) => $event->attendees->each(
                fn($attendee) => $attendee->user->notify(
                    new EventReminderNotification(
                        $event
                    )
                )
            )
        );
//        $breakLoop = false;
//
//        $events->each(function ($event) use (&$breakLoop) {
//            $event->attendees->each(function ($attendee) use (&$breakLoop, $event) {
//                $attendee->user->notify(new EventReminderNotification($event));
//                $breakLoop = true; // Set the flag to break the loop
//                return false; // Optional: Signal to the inner loop to break early
//            });
//
//            if ($breakLoop) {
//                return false; // Signal to the outer loop to break
//            }
//        });
        $this->info('Reminder notification sent successfully');
    }
}
