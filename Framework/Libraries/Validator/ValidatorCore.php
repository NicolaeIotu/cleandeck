<?php

/*
 * CleanDeck for CMD-Auth (https://link133.com) and other similar applications
 *
 * Copyright (c) 2023-2024 Iotu Nicolae, nicolae.g.iotu@link133.com
 * Licensed under the terms of the MIT License (MIT)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Framework\Libraries\Validator;

/**
 * This is a standalone validation library.
 *
 * Because it's specially built for Framework, the library is lighter and faster than other popular general purpose
 *  validation libraries. You may also find this library extremely easy to use.
 *
 */
class ValidatorCore
{
    /**
     * @var string[]
     */
    private array $validation_errors = [];

    private bool $validation_allow_unicode = true;

    private const VALIDATION_REGEXP_HEX = '/^[a-fA-F0-9]+$/';

    private const VALIDATION_REGEXP_ALPHANUMERIC = '/^[a-z0-9]+$/i';

    private const VALIDATION_REGEXP_UTF8_ALPHANUMERIC = '/^[\p{L}\p{N}]+$/u';

    private const VALIDATION_REGEXP_ALPHANUMERIC_SPACE = '/^[a-z0-9 ]+$/i';

    private const VALIDATION_REGEXP_UTF8_ALPHANUMERIC_SPACE = '/^[\p{L}\p{N}\p{Z}]+$/u';

    private const VALIDATION_REGEXP_ALPHANUMERIC_BASIC_PUNCT = '/^[a-z0-9!?_+=:.-]+$/i';

    private const VALIDATION_REGEXP_UTF8_ALPHANUMERIC_BASIC_PUNCT = '/^[\p{L}\p{N}\!\?_+=\:\.-]+$/u';

    private const VALIDATION_REGEXP_PASSWORD = '/^[a-z0-9#\$%&()*+,.\/:;<=>!?@^_{|}~-]+$/i';

    private const VALIDATION_REGEXP_ALPHANUMERIC_PUNCT = '/^[a-z0-9#\$%&()*+,.\/:;<=>!?@^_{|}~-]+$/i';

    private const VALIDATION_REGEXP_UTF8_ALPHANUMERIC_PUNCT = '/^[\p{L}\p{N}\p{P}\p{S}]+$/u';

    private const VALIDATION_REGEXP_ALPHANUMERIC_SPACE_PUNCT = '/^[a-z0-9 #\$%&()*+,.\/:;<=>!?@^_{|}~-]+$/i';

    private const VALIDATION_REGEXP_UTF8_ALPHANUMERIC_SPACE_PUNCT = '/^[\p{L}\p{N}\p{Z}\p{P}\p{S}]+$/u';




    /**
     * Available rules:
     * <pre>
     *  'label': string                 | 'hex'
     *  'if_exist'                      | 'in_list': array
     *  'permit_empty'                  | 'is_natural'
     *  'alpha_numeric'                 | 'less_than': number
     *  'alpha_numeric_basic_punct'     | 'less_than_equal_to': number
     *  'alpha_numeric_punct'           | 'matches': string
     *  'alpha_numeric_space'           | 'min_length': integer
     *  'alpha_numeric_space_punct'     | 'max_length': integer
     *  'email'                         | 'regex_match': string
     *  'password'                      | 'array'
     *  'greater_than': number          | 'min_items': integer
     *  'greater_than_equal_to': number | 'max_items': integer
     *
     * </pre>
     * <br>
     *
     * Usage:
     * <code><pre>
     *   $validator = new Validator([
     *       'product_id' => ['if_exists', 'hex', 'max_length' => 128],
     *       'title' => ['if_exists', 'alpha_numeric_space']
     *   ]);
     *   if ($validator->redirectOnError(UrlUtils::baseUrl('/...'), $_POST)) {
     *       return;
     *   }
     * </pre></code>
     *
     * @param array<string, mixed> $definitions
     */
    public function __construct(array $definitions)
    {
        try {
            $this->validation_allow_unicode = \env('cleandeck.validation.allow_unicode', true);
        } catch (\Exception) {
        }

        foreach ($definitions as $field => $rules) {
            if (\is_string($field) &&
                \is_array($rules) &&
                $rules !== []) {
                if (!$this->validate_definition($field, $rules)) {
                    return;
                }
            } else {
                $this->validation_errors[] = 'Invalid Validator definition';
            }
        }
    }

