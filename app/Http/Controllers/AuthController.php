<?php

namespace App\Http\Controllers;

use App\Models\ConfigGeneral;
use App\Models\LogResetPassword;
use App\Models\LogUsersUuid;
use App\Models\Provider;
use App\Models\User;
use App\Models\UserCashierInstance;
use App\Models\UsersGroups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private $throttle_config = [15, 30, 10];

    public function login(){
        $data['title'] = 'Login';
        return view('login', $data);
    }

    public function loginProcess(Request $req){
        if($this->checkLoginThrottle($req) === false) {
            return redirect()->back()->with(['error' => 'Anda telah gagal login terlalu banyak. Mohon tunggu ' . $this->throttle_config[2] . ' Detik untuk mecoba kembali.']);
        }

        $username = $req->username;
        $password = $this->generateHash($req->password);
        $dataUser = User::where('username',$username)->first();
        $now = date('Y-m-d H:i:s');
        $uuid = Str::random(15);

        $return = new \StdClass;

        if (!empty($dataUser)) {
            $provider = Provider::find($dataUser->provider_id);
            $logResetPass = LogResetPassword::where('username',$username)
                ->where('expired_date','>',date('Y-m-d H:i:s'))
                ->first();
            $tokenkey = Str::random(64);

            if (is_null($dataUser->tokenkey) || empty($dataUser->tokenkey)) {
                $dataUser->tokenkey = $tokenkey;
                $dataUser->update();
            }

            if (!is_null($req->migrate)) {
                if ($dataUser->password == $req->password) {
                    $passMatch = true;
                }else{
                    $passMatch = false;
                }
            }else{
                if ($dataUser->password == $password) {
                    $passMatch = true;
                }else{
                    $passMatch = false;
                }
            }

            $group = explode(',',env('GROUP_KASIR'));
            $anotherGroup = explode(',',env('GROUP_OTHER'));
            $financeGroup = env('GROUP_FINANCE');
            $farmasiGroup = env('GROUP_FARMASI');
            $provMigrate = ConfigGeneral::where('name','migrate_v5')->first()->value;
            $checkComp = $this->checkCompatibility($_SERVER['HTTP_USER_AGENT']);

            if ($passMatch) {
                if ($dataUser->group == $financeGroup) {
                    return redirect()->to(env('APP_URL_FINANCE').'migration-login-v5?username='.$username.'&password='.$req->password);
                }elseif (in_array($dataUser->group, $anotherGroup)) {
                    return redirect()->back()->with(['redirect' => "$username|$req->password"]);
                }elseif ($dataUser->group == $farmasiGroup) {
                    return redirect()->to(env('APP_URL_FARMASI').'login?username='.$username.'&password='.$req->password);
                } else {
                    if(in_array($dataUser->group, $group)){
                        if($provMigrate == 'all' || in_array($dataUser->provider_id, explode(',', $provMigrate))){
                            return redirect()->to(env('APP_URL_CLAIM').'login-migrate?username='.$username.'&password='.$req->password);
                        }else{
                            return redirect()->to(env('APP_URL_CLAIM_V4').'#/login?username='.$username.'&password='.$req->password);
                        }
                    }else{
                        return redirect()->back()->withInput()->with(['error' => 'Terjadi kesahalan saat login. Silakan hubungi Customer Care Medlinx']);
                    }
                }
            }elseif (!is_null($logResetPass) && $logResetPass->password_code == $password) {
                $logResetPass->first_login = date('Y-m-d H:i:s');
                $logResetPass->modified_by = $dataUser->fullname;
                $logResetPass->modified_date = date('Y-m-d H:i:s');
                $logResetPass->update();

                if ($dataUser->group == $financeGroup) {
                    return redirect()->to(env('APP_URL_FINANCE').'migration-login-v5?username='.$username.'&password='.$req->password);
                }else{
                    $this->logUuidLogin($uuid,$provider->merchant_id);
                    if($provMigrate == 'all' || in_array($dataUser->provider_id, explode(',', $provMigrate))){
                        return redirect()->to(env('APP_URL_CLAIM').'login-migrate?username='.$username.'&password='.$req->password);
                    }else{
                        return redirect()->to(env('APP_URL_CLAIM_V4').'#/login?username='.$username.'&password='.$req->password);
                    }
                }
            }else{
                $this->logFailedLogin($req);
                return redirect()->back()->withInput()->with(['error' => 'Username dan Password tidak cocok']);
            }
        } else {
            $this->logFailedLogin($req);
            return redirect()->back()->withInput()->with(['error' => 'Username dan Password tidak cocok']);
        }
    }

    private function generateHash($str,$salt='c0d3i6n1t3r200fm3',$length=6){
        if($salt === null){
            $salt=substr(md5(uniqid(rand(),true)),0,$length);
        }else{
            $salt=substr($salt,0,$length);
        }
        return $salt.sha1($salt.$str);
    }

    private function checkLoginThrottle(Request $req) {
        list($max, $threshold_seconds, $next_try_seconds) = $this->throttle_config;
        
        $identifier = md5($req->ip());
        $file = date('Ymd') . '_' . $identifier;
        $path = storage_path() . '/app/throttle/';
        $banned_path = 'banned/';
        if(!file_exists($path . $banned_path)) mkdir($path . $banned_path, 0777, true);

        $dt = new \DateTime();

        $banned_file = $path . $banned_path . $identifier;
        if(file_exists($banned_file)) {
            $banned_time = (int) file_get_contents($banned_file);
            if(($dt->getTimestamp() - $banned_time) <= $next_try_seconds) false;
        }

        if(!file_exists($path . $file)) return true;

        $contents = file($path . $file);
        $counter = 0;
        for($i=count($contents)-1;$i>0;$i--) {
            $json = json_decode($contents[$i]);
            if(($dt->getTimestamp() - $json->time) <= $threshold_seconds) {
                $counter++;
            }
        }
        
        if($counter > $max) {
            $time = new \DateTime();
            $time->modify("+{$next_try_seconds} seconds");
            file_put_contents($banned_file, $time->getTimestamp());
            return false;
        }
        
        return true;
    }

    private function checkCompatibility($data){
        $browser = self::getBrowser($data);
        $status = false;
        switch ($browser[0]) {
            case 'Firefox':
                if (floatval(substr($browser[1], 0,3)) >= 45) {
                    $status = true;
                }
                break;
            case 'Chrome':
                if (floatval(substr($browser[1], 0,3)) >= 49) {
                    $status = true;
                }
                break;
            case 'Edge':
                if (floatval(substr($browser[1], 0,3)) >= 1.7) {
                    $status = true;
                }
                break;
            case 'Safari':
                $status = true;
                break;
            default:
                $status = false;
                break;
        }

        return $status;
    }
    
    private function getBrowser($user_agent) {
        $browser      = ["Unknown Browser","00"];
        $browser_array = array(
            '/msie/i'      => 'Internet Explorer',
            '/firefox/i'   => 'Firefox',
            '/safari/i'    => 'Safari',
            '/chrome/i'    => 'Chrome',
            '/edge/i'      => 'Edge',
            '/opera/i'     => 'Opera',
            '/netscape/i'  => 'Netscape',
            '/maxthon/i'   => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i'    => 'Handheld Browser'
        );
        foreach ($browser_array as $regex => $value){
            if (preg_match($regex, $user_agent)){
                $a = strpos($user_agent,$value);
                $b = substr($user_agent,$a+strlen($value.'/'));
                $c = substr($b,0,strrpos($b,' '));
                $browser = [$value,(empty($c) ? $b : $c)];
            }
        }

        return $browser;
    }

    private function logFailedLogin(Request $req) {
        $file = date('Ymd') . '_' . md5($req->ip());
        $path = storage_path() . '/app/throttle/';
        if(!file_exists($path)) mkdir($path);

        $content = '';

        if(!file_exists($path . $file)) 
            $content .= $req->ip() . PHP_EOL;

        $data = json_encode([
            'time' => (int) date('U'),
            'user' => $req->username,
            'pass' => $req->password,
        ]);

        $content .= $data . PHP_EOL;
        file_put_contents($path . $file, $content, FILE_APPEND);

        // cleaning old file
        $files = array_diff(scandir($path), ['.', '..']);
        $ctime = new \DateTime();
        foreach($files as $f) {
            if(preg_match("/^([0-9]{8})\_/", $f, $m) == 1) {
                $ftime = substr($m[1], 0, 4) .'-'. substr($m[1], 4, 2) .'-'. substr($m[1], 6, 2);
                $ftime = new \DateTime(date('Y-m-d', strtotime($ftime)));

                // 7 days file keep
                if($ctime->diff($ftime)->days > 7) {
                    unlink($path . $f);
                }
            }
        }
    }

    public function forgotPassword(Request $request){
        $userData = User::where('username', $request->username)->first();

        $resp = [
            'status' => true,
            'message' => '',
        ];

        if (!$userData) {
            $resp = [
                'status' => false,
                'message' => 'Username tidak terdaftar',
            ];
        } else {
            $userData = $userData->toArray();
            $provider = Provider::find($userData['provider_id']);
            if (!is_null($provider)) {
                $provider = $provider->name;
            } else {
                $return = [
                    'type' => 'error',
                    'status' => false,
                    'desc' => 'Username tidak terdaftar',
                ];
                return response()->json($return);
            }
            
            if (!empty($userData['instansi_id'])) {
                $instansi = UserCashierInstance::where('code',$userData['instansi_id'])->first()->name;
            }
            $receiver = User::select('email')->where('provider_id',$userData['provider_id'])->where('group',6)->get();
            foreach ($receiver as $rec) {
                if ($rec->email != '') {
                    $emailRec = explode(';',$rec->email);
                    
                    if(count($emailRec) > 1){
                        foreach ($emailRec as $key => $er) {
                            $emailReceiver[] = trim($er);
                        }
                    }else{
                        $emailReceiver[] = trim($emailRec[0],' ');
                    }

                    foreach ($emailReceiver as $key => $mailRec) {
                        $as = '*';
                        $email = explode('@',$mailRec);
                        if (strlen($email[0]) == 1) {
                            $em = $email[0];
                        }elseif (strlen($email[0]) == 2) {
                            $em = substr_replace($email[0],$as,1,1);
                        }elseif (strlen($email[0]) >= 3 && strlen($email[0]) <= 5) {
                            for($i=3;$i<strlen($email[0]);$i++){
                                $as .= '*'; 
                            }
                            $em = substr_replace($email[0],$as,2,$i);
                        }else{
                            for($i=4;$i<strlen($email[0]);$i++){
                                $as .= '*'; 
                            }
                            $em = substr_replace($email[0],$as,3,$i);
                        }
    
                        $as = '*';
                        if (strlen($email[1]) == 1) {
                            $em = $email[1];
                        }elseif (strlen($email[1]) == 2) {
                            $eh = substr_replace($email[1],$as,0,1);
                        }elseif (strlen($email[1]) >= 3 && strlen($email[1]) <= 5) {
                            for($i=1;$i<strlen($email[1])-2;$i++){
                                $as .= '*'; 
                            }
                            $eh = substr_replace($email[1],$as,0,$i);
                        }else{
                            for($i=1;$i<strlen($email[1])-5;$i++){
                                $as .= '*'; 
                            }
                            $eh = substr_replace($email[1],$as,0,$i);
                        }
                        
                        $retEmail[] = $em.'@'.$eh;
                    }
                }
            }

            switch($userData['group']){
                case 5:
                    $lokasi = 'Kasir';
                    break;
                case 6:
                    $lokasi = 'Marketing';
                    break;
                case 10:
                    $lokasi = 'Report';
                    break;
                case 8:
                    $lokasi = 'Admin';
                case 14:
                    $lokasi = 'RKI';
                    break;
            }
            if(isset($lokasi)){
                $lokasi = ucfirst($lokasi).' '.$instansi;
            }else{
                $lokasi = '';
            }

            $tmpPass = Str::camel(substr(sha1(date('Y-m-d H:i:s')),-5)."_".Str::random(5));
            $newPass = $this->generateHash($tmpPass);
            $hours = ConfigGeneral::where('name','expired_hours_forget_password')->first()->value;

            $data = array(
                'request_to'    => '0',
                'user_id'       => $userData['id'],
                'group_id'      => $userData['group'],
                'provider_id'   => $userData['provider_id'],
                'username'      => $request->username,
                'group'         => UsersGroups::find($userData['group'])->name,
                'provider'      => Provider::find($userData['provider_id'])->name,
                'ip'            => $request->getClientIp(),
                'publish'       => '1',
                'created_by'    => $userData['fullname'],
                'created_date'  => date("Y-m-d H:i:s"),
                'password_code' => $newPass,
                'expired_date'  => date("Y-m-d H:i:s", strtotime(sprintf("+%d hours", $hours)))
            );

            $dataEmail = [
                'provider' => $provider,
                'username' => $request->username,
                'lokasi' => $lokasi,
                'tmpPass' => $tmpPass,
                'expired_date' => $data['expired_date'],
            ];

            Mail::send('mail.forgot_password', ['data' => $dataEmail], function ($message) use ($emailReceiver) {
                $message->from(env('MAIL_FROM'), env('MAIL_FROM_ALIAS'));
                $message->to($emailReceiver)->cc(['customercare@medlinx.co.id','marketing@medlinx.co.id'])
                    ->subject("Pemberitahuan Password Baru iziklaim");
            });

            $logResetPassword = LogResetPassword::where('username',$data['username'])->where('expired_date','>',date('Y-m-d H:i:s'))->first();
            if(!is_null($logResetPassword)){
                $logResetPassword->expired_date = date("Y-m-d H:i:s");
                $logResetPassword->modified_by = $userData['fullname'];
                $logResetPassword->modified_date = date("Y-m-d H:i:s");
                $logResetPassword->update();
            }

            $user = User::where('id',$userData['id'])->first();
            $user->request_lupa_password = $data['created_date'];
            $user->update();

            DB::table('log_reset_password')->insert($data);

            $resp = [
                'status' => true,
                'email' => $retEmail,
            ];
        }

        return $resp;
    }

    private function logUuidLogin($uuid, $mid){
        try {
            $newLogin = new LogUsersUuid();
            $newLogin->uuid = $uuid;
            $newLogin->mid = $mid;
            $newLogin->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
