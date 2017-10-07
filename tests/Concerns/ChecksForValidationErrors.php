<?php

namespace CodeZero\Translator\Tests\Concerns;

trait ChecksForValidationErrors
{
    /**
     * Check if a validation error for the given field is present.
     *
     * @param \Illuminate\Foundation\Testing\TestResponse $response
     * @param string|array $field
     *
     * @return $this
     */
    protected function assertValidationError($response, $field)
    {
        $fields = is_array($field) ? $field : [$field];
        $json = $response->decodeResponseJson();

        $response->assertStatus(422);

        foreach ($fields as $field) {
            $this->assertTrue(array_key_exists($field, $json['errors']));
        }

        return $this;
    }

    /**
     * Check if a validation error for the given field is not present.
     *
     * @param \Illuminate\Foundation\Testing\TestResponse $response
     * @param string|array $field
     *
     * @return $this
     */
    protected function assertNoValidationError($response, $field)
    {
        $fields = is_array($field) ? $field : [$field];
        $json = $response->decodeResponseJson();

        foreach ($fields as $field) {
            $this->assertFalse(
                array_key_exists($field, $json),
                "A validation error was returned for [{$field}] although this was not expected."
            );
        }

        return $this;
    }
}
