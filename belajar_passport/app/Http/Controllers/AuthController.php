<?php
 
namespace App\Http\Controllers\Api\V1;
use App\User;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
 
class AuthController extends Controller
{
    /**
     * @register function
     * body: id,username,email,password,re_password,role
     * response: username,token
     */
    public function register(Request $request){
        //validasi
        $validator = Validator::make($request->all(),[
            'id'       => 'required|unique:users',
            'username' => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
            're_password' => 'required|same:password',
        ]);
        if ($validator->fails()){
            return response()->json(['error'=>$validator->errors()],401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        //input data user ke database
        $user = User::create($input);
        //membuat token
        
        $success['token'] =  $user->createToken('Personal Access Token')->accessToken;
        $success['username']  = $user->name;
 
        return response()->json(['success'=>$success],200); 
    }
    /**
     * @ login function
     * body: username,password
     * 
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'username' => 'required',
            'password' => 'required',
        ]);
         
        if(Auth::attempt(['username' => $request->username, 'password' => $request->password])){
            $user = Auth::user();
            $tokenResult =  $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            if($request->remember_me){
                $token->expires_at = Carbon::now()->addWeeks(1);
            }
            $token->save();
 
            return response()->json([
                'access_token'=>$tokenResult->accessToken,
                'token_type' => 'Bearer', 
                'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
            ]);
        }else{
            return response()->json(['error'=>'Unauthorized'],401);
        }
    }
 
    /**
     * @ logout function
     */
    public function logout(Request $request){
        $request->user()->token()->revoke();
        return response()->json(['massage'=>'sucessfully logout'],200);
    }
}