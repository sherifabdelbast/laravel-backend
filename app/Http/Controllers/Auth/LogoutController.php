<?php
namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
class LogoutController extends Controller
{
    public function destroy(){
        $user= Auth::guard('customer');
            $user->logout();
            return response(null,204);
    }
}
