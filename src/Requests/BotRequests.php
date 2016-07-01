<?php namespace BotTelegram\Requests;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;

class BotRequests extends Request {

	protected $messages = [];
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$images = $this->file( 'files' );

		if ( !empty( $images ) )
		{
			foreach ( $images as $key => $image ) // add individual rules to each image
			{
				Validator::replacer('each_file', function($message, $attribute, $rule, $parameters) use ($image) {

					return str_replace(':variable', $image->getClientOriginalName(), $message);
				});
//				$messages[ sprintf( 'files.%d', $key ) ] = 'llllllll;;;;;';
			}
		}

		$rules = [
			'files'=>'each_file:jpeg,jpg,bmp,png'
		];

		return $rules;
	}

//	public function messages()
//	{
//
//		return $messages;
//	}

	/*
	public function validator(\Illuminate\Validation\Factory $factory)
{
    \Validator::extend('nonEmptyArray', function($attribute, $value, $parameters) {
        return ( is_array($value) && count(array_filter($value)) > 0 );
    });

    $validator = $factory->make(
        $this->all(), $this->container->call([$this, 'rules']), $this->messages(), $this->attributes()
    );

    return $validator;
}
	*/

}
