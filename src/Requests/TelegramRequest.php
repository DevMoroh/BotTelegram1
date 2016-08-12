<?php

namespace BotTelegram\Requests;

use App\Http\Requests\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class TelegramRequest extends Request
{
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
        return [
            'token' => 'required',
            'text' => 'required',
        ];
    }

    public function response(array $errors)
    {
//        if ($this->ajax())
//        {
//            return new JsonResponse($errors, 422);
//        }
//        return $this->redirector->to($this->getRedirectUrl())
//            ->withInput($this->except($this->dontFlash))
//            ->withErrors($errors);

        return new JsonResponse($errors, 422);
    }

    public function forbiddenResponse()
    {
        return Response::make('You are too young to register!',403);
    }
}
