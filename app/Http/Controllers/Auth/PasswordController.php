<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PasswordController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Password Reset Controller
	|--------------------------------------------------------------------------
	|
	| This controller is responsible for handling password reset requests
	| and uses a simple trait to include this behavior. You're free to
	| explore this trait and override any methods you wish to tweak.
	|
	*/

	use ResetsPasswords;

	/**
	 * Create a new password controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\PasswordBroker  $passwords
	 * @return void
	 */
	public function __construct(Guard $auth, PasswordBroker $passwords)
	{
		$this->auth = $auth;
		$this->passwords = $passwords;

		$this->middleware('guest');
	}

	/**
	 * [getEmail description]
	 * @return [type] [description]
	 */
	public function getEmail()
	{
		$this->layout->content = view('auth.password');
	}

	/**
	 * [getReset description]
	 * @param  [type] $token [description]
	 * @return [type]        [description]
	 */
	public function getReset($token = null)
	{
		if ( is_null( $token ) ) {
			throw new NotFoundHttpException;
		}
		$this->layout->content = view('auth.reset')->with('token', $token);
	}

}