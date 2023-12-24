<?php

namespace App\Http\Traits;

trait ApiResponseTrait
{

    private function apiResponse($code = 200, $message = null, $errors = null, $data = null)
    {
        $array = [
            'status' => $code,
            'message' => $message,
        ];

        if (is_null($data) && !is_null($errors)) {
            if (is_string($errors)) {
                $array['errors'] = ["error" => [$errors]];
            } else {
                $array['errors'] = $errors;
            }
        } elseif (is_null($errors) && !is_null($data)) {
            $array['data'] = $data;
        }

        return response($array, $code);
    }

    /*-----------------------------------------------------------------------------------------------*/

    private function getFirstError($validator)
    {
        $errors = $validator->errors()->messages();
        $value = array_key_first($errors);
        return $errors[$value][0];
    }
}
