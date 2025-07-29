<?php

namespace NickMous\Binsta\Internals\Validation;

interface HasValidation
{
    /**
     * Define the validation rules for the request.
     *
     * @return array|string[]|string[][]
     */
    public function rules(): array;

    /**
     * Define the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array;
}
