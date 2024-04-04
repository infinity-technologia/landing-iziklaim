<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class LandingController extends Controller
{
    public function index(){
       
        $apiUrl = env('API_BE_URL')."api/v1/iziklaim";
        $data['title'] = 'Home';
        $response = Http::get($apiUrl);
        if ($response->successful()) {
            $res = $response->json();
            $data += $res['data'];
            // dd($data);
            return view('landing', $data);
        }else{
            return 'API NOT WORKING';
        }
    }
    public function newsDetail($slug){
        $apiUrl = env('API_BE_URL').'api/v1/news/'.$slug;
        $data['title'] = 'News Detail';
        $response = Http::get($apiUrl);
        if ($response->successful()) {
            $res = $response->json();
            $data += $res['data'];
            return view('landing.news-detail', $data);
        }else{
            return 'API NOT WORKING';
        }
    }
    public function newsAll(){
        $apiUrl = env('API_BE_URL')."api/v1/iziklaim";
        $data['title'] = 'News & Updates';
        $response = Http::get($apiUrl);
        if ($response->successful()) {
            $res = $response->json();
            $data += $res['data'];
            return view('landing.news-all', $data);
        }else{
            return 'API NOT WORKING';
        }
    }

    public function formDownloadProfile(){
        $data['title'] = 'Download Company Profile';
        return view('side.company-profile', $data);
    }

    public function sendMail(Request $request, $form){
        if($form == 'download-profile'){
            $validator = Validator::make($request->all(), [
                'fullname' => 'required|string',
                'handphone' => 'required|string',
                'email' => 'required|email',
            ]);
            $mailSubject = 'Permintaan Dokumen Profil Perusahaan';
        }elseif ($form == 'hubungi-kami') {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'institusi' => 'required',
                'subjek' => 'required',
                'pesan' => 'required',
            ]);
            $mailSubject = 'Pesan Hubungi Kami Website iziklaim';
        }
        
        if ($validator->fails()) {
            $errMessage = $validator->errors()->first();
            if($errMessage == 'The g-recaptcha-response field is required.'){
                $errMessage = 'Captcha harus diceklis';
            }

            $resp = [
                'status' => false,
                'message' => $errMessage
            ];
            return response()->json($resp);
        }

        $receiver = env('MAIL_RECIPIENT');
        $cc = env('MAIL_CC');

        try {
            Mail::send('mail.'.$form, $request->all(), function ($msg) use ($receiver, $cc, $mailSubject) {
                $msg->from(env('MAIL_FROM'), env('MAIL_FROM_ALIAS'));
                $msg->to(explode(',',$receiver))->cc($cc)->subject($mailSubject);
            });

            $resp = [
                'status' => true,
                'message' => ''
            ];
        } catch (\Throwable $th) {
            $resp = [
                'status' => false,
                'message' => $th->getMessage()
            ];
        }

        return response()->json($resp);
    }

    public function tnc(){
        $data['title'] = 'Term and Conditions';
        return view('side.tnc', $data);
    }

    public function faq(){
        $data['title'] = 'Frequently Asked Questions';
        return view('side.faq', $data);
    }
}
