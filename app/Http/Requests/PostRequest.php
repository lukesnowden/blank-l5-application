<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Auth\Guard;

class PostRequest extends Request {

	/**
	 * [authorize description]
	 * @return {[type]} [description]
	 */
	public function authorize( Guard $auth ) {
		return true;
	}

	/**
	 * [rules description]
	 * @return {[type]} [description]
	 */
	public function rules() {
		return [

			'name' => 'required',
			'slug' => 'required',
			'category_id' => 'required',

		];
	}

}