    private function requestMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    }

    /**
     * @param string $field
     * @param array<mixed> $rules
     * @return bool
     */
    private function validate_definition(string $field, array $rules): bool
    {
        // Caution: some rules are specified as value and other rules are
        //  specified as key+value. Use functions isset, in_array etc. properly.
        $label = $rules['label'] ?? $this->prettifyField($field);

        // handle 'if_exist'
        $is_mandatory = !\in_array('if_exist', $rules);

        $source_global = $this->requestMethod() === 'GET' ? $_GET : $_POST;

        if (isset($source_global[$field])) {
            $value = $source_global[$field];
        } elseif ($is_mandatory) {
            $this->validation_errors[] = 'Missing ' . $label;
            return false;
        } else {
            return true;
        }

        // handle 'permit_empty'
        if (\in_array('permit_empty', $rules) &&
            $value === '') {
            return true;
        }


        foreach ($rules as $rule => $parameter) {
            if (\is_string($rule)) {
                $real_rule = $rule;
            } else {
                $real_rule = $parameter;
            }

            $has_error = false;

            switch ($real_rule) {
                case 'alpha_numeric':
                    if (\preg_match($this->validation_allow_unicode ?
                            self::VALIDATION_REGEXP_UTF8_ALPHANUMERIC :
                            self::VALIDATION_REGEXP_ALPHANUMERIC, (string)$value) !== 1) {
                        $has_error = true;
                        $err_msg = 'letters and numbers expected';
                    }

                    break;
                case 'alpha_numeric_basic_punct':
                    if (\preg_match($this->validation_allow_unicode ?
                            self::VALIDATION_REGEXP_UTF8_ALPHANUMERIC_BASIC_PUNCT :
                            self::VALIDATION_REGEXP_ALPHANUMERIC_BASIC_PUNCT, (string)$value) !== 1) {
                        $has_error = true;
                        $err_msg = 'letters, numbers and basic punctuation (\'!#$%&*_+=|:.-\') expected';
                    }

                    break;
                case 'alpha_numeric_punct':
                    if (\preg_match($this->validation_allow_unicode ?
                            self::VALIDATION_REGEXP_UTF8_ALPHANUMERIC_PUNCT :
                            self::VALIDATION_REGEXP_ALPHANUMERIC_PUNCT, (string)$value) !== 1) {
                        $has_error = true;
                        $err_msg = 'letters, numbers and punctuation expected';
                    }

                    break;
                case 'alpha_numeric_space':
                    if (\preg_match($this->validation_allow_unicode ?
                            self::VALIDATION_REGEXP_UTF8_ALPHANUMERIC_SPACE :
                            self::VALIDATION_REGEXP_ALPHANUMERIC_SPACE, (string)$value) !== 1) {
                        $has_error = true;
                        $err_msg = 'letters, numbers and spacing expected';
                    }

                    break;
                case 'alpha_numeric_space_punct':
                    if (\preg_match($this->validation_allow_unicode ?
                            self::VALIDATION_REGEXP_UTF8_ALPHANUMERIC_SPACE_PUNCT :
                            self::VALIDATION_REGEXP_ALPHANUMERIC_SPACE_PUNCT, (string)$value) !== 1) {
                        $has_error = true;
                        $err_msg = 'letters, numbers, spacing and punctuation expected';
                    }

                    break;
                case 'email':
                    // 'admin100k' is a special 'email' used by CMD-Auth as the default super-administrator.
                    if (\filter_var($value, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE) === false &&
                        \strtolower((string)$value) !== 'admin100k') {
                        $has_error = true;
                    }

                    break;
                case 'password':
                    // the password is not affected by our Unicode choice
                    if (\preg_match(self::VALIDATION_REGEXP_PASSWORD, (string)$value) !== 1) {
                        $has_error = true;
                        $err_msg = 'letters, numbers, punctuation expected';
                    }

                    break;
                case 'greater_than':
                    if ((float)$value <= $parameter) {
                        $has_error = true;
                        $err_msg = 'expecting value greater than ' . $parameter;
                    }

                    break;
                case 'greater_than_equal_to':
                    if ((float)$value < $parameter) {
                        $has_error = true;
                        $err_msg = 'expecting value greater or equal to ' . $parameter;
                    }

                    break;
                case 'hex':
                    if (\preg_match(self::VALIDATION_REGEXP_HEX, (string)$value) !== 1) {
                        $has_error = true;
                        $err_msg = 'hex characters expected';
                    }

                    break;
                case 'in_list':
                    if (\is_array($parameter)) {
                        if (!\in_array($value, $parameter)) {
                            $has_error = true;
                            $err_msg = 'expecting one of "' . \implode('", "', $parameter) . '"';
                        }
                    } else {
                        $has_error = true;
                        $err_msg = 'validator setup is expecting an array of allowed values';
                    }

                    break;
                case 'is_natural':
                    if (\filter_var($value, FILTER_VALIDATE_INT) === false ||
                        (int)$value < 0) {
                        $has_error = true;
                        $err_msg = 'expecting an integer';
                    }

                    break;
                case 'less_than':
                    if ((float)$value >= $parameter) {
                        $has_error = true;
                        $err_msg = 'expecting value less than ' . $parameter;
                    }

                    break;
                case 'less_than_equal_to':
                    if ((float)$value > $parameter) {
                        $has_error = true;
                        $err_msg = 'expecting value less or equal to ' . $parameter;
                    }

                    break;
                case 'matches':
                    if (!isset($source_global[$parameter])) {
                        $has_error = true;
                        $err_msg = "no such element '" . $parameter . "'";
                    } elseif ($value !== $source_global[$parameter]) {
                        $has_error = true;
                        $err_msg = "'" . $label . "' and '" .
                            $this->prettifyField($parameter) . "' must have the same values";
                    }

                    break;
                case 'min_length':
                    if (\is_string($value)) {
                        if (\strlen($value) < $parameter) {
                            $has_error = true;
                            $err_msg = 'minimum length ' . $parameter;
                        }
                    } elseif (\is_array($value)) {
                        $i = 1;
                        foreach ($value as $v_entry) {
                            if (\strlen((string)$v_entry) < $parameter) {
                                $has_error = true;
                                $err_msg = 'minimum length ' . $parameter . ' for entry ' . $i .
                                    ($v_entry === '' ? '' : '(' . $v_entry . ')');
                            }

                            ++$i;
                        }
                    }

                    break;
                case 'max_length':
                    if (\is_string($value)) {
                        if (\strlen($value) > $parameter) {
                            $has_error = true;
                            $err_msg = 'maximum length ' . $parameter;
                        }
                    } elseif (\is_array($value)) {
                        foreach ($value as $v_entry) {
                            if (\strlen((string)$v_entry) > $parameter) {
                                $has_error = true;
                                $err_msg = 'maximum length ' . $parameter . ' for entry ' . $v_entry;
                            }
                        }
                    }

                    break;
                case 'regex_match':
                    if (\preg_match($parameter, (string)$value) !== 1) {
                        $has_error = true;
                        $err_msg = "expected format '" . $parameter . "'";
                    }

                    break;
                case 'array':
                    if (!\is_array($value)) {
                        $has_error = true;
                        $err_msg = 'expecting an array';
                    }

                    break;
                case 'min_items':
                    if (\is_array($value) && \count($value) < $parameter) {
                        $has_error = true;
                        $err_msg = "minimum number of entries '" . $parameter . "'";
                    }

                    break;
                case 'max_items':
                    if (\is_array($value) && \count($value) > $parameter) {
                        $has_error = true;
                        $err_msg = "maximum number of entries '" . $parameter . "'";
                    }

                    break;
                // Rules with different type of handling
                case 'if_exist':
                case 'label':
                case 'permit_empty':
                    // No actions here.
                    break;
                // End Rules with different type of handling
                default:
                    $has_error = true;
                    $err_msg = "invalid validation rule '" . $real_rule . "'";
            }

            if ($has_error) {
                $this->validation_errors[] = 'Invalid ' . $label . (isset($err_msg) ? ': ' . $err_msg : '');
                return false;
            }
        }

        return true;
    }

    private function prettifyField(string $field): string
    {
        return \str_replace('_', ' ', \ucfirst($field));
    }

    public function hasErrors(): bool
    {
        return $this->validation_errors !== [];
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->validation_errors;
    }
}
