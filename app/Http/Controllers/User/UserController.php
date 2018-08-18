<?php

namespace App\Http\Controllers\User;

use App\Mail\UserCreated;
use App\Transformers\UserTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{

    public function __construct()
    {
//        $this->middleware('client.credentials')->only(['store','resend']);
//
//        $this->middleware('auth:api')->except(['store','resend','verify']);
//
//        $this->middleware('transform.input:'.UserTransformer::class)->only(['store','update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        return $this->showAll($users);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6|confirmed'
        ];

        $this->validate($request, $rules);

        $data = $request->all();

        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        $user = User::create($data);

        return $this->showOne($user, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
    	if($request->ajax()){
//			return $this->showOne($user, 200);
			$user = User::findOrfail($request->id);
			return $this->showOne($user);
		}

		return view('welcome');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $rules =[
            'email'     => 'email|unique:users, email'.$user->id,
            'password'  => 'min:6|confirmed',
            'admin'     => 'in:'. User::ADMIN_USER . ','. User::REGULAR_USER,
        ];

        if($request->has('name')){
            $user->name = $request->name;
        }

        if($request->has('email') && $request->email != $user->email){
            $user->verified = User::VERIFIED_USER;
            $user->email = $request->email;
        }

        if($request->has('password')){
            $user->password = bcrypt($request->password);
        }

//        if($request->has('admin')){
//            if(!$user->isVerified()){
//                return $this->errorResponse('Only verified user can modify the admin field', 409);
//            }
//
//            $user->admin = $request->admin;
//        }


        if(!$user->isDirty()){
            return $this->errorResponse('You need to specify a different value', 422);
        }

        $user->save();

        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //delete the user
        $user->delete();

        return $this->showOne($user);
    }


    public function verify(String $token){
        $user = User::where('verification_token', $token)->firstOrFail();
        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;

        $user->save();

        return $this->showMessage('User is successfully verified', 200);
    }

    public function resend(User $user){
        if($user->isVerified()){
            return $this->errorResponse('This user is has verified', 409);
        }
        retry(5, function() use ($user){
            Mail::to($user)->send(new UserCreated($user));
        }, 100);

        return $this->showMessage('User verification code has been send', 200);
    }
}
