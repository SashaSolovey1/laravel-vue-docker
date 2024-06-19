<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidHtmlTags implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->validateHtmlTags($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Теги HTML в поле текста должны быть правильно закрыты.';
    }

    /**
     * Validate HTML tags.
     *
     * @param  string  $html
     * @return bool
     */
    private function validateHtmlTags($html)
    {
        // Поддерживаемые теги
        $allowedTags = ['i', 'strong', 'code', 'a'];

        // Ищем все открытые теги
        preg_match_all('#<(' . implode('|', $allowedTags) . ')(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedTags = $result[1];

        // Ищем все закрытые теги
        preg_match_all('#</(' . implode('|', $allowedTags) . ')>#iU', $html, $result);
        $closedTags = $result[1];

        // Если количество открытых и закрытых тегов не совпадает, возвращаем false
        if (count($closedTags) != count($openedTags)) {
            return false;
        }

        // Проверяем корректность закрытия тегов
        $openedTags = array_reverse($openedTags);
        foreach ($openedTags as $tag) {
            if (!in_array($tag, $closedTags)) {
                return false;
            } else {
                unset($closedTags[array_search($tag, $closedTags)]);
            }
        }

        return true;
    }
}
