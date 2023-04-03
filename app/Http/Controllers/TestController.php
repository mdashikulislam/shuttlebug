<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;

class TestController extends Controller{
    public function index()
    {
		dd(date('Y-m-d H:i:s'));
        $x = Mail::send('emails.test',[],function ($q){
            $q->from("webmaster@shuttlebug.co.za", 'ashik');
            $q->to('ashik.nwu@gmail.com', 'Ashikul Islam')->subject('Email Subject!');
        });
		dd($x);
    }

}
