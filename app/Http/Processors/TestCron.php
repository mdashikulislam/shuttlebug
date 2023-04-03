<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/07/02
 * Time: 12:39 PM
 */

namespace App\Http\Processors;


use App\Notifications\WebmasterNotes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class TestCron
{
    public function handle()
    {
        $data = ['subj' => 'Testing Cron', 'msg' => 'The time now is '.Carbon::now()->toDateTimeString()];
        Notification::route('mail', 'webmaster@shuttlebug.co.za')->notify(new WebmasterNotes($data));
    }
}